<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseComponent.php";
require_once __DIR__ . "/ModuleRView.php";
require_once __DIR__ . "/ModuleRModel.php";
require_once __DIR__ . "/ModuleRController.php";

/**
 * The class to define the asset select component.
 */
class ModuleRComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param number $id_page
     *  The parent page id
     */
    public function __construct($services, $params)
    {
        $sid = isset($params['sid']) ? intval($params['sid']) : null;
        $mode = isset($params['mode']) ? $params['mode'] : null;
        $model = new ModuleRModel($services, $sid);
        $controller = new ModuleRController($model, $mode, $sid);
        $view = new ModuleRView($model, $controller, $mode, $sid);
        parent::__construct($model, $view, $controller);
    }
}
?>
