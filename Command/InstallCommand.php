<?php

/*
* This file is part of Chalasdev/CapistranoBundle.
*
* https://github.com/chalasr/CapistranoBundle
* Robin Chalas <robin.chalas@gmail.com>
*
*/

namespace Chalasdev\CapistranoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('capistrano:install')
        ->setDescription('Setup capistrano deployment configuration in interactive mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $formatter = $this->getHelper('formatter');
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $root = $this->getContainer()->get('kernel')->getRootDir();
        $welcome = $formatter->formatBlock('Welcome to chalasdev/capistrano', 'title', true);
        $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr']);
        $output->writeln(['', ' > generating <comment>./Capfile</comment>', ' > generating <comment>./Gemfile</comment>', '']);
        if (false !== $fs->exists("{$root}/../config")) {
            $fs->remove("{$root}/../config");
        }
        if (false === $fs->exists("{$root}/../Capfile")) {
            $fs->touch("{$root}/../Capfile");
            $requirements = ['capistrano/setup', 'capistrano/deploy', 'capistrano/composer', 'capistrano/symfony'];
            $fs->dumpFile("{$root}/../Capfile", $this->prepareCapfile($requirements));
        }
        if (false === $fs->exists("{$root}/../Gemfile")) {
            $fs->touch("{$root}/../Gemfile");
            $gems = ['capistrano', 'capistrano-symfony', 'capistrano-rbenv'];
            $fs->dumpFile("{$root}/../Gemfile", $this->prepareGemfile($gems));
        }
        $output->writeln(['<info>Successfully generated </info><comment>Capfile</comment><info> and </info><comment>Gemfile</comment>', '']);
    }

    protected function prepareGemfile(array $gems)
    {
        $GemfileContent = "";
        foreach ($gems as $gem) {
            $GemfileContent .= "gem '{$gem}'".PHP_EOL;
        }

        return $GemfileContent;
    }

    protected function prepareCapfile(array $requirements)
    {
        $CapfileContent = "";
        foreach ($requirements as $gem) {
            $CapfileContent .= "require '{$gem}'".PHP_EOL;
        }

        return $CapfileContent;
    }
}
