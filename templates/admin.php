<form id="user_cas" class='section' method="post">
    <h2><?php p($l->t('CAS Authentication backend')); ?></h2>

    <div id="casSettings" class="personalblock">
        <ul>
            <li><a href="#casSettings-1"><?php p($l->t('CAS Server')); ?></a></li>
            <li><a href="#casSettings-2"><?php p($l->t('Basic')); ?></a></li>
            <li><a href="#casSettings-3"><?php p($l->t('Mapping')); ?></a></li>
            <li><a href="#casSettings-4"><?php p($l->t('PHP-CAS Library')); ?></a></li>
        </ul>

        <fieldset id="casSettings-1">
            <p><label for="cas_server_version"><?php p($l->t('CAS Server Version')); ?></label>
                <select id="cas_server_version" name="cas_server_version">
                    <?php $version = $_['cas_server_version']; ?>
                    <option value="S1" <?php echo $version === 'S1' ? 'selected' : ''; ?>>SAML 1.1</option>
                    <option value="2.0" <?php echo $version === '2.0' ? 'selected' : ''; ?>>CAS 2.0</option>
                    <option value="1.0" <?php echo $version === '1.0' ? 'selected' : ''; ?>>CAS 1.0</option>
                </select>
            </p>
            <p><label for="cas_server_hostname"><?php p($l->t('CAS Server Hostname')); ?></label><input type="text"
                                                                                                        id="cas_server_hostname"
                                                                                                        name="cas_server_hostname"
                                                                                                        value="<?php p($_['cas_server_hostname']); ?>">
            </p>
            <p><label for="cas_server_port"><?php p($l->t('CAS Server Port')); ?></label><input type="text"
                                                                                                id="cas_server_port"
                                                                                                name="cas_server_port"
                                                                                                value="<?php p($_['cas_server_port']); ?>">
            </p>
            <p><label for="cas_server_path"><?php p($l->t('CAS Server Path')); ?></label><input type="text"
                                                                                                id="cas_server_path"
                                                                                                name="cas_server_path"
                                                                                                value="<?php p($_['cas_server_path']); ?>">
            </p>
            <p><label for="cas_service_url"><?php p($l->t('Service URL')); ?></label><input type="text"
                                                                                            id="cas_service_url"
                                                                                            name="cas_service_url"
                                                                                            value="<?php p($_['cas_service_url']); ?>">
            </p>
            <p><label
                    for="cas_cert_path"><?php p($l->t('Certification file path (.crt). Leave empty if dont want to validate')); ?></label><input
                    type="text" id="cas_cert_path" name="cas_cert_path" value="<?php p($_['cas_cert_path']); ?>"></p>


        </fieldset>
        <fieldset id="casSettings-2">
            <p><input type="checkbox" id="cas_force_login"
                      name="cas_force_login" <?php print_unescaped((($_['cas_force_login'] === 'true' || $_['cas_force_login'] === 'on') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox' for="cas_force_login"><?php p($l->t('Force user login using CAS?')); ?></label>
            </p>
            <div id="forceCAS">
                <p><label for="cas_redirect_after_logout"><?php p($l->t('Redirect after logout')); ?></label>
                    <input type="text" id="cas_redirect_after_logout" name="cas_redirect_after_logout" value="<?php p($_['cas_redirect_after_logout']); ?>">
                </p>
                <p><input type="checkbox" id="cas_disable_logout"
                          name="cas_disable_logout" <?php /*print_unescaped((($_['cas_disable_logout'] === 'true' || $_['cas_disable_logout'] === 'on') ? 'checked="checked"' : ''));
                                                            print_unescaped((($_['cas_force_login'] === 'false' || $_['cas_force_login'] === 'off') ? 'disabled="disabled"' : ''));*/ ?>>
                    <label class='checkbox'
                           for="cas_disable_logout"><?php p($l->t('Disable CAS logout (do only OwnCloud logout) [Only works when "Force user login using CAS?" is enabled]')); ?></label>
                </p>
            </div>
            <p><input type="checkbox" id="cas_autocreate"
                      name="cas_autocreate" <?php print_unescaped((($_['cas_autocreate'] === 'true' || $_['cas_autocreate'] === 'on') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_autocreate"><?php p($l->t('Autocreate user after CAS login?')); ?></label>
            </p>
            <!-- <p><input type="checkbox" id="cas_link_to_ldap_backend"
                      name="cas_link_to_ldap_backend" <?php /*print_unescaped((($_['cas_link_to_ldap_backend'] === 'true' || $_['cas_link_to_ldap_backend'] === 'on') ? 'checked="checked"' : ''));*/ ?>>
                <label class='checkbox'
                       for="cas_link_to_ldap_backend"><?php p($l->t('Link CAS authentication with LDAP users and groups backend')); ?></label>
            </p> -->
            <p><input type="checkbox" id="cas_update_user_data"
                      name="cas_update_user_data" <?php print_unescaped((($_['cas_update_user_data'] === 'true' || $_['cas_update_user_data'] === 'on') ? 'checked="checked"' : '')); ?>>
                <label class='checkbox'
                       for="cas_update_user_data"><?php p($l->t('Update user data after login?')); ?></label></p>
            <p><label
                    for="cas_protected_groups"><?php p($l->t('Groups that will not be unlinked from the user when sync the CAS server and the owncloud')); ?></label><input
                    type="text" id="cas_protected_groups" name="cas_protected_groups"
                    value="<?php p($_['cas_protected_groups']); ?>"
                    original-title="<?php p($l->t('Multivalued field, use comma to separate values')); ?>"/></p>
            <p><label
                    for="cas_default_group"><?php p($l->t('Default group when autocreating users and no group data was found for the user')); ?></label><input
                    type="text" id="cas_default_group" name="cas_default_group"
                    value="<?php p($_['cas_default_group']); ?>"></p>
            <input type="hidden" value="<?php p($_['requesttoken']); ?>" name="requesttoken"/>
        </fieldset>
        <fieldset id="casSettings-3">
            <p><label for="cas_email_mapping"><?php p($l->t('Email')); ?></label><input type="text"
                                                                                        id="cas_email_mapping"
                                                                                        name="cas_email_mapping"
                                                                                        value="<?php p($_['cas_email_mapping']); ?>"/>
            </p>
            <p><label for="cas_displayName_mapping"><?php p($l->t('Display Name')); ?></label><input type="text"
                                                                                                     id="cas_displayName_mapping"
                                                                                                     name="cas_displayName_mapping"
                                                                                                     value="<?php p($_['cas_displayName_mapping']); ?>"/>
            </p>
            <p><label for="cas_group_mapping"><?php p($l->t('Group')); ?></label><input type="text"
                                                                                        id="cas_group_mapping"
                                                                                        name="cas_group_mapping"
                                                                                        value="<?php p($_['cas_group_mapping']); ?>"/>
            </p>
        </fieldset>
        <fieldset id="casSettings-4">
            <!-- <p><label for="cas_php_cas_path"><?php p($l->t('PHP CAS path (CAS.php file)')); ?></label><input type="text"
                                                                                                             id="cas_php_cas_path"
                                                                                                             name="cas_php_cas_path"
                                                                                                             value="<?php /*p($_['cas_php_cas_path']);*/ ?>"/>
            </p>-->
            <p><label for="cas_debug_file"><?php p($l->t('PHP CAS debug file')); ?></label><input type="text"
                                                                                                  id="cas_debug_file"
                                                                                                  name="cas_debug_file"
                                                                                                  value="<?php p($_['cas_debug_file']); ?>"/>
            </p>
        </fieldset>
        <input id="casSettingsSubmit" type="submit" value="<?php p($l->t('Save')); ?>"/>
    </div>
</form>