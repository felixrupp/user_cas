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

namespace OCA\UserCAS\User;

use OC\User\Database;
use OCA\UserCAS\Exception\PhpCas\PhpUserCasLibraryNotFoundException;
use OCA\UserCAS\Service\AppService;
use OCA\UserCAS\Service\LoggingService;
use OCP\IUserBackend;
use OCP\User\IProvidesDisplayNameBackend;
use OCP\User\IProvidesHomeBackend;


/**
 * Class Backend
 *
 * @package OCA\UserCAS\User
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @since 1.4.0
 */
class Backend extends Database implements UserCasBackendInterface
{

    /**
     * @var \OCA\UserCAS\Service\LoggingService $loggingService
     */
    private $loggingService;

    /**
     * @var \OCA\UserCAS\Service\AppService $appService
     */
    private $appService;

    /**
     * Backend constructor.
     * @param LoggingService $loggingService
     * @param AppService $appService
     */
    public function __construct(LoggingService $loggingService, AppService $appService)
    {

        parent::__construct();
        $this->loggingService = $loggingService;
        $this->appService = $appService;
    }


    /**
     * Backend name to be shown in user management
     * @return string the name of the backend to be shown
     */
    public function getBackendName()
    {

        return "CAS";
    }


    /**
     * @param string $uid
     * @param string $password
     * @return string|bool The users UID or false
     */
    public function checkPassword(string $uid, string $password)
    {

        if (!$this->appService->isCasInitialized()) {

            try {

                $this->appService->init();
            } catch (PhpUserCasLibraryNotFoundException $e) {

                $this->loggingService->write(\OCP\Util::FATAL, 'Fatal error with code: ' . $e->getCode() . ' and message: ' . $e->getMessage());

                header("Location: " . $this->appService->getAbsoluteURL('/'));
                die();
            }
        }

        if (\phpCAS::isInitialized()) {

            if (!\phpCAS::isAuthenticated()) {

                $this->loggingService->write(\OCP\Util::DEBUG, 'phpCAS user has not been authenticated.');

                return parent::checkPassword($uid, $password);

                #\OCP\Util::writeLog('cas', 'phpCAS user has not been authenticated.', \OCP\Util::ERROR);
            }

            if ($uid === FALSE) {

                $this->loggingService->write(\OCP\Util::ERROR, 'phpCAS returned no user.');
                #\OCP\Util::writeLog('cas', 'phpCAS returned no user.', \OCP\Util::ERROR);
            }

            if (\phpCAS::checkAuthentication()) {

                $casUid = \phpCAS::getUser();

                if ($casUid === $uid) {

                    $this->loggingService->write(\OCP\Util::DEBUG, 'phpCAS user password has been checked.');
                    #\OCP\Util::writeLog('cas', 'phpCAS user password has been checked.', \OCP\Util::ERROR);

                    return $uid;
                }
            }

            return FALSE;
        } else {

            $this->loggingService->write(\OCP\Util::ERROR, 'phpCAS has not been initialized.');
            #\OCP\Util::writeLog('cas', 'phpCAS has not been initialized.', \OCP\Util::ERROR);
            return FALSE;
        }
    }
}
