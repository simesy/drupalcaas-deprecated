<?php
/**
 * @file
 *   A bootstrap file for `phpunit` test runner.
 *
 * This bootstrap file from DTT is fast and customizable.
 *
 * If you get 'class not found' errors while running tests, you should copy this
 * file to a location inside your code-base --such as `/scripts`. Then add the
 * missing namespaces to the bottom of the copied field. Specify your custom
 * `bootstrap-fast.php` file as the bootstrap in `phpunit.xml`.
 *
 * Alternatively, use the bootstrap.php file, in this same directory, which is
 * slower but registers all the namespaces that Drupal tests expect.
 */

// Extend bootstrap provided by DTT.
include_once './vendor/weitzman/drupal-test-traits/src/bootstrap-fast.php';

// Additional namespaces.
$class_loader->addPsr4('Drupal\Tests\lecapi\\', "$root/modules/custom/lecapi/tests/src");
