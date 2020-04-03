<?php
/**
 * @author Felix Rupp <kontakt@felixrupp.com>
 * @copyright Felix Rupp <kontakt@felixrupp.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */
script('user_cas', 'settings');
style('user_cas', 'settings');
?>

<form id="user_cas" class='section' method="post">

    <input type="hidden" autocomplete="false" />

    <h2><?php p($l->t('CAS Authentication backend')); ?>
        &nbsp;<?php p(\OCP\App::getAppInfo('user_cas')["version"]); ?></h2>

    <div id="casSettings" class="personalblock">
        <ul>
            <li><a href="#casSettings-1"><?php p($l->t('CAS Server')); ?></a></li>
            <li><a href="#casSettings-2"><?php p($l->t('Basic')); ?></a></li>
            <li><a href="#casSettings-3"><?php p($l->t('Mapping')); ?></a></li>
            <li><a href="#casSettings-4"><?php p($l->t('Groups')); ?></a></li>
            <li><a href="#casSettings-5"><?php p($l->t('ECAS Settings')); ?></a></li>
            <li><a href="#casSettings-6"><?php p($l->t('Import CLI')); ?></a></li>
            <li><a href="#casSettings-7"><?php p($l->t('phpCAS Library')); ?></a></li>
        </ul>
        <!-- CAS Server Settings -->
        <fieldset id="casSettings-1">
            <p><label for="cas_server_version"><?php p($l->t('CAS Server Version')); ?></label>
                <select id="cas_server_version" name="cas_server_version">
                    <?php $version = $_['cas_server_version']; ?>
                    <option value="3.0" <?php echo $version === '3.0' ? 'selected' : ''; ?>>CAS 3.0</option>
                    <option value="2.0" <?php echo $version === '2.0' ? 'selected' : ''; ?>>CAS 2.0</option>
                    <option value="1.0" <?php echo $version === '1.0' ? 'selected' : ''; ?>>CAS 1.0</option>
                    <option value="S1" <?php echo $version === 'S1' ? 'selected' : ''; ?>>SAML 1.1</option>
                </select>
            </p>
            <p><label for="cas_server_hostname"><?php p($l->t('CAS Server Hostname')); ?></label><input
                        id="cas_server_hostname"
                        name="cas_server_hostname"
                        value="<?php p($_['cas_server_hostname']); ?>">
            </p>
            <p><label for="cas_server_port"><?php p($l->t('CAS Server Port')); ?></label><input
                        id="cas_server_port"
                        name="cas_server_port"
                        placeholder="443"
                        autocomplete="off"
                        value="<?php if( !empty($_['cas_server_port']) ) { p($_['cas_server_port']); } else { p('443'); } ?>">
            </p>
            <p><label for="cas_server_path"><?php p($l->t('CAS Server Path')); ?></label><input
                        id="cas_server_path"
                        name="cas_server_path"
                        autocomplete="off"
                        placeholder="/cas"
                        value="<?php if( !empty($_['cas_server_path']) ) { p($_['cas_server_path']);} else { p('/cas'); } ?>">
            </p>
            <p><label for="cas_service_url"><?php p($l->t('Service URL')); ?></label><input
                        id="cas_service_url"
                        name="cas_service_url"
                        value="<?php p($_['cas_service_url']); ?>">
            </p>
            <p><label
                        for="cas_cert_path"><?php p($l->t('Certification file path (.crt).')); ?></label><input
                        id="cas_cert_path" name="cas_cert_path" value="<?php p($_['cas_cert_path']); ?>"> <span
                        class="csh"><?php p($l->t('Leave empty if you don’t want to validate your CAS server instance')); ?></span>
            </p>
            <p>
                <input type="checkbox" id="cas_use_proxy"
                      name="cas_use_proxy" <?php print_unescaped((($_['cas_use_proxy'] === 'true' || $_['cas_use_proxy'] === 'on' || $_['cas_use_proxy'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_use_proxy"><?php p($l->t('Use CAS proxy initialization')); ?></label>
            </p>
        </fieldset>
        <!-- Basic Settings -->
        <fieldset id="casSettings-2">
            <p><input type="checkbox" id="cas_force_login"
                      name="cas_force_login" <?php print_unescaped((($_['cas_force_login'] === 'true' || $_['cas_force_login'] === 'on' || $_['cas_force_login'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_force_login"><?php p($l->t('Force user login using CAS?')); ?></label>
            </p>
            <p>
                <label for="cas_force_login_exceptions"><?php p($l->t('Don’t use force login on these client-IPs')); ?></label><input
                        id="cas_force_login_exceptions"
                        name="cas_force_login_exceptions"
                        value="<?php p($_['cas_force_login_exceptions']); ?>"
                    <?php print_unescaped((($_['cas_force_login'] === 'false' || $_['cas_force_login'] === 'off' || $_['cas_force_login'] === '0') ? 'disabled="disabled"' : '')); ?> />
                <span class="csh"><?php p($l->t('Comma separated list of client IP addresses (or address ranges), which won’t be forced to login if "Force user login" is enabled (e.g. 192.168.1.1-254,192.168.2.5)')) ?></span>
            </p>
            <p><input type="checkbox" id="cas_disable_logout"
                      name="cas_disable_logout" <?php print_unescaped((($_['cas_disable_logout'] === 'true' || $_['cas_disable_logout'] === 'on' || $_['cas_disable_logout'] === '1') ? 'checked="checked"' : ''));
                print_unescaped((($_['cas_force_login'] === 'true' || $_['cas_force_login'] === 'on' || $_['cas_force_login'] === '1') ? 'disabled="disabled"' : '')); ?>>
                <label class='checkbox'
                       for="cas_disable_logout"><?php p($l->t('Disable CAS logout (do not logout CAS-session, only instance-session)')); ?></label>
            </p>
            <p><input type="checkbox" id="cas_autocreate"
                      name="cas_autocreate" <?php print_unescaped((($_['cas_autocreate'] === 'true' || $_['cas_autocreate'] === 'on' || $_['cas_autocreate'] === '1' || $_['cas_autocreate'] === '') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_autocreate"><?php p($l->t('Autocreate user after first CAS login?')); ?></label>
            </p>

            <p><input type="checkbox" id="cas_update_user_data"
                      name="cas_update_user_data" <?php print_unescaped((($_['cas_update_user_data'] === 'true' || $_['cas_update_user_data'] === 'on' || $_['cas_update_user_data'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_update_user_data"><?php p($l->t('Update user data after each CAS login?')); ?></label>
            </p>

            <p><input type="checkbox" id="cas_disable_singlesignout"
                      name="cas_disable_singlesignout" <?php print_unescaped((($_['cas_disable_singlesignout'] === 'true' || $_['cas_disable_singlesignout'] === 'on' || $_['cas_disable_singlesignout'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_disable_singlesignout"><?php p($l->t('Disable CAS SingleSignout (do not logout instance-session if CAS-server sends SSO-Request)')); ?></label>
            </p>
            <p>
                <label for="cas_handlelogoutrequest_servers"><?php p($l->t('SingleSignout Servers')); ?></label><input
                        id="cas_handlelogout_servers"
                        name="cas_handlelogout_servers"
                        value="<?php p($_['cas_handlelogout_servers']); ?>"
                    <?php print_unescaped((($_['cas_disable_singlesignout'] === 'true' || $_['cas_disable_singlesignout'] === 'on' || $_['cas_disable_singlesignout'] === '1') ? 'disabled="disabled"' : '')); ?> />
                <span class="csh"><?php p($l->t('Comma separated list of servers which can send SingleSignout requests (leave empty if you do not have to restrict SingleSignout to defined servers)')) ?></span>
            </p>

            <p><input type="checkbox" id="cas_keep_ticket_ids"
                      name="cas_keep_ticket_ids" <?php print_unescaped((($_['cas_keep_ticket_ids'] === 'true' || $_['cas_keep_ticket_ids'] === 'on' || $_['cas_keep_ticket_ids'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_keep_ticket_ids"><?php p($l->t('Keep CAS-ticket-ids in URL?')); ?></label>
                <span class="csh">(<?php p($l->t('Beware: Potential security risk! Only activate, if you know what you are doing.')) ?>)</span>

            </p>
            <p>
                <label for="cas_login_button_label"><?php p($l->t('Overwrite Login Button Label')); ?></label><input
                        id="cas_login_button_label"
                        name="cas_login_button_label"
                        value="<?php p($_['cas_login_button_label']); ?>" />
            </p>

            <!-- <p><input type="checkbox" id="cas_link_to_ldap_backend"
                      name="cas_link_to_ldap_backend" <?php /*print_unescaped((($_['cas_link_to_ldap_backend'] === 'true' || $_['cas_link_to_ldap_backend'] === 'on' || $_['cas_link_to_ldap_backend'] === '1') ? 'checked="checked"' : ''));*/ ?>>
                <label class='checkbox'
                       for="cas_link_to_ldap_backend"><?php p($l->t('Link CAS authentication with LDAP users and groups backend')); ?></label>
            </p> -->

        </fieldset>
        <!-- Mapping Settings -->
        <fieldset id="casSettings-3">
            <p><label for="cas_email_mapping"><?php p($l->t('Email')); ?></label><input
                        id="cas_email_mapping"
                        name="cas_email_mapping"
                        value="<?php p($_['cas_email_mapping']); ?>" placeholder="email"/>
            </p>
            <p><label for="cas_displayName_mapping"><?php p($l->t('Display Name')); ?></label><input
                        id="cas_displayName_mapping"
                        name="cas_displayName_mapping"
                        value="<?php p($_['cas_displayName_mapping']); ?>" placeholder="displayName"/>
            </p>
            <p><label for="cas_group_mapping"><?php p($l->t('Groups')); ?></label><input
                        id="cas_group_mapping"
                        name="cas_group_mapping"
                        value="<?php p($_['cas_group_mapping']); ?>" placeholder="groups"/>
            </p>
            <p><label for="cas_quota_mapping"><?php p($l->t('Quota')); ?></label><input
                        id="cas_quota_mapping"
                        name="cas_quota_mapping"
                        value="<?php p($_['cas_quota_mapping']); ?>" placeholder="quota"/>
            </p>
        </fieldset>
        <!-- Groups -->
        <fieldset id="casSettings-4">
            <p><label
                        for="cas_protected_groups"><?php p($l->t('Locked Groups')); ?></label><input
                        id="cas_protected_groups" name="cas_protected_groups"
                        value="<?php p($_['cas_protected_groups']); ?>"
                        placeholder="group1,group2,group3"
                        title="<?php p($l->t('Multivalued field, use comma to separate values')); ?>"/> <span class="csh"><?php p($l->t('Groups that will not be unlinked from the user when sync the CAS server and the owncloud')); ?></span></p>
            <p><label
                        for="cas_default_group"><?php p($l->t('Default Group')); ?></label><input
                        id="cas_default_group" name="cas_default_group"
                        placeholder="defaultGroup"
                        value="<?php p($_['cas_default_group']); ?>"> <span class="csh"><?php p($l->t('Default group when autocreating users and no group data was found for the user')); ?></span></p>
            <p><label
                        for="cas_access_allow_groups"><?php p($l->t('Authorized CAS Groups')); ?></label><input
                        id="cas_access_allow_groups" name="cas_access_allow_groups"
                        value="<?php p($_['cas_access_allow_groups']); ?>"
                        placeholder="group1,group2,group3"
                        title="<?php p($l->t('Multivalued field, use comma to separate values')); ?>"/> <span class="csh"><?php p($l->t('Users in the following groups will be able to log into ownCloud, users not in one of the groups will be logged out immediately')); ?></span></p>
            <p><label
                        for="cas_access_group_quotas"><?php p($l->t('Group Quotas')); ?></label><input
                        id="cas_access_group_quotas" name="cas_access_group_quotas"
                        value="<?php p($_['cas_access_group_quotas']); ?>"
                        placeholder="group1:5GB,group2:20GB,group3:none"
                        title="<?php p($l->t('Multivalued field, use comma to separate values')); ?>"/></p>
            <p><label for="cas_groups_letter_filter"><?php p($l->t('Group Name Filter')); ?></label><input
                        id="cas_groups_letter_filter"
                        name="cas_groups_letter_filter"
                        value="<?php p($_['cas_groups_letter_filter']); ?>" placeholder="a-zA-Z0-9\.\-_ @"/> <span class="csh"><?php p($l->t('Attention: You must use PHP (PCRE) Regex syntax for the filter.')) ?></span>
            </p>
            <p>
                <input type="checkbox" id="cas_groups_letter_umlauts"
                      name="cas_groups_letter_umlauts" <?php print_unescaped((($_['cas_groups_letter_umlauts'] === 'true' || $_['cas_groups_letter_umlauts'] === 'on' || $_['cas_groups_letter_umlauts'] === '1') ? 'checked="checked"' : '')); ?>><label class='checkbox'
                       for="cas_groups_letter_umlauts"><?php p($l->t('Group Name Replace Umlauts')); ?></label>
            </p>
        </fieldset>
        <!-- ECAS Settings -->
        <fieldset id="casSettings-5">
            <p><input type="checkbox" id="cas_ecas_attributeparserenabled"
                      name="cas_ecas_attributeparserenabled" <?php print_unescaped((($_['cas_ecas_attributeparserenabled'] === 'true' || $_['cas_ecas_attributeparserenabled'] === 'on' || $_['cas_ecas_attributeparserenabled'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_ecas_attributeparserenabled"><?php p($l->t('Use ECAS Attribute Parser?')); ?></label>
            </p>
            <p><input type="checkbox" id="cas_ecas_request_full_userdetails"
                      name="cas_ecas_request_full_userdetails" <?php print_unescaped((($_['cas_ecas_request_full_userdetails'] === 'true' || $_['cas_ecas_request_full_userdetails'] === 'on' || $_['cas_ecas_request_full_userdetails'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_ecas_request_full_userdetails"><?php p($l->t('Request full user details?')); ?></label>
            </p>
            <p><label for="cas_ecas_accepted_strengths"><?php p($l->t('ECAS Strength')); ?></label>
                <select id="cas_ecas_accepted_strengths" name="cas_ecas_accepted_strengths">
                    <?php $strength = $_['cas_ecas_accepted_strengths']; ?>
                    <option value="" <?php echo $strength === '' ? 'selected' : ''; ?>><?php p($l->t('Not set')); ?></option>
                    <option value="BASIC" <?php echo $strength === 'BASIC' ? 'selected' : ''; ?>>BASIC</option>
                    <option value="MEDIUM" <?php echo $strength === 'MEDIUM' ? 'selected' : ''; ?>>MEDIUM</option>
                    <option value="HIGH" <?php echo $strength === 'HIGH' ? 'selected' : ''; ?>>HIGH</option>
                </select>
            </p>
            <p><label for="cas_ecas_assurance_level"><?php p($l->t('ECAS AssuranceLevel')); ?></label>
                <select id="cas_ecas_assurance_level" name="cas_ecas_assurance_level">
                    <?php $assuranceLevel = $_['cas_ecas_assurance_level']; ?>
                    <option value="" <?php echo $assuranceLevel === '' ? 'selected' : ''; ?>><?php p($l->t('Not set')); ?></option>
                    <option value="LOW" <?php echo $assuranceLevel === 'LOW' ? 'selected' : ''; ?>>LOW</option>
                    <option value="MEDIUM" <?php echo $assuranceLevel === 'MEDIUM' ? 'selected' : ''; ?>>MEDIUM</option>
                    <option value="HIGH" <?php echo $assuranceLevel === 'HIGH' ? 'selected' : ''; ?>>HIGH</option>
                    <option value="TOP" <?php echo $assuranceLevel === 'TOP' ? 'selected' : ''; ?>>TOP</option>
                </select>
            </p>
            <p>
                <label for="cas_ecas_retrieve_groups"
                       title=""><?php p($l->t('Query ECAS groups')); ?></label><input
                        id="cas_ecas_retrieve_groups" name="cas_ecas_retrieve_groups" placeholder="*"
                        value="<?php p($_['cas_ecas_retrieve_groups']); ?>"> <span class="csh"><?php p($l->t('Note down all groups which you want to receive from your ECAS instance, * returns all groups')); ?></span></p>
            <p>
                <label for="cas_ecas_internal_ip_range"><?php p($l->t('Don’t use Multi-Factor-Authentication on these client-IPs')); ?></label><input
                        id="cas_ecas_internal_ip_range"
                        name="cas_ecas_internal_ip_range"
                        value="<?php p($_['cas_ecas_internal_ip_range']); ?>" />
                <span class="csh"><?php p($l->t('Comma separated list of client IP addresses (or address ranges), which won’t be forced to use Multi-Factor-Authentication if "ECAS AssuranceLevel" is at least MEDIUM (e.g. 192.168.1.1-254,192.168.2.5)')) ?></span>
            </p>
        </fieldset>
        <!-- Import-CLI Settings -->
        <fieldset id="casSettings-6">

            <h3><?php p($l->t('ActiveDirectory (LDAP)')); ?>:</h3>

            <p><label for="cas_import_ad_host"><?php p($l->t('LDAP Host')); ?></label>
                <select id="cas_import_ad_protocol" name="cas_import_ad_protocol">
                    <?php $importAdProtocol = $_['cas_import_ad_protocol']; ?>
                    <option value="ldaps://" <?php echo $importAdProtocol === 'ldaps://' ? 'selected' : ''; ?>>ldaps://</option>
                    <option value="ldap://" <?php echo $importAdProtocol === 'ldap://' ? 'selected' : ''; ?>>ldap://</option>

                </select>
                <input
                        id="cas_import_ad_host"
                        name="cas_import_ad_host"
                        value="<?php p($_['cas_import_ad_host']); ?>" placeholder="ldap.mydomain.com"/>
                :
                <input
                        id="cas_import_ad_port"
                        name="cas_import_ad_port"
                        value="<?php p($_['cas_import_ad_port']); ?>" placeholder="636"/>
            </p>
            <p><label for="cas_import_ad_user"><?php p($l->t('LDAP User and Domain')); ?></label>
                <input
                        id="cas_import_ad_user"
                        name="cas_import_ad_user"
                        value="<?php p($_['cas_import_ad_user']); ?>" placeholder="admin"/>
                @
                <input
                        id="cas_import_ad_domain"
                        name="cas_import_ad_domain"
                        value="<?php p($_['cas_import_ad_domain']); ?>" placeholder="ldap.mydomain.com"/>
            </p>
            <p><label for="cas_import_ad_password"><?php p($l->t('LDAP User Password')); ?></label>
                <input
                        type="password"
                        id="cas_import_ad_password"
                        name="cas_import_ad_password"/>
            </p>
            <p><label for="cas_import_ad_base_dn"><?php p($l->t('LDAP Base DN')); ?></label>
                <input
                        id="cas_import_ad_base_dn"
                        name="cas_import_ad_base_dn"
                        value="<?php p($_['cas_import_ad_base_dn']); ?>" placeholder="OU=People,DC=mydomain,DC=com"/>
            </p>
            <p><label for="cas_import_ad_sync_filter"><?php p($l->t('LDAP Sync Filter')); ?></label>
                <input
                        id="cas_import_ad_sync_filter"
                        name="cas_import_ad_sync_filter"
                        value="<?php print_unescaped($_['cas_import_ad_sync_filter']); ?>" placeholder="(&(objectCategory=user)(objectClass=user)(memberof:1.2.840.113556.1.4.1941:=CN=owncloudusers,CN=Users,DC=mydomain,DC=com))"/>
            </p>
            <p><label for="cas_import_ad_sync_pagesize_value"><?php p($l->t('LDAP Sync Pagesize (1–1500)')); ?></label>
                <input
                        type="range"
                        min="1" max="1500" step="1"
                        id="cas_import_ad_sync_pagesize"
                        name="cas_import_ad_sync_pagesize"
                        value="<?php if(isset($_['cas_import_ad_sync_pagesize'])) { p($_['cas_import_ad_sync_pagesize']); } else { print_unescaped('1500'); } ?>"
                onchange="updateRangeInput(this.value, 'cas_import_ad_sync_pagesize_value');"/>
                <input type="number" id="cas_import_ad_sync_pagesize_value" size="4" maxlength="4" min="1" max="1500" value="<?php if(isset($_['cas_import_ad_sync_pagesize'])) { p($_['cas_import_ad_sync_pagesize']); } else { print_unescaped('1500'); } ?>">
            </p>

            <h3><?php p($l->t('CLI Attribute Mapping')); ?>:</h3>

            <p><label for="cas_import_map_uid"><?php p($l->t('UID/Username')); ?></label>
                <input
                        id="cas_import_map_uid"
                        name="cas_import_map_uid"
                        value="<?php p($_['cas_import_map_uid']); ?>" placeholder="sn"/>
            </p>
            <p><label for="cas_import_map_displayname"><?php p($l->t('Display Name')); ?></label>
                <input
                        id="cas_import_map_displayname"
                        name="cas_import_map_displayname"
                        value="<?php p($_['cas_import_map_displayname']); ?>" placeholder="givenname"/>
            </p>
            <p><label for="cas_import_map_email"><?php p($l->t('Email')); ?></label>
                <input
                        id="cas_import_map_email"
                        name="cas_import_map_email"
                        value="<?php p($_['cas_import_map_email']); ?>" placeholder="email"/>
            </p>

            <p><label for="cas_import_map_groups"><?php p($l->t('Groups')); ?></label>
                <input
                        id="cas_import_map_groups"
                        name="cas_import_map_groups"
                        value="<?php p($_['cas_import_map_groups']); ?>" placeholder="memberof"/>
            </p>
            <p><label for="cas_import_map_groups_description"><?php p($l->t('Group Name Field')); ?></label>
                <input
                        id="cas_import_map_groups_description"
                        name="cas_import_map_groups_description"
                        value="<?php p($_['cas_import_map_groups_description']); ?>" placeholder="description"/>
            </p>

            <p><label for="cas_import_map_quota"><?php p($l->t('Quota')); ?></label>
                <input
                        id="cas_import_map_quota"
                        name="cas_import_map_quota"
                        value="<?php p($_['cas_import_map_quota']); ?>" placeholder="quota"/>
            </p>
            <p><label for="cas_import_map_enabled"><?php p($l->t('Enable')); ?></label>
                <input
                        id="cas_import_map_enabled"
                        name="cas_import_map_enabled"
                        value="<?php p($_['cas_import_map_enabled']); ?>" placeholder="useraccountcontrol"/>
            </p>
            <p><label for="cas_import_map_enabled_and_bitwise"><?php p($l->t('Calculate Enable Attribute Bitwise AND with')); ?></label>
                <input
                        id="cas_import_map_enabled_and_bitwise"
                        name="cas_import_map_enabled_and_bitwise"
                        value="<?php p($_['cas_import_map_enabled_and_bitwise']); ?>" placeholder="2"/>
            </p>

            <p>
                <input type="checkbox" id="cas_import_merge"
                      name="cas_import_merge" <?php print_unescaped((($_['cas_import_merge'] === 'true' || $_['cas_import_merge'] === 'on' || $_['cas_import_merge'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_import_merge"><?php p($l->t('Merge Accounts')); ?></label>
            </p>
            <p>
                <input type="checkbox" id="cas_import_merge_enabled"
                      name="cas_import_merge_enabled" <?php print_unescaped((($_['cas_import_merge_enabled'] === 'true' || $_['cas_import_merge_enabled'] === 'on' || $_['cas_import_merge_enabled'] === '1') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_import_merge_enabled"><?php p($l->t('Prefer Enabled over Disabled Accounts on Merge')); ?></label>
            </p>
            <p><label for="cas_import_map_dn"><?php p($l->t('Merge Two Active Accounts by')); ?></label>
                <input
                        id="cas_import_map_dn"
                        name="cas_import_map_dn"
                        value="<?php p($_['cas_import_map_dn']); ?>" placeholder="dn"/>
            </p>
            <p><label for="cas_import_map_dn_filter"><?php p($l->t('Merge Two Active Accounts by: Filterstring')); ?></label>
                <input
                        id="cas_import_map_dn_filter"
                        name="cas_import_map_dn_filter"
                        value="<?php p($_['cas_import_map_dn_filter']); ?>" placeholder="cn=p"/>
            </p>
        </fieldset>
        <!-- phpCAS Settings -->
        <fieldset id="casSettings-7">
            <p>
                <label for="cas_php_cas_path"><?php p($l->t('Overwrite phpCAS path (CAS.php file)')); ?></label><input
                        id="cas_php_cas_path"
                        name="cas_php_cas_path"
                        value="<?php p($_['cas_php_cas_path']); ?>"/> <span class="csh"><?php p($l->t('Optional: Overwrite phpCAS path (CAS.php file) if you want to use your own version. Leave blank to use the shipped version.')); ?></span>
            </p>
            <p><label for="cas_debug_file"><?php p($l->t('PHP CAS debug file')); ?></label><input
                        id="cas_debug_file"
                        name="cas_debug_file"
                        value="<?php p($_['cas_debug_file']); ?>"/>
            </p>
        </fieldset>
        <input type="hidden" value="<?php p($_['requesttoken']); ?>" name="requesttoken"/>
        <input id="casSettingsSubmit" type="submit" value="<?php p($l->t('Save')); ?>"/>
    </div>
</form>