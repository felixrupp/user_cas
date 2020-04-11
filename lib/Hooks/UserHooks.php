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

use OCA\UserCAS\Exception\PhpCas\PhpUserCasLibraryNotFoundException;
use OCA\UserCAS\User\UserCasBackendInterface;
use \OCP\IUserManager;
use \OCP\IUserSession;
use \OCP\IConfig;

use OCA\UserCAS\Service\LoggingService;
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
     * @var \OCP\IUserManager $userManager
     */
    private $userManager;

    /**
     * @var \OCP\IUserSession $userSession
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
     * @var \OCA\UserCAS\Service\LoggingService
     */
    private $loggingService;

    /**
     * @var UserCasBackendInterface
     */
    private $backend;


    /**
     * UserHooks constructor.
     *
     * @param string $appName
     * @param \OCP\IUserManager $userManager
     * @param \OCP\IUserSession $userSession
     * @param \OCP\IConfig $config
     * @param \OCA\UserCAS\Service\UserService $userService
     * @param \OCA\UserCAS\Service\AppService $appService
     * @param \OCA\UserCAS\Service\LoggingService $loggingService
     * @param UserCasBackendInterface $backend
     */
    public function __construct($appName, IUserManager $userManager, IUserSession $userSession, IConfig $config, UserService $userService, AppService $appService, LoggingService $loggingService, UserCasBackendInterface $backend)
    {
        $this->appName = $appName;
        $this->userManager = $userManager;
        $this->userSession = $userSession;
        $this->config = $config;
        $this->userService = $userService;
        $this->appService = $appService;
        $this->loggingService = $loggingService;
        $this->backend = $backend;
    }

    /**
     * Register method.
     */
    public function register()
    {
        #$this->userSession->listen('\OC\User', 'preLogin', array($this, 'preLogin'));
        $this->userSession->listen('\OC\User', 'postLogin', array($this, 'postLogin'));
        $this->userSession->listen('\OC\User', 'postLogout', array($this, 'postLogout'));
    }


    /**
     * postLogin method to update user data.
     *
     * @param mixed $uid
     * @param string $password
     * @return bool
     * @throws \Exception
     *
     * @deprecated
     * @since 1.8.0
     */
    public function preLogin($uid, $password)
    {

        if (!$this->appService->isCasInitialized()) {

            try {

                $this->appService->init();
            } catch (PhpUserCasLibraryNotFoundException $e) {

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::FATAL, 'Fatal error with code: ' . $e->getCode() . ' and message: ' . $e->getMessage());

                return FALSE;
            }
        };

        if ($uid instanceof \OCP\IUser) {

            $user = $uid;
            $uid = $user->getUID();
        }
        else {

            $user  = $this->userManager->get($uid);
        }

        if (\phpCAS::isAuthenticated() && !$this->userSession->isLoggedIn()) {

            $casUid = \phpCAS::getUser();

            if ($casUid === $uid) {

                if (boolval($this->config->getAppValue($this->appName, 'cas_autocreate'))) {

                    $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCas pre login hook triggered. User: ' . $uid);

                    // Autocreate user if needed or create a new account in CAS Backend
                    if (is_null($user)) {

                        // create users if they do not exist
                        if (preg_match('/[^a-zA-Z0-9 _\.@\-]/', $uid)) {

                            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'Invalid username "' . $uid . '", allowed chars "a-zA-Z0-9" and "_.@-" ');

                            return FALSE;
                        } else {

                            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCAS creating a new user with UID: ' . $uid);

                            /** @var bool|\OCP\IUser the created user or false $uid */
                            $user = $this->userService->create($uid, $this->backend);

                            if ($user instanceof \OCP\IUser) {

                                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCAS created new user with UID: ' . $uid);
                            }
                        }
                    } else {

                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCAS no new user has been created.');
                    }
                }

                # Update the Backend of the user if necessary
                #$this->userService->updateBackend($user);
            }
        } else {

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCas pre login hook NOT triggered. User: ' . $uid);
        }

        return TRUE;
    }


    /**
     * postLogin method to update user data.
     *
     * @param mixed $uid
     * @param string $password
     * @return bool
     */
    public function postLogin($uid, $password)
    {

        if (!$this->appService->isCasInitialized()) {

            try {

                $this->appService->init();
            } catch (PhpUserCasLibraryNotFoundException $e) {

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::FATAL, 'Fatal error with code: ' . $e->getCode() . ' and message: ' . $e->getMessage());

                return FALSE;
            }
        };

        if ($uid instanceof \OCP\IUser) {

            $user = $uid;
            $uid = $user->getUID();
        } else {

            $user = $this->userManager->get($uid);
        }

        if (\phpCAS::isAuthenticated() && $this->userSession->isLoggedIn()) {

            if (boolval($this->config->getAppValue($this->appName, 'cas_update_user_data'))) {

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCas post login hook triggered. User: ' . $uid);

                // $cas_attributes may vary in name, therefore attributes are fetched to $attributes

                $casUid = \phpCAS::getUser();

                if ($casUid === $uid) {

                    # Update the Backend of the user if necessary
                    #$this->userService->updateBackend($user);

                    $casAttributes = \phpCAS::getAttributes();

                    # Test if an attribute parser added a new dimension to our attributes array
                    if (array_key_exists('attributes', $casAttributes)) {

                        $newAttributes = $casAttributes['attributes'];

                        unset($casAttributes['attributes']);

                        $casAttributes = array_merge($casAttributes, $newAttributes);
                    }

                    $casAttributesString = '';
                    foreach ($casAttributes as $key => $attribute) {

                        $attributeString = $this->convertArrayAttributeValuesForDebug($attribute);

                        $casAttributesString .= $key . ': ' . $attributeString . '; ';
                    }

                    // parameters
                    $attributes = array();
                    $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'Attributes for the user: ' . $uid . ' => ' . $casAttributesString);


                    // DisplayName
                    $displayNameMapping = $this->config->getAppValue($this->appName, 'cas_displayName_mapping');

                    $displayNameMappingArray = explode("+", $displayNameMapping);

                    $attributes['cas_name'] = '';

                    foreach ($displayNameMappingArray as $displayNameMapping) {

                        if (array_key_exists($displayNameMapping, $casAttributes)) {

                            $attributes['cas_name'] .= $casAttributes[$displayNameMapping] . " ";
                        }
                    }

                    $attributes['cas_name'] = trim($attributes['cas_name']);

                    if ($attributes['cas_name'] === '' && array_key_exists('displayName', $casAttributes)) {

                        $attributes['cas_name'] = $casAttributes['displayName'];
                    }


                    // E-Mail
                    $mailMapping = $this->config->getAppValue($this->appName, 'cas_email_mapping');
                    if (array_key_exists($mailMapping, $casAttributes)) {

                        $attributes['cas_email'] = $casAttributes[$mailMapping];
                    } else if (array_key_exists('mail', $casAttributes)) {

                        $attributes['cas_email'] = $casAttributes['mail'];
                    }


                    // Group handling
                    $groupMapping = $this->config->getAppValue($this->appName, 'cas_group_mapping');
                    $defaultGroup = $this->config->getAppValue($this->appName, 'cas_default_group');
                    # Test for mapped attribute from settings
                    if (array_key_exists($groupMapping, $casAttributes)) {

                        $attributes['cas_groups'] = $casAttributes[$groupMapping];
                    } # Test for standard 'groups' attribute
                    else if (array_key_exists('groups', $casAttributes)) {

                        $attributes['cas_groups'] = $casAttributes['groups'];
                    } else if (is_string($defaultGroup) && strlen($defaultGroup) > 0) {

                        $attributes['cas_groups'] = array($defaultGroup);

                        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'Using default group "' . $defaultGroup . '" for the user: ' . $uid);
                    }

                    // Group Quota handling
                    $groupQuotas = $this->config->getAppValue($this->appName, 'cas_access_group_quotas');
                    $groupQuotas = explode(",", $groupQuotas);

                    foreach ($groupQuotas as $groupQuota) {

                        $groupQuota = explode(":", $groupQuota);

                        if (is_array($groupQuota) && count($groupQuota) === 2) {

                            $attributes['cas_group_quota'][$groupQuota[0]] = $groupQuota[1];
                        }
                    }

                    // User Quota handling
                    // Overwrites group quota
                    $userQuotaMapping = $this->config->getAppValue($this->appName, 'cas_quota_mapping');

                    #$this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCas quota-mapping-contents: '.$userQuotaMapping);

                    if (array_key_exists($userQuotaMapping, $casAttributes)) {

                        $attributes['cas_quota'] = $casAttributes[$userQuotaMapping];
                    } else if (array_key_exists('quota', $casAttributes)) {

                        $attributes['cas_quota'] = $casAttributes['quota'];
                    }

                    // Try to update user attributes
                    $this->userService->updateUser($user, $attributes);
                }

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCas post login hook finished.');
            }
        } else {

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCas post login hook NOT triggered. User: ' . $uid);
        }

        return TRUE;
    }

    /**
     * postLogout hook method.
     *
     * @return boolean
     */
    public function postLogout()
    {
        if (!$this->appService->isCasInitialized()) {

            try {

                $this->appService->init();
            } catch (PhpUserCasLibraryNotFoundException $e) {

                $this->loggingService->write(\OCA\UserCas\Service\LoggingService::FATAL, 'Fatal error with code: ' . $e->getCode() . ' and message: ' . $e->getMessage());

                return FALSE;
            }
        };

        $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'Logout hook triggered.');

        if (!boolval($this->config->getAppValue($this->appName, 'cas_disable_logout'))) {

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCAS logging out.');

            # Reset cookie
            setcookie("user_cas_redirect_url", '/', null, '/');

            \phpCAS::logout(array("service" => $this->appService->getAbsoluteURL('/')));

        } else {

            $this->loggingService->write(\OCA\UserCas\Service\LoggingService::DEBUG, 'phpCAS not logging out, because CAS logout was disabled.');
        }

        return TRUE;
    }


    /**
     * Convert CAS Attribute values for debug reasons
     *
     * @param $attributes
     * @return string
     */
    private function convertArrayAttributeValuesForDebug($attributes)
    {

        if (is_array($attributes)) {
            $stringValue = '';

            foreach ($attributes as $attribute) {

                if (is_array($attribute)) {

                    $stringValue .= $this->convertArrayAttributeValuesForDebug($attribute);
                } else {

                    $stringValue .= $attribute . ", ";
                }
            }

            return $stringValue;
        }

        return $attributes;
    }
}