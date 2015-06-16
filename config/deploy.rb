# config valid only for current version of Capistrano
lock '3.4.0'

set :application, 'tools'
set :repo_url, 'git@github.com:atomicjolt/kennethware-2.0.git'

set :branch, -> { `git rev-parse --abbrev-ref HEAD`.chomp }

set :log_level, :info

set :logtail_files, %w( /var/log/nginx/*.log )
set :logtail_lines, 50

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, '/srv/www/tools'

# Default value for :linked_files is []
# set :linked_files, fetch(:linked_files, []).push('config/database.yml', 'config/secrets.yml')

# Default value for linked_dirs is []
# set :linked_dirs, fetch(:linked_dirs, []).push('log', 'tmp/pids', 'tmp/cache', 'tmp/sockets', 'vendor/bundle', 'public/system')
after 'deploy:publishing', 'deploy:restart'

namespace :deploy do

  task :restart do
    on roles(:app) do
      execute :sudo, "restart php5-fpm"
    end
  end

end
