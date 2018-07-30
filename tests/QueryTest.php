<?php

use PHPUnit\Framework\TestCase;
include "connect.inc.php";

class QueryTest extends TestCase {

    public function testBasicQuery() {
        $query = $fluent
            ->from('user')
            ->where('id > ?', 0)
            ->orderBy('name')
            ->where('name = ?', 'Marek');

        $queryPrint = $query->getQuery();
        $result = $query->fetch();
        $parameters = $query->getParameters();

        self::assertEquals('SELECT user.* FROM userWHERE id > ?AND name = ? ORDER BY name', $queryPrint);
        self::assertEquals([['id'=> 1],['country_id'=> 1], ["type"=> 'admin'],["name" => 'Mark']], $result);
        self::assertEquals([[0 => 0],[1 => 'Marek']], $parameters);
    }

    public function testReturnQueryWithHaving(){
        $query = $fluent
            ->from('user')
            ->select(null)
            ->select('type, count(id) AS type_count')
            ->where('id > ?', 1)
            ->groupBy('type')
            ->having('type_count > ?', 1)
            ->orderBy('name');

        $queryPrint = $query->getQuery();

        self::assertEquals("SELECT type, count(id) AS type_count FROM user WHERE id > ? GROUP BY type HAVING type_count > ? ORDER BY name", $queryPrint);
    }

    public function testReturnParameterWithId() {
        $query = $fluent
            ->from('user', 2);

        $parameters = $query->getParameters();
        $queryPrint = $query->getQuery();

        self::assertEquals([[0=> 2]], $parameters);
        self::assertEquals('SELECT user.* FROM user WHERE user.id = ?', $queryPrint);
    }

    public function testWhereArrayParameter() {
        $query = $fluent
            ->from('user')
            ->where(array(
                'id'=> 2,
                'type' => 'author'
            ));

        $parameters = $query->getParameters();
        $queryPrint = $query->getQuery();

        self::assertEquals([[0=>2],[1 => 'author']], $parameters);
        self::assertEquals('SELECT user.* FROM user WHERE id = ? AND type = ?', $queryPrint);
    }

    public function testWhereColumnValue() {
        $query = $fluent->from('user')
            ->where('type', 'author');

       $queryPrint = $query->getQuery();
       $parameters = $query->getParameters();

       self::assertEquals([[1 => 'author']], $parameters);
       self::assertEquals('SELECT user.* FROM user WHERE type = ?', $queryPrint);
    }

    public function testWhereColumnNull(){
        $query = $fluent
            ->from('user')
            ->where('type', null);

        $queryPrint = $query->getQuery();

        self::assertEquals('SELECT user.* FROM user WHERE type is NULL', $queryPrint);
    }

    public function testWhereColumnArray() {
        $query = $fluent
            ->from('user')
            ->where('id', array(1,2,3));

        $queryPrint = $query->getQuery();
        $parameters = $query->getParameters());

