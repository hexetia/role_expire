<?php
declare(strict_types = 1);

namespace Drupal\role_expire\Tests;

use Drupal\role_expire\Entity\RoleExpire;
use Drupal\simpletest\KernelTestBase;
use Drupal\simpletest\UserCreationTrait;

/**
 * @group role_expire
 */
class RoleManagerServiceTest extends KernelTestBase
{
    const ROLE_TEST_ID = 'vip';

    use UserCreationTrait {
        createUser as drupalCreateUser;
        createRole as drupalCreateRole;
    }

    /** @var \Drupal\role_expire\RoleManagerService */
    private $roleExpireManager;

    public function setUp()
    {
        parent::setUp();
        /** @var \Drupal\Core\Extension\ModuleInstaller $installer */
        $installer = $this->container->get('module_installer');
        // the modules are only loaded, but not installed. Modules have to be installed manually, if needed.
        $installer->install(['role_expire']);

        $this->roleExpireManager = \Drupal::service('role_expire__manager');
    }

    /**
     * @cover addTime()
     */
    public function testAddTime()
    {
        $VIP_TIME = 666;
        $HALF_VIP_TIME = $VIP_TIME / 2;
        $user = $this->drupalCreateUser();
        $roleExpire = $this->roleExpireManager->addTime($user, self::ROLE_TEST_ID, $HALF_VIP_TIME);
        // verify if time match
        $this->assertEqual($HALF_VIP_TIME, $roleExpire->getExpire());

        // test database integrity
        $roleExpire = $this->roleExpireManager->addTime($user, self::ROLE_TEST_ID, $HALF_VIP_TIME);
        // the value must be equal to the sum of the two previous values
        $this->assertEqual($roleExpire->getExpire(), $VIP_TIME);
    }

    /**
     * @cover expireRole()
     */
    public function testExpireRole()
    {
        $user = $this->drupalCreateUser();
        $roleExpire = $this->roleExpireManager->addTime($user, self::ROLE_TEST_ID, 667);
        $roleExpireID = $roleExpire->id();

        $roleExpire->delete();
        // verify if use has the role
        $this->assertTrue($user->hasRole(self::ROLE_TEST_ID));

        // remove o papel do usuário e testa se o usuário ainda tem aquele papel
        $this->roleExpireManager->expireRole($user, self::ROLE_TEST_ID);
        $this->assertTrue(!($user->hasRole(self::ROLE_TEST_ID)));

        $this->assertNull(RoleExpire::load($roleExpireID));
    }

    /**
     * @cover getRoleExpire()
     */
    public function testGetRoleExpire()
    {
        $user = $this->drupalCreateUser();
        $this->roleExpireManager->addTime($user, self::ROLE_TEST_ID, 667);
        $this->assertNotNull($this->roleExpireManager->getRoleExpire($user, self::ROLE_TEST_ID)->id());
    }

    /**
     * @cover getExpiredRoles()
     */
    public function testGetExpiredRoles()
    {
        $user = $this->drupalCreateUser();
        $this->roleExpireManager->addTime($user, self::ROLE_TEST_ID, strtotime('07-07-2007'));
        $this->assertTrue(!empty($this->roleExpireManager->getExpiredRoles()));
    }
}
