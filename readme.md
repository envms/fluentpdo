# FluentPDO [![Build Status](https://secure.travis-ci.org/envms/fluentpdo.png?branch=master)](http://travis-ci.org/envms/fluentpdo) [![Code Climate](https://codeclimate.com/github/fpdo/fluentpdo/badges/gpa.svg)](https://codeclimate.com/github/fpdo/fluentpdo)

FluentPDO is a quick and light PHP library for rapid query building. It features a smart join builder, which automatically creates table joins.

## Features

- Easy interface for creating queries step by step
- Support for any database compatible with PDO
- Simple API based on PDO and SQL syntax
- Ability to build complex SELECT, INSERT, UPDATE & DELETE queries with little code
- Small and very fast
- Type hinting for magic methods with code completion in smart IDEs

## Requirements

The latest release of FluentPDO requires at least PHP 5.4, and supports up to PHP 7.2

## Reference

[Sitepoint - Getting Started with FluentPDO](http://www.sitepoint.com/getting-started-fluentpdo/)

## Install

### Composer

The preferred way to install FluentPDO is via [composer](http://getcomposer.org/). v1.1.x will be the last until the release of 2.0, so we recommend using 1.1.* to ensure no breaking changes are introduced.

Add the following line in your `composer.json` file:

	"require": {
		...
		"envms/fluentpdo": "1.1.*"
	}

update your dependencies with `composer update`, and you're done!

### Copy

If you prefer not to use composer, simply copy the `/FluentPDO` directory into your libraries directory and add:

```php
include "[your-library-directory]/FluentPDO/FluentPDO.php";
```

to the top of your application.

## Getting Started

Create a new PDO instance, and pass the instance to FluentPDO:

```php
$pdo = new PDO("mysql:dbname=fluentdb", "root");
$fpdo = new FluentPDO($pdo);
```

Then, creating queries is quick and easy:

```php
$query = $fpdo->from('article')
            ->where('published_at > ?', $date)
            ->orderBy('published_at DESC')
            ->limit(5);
```

which builds the query below:

```mysql
SELECT article.*
FROM article
WHERE published_at > ?
ORDER BY published_at DESC
LIMIT 5
```

To get data from the select, all we do is loop through the returned array:

```php
foreach ($query as $row) {
    echo "$row[title]\n";
}
```

## Using the Smart Join Builder

Let's start with a traditional join, below:

```php
$query = $fpdo->from('article')
            ->leftJoin('user ON user.id = article.user_id')
            ->select('user.name');
```

That's pretty verbose, and not very smart. If your tables use proper primary and foreign key names, you can shorten the above to:

```php
$query = $fpdo->from('article')
            ->leftJoin('user')
            ->select('user.name');
```

That's better, but not ideal. However, it would be even easier to **not write any joins**:

```php
$query = $fpdo->from('article')
            ->select('user.name');
```

Awesome, right? FluentPDO is able to build the join for you, by you prepending the foreign table name to the requested column.

All three snippets above will create the exact same query:

```mysql
SELECT article.*, user.name 
FROM article 
LEFT JOIN user ON user.id = article.user_id
```

## CRUD Query Examples

##### SELECT

```php
$query = $fpdo->from('article')->where('id', 1);
$query = $fpdo->from('user', 1); // shorter version if selecting one row by primary key
```

##### INSERT

```php
$values = array('title' => 'article 1', 'content' => 'content 1');

$query = $fpdo->insertInto('article')->values($values)->execute();
$query = $fpdo->insertInto('article', $values)->execute(); // shorter version
```

##### UPDATE

```php
$set = array('published_at' => new FluentLiteral('NOW()'));

$query = $fpdo->update('article')->set($set)->where('id', 1)->execute();
$query = $fpdo->update('article', $set, 1)->execute(); // shorter version if updating one row by primary key
```

##### DELETE

```php
$query = $fpdo->deleteFrom('article')->where('id', 1)->execute();
$query = $fpdo->deleteFrom('article', 1)->execute(); // shorter version if deleting one row by primary key
```

***Note**: INSERT, UPDATE and DELETE queries will only run after you call `->execute()`*

Full documentation can be found on the [FluentPDO homepage](http://envms.github.io/fluentpdo/)

## License

Free for commercial and non-commercial use under the [Apache 2.0](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL 2.0](http://www.gnu.org/licenses/gpl-2.0.html) licenses.
