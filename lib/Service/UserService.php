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
use OCA\UserCAS\User\UserCasBackendInterface;
use \OCP\IConfig;
use \OCP\IUserManager;
use \OCP\IGroupManager;
use \OCP\IUserSession;

use OCA\UserCAS\User\Backend;

/**
 * Class UserService
 *
 * @package OCA\UserCAS\Service
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @since 1.4
 */
class UserService
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
     * @var \OCP\IUserSession; $userSession
     */
    private $userSession;

    /**
     * @var \OCP\IUserManager $userManager
     */
    private $userManager;

    /**
     * @var \OCP\IGroupManager
     */
    private $groupManager;

    /**
     * @var AppService $appService
     */
    private $appService;

    /**
     * @var \OCA\UserCAS\User\Backend $backend
     */
    private $backend;

    /**
     * @var \OCA\UserCAS\Service\LoggingService $loggingService
     */
    private $loggingService;

    /** @var \OCP\UserInterface[] */
    private $oldBackends;


    /**
     * UserService constructor.
     *
     * @param $appName
     * @param IConfig $config
     * @param IUserManager $userManager
     * @param IUserSession $userSession
     * @param IGroupManager $groupManager
     * @param AppService $appService
     * @param UserCasBackendInterface $backend
     * @param LoggingService $loggingService
     */
    public function __construct($appName, IConfig $config, IUserManager $userManager, IUserSession $userSession, IGroupManager $groupManager, AppService $appService, UserCasBackendInterface $backend, LoggingService $loggingService)
    {

        $this->appName = $appName;
        $this->config = $config;
        $this->userManager = $userManager;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->appService = $appService;
        $this->backend = $backend;
        $this->loggingService = $loggingService;
    }

    /**
     * Login hook method.
     *
     * @param $request
     * @param string $uid
     * @param string|null $password
     * @return bool
     */
    public function login($request, $uid, $password = '')
    {

        $this->loggingService->write(\OCP\Util::DEBUG, 'phpCAS login function step 1.');
        #\OCP\Util::writeLog('cas', 'phpCAS login function step 1.', \OCP\Util::DEBUG);

        try {

            if (!boolval($this->config->getAppValue($this->appName, 'cas_autocreate')) && !$this->userExists($uid)) {

                $this->loggingService->write(\OCP\Util::DEBUG, 'phpCas autocreate disabled, and OC User does not exist, phpCas based login not possible. Bye.');

                return FALSE;
            }


            # Check if user may be authorized based on groups or not
            $cas_access_allow_groups = $this->config->getAppValue($this->appName, 'cas_access_allow_groups');
            if (is_string($cas_access_allow_groups) && strlen($cas_access_allow_groups) > 0) {

                $cas_access_allow_groups = explode(',', $cas_access_allow_groups);
                $casAttributes = \phpCAS::getAttributes();
                $casGroups = array();
                $isAuthorized = FALSE;

                $groupMapping = $this->config->getAppValue($this->appName, 'cas_group_mapping');

                # Test if an attribute parser added a new dimension to our attributes array
                if (array_key_exists('attributes', $casAttributes)) {

                    $newAttributes = $casAttributes['attributes'];

                    unset($casAttributes['attributes']);

                    $casAttributes = array_merge($casAttributes, $newAttributes);
                }

                # Test for mapped attribute from settings
                if (array_key_exists($groupMapping, $casAttributes)) {

                    $casGroups = (array)$casAttributes[$groupMapping];
                } # Test for standard 'groups' attribute
                else if (array_key_exists('groups', $casAttributes)) {

                    $casGroups = (array)$casAttributes['groups'];
                }

                foreach ($casGroups as $casGroup) {

                    if (in_array($casGroup, $cas_access_allow_groups)) {

                        $this->loggingService->write(\OCP\Util::DEBUG, 'phpCas CAS users login has been authorized with group: ' . $casGroup);

                        $isAuthorized = TRUE;
                    } else {

                        $this->loggingService->write(\OCP\Util::DEBUG, 'phpCas CAS users login has not been authorized with group: ' . $casGroup . ', because the group was not in allowedGroups: ' . implode(", ", $cas_access_allow_groups));
                    }
                }

                if ($this->groupManager->isInGroup($uid, 'admin')) {

                    $this->loggingService->write(\OCP\Util::DEBUG, 'phpCas CAS users login has been authorized with group: admin');

                    $isAuthorized = TRUE;
                }

                if (!$isAuthorized) {

                    $this->loggingService->write(\OCP\Util::DEBUG, 'phpCas CAS user is not authorized to log into ownCloud. Bye.');

                    return FALSE;
                }
            }


            # Log in the user
            $loginSuccessful = $this->userSession->login($uid, $password);

            $this->loggingService->write(\OCP\Util::DEBUG, 'phpCAS login function result: ' . $loginSuccessful);
            #\OCP\Util::writeLog('cas', 'phpCAS login function result: ' . $loginSuccessful, \OCP\Util::DEBUG);

            if ($loginSuccessful) {

                return $this->userSession->createSessionToken($request, $this->userSession->getUser()->getUID(), $uid, NULL);
            }

            $this->loggingService->write(\OCP\Util::DEBUG, 'phpCAS login function not successful.');
            #\OCP\Util::writeLog('cas', 'phpCAS login function not successful.', \OCP\Util::DEBUG);

            return FALSE;
        } catch (\OC\User\LoginException $e) {

            $this->loggingService->write(\OCP\Util::ERROR, 'Owncloud could not log in the user with UID: ' . $uid . '. Exception thrown with code: ' . $e->getCode() . ' and message: ' . $e->getMessage() . '.');
            #\OCP\Util::writeLog('cas', 'Owncloud could not log in the user with UID: ' . $uid . '. Exception thrown with code: ' . $e->getCode() . ' and message: ' . $e->getMessage() . '.', \OCP\Util::ERROR);

            return FALSE;
        }
    }

    /**
     * IsLoggedIn method.
     *
     * @return boolean
     */
    public function isLoggedIn()
    {

        return $this->userSession->isLoggedIn();
    }

    /**
     * @param string $userId
     * @return boolean|\OCP\IUser the created user or false
     * @throws \Exception
     */
    public function create($userId)
    {

        $randomPassword = $this->getNewPassword();

        return $this->userManager->createUser($userId, $randomPassword);
    }

    /**
     * @param string $userId
     * @return mixed
     */
    public function userExists($userId)
    {

        return $this->userManager->userExists($userId);
    }

    /**
     * Update the user
     *
     * @param \OCP\IUser $user
     * @param array $attributes
     */
    public function updateUser($user, $attributes)
    {

        $userId = $user->getUID();

        /*$attributesString = '';
        foreach ($attributes as $key => $attribute) {

            $attributesString .= $key . ': ' . $attribute . '; ';
        }*/

        $this->loggingService->write(\OCP\Util::DEBUG, 'Updating data of the user: ' . $userId);
        #\OCP\Util::writeLog('cas', 'Updating data of the user: ' . $userId, \OCP\Util::DEBUG);
        #\OCP\Util::writeLog('cas', 'Attributes: ' . $attributesString, \OCP\Util::DEBUG);

        if (isset($attributes['cas_email']) && is_object($user)) {

            $this->updateMail($user, $attributes['cas_email']);
        }
        if (isset($attributes['cas_name']) && is_object($user)) {

            $this->updateName($user, $attributes['cas_name']);
        }
        if (isset($attributes['cas_groups']) && is_object($user)) {

            $this->updateGroups($user, $attributes['cas_groups'], $this->config->getAppValue($this->appName, 'cas_protected_groups'));
        }
        if (isset($attributes['cas_group_quota']) && is_object($user)) {

            $this->updateQuota($user, $attributes['cas_group_quota']);
        }

        $this->loggingService->write(\OCP\Util::DEBUG, 'Updating data finished.');
        #\OCP\Util::writeLog('cas', 'Updating data finished.', \OCP\Util::DEBUG);
    }

    /**
     * Update the eMail address
     *
     * @param \OCP\IUser $user
     * @param string|array $email
     */
    private function updateMail($user, $email)
    {

        if (is_array($email)) {

            $email = $email[0];
        }

        if ($email !== $user->getEMailAddress()) {

            $user->setEMailAddress($email);
            $this->loggingService->write(\OCP\Util::DEBUG, 'Set email "' . $email . '" for the user: ' . $user->getUID());
            #\OCP\Util::writeLog('cas', 'Set email "' . $email . '" for the user: ' . $user->getUID(), \OCP\Util::DEBUG);
        }
    }

    /**
     * Update the display name
     *
     * @param \OCP\IUser $user
     * @param string| $name
     */
    private function updateName($user, $name)
    {

        if (is_array($name)) {

            $name = $name[0];
        }

        if ($name !== $user->getDisplayName()) {

            $user->setDisplayName($name);
            $this->loggingService->write(\OCP\Util::DEBUG, 'Set Name: ' . $name . ' for the user: ' . $user->getUID());
            #\OCP\Util::writeLog('cas', 'Set Name: ' . $name . ' for the user: ' . $user->getUID(), \OCP\Util::DEBUG);
        }
    }

    /**
     * Gets an array of groups and will try to add the group to OC and then add the user to the groups.
     *
     * @param \OCP\IUser $user
     * @param string $groups
     * @param string $protectedGroups
     * @param bool $justCreated
     */
    private function updateGroups($user, $groups, $protectedGroups = '', $justCreated = false)
    {

        if (is_string($groups)) $groups = explode(",", $groups);
        if (is_string($protectedGroups)) $protectedGroups = explode(",", $protectedGroups);

        $uid = $user->getUID();

        if (!$justCreated) {

            $oldGroups = $this->groupManager->getUserGroups($user);

            foreach ($oldGroups as $group) {

                if ($group instanceof \OCP\IGroup) {

                    $groupId = $group->getGID();

                    if (!in_array($groupId, $protectedGroups) && !in_array($groupId, $groups)) {

                        $group->removeUser($user);

                        $this->loggingService->write(\OCP\Util::DEBUG, "Removed '" . $uid . "' from the group '" . $groupId . "'");
                        #\OCP\Util::writeLog('cas', 'Removed "' . $uid . '" from the group "' . $groupId . '"', \OCP\Util::DEBUG);
                    }
                }
            }
        }

        foreach ($groups as $group) {

            $groupObject = NULL;

            if (preg_match('/[^a-zA-Z0-9 _\.@\-]/', $group)) {

                $this->loggingService->write(\OCP\Util::ERROR, "Invalid group '" . $group . "', allowed chars 'a-zA-Z0-9' and '_.@-' ");
                #\OCP\Util::writeLog('cas', 'Invalid group "' . $group . '", allowed chars "a-zA-Z0-9" and "_.@-" ', \OCP\Util::DEBUG);
            } else {

                if (!$this->groupManager->isInGroup($uid, $group)) {

                    if (!$this->groupManager->groupExists($group)) {

                        $groupObject = $this->groupManager->createGroup($group);

                        $this->loggingService->write(\OCP\Util::DEBUG, 'New group created: ' . $group);
                        #\OCP\Util::writeLog('cas', 'New group created: ' . $group, \OCP\Util::DEBUG);
                    } else {

                        $groupObject = $this->groupManager->get($group);
                    }

                    $groupObject->addUser($user);

                    $this->loggingService->write(\OCP\Util::DEBUG, "Added '" . $uid . "' to the group '" . $group . "'");
                    #\OCP\Util::writeLog('cas', 'Added "' . $uid . '" to the group "' . $group . '"', \OCP\Util::DEBUG);
                }
            }
        }
    }

    /**
     * @param \OCP\IUser $user
     * @param array $groupQuotas
     */
    private function updateQuota($user, $groupQuotas)
    {

        $defaultQuota = $this->config->getAppValue('files', 'default_quota');

        if ($defaultQuota === '' || $defaultQuota === 'none') {

            $defaultQuota = '0 B';
        }

        $uid = $user->getUID();
        $collectedQuotas = array();

        foreach ($groupQuotas as $groupName => $groupQuota) {

            if ($this->groupManager->isInGroup($uid, $groupName)) {

                if ($groupQuota === 'none') {

                    $collectedQuotas[PHP_INT_MAX] = $groupQuota;
                } elseif ($groupQuota === 'default') {

                    $defaultQuotaFilesize = \OCP\Util::computerFileSize($defaultQuota);

                    $collectedQuotas[$defaultQuotaFilesize] = $groupQuota;
                } else {

                    $groupQuotaComputerFilesize = \OCP\Util::computerFileSize($groupQuota);
                    $collectedQuotas[$groupQuotaComputerFilesize] = $groupQuota;
                }
            }
        }

        # Sort descending by key
        krsort($collectedQuotas);

        $newQuota = \OCP\Util::computerFileSize(array_shift($collectedQuotas));

        $usersOldQuota = $user->getQuota();

        if ($usersOldQuota === 'none') {

            $usersOldQuota = PHP_INT_MAX;
        } elseif ($usersOldQuota === 'default') {

            $usersOldQuota = \OCP\Util::computerFileSize($defaultQuota);
        } else {

            $usersOldQuota = \OCP\Util::computerFileSize($usersOldQuota);
        }

        $this->loggingService->write(\OCP\Util::DEBUG, "Default System Quota is: '" . $defaultQuota . "'");
        $this->loggingService->write(\OCP\Util::DEBUG, "User '" . $uid . "' old computerized Quota is: '" . $usersOldQuota . "'");
        $this->loggingService->write(\OCP\Util::DEBUG, "User '" . $uid . "' new computerized Quota would be: '" . $newQuota . "'");

        if ($usersOldQuota < $newQuota) {

            $user->setQuota($newQuota);

            $this->loggingService->write(\OCP\Util::DEBUG, "User '" . $uid . "' has new Quota: '" . $newQuota . "'");
        }
    }

    /**
     * Register User Backend.
     */
    public function registerBackend()
    {

        $this->userManager->registerBackend($this->backend);
    }

    /**
     * Generate a random PW with special char symbol characters
     *
     * @return string New Password
     */
    protected function getNewPassword()
    {

        $newPasswordCharsLower = \OC::$server->getSecureRandom()->generate(8, \OCP\Security\ISecureRandom::CHAR_LOWER);
        $newPasswordCharsUpper = \OC::$server->getSecureRandom()->generate(4, \OCP\Security\ISecureRandom::CHAR_UPPER);
        $newPasswordNumbers = \OC::$server->getSecureRandom()->generate(4, \OCP\Security\ISecureRandom::CHAR_DIGITS);
        $newPasswordSymbols = \OC::$server->getSecureRandom()->generate(4, \OCP\Security\ISecureRandom::CHAR_SYMBOLS);

        return str_shuffle($newPasswordCharsLower . $newPasswordCharsUpper . $newPasswordNumbers . $newPasswordSymbols);
    }

    /**
     * @return Backend
     */
    public function getBackend()
    {
        return $this->backend;
    }
}
