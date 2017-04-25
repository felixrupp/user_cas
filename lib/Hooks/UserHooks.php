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

namespace OCA\UserCAS\Hooks;

use \OC\User\Manager;
use \OC\User\Session;
use \OCP\IConfig;

use OCA\UserCAS\Service\UserService;
use OCA\UserCAS\Service\AppService;

/**
 * Class UserCAS_Hooks
 *
 * @package OCA\UserCAS\Hooks
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @since 1.4.0
 */
class UserHooks
{

    /**
     * @var string
     */
    private $appName;

    /**
     * @var \OC\User\Manager $userManager
     */
    private $userManager;

    /**
     * @var \OC\User\Session $userSession
     */
    private $userSession;

    /**
     * @var \OCP\IConfig
     */
    private $config;

    /**
     * @var \OCA\UserCAS\Service\UserService $userService
     */
    private $userService;

    /**
     * @var \OCA\UserCAS\Service\AppService $appService
     */
    private $appService;


    /**
     * UserHooks constructor.
     *
     * @param string $appName
     * @param \OC\User\Manager $userManager
     * @param \OC\User\Session $userSession
     * @param \OCP\IConfig $config
     * @param \OCA\UserCAS\Service\UserService $userService
     * @param \OCA\UserCAS\Service\AppService $appService
     */
    public function __construct($appName, Manager $userManager, Session $userSession, IConfig $config, UserService $userService, AppService $appService)
    {
        $this->appName = $appName;
        $this->userManager = $userManager;
        $this->userSession = $userSession;
        $this->config = $config;
        $this->userService = $userService;
        $this->appService = $appService;
    }

    /**
     * Register method.
     */
    public function register()
    {
        $this->userSession->listen('\OC\User', 'preLogin', array($this, 'preLogin'));
        $this->userSession->listen('\OC\User', 'postLogin', array($this, 'postLogin'));
        $this->userSession->listen('\OC\User', 'logout', array($this, 'logout'));
    }


    /**
     * postLogin method to update user data.
     *
     * @param $uid
     * @param $password
     * @return bool
     */
    public function preLogin($uid, $password)
    {

        if (!$this->appService->isCasInitialized()) $this->appService->init();

        if ($this->appService->isCasInitialized() && \phpCAS::isAuthenticated() && $this->config->getAppValue($this->appName, 'cas_autocreate') === 'true') {

            \OCP\Util::writeLog('cas', 'phpCas pre login hook triggered. User: ' . $uid, \OCP\Util::DEBUG);

            $casUid = \phpCAS::getUser();

            if ($casUid === $uid) {

                // Autocreate user if needed
                if (!$this->userService->userExists($uid)) {

                    // create users if they do not exist
                    if (preg_match('/[^a-zA-Z0-9 _\.@\-]/', $uid)) {

                        \OCP\Util::writeLog('cas', 'Invalid username "' . $uid . '", allowed chars "a-zA-Z0-9" and "_.@-" ', \OCP\Util::DEBUG);

                        return FALSE;
                    } else {

                        \OCP\Util::writeLog('cas', 'phpCAS creating a new user with UID: ' . $uid, \OCP\Util::DEBUG);

                        /** @var bool|\OCP\IUser the created user or false $uid */
                        $user = $this->userService->create($uid);

                        if ($user instanceof \OCP\IUser) {

                            \OCP\Util::writeLog('cas', 'phpCAS created new user with UID: ' . $uid, \OCP\Util::DEBUG);
                        }
                    }
                }

                return TRUE;
            }
        } else {

            \OCP\Util::writeLog('cas', 'phpCas pre login hook NOT triggered. User: ' . $uid, \OCP\Util::DEBUG);
        }

        return FALSE;
    }


    /**
     * postLogin method to update user data.
     *
     * @param \OCP\IUser $user
     * @return bool
     */
    public function postLogin(\OCP\IUser $user, $password)
    {

        if (!$this->appService->isCasInitialized()) $this->appService->init();

        $uid = $user->getUID();

        if ($this->appService->isCasInitialized() && \phpCAS::isAuthenticated() && $this->config->getAppValue($this->appName, 'cas_update_user_data') === 'true') {

            \OCP\Util::writeLog('cas', 'phpCas post login hook triggered. User: ' . $uid, \OCP\Util::DEBUG);

            // $cas_attributes may vary in name, therefore attributes are fetched to $attributes

            $casUid = \phpCAS::getUser();

            if ($casUid === $uid) {

                $casAttributes = \phpCAS::getAttributes();

                $casAttributesString = '';
                foreach ($casAttributes as $key => $attribute) {

                    $casAttributesString .= $key . ': ' . $attribute . '; ';
                }

                // parameters
                $attributes = array();

                \OCP\Util::writeLog('cas', 'Attributes for the user: ' . $uid . ' => ' . $casAttributesString, \OCP\Util::DEBUG);


                $displayNameMapping = $this->config->getAppValue($this->appName, 'cas_displayName_mapping');
                if (array_key_exists($displayNameMapping, $casAttributes)) {

                    $attributes['cas_name'] = $casAttributes[$displayNameMapping];
                } else {

                    if (isset($casAttributes['displayName'])) $attributes['cas_name'] = $casAttributes['displayName'];
                }

                $mailMapping = $this->config->getAppValue($this->appName, 'cas_email_mapping');
                if (array_key_exists($mailMapping, $casAttributes)) {

                    $attributes['cas_email'] = $casAttributes[$mailMapping];
                } else {

                    if (isset($casAttributes['mail'])) $attributes['cas_email'] = $casAttributes['mail'];
                }


                // Group handling
                $groupMapping = $this->config->getAppValue($this->appName, 'cas_group_mapping');
                $defaultGroup = $this->config->getAppValue($this->appName, 'cas_default_group');
                if (array_key_exists($groupMapping, $casAttributes)) {

                    $attributes['cas_groups'] = $casAttributes[$groupMapping];
                } else if (is_string($defaultGroup) && strlen($defaultGroup) > 0) {

                    $attributes['cas_groups'] = array($defaultGroup);
                    \OCP\Util::writeLog('cas', 'Using default group "' . $defaultGroup . '" for the user: ' . $uid, \OCP\Util::DEBUG);
                }

                // Try to update user attributes
                $this->userService->updateUser($user, $attributes);

                return TRUE;
            }
        } else {

            \OCP\Util::writeLog('cas', 'phpCas post login hook NOT triggered. User: ' . $uid, \OCP\Util::DEBUG);
        }

        return FALSE;
    }

    /**
     * Logout hook method.
     *
     * @return boolean
     */
    public function logout()
    {

        if (!$this->appService->isCasInitialized()) $this->appService->init();

        if ($this->config->getAppValue($this->appName, 'cas_disable_logout') === 'false' && $this->config->getAppValue($this->appName, 'cas_force_login') === 'true' && \phpCAS::isAuthenticated()) {

            \OCP\Util::writeLog('cas', 'phpCAS logging out. url='.$this->config->getAppValue($this->appName, 'cas_redirect_after_logout'), \OCP\Util::DEBUG);

            \phpCAS::logout(array("service" => $this->config->getAppValue($this->appName, 'cas_redirect_after_logout')));

        } else {
            \OCP\Util::writeLog('cas', 'phpCAS not logging out.', \OCP\Util::DEBUG);
        }

        return TRUE;
    }
}