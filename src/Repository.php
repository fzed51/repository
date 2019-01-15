<?php

namespace Repository;

/**
 * Class Repository
 * @package Repository
 */
abstract class Repository
{
    /**
     * table de stockage des etity
     * @var string
     */
    protected $table;
    /**
     * Structure de l'entity
     * @var Field[]
     */
    protected $structure;
    /**
     * Validateur de l'entity
     * @var ValidatorInterface
     */
    protected $validator;

    use HasPdo;

    public function __construct(\Pdo $pdo, ValidatorInterface $validator)
    {
        $this->pdo = $pdo;
        $this->validator = $validator;
    }

    /**
     * sauvegarde d'une transaction
     * @param array $entity
     * @return array
     */
    public function save(array $entity): ?array
    {
        ($this->validator)($entity);
        $pk = Field::getPk($this->structure);
        if (is_null($pk) || empty($entity[$pk] ?? 0)) {
            return $this->insert($entity);
        }
        return $this->update($entity);
    }

    protected function update(array $entity): ?array
    {
        $pk = Field::getPk($this->structure);
        $id = $entity[$pk];
        $fields = [];
        $values = [];
        foreach ($this->structure as $field) {
            if ($field->isWritable()) {
                if ($field->isNotNull()
                    && (!isset($entity[$field->getNom()])
                        || is_null($entity[$field->getNom()])
                        || $entity[$field->getNom()] == "")) {
                    throw new BadRequest("Le champ {$field->getNom()} est requis pour {$this->table}. ");
                }
            }
            if (isset($entity[$field->getNom()])) {
                $fields[] = $field->getNom() . " = ?";
                if (is_null($entity[$field->getNom()])) {
                    $values[] = null;
                } else {
                    $values[] = $field->get($entity);
                }
            }
        }
        $fields = implode(', ', $fields);
        $values[] = $id;
        $req = "update {$this->table} set $fields where $pk = ?";
        $this->setReqSql($req);
        $this->execute($values);
        return $this->getById([$id]);
    }

    protected function insert(array $entity): ?array
    {
        $patterns = [];
        $fields = [];
        $values = [];
        foreach ($this->structure as $field) {
            if ($field->isWritable()) {
                if ($field->isNotNull()
                    && (!isset($entity[$field->getNom()])
                        || is_null($entity[$field->getNom()])
                        || $entity[$field->getNom()] == "")) {
                    throw new BadRequest("Le champ {$field->getNom()} est requis pour {$this->table}. ");
                }
                if (isset($entity[$field->getNom()])) {
                    $patterns[] = '?';
                    $fields[] = $field->getNom();
                    if (is_null($entity[$field->getNom()])) {
                        $values[] = null;
                    } else {
                        $values[] = $field->get($entity);
                    }
                }
            }
        }
        $fields = implode(', ', $fields);
        $patterns = implode(', ', $patterns);
        $this->setReqSql("INSERT INTO {$this->table} ($fields) VALUES ($patterns)");
        $this->execute($values);
        return $this->getLastRow();
    }

    public function getAll(): array
    {
        $this->setReqSql("SELECT * FROM {$this->table}");
        return $this->fetchAll();
    }

    public function getById($id): ?array
    {
        $pk = Field::getPk($this->structure);
        $this->setReqSql("SELECT * FROM {$this->table} WHERE $pk = ?");
        return $this->fetchOne($id);
    }

    protected function getLastRow(): array
    {
        $pk = Field::getPk($this->structure);
        if (!is_null($pk)) {
            $id = $this->getLastId();
            $this->setReqSql("SELECT * FROM {$this->table} WHERE $pk = ?");
            return $this->fetchOne([$id]);
        }
        switch ($this->getDbDriver()) {
            case 'sqlite':
                $this->setReqSql("SELECT * FROM {$this->table} WHERE ROWID = (select last_insert_rowid() from {$this->table})");
                return $this->fetchOne();
            case 'postgresql':
                $this->setReqSql("SELECT *, ROW_NUMBER() OVER () FROM {$this->table} ORDER by ROW_NUMBER DESC LIMIT 1");
                return $this->fetchOne();
            case 'oci':
                $this->setReqSql("SELECT *, ROWID FROM {$this->table} WHERE ROWID = (select max(ROWID) from {$this->table})");
                return $this->fetchOne();
            default:
                throw new \LogicException("Il est impossible de récupéré le dernier enregistrement de {$this->table}.");
        }

    }

    public function getLastId()
    {
        $pk = Field::getPk($this->structure);
        $this->setReqSql("select max($pk) as idmax from {$this->table}");
        $data = array_change_key_case($this->fetchOne(), CASE_LOWER);
        return $data['idmax'];
    }

}