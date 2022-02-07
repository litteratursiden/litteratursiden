# Installation instructions

## Production / Staging

### Create settings file with database connection
```php
<?php

/**
 * Hash salt.
 */
$settings['hash_salt'] = '@todo %SET KEY%';

/**
 * Database connection.
 */
$databases['default']['default'] = array (
  'database' => '@todo %SET KEY%',
  'username' => '@todo %SET KEY%',
  'password' => '@todo %SET KEY%',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['trusted_host_patterns'] = array('^litteratursiden\.dk$');

$config['mailchimp.settings']['api_key'] = '@todo %SET KEY%';
$config['bpi.service_settings']['bpi_api_key'] = '@todo %SET KEY%';
$config['bpi.service_settings']['bpi_secret_key'] = '@todo %SET KEY%';

$config['elasticsearch_connector.cluster.local']['url'] = '@todo %SET KEY%';

$config['varnish_purger.settings']['hostname'] = '@todo %SET KEY%';

$config['lit_open_platform.settings']['client_id'] = '@todo %SET KEY%';
$config['lit_open_platform.settings']['client_secret'] = '@todo %SET KEY%';

$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = array('127.0.0.1');
$settings['reverse_proxy_proto_header'] = 'https';

$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

$config['system_status.settings']['system_status_token'] = '@todo %SET KEY%';
$config['system_status.settings']['system_status_encrypt_token'] = '@todo %SET KEY%';

$settings['memcache']['servers'] = '@todo %SET KEY%';
$settings['memcache']['bins'] = '@todo %SET KEY%';
$settings['memcache']['key_prefix'] = 'dev';
$settings['cache']['default'] = 'cache.backend.memcache';
$settings['cache']['bins']['render'] = 'cache.backend.memcache';
$settings['memcache']['key_hash_algorithm'] = 'sha1';

/**
* Add config dir.
 */
$config_directories['sync'] = '../config/sync';
```

## Development

### Create local settings file with database connection

```php
<?php

/**
 * Add development service settings.
 */
if (file_exists(__DIR__ . '/../development.services.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/../development.services.yml';
}

/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

/**
 * Disable caching.
 */
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';

/**
 * Set hash salt.
 */
$settings['hash_salt'] = '1234';

/**
 * Set trusted host pattern.
 */
$settings['trusted_host_patterns'] = [
  '^litt\.local\.itkdev\.dk$',
  '^127\.0\.0\.1$',
  '^0\.0\.0\.0$',
];

/**
 * Database connection.
 */
$databases['default']['default'] = [
 'database' => getenv('DATABASE_DATABASE') ?: 'db',
 'username' => getenv('DATABASE_USERNAME') ?: 'db',
 'password' => getenv('DATABASE_PASSWORD') ?: 'db',
 'host' => getenv('DATABASE_HOST') ?: 'mariadb',
 'port' => getenv('DATABASE_PORT') ?: '',
 'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
 'prefix' => '',
];

/**
 * Set sync path
 */
$settings['config_sync_directory'] = '../config/sync';

```

### Install site
```sh
docker-compose up --detach
docker-compose exec phpfpm composer install
docker-compose exec phpfpm vendor/bin/drush --yes site:install minimal --existing-config
# Get the site url
echo "http://$(docker-compose port nginx 80)"
# Get admin sign in url
docker-compose exec phpfpm vendor/bin/drush --yes --uri="http://$(docker-compose port nginx 80)" user:login
```

Sign in as admin:

```sh
docker-compose exec phpfpm vendor/bin/drush --uri=http://$(docker-compose port nginx 80) user:login
```

### Using Symfony Local Web Server

See [Symfony Local Web
Server](https://symfony.com/doc/current/setup/symfony_server.html) for details.

```sh
docker-compose up -d
symfony composer install

Add local settings file see: ### Create local settings file with database connection

symfony php vendor/bin/drush site-install minimal --existing-config --yes
symfony local:server:start --daemon
# Update the uri to the actual address of the running web server.
symfony php vendor/bin/drush --uri=https://127.0.0.1:8000 user:login
```
