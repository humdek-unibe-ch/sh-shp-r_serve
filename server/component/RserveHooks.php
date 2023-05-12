<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../component/BaseHooks.php";
require_once __DIR__ . "/../../../../component/style/BaseStyleComponent.php";
require_once __DIR__ .'/../service/ext/rserve/vendor/autoload.php';


use Sentiweb\Rserve\Connection;
use Sentiweb\Rserve\Parser\NativeArray;
use Sentiweb\Rserve\Evaluator;

/**
 * The class to define the hooks for the plugin.
 */
class RserveHooks extends BaseHooks
{
    /* Constructors ***********************************************************/

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
    }

    /* Private Methods *********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Set csp rules for Rserve     
     * @return string
     * Return csp_rules
     */
    public function test()
    {
        // Connect to the Rserve server
        $connection = new Connection('localhost', 6311);
        $result = $connection->evalString('sum <- 5 + 10; sum');
        var_dump($result);
        $connection->close();

        // Connect to the Rserve server
        $connection = new Connection('localhost', 6311);
        $connection->evalString("library(dplyr)");
        $connection->evalString("data <- iris %>% filter(Species == 'setosa') %>% select(Sepal.Length, Sepal.Width)");
        $result = $connection->evalString("data");
        var_dump($result);
        $connection->close();

        // Connect to the Rserve server
        $connection = new Connection('localhost', 6311);
        $connection->evalString('data <- data.frame(Name = c("John", "Jane", "Mark", "Emily"), Age = c(25, 30, 35, 40), Gender = c("Male", "Female", "Male", "Female"), Salary = c(50000, 60000, 70000, 80000), stringsAsFactors = FALSE)');
        $connection->evalString("filtered_data <- subset(data, Age > 30)");
        $connection->evalString('sorted_data <- data[order(data$Salary, decreasing = TRUE), ]');
        $connection->evalString('selected_data <- data[, c("Name", "Age", "Salary")]');
        $result = $connection->evalString('list(filtered_data, sorted_data, selected_data)');
        var_dump($result);
        $connection->close();
    }
}
?>
