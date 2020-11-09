<?php

namespace Drupal\Tests\backup_migrate\Functional;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests backup migrate permissions functionality.
 *
 * @group backup_migrate
 */
class BackupMigratePermissionsTest extends BrowserTestBase {

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
   * All of the paths that are being tested.
   *
   * @var array
   */
  protected $allPaths = [
    'admin/config/development/backup_migrate',
    'admin/config/development/backup_migrate/advanced',
    'admin/config/development/backup_migrate/restore',
    'admin/config/development/backup_migrate/backups',
    'admin/config/development/backup_migrate/schedule',
    'admin/config/development/backup_migrate/schedule/add',
    'admin/config/development/backup_migrate/settings',
    'admin/config/development/backup_migrate/settings/add',
    'admin/config/development/backup_migrate/settings/destination',
    'admin/config/development/backup_migrate/settings/destination/add',
    'admin/config/development/backup_migrate/settings/source',
    'admin/config/development/backup_migrate/settings/source/add',
    'admin/config/development/backup_migrate/settings/destination/backups/private_files/delete/none.mysql.gz',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Ensure the backup_migrate folder exists.
    $path = 'private://backup_migrate/';
    \Drupal::service('file_system')->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
  }

  /**
   * Check a set of paths to see if they are accessible for a given permission.
   *
   * @param array $ok_paths
   *   The paths that are expected return a 200 response, all others are
   *   expected to return a 403 response.
   * @param array $permissions
   *   All of the permissions that are to be tested for this set of paths.
   */
  private function checkPathsWithUser(array $ok_paths = [], array $permissions = []) {
    // Before running the tests log in with the requested permissions.
    $this->drupalLogin($this->drupalCreateUser($permissions));

    // Run the path tests.
    $this->checkPaths($ok_paths);
  }

  /**
   * Check a set of paths to see if they are accessible.
   *
   * @param array $ok_paths
   *   The paths that are expected return a 200 response, all others are
   *   expected to return a 403 response.
   */
  private function checkPaths(array $ok_paths = []) {
    foreach ($this->allPaths as $path) {
      $this->drupalGet($path);
      if (in_array($path, $ok_paths)) {
        $this->assertSession()->statusCodeEquals(200);
      }
      else {
        $this->assertSession()->statusCodeEquals(403);
      }
    }
  }

  /**
   * Tests access for anonymous users.
   */
  public function testAnonymous() {
    // Run the tests without any $ok_paths as they should all be 403.
    $this->checkPaths([]);
  }

  /**
   * Tests access for an authenticated user without any permissions.
   */
  public function testAuthenticated() {
    // No permissions as the visitor can't do anything.
    $permissions = [];
    // No paths should be ok.
    $ok_paths = [];

    // Run the tests.
    $this->checkPathsWithUser($ok_paths, $permissions);
  }

  /**
   * Tests access for 'administer backup and migrate' permission.
   */
  public function testAdminister() {
    // The permission(s) to test.
    $permissions = [
      'administer backup and migrate',
    ];
    // Only settings pages should work.
    $ok_paths = [
      'admin/config/development/backup_migrate/schedule',
      'admin/config/development/backup_migrate/schedule/add',
      'admin/config/development/backup_migrate/settings',
      'admin/config/development/backup_migrate/settings/add',
      'admin/config/development/backup_migrate/settings/destination',
      'admin/config/development/backup_migrate/settings/destination/add',
      'admin/config/development/backup_migrate/settings/source',
      'admin/config/development/backup_migrate/settings/source/add',
      'admin/config/development/backup_migrate/settings/destination/backups/private_files/delete/none.mysql.gz',
    ];

    // Run the tests.
    $this->checkPathsWithUser($ok_paths, $permissions);
  }

  /**
   * Tests access for 'perform backup' permission.
   */
  public function testPerformBackup() {
    // The permission(s) to test.
    $permissions = [
      'perform backup',
    ];
    // The paths to check.
    $ok_paths = [
      'admin/config/development/backup_migrate',
      'admin/config/development/backup_migrate/advanced',
    ];

    // Run the tests.
    $this->checkPathsWithUser($ok_paths, $permissions);
  }

  /**
   * Tests access for 'restore from backup' permission.
   */
  public function testRestoreFromBackup() {
    // The permission(s) to test.
    $permissions = [
      'restore from backup',
    ];
    // The paths to check.
    $ok_paths = [
      'admin/config/development/backup_migrate/restore',
    ];

    // Run the tests.
    $this->checkPathsWithUser($ok_paths, $permissions);
  }

  /**
   * Tests access for 'access backup files' permission.
   */
  public function testAccessBackupFiles() {
    // The permission(s) to test.
    $permissions = [
      'access backup files',
    ];
    // The paths to check.
    $ok_paths = [
      'admin/config/development/backup_migrate/backups',
    ];

    // Run the tests.
    $this->checkPathsWithUser($ok_paths, $permissions);
  }

}
