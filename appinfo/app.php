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
 */

$app = new \OCA\UserCAS\AppInfo\Application();
$c = $app->getContainer();

$enabled = TRUE;

$script = (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '');
$requestUri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');

if (in_array(basename($script), array('console.php', 'cron.php', 'status.php', 'version.php')) || strpos($requestUri, "/oauth2/") || strpos($requestUri, "/s/") || strpos($requestUri, 'logreader') || strpos($requestUri, '/gallery/slideshow') || preg_match('/.*(.*\.public).*/', $requestUri)) {
    $enabled = FALSE;
}

if (\OCP\App::isEnabled($c->getAppName()) && !\OC::$CLI && $enabled) {

    $appService = $c->query('AppService');
    $userService = $c->query('UserService');
    $loggingService = $c->query("LoggingService");

    // Register User Backend
    $userService->registerBackend();

    //if (!strpos($script, "ocs") && !strpos($requestUri, "oc.js") && !in_array(basename($script), array('public.php', 'remote.php'))) {
    if (!strpos($script, "ocs") 
        && !strpos($requestUri, "oc.js")
        && !strpos($requestUri, ".css")
        && !strpos($requestUri, "/js/")
        && !strpos($requestUri, "/theming/")
        && !strpos($requestUri, "/svg/")
        && !strpos($requestUri, "/publicpreview/")
        && !strpos($requestUri, "/files_pdfviewer/")
        && !strpos($requestUri, "/ajax/getstoragestats.php")
        
        && !in_array(basename($script), array('public.php', 'remote.php'))) {
            
        // URL params and redirect_url cookie
        setcookie("user_cas_enforce_authentication", "0", null, '/');
        $urlParams = '';

        if (isset($_REQUEST['redirect_url'])) {

            $urlParams = $_REQUEST['redirect_url'];
            // Save the redirect_rul to a cookie
            $cookie = setcookie("user_cas_redirect_url", "$urlParams", null, '/');
        }/*
        else {

            setcookie("user_cas_redirect_url", '/', null, '/');
        }*/

        // Register alternative LogIn
        $appService->registerLogIn();

        // Register UserHooks
        $c->query('UserHooks')->register();

        // Check for enforced authentication
        if ($appService->isEnforceAuthentication($_SERVER['REMOTE_ADDR']) && (!isset($_COOKIE['user_cas_enforce_authentication']) || (isset($_COOKIE['user_cas_enforce_authentication']) && $_COOKIE['user_cas_enforce_authentication'] === '0'))) {

            $loggingService->write(\OCP\Util::DEBUG, 'Enforce Authentication was: ' . $appService->isEnforceAuthentication($_SERVER['REMOTE_ADDR']));
            setcookie("user_cas_enforce_authentication", '1', null, '/');

            // Initialize app
            if (!$appService->isCasInitialized()) {

                try {
                    $appService->init();
                } catch (\OCA\UserCAS\Exception\PhpCas\PhpUserCasLibraryNotFoundException $e) {

                    $loggingService->write(\OCP\Util::FATAL, 'Fatal error with code: ' . $e->getCode() . ' and message: ' . $e->getMessage());

                    header("Location: " . $appService->getAbsoluteURL('/'));
                    die();
                }
            }

            if (!\phpCAS::isAuthenticated()) {

                $loggingService->write(\OCP\Util::DEBUG, 'Enforce Authentication was on and phpCAS is not authenticated. Redirecting to CAS Server.');

                $cookie = setcookie("user_cas_redirect_url", urlencode($requestUri), null, '/');

                header("Location: " . $appService->linkToRouteAbsolute($c->getAppName() . '.authentication.casLogin'));
                die();
            }
        }
    }
}