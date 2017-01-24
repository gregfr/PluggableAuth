<?php

/*** WORK IN PROGRESS DO NOT USE ***/
/*** TODO: finish that ***/

/*
 * Configuration:
 *
 * $this->classes->register('AUTH_scenario', 'AUTH_noRegistrationScenario', 'No registration allowed');
 *
 */


/**
 * This is the logic engine to deal with various registration scenarios
 */
class AUTH_registrationService extends PHPDS_dependant implements iPHPDS_deferred
{
    const PLUGIN_NAME = 'PluggableAuth';
    const REGISTRATION_ROUTE = 'registrationRoute'; // ex. '/register'
    const VALIDATION_ROUTE = 'validation_route'; // ex. '/validate/<:key>'

    protected $settings = array();

    /**
     * Set up the registration engine
     *
     * @return void
     */
    public function construct()
    {
        $this->settings = $this->db->getSettings(
            array(
                AUTH_registrationService::REGISTRATION_ROUTE,
                AUTH_registrationService::VALIDATION_ROUTE
            ),
            AUTH_registrationService::PLUGIN_NAME
        );

        $this->router->addRoute(
            $this,
            $this->settings[AUTH_registrationService::REGISTRATION_ROUTE],
            AUTH_registrationService::PLUGIN_NAME
        );

//        $this->router->addRoute(
//            array($this, 'validationRouteCatcher'),
//            $this->settings[AUTH_registrationService::VALIDATION_ROUTE],
//            AUTH_registrationService::PLUGIN_NAME
//        );
    }

    public function reduce()
    {
        $path = $this->navigation->currentPath;
        switch ($path) {
            case $this->settings[AUTH_registrationService::REGISTRATION_ROUTE]:
                return '461706044';
                break;
            default: return false;
        }
    }

    public function success($controller_result = null)
    {
        print_r($controller_result);
        print 'success!';
    }

    public function failure($something = null)
    {

    }

    /**
     * Fetch the node ID of the registration page from the system-selected scenario
     *
     * Return false if registration is not allowed
     *
     * @version 1.0
     *
     * @date 20130302 (1.0) (greg) creation
     *
     * @author greg <greg@phpdevshell.org
     *
     * @param $params mixed
     * @return string|bool
     *
     */
    public function registrationRouteCatcher($params = null)
    {
        /* @var iAUTH_scenario $scenario */
        $scenario = $this->classes->factory('AUTH_scenario');

        return $scenario->getRegistrationNode();
    }

    /**
     * Register the user according to the system-selected scenario
     *
     * This method is meant to be called by the controller of the registration node, like this:
     *
     * $this->factory('&AUTH_registrationService')->registerUser($user)
     *
     * (note the ampersand to have a singleton)
     *
     * The user will probably be altered (hence the pass-by-reference declaration)
     *
     * @param PHPDS_user $user
     * @return boolean
     */
    public function registerUser(&$user)
    {
        /* @var iAUTH_scenario $scenario */
        $scenario = $this->classes->factory('AUTH_scenario');
        return $scenario->registerUser($user);
    }

    public function validationRouteCatcher($params = null)
    {
        /* @var iAUTH_scenario $scenario */
        $scenario = $this->classes->factory('AUTH_scenario');
        return $scenario->validateUser($params);
    }
}




/**
 * Interface to implement advanced registration scenarios
 */
interface iAUTH_scenario
{

    /**
     * Return the node ID for the node which will be used to present a new user with the registration form
     *
     * @return mixed
     */
    public function getRegistrationNode();


    /**
     * Alter the given user according to the scenario so it's considered as "registered"
     *
     * Return true on success
     *
     * @param PHPDS_user $user
     *
     * @return bool
     */
    public function registerUser($user);

    /**
     * Determine which user should be validated from the parameters and alter the user accordingly
     *
     * @params array|null $params the parameters provided by the router, if any
     *
     */
    public function validateUser($params = null);
}




