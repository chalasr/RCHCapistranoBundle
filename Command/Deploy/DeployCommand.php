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

use RCH\CapistranoBundle\Generator\StagingGenerator;
use RCH\CapistranoBundle\Util\OutputHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Yaml;

// TODO: Create a symfony process that take a staging name as argument & executes "$ cap <staging> deploy"
/**
 * Deployment command.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DeployCommand extends ContainerAwareCommand
{
    use OutputHelper;

    /**
     * {@inheritdoc}
     * @return [type] [description]
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(new InputOption('staging-name', '-sn', InputOption::VALUE_REQUIRED, 'Staging used', 'production')))
            ->setName('rch:deploy:run')
            ->setDescription('Deploys application using Capistrano')
        ;
    }

    /**
     * Deploys application.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDir = $this->getRootDir();
        $stagingName = $input->getOption('staging-name');
        $stagingPath = $this->getCapistranoDir() . '/deploy/';
        $staging = sprintf('%s%s.rb', $stagingPath, $stagingName);

        $this->sayWelcome($input, $output);

        if (false === file_exists($staging)) {
            $nonReadyStaging = sprintf('%s/config/rch/staging/%s.yml', $rootDir, $stagingName);
            if (false === file_exists($nonReadyStaging)) {
                return $output->writeln(sprintf('<error>Unable to find staging with name %s</error>', $stagingName));
            }
            $params = Yaml::parse(file_get_contents($nonReadyStaging));
            $newStaging = new StagingGenerator($params, $stagingPath, $stagingName.'.rb');
            $this->generate($newStaging);
        }

        $output->setVerbosity(10);
        $builder = new ProcessBuilder(['cap', $stagingName, 'deploy']);
        $builder->setTimeout(null);
        $process = $builder->getProcess();
        $process->run(function ($type, $buffer) use ($output) {
            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->write($buffer);
            }
        });
        if (!$process->isSuccessful()) {
            $output->writeln('<error>Deployment failed</error>');

            if (OutputInterface::VERBOSITY_VERBOSE > $output->getVerbosity()) {
                $output->writeln('<error>Run the command with -v option for more details</error>');
            }
        }

        return $process->getExitCode();
    }
}
