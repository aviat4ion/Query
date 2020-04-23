# Query

A query builder/database abstraction layer, using prepared statements for security.

[![Code Coverage](https://scrutinizer-ci.com/g/aviat4ion/Query/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/aviat4ion/Query/?branch=develop)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/aviat4ion/Query/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/aviat4ion/Query/?branch=develop)
[![Latest Stable Version](https://poser.pugx.org/aviat/query/v/stable.png)](https://packagist.org/packages/aviat/query)
[![Total Downloads](https://poser.pugx.org/aviat/query/downloads.png)](https://packagist.org/packages/aviat/query)
[![Latest Unstable Version](https://poser.pugx.org/aviat/query/v/unstable.png)](https://packagist.org/packages/aviat/query)

## Requirements
* PDO extensions for the databases you wish to use
* PHP 7.4 or later

## Databases Supported

* MySQL 5+ / MariaDB
* PostgreSQL 8.4+
* SQLite

## Including Query in your application

* Install via composer and include `vendor/autoload.php`

## Connecting

Create a connection array or object similar to this:

```php
<?php

$params = array(
	'type' => 'mysql', // mysql, pgsql, sqlite
	'host' => 'localhost', // address or socket
	'user' => 'root',
	'pass' => '',
	'port' => '3306',
	'database' => 'test_db',

	// Only required for
	// SQLite
	'file' => '/path/to/db/file',

	// Optional parameters
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
Query is based on CodeIgniter's [Query Builder](http://www.codeigniter.com/user_guide/database/query_builder.html) class.
However, it has camelCased method names, and does not implement the caching methods.
For specific query builder methods, see the [class documentation](https://gitdev.timshomepage.net/Query/apiDocumentation/classes/Query_QueryBuilder.html#methods).

Other database methods not directly involved in building queries, are also available from the query builder object.
The methods available depend on the database, but common methods  are documented
[here](https://gitdev.timshomepage.net/Query/apiDocumentation/classes/Query_Drivers_AbstractDriver.html#methods).

#### You can also run queries manually.

To run a prepared statement, call
`$db->prepareExecute($sql, $params)`.

To run a plain query, `$db->query($sql)`

### Retrieving Results:

An example of a moderately complex query:

```php
<?php
$query = $db->select('id, key as k, val')
	->from('table t')
	->where('k >', 3)
	->orWhere('id !=', 5)
	->orderBy('val', 'DESC')
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

The query execution methods `get`, `getWhere`, `insert`,
 `insertBatch`,`update`, and `delete` return a native [PDOStatement](http://php.net/manual/en/class.pdostatement.php) object.
To retrieve the results of a query, use the PDOStatement method [fetch](http://php.net/manual/en/pdostatement.fetch.php) and/or
[fetchAll](http://php.net/manual/en/pdostatement.fetchall.php).

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

