<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../component/BaseHooks.php";
require_once __DIR__ . "/../../../../component/style/BaseStyleComponent.php";
require_once __DIR__ . "/moduleR/ModuleRModel.php";


/**
 * The class to define the hooks for the plugin.
 */
class RserveHooks extends BaseHooks
{
    /* Constructors ***********************************************************/

    /**
     * The moduleR where the R script can be executed
     */
    private $moduleR;

    /**
     * The constructor creates an instance of the hooks.
     * @param object $services
     *  The service handler instance which holds all services
     * @param object $params
     *  Various params
     */
    public function __construct($services, $params = array())
    {
        parent::__construct($services, $params);
        $this->moduleR = new ModuleRModel($this->services);
    }

    /* Private Methods *********************************************************/

    /**
     * Execute R script
     *
     * @param object $args
     * Params passed to the method
     * @return bool
     *  True on success, false on failure.
     */
    private function execute_r_script($args)
    {
        // Connect to the Rserve server
        $r_script_info = $this->moduleR->get_script($args['task_info']['config']['r_script']);
        if ($r_script_info) {
            $r_script = $r_script_info['script'];
            $form_values = $this->user_input->get_form_values($args['task_info']['config']['form_data']['form_fields']);
            $result = $this->moduleR->execute_r_script($r_script, $form_values);
            if ($result['result']) {
                $result['data']['id_users'] = $args['user']['id_users'];
                $save_result = $this->user_input->save_external_data(transactionBy_by_r_script, $r_script_info['generated_id'], $result['data']);
                if ($save_result) {
                    $this->transaction->add_transaction(
                        transactionTypes_insert,
                        transactionBy_by_r_script,
                        null,
                        $this->transaction::TABLE_SCHEDULED_JOBS,
                        $args['user']['id_scheduledJobs'],
                        false,
                        "R script results were saved in table " . $r_script_info['generated_id']
                    );
                }
                return $save_result;
            } else {
                $this->transaction->add_transaction(
                    transactionTypes_insert,
                    transactionBy_by_r_script,
                    null,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $args['user']['id_scheduledJobs'],
                    false,
                    array(
                        "error" => "Error while executing R Script",
                        "error_msg" => $result['data']
                    )
                );
                return false;
            }
        } else {
            $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_r_script, null, null, null, false, "The R script was not found; " . json_encode($args));
            return false;
        }
    }


    /* Public Methods *********************************************************/

    /**
     * Execute R task
     *
     * @param object $args
     * Params passed to the method
     * @return bool
     *  True on success, false on failure.
     */
    public function execute_r_task($args)
    {
        if ($args['task_info']['config']['type'] == ACTION_JOB_TYPE_R_SCRIPT) {
            return $this->execute_r_script($args);
        } else {
            return $this->execute_private_method($args);
        }
    }

    /**
     * Execute R task
     *
     * @param object $args
     * Params passed to the method
     * @return bool
     *  True on success, false on failure.
     */
    public function get_json_schema($args)
    {
        $r_scripts = $this->db->fetch_table_as_select_values('r_scripts', 'id', array('generated_id', 'name'));
        $enum_titles = array();
        $enum = array();
        foreach ($r_scripts as $key => $value) {
            $enum_titles[] = $value['text'];
            $enum[] = $value['value'];
        }
        $res = (string) $this->execute_private_method($args);
        $res = json_decode($res, true);
        $r_script = array(
            "type" => "string",
            "options" => array(
                "grid_columns" => 12,
                "enum_titles" => $enum_titles,
                "dependencies" => array(
                    "job_type" => array(
                        "r_script"
                    )
                )
            ),
            "title" => "R script",
            "description" => "Select R script",
            "enum" => $enum
        );
        $res['definitions']['job_ref']['properties']['job_type']['enum'][] = "r_script";
        $res['definitions']['job_ref']['properties']['job_type']['options']['enum_titles'][] = "R script";
        $res['definitions']['job_ref']['properties']['r_script'] = $r_script;
        return json_encode($res);
    }

    /**
     * Get task config for R scrip task
     *
     * @param object $args
     * Params passed to the method
     * @return bool
     *  True on success, false on failure.
     */
    public function get_task_config($args)
    {
        $job = $args['job'];
        if ($args['job']['job_type'] == ACTION_JOB_TYPE_R_SCRIPT) {
            $task_config = array(
                "type" => $job[ACTION_JOB_TYPE],
                "description" => isset($job['job_name']) ? $job['job_name'] : "Schedule task (R script) by form: " . $args['form_data']['form_name'],
                ACTION_JOB_TYPE_R_SCRIPT => $job[ACTION_JOB_TYPE_R_SCRIPT],
                "form_data" => $args['form_data'],
                "id_users" => $_SESSION['id_user']
            );
            return $task_config;
        } else {
            return $this->execute_private_method($args);
        }
    }

    /**
     * Get job task
     *
     * @param object $args
     * Params passed to the method
     * @return bool
     *  True on success, false on failure.
     */
    public function get_job_type($args)
    {
        $res = $this->execute_private_method($args);
        if ($args['job']['job_type'] == ACTION_JOB_TYPE_R_SCRIPT) {
            return jobTypes_task;
        }
        return $res;
    }

    /**
     * Add sensible page for s script editing     
     * @return array
     * Return array with the sensible pages
     */
    public function get_sensible_pages($args)
    {
        $res = $this->execute_private_method($args);
        $res[] = 'moduleRMode';
        return $res;
    }
}
?>
