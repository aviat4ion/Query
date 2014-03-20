# Query

A query builder/database abstraction layer, using prepared queries for security.

[![Build Status](https://secure.travis-ci.org/timw4mail/Query.png)](http://travis-ci.org/timw4mail/Query)
[![Latest Stable Version](https://poser.pugx.org/aviat4ion/query/v/stable.png)](https://packagist.org/packages/aviat4ion/query)
[![Total Downloads](https://poser.pugx.org/aviat4ion/query/downloads.png)](https://packagist.org/packages/aviat4ion/query)
[![Latest Unstable Version](https://poser.pugx.org/aviat4ion/query/v/unstable.png)](https://packagist.org/packages/aviat4ion/query)
[![License](https://poser.pugx.org/aviat4ion/query/license.png)](https://packagist.org/packages/aviat4ion/query)

## Requirements
* Pdo extensions for the databases you wish to use (unless it's Firebird, in which case, the interbase extension is required)
* PHP 5.3+

## Databases Supported

* Firebird (via interbase extension)
* MySQL
* PostgreSQL
* SQLite

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

		// Optional paramaters
		'prefix' => 'tbl_', 	// Database table prefix
		'alias' => 'old' 		// Connection name for the Query function
	);

	$db = Query($params);

The parameters required depend on the database.

### Query function

You can use the `Query()` function as a reference to the last connected database. E.g.

	Query()->get('table_name');

or

	$result = Query()->query($sql);

If the `alias` key is set in the parameters, you can refer to a specific database connection

	// Set the alias in the connection parameters
	$params['alias'] = 'old';

	// Connect to the legacy database
	Query('old')->query($sql);

### Running Queries
Query uses the same interface as CodeIgniter's [Active Record class](http://codeigniter.com/user_guide/database/active_record.html). However, it does not implement the `update_batch` or caching methods.

####You can also run queries manually.

To run a prepared statement, call
`$db->prepare_execute($sql, $params)`.

To run a plain query, `$db->query($sql)`

### Retrieving Results:

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


### Inserting / Updating

An example of an insert query:

	$query = $db->set('foo', 'bar')
		->set('foobar', 'baz')
		->where('foo !=', 'bar')
		->insert('table');

An example of an update query:

	$query = $db->set('foo', 'bar')
		->set('foobar', 'baz')
		->where('foo !=', 'bar')
		->update('table');

The `set` method can also take an array as a paramater, instead of setting individual values.

