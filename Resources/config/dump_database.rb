
namespace :deploy do
  before 'updated', 'schemadb'
  task :schemadb do
    invoke 'symfony:console', 'doctrine:schema:update', '--force'
  end
end
