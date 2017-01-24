<?php
/**
 * AUTH_userRegistration.php
 *
 * Controller file for the default user registration form
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PluggableAuth
 * @author   greg <greg@phpdevshell.org>
 * @license  unknown
 * @link     http://www.phpdevshell.org
 */

/**
 * @see http://www.phpdevshell.org/content/controller
 * @see http://www.phpdevshell.org/node/36
 * @see http://doc.phpdevshell.org/d3/d9a/class_p_h_p_d_s__controller.html
 */
class AUTH_userRegistration extends PHPDS_controller
{
    /**
     * General construction.
     *
     * @return object
     */
    public function construct()
    {
        // If you need a contructor, add the code here but DON'T FORGET to call the parent

        return parent::construct();
    }

    /**
     * This method is meant to be the entry point of your class. Most checks and cleanup should have been done by the time it's executed
     *
     * @return whatever, if you return "false" output will be truncated
     */
    public function execute()
    {
        $this->template->activatePlugin('GUI_forms');

        $validator = new FORM_validator('form');

        // These are the requirements declarations
        $validator->requires('user_name', 'notempty');
        $validator->requires('user_email', 'notempty');

        // form logic: is it unsubmitted, submitted but invalid, or submitted and valid?
        $good = $validator->isGood();
        if (true === $good) { // OK
            return array('result' => $validator->values());
        } elseif (false === $good) { // WRONG
            print $validator->spitErrors();
        }

        // note this will set up the requirements so it should be called AFTER the declarations
        print $validator->spitJS();

        // Your code here
        // to access the http request parameters, use something like:
        //    $value_of_a = $this->GET('a');
        $view = $this->factory('views');

//        $view->set('variable_name', $variable_value);

        $view->show();

        return true;
    }

    /**
     * This method is run if your controller is called in an ajax context
     *
     * @return mixed, there are 3 cases: "true" (or nothing)  the output will be handled by the template the usual way, "false" it's an error, otherwise the result data will be displayed in an empty template
     */
    public function viaAJAX()
    {
        // Your code here
    }

    /**
     * This is a sample Ajax remotely-called method
     *
     * In your JS code, write something like:
     *
     *		$.when(PHPDS_remoteCall('RemoteMethod', {'param1': valueOfParam1, 'param2': valueOfParam2}))
     *		  .then(function(result) {
     *				// do something with the result
     *		  });
     *
     */
    /*
    public function ajaxRemoteMethod($param1, $param2)
    {
        $result = $param1 + $param2;

        return $result;
    }
    */
}

return 'AUTH_userRegistration';