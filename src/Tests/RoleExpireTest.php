<?php
declare(strict_types = 1);

namespace Drupal\role_expire\Tests;

use Drupal\role_expire\Entity\RoleExpire;
use Drupal\simpletest\KernelTestBase;

/**
 * @group role_expire
 */
class RoleExpireTest extends KernelTestBase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();
        /** @var \Drupal\Core\Extension\ModuleInstaller $installer */
        $installer = $this->container->get('module_installer');
        // the modules are only loaded, but not installed. Modules have to be installed manually, if needed.
        $installer->install(['role_expire']);
    }

    public function testUserId()
    {
        $re = RoleExpire::create();
        $re->setUserId(666);

        $this->assertEqual(666, $re->getUserId());
    }

    public function testRoleId()
    {
        $re = RoleExpire::create();
        $re->setRoleId('akbar');

        $this->assertEqual('akbar', $re->getRoleId());
    }

    public function testExpire()
    {
        $re = RoleExpire::create();
        $time = strtotime('20-07-2007');
        $re->setExpire($time);

        $this->assertEqual($time, $re->getExpire());
    }
}
