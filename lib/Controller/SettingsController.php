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
    private $l10n;
    private $config;

    private $params = array('cas_server_version', 'cas_server_hostname', 'cas_server_port', 'cas_server_path', 'cas_force_login', 'cas_autocreate',
        'cas_update_user_data', 'cas_protected_groups', 'cas_default_group', 'cas_email_mapping', 'cas_displayName_mapping', 'cas_group_mapping',
        'cas_cert_path', 'cas_debug_file', 'cas_php_cas_path', 'cas_link_to_ldap_backend', 'cas_disable_logout', 'cas_service_url','cas_redirect_after_logout');

    protected $appName;

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
     * @param $cas_server_version
     * @param $cas_server_hostname
     * @param $cas_server_port
     * @param $cas_server_path
     * @param $cas_update_user_data
     * @param $cas_protected_groups
     * @param $cas_default_group
     * @param $cas_email_mapping
     * @param $cas_displayName_mapping
     * @param $cas_group_mapping
     * @param $cas_cert_path
     * @param $cas_debug_file
     * @param $cas_php_cas_path
     * @param $cas_service_url
     * @param null $cas_force_login
     * @param null $cas_autocreate
     * @param null $cas_update_user_data
     * @param null $cas_link_to_ldap_backend
     * @param null $cas_disable_logout
     * @param null $cas_redirect_after_logout
     * @return mixed
     */
    public function saveSettings($cas_server_version, $cas_server_hostname, $cas_server_port, $cas_server_path, $cas_protected_groups, $cas_default_group,
                                 $cas_email_mapping, $cas_displayName_mapping, $cas_group_mapping, $cas_cert_path, $cas_debug_file, $cas_php_cas_path, $cas_service_url,
                                 $cas_force_login = NULL, $cas_autocreate = NULL, $cas_update_user_data = NULL, $cas_link_to_ldap_backend = NULL, $cas_disable_logout = NULL,
                                 $cas_redirect_after_logout = NULL)
    {

        try {

            $this->config->setAppValue($this->appName, 'cas_server_version', $cas_server_version);
            $this->config->setAppValue($this->appName, 'cas_server_hostname', $cas_server_hostname);
            $this->config->setAppValue($this->appName, 'cas_server_port', $cas_server_port);
            $this->config->setAppValue($this->appName, 'cas_server_path', $cas_server_path);


            $this->config->setAppValue($this->appName, 'cas_protected_groups', $cas_protected_groups);
            $this->config->setAppValue($this->appName, 'cas_default_group', $cas_default_group);
            $this->config->setAppValue($this->appName, 'cas_email_mapping', $cas_email_mapping);
            $this->config->setAppValue($this->appName, 'cas_displayName_mapping', $cas_displayName_mapping);
            $this->config->setAppValue($this->appName, 'cas_group_mapping', $cas_group_mapping);
            $this->config->setAppValue($this->appName, 'cas_cert_path', $cas_cert_path);
            $this->config->setAppValue($this->appName, 'cas_debug_file', $cas_debug_file);
            $this->config->setAppValue($this->appName, 'cas_php_cas_path', $cas_php_cas_path);

            $this->config->setAppValue($this->appName, 'cas_service_url', $cas_service_url);

            $this->config->setAppValue($this->appName, 'cas_force_login', ($cas_force_login !== NULL) ? 'true' : 'false');
            $this->config->setAppValue($this->appName, 'cas_autocreate', ($cas_autocreate !== NULL) ? 'true' : 'false');
            $this->config->setAppValue($this->appName, 'cas_update_user_data', ($cas_update_user_data !== NULL) ? 'true' : 'false');
            $this->config->setAppValue($this->appName, 'cas_link_to_ldap_backend', ($cas_link_to_ldap_backend !== NULL) ? 'true' : 'false');
            $this->config->setAppValue($this->appName, 'cas_disable_logout', ($cas_disable_logout !== NULL) ? 'true' : 'false');
            $this->config->setAppValue($this->appName, 'cas_redirect_after_logout',$cas_redirect_after_logout);

            return array(
                'code' => 200,
                'message' => 'Your settings have been updated.'
            );
        } catch (\Exception $e) {

            return array(
                'code' => 500,
                'message' => 'Your settings could not be updated. Please try again.'
            );
        }
    }

    /**
     * @AdminRequired
     *
     * @return \OCP\AppFramework\Http\TemplateResponse
     */
    public function displayPanel()
    {

        $templateParams = array();

        foreach ($this->params as $param) {

            $value = htmlentities($this->config->getAppValue($this->appName, $param));
            $templateParams[$param] = $value;
        }

        $tmpl = new TemplateResponse($this->appName, 'admin', $templateParams, 'blank');

        return $tmpl->render();
    }
}