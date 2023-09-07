<?php

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/local.services.yml';

$settings['twig_debug'] = TRUE;
$settings['hot_module_replacement'] = TRUE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.logging']['error_level'] = 'verbose';

// Beware of xdebug slowness.
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$settings['update_free_access'] = FALSE;
$settings['rebuild_access'] = FALSE;
$settings['skip_permissions_hardening'] = TRUE;

$settings['trusted_host_patterns'] = ['^.+\.lndo\.site$', '^localhost$'];
$config['media.settings']['iframe_domain'] = '';
