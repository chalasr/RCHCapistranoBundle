<?php

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
       $welcome = $formatter->formatBlock("Welcome to chalasdev/capistrano", "title", true);
       $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr']);
       $output->writeln(['', " > generating <comment>./Capfile</comment>", " > generating <comment>./Gemfile</comment>", '']);
       if (false !== $fs->exists("{$root}/../config")) {
           $fs->remove("{$root}/../config");
       }
       if (false === $fs->exists("{$root}/../Capfile")) {
           $fs->touch("{$root}/../Capfile");
           $capfile =
           "require 'capistrano/setup'".PHP_EOL.
           "require 'capistrano/deploy'".PHP_EOL.
           "require 'capistrano/composer'".PHP_EOL.
           "require 'capistrano/symfony'".PHP_EOL;
           $fs->dumpFile("{$root}/../Capfile", $capfile);
       }
       if (false === $fs->exists("{$root}/../Gemfile")) {
           $fs->touch("{$root}/../Gemfile");
           $gems =
           "gem 'capistrano'".PHP_EOL.
           "gem 'capistrano-symfony'".PHP_EOL.
           "gem 'capistrano-rbenv'".PHP_EOL;
           $fs->dumpFile("{$root}/../Gemfile", $gems);
       }
       $fs->mirror(
           "{$root}/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/capistrano", //production
           "{$root}/../config/"
       );
       $output->writeln(["<info>Successfully generated </info><comment>Capfile</comment><info> and </info><comment>Gemfile</comment>", '']);
   }

}
