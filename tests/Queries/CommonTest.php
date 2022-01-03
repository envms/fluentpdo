<?php

require __DIR__ . '/../_resources/init.php';

use PHPUnit\Framework\TestCase;
use Envms\FluentTest\Model\User;
use Envms\FluentPDO\Query;

/**
 * Class CommonTest
 *
 * @covers \Envms\FluentPDO\Queries\Common
 */
class CommonTest extends TestCase
{

    /** @var Query */
    protected $fluent;

    public function setUp(): void
    {
        global $pdo;

        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_BOTH);

        $this->fluent = new Query($pdo);
    }

    public function testFullJoin()
    {
        $query = $this->fluent->from('article')
            ->select('user.name')
            ->leftJoin('user ON user.id = article.user_id')
            ->orderBy('article.title');

        $returnValue = '';
        foreach ($query as $row) {
            $returnValue .= "$row[name] - $row[title] ";
        }

        self::assertEquals('SELECT article.*, user.name FROM article LEFT JOIN user ON user.id = article.user_id ORDER BY article.title',
            $query->getQuery(false));
        self::assertEquals('Marek - article 1 Robert - article 2 Marek - article 3 Kevin - artïcle 4 Chris - article 5 Chris - სარედაქციო 6 ', $returnValue);
    }

    public function testShortJoin()
    {

        $query = $this->fluent->from('article')->leftJoin('user');
        $query2 = $this->fluent->from('article')->leftJoin('user author');
        $query3 = $this->fluent->from('article')->leftJoin('user AS author');

        self::assertEquals('SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id', $query->getQuery(false));
        self::assertEquals('SELECT article.* FROM article LEFT JOIN user AS author ON author.id = article.user_id', $query2->getQuery(false));
        self::assertEquals('SELECT article.* FROM article LEFT JOIN user AS author ON author.id = article.user_id', $query3->getQuery(false));
    }

    public function testJoinShortBackRef()
    {
        $query = $this->fluent->from('user')->innerJoin('article:');
        $query2 = $this->fluent->from('user')->innerJoin('article: with_articles');
        $query3 = $this->fluent->from('user')->innerJoin('article: AS with_articles');

        self::assertEquals('SELECT user.* FROM user INNER JOIN article ON article.user_id = user.id', $query->getQuery(false));
        self::assertEquals('SELECT user.* FROM user INNER JOIN article AS with_articles ON with_articles.user_id = user.id',
            $query2->getQuery(false));
        self::assertEquals('SELECT user.* FROM user INNER JOIN article AS with_articles ON with_articles.user_id = user.id',
            $query3->getQuery(false));
    }

    public function testJoinShortMulti()
    {
        $query = $this->fluent->from('comment')
            ->leftJoin('article.user');

        self::assertEquals('SELECT comment.* FROM comment LEFT JOIN article ON article.id = comment.article_id  LEFT JOIN user ON user.id = article.user_id',
            $query->getQuery(false));
    }

    public function testJoinMultiBackRef()
    {
        $query = $this->fluent->from('article')
            ->innerJoin('comment:user AS comment_user');

        self::assertEquals('SELECT article.* FROM article INNER JOIN comment ON comment.article_id = article.id  INNER JOIN user AS comment_user ON comment_user.id = comment.user_id',
            $query->getQuery(false));
        self::assertEquals(['id' => '1', 'user_id' => '1', 'published_at' => '2011-12-10 12:10:00', 'title' => 'article 1', 'content' => 'content 1'],
            $query->fetch());
    }

    public function testJoinShortTwoSameTable()
    {
        $query = $this->fluent->from('article')
            ->leftJoin('user')
            ->leftJoin('user');

        self::assertEquals('SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id', $query->getQuery(false));
    }

    public function testJoinShortTwoTables()
    {
        $query = $this->fluent->from('comment')
            ->where('comment.id', 2)
            ->leftJoin('user comment_author')->select('comment_author.name AS comment_name')
            ->leftJoin('article.user AS article_author')->select('article_author.name AS author_name');

        self::assertEquals('SELECT comment.*, comment_author.name AS comment_name, article_author.name AS author_name FROM comment LEFT JOIN user AS comment_author ON comment_author.id = comment.user_id  LEFT JOIN article ON article.id = comment.article_id  LEFT JOIN user AS article_author ON article_author.id = article.user_id WHERE comment.id = ?',
            $query->getQuery(false));
        self::assertEquals([
            'id'           => '2',
            'article_id'   => '1',
            'user_id'      => '2',
            'content'      => 'comment 1.2',
            'comment_name' => 'Robert',
            'author_name'  => 'Marek'
        ], $query->fetch());
    }

    public function testJoinInWhere()
    {
        $query = $this->fluent->from('article')->where('comment:content <> "" AND user.country.id = ?', 1);

        self::assertEquals('SELECT article.* FROM article LEFT JOIN comment ON comment.article_id = article.id  LEFT JOIN user ON user.id = article.user_id  LEFT JOIN country ON country.id = user.country_id WHERE comment.content <> "" AND country.id = ?',
            $query->getQuery(false));
    }

    public function testJoinInSelect()
    {
        $query = $this->fluent->from('article')->select('user.name AS author');

        self::assertEquals('SELECT article.*, user.name AS author FROM article LEFT JOIN user ON user.id = article.user_id', $query->getQuery(false));
    }

    public function testJoinInOrderBy()
    {
        $query = $this->fluent->from('article')->orderBy('user.name, article.title');

        self::assertEquals('SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id ORDER BY user.name, article.title',
            $query->getQuery(false));
    }

    public function testJoinInGroupBy()
    {
        $query = $this->fluent->from('article')->groupBy('user.type')
            ->select(null)->select('user.type, count(article.id) AS article_count');

        self::assertEquals('SELECT user.type, count(article.id) AS article_count FROM article LEFT JOIN user ON user.id = article.user_id GROUP BY user.type',
            $query->getQuery(false));
        self::assertEquals(['0' => ['type' => 'admin', 'article_count' => '4'], '1' => ['type' => 'author', 'article_count' => '2']],
            $query->fetchAll());
    }

    public function testEscapeJoin()
    {
        $query = $this->fluent->from('article')
            ->where('user\.name = ?', 'Chris');

        self::assertEquals('SELECT article.* FROM article WHERE user.name = ?', $query->getQuery(false));

        $query = $this->fluent->from('article')
            ->where('comment.id = :id', 1)
            ->where('user\.name = :name', 'Chris');

        self::assertEquals('SELECT article.* FROM article LEFT JOIN comment ON comment.id = article.comment_id WHERE comment.id = :id AND user.name = :name',
            $query->getQuery(false));
    }

    public function testDontCreateDuplicateJoins()
    {
        $query = $this->fluent->from('article')
            ->innerJoin('user AS author ON article.user_id = author.id')
            ->select('author.name');

        $query2 = $this->fluent->from('article')
            ->innerJoin('user ON article.user_id = user.id')
            ->select('user.name');

        $query3 = $this->fluent->from('article')
            ->innerJoin('user AS author ON article.user_id = author.id')
            ->select('author.country.name');

        $query4 = $this->fluent->from('article')
            ->innerJoin('user ON article.user_id = user.id')
            ->select('user.country.name');

        self::assertEquals('SELECT article.*, author.name FROM article INNER JOIN user AS author ON article.user_id = author.id',
            $query->getQuery(false));
        self::assertEquals('SELECT article.*, user.name FROM article INNER JOIN user ON article.user_id = user.id', $query2->getQuery(false));
        self::assertEquals('SELECT article.*, country.name FROM article INNER JOIN user AS author ON article.user_id = author.id  LEFT JOIN country ON country.id = author.country_id',
            $query3->getQuery(false));
        self::assertEquals('SELECT article.*, country.name FROM article INNER JOIN user ON article.user_id = user.id  LEFT JOIN country ON country.id = user.country_id',
            $query4->getQuery(false));
    }

    public function testClauseWithRefBeforeJoin()
    {
        $query = $this->fluent->from('article')->select('user.name')->innerJoin('user');
        $query2 = $this->fluent->from('article')->select('author.name')->innerJoin('user AS author');
        $query3 = $this->fluent->from('user')->select('article:title')->innerJoin('article:');

        self::assertEquals('SELECT article.*, user.name FROM article INNER JOIN user ON user.id = article.user_id', $query->getQuery(false));
        self::assertEquals('SELECT article.*, author.name FROM article INNER JOIN user AS author ON author.id = article.user_id',
            $query2->getQuery(false));
        self::assertEquals('SELECT user.*, article.title FROM user INNER JOIN article ON article.user_id = user.id', $query3->getQuery(false));
    }

    public function testFromOtherDB()
    {
        $queryPrint = $this->fluent->from('db2.user')->where('db2.user.name', 'name')->order('db2.user.name')->getQuery(false);

        self::assertEquals('SELECT db2.user.* FROM db2.user WHERE db2.user.name = ? ORDER BY db2.user.name', $queryPrint);
    }

    public function testJoinTableWithUsing()
    {
        $query = $this->fluent->from('article')
            ->innerJoin('user USING (user_id)')
            ->select('user.*')
            ->getQuery(false);

        $query2 = $this->fluent->from('article')
            ->innerJoin('user u USING (user_id)')
            ->select('u.*')
            ->getQuery(false);

        $query3 = $this->fluent->from('article')
            ->innerJoin('user AS u USING (user_id)')
            ->select('u.*')
            ->getQuery(false);

        self::assertEquals('SELECT article.*, user.* FROM article INNER JOIN user USING (user_id)', $query);
        self::assertEquals('SELECT article.*, u.* FROM article INNER JOIN user u USING (user_id)', $query2);
        self::assertEquals('SELECT article.*, u.* FROM article INNER JOIN user AS u USING (user_id)', $query3);
    }

    public function testDisableSmartJoin()
    {
        $query = $this->fluent->from('comment')
            ->select('user.name')
            ->orderBy('article.published_at')
            ->getQuery(false);
        $printQuery = "-- Plain: $query";

        $query2 = $this->fluent->from('comment')
            ->select('user.name')
            ->disableSmartJoin()
            ->orderBy('article.published_at')
            ->getQuery(false);

        $printQuery2 = "-- Disable: $query2";

        $query3 = $this->fluent->from('comment')
            ->disableSmartJoin()
            ->select('user.name')
            ->enableSmartJoin()
            ->orderBy('article.published_at')
            ->getQuery(false);
        $printQuery3 = "-- Disable and enable: $query3";

        self::assertEquals('-- Plain: SELECT comment.*, user.name FROM comment LEFT JOIN user ON user.id = comment.user_id  LEFT JOIN article ON article.id = comment.article_id ORDER BY article.published_at',
            $printQuery);
        self::assertEquals('-- Disable: SELECT comment.*, user.name FROM comment ORDER BY article.published_at', $printQuery2);
        self::assertEquals('-- Disable and enable: SELECT comment.*, user.name FROM comment LEFT JOIN user ON user.id = comment.user_id  LEFT JOIN article ON article.id = comment.article_id ORDER BY article.published_at',
            $printQuery3);
    }

    public function testPDOFetchObj()
    {
        $query = $this->fluent->from('user')->where('id > ?', 0)->orderBy('name');
        $query = $query->where('name = ?', 'Marek');
        $this->fluent->getPdo()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $expectObj = new stdClass();
        $expectObj->id = 1;
        $expectObj->country_id = 1;
        $expectObj->type = 'admin';
        $expectObj->name = 'Marek';

        self::assertEquals(['0' => '0', '1' => 'Marek'], $query->getParameters());
        self::assertEquals($expectObj, $query->fetch());
    }

    public function testFromIdAsObject()
    {
        $query = $this->fluent->from('user', 2)->asObject();

        $expectObj = new stdClass();
        $expectObj->id = 2;
        $expectObj->country_id = 1;
        $expectObj->type = 'author';
        $expectObj->name = 'Robert';

        self::assertEquals('SELECT user.* FROM user WHERE user.id = ?', $query->getQuery(false));
        self::assertEquals($expectObj, $query->fetch());
    }

    public function testFromIdAsObjectUser()
    {
        $expectedUser = new User();
        $expectedUser->id = 2;
        $expectedUser->country_id = 1;
        $expectedUser->type = 'author';
        $expectedUser->name = 'Robert';

        $query = $this->fluent->from('user', 2)->asObject(User::class);
        $user = $query->fetch();

        self::assertEquals('SELECT user.* FROM user WHERE user.id = ?', $query->getQuery(false));
        self::assertEquals($expectedUser, $user);
    }

}
