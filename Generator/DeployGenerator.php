<?php

/*
* This file is part of RCH/CapistranoBundle.
*
* Robin Chalas <robin.chalas@gmail.com>
*
* For more informations about license, please see the LICENSE
* file distributed in this source code.
*/

namespace RCH\CapistranoBundle\Generator;

/**
 * Generates deploy.rb file.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DeployGenerator extends AbstractGenerator
{
    /**
     * Template of deploy.rb.
     *
     * @var string
     */
    protected static $defaultConfigTemplate =
"
# Default
set :app_path, 'app'
set :web_path, 'web'
set :scm, 'git'
set :format, :pretty
set :log_level, :debug
set :stage, 'production'
set :pty, true
set :log_path, fetch(:app_path) + '/logs'
set :cache_path, fetch(:app_path) + '/cache'
set :app_config_path, fetch(:app_path) + '/config'
set :linked_dirs, %w{app/logs}
set :linked_files, ['app/config/parameters.yml']

# Permissions
set :file_permissions_paths, [fetch(:log_path), fetch(:cache_path)]
set :file_permissions_users, ['www-data']
set :webserver_user, 'www-data'

# Assets
set :assets_install_path, fetch(:web_path)
set :assets_install_flags, '--symlink'
";

    /**
     * Template of deploy.rb.
     *
     * @var string
     */
    protected static $configTemplate =
"# Server
set :application, '<application>'
set :repo_url, '<repo_url>'
set :branch, '<branch>'
set :model_manager, '<model_manager>'
set :symfony_env, '<symfony_env>'
set :use_sudo, <use_sudo>
set :use_set_permissions, <use_set_permissions>
set :permission_method, <permission_method>
set :keep_releases, <keep_releases>
";

    /**
     * Template of composer:dowload task.
     *
     * @var string
     */
    protected static $downloadComposerTaskTemplate =
"namespace :composer do
    before 'install', 'download'
    desc 'Composer update'
    task :download do
        on roles(:all) do
            execute 'cd ~/ && curl -s https://getcomposer.org/installer | php'
        end
    end
end

SSHKit.config.command_map[:composer] = 'php ~/composer.phar'
set :composer_install_flags, '--no-dev --quiet --no-interaction --optimize-autoloader'
set :composer_dump_autoload_flags, '--optimize'
";

    /**
     * Template of deploy:schemadb.
     *
     * @var string
     */
    protected static $updateSchemaTaskTemplate =
"namespace :deploy do
    before 'updated', 'schemadb'
    task :schemadb do
        invoke 'symfony:console', 'doctrine:schema:update', '--force'
    end
end
";

    /**
     * Constructor.
     *
     * @param array  $parameters
     * @param string $path
     */
    public function __construct(array $parameters, $path, $name = 'deploy.rb')
    {
        parent::__construct($parameters, $path, $name);
        $this->path = sprintf('%s/../config/%s', $path, $name);
    }

    /**
     * Writes deployment file.
     */
    public function write()
    {
        foreach ($this->parameters as $prop => $value) {
            $placeHolders[] = sprintf('<%s>', $prop);
            $replacements[] = $value;
        }

        $config = str_replace($placeHolders, $replacements, self::$configTemplate);
        $config = sprintf('%s%s%s', self::$defaultConfigTemplate, PHP_EOL, $config);

        if (true === $this->parameters['composer']) {
            $config = sprintf('%s%s%s', $config, PHP_EOL, self::$downloadComposerTaskTemplate);
        }

        if (true === $this->parameters['schemadb']) {
            $config = sprintf('%s%s%s', $config, PHP_EOL, self::$updateSchemaTaskTemplate);
        }

        fwrite($this->file, $this->addHeaders($config));
    }
}
