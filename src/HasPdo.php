<?php

namespace Repository;

use App\Exception\InternalError;
use App\Exception\PdoExceptionByPass;

/**
 * Fonctioin utilisant la propriété PDO
 */
trait HasPdo
{
    /**
     * connexion a la base de donnée via PDO
     * @var \PDO
     */
    protected $pdo;
    /**
     * encodage de la base de donnée
     * @var string
     */
    protected $encoded = 'UTF-8';
    
    /**
     * Requete SQL à executer
     * @var string
     */
    protected $reSql;
    /**
     * Cache de requête SQL
     * @var \PDOStatement[];
     */
    private $cachePdoStatement = [];

    /**
     * Affecte la requete SQL
     * @param string $reqSql
     * @return void
     */
    protected function setReqSql(string $reqSql)
    {
        $this->reqSql = $reqSql;
    }

    /**
     * Execute la requete SQL
     * @param array $params
     * @return \PDOStatement
     */
    protected function execute(array $params) : \PDOStatement
    {
        try {
            /**
             * @var \PDOStatement $stm
             */
            $stm = $this->prepare();
            $ok = true;
            if (empty($params)) {
                $ok = $stm->execute();
            } else {
                $params = $this->controlInputEncoding($params);
                $ok = $stm->execute($params);
            }
            if (!$ok) {
                throw new InternalError(
                    "Impossible d'executer la requete : '" . $this->reSql
                        . "' dans " . static::class
                        . " avec " . var_export($params, true)
                );
            }
            return $stm;
        } catch (\PDOException $ex) {
            throw new PdoExceptionByPass($ex);
        }
        return $stm;
    }

    /**
     * Prépare la requete SQL
     * @return \PDOStatement
     */
    protected function prepare() : \PDOStatement
    {
        if (empty($this->reqSql)) {
            throw new InternalError("Il n'y a pas de requêtes initialisée dans " . static::class);
        }
        $hashReq = md5($this->reqSql);
        if (!isset($this->cachePdoStatement[$hashReq])) {
            $stm = $this->pdo->prepare($this->reqSql);
            if ($stm === false) {
                throw new InternalError("Impossible de préparer la requete : " . $this->reqSql . " dans " . static::class);
            }
            $this->cachePdoStatement[$hashReq] = $stm;
        }
        return $this->cachePdoStatement[$hashReq];
    }

    /**
     * Retourne un enregistrement
     * @param array $param
     * @return array|null
     * @throws InternalError
     * @throws PdoExceptionByPass
     */
    protected function fetchOne(array $param = []) : ? array
    {
        $fetch = $this->execute($param)->fetch(\PDO::FETCH_ASSOC);
        // TODO : modifier le charset
        return $fetch !== false ? $fetch : null;
    }

    /**
     * Retourne tous les enregistrements
     * @param array $param
     * @return array
     * @throws InternalError
     * @throws PdoExceptionByPass
     */
    protected function fetchAll(array $param = []) : array
    {
        // TODO : modifier le charset
        return $this->execute($param)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * modifie les charset pour la base de donnée
     * @param array $elements
     * @return array
     */
    protected function controlInputEncoding(array $elements) : array
    {
        $out = [];
        foreach ($elements as $key => $value) {
            if (is_string($value) && !is_null($value)) {
                $out[$key] = $this->changeEncoding($value, $this->encoded);
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * modifie les charset pour la sortie php
     * @param array $elements
     * @return array
     */
    protected function controlOutputEncoding(array $elements) : array
    {
        $out = [];
        foreach ($elements as $key => $value) {
            if (is_string($value)) {
                $out[$key] = $this->changeEncoding($value, ini_get('default_charset'));
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * modifie l'encodage d'une chaine
     * @param string $value
     * @param string $newCharset
     * @return string
     */
    protected function changeEncoding(string $value, string $newCharset) : string
    {
        $supportedCharset = [];
        $supportedCharset[] = 'UTF-32';
        $supportedCharset[] = 'UTF-16';
        $supportedCharset[] = 'UTF-8';
        $supportedCharset[] = 'CP1252';
        $supportedCharset[] = 'ISO-8859-15';
        $supportedCharset[] = 'ISO-8859-1';
        $supportedCharset[] = 'ASCII';
        $charset = mb_detect_encoding ($value, $supportedCharset, true);
        if($newCharset != $charset){
            return mb_convert_encoding($value, $newCharset, $charset);
        }
        return $value;
    }

    /**
     * retourne le driver PDO de la base de donnée
     * @return string
     */
    protected function getDbDriver() :string
    {
        return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
}
