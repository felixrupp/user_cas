<?php

/**
 * ownCloud - user_cas
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\UserCAS\Service;

use OCA\UserCAS\Exception\PhpCas\PhpUserCasLibraryNotFoundException;
use \OCP\IConfig;
use \OCP\IUserSession;
use \OCP\IUserManager;
use \OCP\IURLGenerator;

/**
 * Class UserService
 *
 * @package OCA\UserCAS\Service
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @since 1.4.0
 */
class AppService
{

    /**
     * @var string $appName
     */
    private $appName;

    /**
     * @var \OCP\IConfig $appConfig
     */
    private $config;

    /**
     * @var \OCA\UserCAS\Service\LoggingService
     */
    private $loggingService;

    /**
     * @var \OCA\UserCAS\Service\PhpCasTicket $phpCasTicket
     */
    private $phpCasTicket;

    /**
     * @var \OCP\IUserManager $userManager
     */
    private $userManager;

    /**
     * @var \OCP\IUserSession $userSession
     */
    private $userSession;

    /**
     * @var \OCP\IURLGenerator $urlGenerator
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $casVersion;

    /**
     * @var string
     */
    private $casHostname;

    /**
     * @var int
     */
    private $casPort;

    /**
     * @var string
     */
    private $casPath;

    /**
     * @var string
     */
    private $casDebugFile;

    /**
     * @var string
     */
    private $casCertPath;

    /**
     * @var string
     */
    private $casPhpFile;

    /**
     * @var string
     */
    private $casServiceUrl;

    /**
     * @var boolean
     */
    private $casDisableLogout;

    /**
     * @var boolean
     */
    private $casDisableSinglesignout;

    /**
     * @var array
     */
    private $casHandleLogoutServers;

    /**
     * @var boolean
     */
    private $casKeepTicketIds;

    /**
     * @var string
     */
    private $cas_ecas_accepted_strengths;

    /**
     * @var string
     */
    private $cas_ecas_retrieve_groups;

    /**
     * @var string
     */
    private $cas_ecas_assurance_level;

    /**
     * @var boolean
     */
    private $cas_ecas_request_full_userdetails;

    /**
     * @var string
     */
    private $cas_ecas_internal_ip_range;

    /**
     * @var boolean
     */
    private $casInitialized;

    /**
     * @var boolean
     */
    private $ecasAttributeParserEnabled;

    /**
     * @var boolean
     */
    private $casUseProxy;

    /**
     * UserService constructor.
     * @param $appName
     * @param \OCP\IConfig $config
     * @param \OCA\UserCAS\Service\LoggingService $loggingService
     * @param \OCP\IUserManager $userManager
     * @param \OCP\IUserSession $userSession
     * @param \OCP\IURLGenerator $urlGenerator
     * @param \OCA\UserCAS\Service\PhpCasTicket $phpCasTicket
     */
    public function __construct($appName, IConfig $config, LoggingService $loggingService, IUserManager $userManager, IUserSession $userSession, IURLGenerator $urlGenerator, PhpCasTicket $phpCasTicket)
    {

        $this->appName = $appName;
        $this->config = $config;
        $this->loggingService = $loggingService;
        $this->userManager = $userManager;
        $this->userSession = $userSession;
        $this->urlGenerator = $urlGenerator;
	$this->phpCasTicket = $phpCasTicket;
        $this->casInitialized = FALSE;
    }


