<?php

namespace Drupal\backup_migrate\Core\Config;

/**
 * Provides an interface defining a backup source.
 */
interface ConfigInterface {

  /**
   * Get a setting value.
   *
   * @param string $key
   *   The key for the setting.
   * @param $default
   *   The default to return if there is no value set for this key.
   *
   * @return mixed
   *   The value of the setting.
   */
  public function get($key, $default = NULL);

  /**
   * Set a setting value.
   *
   * @param string $key
   *   The key for the setting.
   * @param mixed $value
   *   The value for the setting.
   */
  public function set($key, $value);

  /**
   * Determine if the given key has had a value set for it.
   *
   * @param $key
   *
   * @return bool
   */
  public function keyIsSet($key);

  /**
   * Get all settings as an associative array.
   *
   * @return array
   *   All of the settings in this profile
   */
  public function toArray();

  /**
   * Set all from an array.
   *
   * @param array $values
   *   An associative array of settings.
   */
  public function fromArray(array $values);

}
