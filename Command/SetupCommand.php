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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class SetupCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->deployProperties = [
            'branch' => [
                'helper' => 'master',
                'label' => 'Git branch',
            ],
            'ssh_user' => [
                'helper' => 'chalasr',
                'label' => 'SSH username',
            ],
            'deploy_to' => [
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
        ];
    }

    protected function configure()
    {
        $this
        ->setName('capistrano:setup')
        ->setDescription('Setup capistrano deployment configuration in interactive mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $root = $this->getContainer()->get('kernel')->getRootDir();
        $questionHelper = $this->getHelper('question');
        $formatter = $this->getHelper('formatter');
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);
        $welcome = $formatter->formatBlock('Welcome to chalasdev/capistrano', 'title', true);
        $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr', '']);
        $output->writeln([$formatter->formatSection('SETUP', 'Project settings'), '']);
        $deployRb = $root.'/../config/deploy.rb';
        $appPath = explode('/', $root);
        $appName = $appPath[count($appPath) - 2];
        $this->initConfig($fs, $root);
        $deployData = $this->configureDeploy($input, $output, $questionHelper, $appName);
        foreach ($deployData as $k => $v) {
            if ($k == 'deploy_to' || $k == 'ssh_user') {
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
        $stagingPath = $root.'/../config/deploy/production.rb';
        $sshProps = $this->configureSSH($input, $output, $questionHelper, $deployData);
        $expression = PHP_EOL."server '{$sshProps['domain']}',".PHP_EOL."user: '{$deployData['ssh_user']}',".PHP_EOL;
        $expression .= 'ssh_options: {'.PHP_EOL;
        foreach ($sshProps as $k => $v) {
            if ($k == 'domain') {
                continue;
            }
            if ($k == 'user') {
                $expression .= "		{$k}: '{$v}',".PHP_EOL;
            } else {
                $expression .= "		{$k}: {$v},".PHP_EOL;
            }
        }
        $expression .= '}'.PHP_EOL;
        $expression .= sprintf('set(:deploy_to, "%s")', $deployData['deploy_to']);

        file_put_contents($stagingPath, $expression, FILE_APPEND);
        $output->writeln('<comment>Remote server successfully configured</comment>');
    }

    /**
     * Dump capistrano configuration files from vendor.
     *
     * @param class  $fs   symfony/file-system
     * @param string $root Application root dir
     */
    protected function initConfig($fs, $root)
    {
        $path = $root.'/../config';
        if (!$fs->exists("{$path}/deploy.rb") || !$fs->exists("{$path}/deploy/production.rb")) {
            return $fs->mirror(
                "{$root}/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/capistrano",
                "{$root}/../config/"
            );
        }
        $fs->remove("{$path}/deploy.rb");
        $fs->remove("{$path}/deploy");
        $fs->remove("{$path}");

        return $fs->mirror(
            "{$root}/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/capistrano",
            "{$root}/../config/"
        );
    }

    /**
     * Configure deployment options for capistrano.
     *
     * @param string $appName app name
     *
     * @return array $data
     */
    protected function configureDeploy($input, $output, $questionHelper, $appName)
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
            'autocomplete' => ["git@github.com:chalasr/{$appName}.git", "git@git.sutunam.com:rchalas/{$appName}.git", "git@git.chaladev.fr:chalasr/{$appName}.git"],
        ];
        $properties = $add + $this->deployProperties;
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

        return $data;
    }

    /**
     * Check for composer installation type.
     *
     * @param class  $questionHelper QuestionHelper
     * @param string $deployRb       deploy.rb path
     */
    protected function checkComposer($input, $output, $questionHelper, $deployRb, $root)
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
     * Check for database schema update.
     *
     * @param class  $questionHelper QuestionHelper
     * @param string $deployRb       deploy.rb path
     */
    protected function checkSchemaUpdate($input, $output, $questionHelper, $deployRb, $root)
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
     * @param class $questionHelper QuestionHelper
     * @param array $deployData     deploy.rb parameters
     *
     * @return array $sshProps deploy/production.rb staging config
     */
    protected function configureSSH($input, $output, $questionHelper, $deployData)
    {
        $serverOptions = [
            'domain' => [
                'helper' => $deployData['ssh_user'],
                'label' => 'Domain name',
            ],
        ];
        $serverProps = [];
        $question = new Question("<info>{$serverOptions['domain']['label']}</info> [<comment>{$serverOptions['domain']['helper']}</comment>]: ", $serverOptions['domain']['helper']);
        $serverProps['domain'] = $questionHelper->ask($input, $output, $question);
        $sshOptions = [
            'forward_agent' => [
                'label' => 'SSH forward_agent',
                'helper' => 'false',
            ],
            'auth_methods' => [
                'label' => 'SSH auth method',
                'helper' => 'publickey password',
            ],
            'keys' => [
                'label' => 'Remote SSH key',
                'helper' => "/home/{$deployData['ssh_user']}/.ssh/id_rsa",
            ],
        ];
        $sshProps = [
            'user' => $deployData['ssh_user'],
            'keys' => '',
            'forward_agent' => false,
            'auth_methods' => 'publickey password',
        ];
        foreach ($sshOptions as $key => $property) {
            $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
            if (isset($property['autocomplete'])) {
                $question->setAutocompleterValues($property['autocomplete']);
            }
            if (in_array($key, ['auth_methods', 'keys'])) {
                $sshProps[$key] = '%w('.$questionHelper->ask($input, $output, $question).')';
            } else {
                $sshProps[$key] = $questionHelper->ask($input, $output, $question);
            }
        }
        $sshProps['domain'] = $serverProps['domain'];

        return $sshProps;
    }
}
