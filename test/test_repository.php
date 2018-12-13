<?php

use Repository\Field;
use Repository\Repository;
use Repository\ValidatorInterface;

require(__DIR__ . "/../vendor/autoload.php");

class TestValidator implements ValidatorInterface
{
    public function __invoke(array $data)
    {
    }
}

class TestRepository extends Repository
{
    public function __construct(\Pdo $pdo, ValidatorInterface $validator)
    {
        parent::__construct($pdo, $validator);
        $this->table = "test";
        $this->structure = [
            new Field(Field::T_INT, 'id', Field::A_READ | Field::A_WRITE),
            new Field(Field::T_STR, 'valeur', Field::A_READ | Field::A_WRITE),
            new Field(Field::T_DATE, 'jour', Field::A_READ | Field::A_WRITE)
        ];
    }
}

class Test2Repository extends Repository
{
    public function __construct(\Pdo $pdo, ValidatorInterface $validator)
    {
        parent::__construct($pdo, $validator);
        $this->table = "xtest2";
        $this->structure = [
            new Field(Field::T_INT | Field::T_PK, 'id', Field::A_READ),
            new Field(Field::T_STR, 'valeur', Field::A_READ | Field::A_WRITE)
        ];
    }
}

$pdo = new \PDO("sqlite::memory:");

$ok = $pdo->exec("create table test ( id INTEGER , valeur TEXT, jour TEXT )");
$ok = $pdo->exec("create table xtest2 ( id INTEGER PRIMARY KEY AUTOINCREMENT, valeur TEXT )");

$repo = new TestRepository($pdo, new TestValidator());

var_export($repo->save([
    "id" => 2,
    "valeur" => "lorem",
    "jour" => (new DateTime())->format('Y-m-d')
]));

var_export($pdo->query("select * from test")->fetchAll(PDO::FETCH_ASSOC));

$repo2 = new Test2Repository($pdo, new TestValidator());

var_export($repo2->save([
    "valeur" => "lorem ipsum"
]));

var_export($pdo->query("select * from xtest2")->fetchAll(PDO::FETCH_ASSOC));

var_export($repo2->save([
    "id" => 1,
    "valeur" => "ipsum lorem"
]));

var_export($pdo->query("select * from xtest2")->fetchAll(PDO::FETCH_ASSOC));

var_export($repo2->save([
    "valeur" => "Lorem Elsass ipsum libero, schnaps Miss Dahlias wurscht merci vielmols mänele placerat nullam Racing."
]));

var_export($pdo->query("select * from xtest2")->fetchAll(PDO::FETCH_ASSOC));
