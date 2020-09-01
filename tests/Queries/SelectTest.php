<?php

require __DIR__ . '/../_resources/init.php';

use PHPUnit\Framework\TestCase;
use Envms\FluentPDO\Query;

/**
 * Class SelectTest
 *
 * @covers \Envms\FluentPDO\Queries\Select
 */
class SelectTest extends TestCase
{

    /** @var Query */
    protected $fluent;

    public function setUp(): void
    {
        global $pdo;

        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_BOTH);

        $this->fluent = new Query($pdo);
    }

    public function testBasicQuery()
    {
        $query = $this->fluent
            ->from('user')
            ->where('id > ?', 0)
            ->orderBy('name');

        $query = $query->where('name = ?', 'Marek');

        self::assertEquals('SELECT user.* FROM user WHERE id > ? AND name = ? ORDER BY name', $query->getQuery(false));
        self::assertEquals(['id' => '1', 'country_id' => '1', 'type' => 'admin', 'name' => 'Marek'], $query->fetch());
        self::assertEquals([0 => 0, 1 => 'Marek'], $query->getParameters());
    }

    public function testReturnQueryWithHaving()
    {

        $query = $this->fluent
            ->from('user')
            ->select(null)
            ->select('type, count(id) AS type_count')
            ->where('id > ?', 1)
            ->groupBy('type')
            ->having('type_count > ?', 1)
            ->orderBy('name');

        self::assertEquals("SELECT type, count(id) AS type_count FROM user WHERE id > ? GROUP BY type HAVING type_count > ? ORDER BY name",
            $query->getQuery(false));
    }

    public function testReturnParameterWithId()
    {
        $query = $this->fluent
            ->from('user', 2);

        self::assertEquals([0 => 2], $query->getParameters());
        self::assertEquals('SELECT user.* FROM user WHERE user.id = ?', $query->getQuery(false));
    }

    public function testFromWithAlias()
    {
        $query = $this->fluent->from('user author')->getQuery(false);
        $query2 = $this->fluent->from('user AS author')->getQuery(false);
        $query3 = $this->fluent->from('user AS author', 1)->getQuery(false);
        $query4 = $this->fluent->from('user AS author')->select('country.name')->getQuery(false);

        self::assertEquals('SELECT author.* FROM user author', $query);
        self::assertEquals('SELECT author.* FROM user AS author', $query2);
        self::assertEquals('SELECT author.* FROM user AS author WHERE author.id = ?', $query3);
        self::assertEquals('SELECT author.*, country.name FROM user AS author LEFT JOIN country ON country.id = user AS author.country_id', $query4);
    }

    public function testWhereArrayParameter()
    {
        $query = $this->fluent
            ->from('user')
            ->where([
                'id' => 2,
                'type' => 'author'
            ]);

        self::assertEquals('SELECT user.* FROM user WHERE id = ? AND type = ?', $query->getQuery(false));
        self::assertEquals([0 => 2, 1 => 'author'], $query->getParameters());
    }

    public function testWhereColumnValue()
    {
        $query = $this->fluent->from('user')
            ->where('type', 'author');

        self::assertEquals('SELECT user.* FROM user WHERE type = ?', $query->getQuery(false));
        self::assertEquals([0 => 'author'], $query->getParameters());
    }

    public function testWhereColumnNull()
    {
        $query = $this->fluent
            ->from('user')
            ->where('type', null);

        self::assertEquals('SELECT user.* FROM user WHERE type IS NULL', $query->getQuery(false));
    }

    public function testWhereColumnArray()
    {
        $query = $this->fluent
            ->from('user')
            ->where('id', [1, 2, 3]);

        self::assertEquals('SELECT user.* FROM user WHERE id IN (1, 2, 3)', $query->getQuery(false));
        self::assertEquals([], $query->getParameters());
    }

    public function testWherePreparedArray()
    {
        $query = $this->fluent
            ->from('user')
            ->where('id IN (?, ?, ?)', [1, 2, 3]);

        self::assertEquals('SELECT user.* FROM user WHERE id IN (?, ?, ?)', $query->getQuery(false));
        self::assertEquals([0 => 1, 1 => 2, 2 => 3], $query->getParameters());
    }

    public function testWhereColumnName()
    {
        $query = $this->fluent->from('user')
            ->where('type = :type', [':type' => 'author'])
            ->where('id > :id AND name <> :name', [':id' => 3, ':name' => 'Marek']);

        $returnValue = '';
        foreach ($query as $row) {
            $returnValue = $row['name'];
        }

        self::assertEquals('SELECT user.* FROM user WHERE type = :type AND id > :id AND name <> :name', $query->getQuery(false));
        self::assertEquals([':type' => 'author', ':id' => 3, ':name' => 'Marek'], $query->getParameters());
        self::assertEquals('Kevin', $returnValue);
    }

    public function testWhereOr()
    {
        $query = $this->fluent->from('comment')
            ->where('comment.id = :id', [':id' => 1])
            ->whereOr('user.id = :userId', [':userId' => 2]);

        self::assertEquals('SELECT comment.* FROM comment LEFT JOIN user ON user.id = comment.user_id WHERE comment.id = :id OR user.id = :userId',
            $query->getQuery(false));
        self::assertEquals([':id' => '1', ':userId' => '2'], $query->getParameters());
    }

    public function testWhereReset()
    {
        $query = $this->fluent->from('user')->where('id > ?', 0)->orderBy('name');
        $query = $query->where(null)->where('name = ?', 'Marek');

        self::assertEquals('SELECT user.* FROM user WHERE name = ? ORDER BY name', $query->getQuery(false));
        self::assertEquals(['0' => 'Marek'], $query->getParameters());
        self::assertEquals(['id' => '1', 'country_id' => '1', 'type' => 'admin', 'name' => 'Marek'], $query->fetch());
    }



    public function testSelectArrayParam()
    {
        $query = $this->fluent
            ->from('user')
            ->select(null)
            ->select(['id', 'name'])
            ->where('id < ?', 2);

        self::assertEquals('SELECT id, name FROM user WHERE id < ?', $query->getQuery(false));
        self::assertEquals(['0' => '2'], $query->getParameters());
        self::assertEquals(['id' => '1', 'name' => 'Marek'], $query->fetch());
    }

    public function testGroupByArrayParam()
    {
        $query = $this->fluent
            ->from('user')
            ->select(null)
            ->select('count(*) AS total_count')
            ->groupBy(['id', 'name']);

        self::assertEquals('SELECT count(*) AS total_count FROM user GROUP BY id,name', $query->getQuery(false));
        self::assertEquals(['total_count' => '1'], $query->fetch());
    }

    public function testCountable()
    {
        $articles = $this->fluent
            ->from('article')
            ->select(null)
            ->select('title')
            ->where('id > 1')
            ->where('id < 4');

        $count = count($articles);

        self::assertEquals(2, $count);
        self::assertEquals([0 => ['title' => 'article 2'], 1 => ['title' => 'article 3']], $articles->fetchAll());
    }

    public function testWhereNotArray()
    {
        $query = $this->fluent->from('article')->where('NOT id', [1, 2]);

        self::assertEquals('SELECT article.* FROM article WHERE NOT id IN (1, 2)', $query->getQuery(false));
    }

    public function testWhereColNameEscaped()
    {
        $query = $this->fluent->from('user')
            ->where('`type` = :type', [':type' => 'author'])
            ->where('`id` > :id AND `name` <> :name', [':id' => 3, ':name' => 'Marek']);

        $rowDisplay = '';
        foreach ($query as $row) {
            $rowDisplay = $row['name'];
        }

        self::assertEquals('SELECT user.* FROM user WHERE `type` = :type AND `id` > :id AND `name` <> :name', $query->getQuery(false));
        self::assertEquals([':type' => 'author', ':id' => '3', ':name' => 'Marek'], $query->getParameters());
        self::assertEquals('Kevin', $rowDisplay);
    }

    public function testAliasesForClausesGroupbyOrderBy()
    {
        $query = $this->fluent->from('article')->group('user_id')->order('id');

        self::assertEquals('SELECT article.* FROM article GROUP BY user_id ORDER BY id', $query->getQuery(false));
    }

    public function testFetch()
    {
        $queryPrint = $this->fluent->from('user', 1)->fetch('name');
        $queryPrint2 = $this->fluent->from('user', 1)->fetch();
        $statement = $this->fluent->from('user', 5)->fetch();
        $statement2 = $this->fluent->from('user', 5)->fetch('name');

        self::assertEquals('Marek', $queryPrint);
        self::assertEquals(['id' => '1', 'country_id' => '1', 'type' => 'admin', 'name' => 'Marek'], $queryPrint2);
        self::assertEquals(false, $statement);
        self::assertEquals(false, $statement2);
    }

    public function testFetchPairsFetchAll()
    {
        $result = $this->fluent->from('user')->fetchPairs('id', 'name');
        $result2 = $this->fluent->from('user')->fetchAll();

        self::assertEquals(['1' => 'Marek', '2' => 'Robert', '3' => 'Chris', '4' => 'Kevin'], $result);
        self::assertEquals([
            0 => ['id' => '1', 'country_id' => '1', 'type' => 'admin', 'name' => 'Marek'],
            1 => ['id' => '2', 'country_id' => '1', 'type' => 'author', 'name' => 'Robert'],
            2 => ['id' => '3', 'country_id' => '2', 'type' => 'admin', 'name' => 'Chris'],
            3 => ['id' => '4', 'country_id' => '2', 'type' => 'author', 'name' => 'Kevin']
        ], $result2);
    }

    public function testFetchAllWithParams()
    {
        $result = $this->fluent->from('user')->fetchAll('id', 'type, name');

        self::assertEquals([1 => ['id' => '1', 'type' => 'admin', 'name' => 'Marek'], 2 => ['id' => '2', 'type' => 'author', 'name' => 'Robert'],
                            3 => ['id' => '3', 'type' => 'admin', 'name' => 'Chris'], 4 => ['id' => '4', 'type' => 'author', 'name' => 'Kevin']],
            $result);
    }

    public function testFetchColumn()
    {
        $printColumn = $this->fluent->from('user', 3)->fetchColumn();
        $printColumn2 = $this->fluent->from('user', 3)->fetchColumn(3);
        $statement = $this->fluent->from('user', 5)->fetchColumn();
        $statement2 = $this->fluent->from('user', 5)->fetchColumn(3);

        self::assertEquals(3, $printColumn);
        self::assertEquals('Chris', $printColumn2);
        self::assertEquals(false, $statement);
        self::assertEquals(false, $statement2);
    }
}