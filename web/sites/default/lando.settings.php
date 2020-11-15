<?php

/**
 * @file
 * Copy this file to settings.local.php to override local stuff.
 */

$databases['default']['default'] = array (
  'database' => 'drupal9',
  'username' => 'drupal9',
  'password' => 'drupal9',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/lando.services.yml';

$settings['twig_debug'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.logging']['error_level'] = 'verbose';

// By default lando has some dev stuff turned off so that xdebug isn't slow if it's enabled in .lando.yml.
// You can override these in local.settings.php - useful examples shown here.
// $settings['cache']['bins']['render'] = 'cache.backend.null';
// $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
// $settings['cache']['bins']['page'] = 'cache.backend.null';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$settings['update_free_access'] = FALSE;
$settings['rebuild_access'] = TRUE;
$settings['skip_permissions_hardening'] = TRUE;

$config['stage_file_proxy.settings']['origin'] = 'https://content.lilengine.co';
$config['stage_file_proxy.settings']['hotlink'] = TRUE;
