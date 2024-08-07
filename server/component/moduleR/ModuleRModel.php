<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseModel.php";
require_once __DIR__ . '/../../service/ext/rserve/vendor/autoload.php';

use Sentiweb\Rserve\Connection;
use Sentiweb\Rserve\Exception as Rserve_Exception;

/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ModuleRModel extends BaseModel
{
    /* Constants ************************************************/
    const R_SCRIPT_CHECK_RESULT = 'if (exists("result")) {
                result
            } else {
                result = "There is no variable `result`. Please assign the end result into a variable called `result`. You can pass multiple values in a list."
                result
            }';
    const R_SCRIPT_ASYNC_CALLBACK = 'if (!require("httr")) {
                                       # If not installed, install httr
                                        install.packages("httr")
                                    }
                                    library(httr)
                                    url <- "$callback_url"
                                    body <- list($callback_params)
                                    if (!is.list(result)) {
                                        body[["result"]] <- result
                                    }
                                    # Loop through each key-value pair in the result list
                                    for (key in names(result)) {
                                        value <- result[[key]]
                                        body[[key]] <- value
                                    }
                                    POST(url, body = body, encode = "form", content_type("application/x-www-form-urlencoded"))';


    /**
     * The settings for the Rserve
     */
    private $rserve_settings;

    /**
     * The r script row
     */
    private $r_script;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $sid)
    {
        parent::__construct($services);
        $this->rserve_settings = $this->db->fetch_page_info(SH_MODULE_R);
        if ($sid > 0) {
            $this->r_script = $this->fetch_script($sid);
        }
    }

    /**
     * Get the protocol. If it is debug it returns http otherwise https
     * @retval string
     * it returns the protocol
     */
    private function get_protocol()
    {
        return DEBUG ? 'http://' : 'https://';
    }

    /**
     * Get Rserve connection
     * @return object 
     * Return the connection object
     */
    private function get_rserve_connection()
    {
        return new Connection(
            $this->rserve_settings['rserve_host'],
            $this->rserve_settings['rserve_port'],
            array(
                'username' => $this->rserve_settings['rserve_user_name'],
                'password' => $this->rserve_settings['rserve_password']
            )
        );
    }

    /**
     * Add extra script to check if the result variable exists
     * @param string $r_script
     * The R script
     * @return string
     * Return the modified script
     */
    private function add_result_check($r_script)
    {
        return $r_script . (in_array(substr($r_script, -1), array(';', "\n", "\r\n")) ? "" : ";") . ModuleRModel::R_SCRIPT_CHECK_RESULT;
    }

    /**
     * Add extra script to check if the result variable exists
     * @param string $r_script
     * The R script
     * @param int $id_users
     * The user for whom we save the result
     * @param int $id_scheduledJobs
     * The job id that was used for the R task
     * @return string
     * Return the modified script
     */
    private function add_async_callback_request($r_script, $r_generated_id, $id_users, $id_scheduledJobs)
    {
        $r_script = $r_script . (in_array(substr($r_script, -1), array(';', "\n", "\r\n")) ? "" : ";") . ModuleRModel::R_SCRIPT_ASYNC_CALLBACK;
        $callback_url = $this->get_protocol() . $_SERVER['HTTP_HOST'] . $this->get_link_url("callback", array("class" => "CallbackRserve", "method" => "save_data"));
        $callback_params = '"callback_key" = "' . $this->db->get_callback_key() . '",' . PHP_EOL;
        $callback_params .= '"id_users" = ' . $id_users . ',' . PHP_EOL;
        $callback_params .= '"r_generated_id" = "' . $r_generated_id . '",' . PHP_EOL;
        $callback_params .= '"id_scheduledJobs" = ' . $id_scheduledJobs;
        $r_script = str_replace('$callback_url', $callback_url, $r_script);
        $r_script = str_replace('$callback_params', $callback_params, $r_script);
        return $r_script;
    }

    /**
     * Insert a new R script.     
     * @return int
     *  The id of the new script or false if the process failed.
     */
    public function insert_new_script()
    {
        try {
            $this->db->begin_transaction();
            $generated_id = "R_SCRIPT_" . substr(uniqid(), -11);
            $this->set_dataTables_displayName($generated_id, $generated_id);
            $sid = $this->db->insert(RSERVE_TABLE_R_SCRIPTS, array(
                "generated_id" => $generated_id,
                "name" => $generated_id
            ));
            $this->transaction->add_transaction(
                transactionTypes_insert,
                transactionBy_by_user,
                $_SESSION['id_user'],
                RSERVE_TABLE_R_SCRIPTS,
                $sid
            );
            $this->db->commit();
            return $sid;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Update a R script.   
     * @param int $sid
     * Script id,
     * @param string $name
     * Script name
     * @param string $script
     * Script text
     * @param string $test_variables
     * Json structure for test variables
     * @param boolean $async
     * Is the script async
     * @param string $data_config
     * Json structure for data config
     * @return int
     *  The id of the new survey or false if the process failed.
     */
    public function update_script($sid, $name, $script, $test_variables, $async, $data_config)
    {
        try {
            $this->db->begin_transaction();
            $this->set_dataTables_displayName($this->r_script['generated_id'], $name);
            $this->db->update_by_ids(RSERVE_TABLE_R_SCRIPTS, array(
                "name" => $name,
                "script" => $script,
                "test_variables" => $test_variables,
                "async" => (int)$async,
                "data_config" => $data_config
            ), array('id' => $sid));
            $this->transaction->add_transaction(
                transactionTypes_update,
                transactionBy_by_user,
                $_SESSION['id_user'],
                RSERVE_TABLE_R_SCRIPTS,
                $sid
            );
            $this->db->commit();
            return $sid;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get script
     * @param int $sid     
     * Script id
     * @return array
     * Return the script row
     */
    public function fetch_script($sid)
    {
        $sql = "SELECT *
                FROM r_scripts
                WHERE id = :id";
        return $this->db->query_db_first($sql, array(':id' => $sid));
    }

    /**
     * R script getter
     * @return array
     * Return the r script record;
     */
    public function get_script()
    {
        return $this->r_script;
    }

    /**
     * Get all r scripts
     * @return array
     * Return all scripts as rows
     */
    public function get_scripts()
    {
        return $this->db->select_table(RSERVE_TABLE_R_SCRIPTS);
    }

    /**
     * Delete script
     * @param int $sid
     * The script id
     * @return bool
     * Return the success result of the operation
     */
    public function delete_script($sid)
    {
        return $this->db->remove_by_ids(RSERVE_TABLE_R_SCRIPTS, array("id" => $sid));
    }

    /**
     * Execute R script
     * @param string $script
     * The source code of R
     * @param object $variables
     * Variable values that will be used in the script
     * @param int $user_id
     * Show the data for that user
     * @return object
     * Return the result, object with all variables and their values
     */
    public function execute_r_script($script, $data_config, $variables = array(), $id_users = null)
    {
        try {
            // Connect to the Rserve server
            $connection = $this->get_rserve_connection();
            if (!is_array($variables) && $variables != null) {
                return array(
                    "result" => false,
                    "data" => "Error in the variables"
                );
            }
            if ($variables == null) {
                $variables = array();
            }
            if ($data_config == null) {
                $data_config = array();
            }
            $data_config = $this->db->replace_calced_values($data_config, $variables); // take some variables used in data_config
            $data_config_values = $data_config ? $this->fetch_data($data_config, $id_users) : [];
            $r_script = $this->db->replace_calced_values($script, array_merge($data_config_values, $variables));
            $r_script = $this->add_result_check($r_script);
            $r_script = str_replace("\r\n", "\n", $r_script); //RServe accepts only these new lines
            $result = $connection->evalString($r_script);
            $connection->close();
            return array(
                "result" => true,
                "data" => is_array($result) ? $result : array("result" => $result)
            );
        } catch (Rserve_Exception  $e) {
            return array(
                "result" => false,
                "data" => $e->getMessage(),
                "script" => $r_script
            );
        }
    }

    /**
     * Execute R script asynchronously
     * @param string $script 
     * The source code of R
     * @param object $args
     * Params passed to the method
     * @param object $r_script_info
     * Script info
     * @param array $variables 
     * Variable values that will be used in the script
     */
    public function execute_r_script_async($script, $args, $r_script_info, $variables = array())
    {
        // Connect to the Rserve server
        $connection = $this->get_rserve_connection();
        if (!is_array($variables)) {
            return array(
                "data" => "Error in the variables",
                "result" => false,
            );
        }
        $data_config = $r_script_info['data_config'] ? json_decode($r_script_info['data_config'], true) : false;
        $data_config = $this->db->replace_calced_values($data_config, $variables); // take some variables used in data_config
        $data_config_values = $data_config ? $this->fetch_data($data_config, $args['user']['id_users']) : [];
        $r_script = $this->db->replace_calced_values($script, array_merge($data_config_values, $variables));
        $r_script = $this->add_result_check($r_script);
        $r_script = $this->add_async_callback_request($r_script, $r_script_info['generated_id'], $args['user']['id_users'], $args['task_info']['id']);
        $r_script = str_replace("\r\n", "\n", $r_script); //RServe accepts only these new lines
        $connection->setAsync(true);
        $result = $connection->evalString($r_script);
        $connection->close();
    }

    /**
     * Save the R results in an external form
     * @param object $results
     * The results, object with the the variables and their data
     * @param int $id_users
     * The user for whom we save the result
     * @param int $id_scheduledJobs
     * The job id that was used for the R task
     * @param string $r_generated_id
     * The R generated id which is used for the table name
     * @return boolean
     * The result of the function
     */
    public function save_r_results($result, $id_users, $id_scheduledJobs, $r_generated_id)
    {
        if ($result['result']) {
            $result['data']['id_users'] = $id_users;
            foreach ($result['data'] as $key => $value) {
                // Check if the property is an array
                if (is_array($value)) {
                    // Convert the array to a JSON-encoded string
                    $result['data'][$key] = json_encode($value);
                }
            }
            $save_result = $this->user_input->save_data(transactionBy_by_r_script, $r_generated_id, $result['data']);
            if ($save_result) {
                $this->transaction->add_transaction(
                    transactionTypes_insert,
                    transactionBy_by_r_script,
                    null,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $id_scheduledJobs,
                    false,
                    "R script results were saved in table " . $r_generated_id
                );
            }
            return $save_result;
        } else {
            $this->transaction->add_transaction(
                transactionTypes_insert,
                transactionBy_by_r_script,
                null,
                $this->transaction::TABLE_SCHEDULED_JOBS,
                $id_scheduledJobs,
                false,
                array(
                    "error" => "Error while executing R Script",
                    "error_msg" => $result['data']
                )
            );
            return false;
        }
    }
}
