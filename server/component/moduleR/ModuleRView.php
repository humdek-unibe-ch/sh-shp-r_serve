<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseView.php";
require_once __DIR__ . "/../../../../../component/style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleRView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * Script id, 
     * if it is > 0  edit/delete script page     
     */
    private $sid;

    /**
     * The mode type of the form EDIT, DELETE, INSERT, VIEW     
     */
    private $mode;

    /**
     * the current selected script
     */
    private $script;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $mode, $sid)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->sid = $sid;
        if ($this->sid) {
            $this->script = $this->model->get_script($this->sid);
        }
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if (!$this->mode) {
            require __DIR__ . "/tpl_moduleR.php";
        } else {
            require __DIR__ . "/tpl_moduleR_Alerts.php";
            $card_title = '<span>R script </span>'  . (isset($this->survey['generated_id']) ? ('<div> <code>&nbsp;' . $this->survey['generated_id'] . '</code></div>') : '');          
            $rScriptHolderChildren = array(
                $this->output_check_multiple_users(true),
                new   BaseStyleComponent("div", array(
                    "css" => "mb-3 d-flex justify-content-between",
                    "children" => array(
                        new   BaseStyleComponent("div", array(
                            "css" => "",
                            "children" => array(
                                new BaseStyleComponent("button", array(
                                    "label" => "Back to All R Scripts",
                                    "url" => $this->model->get_link_url("moduleR"),
                                    "type" => "secondary",
                                ))
                            )
                        )),
                        new BaseStyleComponent("button", array(
                            "label" => "Delete R Script",
                            "id" => "r-script-delete-btn",
                            "url" => $this->model->get_link_url("moduleRMode", array("mode" => DELETE, "sid" => $this->sid)),
                            "type" => "danger",
                        ))
                    )
                )),
                new BaseStyleComponent("card", array(
                    "css" => "r-script-card",
                    "is_expanded" => true,
                    "is_collapsible" => false,
                    "type" => "warning",
                    "id" => "r-script-card",
                    "title" => $card_title,
                    "children" => array()
                ))
            );
            $rScriptHolder = new BaseStyleComponent("div", array(
                "css" => "m-3",
                "children" => $rScriptHolderChildren
            ));

            $rScriptHolder->output_content();
        }
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the alert message.
     */
    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @return array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        if (empty($local)) {
            $local = array(
            );
        }
        return parent::get_js_includes($local);
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @return array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        if (empty($local)) {
            if (DEBUG) {
                $local = array(
                );
            } else {
                $local = array(__DIR__ . "/../../../../rserve/css/ext/rserve.min.css?v=" . rtrim(shell_exec("git describe --tags")));
            }
        }
        return parent::get_css_includes($local);
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        //show create button
        $createButton = new BaseStyleComponent("button", array(
            "label" => "Create New R Script",
            "url" => $this->model->get_link_url("moduleRMode", array("mode" => INSERT)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $createButton->output_content();
    }

    /**
     * render the page content
     */
    public function output_page_content()
    {
        require __DIR__ . "/tpl_moduleR_table.php";
    }

    /**
     * Render the rows for the scripts
     */
    public function output_scripts_rows()
    {
        foreach ($this->model->get_scripts() as $script) {
            require __DIR__ . "/tpl_moduleR_row.php";
        }
    }
}
?>
