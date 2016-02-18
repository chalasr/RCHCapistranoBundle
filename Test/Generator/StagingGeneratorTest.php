<?php

namespace RCH\CapistranoBundle\Test\Generator;

use RCH\CapistranoBundle\Generator\Handler;
use RCH\CapistranoBundle\Generator\StagingGenerator;

/**
 * Tests the StagingGenerator.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class StagingGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $path;

    /** @var array */
    protected $params;

    /** @var string */
    protected $expected = "
###########################################################################
#             This file is generated by RCH/CapistranoBundle              #
#                                                                         #
#               https://github.com/chalasr/CapistranoBundle               #
#                  Robin Chalas <robin.chalas@gmail.com>                  #
#                                                                         #
###########################################################################

# Production
server 'rch.fr',
user: 'chalasr',
ssh_options: {
    user: 'chalasr',
    keys: %w(/home/ssh_user/.ssh/id_rsa),
    forward_agent: false,
    auth_methods: %w(publickey password),
}

set(:deploy_to, '/var/www/html')";

    public function setUp()
    {
        $this->path = __DIR__.'/stagings/';
        $this->params = array(
            'domain' => 'rch.fr',
            'user' => 'chalasr',
            'keys' => '/home/ssh_user/.ssh/id_rsa',
            'forwardAgent' => 'false',
            'authMethods' => 'publickey password',
            'deployTo' => '/var/www/html',
        );
    }

    public function testGenerate()
    {
        $generator = new StagingGenerator($this->params, $this->path, 'test.rb');
        $generator->open();
        $generator->write();
        $generator->close();

        $this->assertEquals($this->expected, file_get_contents($this->path.'test.rb'));
    }

    public function tearDown()
    {
        unlink($this->path.'test.rb');
    }
}
