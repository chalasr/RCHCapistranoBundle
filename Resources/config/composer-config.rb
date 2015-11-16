task :download_composer do
  run "cd #{deploy_to} && curl -s https://getcomposer.org/installer | php"
end

before "symfony:composer:update", "download_composer"
SSHKit.config.command_map[:composer] = "php #{deploy_to}/composer.phar"
set :composer_install_flags, '--no-dev --quiet --no-interaction --optimize-autoloader'
set :composer_dump_autoload_flags, '--optimize'
