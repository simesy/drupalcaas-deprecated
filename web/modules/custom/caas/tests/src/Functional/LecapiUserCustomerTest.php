<?php

namespace Drupal\Tests\caas\Functional;

use Drupal\caas\Ia;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\caas\CaasTestBase;

/**
 * Class define any test case for user.
 */
class CaasUserCustomerTest extends CaasTestBase {

  /**
   * Test customer user not belong workbench section unable create content.
   */
  public function testCustomerCreatingContent() {
    // Create an customer user without workbench access, then log in.
    $customer_user = $this->getCustomer();
    $this->drupalLogin($customer_user);
    // Test customer user unable to create any content.
    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $node_type) {
      $this->drupalGet('/node/add/' . $node_type->id());
      $this->assertSession()->statusCodeEquals(403);
    }
    $this->drupalLogout();
  }

  /**
   * Test customer user belong specific workbench section able to create content.
   */
  public function testCustomerWithWorkbenchAccessCreatingContent() {
    // Create an customer user, add to workbench section 'Demo' then login.
    $customer_user = $this->getCustomer();
    $site_term = $this->getSiteTerm();
    $this->addUserToSite($customer_user, $site_term);
    $this->drupalLogin($customer_user);
    // Test customer user able to create any content.
    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $node_type) {
      $this->drupalGet('/node/add/' . $node_type->id());
      $this->assertSession()->statusCodeEquals(200);
    }
    $this->drupalLogout();
  }

  /**
   * Test customer user belong specific workbench section able to edit content.
   */
  public function testCustomerWithWorkbenchAccessEditingContent() {
    // Create customer 1 assign to section id #4.
    $customer_user_1 = $this->getCustomer();
    $customer_user_2 = $this->getCustomer();
    $site_term_1 = $this->getSiteTerm();
    $site_term_2 = $this->getSiteTerm();
    $this->addUserToSite($customer_user_1, $site_term_1);
    $this->addUserToSite($customer_user_2, $site_term_2);

    // Customer 1 create a node page.
    $node_page_1 = $this->createNode([
      'title' => 'Page 1 test Edit - customer 1',
      'type' => 'page',
      'uid' => $customer_user_1->id(),
      Ia::FIELD_SITE => $site_term_1->id(),
    ]);
    $node_page_1->save();

    // Customer 1 should be access edit node page 1.
    $this->drupalLogin($customer_user_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();

    // Customer 2 should not access edit page 1.
    $this->drupalLogin($customer_user_2);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(403);

    // Add customer 2 to site 2 then customer 2 should be access edit.
    $this->addUserToSite($customer_user_2, $site_term_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test customer user user belong specific workbench section able to delete content.
   */
  public function testCustomerWithWorkbenchAccessDeletingContent() {
    // Create customer 1 assign to section id #4.
    $customer_user_1 = $this->getCustomer();
    $customer_user_2 = $this->getCustomer();
    $site_term_1 = $this->getSiteTerm();
    $site_term_2 = $this->getSiteTerm();
    $this->addUserToSite($customer_user_1, $site_term_1);
    $this->addUserToSite($customer_user_2, $site_term_2);

    // Customer 1 create a node page.
    $node_page_1 = $this->createNode([
      'title' => 'Page 1 test Delete - customer 1',
      'type' => 'page',
      'uid' => $customer_user_1->id(),
      Ia::FIELD_SITE => $site_term_1->id(),
    ]);
    $node_page_1->save();

    // Customer 2 should be access edit node page 1.
    $this->drupalLogin($customer_user_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/delete');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();

    // Customer 2 should not access edit page 1.
    $this->drupalLogin($customer_user_2);
    $this->drupalGet('/node/' . $node_page_1->id() . '/delete');
    $this->assertSession()->statusCodeEquals(403);

    // Add customer 2 to site 2 then customer 2 should be access delete.
    $this->addUserToSite($customer_user_2, $site_term_1);
    $this->drupalGet('/node/' . $node_page_1->id() . '/delete');
    $this->assertSession()->statusCodeEquals(200);
  }

}
