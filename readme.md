# FluentPDO

FluentPDO - simple and smart SQL query builder for PDO.

With FluentPDO you can build simple and mainly difficult queries quickly and effectively. Killer feature of FluentPDO is *"Smart join builder"* which is able generate joins automatically. FluentPDO is a perfect choice for small projects. If you are not *"in-doctrine-ated"* ;-) you can use FluentPDO also for large projects as a base of your models or repositories.

## Features

- Fluent interface for creating queries step by step
- Smart join builder
- Simple API based on PDO and SQL syntax
- small and fast - only one file with less then 500 lines
- type hinting with code completion for smart IDEs
- requires PHP 5.1+ with any database supported by PDO

## Tutorial

### Connection

Just copy `FluentPDO.php` file into your `libs/` directory then:

	include "libs/FluentPDO.php";
	$pdo = new PDO("mysql:dbname=fblog", "root");
	$fpdo = new FluentPDO($pdo);
	
### Simple usage with fluent interface

FluentPDO has simple API based on well-known SQL syntax:

	$query = $fpdo->from('article')
				->where('published_at > ?', $date)
				->orderBy('published_at DESC')
				->limit(5);
	if ($user_id) {
		$query = $query->where('user_id', $user_id);
	}
	foreach ($query->execute() as $row) {
		echo "$row[title]\n";
	}

*(function execute() return [PDOStatement](http://www.php.net/manual/en/class.pdostatement.php))*

## Smart join builder

You can use "full sql join syntax":

	$query = $fpdo->from('article')->innerJoin('user ON user.id = article.user_id');

It was not so much smart, was it? ;-) If your database uses convention for primary and foreign key names, you can write only:

	$query = $fpdo->from('article')->innerJoin('user');
		
Smarter? May be. As you expected, both commands create same query:

	SELECT article.* FROM article INNER JOIN user ON user.id = article.user_id
	
you can use also `AS`
	
	$query = $fpdo->from('article')->innerJoin('user AS author');
	
### Colon after joined table means back reference

	$query = $fpdo->from('user')->innerJoin('article:');
	
then result is:
	
	SELECT user.* FROM user INNER JOIN article ON article.user_id = user.id
	
### Best practice how to write joins is not to write any joins ;-)

If you use referenced column in `select(), where(), groupBy() or orderBy()` clauses, you don't need to write any joins manualy. E.g.:

	$query = $fpdo->from('article')->orderBy('user.name');
	
this command adds join clause automatically.

	SELECT article.* FROM article LEFT JOIN user ON user.id = article.user_id ORDER BY user.name

References across more tables with dots and colons are possible as well:

	$query = $fpdo->from('article')
		->select('comment:user.name AS comment_author')
		->leftJoin('user AS article_author')
			->select('article_author.name')
			->where('article_author.country.name = ?',  $country);
		
then result is:

	SELECT article.*, user.name AS comment_author, article_author.name 
	FROM article 
    	LEFT JOIN user AS article_author ON article_author.id = article.user_id
    	LEFT JOIN country ON country.id = article_author.country_id
   		LEFT JOIN comment ON comment.article_id = article.id
    	LEFT JOIN user ON user.id = comment.user_id 
	WHERE country.name = ?


Really smart, isn't it? ;-)

*For more examples see subdirectory tests/*

## API

### SELECT ... FROM ...

Every SELECT query begins with `$fpdo->from($table)` followed by as many clauses as you want.

*syntax*                           | *description*
-----------------------------------|-----------------------------------
`from($table)`                     | set *$table* in **FROM** clause 
`from($table, $id)`                | shortcut for `from($table)->where('id = ?', $id)`
`select($columns[, ...])`          | appends **SELECT** clause with *$column* or any expresion (e.g. `CURDATE() AS today`)
`leftJoin($joinedTable)`<br>`innerJoin($joinedTable)` | appends **LEFT JOIN** or **INNER JOIN** clause,<br>*$joinedTable* could be "tableName" only or full join statement <br>*("tableName:" colon means back reference, see **Smart join builder**)*
`where($condition[, $parameters])` | explained later
`groupBy($columns[, ...])`         | appends **GROUP BY** clause
`having($columns[, ...])`          | appends **HAVING** clause
`orderBy($columns[, ...])`         | appends **ORDER BY** clause
`limit($limit)`	                   | sets **LIMIT** clause
`offset($offset)`	               | sets **OFFSET** clause
`execute()`                        | executes query and return [PDOStatement](http://www.php.net/manual/en/class.pdostatement.php)
`fetch($column = '')`              | fetch first row or column only from first row
`fetchPairs($key, $value)`         | fetch pairs
`fetchAll()`                       | fetch all rows


You can add clauses `select(), where(), groupBy(), having(), orderBy()`
as many times as you want. Everything will be appended into query. Clauses `from(), limit(), offset()` rewrite previous setting.

*If you want to reset a clause (i.e. remove previous defined statements), call any clause with `null`. E.g.:*

	$query = $query->where(null);   // remove all prev defined where() clauses
	$query = $query->orderBy(null); // remove all prev defined orderBy() clauses
	$query = $query->select(null)->select('id'); # set "SELECT id FROM ...."
	
### WHERE clause

Repetitive calls of `where()` are connected with `AND`. The `where()` *$condition* can contain ? or :name which is [bound by PDO](http://www.php.net/manual/en/pdostatement.execute.php) (so no manual escaping is required). If the question mark and colon are missing in `where()` $condition then the behavior is:

*syntax*                                  | *description*
------------------------------------------|-----------------------------------
`$table->where("field", "x")`             | Translated to `field = 'x'`
`$table->where("field", null)`            | Translated to `field IS NULL`
`$table->where("field", array("x", "y"))` | Translated to `field IN ('x', 'y')`
`$table->where(null)`                     | beware, `where(null)` reset clause and remove all prev defined conditions
`$table->where("field > ?", "x")`                       | bound by PDO
`$table->where("field > :name", array(':name' => 'x'))` | bound by PDO
`$table->where(array("field1" => "value1", ...))`       | Translated to `field1 = 'value1' AND ...`

Every value is automatically escaped.

*Syntax of this library was inspired by NotORM library.*

## Licence

Free for commercial and non-commercial use ([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL](http://www.gnu.org/licenses/gpl-2.0.html)).

*Copyright (c) 2012, Marek Lichtner*

