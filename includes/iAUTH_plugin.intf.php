<?php

/**
 * This is an interface for implementation of the PluggableAuth
 */
interface iAUTH_plugin
{
    /**
     * Given the provided credential, is the user known to the authentication source?
     *
     * Note that this is only step 1 in actual authentication on the PHPDevShell server,
     * after that the user must be either:
     * - already known locally, in that case it receive his/her locally-configured access rights
     * - or created locally with some default access rights
     *
     * @param array $credentials
     * @return bool if true the user is considered as allowed to log in
     */
    public function lookupUser($credentials);
}

