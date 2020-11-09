<?php

namespace Drupal\backup_migrate\Core\Service;

/**
 * A very simple service locator.
 *
 * Note: Does not handle dependency injection but could be replaced by a more
 * complex application specific version which does.
 *
 * @package Drupal\backup_migrate\Core\Service
 */
class ServiceManager implements ServiceManagerInterface {

  /**
   * @var array
   */
  protected $services;

  /**
   * @var array
   */
  protected $clients;

  /**
   * The constructor. Initialise the list of services.
   */
  public function __construct() {
    $this->services = [];

    // Allow the locator to inject itself.
    $this->services['ServiceManager'] = $this;
  }

  /**
   * Add a fully configured service to the service locator.
   *
   * @param string $type
   *   The service type identifier.
   * @param mixed $service
   */
  public function add($type, $service) {
    $this->services[$type] = $service;

    // Add this service as a client so it can have dependencies injected.
    $this->addClient($service);

    // Update any plugins that have already had this service injected.
    if (isset($this->clients[$type])) {
      foreach ($this->clients[$type] as $client) {
        $client->{'set' . $type}($service);
      }
    }
  }

  /**
   * Retrieve a service from the locator.
   *
   * @param string $type
   *   The service type identifier.
   *
   * @return mixed
   */
  public function get($type) {
    return $this->services[$type];
  }

  /**
   * Get an array of keys for all available services.
   *
   * @return array
   */
  public function keys() {
    return array_keys($this->services);
  }

  /**
   * Inject all available services into the give plugin.
   *
   * @param object $client
   */
  public function addClient($client) {
    // Inject available services.
    foreach ($this->keys() as $type) {
      if (method_exists($client, 'set' . $type) && $service = $this->get($type)) {
        // Save the plugin so it can be updated if this service is updated.
        $this->clients[$type][] = $client;

        $client->{'set' . $type}($service);
      }
    }
  }

}
