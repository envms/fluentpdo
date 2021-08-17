# FluentPDO [![Build Status](https://secure.travis-ci.org/envms/fluentpdo.png?branch=master)](http://travis-ci.org/envms/fluentpdo) [![Maintainability](https://api.codeclimate.com/v1/badges/19210ca91c7055b89705/maintainability)](https://codeclimate.com/github/fpdo/fluentpdo/maintainability)

FluentPDO is a PHP SQL query builder using PDO. It's a quick and light library featuring a smart join builder, which automatically creates table joins for you.

## Features

- Easy interface for creating robust queries
- Supports any database compatible with PDO
- Ability to build complex SELECT, INSERT, UPDATE & DELETE queries with little code
- Type hinting for magic methods with code completion in smart IDEs

## Versions

#### Version 2.x

The stable release of FluentPDO and actively maintained. Officially supports PHP 7.3 to PHP 8.0,
but it can work with previous versions of PHP 7.

#### Version 1.x

The legacy release of FluentPDO. It is no longer supported and will not be maintained or updated.
This version works with PHP 5.4 to 7.1.

#### Version 3.x - alpha

This version is a full rewrite of Fluent from the ground up. Its main advantage is
significantly less memory usage and much greater performance in query building. It also places
a few additional restrictions to make queries easier to read and maintain. Documentation has also
been a very common request, and version 3 is being fully documented alongside development.
Details and metrics will be posted once available.

## Reference

[Sitepoint - Getting Started with FluentPDO](http://www.sitepoint.com/getting-started-fluentpdo/)

## Installation

### Composer

The preferred way to install FluentPDO is via [composer](http://getcomposer.org/).

Add the following line in your `composer.json` file:

	"require": {
		...
		"envms/fluentpdo": "^2.2.0"
	}

update your dependencies with `composer update`, and you're done!

### Download Zip

If you prefer not to use composer, download the latest release, create the directory `Envms/FluentPDO` in your library directory, and drop this repository into it. Finally, add:

```php
require '[lib-dir]/Envms/FluentPDO/src/Query.php';
```

to the top of your application. **Note:** You will need an autoloader to use FluentPDO without changing its source code.

## Getting Started

Create a new PDO instance, and pass the instance to FluentPDO:

```php
$pdo = new PDO('mysql:dbname=fluentdb', 'user', 'password');
$fluent = new \Envms\FluentPDO\Query($pdo);
```

Then, creating queries is quick and easy:

```php
$query = $fluent->from('comment')
             ->where('article.published_at > ?', $date)
             ->orderBy('published_at DESC')
             ->limit(5);
```

which would build the query below:

```mysql
SELECT comment.*
FROM comment
LEFT JOIN article ON article.id = comment.article_id
WHERE article.published_at > ?
ORDER BY article.published_at DESC
LIMIT 5
```

To get data from the select, all we do is loop through the returned array:

```php
foreach ($query as $row) {
    echo "$row['title']\n";
}
```

## Using the Smart Join Builder

Let's start with a traditional join, below:

```php
$query = $fluent->from('article')
             ->leftJoin('user ON user.id = article.user_id')
             ->select('user.name');
```

That's pretty verbose, and not very smart. If your tables use proper primary and foreign key names, you can shorten the above to:

```php
$query = $fluent->from('article')
             ->leftJoin('user')
             ->select('user.name');
```

That's better, but not ideal. However, it would be even easier to **not write any joins**:

```php
$query = $fluent->from('article')
             ->select('user.name');
```

Awesome, right? FluentPDO is able to build the join for you, by you prepending the foreign table name to the requested column.

All three snippets above will create the exact same query:

```mysql
SELECT article.*, user.name 
FROM article 
LEFT JOIN user ON user.id = article.user_id
```

##### Close your connection

Finally, it's always a good idea to free resources as soon as they are done with their duties:
 
 ```php
$fluent->close();
```

## CRUD Query Examples

##### SELECT

```php
$query = $fluent->from('article')->where('id', 1)->fetch();
$query = $fluent->from('user', 1)->fetch(); // shorter version if selecting one row by primary key
```

##### INSERT

```php
$values = array('title' => 'article 1', 'content' => 'content 1');

$query = $fluent->insertInto('article')->values($values)->execute();
$query = $fluent->insertInto('article', $values)->execute(); // shorter version
```

##### UPDATE

```php
$set = array('published_at' => new FluentLiteral('NOW()'));

$query = $fluent->update('article')->set($set)->where('id', 1)->execute();
$query = $fluent->update('article', $set, 1)->execute(); // shorter version if updating one row by primary key
```

##### DELETE

```php
$query = $fluent->deleteFrom('article')->where('id', 1)->execute();
$query = $fluent->deleteFrom('article', 1)->execute(); // shorter version if deleting one row by primary key
```

***Note**: INSERT, UPDATE and DELETE queries will only run after you call `->execute()`*

## License

Free for commercial and non-commercial use under the [Apache 2.0](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL 2.0](http://www.gnu.org/licenses/gpl-2.0.html) licenses.
