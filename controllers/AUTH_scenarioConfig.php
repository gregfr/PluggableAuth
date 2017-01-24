<?php
/**
 * AUTH_authConfig.php
 *
 * Controller file for the authentication configuration page
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PluggableAuth
 * @author   greg <greg@phpdevshell.org>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.phpdevshell.org
 */

require_once dirname(__FILE__) . '/../includes/AUTH_scenarios.class.php';

/**
 *
 * @see http://www.phpdevshell.org/content/controller
 * @see http://www.phpdevshell.org/node/36
 * @see http://doc.phpdevshell.org/d3/d9a/class_p_h_p_d_s__controller.html
 */
class AUTH_scenarioConfig extends PHPDS_controller
{
    /**
     * General construction.
     *
     * @return object
     */
    public function construct()
    {
        // If you need a constructor, add the code here but DON'T FORGET to call the parent

        return parent::construct();
    }

    /**
     * This method is meant to be the entry point of your class. Most checks and cleanup
     * should have been done by the time it's executed
     *
     * @return whatever, if you return "false" output will be truncated
     */
    public function execute()
    {
        // Your code here
        // to access the http request parameters, use something like:
        //    $value_of_a = $this->GET('a');
        $view = $this->factory('views');

        $configuration = $this->configuration;

        $this->settings = $this->db->getSettings(
            array(
                AUTH_registrationService::REGISTRATION_ROUTE,
                AUTH_registrationService::VALIDATION_ROUTE
            ),
            AUTH_registrationService::PLUGIN_NAME
        );

        $view->set('registration_route_key', AUTH_registrationService::REGISTRATION_ROUTE);
        $view->set('validation_route_key', AUTH_registrationService::VALIDATION_ROUTE);
        if (!empty($this->settings)) {
            $view->set(
                'registration_route_node',
                !empty($this->settings[AUTH_registrationService::REGISTRATION_ROUTE])
                    ? $this->settings[AUTH_registrationService::REGISTRATION_ROUTE]
                    : ''
            );
            $view->set(
                'validation_route_node',
                !empty($this->settings[AUTH_registrationService::VALIDATION_ROUTE])
                    ? $this->settings[AUTH_registrationService::VALIDATION_ROUTE]
                    : ''
            );

        }

        $view->show();
    }

    /**
     * This method is run if your controller is called in an ajax context
     *
     * @return mixed, there are 3 cases: "true" (or nothing)  the output will be handled by the template the
     * usual way, "false" it's an error, otherwise the result data will be displayed in an empty template
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
     *        $.when(PHPDS_remoteCall('RemoteMethod', {'param1': valueOfParam1, 'param2': valueOfParam2}))
     *          .then(function(result) {
     *                // do something with the result
     *          });
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

return 'AUTH_scenarioConfig';