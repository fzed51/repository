<?php
declare(strict_types=1);

/**
 * User: Fabien Sanchez
 * Date: 02/10/2019
 * Time: 09:03
 */

use PHPUnit\Framework\TestCase;
use Test\Stub\Stub2Repository;
use Test\Stub\StubRepository;
use Test\Stub\StubValidator;

class RepositoryTest extends TestCase
{
    protected $pdo;

    public function testSave()
    {

        $repo = new StubRepository($this->pdo, new StubValidator());

        $entity = [
            'id' => 2,
            'valeur' => 'lorem'
        ];
        $stored = $repo->save($entity);
        $this->assertIsArray($stored);
        $this->assertArrayHasKey('id', $stored);
        $this->assertArrayHasKey('valeur', $stored);
        $this->assertSame($entity['id'], $stored['id']);
        $this->assertSame($entity['valeur'], $stored['valeur']);
    }

    public function testSave_autoincrement()
    {

        $repo = new Stub2Repository($this->pdo, new StubValidator());

        $entity = [
            'valeur' => 'lorem'
        ];
        $stored = $repo->save($entity);
        $this->assertIsArray($stored);
        $this->assertArrayHasKey('id', $stored);
        $this->assertArrayHasKey('valeur', $stored);
        $this->assertGreaterThan(0, (int)$stored['id']);
        $this->assertSame($entity['valeur'], $stored['valeur']);
    }

    public function testUpdateOnUnknowEntity()
    {
        $repo = new Stub2Repository($this->pdo, new StubValidator());

        $entity = [
            'id' => 111111,
            'valeur' => 'lorem'
        ];
        $stored = $repo->save($entity);
        $this->assertNull($stored);
    }

    public function testGetLastId()
    {
        $repo = new Stub2Repository($this->pdo, new StubValidator());
        $stored = $repo->save(['valeur' => 'lorem']);
        $lastId = $repo->getLastId();
        $this->assertSame($stored['id'], $lastId);
    }

    public function testGetById()
    {
        $repo = new Stub2Repository($this->pdo, new StubValidator());
        $valeur = (string)uniqid('', true);
        $stored = $repo->save(['valeur' => $valeur]);
        $return = $repo->getById((int)$stored['id']);
        $this->assertSame($valeur, $return['valeur']);
    }

    public function test_castOutput()
    {
        $repo = new Stub2Repository($this->pdo, new StubValidator());
        $valeur = "1";
        $stored = $repo->save(['valeur' => $valeur]);
        $this->assertIsInt($stored['id']);
        $this->assertIsString($stored['valeur']);
    }

    public function testGetAll()
    {
        $repo = new Stub2Repository($this->pdo, new StubValidator());
        $stored = $repo->save(['valeur' => 'lorem']);
        $lastId = (int)$repo->getLastId();
        $all = $repo->getAll();
        $this->assertGreaterThan(0, count($all));
    }

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('create table stub1 ( id INTEGER , valeur TEXT )');
        $pdo->exec('create table stub2 ( id INTEGER PRIMARY KEY AUTOINCREMENT, valeur TEXT )');
        $this->pdo = $pdo;
    }
}
