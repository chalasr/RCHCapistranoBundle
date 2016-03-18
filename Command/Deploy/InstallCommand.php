<?php

/*
* This file is part of RCH/CapistranoBundle.
*
* Robin Chalas <robin.chalas@gmail.com>
*
* For more informations about license, please see the LICENSE
* file distributed in this source code.
*/

namespace RCH\CapistranoBundle\Command\Deploy;

use RCH\CapistranoBundle\Generator\CapfileGenerator;
use RCH\CapistranoBundle\Generator\GemfileGenerator;
use RCH\CapistranoBundle\Util\LocalizableTrait as Localizable;
use RCH\CapistranoBundle\Util\OutputWritableTrait as OutputWritable;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
    use OutputWritable, Localizable;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('rch:deploy:install')
        ->setDescription('Build installation files for capistrano requirements');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDir = $this->getRootDir();
        $bundleConfigDir = $this->getPublishedConfigDir();
        $capistranoDir = $this->getCapistranoDir();
        $fs = new Filesystem();

        $this->sayWelcome($output);

        if (false === $fs->exists($bundleConfigDir)) {
            $fs->mkdir(array(
                $bundleConfigDir,
                $bundleConfigDir.'/task',
                $bundleConfigDir.'/staging',
            ));
        }

        if (true === $fs->exists($capistranoDir)) {
            $fs->remove($capistranoDir);
        }

        $output->writeln(['', ' > generating <comment>./Capfile</comment>', ' > generating <comment>./Gemfile</comment>', '']);

        $requirements = ['capistrano/setup', 'capistrano/deploy', 'capistrano/composer', 'capistrano/symfony'];
        $gems = ["'capistrano', '~> 1.0.0.rc1'", "capistrano-symfony', '~> 1.0.0.rc1'", 'capistrano-rbenv'];
        $capfile = new CapfileGenerator($requirements, $rootDir);
        $gemfile = new GemfileGenerator($gems, $rootDir);
        $this->generate($capfile);
        $this->generate($gemfile);

        $output->writeln([
            '<info>Successfully generated </info><comment>Capfile</comment><info> and </info><comment>Gemfile</comment>',
            '',
        ]);
    }
}
