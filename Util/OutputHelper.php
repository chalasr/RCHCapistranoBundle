<?php

/*
* This file is part of RCH/CapistranoBundle.
*
* Robin Chalas <robin.chalas@gmail.com>
*
* For more informations about license, please see the LICENSE
* file distributed in this source code.
*/

namespace RCH\CapistranoBundle\Util;

use RCH\CapistranoBundle\Generator\Handler;
use RCH\CapistranoBundle\Generator\AbstractGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides helper methods.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
trait OutputHelper
{
    /**
     * Writes stylized welcome message in Output.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function sayWelcome(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $welcome = $formatter->formatBlock('Welcome to chalasr/capistrano', 'title', true);
        $output->writeln(array(
            '',
            $welcome,
            '',
            'Standardizes deployment as part of your development workflow, built on top of <comment>capistrano/symfony</comment> rubygem .',
            'Created by Robin Chalas - github.com/chalasr',
            '',
        ));
    }

    public function generate(AbstractGenerator $generator)
    {
        $callback = function () use ($generator) {
            $handler = Handler::create($generator);
            $handler->generate();
        };

        return $callback();
    }

    /**
     * Get application root directory.
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->getContainer()->get('kernel')->getRootDir();
    }

    /**
     * Get bundle directory.
     *
     * @return string
     */
    public function getBundleDir()
    {
        return $this->getRootDir() . '/../vendor/chalasr/capistrano-bundle/RCH/CapistranoBundle';
    }

    /**
     * Get published config directory.
     *
     * @return string
     */
    public function getPublishedConfigDir()
    {
        return $this->getRootdir() . '/config/rch';
    }

    /**
     * Get published config directory.
     *
     * @return string
     */
    public function getCapistranoDir()
    {
        return $this->getRootdir() . '/../config';
    }
}
