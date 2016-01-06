<?php

/*
* This file is part of Chalasdev/CapistranoBundle.
*
* https://github.com/chalasr/CapistranoBundle
* Robin Chalas <robin.chalas@gmail.com>
*
*/

namespace Chalasdev\CapistranoBundle\Command;

use Chalasdev\CapistranoBundle\Generator\DeployGenerator;
use Chalasdev\CapistranoBundle\Generator\StagingGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Setup capistrano workflow.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class SetupCommand extends ContainerAwareCommand
{
    protected $deployProperties;

    protected $bundleDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('capistrano:setup')
        ->setDescription('Setup capistrano deployment configuration in interactive mode');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * Configures deployment.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return OutputInterface
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $questionHelper = $this->getHelper('question');
        $formatter = $this->getHelper('formatter');
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $welcome = $formatter->formatBlock('Welcome to chalasdev/capistrano', 'title', true);
        $root = $this->getContainer()->get('kernel')->getRootDir();
        $appPath = explode('/', $root);
        $appName = $appPath[count($appPath) - 2];

        $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr', '']);
        $this->initConfig($fs, $root);
        $output->writeln([$formatter->formatSection('SETUP', 'Project settings'), '']);
        $deployData = $this->configureDeploy($input, $output, $questionHelper, $appName);
        $deployFile = new DeployGenerator($deployData, $root);
        $deployFile->generate();

        $output->writeln(['', " > generating <comment>{$appName}/config/deploy.rb</comment>"]);
        $output->writeln(['<info>Successfully created.</info>', '']);
        $output->writeln([$formatter->formatSection('PRODUCTION', 'Remote server / SSH settings'), '']);

        $sshProps = $this->configureSSH($input, $output, $questionHelper, $deployData);
        $staging = new StagingGenerator($sshProps, $root);
        $staging->generate();

        return $output->writeln('<comment>Remote server successfully configured</comment>');
    }

    /**
     * Dump capistrano configuration skin from Resources directory.
     *
     * @param Filesystem $fs
     * @param string     $root Application root dir
     */
    protected function initConfig(Filesystem $fs, $root)
    {
        $this->bundleDir = $root.'/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle';
        $path = $root.'/../config';

        if (!$fs->exists($path.'/deploy.rb') || !$fs->exists($path.'/deploy/production.rb')) {
            return $fs->mirror($this->bundleDir.'/Resources/config/capistrano', $path);
        }

        $fs->remove($path.'/deploy.rb');
        $fs->remove($path.'/deploy');
        $fs->remove($path);

        return $fs->mirror($this->bundleDir.'/Resources/config/capistrano', $path);
    }

    /**
     * Configure deploy.rb capistrano file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param string          $appName
     *
     * @return array
     */
    protected function configureDeploy(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $appName)
    {
        $data = [];
        $add = [];
        $add['application'] = [
            'helper'       => $appName,
            'label'        => 'Application',
            'autocomplete' => [$appName],
        ];
        $add['repo_url'] = [
            'helper'       => 'git@github.com:{user}/{repo}.git',
            'label'        => 'Repository',
            'autocomplete' => [
                sprintf('git@github.com:chalasr/%s.git', $appName),
                sprintf('git@git.sutunam.com:rchalas/%s.git', $appName),
                sprintf('git@git.chaladev.fr:chalasr/%s.git', $appName),
            ],
        ];
        $properties = $add + Yaml::parse(file_get_contents($this->bundleDir.'/Resources/config/deploy_config.yml'));
        foreach ($properties as $key => $property) {
            if ('deploy_to' == $key && null !== $data['ssh_user']) {
                $property['helper'] = "/home/{$data['ssh_user']}/public_html";
            }
            $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
            if (isset($property['autocomplete'])) {
                $question->setAutocompleterValues($property['autocomplete']);
            }
            $data[$key] = $questionHelper->ask($input, $output, $question);
        }

        $data['composer'] = $this->checkComposer($input, $output, $questionHelper, $data);
        $data['schemadb'] = $this->checkSchemaUpdate($input, $output, $questionHelper, $data);

        return $data;
    }

    /**
     * Check need of composer.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param array           $deployData
     *
     * @return bool
     */
    protected function checkComposer(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $deployData)
    {
        $question = new Question('<info>Download composer</info> [<comment>Y</comment>]: ', 'Y');
        $question->setAutocompleterValues(['Y', 'N']);
        $downloadComposer = $questionHelper->ask($input, $output, $question);
        $deployData['composer'] = $downloadComposer == 'Y' ? true : false;

        return $deployData['composer'];
    }

    /**
     * Check for database schema update.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param array           $deployData
     *
     * @return bool
     */
    protected function checkSchemaUpdate(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $deployData)
    {
        $question = new Question('<info>Update database schema</info> [<comment>Y</comment>]: ', 'Y');
        $question->setAutocompleterValues(['Y', 'N']);
        $wantDump = $questionHelper->ask($input, $output, $question);
        $deployData['schemadb'] = $wantDump == 'Y' ? true : false;

        return $deployData['schemadb'];
    }

    /**
     * Configure SSH options for production staging.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param array           $deployData
     *
     * @return array The production staging configuration
     */
    protected function configureSSH(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $deployData)
    {
        $serverOptions = [
            'domain' => [
                'helper' => $deployData['ssh_user'],
                'label'  => 'Domain name',
            ],
        ];
        $sshOptions = [
            'forwardAgent' => [
                'label'  => 'SSH forwardAgent',
                'helper' => 'false',
            ],
            'authMethods' => [
                'label'  => 'SSH auth method',
                'helper' => 'publickey password',
            ],
            'keys' => [
                'label'  => 'Remote SSH key',
                'helper' => sprintf('/home/%s/.ssh/id_rsa', $deployData['ssh_user']),
            ],
        ];
        $sshProps = [
          'user'         => $deployData['ssh_user'],
          'keys'         => '',
          'forwardAgent' => false,
          'authMethods'  => 'publickey password',
          'deployTo'     => $deployData['deploy_to'],
        ];

        $question = new Question("<info>{$serverOptions['domain']['label']}</info> [<comment>{$serverOptions['domain']['helper']}</comment>]: ", $serverOptions['domain']['helper']);
        $sshProps['domain'] = $questionHelper->ask($input, $output, $question);

        foreach ($sshOptions as $key => $property) {
            $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
            if (isset($property['autocomplete'])) {
                $question->setAutocompleterValues($property['autocomplete']);
            }
            $sshProps[$key] = $questionHelper->ask($input, $output, $question);
        }

        return $sshProps;
    }
}
