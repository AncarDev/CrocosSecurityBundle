<?php
namespace Crocos\SecurityBundle\Tests\Exception;

use Crocos\SecurityBundle\Annotation\SecureConfig;

class SecureConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $secure = new SecureConfig([
            'domain'        => 'secured',
            'httpsRequired' => 'https',
            'auth'          => 'session',
            'roleManager'   => 'session',
            'forward'       => 'AdminController::loginAction',
        ]);

        $this->assertEquals('secured', $secure->domain());
        $this->assertEquals('https', $secure->httpsRequired());
        $this->assertEquals('session', $secure->auth());
        $this->assertEquals('session', $secure->roleManager());
        $this->assertEquals('AdminController::loginAction', $secure->forward());
    }
}
