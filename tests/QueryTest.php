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
        self::assertEquals('[[id => 1],[article_id => 1], [user_id => 2], [content => comment 1.1], [comment_name => Robert],[author_name] => Marek]]', $result);
    }

    public function testFluentUtil() {

        $value =  "'". Envms\FluentPDO\Utilities::toUpperWords('one') . "'";
        $value2 =  "'". Envms\FluentPDO\Utilities::toUpperWords(' one ') . "'";
        $value3 =  "'". Envms\FluentPDO\Utilities::toUpperWords('oneTwo') . "'";
        $value4 =  "'". Envms\FluentPDO\Utilities::toUpperWords('OneTwo') . "'";
        $value5 =  "'". Envms\FluentPDO\Utilities::toUpperWords('oneTwoThree') . "'";
        $value6 =  "'". Envms\FluentPDO\Utilities::toUpperWords(' oneTwoThree ') . "'";

        self::assertEquals('ONE', $value);
        self::assertEquals('ONE', $value2);
        self::assertEquals('ONE TWO', $value3);
        self::assertEquals('ONE TWO', $value4);
        self::assertEquals('ONE TWO THREE', $value5);
        self::assertEquals('ONE TWO THREE', $value6);

    }

    public function testJoinInWhere() {
        $query = $fluent->from('article')->where('comment:content <> "" AND user.country.id = ?', 1);
        $queryPrint= $query->getQuery();

        self::assertEquals('SELECT article.* FROM article LEFT JOIN comment ON comment.article_id = article.id LEFT JOIN user ON user.id = article.user_id
                                    LEFT JOIN country ON country.id = user.country_id WHERE comment.content <> "" AND country.id = ?', $queryPrint);
    }

    public function testJoinInSelect() {
        $query = $fluent->from('article')->select('user.name as author');
        $queryPrint = $query->getQuery();

        self::assertEquals('SELECT article.*, user.name as author FROM article LEFT JOIN user ON user.id = article.user_id', $queryPrint);
    }

    public function testJoinInOrderBy() {
        $query = $fluent->from('article')->orderBy('user.name, article.title');
        $queryPrint = $query->getQuery();

        self::assertEquals('SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id ORDER BY user.name, article.title', $queryPrint);
    }

    public function testJoinInGroupBy() {
        $query = $fluent->from('article')->groupBy('user.type')
            ->select(null)->select('user.type, count(article.id) as article_count');
        $printQuery = $query->getQuery();
        $result = $query->fetchAll();

        self::assertEquals('SELECT user.type, count(article.id) as article_count FROM article
                                    LEFT JOIN user ON user.id = article.user_id GROUP BY user.type', $printQuery);
        self::assertEquals('[[0] => Array ([type] => admin, [article_count] => 2)),[1] => Array ([type] => author, [article_count] => 1)]', $result);
    }

    public function testDontCreateDuplicateJoins() {
        $query = $fluent->from('article')->innerJoin('user AS author ON article.user_id = author.id')
            ->select('author.name');
        $query2 = $fluent->from('article')->innerJoin('user ON article.user_id = user.id')
            ->select('user.name');
        $query3 = $fluent->from('article')->innerJoin('user AS author ON article.user_id = author.id')
            ->select('author.country.name');
        $query4 = $fluent->from('article')->innerJoin('user ON article.user_id = user.id')
            ->select('user.country.name');

        $queryPrint =  $query->getQuery();
        $queryPrint2 = $query2->getQuery();
        $queryPrint3 = $query3->getQuery();
        $queryPrint4 = $query4->getQuery();

        self::assertEquals('SELECT article.*, author.name FROM article INNER JOIN user AS author ON article.user_id = author.id', $queryPrint);
        self::assertEquals('SELECT article.*, user.name FROM articleINNER JOIN user ON article.user_id = user.id', $queryPrint2);
        self::assertEquals('SELECT article.*, country.name FROM article INNER JOIN user AS author ON article.user_id = author.id LEFT JOIN country ON country.id = author.country_id', $queryPrint3);
        self::assertEquals('SELECT article.*, country.name FROM article INNER JOIN user ON article.user_id = user.id LEFT JOIN country ON country.id = user.country_id', $queryPrint4);
    }

    public function testClauseWithRefBeforeJoin() {
        $query = $fluent->from('article')->select('user.name')->innerJoin('user');
        $query2 = $fluent->from('article')->select('author.name')->innerJoin('user as author');
        $query3 = $fluent->from('user')->select('article:title')->innerJoin('article:');

        $printQuery = $query->getQuery();
        $printQuery2 = $query2->getQuery();
        $printQuery3 = $query3->getQuery();

        self::assertEquals('SELECT article.*, user.name FROM article INNER JOIN user ON user.id = article.user_id', $printQuery);
        self:assertEquals('SELECT article.*, author.name FROM article INNER JOIN user AS author ON author.id = article.user_id', $printQuery2);
        self:assertEquals('SELECT user.*, article.title FROM user INNER JOIN article ON article.user_id = user.id', $printQuery3);
    }

    public function testAliasesForClausesGroupbyOrderBy() {
        $query = $fluent->from('article')->group('user_id')->order('id');
        $printQuery = $query->getQuery();

        self::assertEquals('SELECT article.* FROM article GROUP BY user_id ORDER BY id', $printQuery);
    }

    public function testFetch() {
        $queryPrint = $fluent->from('user', 1)->fetch('name');
        $queryPrint2 = $fluent->from('user', 1)->fetch();
        $statement = $fluent->from('user', 3)->fetch();
        $statement2 = $fluent->from('user', 3)->fetch('name');

        self::assertEquals('Marek', $queryPrint);
        self::assertEquals('[id => 1], [country_id => 1], [type => admin], [name => Marek]', $queryPrint2);
        self::assertEquals(false, $statement);
        self::assertEquals(false, $statement2);
    }

    public function testFetchPairsFetchAll() {
        $result = $fluent->from('user')->fetchPairs('id', 'name');
        $result2 = $fluent->from('user')->fetchAll();

        self::assertEquals('([1] => Marek, [2] => Robert)', $result);
        self::assertEquals('([0] => Array ([id] => 1, [country_id] => 1, [type] => admin, [name] => Marek)
                                    [1] => Array ([id] => 2, [country_id] => 1, [type] => author, [name] => Robert))', $result2);
    }

    public function testFetchAllWithParams() {
        $result = $fluent->from('user')->fetchAll('id', 'type, name');

        self::assertEquals('[1] => Array ([id] => 1, [type] => admin, [name] => Marek)
                                    [2] => Array ([id] => 2, [type] => author, [name] => Robert)', $result);
    }

    public function testFromOtherDB() {
        $queryPrint = $fluent->from('db2.user')->order('db2.user.name')->getQuery();

        self::assertEquals('SELECT db2.user.* FROM db2.user ORDER BY db2.user.name', $queryPrint);
    }

    public function testJoinTableWithUsing() {
        $query = $fluent2->from('article')
                ->innerJoin('user USING (user_id)')
                ->select('user.*')
                ->getQuery();

        $query2 = $fluent2->from('article')
                ->innerJoin('user u USING (user_id)')
                ->select('u.*')
                ->getQuery();

        $query3 = $fluent2->from('article')
                ->innerJoin('user AS u USING (user_id)')
                ->select('u.*')
                ->getQuery();

        self::assertEquals('SELECT article.*, user.* FROM article INNER JOIN user USING (user_id)', $query);
        self::assertEquals('SELECT article.*, u.* FROM article INNER JOIN user u USING (user_id)', $query2);
        self::assertEquals('SELECT article.*, u.* FROM article INNER JOIN user AS u USING (user_id)', $query3)''
    }

    public function testFromWithAlias() {
        $query = $fluent->from('user author')->getQuery();
        $query2 = $fluent->from('user AS author')->getQuery();
        $query3 = $fluent->from('user AS author', 1)->getQuery();
        $query4 = $fluent->from('user AS author')->select('country.name')->getQuery();

        self::assertEquals('SELECT author.* FROM user author', $query);
        self::assertEquals('SELECT author.* FROM user AS author', $query2);
        self::assertEquals('SELECT author.* FROM user AS author WHERE author.id = ?', $query3);
        self::assertEquals('SELECT author.*, country.name FROM user AS author LEFT JOIN country ON country.id = user AS author.country_id', $query4);
    }

    public function testInsertStatement() {
        $query = $fluent->insertInto('article', array(
                'user_id' => 1,
                'title' => 'new title',
                'content' => 'new content'
            ));

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());
        $lastInsert = $query->execute();

        $executeReturn = $pdo->query('DELETE FROM article WHERE id > 3')->execute();

        self::assertEquals('INSERT INTO article (user_id, title, content', $printQuery);
        self::assertEquals('VALUES (?, ?, ?)', $parameters);
        self::assertEquals('Array([0] => 1, [1] => new title, [2] => new content', $executeReturn);
    }

    public function testInsertUpdate() {
        $query = $fluent->insertInto('article', array('id' => 1))
            ->onDuplicateKeyUpdate(array(
                'title' => 'article 1b',
                'content' => new Envms\FluentPDO\Literal('abs(-1)') // let's update with a literal and a parameter value
            ));

        $q = $fluent->from('article', 1)->fetch();

        $query2 = $fluent->insertInto('article', array('id' => 1))
            ->onDuplicateKeyUpdate(array(
                'title' => 'article 1',
                'content' => 'content 1',
            ))->execute();

        $q2 = $fluent->from('article', 1)->fetch();

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());
        $insertStatement = 'last_inserted_id = ' . $query->execute();
        $printParameters = print_r($q);
        $insertStatement2 = "last_inserted_id =". $query2;
        $printParameters2 = print_r($q2);

        self::assertEquals('INSERT INTO article (id) VALUES (?) ON DUPLICATE KEY UPDATE title = ?, content = abs(-1)', $printQuery);
        self::assertEquals('Array([0] => 1,[1] => article 1b)', $parameters);
        self::assertEquals('last_inserted_id = 1', $insertStatement);
        self::assertEquals('Array([id] => 1,[user_id] => 1,[published_at] => 2011-12-10 12:10:00,[title] => article 1b,[content] => 1)', $printParameters);
        self::assertEquals('last_inserted_id = 1', $insertStatement2);
        self::assertEquals('Array([id] => 1,[user_id] => 1,[published_at] => 2011-12-10 12:10:00,[title] => article 1,[content] => content 1)', $printParameters2);
    }

    public function testInsertIgnore() {
        $query = $fluent->insertInto('article',
            array(
                'user_id' => 1,
                'title' => 'new title',
                'content' => 'new content',
            ))->ignore();

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('INSERT IGNORE INTO article (user_id, title, content) VALUES (?, ?, ?)', $printQuery);
        self::assertEquals('Array([0] => 1,[1] => new title,[2] => new content)', $parameters);
    }

    public function testInsertWithLiteral() {
        $query = $fluent->insertInto('article',
            array(
                'user_id' => 1,
                'updated_at' => new Envms\FluentPDO\Literal('NOW()'),
                'title' => 'new title',
                'content' => 'new content',
            ));

        $printQuery = $query->getQuery();
        $printParameters = print_r($query->getParameters());

        self::assertEquals('INSERT INTO article (user_id, updated_at, title, content) VALUES (?, NOW(), ?, ?)', $printQuery);
        self::assertEquals('Array([0] => 1,[1] => new title,[2] => new content)', $printParameters);
    }

    public function testDisableSmartJoin() {
        $query = $fluent->from('comment')
            ->select('user.name')
            ->orderBy('article.published_at')
            ->getQuery();
        $printQuery = "-- Plain:\n$query\n\n";

        $query2 = $fluent->from('comment')
            ->select('user.name')
            ->disableSmartJoin()
            ->orderBy('article.published_at')
            ->getQuery();

        $printQuery2 = "-- Disable:\n$query2\n\n";

        $query2 = $fluent->from('comment')
            ->disableSmartJoin()
            ->select('user.name')
            ->enableSmartJoin()
            ->orderBy('article.published_at')
            ->getQuery();
        $printQuery3 = "-- Disable and enable:\n$query3\n\n";

        self::assertEquals('-- Plain: SELECT comment.*, user.name FROM comment LEFT JOIN user ON user.id = comment.user_id LEFT JOIN article ON article.id = comment.article_id ORDER BY article.published_at', $printQuery);
        self::assertEquals('-- Disable: SELECT comment.*, user.name FROM comment ORDER BY article.published_at', $printQuery2);
        self::assertEquals('-- Disable and enable: SELECT comment.*, user.name FROM comment LEFT JOIN user ON user.id = comment.user_id LEFT JOIN article ON article.id = comment.article_id ORDER BY article.published_at', $printQuery2);
    }

    public function testFetchColumn() {
        $printColumn = $fluent->from('user', 1)->fetchColumn();
        $printColumn2 = $fluent->from('user', 1)->fetchColumn(3);
        $statement = $fluent->from('user', 3)->fetchColumn();
        $statement2 = $fluent->from('user', 3)->fetchColumn(3);

        self::assertEquals(1, $printColumn);
        self::assertEquals('Marek', $printColumn2);
        self::assertEquals(false, $statement);
        self::assertEquals(false, $statement2);
    }

    public function testPDOFetchObj(){
        $query = $fluent->from('user')->where('id > ?', 0)->orderBy('name');
        $query = $query->where('name = ?', 'Marek');
        $fluent->getPdo()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $parameters = print_r($query->getParameters());
        $result = print_r($query->fetch());

        self::assertEquals('Array([0] => 0,[1] => Marek)', $parameters);
        self::assertEquals('stdClass Object([id] => 1,[country_id] => 1,[type] => admin,[name] => Marek)', $result);
    }

    public function testUpdate(){
        $query = $fluent->update('country')->set('name', 'aikavolS')->where('id', 1);
        $query->execute();

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        $query2 = $fluent->from('country')->where('id', 1);
        $results = print_r($query2->fetch());

        $fluent->update('country')->set('name', 'Slovakia')->where('id', 1)->execute();

        $query3 = $fluent->from('country')->where('id', 1);
        $printQuery3 = print_r($query3->fetch());

        self::assertEquals('UPDATE country SET name = ? WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => aikavolS,[1] => 1)', $parameters);
        self::assertEquals('Array([0] => aikavolS,[1] => 1)', $results);
        self::assertEquals('Array([id] => 1, [name] => Slovakia)', $printQuery3);
    }

    public function testUpdateLiteral() {
        $query = $fluent->update('article')->set('published_at', new Envms\FluentPDO\Literal('NOW()'))->where('user_id', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('UPDATE article SET published_at = NOW() WHERE user_id = ?', $printQuery);
        self::assertEquals('Array([0] => 1)', $parameters);
    }

    public function testUpdateFromArray() {
        $query = $fluent->update('user')->set(array('name' => 'keraM', '`type`' => 'author'))->where('id', 1);
        $query->execute();

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('UPDATE user SET name = ?, `type` = ? WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => keraM, [1] => author, [2] => 1)', $parameters)
    }

    public function testUpdateLeftJoin() {
        $query = $fluent->update('user')
            ->outerJoin('country ON country.id = user.country_id')
            ->set(array('name' => 'keraM', '`type`' => 'author'))
            ->where('id', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('UPDATE user OUTER JOIN country ON country.id = user.country_id SET name = ?, `type` = ? WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => keraM,[1] => author,[2] => 1)', $parameters);
    }

    public function testUpdateSmartJoin() {
        $query = $fluent->update('user')
            ->set(array('type' => 'author'))
            ->where('country.id', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('UPDATE user LEFT JOIN country ON country.id = user.country_id SET type = ? WHERE country.id = ?', $printQuery);
        self::assertEquals('Array([0] => author,[1] => 1)', $parameters);
    }

    public function testUpdateOrderLimit(){
        $query = $fluent->update('user')
            ->set(array('type' => 'author'))
            ->where('id', 2)
            ->orderBy('name')
            ->limit(1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('UPDATE user SET type = ? WHERE id = ? ORDER BY name LIMIT 1', $printQuery);
        self::assertEquals('Array([0] => author,[1] => 2)', $parameters);
    }

    public function testDelete(){
        $query = $fluent->deleteFrom('user')
            ->where('id', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('DELETE FROM user WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => 1)', $parameters);
    }

    public function testDeleteIgnore(){
        $query = $fluent->deleteFrom('user')
            ->ignore()
            ->where('id', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('DELETE IGNORE FROM user WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => 1)', $parameters);
    }

    public function testDeleteOrderLimit() {
        $query = $fluent->deleteFrom('user')
            ->where('id', 2)
            ->orderBy('name')
            ->limit(1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('DELETE FROM user WHERE id = ? ORDER BY name LIMIT 1', $printQuery);
        self::assertEquals('Array([0] => 2)', $parameters);
    }

    public function testDeleteExpanded(){
        $query = $fluent->delete('t1, t2')
            ->from('t1')
            ->innerJoin('t2 ON t1.id = t2.id')
            ->innerJoin('t3 ON t2.id = t3.id')
            ->where('t1.id', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('DELETE t1, t2 FROM t1 INNER JOIN t2 ON t1.id = t2.id INNER JOIN t3 ON t2.id = t3.id WHERE t1.id = ?', $printQuery);
        self::assertEquals('Array([0] => 1)', $parameters);
    }

    public function testUpdateShortCut() {
        $query = $fluent->update('user', array('type' => 'admin'), 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('UPDATE user SET type = ? WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => admin,[1] => 1)', $parameters);
    }

    public function testDeleteShortcut() {
        $query = $fluent->deleteFrom('user', 1);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('DELETE FROM user WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => 1)', $parameters);
    }

    public function testAddFromAfterDelete(){
        $query = $fluent->delete('user', 1)->from('user');

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());

        self::assertEquals('DELETE user FROM user WHERE id = ?', $printQuery);
        self::assertEquals('Array([0] => 1)', $parameters);
    }

    public function testFromIdAsObject() {
        $query = $fluent->from('user', 2)->asObject();

        $printQuery = $query->getQuery();
        $result = print_r($query->fetch());

        self::assertEquals('SELECT user.* FROM user WHERE user.id = ?',$printQuery);
        self::assertEquals('stdClass Object([id] => 2,[country_id] => 1,[type] => author,[name] => Robert)', $result);
    }

    public function testFromIdAsObjectUser(){
        class User { public $id, $country_id, $type, $name; }
        $query = $fluent->from('user', 2)->asObject('User');

        $printQuery = $query->getQuery();
        $parameters = print_r($query->fetch());

        self::assertEquals('SELECT user.* FROM user WHERE user.id = ?', $printQuery);
        self::assertEquals('User Object([id] => 2,[country_id] => 1,[type] => author,[name] => Robert)', $parameters);
    }

    public function testWhereReset() {
        $query = $fluent->from('user')->where('id > ?', 0)->orderBy('name');
        $query = $query->where(null)->where('name = ?', 'Marek');

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());
        $result = print_r($query->fetch());

        self::assertEquals('SELECT user.* FROM user WHERE name = ? ORDER BY name', $printQuery);
        self::assertEquals('Array([0] => Marek)', $parameters);
        self::assertEquals('Array([id] => 1,[country_id] => 1,[type] => admin,[name] => Marek)', $result);
    }

    public function testUpdateZero(){
        $fluent->update('article')->set('content', '')->where('id', 1)->execute();
        $user = $fluent->from('article')->where('id', 1)->fetch();

        $printQuery = 'ID: ' . $user['id'] . ' - content: ' . $user['content'] ;

        $fluent->update('article')->set('content', 'content 1')->where('id', 1)->execute();

        $user2 = $fluent->from('article')->where('id', 1)->fetch();

        $printQuery2 = 'ID: ' . $user2['id'] . ' - content: ' . $user2['content'];

        self::assertEquals('ID: 1 - content:', $printQuery);
        self::assertEquals('ID: 1 - content: content 1', $printQuery2);
    }

    public function testSelectArrayParam() {
        $query = $fluent
            ->from('user')
            ->select(null)
            ->select(array('id', 'name'))
            ->where('id < ?', 2);

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());
        $result = print_r($query->fetch());

        self::assertEquals('SELECT id, name FROM user WHERE id < ?', $printQuery);
        self::assertEquals('Array([0] => 2)', $parameters);
        self::assertEquals('Array([id] => 1, [name] => Marek)', $result);
    }

    public function testGroupByArrayParam() {
        $query = $fluent
            ->from('user')
            ->select(null)
            ->select('count(*) AS total_count')
            ->groupBy(array('id', 'name'));

        $printQuery = $query->getQuery();
        $result = print_r($query->fetch());

        self::assertEquals('SELECT count(*) AS total_count FROM user GROUP BY id,name', $printQuery);
        self::assertEquals('Array([total_count] => 1)', $result);
    }

    public function testCountable() {
        $articles = $fluent
            ->from('article')
            ->select(NULL)
            ->select('title')
            ->where('id > 1');

        $count = count($articles);
        $result = print_r($articles->fetchAll());

        self::assertEquals(2, $count);
        self::assertEquals('Array ([0] => Array ([title] => article 2)
                                            [1] => Array ([title] => article 3))', $result);
    }

    public function testWhereNotArray() {
        $query = $fluent->from('article')->where('NOT id', array(1,2));

        $printQuery = $query->getQuery();

        self::assertEquals('SELECT article.* FROM article WHERE NOT id IN (1, 2)', $printQuery);
    }

    public function testWhereColNameEscaped() {
        $query = $fluent->from('user')
            ->where('`type` = :type', array(':type' => 'author'))
            ->where('`id` > :id AND `name` <> :name', array(':id' => 1, ':name' => 'Marek'));

        $printQuery = $query->getQuery();
        $parameters = print_r($query->getParameters());
        $rowDisplay = '';
        foreach ($query as $row) {
            $rowDisplay = $row['name'];
        }

        self::assertEquals('SELECT user.* FROM user WHERE `type` = :type AND `id` > :id AND `name` <> :name', $printQuery);
        self::assertEquals('Array([:type] => author,[:id] => 1,[:name] => Marek)', $parameters);
        self::assertEquals('Robert', $rowDisplay);
    }

    public function testUpdateWhere() {
        $query = $fluent->update('users')
            ->set("`users`.`active`", 1)
            ->where("`country`.`name`", 'Slovakia')
            ->where("`users`.`name`", 'Marek');

        $printQuery = $query->getQuery() . "\n";
        $parameters = print_r($query->getParameters());

        $query2 = $fluent->update('users')
            ->set("[users].[active]", 1)
            ->where("[country].[name]", 'Slovakia')
            ->where("[users].[name]", 'Marek');

        $printQuery2 = $query2->getQuery() . "\n";
        $parameters2 = print_r($query2->getParameters());

        self::assertEquals('UPDATE users LEFT JOIN country ON country.id = users.country_id SET `users`.`active` = ? WHERE `country`.`name` = ? AND `users`.`name` = ?', $printQuery);
        self::assertEquals('Array([0] => 1,[1] => Slovakia,[2] => Marek)', $parameters);
        self::assertEquals('UPDATE users LEFT JOIN country ON country.id = users.country_id SET [users].[active] = ? WHERE [country].[name] = ? AND [users].[name] = ?', $printQuery2);
        self::assertEquals('Array([0] => 1,[1] => Slovakia,[2] => Marek)', $parameters2);
    }
}