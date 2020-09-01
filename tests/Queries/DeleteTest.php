<?php

require __DIR__ . '/../_resources/init.php';

use PHPUnit\Framework\TestCase;
use Envms\FluentPDO\Query;

/**
 * Class DeleteTest
 *
 * @covers \Envms\FluentPDO\Queries\Delete
 */
class DeleteTest extends TestCase
{

    /** @var Query */
    protected $fluent;

    public function setUp(): void
    {
        global $pdo;

        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_BOTH);

        $this->fluent = new Query($pdo);
    }

    public function testDelete()
    {
        $query = $this->fluent->deleteFrom('user')
            ->where('id', 1);

        self::assertEquals('DELETE FROM user WHERE id = ?', $query->getQuery(false));
        self::assertEquals(['0' => '1'], $query->getParameters());
    }

    public function testDeleteIgnore()
    {
        $query = $this->fluent->deleteFrom('user')
            ->ignore()
            ->where('id', 1);

        self::assertEquals('DELETE IGNORE FROM user WHERE id = ?', $query->getQuery(false));
        self::assertEquals(['0' => '1'], $query->getParameters());
    }

    public function testDeleteOrderLimit()
    {
        $query = $this->fluent->deleteFrom('user')
            ->where('id', 2)
            ->orderBy('name')
            ->limit(1);

        self::assertEquals('DELETE FROM user WHERE id = ? ORDER BY name LIMIT 1', $query->getQuery(false));
        self::assertEquals(['0' => '2'], $query->getParameters());
    }

    public function testDeleteExpanded()
    {
        $query = $this->fluent->delete('t1, t2')
            ->from('t1')
            ->innerJoin('t2 ON t1.id = t2.id')
            ->innerJoin('t3 ON t2.id = t3.id')
            ->where('t1.id', 1);

        self::assertEquals('DELETE t1, t2 FROM t1 INNER JOIN t2 ON t1.id = t2.id  INNER JOIN t3 ON t2.id = t3.id WHERE t1.id = ?',
            $query->getQuery(false));
        self::assertEquals(['0' => '1'], $query->getParameters());
    }

    public function testDeleteShortcut()
    {
        $query = $this->fluent->deleteFrom('user', 1);

        self::assertEquals('DELETE FROM user WHERE id = ?', $query->getQuery(false));
        self::assertEquals(['0' => '1'], $query->getParameters());
    }

    public function testAddFromAfterDelete()
    {
        $query = $this->fluent->delete('user', 1)->from('user');

        self::assertEquals('DELETE user FROM user WHERE id = ?', $query->getQuery(false));
        self::assertEquals(['0' => '1'], $query->getParameters());
    }
}