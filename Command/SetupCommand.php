<?php

/*
* This file is part of Chalasdev/CapistranoBundle.
*
* https://github.com/chalasr/CapistranoBundle
* Robin Chalas <robin.chalas@gmail.com>
*
*/

namespace Chalasdev\CapistranoBundle\Command;

use Chalasdev\CapistranoBundle\Generator\StagingGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Setup deployment workflow on top of capistrano.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class SetupCommand extends ContainerAwareCommand
{
    /**
     * @property array
     */
    protected $deployProps;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->deployProps = array(
            'branch' => [
                'helper' => 'master',
                'label' => 'Git branch',
            ],
            'sshUser' => [
                'helper' => 'chalasr',
                'label' => 'SSH username',
            ],
            'deployTo' => [
                'helper' => '',
                'label' => 'Remote directory',
            ],
            'model_manager' => [
                'helper' => 'doctrine',
                'label' => 'Model manager',
            ],
            'symfony_env' => [
                'helper' => 'prod',
                'label' => 'Environment',
            ],
            'use_sudo' => [
                'helper' => 'false',
                'label' => 'Use sudo',
            ],
            'use_set_permissions' => [
                'helper' => 'true',
                'label' => 'Set permissions',
            ],
            'permission_method' => [
                'helper' => ':chmod',
                'label' => 'Permission method',
                'autocomplete' => [
                    ':chmod', ':acl',
                ],
            ],
            'keep_releases' => [
                'helper' => 3,
                'label' => 'Number of releases',
            ],
        );
    }

    /**
     * Configures command.
     */
    protected function configure()
    {
        $this
          ->setName('capistrano:setup')
          ->setDescription('Setup capistrano deployment configuration in interactive mode')
        ;
    }

    /**
     * Executes state.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * Starts an interactive question-answer dialog.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $questionHelper = $this->getHelper('question');
        $formatter = $this->getHelper('formatter');
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $welcome = $formatter->formatBlock('Welcome to chalasdev/capistrano', 'title', true);
        $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr', '']);
        $root = $this->getContainer()->get('kernel')->getRootDir();
        $deployRb = $root.'/../config/deploy.rb';
        $stagingPath = $root.'/../config/deploy/production.rb';
        $appPath = explode('/', $root);
        $appName = $appPath[count($appPath) - 2];

        $this->initConfig($fs, $root);
        $output->writeln([$formatter->formatSection('SETUP', 'Project settings'), '']);
        $deployData = $this->configureDeploy($input, $output, $questionHelper, $appName);

        foreach ($deployData as $k => $v) {
            if ($k == 'deployTo' || $k == 'sshUser') {
                continue;
            }
            if (in_array($v, ['true', 'false']) || $v[0] == ':' || is_bool($v) || is_int($v)) {
                $expression = "set :{$k}, {$v}".PHP_EOL;
            } else {
                $expression = "set :{$k}, '{$v}'".PHP_EOL;
            }
            file_put_contents($deployRb, $expression, FILE_APPEND);
        }

        $this->checkComposer($input, $output, $questionHelper, $deployRb, $root);
        $this->checkSchemaUpdate($input, $output, $questionHelper, $deployRb, $root);
        $output->writeln(['', " > generating <comment>{$appName}/config/deploy.rb</comment>"]);
        $output->writeln(['<info>Successfully created.</info>', '']);
        $output->writeln([$formatter->formatSection('PRODUCTION', 'Remote server / SSH settings'), '']);

        $sshProps = $this->configureSSH($input, $output, $questionHelper, $deployData);
        $staging = new StagingGenerator($sshProps);
        file_put_contents($stagingPath, $staging->generate(), FILE_APPEND);

        return $output->writeln('<comment>Remote server successfully configured</comment>');
    }

    /**
     * Dump capistrano configuration files from vendor.
     *
     * @param Filesystem $fs
     * @param string     $root Root directory
     *
     * @return mixed Filesystem::mirror
     */
    protected function initConfig(Filesystem $fs, $root)
    {
        $path = $root.'/../config';
        $bundle = $root.'/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle';

        if (!$fs->exists("{$path}/deploy.rb") || !$fs->exists("{$path}/deploy/production.rb")) {
            return $fs->mirror($bundle.'/Resources/config/capistrano', $path);
        }

        $fs->remove($path.'/deploy.rb');
        $fs->remove($path.'/deploy');
        $fs->remove($path);

        return $fs->mirror($bundle.'/Resources/config/capistrano', $path);
    }

    /**
     * Configure deployment options for capistrano.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param string          $appName        App name
     *
     * @return array $data
     */
    protected function configureDeploy(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $appName)
    {
        $data = [];
        $add = [];
        $add['application'] = [
            'helper' => $appName,
            'label' => 'Application',
            'autocomplete' => [$appName],
        ];
        $add['repo_url'] = [
            'helper' => 'git@github.com:{user}/{repo}.git',
            'label' => 'Repository',
            'autocomplete' => [
                sprintf('git@github.com:chalasr/%s.git', $appName),
                sprintf('git@git.sutunam.com:rchalas/%s.git', $appName),
                sprintf('git@git.chaladev.fr:chalasr/%s.git', $appName),
            ],
        ];
        $properties = $add + $this->deployProps;
        foreach ($properties as $key => $property) {
            if ('deployTo' == $key && null !== $data['sshUser']) {
                $property['helper'] = "/home/{$data['sshUser']}/public_html";
            }
            $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
            if (isset($property['autocomplete'])) {
                $question->setAutocompleterValues($property['autocomplete']);
            }
            $data[$key] = $questionHelper->ask($input, $output, $question);
        }

        return $data;
    }

    /**
     * Asks for composer strategy.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param string          $deployRb
     * @param string          $root
     */
    protected function checkComposer(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $deployRb, $root)
    {
        $question = new Question('<info>Download composer</info> [<comment>Y</comment>]: ', 'Y');
        $question->setAutocompleterValues(['Y', 'N']);
        $downloadComposer = $questionHelper->ask($input, $output, $question);
        if ($downloadComposer == 'Y') {
            $downloadComposerTask = file_get_contents("{$root}/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/download_composer.rb");
            file_put_contents($deployRb, $downloadComposerTask, FILE_APPEND);
        }
    }

    /**
     * Asks for database schema updating strategy.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param string          $deployRb
     * @param string          $root
     */
    protected function checkSchemaUpdate(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $deployRb, $root)
    {
        $question = new Question('<info>Update database schema</info> [<comment>Y</comment>]: ', 'Y');
        $question->setAutocompleterValues(['Y', 'N']);
        $wantDump = $questionHelper->ask($input, $output, $question);
        if ($wantDump == 'Y') {
            $dumpTask = file_get_contents("{$root}/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/dump_database.rb");
            file_put_contents($deployRb, $dumpTask, FILE_APPEND);
        }
    }

    /**
     * Configure SSH options for production staging.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     * @param array           $deployData
     *
     * @return array $sshProps Staging configuration
     */
    protected function configureSSH(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $deployData)
    {
        $serverOptions = [
            'domain' => [
                'helper' => $deployData['sshUser'],
                'label' => 'Domain name',
            ],
        ];
        $sshOptions = [
            'forwardAgent' => [
                'label' => 'SSH forward agent',
                'helper' => 'false',
            ],
            'authMethods' => [
                'label' => 'SSH auth method',
                'helper' => 'publickey password',
            ],
            'keys' => [
                'label' => 'Remote SSH key',
                'helper' => sprintf('/home/%s/.ssh/id_rsa', $deployData['sshUser']),
            ],
        ];
        $sshProps = [
          'user' => $deployData['sshUser'],
          'keys' => '',
          'forwardAgent' => false,
          'authMethods' => 'publickey password',
          'deployTo' => $deployData['deployTo'],
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
