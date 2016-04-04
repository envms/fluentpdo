--TEST--
INSERT with ON DUPLICATE KEY UPDATE
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/** @var $fpdo FluentPDO */

//--------------------------------------------------------------------
$query = $fpdo->insertInto('article', array('id' => 1))
		->onDuplicateKeyUpdate(array(
			'title' => 'article 1b',
			'content' => new FluentLiteral('abs(-1)') // let's update with a literal and a parameter value
		));

echo $query->getQuery() . "\n";
print_r($query->getParameters());
echo 'last_inserted_id = ' . $query->execute() . "\n";
$q = $fpdo->from('article', 1)->fetch();
print_r($q);

//--------------------------------------------------------------------
$query = $fpdo->insertInto('article', array('id' => 1))
		->onDuplicateKeyUpdate(array(
			'title' => 'article 1',
			'content' => 'content 1',
		))->execute();
echo "last_inserted_id = $query\n";
$q = $fpdo->from('article', 1)->fetch();
print_r($q);
//#############################################################################

$sqlCreateLastAct = 'CREATE TABLE IF NOT EXISTS `last_activity` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`last_time` INT NOT NULL,
	PRIMARY KEY (`id`)
) AUTO_INCREMENT=100;';

$fpdo->getPdo()->prepare('DROP TABLE IF EXISTS last_activity')->execute();
$fpdo->getPdo()->prepare($sqlCreateLastAct)->execute();

$query = $fpdo->insertInto('last_activity', array('last_time' => '777'))
		->onDuplicateKeyUpdate(array(
			'last_time' => 0,
		))->execute();

echo "last_inserted_id = $query\n";
$q = $fpdo->from('last_activity', 100)->fetch();
print_r($q);

//--------------------------------------------------------------------

$query = $fpdo->insertInto('last_activity', array('id' => 100, 'last_time' => '888'))
		->onDuplicateKeyUpdate(array(
			'last_time' => 0,
		))->execute();

echo "last_inserted_id = $query\n";
$q = $fpdo->from('last_activity', 100)->fetch();
print_r($q);

//#############################################################################


?>
--EXPECTF--
INSERT INTO article (id)
VALUES (?)
ON DUPLICATE KEY UPDATE title = ?, content = abs(-1)
Array
(
    [0] => 1
    [1] => article 1b
)
last_inserted_id = 1
Array
(
    [id] => 1
    [user_id] => 1
    [published_at] => 2011-12-10 12:10:00
    [title] => article 1b
    [content] => 1
)
last_inserted_id = 1
Array
(
    [id] => 1
    [user_id] => 1
    [published_at] => 2011-12-10 12:10:00
    [title] => article 1
    [content] => content 1
)
last_inserted_id = 100
Array
(
    [id] => 100
    [last_time] => 777
)
last_inserted_id = 100
Array
(
    [id] => 100
    [last_time] => 0
)


<? //