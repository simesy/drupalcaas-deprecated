<?php

namespace Drupal\backup_migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\backup_migrate\SettingsProfileInterface;

/**
 * Defines the Settings Profile entity.
 *
 * @ConfigEntityType(
 *   id = "backup_migrate_settings",
 *   label = @Translation("Settings Profile"),
 *   module = "backup_migrate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   admin_permission = "administer backup and migrate",
 *   handlers = {
 *     "list_builder" = "Drupal\backup_migrate\Controller\SettingsProfileListBuilder",
 *     "form" = {
 *       "default" = "Drupal\backup_migrate\Form\SettingsProfileForm",
 *       "delete" = "Drupal\backup_migrate\Form\EntityDeleteForm"
 *     },
 *   },
 *   links = {
 *     "canonical" =
 *        "/admin/config/development/backup_migrate/settings/{backup_migrate_settings}",
 *     "add-form" = "/admin/config/development/backup_migrate/settings/add",
 *     "edit-form" = "/admin/config/development/backup_migrate/settings/{backup_migrate_settings}/edit",
 *     "delete-form" = "/admin/config/development/backup_migrate/settings/{backup_migrate_settings}/delete",
 *     "collection" = "/admin/config/development/backup_migrate/settings"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "config"
 *   }
 * )
 */
class SettingsProfile extends ConfigEntityBase implements SettingsProfileInterface {

  /**
   * The Settings Profile ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Settings Profile label.
   *
   * @var string
   */
  protected $label;

}
