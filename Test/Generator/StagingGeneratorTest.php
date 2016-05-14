<?php

/*
 * This file is part of the RCHCapistranoBundle.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RCH\CapistranoBundle\Test\Generator;

use RCH\CapistranoBundle\Generator\GeneratorInterface;
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

    /** @var string */
    protected $name;

    /** @var array */
    protected $params;

    /** @var string */
    protected $expected = "
# This file is part of the RCHCapistranoBundle.
#
# (c) Robin Chalas <robin.chalas@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.

server 'rch.fr',
user: 'chalasr',
ssh_options: {
    user: 'chalasr',
    keys: %w(/home/ssh_user/.ssh/id_rsa),
    forward_agent: false,
    auth_methods: %w(publickey password),
}

set :deploy_to, '/var/www/html'
set :repo_url,  'git@github.com:chalasr/RCHCapistranoBundle'
";

    public function setUp()
    {
        $this->path = sys_get_temp_dir().'/stagings/';
        $this->name = 'test';
        $this->params = array(
            'domain'       => 'rch.fr',
            'user'         => 'chalasr',
            'keys'         => '/home/ssh_user/.ssh/id_rsa',
            'forwardAgent' => 'false',
            'authMethods'  => 'publickey password',
            'deployTo'     => '/var/www/html',
            'repoUrl'      => 'git@github.com:chalasr/RCHCapistranoBundle'
        );
    }

    public function testGenerate()
    {
        $generator = new StagingGenerator($this->params, $this->path, $this->name);
        $this->generateStaging($generator);

        $this->assertEquals($this->expected, file_get_contents(sprintf('%s/%s.rb', $this->path, $this->name)));
    }

    private function generateStaging(GeneratorInterface $generator)
    {
        $generator->open();
        $generator->write();
        $generator->close();
    }

    public function tearDown()
    {
        unlink(sprintf('%s/%s.rb', $this->path, $this->name));
        rmdir($this->path);
    }
}
