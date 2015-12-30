
namespace :composer do
    before 'install', 'download'
    desc 'Composer update'
    task :download do
        on roles(:all) do
            execute "cd ~/ && curl -s https://getcomposer.org/installer | php"
        end
    end
end
SSHKit.config.command_map[:composer] = "php ~/composer.phar"
set :composer_install_flags, '--no-dev --quiet --no-interaction --optimize-autoloader'
set :composer_dump_autoload_flags, '--optimize'
