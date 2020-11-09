<?php

namespace Drupal\backup_migrate\Core\Config;

use Drupal\backup_migrate\Core\Translation\TranslatableTrait;

/**
 * A configurable object. Manages injection and access to a config object.
 *
 * @package Drupal\backup_migrate\Core\Config
 */
trait ConfigurableTrait {

  use TranslatableTrait;

  /**
   * The object's configuration object.
   *
   * @var \Drupal\backup_migrate\Core\Config\ConfigInterface
   */
  protected $config;

  /**
   * The initial configuration.
   *
   * These configuration options can be overriden by the config options but will
   * not be overwritten. If the object is re-configured after construction any
   * missing configuration options will revert to these values.
   *
   * @var \Drupal\backup_migrate\Core\Config\ConfigInterface
   */
  protected $init;

  /**
   * @param ConfigInterface|array $init
   *   The initial values for the configurable item.
   */
  public function __construct($init = []) {
    if (is_array($init)) {
      $init = new Config($init);
    }
    $this->init = $init;

    // Set the config to a blank object to populate all values with the initial
    // and default values.
    $this->setConfig(new Config());
  }

  /**
   * Set the configuration for all plugins.
   *
   * @param ConfigInterface $config
   *   A configuration object containing only configuration for all plugins.
   */
  public function setConfig(ConfigInterface $config) {
    // Set the configuration object to the one passed in.
    $this->config = $config;

    // Add the init/default values to the config object so they will always
    // exist.
    // @todo Make this cascade happen when the config key is requested.
    // That will allow read-only or runtime generation config object to be
    // passed. This would work by creating a CascadeConfig object which takes
    // an array of ConfigInterface objects and queries each in order to find
    // the given key.
    $defaults = $this->configDefaults();
    $init = $this->init;
    foreach ([$init, $defaults] as $config_object) {
      foreach ($config_object->toArray() as $key => $value) {
        if (!$this->config->keyIsSet($key)) {
          $this->config->set($key, $value);
        }
      }
    }
  }

  /**
   * Get the configuration object for this item.
   *
   * @return \Drupal\backup_migrate\Core\Config\ConfigInterface
   */
  public function config() {
    return $this->config ? $this->config : new Config();
  }

  /**
   * Get the default values for the plugin.
   *
   * @return \Drupal\backup_migrate\Core\Config\Config
   */
  public function configDefaults() {
    return new Config();
  }

  /**
   * Get a default (blank) schema.
   *
   * @param array $params
   *   The parameters including:
   *    - operation - The operation being performed, will be one of:
   *      - 'backup': Configuration needed during a backup operation
   *      - 'restore': Configuration needed during a restore
   *      - 'initialize': Core configuration always needed by this item.
   *
   * @return array
   */
  public function configSchema(array $params = []) {
    return [];
  }

  /**
   * Get any validation errors in the config.
   *
   * @param array $params
   *
   * @return array
   */
  public function configErrors(array $params = []) {
    $out = [];

    // Do some basic validation based on length and regex matching.
    $schema = $this->configSchema($params);

    // Check each specified field.
    foreach ($schema['fields'] as $key => $field) {
      $value = $this->confGet($key);

      // Check if it's required.
      if (!empty($field['required']) && empty($value)) {
        $out[] = new ValidationError($key, $this->t('%title is required.'), ['%title' => $field['title']]);
      }

      // Check it for length.
      if (!empty($field['min_length']) && strlen($value) < $field['min_length']) {
        $out[] = new ValidationError($key, $this->t('%title must be at least %count characters.'), ['%title' => $field['title'], '%count' => $field['min_length']]);
      }
      if (!empty($field['max_length']) && strlen($value) > $field['max_length']) {
        $out[] = new ValidationError($key, $this->t('%title must be at no more than %count characters.'), ['%title' => $field['title'], '%count' => $field['max_length']]);
      }

      // Check for the regular expression match.
      if (!empty($field['must_match']) && !preg_match($field['must_match'], $value)) {
        if (!empty($field['must_match_error'])) {
          $out[] = new ValidationError($key, $field['must_match_error'], ['%title' => $field['title']]);
        }
        else {
          $out[] = new ValidationError($key, $this->t('%title contains invalid characters.'), ['%title' => $field['title']]);
        }
      }
    }
    return $out;
  }

  /**
   * Get a specific value from the configuration.
   *
   * @param string $key
   *   The configuration object key to retrieve.
   *
   * @return mixed
   *   The configuration value.
   */
  public function confGet($key) {
    return $this->config()->get($key);
  }

}
