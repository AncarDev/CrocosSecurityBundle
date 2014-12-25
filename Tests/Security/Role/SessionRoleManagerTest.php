<?php

namespace Crocos\SecurityBundle\Tests\Security\Role;

use Crocos\SecurityBundle\Security\Role\SessionRoleManager;
use Phake;

class SessionRoleManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $session;
    protected $roleManager;

    protected function setUp()
    {
        $session = Phake::mock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $roleManager = new SessionRoleManager($session);
        $roleManager->setDomain('secured');

        $this->session = $session;
        $this->roleManager = $roleManager;
    }

    public function testHasRoleReturnsTrueIfEmptyRolesIsPasssed()
    {
        Phake::when($this->session)->get('secured/role/roles', [])->thenReturn([]);

        $this->assertTrue($this->roleManager->hasRole([]));
    }

    public function testHasRoleReturnsTrueIfAPassedRoleIsGranted()
    {
        Phake::when($this->session)->get('secured/role/roles', [])->thenReturn(['FOO', 'BAR']);

        $this->assertTrue($this->roleManager->hasRole('FOO'));
    }

    public function testHasRoleReturnsTrueIfPassedRolesContainAnyGrantedRole()
    {
        Phake::when($this->session)->get('secured/role/roles', [])->thenReturn(['FOO', 'BAR']);

        $this->assertTrue($this->roleManager->hasRole(['BAR', 'BAZ']));
    }

    public function testHasRoleReturnsFalseIfPassedRolesDoesNotContaineGrantedRole()
    {
        Phake::when($this->session)->get('secured/role/roles', [])->thenReturn(['FOO', 'BAR']);

        $this->assertFalse($this->roleManager->hasRole('XYZ'));
    }

    public function testSetRoles()
    {
        $this->roleManager->setRoles(['FOO', 'BAR']);

        Phake::verify($this->session)->set('secured/role/roles', ['FOO', 'BAR']);
    }

    public function testAddRoles()
    {
        Phake::when($this->session)->get('secured/role/roles', [])->thenReturn(['FOO', 'BAR']);

        $this->roleManager->addRoles('BAZ');

        Phake::verify($this->session)->set('secured/role/roles', ['FOO', 'BAR', 'BAZ']);
    }

    public function testGetRoles()
    {
        Phake::when($this->session)->get('secured/role/roles', [])->thenReturn(['FOO', 'BAR']);

        $this->assertEquals(['FOO', 'BAR'], $this->roleManager->getRoles());
    }
}
