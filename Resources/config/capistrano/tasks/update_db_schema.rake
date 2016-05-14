task :schemadb do
    invoke 'symfony:console', 'doctrine:schema:update', '--force'
end
