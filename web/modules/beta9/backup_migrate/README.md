# Backup and Migrate for Drupal 8 and 9

This is a rewrite of Backup and Migrate for Drupal 8 and 9.

## Installation

### Install without composer

* Download the zip or tgz archive of the latest release from the project page: https://www.drupal.org/project/backup_migrate
* Extra the archive and rename it so that there is just a directory called `backup_migrate`.
* Move the directory to the site's `modules/contrib` directory.

### Install using composer

`composer require drupal/backup_migrate`

### Optional: php-encryption

The backups may be encrypted using the defuse/php-encryption library. This must be installed using Composer, it cannot be downloaded without using Composer.

`composer require defuse/php-encryption`

## Related modules

The following modules can extend the functionality of your backup solution:

* Backup & Migrate: Flysystem
  https://www.drupal.org/project/backup_migrate_flysystem
  Provides a wrapper around the Flysystem abstraction system which allows use of
  a wide variety of backup destinations without additional changes to the B&M
  module itself. Please see that module's README.md file for details.
