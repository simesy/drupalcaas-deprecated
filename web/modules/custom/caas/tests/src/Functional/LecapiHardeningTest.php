<?php

namespace Drupal\Tests\caas\Functional;

use Drupal\Tests\caas\CaasTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests for messaging related to API hardening.
 */
class CaasHardeningTest extends CaasTestBase {

  /**
   * Ensure warnings when authenticated and anonymous users are given permissions.
   */
  public function testHardeningReportWarnings() {
    $anon_role = Role::load('anonymous');
    $auth_role = Role::load('authenticated');
    $api_role = Role::load('api');

    $administrator = $this->getAdministrator();
    $this->drupalLogin($administrator);

    // Default clean system.
    $this->drupalGet('/admin/reports/status');
    // Commenting out this test for now due to the oauth issue.
    // Anon users need permission to see content.
    // ($this->assertSession()->pageTextContains('The anonymous and authenticated roles have no permissions');).
    $this->assertSession()->pageTextContains('The API role is configured corrected');
    $this->assertSession()->pageTextContains('OAuth consumers are set up correctly');

    // Illegal role permissions.
    $api_role->grantPermission('administer users')->save();
    $anon_role->grantPermission('administer users')->save();
    $auth_role->grantPermission('bypass node access')->save();

    // Illegal consumer.
    $customer = $this->getCustomer();
    $consumer = $this->getConsumer();
    // Assign an illegal user (a customer in this case).
    $consumer->set('user_id', $customer);
    // Assign an illegal role.
    $consumer->set('roles', ['administrator']);
    $consumer->save();

    $this->drupalGet('/admin/reports/status');
    $this->assertSession()->pageTextContains('The api role should be restricted to read-only access');
    $this->assertSession()->pageTextContains('The anonymous and authenticated roles should not have any permissions');
    $this->assertSession()->pageTextContains('The user ' . $customer->get('name')->value . ' assigned to the consumer ' . $consumer->get('label')->value . ' should only have the API role');
    $this->assertSession()->pageTextContains('The consumer ' . $consumer->get('label')->value . ' should not be assigned to the role administrator');

    $api_role->revokePermission('administer users')->save();
    $anon_role->revokePermission('administer users')->save();
    $auth_role->revokePermission('bypass node access')->save();

    $this->drupalLogout();
  }

}
