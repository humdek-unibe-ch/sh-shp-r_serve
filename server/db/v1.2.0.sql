-- update plugin entry in the plugin table
UPDATE `plugins`
SET version = 'v1.2.0'
WHERE `name` = 'rserve';

DELETE 
FROM hooks
WHERE `name` = 'field-rserve_panel-view';
