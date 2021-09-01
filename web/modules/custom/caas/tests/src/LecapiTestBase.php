<?php

namespace Drupal\Tests\caas;

use Drupal\caas\Ia;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\jsonapi\Functional\JsonApiRequestTestTrait;
use Drupal\user\UserInterface;
use Drupal\Tests\RandomGeneratorTrait;
use weitzman\DrupalTestTraits\Entity\MediaCreationTrait;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;
use weitzman\DrupalTestTraits\ScreenShotTrait;

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
  use ScreenShotTrait;
  use JsonApiRequestTestTrait;

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
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Workbench Access schema.
   *
   * @var \Drupal\workbench_access\Entity\AccessSchemeInterface
   */
  protected $workbenchAccessSchema;

  /**
   * User section storage.
   *
   * @var \Drupal\workbench_access\UserSectionStorage
   */
  protected $workbenchAccessUserStorage;

  /**
   * Setup function for test case.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->workbenchAccessUserStorage = \Drupal::service('workbench_access.user_section_storage');
    $this->workbenchAccessSchema = $this->entityTypeManager->getStorage('access_scheme')->load(Ia::FIELD_SITE);
  }

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

  /**
   * Create a customer.
   *
   * @return \Drupal\user\UserInterface
   *   A new user entity.
   */
  protected function getCustomer() {
    $user = $this->drupalCreateUser();
    $user->addRole('customer');
    $user->save();
    return $user;
  }

  /**
   * Create an administrator.
   *
   * @return \Drupal\user\UserInterface
   *   A new user entity.
   */
  protected function getAdministrator() {
    $user = $this->drupalCreateUser();
    $user->addRole('administrator');
    $user->save();
    return $user;
  }

  /**
   * Return a new site term.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   A new term that represents a new site.
   */
  protected function getSiteTerm() {
    $vocab = Vocabulary::load(Ia::FIELD_SITE);
    $term = $this->createTerm($vocab);
    return $term;
  }

  /**
   * Return a new site term.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   A new term that represents a new site.
   */
  protected function getConsumer() {
    $consumer = $this->entityTypeManager->getStorage('consumer')->create([
      'label' => 'Test consumer ' . $this->randomString(),
    ]);
    $this->markEntityForCleanup($consumer);
    return $consumer;
  }

  /**
   * Assign a user to a site.
   *
   * @param \Drupal\user\UserInterface $user
   *   A user entity.
   * @param \Drupal\taxonomy\Entity\Term $siteTerm
   *   A term in the site taxonomy.
   */
  protected function addUserToSite(UserInterface $user, Term $siteTerm) {
    $this->workbenchAccessUserStorage->addUser($this->workbenchAccessSchema, $user, [$siteTerm->id()]);
  }

}
