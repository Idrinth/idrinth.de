# Idrinth.DE

This is a small blog for personal use, feel free to use the source code elsewhere.

# Configuration

Tracking can optionally use a database instead of flat files. Create `config/config.php` returning an array with your database settings. If the file is missing or the connection fails, tracking falls back to file-based storage automatically.

## MariaDB / MySQL

```php
<?php
return [
    'driver'   => 'mariadb', // or 'mysql'
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'idrinth_blog',
    'username' => 'user',
    'password' => 'secret',
];
```

## PostgreSQL

```php
<?php
return [
    'driver'   => 'postgres', // or 'pgsql'
    'host'     => '127.0.0.1',
    'port'     => 5432,
    'database' => 'idrinth_blog',
    'username' => 'user',
    'password' => 'secret',
];
```

## SQLite

```php
<?php
return [
    'driver' => 'sqlite',
    'path'   => '/var/data/tracking.sqlite',
];
```

| Key | Required | Default | Description |
|---|---|---|---|
| `driver` | yes | — | `mariadb`, `mysql`, `postgres`, `pgsql`, or `sqlite` |
| `host` | no | `127.0.0.1` | Database server hostname or IP |
| `port` | no | `3306` (MySQL) / `5432` (Postgres) | Database server port |
| `database` | no | `idrinth_blog` | Database name |
| `username` | no | `''` | Database user |
| `password` | no | `''` | Database password |
| `path` | no | `config/tracking.sqlite` | File path for SQLite databases |

`config/config.php` is gitignored so credentials are never committed.

# LICENSE

- Source code (PHP, HTML, CSS, JS): MIT
- Content (MD): Quoting allowed, Copying not. Original Blog article has to be linked as source
