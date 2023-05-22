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
        $r_script = 'myString <- "{{question1}}"; stringLength <- nchar(myString);result <- list(stringLength = stringLength);result';
        $form_values = $this->user_input->get_form_values($args['task_info']['config']['form_data']['form_fields']);
        $r_script = $this->db->replace_calced_values($r_script, $form_values);
        $result = $this->moduleR->execute_r_script($r_script);
        // var_dump($result);
        return true;
    }


    /* Public Methods *********************************************************/

    /**
     * Set csp rules for Rserve     
     * @return string
     * Return csp_rules
     */
    public function test()
    {
        return;
        // // Connect to the Rserve server
        // $connection = new Connection('localhost', 6311);
        // $result = $connection->evalString('sum <- 5 + 10; sum');
        // // var_dump($result);
        // $connection->close();

        // // Connect to the Rserve server
        // $connection = new Connection('localhost', 6311);
        // $connection->evalString("library(dplyr)");
        // $connection->evalString("data <- iris %>% filter(Species == 'setosa') %>% select(Sepal.Length, Sepal.Width)");
        // $result = $connection->evalString("data");
        // // var_dump($result);
        // $connection->close();

        // // Connect to the Rserve server
        // $connection = new Connection('localhost', 6311);
        // $r_string = 'data <- data.frame(Name = c("John", "Jane", "Mark", "Emily"), Age = c(25, 30, 35, 40), Gender = c("Male", "Female", "Male", "Female"), Salary = c(50000, 60000, 70000, 80000), stringsAsFactors = FALSE);filtered_data <- subset(data, Age > 30);sorted_data <- data[order(data$Salary, decreasing = TRUE), ];selected_data <- data[, c("Name", "Age", "Salary")];list(filtered_data, sorted_data, selected_data)';
        // $result = $connection->evalString($r_string);
        // var_dump($result);
        // $connection->close();

        // // Connect to the Rserve server
        // $connection = new Connection('localhost', 6311);
        // // $connection->setAsync(true);
        // $r_string = 'require(udpipe);ud_model <- udpipe_download_model(language = "german");ud_model <- udpipe_load_model(ud_model$file_model);ud_model';
        // $res = $connection->evalString($r_string);
        // // $res = $connection->getResults();
        // var_dump($res);
        // // $connection->evalString("ud_model <- udpipe_download_model(language = 'german')");
        // // $connection->evalString('ud_model <- udpipe_load_model(ud_model$file_model)');
        // //$connection->evalString('x <- udpipe_annotate(ud_model, x = nlp$V1, doc_id = nlp$V1)');
        // //$connection->evalString('x <- as.data.frame(x)');
        // // $result = $connection->evalString('ud_model');
        // $connection->close();
    }

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
        $res = (string) $this->execute_private_method($args);
        $res = json_decode($res, true);
        $r_script = array(
            "type" => "string",
            "options" => array(
                "grid_columns" => 12,
                "enum_titles" => array(
                    "script1",
                    "script2",
                    "script3"
                ),
                "dependencies" => array(
                    "job_type" => array(
                        "r_script"
                    )
                )
            ),
            "title" => "R script",
            "description" => "Select R script",
            "enum" => array(
                "script1",
                "script2",
                "script3"
            )
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
