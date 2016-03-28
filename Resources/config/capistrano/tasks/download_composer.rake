desc "Download Composer"
task :download_composer do
    on roles(:all) do
        execute "cd #{shared_path} && curl -s https://getcomposer.org/installer | php"
        SSHKit.config.command_map[:composer] = "#{shared_path.join("composer.phar")}"
    end
end
