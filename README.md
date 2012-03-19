# Query

A query builder/abstraction layer, using prepared queries for security.

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
Query uses the same interface as CodeIgniter's [Active Record class](http://codeigniter.com/user_guide/database/active_record.html). However, it does not implement the `select_` methods, `count_all_results`, `distinct`, `having`, `or_having`, `get_compiled_query`, `insert_batch`, `update_batch`, or `count_all` methods.

To retreive the results of a query, use the PDO methods `fetch` and `fetchAll`.

	$query = $db->get('table_name');
	
	$results = $query->fetchAll(PDO::FETCH_ASSOC);