class AUTH_registrationBaseScenario extends PHPDS_dependant
{

    /**
     * Send an email to the user with the given text
     *
     * @version 1.0
     *
     * @date 20130302 (1.0) (greg) creation
     *
     * @author greg <greg@phpdevshell.org
     *
     * @param string $message the body of the message
     * @param array $parameters optional parameters to build the message body
     *
     * @return bool
     *
     * @throw ExceptionType
     */
    public function sendUserEmailMessage($message, $parameters = null)
    {

    }

}





/**
 * This scenario is made for "private" server, where registrations are not allowed
 */
class AUTH_noRegistrationScenario extends AUTH_registrationBaseScenario implements iAUTH_scenario
{
    /**
     * In this scenario we don't accept registration
     */
    public function getRegistrationNode()
    {
        return false;
    }

    /**
     * {inheritDoc}
     *
     * @param PHPDS_user $user
     * @return bool
     */
    public function registerUser($user)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param array|null $params
     * @return bool
     */
    public function validateUser($params = null)
    {
        return false;
    }
}




/**
 * This scenario provides a very simple registration, with no validation
 */
class AUTH_directRegistrationScenario extends AUTH_registrationBaseScenario implements iAUTH_scenario
{
    const REGISTRATION_NODE_ID = 'registrationNodeID';
    const CONFIRM_EMAIL = 'registrationConfirmEmail';

    protected $settings = array();

    public function construct()
    {
        $this->settings = $this->db->getSettings(array(
            AUTH_directRegistrationScenario::REGISTRATION_NODE_ID,
            AUTH_directRegistrationScenario::CONFIRM_EMAIL
        ), AUTH_registrationService::PLUGIN_NAME);
    }

    /**
     * {@inheritDoc}
     *
     * @return boolean|string
     */
    public function getRegistrationNode()
    {
        return
            empty($this->settings[AUTH_directRegistrationScenario::REGISTRATION_NODE_ID])
                ? false
                : $this->settings[AUTH_directRegistrationScenario::REGISTRATION_NODE_ID];
    }

    /**
     * {@inheritDoc}
     *
     * @param PHPDS_user $user
     */
    public function registerUser($user)
    {
        $user->setState('registered')->save();// todo: this should be implemented

        $this->sendUserEmailConfirm();
    }

    /**
     * Send an email to the user with the confirmation message
     *
     * @return bool
     */
    public function sendUserEmailConfirm()
    {
        $message = $this->settings[AUTH_directRegistrationScenario::CONFIRM_EMAIL];
        return $this->sendUserEmailMessage($message);
    }

    /**
     * In this scenario we don't validate user explicitly
     *
     * @param null $params
     * @return bool
     */
    public function validateUser($params = null)
    {
        return false;
    }
}


/**
 * This scenario implement a classical "validation by email" process: once the user registered himself/herself
 * on the site, his/her
 */
class AUTH_emailValidationRegistrationScenario extends AUTH_directRegistrationScenario
{
    const WAIT_EMAIL = 'registrationWaitEmail';
    const VALIDATION_NODE_ID = 'validationNodeID';

    /**
     * @param PHPDS_user user
     */
    public function registerUser($user)
    {
        $user->setState('awaiting');

        $key = $this->security->encrypt($user->user_id);
        $url = '/validate/'.$key;

        $message = $this->settings[AUTH_emailValidationRegistrationScenario::WAIT_EMAIL];

        $this->sendUserEmailMessage($message, array('key' => $key, 'url' => $url));
    }

    public function validateUser($params = null)
    {
        $key = $this->security->decrypt($params['key']);

        $user = $this->factory('PHPDS_user', $key);
        $user->setState('registered');


        return
            empty($this->settings[AUTH_emailValidationRegistrationScenario::VALIDATION_NODE_ID]) ?
                false :
                $this->settings[AUTH_emailValidationRegistrationScenario::VALIDATION_NODE_ID];
    }
}








