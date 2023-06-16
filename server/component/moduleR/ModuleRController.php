<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseController.php";
/**
 * The controller class of the group insert component.
 */
class ModuleRController extends BaseController
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $mode, $sid)
    {
        parent::__construct($model);
        if (isset($mode) && !$this->check_acl($mode)) {
            return false;
        }
        if ($mode === INSERT) {
            //insert mode
            $sid = $this->model->insert_new_script();
            if ($sid) {
                // redirect in update mode with the newly created survey id
                $url = $this->model->get_link_url("moduleRMode", array("mode" => UPDATE, "sid" => $sid));
                header('Location: ' . $url);
            }
        } else if ($mode === UPDATE && $sid > 0 && isset($_POST['name']) && isset($_POST['script']) && isset($_POST['test_variables'])) {
            $async = isset($_POST['async']) ? $_POST['async'] : 0;
            $res = $this->model->update_script($sid, $_POST['name'], $_POST['script'], $_POST['test_variables'], $async);            
            if ($res) {
                $this->success = true;
                $this->success_msgs[] = "[" . date("H:i:s") . "] Successfully updated script: " . $_POST['name'];
            } else {
                $this->fail = true;
                $this->error_msgs[] = "[" . date("H:i:s") . "] Failed to update script: " . $_POST['name'];
            }
        } else if (
            $mode === UPDATE && $sid > 0 && isset($_POST['mode']) && $_POST['mode'] == 'test_script'
            && isset($_POST['script']) && isset($_POST['test_variables'])
        ) {
            $test_variables = array();
            if ($_POST['test_variables'] != '') {
                $test_variables = json_decode($_POST['test_variables'], true);
            }
            $result = $this->model->execute_r_script($_POST['script'], $test_variables);
            header("Content-Type: application/json");
            echo json_encode($result);
            uopz_allow_exit(true);
            exit();
        } else if ($mode === DELETE && $sid > 0) {
            $del_res = $this->model->delete_script($sid);
            if ($del_res) {
                header('Location: ' . $this->model->get_link_url("moduleR"));
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete script: " . $sid;
            }
        }
    }

    /**
     * Check the acl for the current user and the current page
     * @return bool
     * true if access is granted, false otherwise.
     */
    protected function check_acl($mode)
    {
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleRMode"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You don't have rights to " . $mode . " this survey";
            return false;
        } else {
            return true;
        }
    }

    /* Public Methods *********************************************************/
}
?>