        self::assertEquals('SELECT user.* FROM user WHERE id IN (1, 2, 3)', $queryPrint);
        self::assertEquals([], $parameters);
    }

    public function testWhereColumnName() {
        $query = $fluent->from('user')
            ->where('type = :type', array(':type' => 'author'))
            ->where('id > :id AND name <> :name', array(':id' => 1, ':name' => 'Marek'));

        $queryPrint = $query->getQuery();
        $parameters = $query->getParameters();
        $returnValue = '';
        foreach ($query as $row) {
            $returnValue  = $row['name'];
        }

        self::assertEquals('SELECT user.* FROM user WHERE type = :type AND id > :id AND name <> :name', $queryPrint);
        self::assertEquals([[type => author],[id => 1 ],[name => Marek]], $parameters);
        self::assertEquals('Robert', $returnValue);
    }

    public function testFullJoin() {
        $query = $fluent->from('article')
            ->select('user.name')
            ->leftJoin('user ON user.id = article.user_id')
            ->orderBy('article.title');

        $queryPrint = $query->getQuery();
        $returnValue = '';
        foreach ($query as $row) {
            $returnValue .= "$row[name] - $row[title] ";
        }

        self::assertEquals('SELECT article.*, user.name FROM article LEFT JOIN user ON user.id = article.user_id ORDER BY article.title', $queryPrint);
        self::assertEquals('Marek - article 1 Robert - article 2 Marek - article 3', $returnValue);
    }

    public function testShortJoin() {

        $query = $fluent->from('article')->leftJoin('user');
        $query2 = $fluent->from('article')->leftJoin('user author');
        $query3 = $fluent->from('article')->leftJoin('user AS author');

        $printQuery = $query->getQuery();
        $printQuery1 = $query2->getQuery();
        $printQuery2 = $query3->getQuery();

        self::assertEquals('SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id', $printQuery);
        self::assertEquals('SELECT article.* FROM article LEFT JOIN user AS author ON author.id = article.user_id', $printQuery2);
        self::assertEquals('SELECT article.* FROM article LEFT JOIN user AS author ON author.id = article.user_id', $printQuery3);
    }

    public function testJoinShortBackRef() {
        $query = $fluent->from('user')->innerJoin('article:');
        $query2 = $fluent->from('user')->innerJoin('article: with_articles');
        $query3 = $fluent->from('user')->innerJoin('article: AS with_articles');

        $printQuery = $query->getQuery();
        $printQuery2 = $query2->getQuery();
        $printQuery3 = $query3->getQuery();

        self::assertEquals('SELECT user.* FROM user INNER JOIN article ON article.user_id = user.id', $printQuery);
        self::assertEquals('SELECT user.* FROM user INNER JOIN article AS with_articles ON with_articles.user_id = user.id', $printQuery2);
        self::assertEquals('SELECT user.* FROM user INNER JOIN article AS with_articles ON with_articles.user_id = user.id', $printQuery3);
    }

    public function testJoinShortMulti() {
        $query = $fluent->from('comment')
            ->leftJoin('article.user');

        $printQuery = $query->getQuery();
        $query = $fluent->from('article')->innerJoin('comment:user AS comment_user');
        echo $query->getQuery() . "\n";
        print_r($query->fetch());    }

    public function testJoinMultiBackRef() {
        $query = $fluent->from('article')
            ->innerJoin('comment:user AS comment_user');

        $queryPrint = $query->getQuery();
        $result = $query->fetch();

        self::assertEquals('SELECT article.* FROM article INNER JOIN comment ON comment.article_id = article.id INNER JOIN user AS comment_user ON comment_user.id = comment.user_id', $printQuery);
        self::assertEquals('[[id => 1], [user_id => 1], [published_at => 2011-12-10 12:10:00], [title => article 1], [content => content 1]', $result);
    }

    public function testJoinShortTwoSameTable() {
        $query = $fluent->from('article')
            ->leftJoin('user')
            ->leftJoin('user');
        $queryPrint = $query->getQuery();

        self::assertEquals('SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id', $queryPrint);
    }

    public function testJoinShortTwoTables() {
        $query = $fluent->from('comment')
            ->where('comment.id', 1)
            ->leftJoin('user comment_author')->select('comment_author.name AS comment_name')
            ->leftJoin('article.user AS article_author')->select('article_author.name AS author_name');

        $queryPrint = $query->getQuery() . "\n";
        $result = $query->fetch();

        self::assertEquals('SELECT comment.*, comment_author.name AS comment_name, article_author.name AS author_name
                                    FROM comment LEFT JOIN user AS comment_author ON comment_author.id = comment.user_id LEFT JOIN article ON article.id = comment.article_id
                                    LEFT JOIN user AS article_author ON article_author.id = article.user_id WHERE comment.id = ?', $queryPrint);
        self::assertEquals( [[id => 1],[article_id => 1], [user_id => 2], [content => comment 1.1], [comment_name => Robert],[author_name] => Marek]], $result);
    }

    public function testFluentUtil() {

        $value =  "'". Envms\FluentPDO\Utilities::toUpperWords('one') . "'";
        $value2 =  "'". Envms\FluentPDO\Utilities::toUpperWords(' one ') . "'";
        $value3 =  "'". Envms\FluentPDO\Utilities::toUpperWords('oneTwo') . "'";
        $value4 =  "'". Envms\FluentPDO\Utilities::toUpperWords('OneTwo') . "'";
        $value5 =  "'". Envms\FluentPDO\Utilities::toUpperWords('oneTwoThree') . "'";
        $value5 =  "'". Envms\FluentPDO\Utilities::toUpperWords(' oneTwoThree ') . "'";

        self::assertEquals('ONE', $value);
        self::assertEquals('ONE', $value);
        self::assertEquals('ONE', $value);
        self::assertEquals('ONE', $value);
        self::assertEquals('ONE', $value);

    }

}