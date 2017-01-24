<?php
    /**
     * This class enables you to authenticate with your Google account.
     *
     * Uses PHPoAuthLib, this class is based on their sample code
     *
     * The library is installed by Composer in the "vendor" directory
     *
     * TODO: allow more than 1 service (use route or GET param)
     *
     * @see https://github.com/Lusitanian/PHPoAuthLib
     */

    use OAuth\OAuth2\Service\Google;
    use OAuth\Common\Storage\Session;
    use OAuth\Common\Consumer\Credentials;

    /**
     * Created by PhpStorm.
     * User: greg
     * Date: 24/01/17
     * Time: 03:45
     */
    class AUTH_oauth extends PHPDS_dependant //implements iPHPDS_deferred
    {
        protected $uriFactory;
        protected $currentUri;
        protected $serviceFactory;
        protected $storage;
        protected $credentials;
        protected $googleService;

        protected $go_label = 'external';

        public function before_display_login_form()
        {
            $credentials = $this->db->getSettings(array('credentials_google_key', 'credentials_google_secret'), 'AUTH');

            $html = '';

            if (!empty($credentials['credentials_google_key']) && !empty($credentials['credentials_google_secret'])) {
                $html = $this->google($credentials['credentials_google_key'], $credentials['credentials_google_secret']);
            }
            return $html;
        }

        public function google($key, $secret)
        {
            require_once __DIR__.'/../vendor/autoload.php';

            /**
             * Create a new instance of the URI class with the current URI, stripping the query string
             */
            $this->uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
            $this->currentUri = $this->uriFactory->createFromSuperGlobalArray($_SERVER);
            $this->currentUri->setQuery('');


            /** @var $serviceFactory \OAuth\ServiceFactory An OAuth service factory. */
            $this->serviceFactory = new \OAuth\ServiceFactory();

            // Session storage
            $this->storage = new Session();

            // Setup the credentials for the requests
            $this->credentials = new Credentials($key, $secret, $this->currentUri->getAbsoluteUri());

            // Instantiate the Google service using the credentials, http client and storage mechanism for the token
            /** @var $googleService Google */
            $this->googleService = $this->serviceFactory->createService('google', $this->credentials, $this->storage, array('userinfo_email', 'userinfo_profile'));


            if (!empty($_GET['code'])) {
                // retrieve the CSRF state parameter
                $state = isset($_GET['state']) ? $_GET['state'] : null;

                // This was a callback request from google, get the token
                $this->googleService->requestAccessToken($_GET['code'], $state);

                // Send a request with it
                $result = json_decode($this->googleService->request('userinfo'), true);

                if ($result['verified_email']) {
                    /* @var StandardLogin $this ->dependance */
                    $u = $this->dependance->lookupUser($result['email']);
                    if (!empty($u)) {
                        // TODO: improve forced login process
                        $this->dependance->setLogin($u);
                        if ($this->dependance->isLoggedIn()) {
                            $settings = $this->db->getSettings(array('front_page_id_in'));
                            PU_relocate('?m='.$settings['front_page_id_in']);
                        }
                    }
                }

            } elseif (!empty($_GET[$this->go_label]) && $_GET[$this->go_label] === 'google') {
                $url = $this->googleService->getAuthorizationUri();
                PU_relocate($url);
            } else {
                $url = $this->currentUri->getRelativeUri().'?'.$this->go_label.'=google';
                // TODO: use a template
                $html = '<div class="row">
                            <div class="column grid_4"><fieldset><legend>External Authentification</legend>
                    <a href="'.$url.'">
                        <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png"
                            alt="'.__('Connect with Google').'"/>
                    </a></fieldset></div></div>';

                return $html;
            }
        }
    }