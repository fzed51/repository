<?php

namespace Repository;


class Field
{

    private $type;
    private $nom;
    private $acces;

    const T_PK = 1;
    const T_NN = 2;
    const T_INT = 4;
    const T_FLOAT = 8;
    const T_BOOL = 16;
    const T_STR = 32;
    const T_TIME = 64;
    const T_DATE = 128;
    const T_DATETIME = 256;

    const A_READ = 1;
    const A_WRITE = 2;

    public function __construct(int $type, string $nom, int $acces)
    {
        $this->nom = $nom;
        $this->acces = $acces;
        $this->type = $type;
    }

    public function isNotNull()
    {
        return ($this->type & self::T_NN) == self::T_NN;
    }

    public function isPk()
    {
        return ($this->type & self::T_PK) == self::T_PK;
    }

    public function isReadable()
    {
        return ($this->acces & self::A_READ) == self::A_READ;
    }

    public function isWritable()
    {
        return ($this->acces & self::A_WRITE) == self::A_WRITE;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function exist($data)
    {
        if (is_array($data) || is_object($data)) {
            return isset($dataArray[$this->nom]);
        }
        $className = basename(static::class);
        throw \App\Exception\InternalError("Entity donné à $className non valide");
    }

    public function get(array $data)
    {
        return $this->cast($data[$this->nom]);
    }

    protected function cast($value)
    {
        $type = $this->type & ~Field::T_NN;
        $type = $type & ~Field::T_PK;
        switch ($type) {
            case Field::T_INT:
                return (int)$value;
            case Field::T_FLOAT:
                return (Double)$value;
            case Field::T_BOOL:
                return (bool)$value;
            case Field::T_STR:
                return (string)$value;
            case Field::T_TIME:
                throw new Exception("Cas non pris en compte", 500);
                return (int)$value;
            case Field::T_DATE:
                return $this->castDate($value);
            case Field::T_DATETIME:
                return $this->castDateTime($value);
        }
    }

    protected function castDate($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format("Y-m-d");
        }
        return (new \DateTime((string)$value))->format("Y-m-d");
    }

    protected function castDateTime($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format("Y-m-d H:i:s");
        }
        return (new \DateTime((string)$value))->format("Y-m-d H:i:s");
    }

    /**
     * Donne la clé primaire d'une liste de Field
     * @param self[] $fields
     * @return string
     */
    static public function getPk(array $fields): ?string
    {
        /**
         * @var self $field
         */
        $fieldPk = array_filter($fields, function (Field $field) {
            return $field->isPk();
        });
        return isset($fieldPk[0]) ? $fieldPk[0]->nom : null;
    }
}