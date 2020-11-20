<?php

namespace Drupal\Tests\lecapi\LecapiUserTest;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\RandomGeneratorTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class define any test case for user.
 */
class LecapiUserTest extends ExistingSiteBase {

  use RandomGeneratorTrait;

  /**
   * Test customer role create content.
   */
  public function testCustomerRoleCreatingContent() {
    // Create an customer user then login.
    $customer_user = $this->createUser([], 'Customer Name', ['roles' => ['customer']]);
    $this->drupalLogin($customer_user);
    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $node_type) {
      $this->drupalGet('/node/add/' . $node_type->id());
      $this->assertSession()->statusCodeEquals(200);
    }
  }

  /**
   * Test customer role edit content.
   */
  public function testCustomerRoleEditingContent() {
    // Create an customer user then login.
    $customer_user_1 = $this->createUser([], 'customer1-' . $this->randomString(), ['roles' => ['customer']]);
    $customer_user_2 = $this->createUser([], 'customer2-' . $this->randomString(), ['roles' => ['customer']]);

    // Customer 1 create a node page.
    $node_page_1 = $this->createNode([
      'title' => 'Page 1 - customer 1',
      'type' => 'page',
      'uid' => $customer_user_1->id(),
    ]);
    $node_page_1->set('site', [1]);
    // Customer should be access edit node page 1.
    $this->drupalLogin($customer_user_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();
    // Customer 2 should not access edit page 1.
    $this->drupalLogin($customer_user_2);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeNotEquals(200);
  }

}
