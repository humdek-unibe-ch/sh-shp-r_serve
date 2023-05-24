<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../callback/BaseCallback.php";
require_once __DIR__ . "/../component/moduleR/ModuleRModel.php";


/**
 * A small class that handles callbak and set the group number for validation code
 * calls.
 */
class CallbackRserve extends BaseCallback
{
    /* Constants ************************************************/
    const CALLBACK_R_GENERATED_ID = 'r_generated_id';

    /* Private Properties *****************************************************/

    /**
     * Services
     */
    private $services = null;

    /**
     * Instance of ModuleQualtricsSurveyModel
     */
    private $moduleRModel;

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $this->services = $services;
        $this->moduleRModel = new ModuleRModel($this->services);
    }

    /**
     * Validate all request parameters and return the results
     *
     * @param $data
     *  The POST data of the callback call:
     *   callbackKey is expected from where the callback is initialized
     * @return array
     *  An array with the callback results
     */
    private function validate_callback($data)
    {
        $result['selfhelpCallback'] = [];
        $result[CallbackRserve::CALLBACK_STATUS] = CallbackRserve::CALLBACK_SUCCESS;
        if (!isset($data[CallbackRserve::CALLBACK_KEY]) || $this->db->get_callback_key() !== $data[CallbackRserve::CALLBACK_KEY]) {
            //validation for the callback key; if wrong return not secured
            array_push($result['selfhelpCallback'], 'wrong callback key');
            $result[CallbackRserve::CALLBACK_STATUS] = CallbackRserve::CALLBACK_ERROR;
            return $result;
        }
        return $result;
    }


    /**
     * Save data from the R script
     *
     * @param $data
     * The POST data of the callback call:
     * callback_key,
     * id_users,
     * r_generated_id,
     * data
     */
    public function save_data($data)
    {
        $start_time = microtime(true);
        $start_date = date("Y-m-d H:i:s");
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = $this->validate_callback($data);
        if ($result[CallbackRserve::CALLBACK_STATUS] == CallbackRserve::CALLBACK_SUCCESS) {
            if (isset($data[CallbackRserve::CALLBACK_R_GENERATED_ID])) {
                $r_generated_id = $data[CallbackRserve::CALLBACK_R_GENERATED_ID];
                unset($data[CallbackRserve::CALLBACK_R_GENERATED_ID]);
                unset($data[CallbackRserve::CALLBACK_KEY]);
                $this->moduleRModel->save_r_results(
                    array("result" => true, "data" => $data),
                    $data['id_users'],
                    $data['id_scheduledJobs'],
                    $r_generated_id
                );
            } else {
                array_push($result['selfhelpCallback'], 'No R generated id');
                $result[CallbackRserve::CALLBACK_STATUS] = CallbackRserve::CALLBACK_ERROR;
            }
        } else {
            $this->moduleRModel->save_r_results(
                array("result" => false, "data" => array("data" => $data, "msg" => $result)),
                $data['id_users'],
                $data['id_scheduledJobs'],
                null
            );
        }
        $end_time = microtime(true);
        $result['time'] = [];
        $result['time']['exec_time'] = $end_time - $start_time;
        $result['time']['start_date'] = $start_date;
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }
}
?>
