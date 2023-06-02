# Laravel Query Log

Laravel sql query logging library.

## Requirements
* PHP >= 7.4
* Laravel >= 8.0

## Installation

Install the package via composer:

```
composer require sanmanto-sds/laravel-query-log
```

## Configuration

Define `true` for begin query logging:

```
QUERY_LOG_ENABLE=true
```

## Example

Laravel query:

```php
DB::beginTransaction();

$user = User::create([
    'email' => 'john.doe@example.com',
    'password' => bcrypt('YP5fQDadZzjcm4E'),
]);

$sql = 'insert into "user_profiles" ("user_id", "first_name", "last_name", "country")';
$sql .= ' values (:user_id, :first_name, :last_name, :country)';

DB::insert($sql, [
    'user_id' => $user->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'country' => 'Australia'
]);

DB::commit();
```

Log result:

```
[2022-03-06 18:16:31] local.DEBUG: Query from 'pgsql' connection:
TRANSACTION BEGIN

[2022-03-06 18:16:32] local.DEBUG: Query from 'pgsql' connection. Time 1.890000 ms. SQL:
insert into "users" ("email", "password", "updated_at", "created_at") values ('john.doe@example.com', 'y$xQgcnNqoxaanrF3tFh2YweNFr/rhr8jfUpbdrlNoe6rPf22xIqGxy', '2022-03-06 18:16:32', '2022-03-06 18:16:32') retu
rning "id"

[2022-03-06 18:16:32] local.DEBUG: Query from 'pgsql' connection. Time 1.600000 ms. SQL:
insert into "user_profiles" ("user_id", "first_name", "last_name", "country") values (1, 'John', 'Doe', 'Australia')

[2022-03-06 18:16:32] local.DEBUG: Query from 'pgsql' connection:
TRANSACTION COMMIT
```