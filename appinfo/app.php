<?php

/**
 * ownCloud - user_cas
 *
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @author Felix Rupp <kontakt@felixrupp.com>
 *
 * @copyright Sixto Martin Garcia. 2012
 * @copyright Leonis. 2014 <devteam@leonis.at>
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

$app = new \OCA\UserCAS\AppInfo\Application();
$c = $app->getContainer();

if (\OCP\App::isEnabled($c->getAppName())) {

    require_once __DIR__ . '/../vendor/phpCAS/CAS.php';

    $appService = $c->query('AppService');
    $userService = $c->query('UserService');

    // Initialize app
    //$appService->init();

    // Register User Backend
    $appService->registerBackend();

    // Register UserHooks
    $c->query('UserHooks')->register();

    // Register Admin Panel
    \OCP\App::registerAdmin($c->getAppName(), 'admin');

    // Register alternative LogIn
    \OC_App::registerLogIn(array('href' => $appService->linkToRoute($c->getAppName() . '.authentication.casLogin'), 'name' => 'CAS Login'));


    // Check for enforced authentication

     if ($appService->isEnforceAuthentication() && !$userService->isLoggedIn() ) {

         $appService->init();

         if ( !\phpCAS::isAuthenticated()) {

             \OCP\Util::writeLog('cas', "phpCAS Force authentification", \OCP\Util::DEBUG);

             \phpCAS::forceAuthentication();

             $loggedIn = $userService->login($c->query('Request'), '');

             if (!$loggedIn) {

                 $defaultPage = $c->query("Config")->getAppValue('core', 'defaultpage');

                 \OCP\Util::writeLog('cas', "phpCAS Force authentification defaultpage=" . $defaultPage, \OCP\Util::ERROR);

                 if ($defaultPage) {

                     $location = $this->appService->getAbsoluteURL($defaultPage);

                     return new \OCP\AppFramework\Http\RedirectResponse($location);
                 }
             }
        }
    }

}