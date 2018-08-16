<?php

use PHPUnit\Framework\TestCase;
use Envms\FluentPDO\Query;

class SelectTest extends TestCase
{
    protected $fluent;

    public function setUp()
    {
        $pdo = new PDO("mysql:dbname=fluentdb;host=localhost", "vagrant","vagrant");

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

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
        self::assertEquals(['id' => '1', 'country_id'=> '1' , 'type' => 'admin' ,'name' => 'Marek'] , $query->fetch());
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

        self::assertEquals("SELECT type, count(id) AS type_count FROM user WHERE id > ? GROUP BY type HAVING type_count > ? ORDER BY name", $query->getQuery(false));
    }

    public function testReturnParameterWithId()
    {
        $query = $this->fluent
            ->from('user', 2);

        self::assertEquals([0=> 2], $query->getParameters());
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
            ->where(array(
                'id'=> 2,
                'type' => 'author'
            ));

        self::assertEquals([ 0 => 2, 1 => 'author'], $query->getParameters());
        self::assertEquals('SELECT user.* FROM user WHERE id = ? AND type = ?', $query->getQuery(false));
    }

    public function testWhereColumnValue()
    {
        $query = $this->fluent->from('user')
            ->where('type', 'author');

        self::assertEquals([0 => 'author'], $query->getParameters());
        self::assertEquals('SELECT user.* FROM user WHERE type = ?', $query->getQuery(false));
    }

    public function testWhereColumnNull()
    {
        $query = $this->fluent
            ->from('user')
            ->where('type', null);

        self::assertEquals('SELECT user.* FROM user WHERE type is NULL', $query->getQuery(false));
    }

    public function testWhereColumnArray()
    {
        $query = $this->fluent
            ->from('user')
            ->where('id', array(1,2,3));

        self::assertEquals('SELECT user.* FROM user WHERE id IN (1, 2, 3)', $query->getQuery(false));
        self::assertEquals([], $query->getParameters());
    }

    public function testWhereColumnName()
    {
        $query = $this->fluent->from('user')
            ->where('type = :type', array(':type' => 'author'))
            ->where('id > :id AND name <> :name', array(':id' => 1, ':name' => 'Marek'));

        $returnValue = '';
        foreach ($query as $row) {
            $returnValue  = $row['name'];
        }

        self::assertEquals('SELECT user.* FROM user WHERE type = :type AND id > :id AND name <> :name', $query->getQuery(false));
        self::assertEquals([':type' => 'author', ':id' => 1 ,':name' => 'Marek'], $query->getParameters());
        self::assertEquals('Robert', $returnValue);
    }
    public function testWhereReset()
    {
        $query = $this->fluent->from('user')->where('id > ?', 0)->orderBy('name');
        $query = $query->where(null)->where('name = ?', 'Marek');

        self::assertEquals('SELECT user.* FROM user WHERE name = ? ORDER BY name', $query->getQuery(false));
        self::assertEquals('Array([0] => Marek)', $query->getParameters());
        self::assertEquals(['id' => '1','country_id' => '1','type' => 'admin','name' => 'Marek'], $query->fetch());
    }

    public function testSelectArrayParam()
    {
        $query = $this->fluent
            ->from('user')
            ->select(null)
            ->select(array('id', 'name'))
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
            ->groupBy(array('id', 'name'));

        self::assertEquals('SELECT count(*) AS total_count FROM user GROUP BY id,name', $query->getQuery(false));
        self::assertEquals(['total_count' => '1'], $query->fetch());
    }

    public function testCountable()
    {
        $articles = $this->fluent
            ->from('article')
            ->select(NULL)
            ->select('title')
            ->where('id > 1');

        $count = count($articles);

        self::assertEquals(2, $count);
        self::assertEquals(['0' => ['title' => 'article 2'], '1' =>['title' => 'article 3']], $articles->fetchAll());
    }

    public function testWhereNotArray()
    {
        $query = $this->fluent->from('article')->where('NOT id', array(1,2));

        self::assertEquals('SELECT article.* FROM article WHERE NOT id IN (1, 2)',  $query->getQuery(false));
    }

    public function testWhereColNameEscaped()
    {
        $query = $this->fluent->from('user')
            ->where('`type` = :type', array(':type' => 'author'))
            ->where('`id` > :id AND `name` <> :name', array(':id' => 1, ':name' => 'Marek'));

        $rowDisplay = '';
        foreach ($query as $row) {
            $rowDisplay = $row['name'];
        }

        self::assertEquals('SELECT user.* FROM user WHERE `type` = :type AND `id` > :id AND `name` <> :name', $query->getQuery(false));
        self::assertEquals([':type' => 'author', ':id' => '1', ':name' => 'Marek'], $query->getParameters());
        self::assertEquals('Robert', $rowDisplay);
    }
}