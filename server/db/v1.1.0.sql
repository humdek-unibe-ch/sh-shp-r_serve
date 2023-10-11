-- update plugin entry in the plugin table
UPDATE `plugins`
SET version = 'v1.1.0'
WHERE `name` = 'rserve';

-- add column data_config
CALL add_table_column('r_scripts', 'data_config ', 'TEXT');
