# FluentPDO

[![Build Status](https://secure.travis-ci.org/lichtner/fluentpdo.png?branch=master)](http://travis-ci.org/lichtner/fluentpdo)

FluentPDO - smart SQL builder for PHP.

FluentPDO is small PHP library for rapid query building. Killer feature is "Smart join builder" which generates joins automatically.

## Features

- Fluent interface for creating queries step by step
- Smart join builder
- Simple API based on PDO and SQL syntax
- Build SELECT, INSERT, UPDATE & DELETE queries
- Small and fast
- Type hinting with code completion in smart IDEs
- Requires PHP 5.1+ with any database supported by PDO

## Install

### Composer

The preferred way to install FluentPDO is via [composer](http://getcomposer.org/).

Add in your `composer.json`:

	"require": {
		...
		"lichtner/fluentpdo": "dev-master"	
	}

then update your dependencies with `composer update`.

### Copy

If you are not familiar with composer just copy `/FluentPDO` directory into your `libs/` directory then:

	```php
	include "libs/FluentPDO/FluentPDO.php";
	```

## Start usage

	```php
    $pdo = new PDO("mysql:dbname=fblog", "root");
	$fpdo = new FluentPDO($pdo);
	```

## First example

FluentPDO is easy to use:

    ```php
    $query = $fpdo->from('article')
                ->where('published_at > ?', $date)
                ->orderBy('published_at DESC')
                ->limit(5);
    if ($user_id) {
        $query = $query
                ->where('user_id', $user_id)
                ->select('user.name');        // this join table user
    }
    foreach ($query as $row) {
        echo "$row[name] - $row[title]\n";
    }
    ```

And executed query is:

    ```mysql
	SELECT article.*, user.name
	FROM article
    		LEFT JOIN user ON user.id = article.user_id
	WHERE published_at > ? AND user_id = ?
	ORDER BY published_at DESC
	LIMIT 5
	```


Full documentation can be found on the [FluentPDO homepage](http://fluentpdo.com)

## Simple Query Examples

##### SELECT

    ```php
	$query = $fpdo->from('article')->orderBy('published_at DESC')->limit(5);
	// or if you want to one row by primary key
	$query = $fpdo->from('user', 2);
    ```

##### INSERT

    ```php
	$query = $fpdo->insertInto('article')->values(array('title' => 'article 1', 'content' => 'content 1'));
	// or shortly
	$values = array('title' => 'article 1', 'content' => 'content 1');
	$query = $fpdo->insertInto('article', $values);
    ```

##### UPDATE

    ```php
    $set = array('published_at' => new FluentLiteral('NOW()'));
	$query = $fpdo->update('article')->set($set)->where('id', 1);
	// or shortly
	$query = $fpdo->update('article', $set, 'id', 1);
    ```

##### DELETE

    ```php
	$query = $fpdo->deleteFrom('article')->where('id', 1);
	// or shortly
	$query = $fpdo->deleteFrom('article', 'id', 1);
    ```

*Note: INSERT, UPDATE and DELETE will be executed after `->execute()`:*

    ```php
	$fpdo->deleteFrom('article', 'id', 1)->execute();
    ```

Full documentation can be found on the [FluentPDO homepage](http://fluentpdo.com)

## Licence

Free for commercial and non-commercial use ([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL](http://www.gnu.org/licenses/gpl-2.0.html)).
