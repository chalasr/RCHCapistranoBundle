<?php

/*
* This file is part of RCH/CapistranoBundle.
*
* Robin Chalas <robin.chalas@gmail.com>
*
* For more informations about license, please see the LICENSE
* file distributed in this source code.
*/

namespace RCH\CapistranoBundle\Command;

use RCH\CapistranoBundle\Generator\CapfileGenerator;
use RCH\CapistranoBundle\Generator\GemfileGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Setups Capfile & Gemfile.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class InstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('capistrano:install')
        ->setDescription('Setup capistrano deployment configuration in interactive mode');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $formatter = $this->getHelper('formatter');
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $root = $this->getContainer()->get('kernel')->getRootDir();

        $welcome = $formatter->formatBlock('Welcome to chalasr/capistrano', 'title', true);
        $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr']);
        $output->writeln(['', ' > generating <comment>./Capfile</comment>', ' > generating <comment>./Gemfile</comment>', '']);

        if (false !== $fs->exists("{$root}/../config")) {
            $fs->remove("{$root}/../config");
        }

        $requirements = ['capistrano/setup', 'capistrano/deploy', 'capistrano/composer', 'capistrano/symfony'];
        $gems = ['capistrano', 'capistrano-symfony', 'capistrano-rbenv'];
        $capfile = new CapfileGenerator($requirements, $root);
        $gemfile = new GemfileGenerator($gems, $root);
        $capfile->generate();
        $gemfile->generate();

        $output->writeln([
            '<info>Successfully generated </info><comment>Capfile</comment><info> and </info><comment>Gemfile</comment>',
            '',
        ]);
    }
}
