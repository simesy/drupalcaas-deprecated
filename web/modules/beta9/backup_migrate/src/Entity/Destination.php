<?php

namespace Drupal\backup_migrate\Entity;

/**
 * Defines the Backup Destination entity.
 *
 * @ConfigEntityType(
 *   id = "backup_migrate_destination",
 *   label = @Translation("Backup Destination"),
 *   module = "backup_migrate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "type" = "type",
 *     "config" = "config"
 *   },
 *   admin_permission = "administer backup and migrate",
 *   handlers = {
 *     "list_builder" = "Drupal\backup_migrate\Controller\DestinationListBuilder",
 *     "form" = {
 *       "default" = "Drupal\backup_migrate\Form\DestinationForm",
 *       "delete" = "Drupal\backup_migrate\Form\EntityDeleteForm"
 *     },
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/development/backup_migrate/settings/destination/edit/{backup_migrate_destination}",
 *     "delete-form" = "/admin/config/development/backup_migrate/settings/destination/delete/{backup_migrate_destination}",
 *     "collection" = "/admin/config/development/backup_migrate/settings/destination",
 *     "backups" = "/admin/config/development/backup_migrate/settings/destination/backups/{backup_migrate_destination}",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "type",
 *     "config"
 *   }
 * )
 */
class Destination extends WrapperEntityBase {

  /**
   * Return the plugin manager.
   *
   * @return string
   */
  public function getPluginManager() {
    return \Drupal::service('plugin.manager.backup_migrate_destination');
  }

}
