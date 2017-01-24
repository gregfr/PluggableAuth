<?php

    require_once dirname(__FILE__).'/iAUTH_plugin.intf.php';


/**
 * Implements basic LDAP/Activedirectory support
 *
 * The method used here is to check if the provided username/password allows to connect to the ldap
 * server (no lookup is performed)
 * An alternate method would be to log in with a default credential and lookup the user.
 */
class AUTH_ldap extends PHPDS_dependant implements iAUTH_plugin
{
    protected $source = array();

    /**
     * Try to connect to the ldap server with the given credentials
     *
     * @param array $credentials associative array ['username', 'password']
     * @return bool whether the connection was successful
     *
     * @throws PHPDS_exception
     *
     * @author greg <greg@phpdevhshell.org>
     */
    public function lookupUser($credentials)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $this->log('Ldap: looking up user "'.$username.'" in LDAP server ');

        $sourceConfig = $this->source;

        $server = parse_url($sourceConfig['url']);

        if (empty($server['host'])) {
            return false; // oops
        }

        $connect = ldap_connect($server['host'], empty($server['port']) ? 389 : $server['port']);
        ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

        //$connect=ldap_connect($server['host']);
        $this->log('Connected');
        if (!$connect) throw new PHPDS_exception('Unable to connect to the LDAP server');

        if ($sourceConfig['namePattern']) {
            $username = PU_sprintfn($sourceConfig['namePattern'], array($username));
        }

        if (!@ldap_bind($connect, $username, $password)) {
            return false;  // if we can't bind it's likely the user is unknown or the password is wrong
        }
        $this->log('Bound');

        return true;
    }

    /**
     * Adding a user to the ldap directory is not implemented
     *
     * @param string $username
     * @param string $password
     * @param string|null $sourceName
     * @return bool
     */
    public function addUser($username, $password, $sourceName = null)
    {
        return false;
    }
}


