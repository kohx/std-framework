<?php

/*
 * http://www.php.net/manual/ja/pdo.drivers.php
 */

return [
	'default' => [
		'dbtype' => 'mysql',
		'dbhost' => 'localhost',
		'dbname' => 'stdcms',
		'username' => 'root',
		'passwd' => '',
		'port' => 3306,
		'charset' => 'utf8',
	],
	'test1' => [
		'dbtype' => 'pgsql',
		'dbhost' => 'localhost',
		'dbname' => 'stdcms',
		'username' => 'root',
		'passwd' => '',
		'port' => 5432,
		'sequence_object' => 'id',
	],	
	'test2' => [
		'dbtype' => 'sqlite',
		'filepath' => '/sqlite/test.db',
		//'filepath' => ':memory:',
	],
	'test3' => [
		'dbtype' => 'sqlite2',
		'filepath' => '/sqlite/test.db',
		//'filepath' => ':memory:',
	],
];
