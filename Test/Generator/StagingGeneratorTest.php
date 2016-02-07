<?php

namespace RCH\CapistranoBundle\Test\Generator;

use RCH\CapistranoBundle\Generator\StagingGenerator;

/**
 * Tests the StagingGenerator.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class StagingGeneratorTest extends \PHPUnit_Framework_TestCase
{
    $params = array(
        'domain' => 'ssh_host',
        'user' => 'ssh_user',
        'keys' => '/home/ssh_user/.ssh/id_rsa',
        'forward_agent' => 'false',
        'auth_methods' => 'publickey password',
        'deploy_to' => '/path/to/deploy',
    );

    $newStaging = new StagingGenerator($params, $stagingPath, 'test.rb');
    $this->generate($newStaging);
}
