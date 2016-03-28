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

use RCH\CapistranoBundle\Generator\StagingGenerator;
use RCH\CapistranoBundle\Util\CanGenerateTrait as CanGenerate;
use RCH\CapistranoBundle\Util\LocalizableTrait as Localizable;
use RCH\CapistranoBundle\Util\OutputWritableTrait as OutputWritable;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Deployment command.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class RunCommand extends ContainerAwareCommand
{
    use OutputWritable, Localizable, CanGenerate;

    /**
     * {@inheritdoc}
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDir = $this->getRootDir();
        $stagingName = $input->getOption('staging-name');
        $stagingPath = $this->getCapistranoDir().'/deploy/';
        $capitalizer = $this->getContainer()->get('rch_capistrano.capitalizer');
        $staging = sprintf('%s%s.rb', $stagingPath, $stagingName);

        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $this->sayWelcome($output);

        if (false === file_exists($staging)) {
            $yamlStaging = sprintf('%s/config/rch/staging/%s.yml', $rootDir, $stagingName);

            if (false === file_exists($yamlStaging)) {
                throw new InvalidArgumentException(sprintf('Unable to find staging with name %s', $stagingName));
            }

            $params = $this->parseYamlStaging($yamlStaging);
            $generator = new StagingGenerator($params, $stagingPath, $stagingName);

            $this->generate($generator);
        }

        $builder = new ProcessBuilder(['bundle', 'exec', 'cap', $stagingName, 'deploy']);
        $builder->setTimeout(null);
        $process = $builder->getProcess();

        $process->run(function ($type, $buffer) use ($output) {
            $output->write(sprintf('<info>%s</info>', $buffer));
        });

        if (!$process->isSuccessful()) {
            $output->writeln(PHP_EOL);
            $output->writeln('<error>Deployment failed</error>');
            $output->writeln(
                sprintf('<error>Execute <comment>cap %s deploy --trace</comment> for more details</error>', $stagingName)
            );

            return $process->getExitCode();
        }

        return $output->writeln(
            '<info>Project successfully deployed !</info>'
        );
    }
}
