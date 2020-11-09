<?php

namespace Drupal\Tests\backup_migrate\Functional;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests backup migrate quick backup functionality.
 *
 * @group backup_migrate
 */
class BackupMigrateQuickBackupTest extends BrowserTestBase {
  use StringTranslationTrait;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Ensure backup_migrate folder exists.
    $path = 'private://backup_migrate/';
    \Drupal::service('file_system')
      ->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);

    // Log in as an admin.
    $this->drupalLogin($this->drupalCreateUser([
      'perform backup',
      'access backup files',
      'administer backup and migrate',
      // Required for the file admin page.
      'administer site configuration',
    ]));
    $this->drupalGet('admin/config/media/file-system');
  }

  /**
   * Tests quick backup.
   */
  public function testQuickBackup() {
    // Load the main B&M admin page.
    $this->drupalGet('admin/config/development/backup_migrate');
    $this->assertSession()->statusCodeEquals(200);

    // Submit the quick backup form.
    $data = [
      'source_id' => 'default_db',
      'destination_id' => 'private_files',
    ];
    $this->submitForm($data, $this->t('Backup now'));

    // Confirm that the form submitted.
    $this->assertSession()->pageTextContains('Backup Complete.');

    // Get backups page.
    $this->drupalGet('admin/config/development/backup_migrate/backups');
    $this->assertSession()->statusCodeEquals(200);

    // Searching for the existing backups.
    $page = $this->getSession()->getPage();
    $table = $page->find('css', 'table');
    $row = $table->find('css', sprintf('tbody tr:contains("%s")', '.mysql.gz'));
    $this->assertNotNull($row);
  }

}
