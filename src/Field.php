<?php

namespace Repository;

class Field
{

    private $type;
    private $nom;
    private $acces;

    public const T_PK = 1;
    public const T_NN = 2;
    public const T_INT = 4;
    public const T_FLOAT = 8;
    public const T_BOOL = 16;
    public const T_STR = 32;
    public const T_TIME = 64;
    public const T_DATE = 128;
    public const T_DATETIME = 256;

    public const A_READ = 1;
    public const A_WRITE = 2;

    /**
     * Field constructor.
     * @param int $type
     * @param string $nom
     * @param int $acces
     */
    public function __construct(int $type, string $nom, int $acces)
    {
        $this->nom = $nom;
        $this->acces = $acces;
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isNotNull(): bool
    {
        return ($this->type & self::T_NN) === self::T_NN;
    }

    /**
     * @return bool
     */
    public function isPk(): bool
    {
        return ($this->type & self::T_PK) === self::T_PK;
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return ($this->acces & self::A_READ) === self::A_READ;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return ($this->acces & self::A_WRITE) === self::A_WRITE;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param array|object $data
     * @return bool
     */
    public function exist($data): bool
    {
        if (is_array($data) || is_object($data)) {
            return isset($dataArray[$this->nom]);
        }
        $className = basename(static::class);
        throw new \LogicException("Entity donné à $className non valide");
    }

    /**
     * @param array $data
     * @return string
     */
    public function get(array $data): string
    {
        return $this->cast($data[$this->nom]);
    }

    /**
     * @param $value
     * @return string|null
     */
    protected function cast($value): ?string
    {
        $type = $this->type & ~self::T_NN;
        $type &= ~self::T_PK;
        switch ($type) {
            case self::T_INT:
                return (int)$value;
            case self::T_FLOAT:
                return (Double)$value;
            case self::T_BOOL:
                return (bool)$value;
            case self::T_STR:
                return (string)$value;
            case self::T_TIME:
                return (int)$value;
            case self::T_DATE:
                try {
                    return $this->castDate($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException(sprintf('la valeur du champ %s n\'est pas compatible avec le type DATE', $this->nom));
                }
            case self::T_DATETIME:
                try {
                    return $this->castDateTime($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException(sprintf('la valeur du champ %s n\'est pas compatible avec le type DATETIME', $this->nom));
                }
            default:
                throw new \RuntimeException('Type de champ inconnu.');
        }
    }

    /**
     * @param $value
     * @return string
     * @throws \Exception
     */
    protected function castDate($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        return (new \DateTime((string)$value))->format('Y-m-d');
    }

    /**
     * @param $value
     * @return string
     * @throws \Exception
     */
    protected function castDateTime($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        return (new \DateTime((string)$value))->format('Y-m-d H:i:s');
    }

    /**
     * Donne la clé primaire d'une liste de Field
     * @param self[] $fields
     * @return string
     */
    public static function getPk(array $fields): ?string
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