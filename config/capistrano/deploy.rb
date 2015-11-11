# Default
set :symfony_env, 'prod'
set :app_path, 'app'
set :web_path, 'web'
set :scm, 'git'
set :format, :pretty
set :log_level, :debug
set :stage, "production"
set :pty, true
set :log_path, fetch(:app_path) + "/logs"
set :cache_path, fetch(:app_path) + "/cache"
set :app_config_path, fetch(:app_path) + "/config"
set :linked_dirs, %w{app/logs}

# Permissions
set :file_permissions_paths, [fetch(:log_path), fetch(:cache_path)]
set :file_permissions_users, ['www-data']
set :webserver_user, "www-data"

# Assets
set :assets_install_path, fetch(:web_path)
set :assets_install_flags, '--symlink'

# Server
