# FluentPDO

FluentPDO - simple and smart SQL query builder for PDO.

FluentPDO was inspired by very good NotORM library. I use it often but sometimes I need to build a large query *"in classical way"* with many joins and clauses and with full control over generated query string. For this reason I created FluentPDO. *(I also made it for my fun during Christmas holiday 2011;-)*

## Features

- Simple API based on SQL syntax
- Fluent interface
- Smart join builder
- both PDO bind param syntax support (? and :name)
- small and fast - only one file with less then 500 lines
- type hinting with code completion for smart IDEs
- requires PHP 5.1+ with any database supported by PDO

## Tutorial

### Connection

Just copy `FluentPDO.php` file into your `libs/` directory then:

	include "libs/FluentPDO.php";
	$pdo = new PDO("mysql:dbname=fblog", "root");
	$fpdo = new FluentPDO($pdo);
	
### Simple usage

FluentPDO has simple API based on well-known SQL syntax:

	$query = $fpdo->from('article')
				->where('user_id = ?', $user_id)
				->orderBy('published_at DESC');
	
	foreach ($query->execute() as $row) {
		echo "$row[title]\n";
	}

*(function execute() return [PDOStatement](http://www.php.net/manual/en/class.pdostatement.php))*

### Fluent interface

You can build a query step by step:

	$query = $fpdo->from('article')->where('published_at > ?', $date);
	if ($user_id) {
		$query = $query->where('user_id', $user_id);
	}
	if ($order) {
		$query = $query->orderBy($order);
	} else {
		$query = $query->orderBy('published_at');
	}

	
### Smart join builder

You can use "full sql join syntax":

	$query = $fpdo->from('article')->innerJoin('user ON user.id = article.user_id');

It was not so much smart, was it? ;-) If your database uses convention for primary and foreign key names, you can write only:

	$query = $fpdo->from('article')->innerJoin('user');
		
Smarter? May be. As you expected, both commands create same query:

	SELECT article.* FROM article INNER JOIN user ON user.id = article.user_id
	
*note: you can use also `AS`*
	
	$query = $fpdo->from('article')->innerJoin('user AS author');
	
Write colon after joined table for back reference:

	$query = $fpdo->from('user')->innerJoin('article:');
	
#### Best practice how to write joins is don't write any joins ;-)

If you use referenced column in `select(), where(), groupBy() or orderBy()` clauses, you don't need to write any joins manualy. E.g.:

	$query = $fpdo->from('article')->orderBy('user.name');
	// or
	$query = $fpdo->from('article')->select('user.name');
	
both commands add clause `LEFT JOIN user ON user.id = article.user_id` automatically.

References across more tables with dots and colons are also possible:

	$query = $fpdo->from('comment')
		->select('user.name AS comment_author')
		->select('article.user.name AS article_author');
	// or
	$query = $fpdo->from('article')
		->where('comment:user.country = ?', $country_id);

Really smart, isn't it? ;-) *(this syntax was inspired by NotORM.)*

*Note: For more examples see subdirectory tests/*

## API

### SELECT ... FROM ...

Every SELECT query begins with `$fpdo->from($table)` followed by as many clauses as you want.

*syntax*                           | *description*
-----------------------------------|-----------------------------------
`from($table)`                     | set *$table* in **FROM** clause 
`from($table, $id)`                | shortcut for `from($table)->where('id = ?', $id)`
`select($columns[, ...])`          | appends **SELECT** clause with *$column* or any expresion (e.g. `CURDATE() AS today`)
`leftJoin($joinedTable)`           | appends **LEFT JOIN** clause, *$joinedTable* could be "tableName" only or full join statement <br>*("tableName:" means back reference, see **Smart join builder**)*
`innerJoin($joinedTable)`          | appends **INNER JOIN** clause, $joinedTable could be "tableName" only or full join statement <br>*("tableName:" means back reference, see **Smart join builder**)*
`where($condition[, $parameters])` | explained later
`groupBy($columns[, ...])`         | appends **GROUP BY** clause
`having($columns[, ...])`          | appends **HAVING** clause
`orderBy($columns[, ...])`         | appends **ORDER BY** clause
`limit($limit)`	                   | sets **LIMIT** clause
`offset($offset)`	               | sets **OFFSET** clause
`execute()`                        | executes query and return [PDOStatement](http://www.php.net/manual/en/class.pdostatement.php)


You can add clauses `select(), where(), groupBy(), having(), orderBy()`
as many times as you want. All will be appended into query. Clauses `from(), limit(), offset()` rewrite previous setting.	

*Note: If you want to reset a clause (i.e. remove previous defined statement), call any clause with `null`. E.g.:*

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

Every values are automatically escaped.

phew ... this readme was harder to write then the FluentPDO itself ;-)

## Licence

Free for commercial and non-commercial use ([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL](http://www.gnu.org/licenses/gpl-2.0.html)).

*Copyright (c) 2012, Marek Lichtner*




