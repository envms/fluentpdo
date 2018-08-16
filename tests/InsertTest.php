<?php

use PHPUnit\Framework\TestCase;
use Envms\FluentPDO\Query;

class InsertTest extends TestCase
{

    protected $fluent;

    public function setUp()
    {
        $pdo = new PDO("mysql:dbname=fluentdb;host=localhost", "vagrant","vagrant");

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        $this->fluent = new Query($pdo);
    }

    public function testInsertStatement()
    {
        $query = $this->fluent->insertInto('article', array(
            'user_id' => 1,
            'title' => 'new title',
            'content' => 'new content'
        ));

        //   $executeReturn = $pdo->query('DELETE FROM article WHERE id > 3')->execute();

        self::assertEquals('INSERT INTO article (user_id, title, content) VALUES (?, ?, ?)', $query->getQuery(false));
        self::assertEquals(['0' => '1', '1' => 'new title', '2' => 'new content'], $query->getParameters());
        //  self::assertEquals('['0' => '1','1' => 'new title', '2' => 'new content'', $query->getParameters());
    }


    /*   public function testInsertUpdate()
       {
           $query = $this->fluent->insertInto('article', array('id' => 1))
               ->onDuplicateKeyUpdate(array(
                   'title' => 'article 1b',
                   'content' => new Envms\FluentPDO\Literal('abs(-1)') // let's update with a literal and a parameter value
               ));

           $q = $this->fluent->from('article', 1)->fetch();

           $query2 = $this->fluent->insertInto('article', array('id' => 1))
               ->onDuplicateKeyUpdate(array(
                   'title' => 'article 1',
                   'content' => 'content 1',
               ))->execute();

           $q2 = $this->fluent->from('article', 1)->fetch();

           $parameters = print_r($query->getParameters());
           $insertStatement = 'last_inserted_id = ' . $query->execute();
           $printParameters = print_r($q);
           $insertStatement2 = "last_inserted_id =". $query2;
           $printParameters2 = print_r($q2);

           self::assertEquals('INSERT INTO article (id) VALUES (?) ON DUPLICATE KEY UPDATE title = ?, content = abs(-1)', $query->getQuery(false));
          // self::assertEquals('Array([0] => 1,[1] => article 1b)', $parameters);
         //  self::assertEquals('last_inserted_id = 1', $insertStatement);
         //  self::assertEquals('Array([id] => 1,[user_id] => 1,[published_at] => 2011-12-10 12:10:00,[title] => article 1b,[content] => 1)', $printParameters);
         //  self::assertEquals('last_inserted_id = 1', $insertStatement2);
         //  self::assertEquals('Array([id] => 1,[user_id] => 1,[published_at] => 2011-12-10 12:10:00,[title] => article 1,[content] => content 1)', $printParameters2);
       }*/

    public function testInsertWithLiteral()
    {
        $query = $this->fluent->insertInto('article',
            array(
                'user_id' => 1,
                'updated_at' => new Envms\FluentPDO\Literal('NOW()'),
                'title' => 'new title',
                'content' => 'new content',
            ));

        self::assertEquals('INSERT INTO article (user_id, updated_at, title, content) VALUES (?, NOW(), ?, ?)', $query->getQuery(false));
        self::assertEquals(['0' => '1','1' => 'new title', '2' => 'new content'], $query->getParameters());
    }

    public function testInsertIgnore()
    {
        $query = $this->fluent->insertInto('article',
            array(
                'user_id' => 1,
                'title' => 'new title',
                'content' => 'new content',
            ))->ignore();

        self::assertEquals('INSERT IGNORE INTO article (user_id, title, content) VALUES (?, ?, ?)', $query->getQuery(false));
        self::assertEquals(['0' => '1', '1' => 'new title', '2' => 'new content'], $query->getParameters());
    }
}