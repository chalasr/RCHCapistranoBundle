<?php

/*
 * This file is part of the RCHCapistranoBundle.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RCH\CapistranoBundle\Command\Deploy;

use RCH\CapistranoBundle\Generator\CapfileGenerator;
use RCH\CapistranoBundle\Generator\GemfileGenerator;
use RCH\CapistranoBundle\Util\CanGenerateTrait as CanGenerate;
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
    use OutputWritable, Localizable, CanGenerate;

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
        $fs = new Filesystem();

        $this->sayWelcome($output);

        if (false === $fs->exists($bundleConfigDir)) {
            $fs->mkdir(array(
                $bundleConfigDir,
                $bundleConfigDir.'/task',
                $bundleConfigDir.'/staging',
            ));
        }

        $output->writeln(['', ' > generating <comment>./Capfile</comment>', ' > generating <comment>./Gemfile</comment>', '']);

        $requirements = ['capistrano/setup', 'capistrano/deploy', 'capistrano/composer', 'capistrano/symfony'];
        $gems = ["'capistrano', '~> 3.4'", "'capistrano-symfony', '~> 1.0.0.rc1'", "'capistrano-rbenv'"];
        $capfile = new CapfileGenerator($requirements, $rootDir);
        $gemfile = new GemfileGenerator($gems, $rootDir);
        $this->generateMany([$capfile, $gemfile]);

        $output->writeln([
            '<info>Successfully generated </info><comment>Capfile</comment><info> and </info><comment>Gemfile</comment>',
            '',
        ]);
    }
}
