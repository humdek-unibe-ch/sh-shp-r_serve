-- add plugin entry in the plugin table
INSERT IGNORE INTO plugins (name, version) 
VALUES ('rserve', 'v1.0.0');

-- register hook test
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`, `priority`) 
VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_on_function_execute' LIMIT 0,1), 'rserve-test', 'Rserve tesr', 'BasePage', 'output_base_content', 'RserveHooks', 'test', 1);

