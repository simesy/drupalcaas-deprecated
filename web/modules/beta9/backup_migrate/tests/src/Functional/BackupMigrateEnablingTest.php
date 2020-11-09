<?php

namespace Drupal\Tests\backup_migrate\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Checks if module enabling doesn't break the site.
 *
 * @group backup_migrate
 */
class BackupMigrateEnablingTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['backup_migrate'];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests if site opens with no errors.
   */
  public function testEnabling() {
    $this->drupalGet('');
    $this->assertSession()->statusCodeEquals(200);
  }

}
