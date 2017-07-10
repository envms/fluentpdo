# FluentPDO [![Build Status](https://secure.travis-ci.org/envms/fluentpdo.png?branch=master)](http://travis-ci.org/envms/fluentpdo) [![Code Climate](https://codeclimate.com/github/fpdo/fluentpdo/badges/gpa.svg)](https://codeclimate.com/github/fpdo/fluentpdo)

FluentPDO - smart SQL builder for PHP.

FluentPDO is small PHP library for rapid query building. Killer feature is "Smart join builder" which generates joins automatically.

## Features

- Fluent interface for creating queries step by step
- Smart join builder
- Simple API based on PDO and SQL syntax
- Build SELECT, INSERT, UPDATE & DELETE queries
- Small and fast
- Type hinting with code completion in smart IDEs
- Requires PHP 5.3+ with any database supported by PDO

## Reference

[Sitepoint - Getting Started with FluentPDO](http://www.sitepoint.com/getting-started-fluentpdo/)

## Install

### Composer

The preferred way to install FluentPDO is via [composer](http://getcomposer.org/). v1.1.x will be the last until the release of 2.0, so we recommend using 1.1.* to ensure no breaking changes are introduced.

Add in your `composer.json`:

	"require": {
		...
		"fpdo/fluentpdo": "1.1.*"
	}

then update your dependencies with `composer update`.

### Copy

If you are not familiar with composer just copy `/FluentPDO` directory into your `libs/` directory then:

```php
include "libs/FluentPDO/FluentPDO.php";
```

## Start usage

```php
$pdo = new PDO("mysql:dbname=fluentdb", "root");
$fpdo = new FluentPDO($pdo);
```

## First example

FluentPDO is easy to use:

```php
$query = $fpdo->from('article')
            ->where('published_at > ?', $date)
            ->orderBy('published_at DESC')
            ->limit(5);
foreach ($query as $row) {
    echo "$row[title]\n";
}
```
executed query is:

```mysql
SELECT article.*
FROM article
WHERE published_at > ?
ORDER BY published_at DESC
LIMIT 5
```

## Smart join builder (how to build queries)

If you want to join table you can use full sql join syntax. For example we would like to show list of articles with author name:

```php
$query = $fpdo->from('article')
              ->leftJoin('user ON user.id = article.user_id')
              ->select('user.name');
```

It was not so much smart, was it? ;-) If your database uses convention for primary and foreign key names, you can write only:

```php
$query = $fpdo->from('article')->leftJoin('user')->select('user.name');
```

Smarter? May be. but **best practice how to write joins is not to write any joins ;-)**

```php
$query = $fpdo->from('article')->select('user.name');
```

All three commands create same query:

```mysql
SELECT article.*, user.name 
FROM article 
LEFT JOIN user ON user.id = article.user_id
```

## Simple CRUD Query Examples

##### SELECT

```php
$query = $fpdo->from('article')->where('id', 1);
// or shortly if you select one row by primary key
$query = $fpdo->from('user', 1);
```

##### INSERT

```php
$values = array('title' => 'article 1', 'content' => 'content 1');
$query = $fpdo->insertInto('article')->values($values)->execute();
// or shortly
$query = $fpdo->insertInto('article', $values)->execute();
```

##### UPDATE

```php
$set = array('published_at' => new FluentLiteral('NOW()'));
$query = $fpdo->update('article')->set($set)->where('id', 1)->execute();
// or shortly if you update one row by primary key
$query = $fpdo->update('article', $set, 1)->execute();
```

##### DELETE

```php
$query = $fpdo->deleteFrom('article')->where('id', 1)->execute();
// or shortly if you delete one row by primary key
$query = $fpdo->deleteFrom('article', 1)->execute();
```

*Note: INSERT, UPDATE and DELETE will be executed after `->execute()`:*

Full documentation can be found on the [FluentPDO homepage](http://envms.github.io/fluentpdo/)

## Licence

Free for commercial and non-commercial use ([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL](http://www.gnu.org/licenses/gpl-2.0.html)).
