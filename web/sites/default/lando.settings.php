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

// Lando local solr overrides.
$config['search_api.server.solr']['backend_config'] = [
  'connector' => 'standard',
  'connector_config' => [
    'host' => 'solr',
    'port' => '8983',
    'path' => '/',
    'core' => 'drupal',
  ],
];

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/lando.services.yml';

$settings['twig_debug'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.logging']['error_level'] = 'verbose';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$settings['update_free_access'] = FALSE;
$settings['rebuild_access'] = FALSE;
$settings['skip_permissions_hardening'] = TRUE;

$config['stage_file_proxy.settings']['origin'] = 'https://caas.freesauce.au';
$config['stage_file_proxy.settings']['hotlink'] = TRUE;

$settings['trusted_host_patterns'] = ['^caas\.lndo\.site$', '^localhost$'];
