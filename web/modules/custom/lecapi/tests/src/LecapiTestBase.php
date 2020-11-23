<?php

namespace Drupal\Tests\lecapi;

use Drupal\Tests\RandomGeneratorTrait;
use weitzman\DrupalTestTraits\Entity\MediaCreationTrait;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A useful base class for functional tests.
 */
class LecapiTestBase extends ExistingSiteBase {
  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }
  use UserCreationTrait {
    createRole as drupalCreateRole;
    createUser as drupalCreateUser;
  }
  use MediaCreationTrait {
    createMedia as drupalCreateMedia;
  }
  use TaxonomyCreationTrait;
  use RandomGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  protected $runTestInSeparateProcess = TRUE;

  /**
   * The database prefix of this test run.
   *
   * @var string
   */
  protected $databasePrefix;

  /**
   * Configuration service.
   *
   * @param string $name
   *   Configuration name.
   *
   * @return \Drupal\Core\Config\Config
   *   The configuration object.
   */
  protected function config($name) {
    return $this->container->get('config.factory')->getEditable($name);
  }

}