    /**
     * init method.
     * @throws PhpUserCasLibraryNotFoundException
     */
    public function init()
    {

        $serverHostName = (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : '';

        // Gather all app config values
        $this->casVersion = $this->config->getAppValue($this->appName, 'cas_server_version', '3.0');
        $this->casHostname = $this->config->getAppValue($this->appName, 'cas_server_hostname', $serverHostName);
        $this->casPort = intval($this->config->getAppValue($this->appName, 'cas_server_port', 443));
        $this->casPath = $this->config->getAppValue($this->appName, 'cas_server_path', '/cas');
        $this->casServiceUrl = $this->config->getAppValue($this->appName, 'cas_service_url', '');
        $this->casCertPath = $this->config->getAppValue($this->appName, 'cas_cert_path', '');


        // Correctly handle cas server path for document root
        if ($this->casPath === '/') {
            $this->casPath = '';
        }

        $this->casUseProxy = boolval($this->config->getAppValue($this->appName, 'cas_use_proxy', false));
        $this->casDisableLogout = boolval($this->config->getAppValue($this->appName, 'cas_disable_logout', false));
        $this->casDisableSinglesignout = boolval($this->config->getAppValue($this->appName, 'cas_disable_singlesignout', false));
        $logoutServersArray = explode(",", $this->config->getAppValue($this->appName, 'cas_handlelogout_servers', ''));
        $this->casHandleLogoutServers = array();
        $this->casKeepTicketIds = boolval($this->config->getAppValue($this->appName, 'cas_keep_ticket_ids', false));

        # ECAS
        $this->ecasAttributeParserEnabled = boolval($this->config->getAppValue($this->appName, 'cas_ecas_attributeparserenabled', false));
        $this->cas_ecas_request_full_userdetails = boolval($this->config->getAppValue($this->appName, 'cas_ecas_request_full_userdetails', false));
        $this->cas_ecas_accepted_strengths = $this->config->getAppValue($this->appName, 'cas_ecas_accepted_strengths');
        $this->cas_ecas_retrieve_groups = $this->config->getAppValue($this->appName, 'cas_ecas_retrieve_groups');
        $this->cas_ecas_assurance_level = $this->config->getAppValue($this->appName, 'cas_ecas_assurance_level');
        $this->cas_ecas_internal_ip_range = $this->config->getAppValue($this->appName, 'cas_ecas_internal_ip_range');


        foreach ($logoutServersArray as $casHandleLogoutServer) {

            $casHandleLogoutServer = ltrim(trim($casHandleLogoutServer));

            if (strlen($casHandleLogoutServer) > 4) {

                $this->casHandleLogoutServers[] = $casHandleLogoutServer;
            }
        }

        $this->casDebugFile = $this->config->getAppValue($this->appName, 'cas_debug_file', '');
        $this->casPhpFile = $this->config->getAppValue($this->appName, 'cas_php_cas_path', '');

        if (is_string($this->casPhpFile) && strlen($this->casPhpFile) > 0) {

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'Use custom phpCAS file:: ' . $this->casPhpFile);
            #\OCP\Util::writeLog('cas', 'Use custom phpCAS file:: ' . $this->casPhpFile, \OCA\UserCas\Service\LoggingService::DEBUG);

            if (is_file($this->casPhpFile)) {

                require_once("$this->casPhpFile");
            } else {

                throw new PhpUserCasLibraryNotFoundException('Your custom phpCAS library could not be loaded. The class was not found. Please disable the app with ./occ command or in Database and adjust the path to your library (or remove it to use the shipped library).', 500);
            }

        } else {

            if (is_file(__DIR__ . '/../../vendor/jasig/phpcas/CAS.php')) {

                require_once(__DIR__ . '/../../vendor/jasig/phpcas/CAS.php');
            } else {

                throw new PhpUserCasLibraryNotFoundException('phpCAS library could not be loaded. The class was not found.', 500);
            }
        }

        if (!class_exists('\\phpCAS')) {

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::ERROR, 'phpCAS library could not be loaded. The class was not found.');

            throw new PhpUserCasLibraryNotFoundException('phpCAS library could not be loaded. The class was not found.', 500);
        }

