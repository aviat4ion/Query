# Query

A query builder/database abstraction layer, using prepared statements for security.

[![Build Status](https://jenkins.timshomepage.net/buildStatus/icon?job=query)](https://jenkins.timshomepage.net/job/query/)
[![Code Coverage](https://scrutinizer-ci.com/g/aviat4ion/Query/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/aviat4ion/Query/?branch=develop)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/aviat4ion/Query/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/aviat4ion/Query/?branch=develop)
[![Latest Stable Version](https://poser.pugx.org/aviat/query/v/stable.png)](https://packagist.org/packages/aviat/query)
[![Total Downloads](https://poser.pugx.org/aviat/query/downloads.png)](https://packagist.org/packages/aviat/query)
[![Latest Unstable Version](https://poser.pugx.org/aviat/query/v/unstable.png)](https://packagist.org/packages/aviat/query)
[![License](https://poser.pugx.org/aviat/query/license.png)](http://www.dbad-license.org/)

## Requirements
* PDO extensions for the databases you wish to use (unless it's Firebird, in which case, the interbase extension is required)
* Supported version of PHP (Older versions may work, but are not supported)

## Databases Supported

* Firebird (via interbase extension)
* MySQL
* PostgreSQL
* SQLite

## Including Query in your application

* Install via composer and include `vendor/autoload.php`
or
* Just include the `autoload.php` file. This will automatically load the classes that are supported by the current PHP installation.


## Connecting

Create a connection array or object similar to this:

```php
<?php

$params = array(
	'type' => 'mysql', // mysql, pgsql, firebird, sqlite
	'host' => 'localhost', // address or socket
	'user' => 'root',
	'pass' => '',
	'port' => '3306',
	'database' => 'test_db',

	// Only required for
	// SQLite or Firebird
	'file' => '/path/to/db/file',

	// Optional paramaters
	'prefix' => 'tbl_', 	// Database table prefix
	'alias' => 'old' 		// Connection name for the Query function
);

$db = Query($params);
```

The parameters required depend on the database.

### Query function

You can use the `Query()` function as a reference to the last connected database. E.g.

```php
<?php
Query()->get('table_name');

// or
$result = Query()->query($sql);
```

If the `alias` key is set in the parameters, you can refer to a specific database connection

```php
<?php

// Set the alias in the connection parameters
$params['alias'] = 'old';

// Connect to the legacy database
Query('old')->query($sql);
```

### Running Queries
Query uses the same interface as CodeIgniter's [Query Builder](http://www.codeigniter.com/user_guide/database/query_builder.html) class. However, it does not implement the `update_batch` or caching methods. For specific query builder methods, see the [class documentation](https://gitdev.timshomepage.net/Query/docs/classes/Query_QueryBuilder.html#methods).

#### You can also run queries manually.

To run a prepared statement, call
`$db->prepare_execute($sql, $params)`.

To run a plain query, `$db->query($sql)`

### Retrieving Results:

An example of a moderately complex query:

```php
<?php
$query = $db->select('id, key as k, val')
	->from('table t')
	->where('k >', 3)
	->or_where('id !=' 5)
	->order_by('val', 'DESC')
	->limit(3, 1)
	->get();
```

This will generate a query similar to (with this being the output for a PostgreSQL database):

```sql
SELECT "id", "key" AS "k", "val"
FROM "table" "t"
WHERE "k" > ?
OR "id" != ?
ORDER BY "val" DESC
LIMIT 3 OFFSET 1
```


To retrieve the results of a query, use the PDO method [fetch](http://php.net/manual/en/pdostatement.fetch.php) and/or [fetchAll](http://php.net/manual/en/pdostatement.fetchall.php).

```php
<?php
$query = $db->get('table_name');

$results = $query->fetchAll(PDO::FETCH_ASSOC);
```


### Inserting / Updating

An example of an insert query:
```php
<?php
$query = $db->set('foo', 'bar')
	->set('foobar', 'baz')
	->where('foo !=', 'bar')
	->insert('table');
```

An example of an update query:

```php
<?php
$query = $db->set('foo', 'bar')
	->set('foobar', 'baz')
	->where('foo !=', 'bar')
	->update('table');
```

The `set` method can also take an array as a parameter, instead of setting individual values.

