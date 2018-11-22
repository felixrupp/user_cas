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

namespace OCA\UserCAS\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;
use \OCP\IL10N;
use \OCP\IConfig;


/**
 * Class SettingsController
 *
 * @package OCA\UserCAS\Controller
 *
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @since 1.4
 */
class SettingsController extends Controller
{
    /**
     * @var IL10N
     */
    private $l10n;

    /**
     * @var IConfig
     */
    private $config;


    /**
     * @var string
     */
    protected $appName;

    /**
     * SettingsController constructor.
     * @param $appName
     * @param IRequest $request
     * @param IConfig $config
     * @param IL10N $l10n
     */
    public function __construct($appName, IRequest $request, IConfig $config, IL10N $l10n)
    {
        $this->config = $config;
        $this->appName = $appName;
        $this->l10n = $l10n;
        parent::__construct($appName, $request);
    }

    /**
     * @AdminRequired
     *
     * @param string $cas_server_version
     * @param string $cas_server_hostname
     * @param string $cas_server_port
     * @param string $cas_server_path
     * @param string $cas_protected_groups
     * @param string $cas_default_group
     * @param string $cas_email_mapping
     * @param string $cas_displayName_mapping
     * @param string $cas_group_mapping
     * @param string $cas_cert_path
     * @param string $cas_debug_file
     * @param string $cas_php_cas_path
     * @param string $cas_service_url
     * @param string $cas_handlelogout_servers
     * @param string $cas_access_allow_groups
     * @param string $cas_ecas_accepted_strengths
     * @param string $cas_ecas_retrieve_groups
     * @param string $cas_ecas_assurance_level
     * @param string $cas_access_group_quotas
     * @param string $cas_force_login_exceptions
     * @param string|null $cas_ecas_attributeparserenabled
     * @param string|null $cas_ecas_request_full_userdetails
     * @param string|null $cas_force_login
     * @param string|null $cas_autocreate
     * @param string|null $cas_update_user_data
     * @param string|null $cas_link_to_ldap_backend
     * @param string|null $cas_disable_logout
     * @param string|null $cas_use_proxy
     * @return mixed
     */
    public function saveSettings($cas_server_version, $cas_server_hostname, $cas_server_port, $cas_server_path, $cas_protected_groups, $cas_default_group,
                                 $cas_email_mapping, $cas_displayName_mapping, $cas_group_mapping, $cas_cert_path, $cas_debug_file, $cas_php_cas_path, $cas_service_url, $cas_handlelogout_servers,
                                 $cas_access_allow_groups, $cas_ecas_accepted_strengths, $cas_ecas_retrieve_groups, $cas_ecas_assurance_level, $cas_access_group_quotas, $cas_force_login_exceptions,
                                 $cas_ecas_attributeparserenabled = NULL, $cas_ecas_request_full_userdetails = NULL, $cas_force_login = NULL, $cas_autocreate = NULL, $cas_update_user_data = NULL, $cas_link_to_ldap_backend = NULL, $cas_disable_logout = NULL, $cas_use_proxy = NULL)
    {

        try {

            $this->config->setAppValue($this->appName, 'cas_server_version', $cas_server_version);
            $this->config->setAppValue($this->appName, 'cas_server_hostname', $cas_server_hostname);
            $this->config->setAppValue($this->appName, 'cas_server_port', $cas_server_port, '443');
            $this->config->setAppValue($this->appName, 'cas_server_path', $cas_server_path, '/cas');

            $this->config->setAppValue($this->appName, 'cas_force_login_exceptions', $cas_force_login_exceptions);
            $this->config->setAppValue($this->appName, 'cas_protected_groups', $cas_protected_groups);
            $this->config->setAppValue($this->appName, 'cas_default_group', $cas_default_group);
            $this->config->setAppValue($this->appName, 'cas_access_allow_groups', $cas_access_allow_groups);
            $this->config->setAppValue($this->appName, 'cas_access_group_quotas', $cas_access_group_quotas);

            $this->config->setAppValue($this->appName, 'cas_email_mapping', $cas_email_mapping);
            $this->config->setAppValue($this->appName, 'cas_displayName_mapping', $cas_displayName_mapping);
            $this->config->setAppValue($this->appName, 'cas_group_mapping', $cas_group_mapping);

            $this->config->setAppValue($this->appName, 'cas_cert_path', $cas_cert_path);
            $this->config->setAppValue($this->appName, 'cas_debug_file', $cas_debug_file);
            $this->config->setAppValue($this->appName, 'cas_php_cas_path', $cas_php_cas_path);
            $this->config->setAppValue($this->appName, 'cas_service_url', $cas_service_url);
            $this->config->setAppValue($this->appName, 'cas_handlelogout_servers', $cas_handlelogout_servers);

            # ECAS settings
            $this->config->setAppValue($this->appName, 'cas_ecas_accepted_strengths', $cas_ecas_accepted_strengths);
            $this->config->setAppValue($this->appName, 'cas_ecas_retrieve_groups', $cas_ecas_retrieve_groups, '*');
            $this->config->setAppValue($this->appName, 'cas_ecas_assurance_level', $cas_ecas_assurance_level);

            # Checkbox settings
            $this->config->setAppValue($this->appName, 'cas_force_login', ($cas_force_login !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_autocreate', ($cas_autocreate !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_update_user_data', ($cas_update_user_data !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_link_to_ldap_backend', ($cas_link_to_ldap_backend !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_disable_logout', ($cas_disable_logout !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_ecas_attributeparserenabled', ($cas_ecas_attributeparserenabled !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_ecas_request_full_userdetails', ($cas_ecas_request_full_userdetails !== NULL) ? '1' : '0');
            $this->config->setAppValue($this->appName, 'cas_use_proxy', ($cas_use_proxy !== NULL) ? '1' : '0');


            return array(
                'code' => 200,
                'message' => $this->l10n->t('Your CAS settings have been updated.')
            );
        } catch (\Exception $e) {

            return array(
                'code' => 500,
                'message' => $this->l10n->t('Your CAS settings could not be updated. Please try again.')
            );
        }
    }
}