        if (!\phpCAS::isInitialized()) {

            try {

                \phpCAS::setVerbose(FALSE);

                if (!empty($this->casDebugFile)) {

                    \phpCAS::setDebug($this->casDebugFile);
                    \phpCAS::setVerbose(TRUE);
                }

		//Ticket not register in phpCAS but needed in order to know what ticket for what user...
		//For SingleSignOut
		if (!$this->casDisableSinglesignout) {
		    $this->phpCasTicket->saveTicket();
		}

                # Initialize client
                if ($this->casUseProxy) {

                    \phpCAS::proxy($this->casVersion, $this->casHostname, intval($this->casPort), $this->casPath);
                } else {

                    \phpCAS::client($this->casVersion, $this->casHostname, intval($this->casPort), $this->casPath);
                }

                # Handle SingleSignout requests
                if (!$this->casDisableSinglesignout) {

                    \phpCAS::setSingleSignoutCallback([$this->phpCasTicket, 'invalidateTokenByTicket']);
                    \phpCAS::handleLogoutRequests(true, $this->casHandleLogoutServers);
                }

                # Handle fixed service URL
                if (!empty($this->casServiceUrl)) {

                    \phpCAS::setFixedServiceURL($this->casServiceUrl);
                }

                # Handle certificate
                if (!empty($this->casCertPath)) {

                    \phpCAS::setCasServerCACert($this->casCertPath);
                } else {

                    \phpCAS::setNoCasServerValidation();
                }

                # Handle keeping of cas-ticket-ids
                if ($this->casKeepTicketIds) {

                    \phpCAS::setNoClearTicketsFromUrl();
                }

                # Handle ECAS Attributes if enabled
                if ($this->ecasAttributeParserEnabled) {

                    if (is_file(__DIR__ . '/../../vendor/ec-europa/ecas-phpcas-parser/src/EcasPhpCASParser.php')) {

                        require_once(__DIR__ . '/../../vendor/ec-europa/ecas-phpcas-parser/src/EcasPhpCASParser.php');
                    } else {

                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::ERROR, 'phpCAS EcasPhpCASParser library could not be loaded. The class was not found.');

                        throw new PhpUserCasLibraryNotFoundException('phpCAS EcasPhpCASParser could not be loaded. The class was not found.', 500);
                    }

                    # Register the parser
                    \phpCAS::setCasAttributeParserCallback(array(new \EcasPhpCASParser\EcasPhpCASParser(), 'parse'));
                    $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS EcasPhpCASParser has been successfully set.");
                }

                #### Register the new ticket validation url
                if ((is_string($this->cas_ecas_retrieve_groups) && strlen($this->cas_ecas_retrieve_groups) > 0)
                    || ($this->cas_ecas_request_full_userdetails)
                    || (is_string($this->cas_ecas_assurance_level) && strlen($this->cas_ecas_assurance_level) > 0)
                    || (is_string($this->cas_ecas_accepted_strengths) && strlen($this->cas_ecas_accepted_strengths) > 0)) {


                    ## Check for external IP Ranges to en-/disable the Two-Factor-Authentication (AcceptedStrength at least MEDIUM)
                    if ($this->isIpInLocalRange($this->cas_ecas_internal_ip_range) && $this->cas_ecas_accepted_strengths !== '') {

                        $this->cas_ecas_accepted_strengths = 'BASIC';
                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS AcceptedStrength Level is forced to BASIC, because the user is in the internal network. Test Address: " . $endIp . " | Users Remote Address: " . $remoteAddress);
                    }

                    # Add acceptedStrength Querystring Parameters
                    if (is_string($this->cas_ecas_accepted_strengths) && strlen($this->cas_ecas_accepted_strengths) > 0) {

                        # Register the new login url
                        $serverLoginUrl = \phpCAS::getServerLoginURL();

                        $serverLoginUrl = $this->buildQueryUrl($serverLoginUrl, 'acceptStrengths=' . urlencode($this->cas_ecas_accepted_strengths));

                        \phpCAS::setServerLoginURL($serverLoginUrl);

                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS strength attribute has been successfully set. New service login URL: " . $serverLoginUrl);
                    }

                    ## Change validation URL based on ECAS assuranceLevel
                    $newProtocol = 'http://';
                    $newUrl = '';
                    $newSamlUrl = '';

                    if ($this->getCasPort() === 443) {

                        $newProtocol = 'https://';
                    }

                    if ($this->getCasVersion() === "1.0") {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/validate';
                    } else if ($this->getCasVersion() === "2.0") {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/serviceValidate';
                    } else if ($this->getCasVersion() === "3.0") {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/p3/serviceValidate';
                    } else if ($this->getCasVersion() === "S1") {

                        $newSamlUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/samlValidate';
                    }

                    if (is_string($this->cas_ecas_assurance_level) && $this->cas_ecas_assurance_level === 'LOW') {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/laxValidate';
                    } else if (is_string($this->cas_ecas_assurance_level) && $this->cas_ecas_assurance_level === 'MEDIUM') {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/sponsorValidate';
                    } else if (is_string($this->cas_ecas_assurance_level) && $this->cas_ecas_assurance_level === 'HIGH') {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/interinstitutionalValidate';
                    } else if (is_string($this->cas_ecas_assurance_level) && $this->cas_ecas_assurance_level === 'TOP') {

                        $newUrl = $newProtocol . $this->getCasHostname() . $this->getCasPath() . '/strictValidate';
                    }

                    if (!empty($this->casServiceUrl)) {

                        $newUrl = $this->buildQueryUrl($newUrl, 'service=' . urlencode($this->casServiceUrl));
                        $newSamlUrl = $this->buildQueryUrl($newSamlUrl, 'TARGET=' . urlencode($this->casServiceUrl));
                    } else {

                        $newUrl = $this->buildQueryUrl($newUrl, 'service=' . urlencode(\phpCAS::getServiceURL()));
                        $newSamlUrl = $this->buildQueryUrl($newSamlUrl, 'TARGET=' . urlencode(\phpCAS::getServiceURL()));
                    }

                    # Add the groups to retrieve
                    if (is_string($this->cas_ecas_retrieve_groups) && strlen($this->cas_ecas_retrieve_groups) > 0) {

                        $newUrl = $this->buildQueryUrl($newUrl, 'groups=' . urlencode($this->cas_ecas_retrieve_groups));
                        $newSamlUrl = $this->buildQueryUrl($newSamlUrl, 'groups=' . urlencode($this->cas_ecas_retrieve_groups));
                    }

                    # Add the requestFullUserDetails flag
                    if ($this->cas_ecas_request_full_userdetails) {

                        $newUrl = $this->buildQueryUrl($newUrl, 'userDetails=' . urlencode('true'));
                        $newSamlUrl = $this->buildQueryUrl($newSamlUrl, 'userDetails=' . urlencode('true'));
                    }

                    # Set the user agent to mimic an ecas client
                    $userAgent = sprintf("ECAS PHP Client (%s, %s)",
                        '2.1.3',
                        $_SERVER['SERVER_SOFTWARE']);
                    \phpCAS::setExtraCurlOption(CURLOPT_USERAGENT, $userAgent);

                    # Set the new URLs
                    if ($this->getCasVersion() !== "S1" && !empty($newUrl)) {

                        \phpCAS::setServerServiceValidateURL($newUrl);
                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS additional attributes have been successfully set. New CAS " . $this->getCasVersion() . " service validate URL: " . $newUrl);

                    } elseif ($this->getCasVersion() === "S1" && !empty($newSamlUrl)) {

                        \phpCAS::setServerSamlValidateURL($newSamlUrl);
                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS additional attributes have been successfully set. New SAML 1.0 service validate URL: " . $newSamlUrl);
                    }
                }


                $this->casInitialized = TRUE;

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS has been successfully initialized.");
                #\OCP\Util::writeLog('cas', "phpCAS has been successfully initialized.", \OCA\UserCas\Service\LoggingService::DEBUG);

            } catch (\CAS_Exception $e) {

                \phpCAS::setVerbose(TRUE);

                $this->casInitialized = FALSE;

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::ERROR, "phpCAS has thrown an exception with code: " . $e->getCode() . " and message: " . $e->getMessage() . ".");
                #\OCP\Util::writeLog('cas', "phpCAS has thrown an exception with code: " . $e->getCode() . " and message: " . $e->getMessage() . ".", \OCA\UserCas\Service\LoggingService::ERROR);
            }
        } else {

            $this->casInitialized = TRUE;

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS has already been initialized.");
            #\OCP\Util::writeLog('cas', "phpCAS has already been initialized.", \OCA\UserCas\Service\LoggingService::DEBUG);
        }
    }

    /**
     * Test if the instance is not a Nextcloud instance
     *
     * @return bool
     */
    public function isNotNextcloud()
    {

        require __DIR__ . '/../../../../version.php';

        /**
         * @var string $vendor The vendor of this instance
         */

        #$this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS vendor: ".$vendor);

        if (strpos(strtolower($vendor), 'next') === FALSE) {

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Check if login should be enforced using user_cas.
     *
     * @param $remoteAddress
     * @return bool TRUE|FALSE
     */
    public function isEnforceAuthentication($remoteAddress)
    {

        $isEnforced = TRUE;

        $forceLoginExceptions = $this->config->getAppValue($this->appName, 'cas_force_login_exceptions', '');
        $forceLoginExceptionsArray = explode(',', $forceLoginExceptions);

        # Enforce off
        if ($this->config->getAppValue($this->appName, 'cas_force_login') !== '1') {

            $isEnforced = FALSE;
        } else {

            # Check enforce IP ranges
            foreach ($forceLoginExceptionsArray as $forceLoginException) {

                $forceLoginExceptionRanges = explode('-', $forceLoginException);

                if (isset($forceLoginExceptionRanges[0])) {

                    if (isset($forceLoginExceptionRanges[1])) {

                        $baseIpComponents = explode('.', $forceLoginExceptionRanges[0]);

                        $baseIp = $baseIpComponents[0] . '.' . $baseIpComponents[1] . '.';

                        $additionalIpComponents = explode('.', $forceLoginExceptionRanges[1]);

                        if (isset($additionalIpComponents[1]) && $additionalIpComponents[0]) {

                            # We have a two part range here (eg. 127.0.0.1-1.19) which means, we have to cover 127.0.0.1-127.0.0.254 and 127.0.1.1-127.0.1.19

                            for ($ipThirdPart = intval($baseIpComponents[2]); $ipThirdPart <= intval($additionalIpComponents[0]); $ipThirdPart++) {

                                if ($ipThirdPart !== intval($additionalIpComponents[0])) {

                                    $ipFourthPartMax = 254;
                                } else {

                                    $ipFourthPartMax = intval($additionalIpComponents[1]);
                                }

                                for ($ipFourthPart = intval($baseIpComponents[3]); $ipFourthPart <= $ipFourthPartMax; $ipFourthPart++) {

                                    $endIp = $baseIp . $ipThirdPart . '.' . $ipFourthPart;

                                    #$this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS Enforce Login IP checked: " . $endIp);

                                    if ($remoteAddress === $endIp) {

                                        $isEnforced = FALSE;

                                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS Enforce Login NOT triggered. Test Address: " . $endIp . " | Users Remote Address: " . $remoteAddress);
                                    }
                                }
                            }

                        } elseif ($additionalIpComponents[0]) {

                            # We have a one part range here (eg. 127.0.0.1-19)

                            $newIp = $baseIp . $baseIpComponents[2] . '.';

                            for ($ipFourthPart = intval($baseIpComponents[3]); $ipFourthPart <= intval($additionalIpComponents[0]); $ipFourthPart++) {

                                $endIp = $newIp . $ipFourthPart;

                                if ($remoteAddress === $endIp) {

                                    $isEnforced = FALSE;

                                    $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS Enforce Login NOT triggered. Test Address: " . $endIp . " | Users Remote Address: " . $remoteAddress);
                                }
                            }
                        }
                    } else {

                        # Single IP-Adress given
                        if ($remoteAddress === $forceLoginExceptionRanges[0]) {

                            $isEnforced = FALSE;

                            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS Enforce Login NOT triggered. Test Address: " . $forceLoginExceptionRanges[0] . " | Users Remote Address: " . $remoteAddress);
                        }
                    }
                }
            }
        }

        # User already logged in
        if ($this->userSession->isLoggedIn()) {

            $isEnforced = FALSE;
        }

        return $isEnforced;
    }

    /**
     * Register Login
     *
     */
    public function registerLogIn()
    {

        $loginButtonLabel = $this->config->getAppValue($this->appName, 'cas_login_button_label', 'CAS Login');

        // Login Button handling
        if (strlen($loginButtonLabel) <= 0) {

            $loginButtonLabel = 'CAS Login';
        }

        $this->unregisterLogin();

        if ($this->isNotNextcloud()) {

            /** @var array $loginAlternatives */
            /*$loginAlternatives = $this->config->getSystemValue('login.alternatives', []);

            $loginAlreadyRegistered = FALSE;

            foreach ($loginAlternatives as $key => $loginAlternative) {

                if (isset($loginAlternative['name']) && $loginAlternative['name'] === $loginButtonLabel) {

                    $loginAlternatives[$key]['href'] = $this->linkToRoute($this->appName . '.authentication.casLogin');
                    $this->config->setSystemValue('login.alternatives', $loginAlternatives);
                    $loginAlreadyRegistered = TRUE;
                }
            }

            if (!$loginAlreadyRegistered) {*/

            $loginAlternatives[] = ['href' => $this->linkToRoute($this->appName . '.authentication.casLogin'), 'name' => $loginButtonLabel, 'img' => substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "/index.php/")) . '/apps/user_cas/img/cas-logo.png'];

            $this->config->setSystemValue('login.alternatives', $loginAlternatives);
            #}
        } else {

            #$this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS Nextcloud " . $version[0] . "." . $version[1] . "." . $version[2] . "." . " detected.");
            \OC_App::registerLogIn(array('href' => $this->linkToRoute($this->appName . '.authentication.casLogin'), 'name' => $loginButtonLabel));
        }
    }

    /**
     * UnregisterLogin
     */
    public function unregisterLogin()
    {

        $loginButtonLabel = $this->config->getAppValue($this->appName, 'cas_login_button_label', 'CAS Login');

        // Login Button handling
        if (strlen($loginButtonLabel) <= 0) {

            $loginButtonLabel = 'CAS Login';
        }

        if ($this->isNotNextcloud()) {

            $loginAlternatives = $this->config->getSystemValue('login.alternatives', []);

            foreach ($loginAlternatives as $key => $loginAlternative) {

                if (isset($loginAlternative['name']) && ($loginAlternative['name'] === $loginButtonLabel || $loginAlternative['name'] === 'CAS Login')) {

                    unset($loginAlternatives[$key]);
                }
            }

            $this->config->setSystemValue('login.alternatives', $loginAlternatives);
        }
    }

    /**
     * @return bool
     */
    public function isSetupValid()
    {

        $casHostname = $this->config->getAppValue($this->appName, 'cas_server_hostname');
        $casPort = intval($this->config->getAppValue($this->appName, 'cas_server_port'));
        $casPath = $this->config->getAppValue($this->appName, 'cas_server_path');

        if (is_string($casHostname) && strlen($casHostname) > 1 && is_int($casPort) && $casPort > 1 && is_string($casPath) && strpos($casPath, "/") === 0) {

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Create a link to $route with URLGenerator.
     *
     * @param string $route
     * @param array $arguments
     * @return string
     */
    public function linkToRoute($route, $arguments = array())
    {

        return $this->urlGenerator->linkToRoute($route, $arguments);
    }

    /**
     * Create an absolute link to $route with URLGenerator.
     *
     * @param string $route
     * @param array $arguments
     * @return string
     */
    public function linkToRouteAbsolute($route, $arguments = array())
    {

        return $this->urlGenerator->linkToRouteAbsolute($route, $arguments);
    }

    /**
     * Create an url relative to owncloud host
     *
     * @param string $url
     * @return mixed
     */
    public function getAbsoluteURL($url)
    {

        return $this->urlGenerator->getAbsoluteURL($url);
    }

    /**
     * @return boolean
     */
    public function isCasInitialized()
    {
        return $this->casInitialized;
    }

    /**
     * This method is used to append query parameters to an url. Since the url
     * might already contain parameter it has to be detected and to build a proper
     * URL
     *
     * @param string $url base url to add the query params to
     * @param string $query params in query form with & separated
     *
     * @return string url with query params
     */
    private function buildQueryUrl($url, $query)
    {
        $url .= (strstr($url, '?') === false) ? '?' : '&';
        $url .= $query;
        return $url;
    }


    /**
     * Test if the client’s IP adress is in our local range
     *
     * @param string|array $internalIps
     * @return bool TRUE|FALSE
     */
    private function isIpInLocalRange($internalIps)
    {

        if (is_string($internalIps)) {

            $internalIps = explode(',', $internalIps);
        }

        $remoteAddress = $_SERVER['REMOTE_ADDR'];

        $proxyHeader = "HTTP_X_FORWARDED_FOR";

        # Header can contain multiple IP-s of proxies that are passed through.
        # Only the IP added by the last proxy (last IP in the list) can be trusted.
        if (array_key_exists($proxyHeader, $_SERVER)) {

            $proxyIp = trim(end(explode(",", $_SERVER[$proxyHeader])));

            if (filter_var($proxyIp, FILTER_VALIDATE_IP)) {

                $remoteAddress = $proxyIp;
            }
        }

        # Check enforce IP ranges for acceptedStrength attribute
        foreach ($internalIps as $internalIp) {

            $internalIpRanges = explode('-', $internalIp);

            if (isset($internalIpRanges[0])) {

                if (isset($internalIpRanges[1])) {

                    $baseIpComponents = explode('.', $internalIpRanges[0]);

                    $baseIp = $baseIpComponents[0] . '.' . $baseIpComponents[1] . '.';

                    $additionalIpComponents = explode('.', $internalIpRanges[1]);

                    if (isset($additionalIpComponents[1]) && $additionalIpComponents[0]) {

                        # We have a two part range here (eg. 127.0.0.1-1.19) which means, we have to cover 127.0.0.1-127.0.0.254 and 127.0.1.1-127.0.1.19

                        for ($ipThirdPart = intval($baseIpComponents[2]); $ipThirdPart <= intval($additionalIpComponents[0]); $ipThirdPart++) {

                            if ($ipThirdPart !== intval($additionalIpComponents[0])) {

                                $ipFourthPartMax = 254;
                            } else {

                                $ipFourthPartMax = intval($additionalIpComponents[1]);
                            }

                            for ($ipFourthPart = intval($baseIpComponents[3]); $ipFourthPart <= $ipFourthPartMax; $ipFourthPart++) {

                                $endIp = $baseIp . $ipThirdPart . '.' . $ipFourthPart;

                                #$this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS Enforce Login IP checked: " . $endIp);

                                if ($remoteAddress === $endIp) {

                                    return TRUE;

                                    /*if ($this->cas_ecas_accepted_strengths !== '') {

                                        $this->cas_ecas_accepted_strengths = 'BASIC';
                                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS AcceptedStrength Level is forced to BASIC, because the user is in the internal network. Test Address: " . $endIp . " | Users Remote Address: " . $remoteAddress);
                                    }*/
                                }
                            }
                        }

                    } elseif ($additionalIpComponents[0]) {

                        # We have a one part range here (eg. 127.0.0.1-19)

                        $newIp = $baseIp . $baseIpComponents[2] . '.';

                        for ($ipFourthPart = intval($baseIpComponents[3]); $ipFourthPart <= intval($additionalIpComponents[0]); $ipFourthPart++) {

                            $endIp = $newIp . $ipFourthPart;

                            if ($remoteAddress === $endIp) {

                                return TRUE;

                                /*if ($this->cas_ecas_accepted_strengths !== '') {

                                    $this->cas_ecas_accepted_strengths = 'BASIC';
                                    $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS AcceptedStrength is forced to BASIC, because the user is in the internal network. Test Address: " . $endIp . " | Users Remote Address: " . $remoteAddress);
                                }*/
                            }
                        }
                    }
                } else {

                    # Single IP-Adress given
                    if ($remoteAddress === $internalIpRanges[0]) {

                        return TRUE;

                        /*if ($this->cas_ecas_accepted_strengths !== '') {

                            $this->cas_ecas_accepted_strengths = 'BASIC';
                            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, "phpCAS ECAS AcceptedStrength is forced to BASIC, because the user is in the internal network. Test Address: " . $internalIpRanges[0] . " | Users Remote Address: " . $remoteAddress);
                        }*/
                    }
                }
            }
        }

        return FALSE;
    }

    ## Setters/Getters

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @return string
     */
    public function getCasVersion()
    {
        return $this->casVersion;
    }

    /**
     * @return string
     */
    public function getCasHostname()
    {
        return $this->casHostname;
    }

    /**
     * @return int
     */
    public function getCasPort()
    {
        return $this->casPort;
    }

    /**
     * @return string
     */
    public function getCasPath()
    {
        return $this->casPath;
    }

    /**
     * @return boolean
     */
    public function getCasDisableSinglesignout()
    {
        return $this->casDisableSinglesignout;
    }
}
