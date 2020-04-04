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

namespace OCA\UserCAS\AppInfo;

use \OCP\AppFramework\App;
use \OCP\IContainer;

use OCA\UserCAS\Service\UserService;
use OCA\UserCAS\Service\AppService;
use OCA\UserCAS\Service\PhpCasTicket;
use OCA\UserCAS\Hooks\UserHooks;
use OCA\UserCAS\Controller\SettingsController;
use OCA\UserCAS\Controller\AuthenticationController;
use OCA\UserCAS\User\Backend;
use OCA\UserCAS\User\NextBackend;
use OCA\UserCAS\Service\LoggingService;

/**
 * Class Application
 *
 * @package OCA\UserCAS\AppInfo
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @since 1.4.0
 */
class Application extends App
{

    /**
     * Application constructor.
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = array())
    {

        parent::__construct('user_cas', $urlParams);

        $container = $this->getContainer();

        $container->registerService('User', function (IContainer $c) {
            return $c->query('UserSession')->getUser();
        });

        $container->registerService('Config', function (IContainer $c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $container->registerService('L10N', function (IContainer $c) {
            return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });

        $container->registerService('Logger', function (IContainer $c) {
            return $c->query('ServerContainer')->getLogger();
        });

        /**
         * Register LoggingService
         */
        $container->registerService('LoggingService', function (IContainer $c) {
            return new LoggingService(
                $c->query('AppName'),
                $c->query('Config'),
                $c->query('Logger')
            );
        });

        /**
         * Register AppService with config
         */
        $container->registerService('AppService', function (IContainer $c) {
            return new AppService(
                $c->query('AppName'),
                $c->query('Config'),
                $c->query('LoggingService'),
                $c->query('ServerContainer')->getUserManager(),
                $c->query('ServerContainer')->getUserSession(),
                $c->query('ServerContainer')->getURLGenerator(),
		$c->query(PhpCasTicket::class)
            );
        });


        // Workaround for Nextcloud >= 14.0.0
        if ($container->query('AppService')->isNotNextcloud()) {

            /**
             * Register regular Backend
             */
            $container->registerService('Backend', function (IContainer $c) {
                return new Backend(
                    $c->query('AppName'),
                    $c->query('Config'),
                    $c->query('LoggingService'),
                    $c->query('AppService'),
                    $c->query('ServerContainer')->getUserManager()
                );
            });
        } else {

            /**
             * Register Nextcloud Backend
             */
            $container->registerService('Backend', function (IContainer $c) {
                return new NextBackend(
                    $c->query('AppName'),
                    $c->query('Config'),
                    $c->query('LoggingService'),
                    $c->query('AppService'),
                    $c->query('ServerContainer')->getUserManager(),
                    $c->query('UserService')
                );
            });
        }

        /**
         * Register UserService with UserSession for login/logout and UserManager for create
         */
        $container->registerService('UserService', function (IContainer $c) {
            return new UserService(
                $c->query('AppName'),
                $c->query('Config'),
                $c->query('ServerContainer')->getUserManager(),
                $c->query('ServerContainer')->getUserSession(),
                $c->query('ServerContainer')->getGroupManager(),
                $c->query('AppService'),
                $c->query('LoggingService'),
		$c->query(PhpCasTicket::class)
            );
        });

        /**
         * Register SettingsController
         */
        $container->registerService('SettingsController', function (IContainer $c) {
            return new SettingsController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('Config'),
                $c->query('L10N')
            );
        });

        /**
         * Register AuthenticationController
         */
        $container->registerService('AuthenticationController', function (IContainer $c) {
            return new AuthenticationController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('Config'),
                $c->query('UserService'),
                $c->query('AppService'),
                $c->query('ServerContainer')->getUserSession(),
                $c->query('LoggingService')
            );
        });

        /**
         * Register UserHooks
         */
        $container->registerService('UserHooks', function (IContainer $c) {
            return new UserHooks(
                $c->query('AppName'),
                $c->query('ServerContainer')->getUserManager(),
                $c->query('ServerContainer')->getUserSession(),
                $c->query('Config'),
                $c->query('UserService'),
                $c->query('AppService'),
                $c->query('LoggingService'),
                $c->query('Backend'),
		$c->query(PhpCasTicket::class)
            );
        });
    }
}
