<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 02/10/2019
 * Time: 09:13
 */

namespace Test\Stub;

use PDO;
use Repository\Field;
use Repository\Repository;
use Repository\ValidatorInterface;

class Stub2Repository extends Repository
{
    public function __construct(Pdo $pdo, ValidatorInterface $validator)
    {
        parent::__construct($pdo, $validator);
        $this->table = "stub2";
        $this->structure = [
            new Field(Field::T_INT | Field::T_PK, 'id', Field::A_READ),
            new Field(Field::T_STR, 'valeur', Field::A_READ | Field::A_WRITE),
        ];
    }
}