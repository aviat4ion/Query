# Query

A query builder/abstraction layer, using prepared queries for security.

[![Build Status](https://secure.travis-ci.org/timw4mail/Query.png)](http://travis-ci.org/timw4mail/Query)

## Requirements
* Pdo extensions for the databases you wish to use (unless it's Firebird, in which case, the interbase extension is required)
* PHP 5.2+

## Databases Supported
	
* Firebird
* MySQL
* PostgreSQL
* SQLite
* Others, via ODBC

## Including Query in your application

To include Query in your PHP project, just include the `autoload.php` file. This will automatically load the classes that are supported by the current PHP installation.

	
## Connecting

Create a connection array or object similar to this:

	<?php
	
	$params = array(
		'type' => 'mysql',
		'host' => 'localhost',
		'user' => 'root',
		'pass' => '',
		'port' => '3306',
		'database' => 'test_db',
		
		// Only required
		// SQLite or Firebird
		'file' => '/path/to/db/file',
	);
	
	$db = new Query_Builder($params);

The parameters required depend on the database. 

### Running Queries
Query uses the same interface as CodeIgniter's [Active Record class](http://codeigniter.com/user_guide/database/active_record.html). However, it does not implement the `count_all_results`, `having`, `or_having`, `insert_batch`, `update_batch` methods.

#### Retrieving Results

An example of a moderately complex query:

	$query = $db->select('id, key as k, val')
		->from('table t')
		->where('k >', 3)
		->or_where('id !=' 5)
		->order_by('val', 'DESC')
		->limit(3, 1)
		->get();
		
This will generate a query similar to (with this being the output for a Postgres database):

	SELECT "id", "key" AS "k", "val"
	FROM "table" "t"
	WHERE "k" > ?
	OR "id" != ?
	ORDER BY "val" DESC
	LIMIT 3 OFFSET 1


To retreive the results of a query, use the PDO method [fetch](http://php.net/manual/en/pdostatement.fetch.php) and/or [fetchAll](http://php.net/manual/en/pdostatement.fetchall.php).

	$query = $db->get('table_name');
	
	$results = $query->fetchAll(PDO::FETCH_ASSOC);