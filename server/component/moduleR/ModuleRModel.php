<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class ModuleRModel extends BaseModel
{

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
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
     * @return int
     *  The id of the new survey or false if the process failed.
     */
    public function update_script($sid, $name, $script)
    {
        try {
            $this->db->begin_transaction();
            $this->db->update_by_ids(RSERVE_TABLE_R_SCRIPTS, array("name" => $name, "script" => $script), array('id' => $sid));
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
     * @param return object
     * Return the script row
     */
    public function get_script($sid)
    {
        $sql = "SELECT *
                FROM r_scripts
                WHERE id = :id";
        return $this->db->query_db_first($sql, array(':id' => $sid));
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
}
