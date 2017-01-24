<?php
/**
 * Pluggable/config/host.config.php
 *
 * This file will be executed whatever site is responding, in order to add the "/logout" virtual route
 *
 * PHP version 5
 *
 * @category PHP
 * @package  StandardLogin
 * @author   greg <greg@phpdevshell.org>
 * @license  unknown
 * @link     http://www.phpdevshell.org
 *
 */



/* @var PHPDS $this */
$this->PHPDS_classFactory()->registerClass('#AUTH_registrationService', 'AUTH_registrationService', 'PluggableAuth', 'AUTH_scenarios');