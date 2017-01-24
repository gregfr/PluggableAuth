<?php

require_once 'includes/PHPDS_query.class.php';
require_once 'includes/PHPDS_login.class.php';
require_once 'includes/models/PHPDS_login.query.php';
require_once 'plugins/StandardLogin/models/StandardLogin.query.php';
require_once 'plugins/StandardLogin/includes/StandardLogin.class.php';


// TODO: allows default settings to be stored in DB

require_once dirname(__FILE__).'/iAUTH_plugin.intf.php';

/**
 * This class provides a substitute for the default StandardLogin,
 * allowing to authenticate from alternate sources
 *
 * Actual source-specific implementations are provided by plugins
 *
 * @author greg <greg@phpdevshell.org>
 * @package PluggableAuth
 *
 */
class AUTH_login extends StandardLogin
{
    public function loginForm($return = false)
    {
        $html = $this->inform('before_display_login_form');
        $html .= parent::loginForm(true);

        if ($return == false) {
            echo $html;
            return;
        } else {
            return $html;
        }
    }

    /**
     * Create a new authentication instance (interfacing iAUTH_plugin)
     *
     * If no classname is provided, a default name is taken from the general configuration
     *
     * If the requested source cannot be found, returns false (the call
     *
     * Note: no caching is provided since it's very unlikely we would need
     * the same source twice in the same request
     *
     * @param string $className (optional) name of the source
     *
     * @return iAUTH_plugin
     *
     * @throws PHPDS_exception
     *
     * @version 1.0
     * @date 20120208 (v1.0) (greg) created
     * @author greg <greg@phpdevshell.org>
     */
    public function getAuthPlugin($className = '')
    {
        $configuration = $this->configuration;
        if (empty($className)) {
            if (isset($configuration['auth']['default'])) {
                $className = $configuration['auth']['default'];
            }
        }

        if (!empty($className) && class_exists($className)) {
            $auth = $this->factory($className);
            if (is_a($auth, 'iAUTH_plugin')) {
                return $auth;
            }
            throw new PHPDS_exception('Wrong implementation of auth plugin');
        }

        //throw new PHPDS_exception('Unable to implement auth plugin');
        return false;
    }

    /**
     * Try to find the user's data based on the given credential
     *
     * If no password is provided, only the username is checked against the local database
     *
     * @param string $username
     * @param string $password
     * @return array the user's data array
     *
     * @version 1.0
     * @date 20120208 (v1.0) (greg) created
     * @author greg <greg@phpdevshell.org>
     */
    public function lookupUser($username, $password = '')
    {
        $this->log('Auth: looking up user '.$username);

        // first we check for a local user
        $user = parent::lookupUser($username);

        if (empty($password)) {
            // if no password is provided, we do nothing more
            return $user;
        }

        if (!empty($user)) { // if the username is know, we check it password, one way or another
            $this->log('User is known locally, checking password');
            $source = $user['user_password'];
            if ($source == $this->security->hashPassword($password)) {
                return $user; // local auth
            }
            // TODO: gently log and don't crash
            $plugin = $this->getAuthPlugin($source);
            if (is_a($plugin, 'iAUTH_plugin')
                && ($plugin->lookupUser(array('username' => $username, 'password' => $password)))
            ) {
                return $user;
            }

        } else { // we don't know this user, maybe we should create it
            $this->log('User is unknown locally, trying other methods');

            $plugin = $this->getAuthPlugin();
            if ($plugin) {
                $candidate = $plugin->lookupUser(array('username' => $username, 'password' => $password));
                if ($candidate) {
                    // create the use with the default auth source
                    if ($plugin->addUser($username, $password)) {
                        // since the new user should be in the DB, as a last check, we look it up
                        return parent::lookupUser($username);
                    }
                }
            }
        }

        return array();
    }

    /**
     * Checks to see if user and password is correct and allowed. Then creates session data accordingly.
     *
     * The main difference with the overridden method is that we CANNOT tell a bad password from a bad login
     *
     * @param string $username username part of the credentials
     * @param string $password password part of the credentials
     *
     * @return void
     *
     * @version 1.0
     * @date 20120208 (v1.0) (greg) created
     * @author greg <greg@phpdevshell.org>
     */
    public function processLogin($username, $password)
    {
        if (empty($username) || empty($password)) {
            $this->template->loginMessage
                = ___('You did not complete required username and password fields.');
        } else {
            $userArray = $this->lookupUser($username, $password);

            // Check if we have a login to process.
            if (! empty($userArray)) {
                $this->setLogin($userArray);
                if ($this->db->essentialSettings['allow_remember'] && isset($_POST['user_remember'])) {
                    $this->setUserCookie($userArray['user_id']);
                }
            } else {
                $this->template->loginMessage
                    = ___('Wrong <strong>username</strong> and/or <strong>password</strong>.');
            }
        }
    }

    public function setGuest()
    {
        parent::setGuest();
        $plugin = $this->getAuthPlugin();

        if (method_exists($plugin, 'setGuest')) {
            $plugin->setGuest();
        }
    }
}


