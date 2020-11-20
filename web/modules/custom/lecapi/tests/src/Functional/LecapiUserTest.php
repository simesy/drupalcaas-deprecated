<?php

namespace Drupal\Tests\lecapi\LecapiUserTest;

use Drupal\lecapi\Ia;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\RandomGeneratorTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class define any test case for user.
 */
class LecapiUserTest extends ExistingSiteBase {

  use RandomGeneratorTrait;

  /**
   * User section storage.
   *
   * @var \Drupal\workbench_access\UserSectionStorage
   */
  protected $userStorage;

  /**
   * Workbench Access schema.
   *
   * @var \Drupal\workbench_access\Entity\AccessSchemeInterface
   */
  protected $workbenchAccessSchema;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Setup function for test case.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->userStorage = \Drupal::service('workbench_access.user_section_storage');
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->workbenchAccessSchema = $this->entityTypeManager->getStorage('access_scheme')->load('site');
  }

  /**
   * Test customer user not belong workbench section unable create content.
   */
  public function testCustomerCreatingContent() {
    // Create an customer user then login.
    $customer_user = $this->createUser([], 'customer_normal', FALSE, ['roles' => ['customer']]);
    $this->drupalLogin($customer_user);
    // Test customer user unable to create any content.
    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $node_type) {
      $this->drupalGet('/node/add/' . $node_type->id());
      $this->assertSession()->statusCodeNotEquals(200);
    }
  }

  /**
   * Test customer user belong specific workbench section able to create content.
   */
  public function testCustomerWithWorkbenchAccessCreatingContent() {
    // Create an customer user, add to workbench section 'Demo' then login.
    $customer_user = $this->createUser([], 'customer_workbench_access', FALSE, ['roles' => ['customer']]);
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user, [4]);
    $this->drupalLogin($customer_user);
    // Test customer user able to create any content.
    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $node_type) {
      $this->drupalGet('/node/add/' . $node_type->id());
      $this->assertSession()->statusCodeEquals(200);
    }
  }

  /**
   * Test customer user belong specific workbench section able to edit content.
   */
  public function testCustomerWithWorkbenchAccessEditingContent() {
    // Create customer 1 assign to section id #4.
    $customer_user_1 = $this->createUser([], 'customer1-' . $this->randomString(), FALSE, ['roles' => ['customer']]);
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user_1, [4]);
    // Create customer 2 assign to section id #1.
    $customer_user_2 = $this->createUser([], 'customer2-' . $this->randomString(), FALSE, ['roles' => ['customer']]);
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user_2, [1]);
    // Customer 1 create a node page.
    $node_page_1 = $this->createNode([
      'title' => 'Page 1 test Edit - customer 1',
      'type' => 'page',
      'uid' => $customer_user_1->id(),
    ]);
    $node_page_1->set(Ia::FIELD_SITE, [4]);
    $node_page_1->save();
    // Customer 2 should be access edit node page 1.
    $this->drupalLogin($customer_user_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();
    // Customer 2 should not access edit page 1.
    $this->drupalLogin($customer_user_2);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeNotEquals(200);
    // Add customer 2 to section #4 then customer 2 should be access edit.
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user_2, [4]);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test customer user user belong specific workbench section able to delete content.
   */
  public function testCustomerWithWorkbenchAccessDeletingContent() {
    // Create customer 1 assign to section id #4.
    $customer_user_1 = $this->createUser([], 'customer1-' . $this->randomString(), FALSE, ['roles' => ['customer']]);
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user_1, [4]);
    // Create customer 2 assign to section id #1.
    $customer_user_2 = $this->createUser([], 'customer2-' . $this->randomString(), FALSE, ['roles' => ['customer']]);
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user_2, [1]);
    // Customer 1 create a node page.
    $node_page_1 = $this->createNode([
      'title' => 'Page 1 test Delete - customer 1',
      'type' => 'page',
      'uid' => $customer_user_1->id(),
    ]);
    $node_page_1->set(Ia::FIELD_SITE, [4]);
    $node_page_1->save();
    // Customer 2 should be access edit node page 1.
    $this->drupalLogin($customer_user_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/delete');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();
    // Customer 2 should not access edit page 1.
    $this->drupalLogin($customer_user_2);
    $this->drupalGet('/node/' . $node_page_1->id() . '/delete');
    $this->assertSession()->statusCodeNotEquals(200);
    // Add customer 2 to section #4 then customer 2 should be access edit.
    $this->userStorage->addUser($this->workbenchAccessSchema, $customer_user_2, [4]);
    $this->drupalGet('/node/' . $node_page_1->id() . '/delete');
    $this->assertSession()->statusCodeEquals(200);
  }

}
