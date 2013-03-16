# FluentPDO

FluentPDO - smart SQL builder for PHP.

FluentPDO is small PHP library for rapid query building. Killer feature is "Smart join builder" which generates joins automatically.

## Features

- Fluent interface for creating queries step by step
- Smart join builder
- Simple API based on PDO and SQL syntax
- Build SELECT, INSERT query (UPDATE and DELETE comming soon)
- Small and fast
- Type hinting with code completion in smart IDEs
- requires PHP 5.1+ with any database supported by PDO

## Install

### Composer

Preferred way how to install FluentPDO is via [composer](http://getcomposer.org/).

Add in your `composer.json`:

	"require": {
		...
		"lichtner/fluentpdo": "dev-master"	
	}

then update your dependencies with `composer update`.

### Copy

If you are not familiar with composer just copy `/FluentPDO` directory into your `libs/` directory then:

	include "libs/FluentPDO/FluentPDO.php";
	$pdo = new PDO("mysql:dbname=fblog", "root");
	$fpdo = new FluentPDO($pdo);
	
## First example

FluentPDO is easy to use:

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

And executed query is:

	SELECT article.*, user.name
	FROM article
    		LEFT JOIN user ON user.id = article.user_id
	WHERE published_at > ? AND user_id = ?
	ORDER BY published_at DESC
	LIMIT 5


Full documentation can you find on [FluentPDO homepage](http://fluentpdo.com)

## Licence

Free for commercial and non-commercial use ([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL](http://www.gnu.org/licenses/gpl-2.0.html)).

*Copyright (c) 2012, Marek Lichtner*

