INTRODUCTION
============

This App provides CAS authentication support, using the phpCAS library of jasig/apereo.

INSTALLATION
============

1\. DEPENDENCIES
---------------

* ownCloud >= 10.0.0 and Nextcloud >= 14.0.0
* PHP >= 5.6, PHP 7.0 if possible
* Optional: [Composer Dependency Manager](https://getcomposer.org/), if you want to install via GIT.
* Optional: your own phpCAS installation with at least phpCAS 1.3.5.

This app does not require a standalone version of jasig’s/apereo’s phpCAS any longer. The library is shipped within composer dependencies, in the archive file you downloaded or the Market/App-Store version if used. Although you can configure to use your own version of jasig’s/apereo’s phpCAS library later on.

2\. Recommended - ownCloud Market:
----------------------------

1. Access the ownCloud web interface with a locally created ownCloud user with admin privileges.
2. Navigate to the market in your ownCloud instance.
3. Navigate to the Security category and find **CAS user and group backend**.
4. Install the app.
5. Access the administrations panel => Apps and enable the **CAS user and group backend** app.
6. Access the administration panel => User Authentication and configure the app.


3\. Basic - Release archive/Nextcloud Appstore:
---------------------------

1. Download the current stable release from [the github releases page](https://github.com/felixrupp/user_cas/releases) according to your platform (ownCloud or Nextcloud) or use the link provided on [apps.nextcloud.com](https://apps.nextcloud.com/apps/user_cas) for Nextcloud.
2. Unzip/Untar the archive.
3. Rename the unarchived folder to `user_cas` if not already named like that.
4. Move the `user_cas` folder to the apps folder of your platform installation.
5. Adjust the settings for the `user_cas` folder according to your webserver setup.
6. Access the platform web interface with a locally created platform user with admin privileges.
7. Access the administrations panel => Apps and enable the **CAS user and group backend** app.
8. Access the administration panel => Security and configure the app.


4\. Advanced for development purposes only – GIT clone with composer:
-------------------------

1. Git clone/copy the downloaded `user_cas` folder into the platform’s apps folder and make sure to set correct permissions for your webserver.
2. Change directory inside `user_cas` folder after cloning and perform a `composer update` command if you installed via GIT. The dependencies will be installed. Attention: You will need the [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) binary to be installed.
3. Adjust the settings for the `user_cas` folder according to your webserver setup.
4. Access the platform web interface with a locally created platform user with admin privileges.
5. Access the administrations panel => Apps and enable the **CAS user and group backend** app.
6. Access the administration panel => User Authentication/Security and configure the app.


**Be aware that you may need to add the following entry to your config/config.php to prevent warning messages about the missing app signature in your backend:**

```
'integrity.ignore.missing.app.signature' =>
    array (
        0 => 'user_cas',
    ),
```


CONFIGURATION
=============

The app is configured by using the administration panel. Please make sure to configure with an admin user, authenticated locally against your ownCloud instance (and not against CAS). Make sure to fill in all the fields provided.


CAS Server
----------

**CAS Server Version**: Default is CAS version 3.0, if you have no special configuration leave it as is.

**CAS Server Hostname**: The host name of the webserver hosting your CAS, lookup /etc/hosts or your DNS configuration or ask your IT-Department.

**CAS Server Port**: The port CAS is listening to. Default for HTTPS should be `443`.

**CAS Server Path**: The directory of your CAS. In common setups this path is `/cas`. Use `/` if your CAS is in your document root.

**Service URL**: Service URL pointing to your plattform used for CAS authentication and redirection. Useful when behind a reverse proxy. This url must end in `/apps/user_cas/login`.

**Certification file path (.crt)**: If you don't want to validate the certificate (i.e. self-signed certificates) then leave this blank. Otherwise enter the absolute path to the certificate (.crt) on your server, beginning at root level.

**Use CAS proxy initialization**: If active, the CAS-Client is initialized as a proxy. Default off. Activate only, if you know what you’re doing.


Basic
-----

**Force user login using CAS?**: If checked, users will immediately be redirected to CAS login page, after visiting the ownCloud URL. If checked, *Disable CAS logout* is automatically disabled. Default: off

**Don’t use force login on these client-IPs**: Comma separated list of client IP addresses (or address ranges), which won’t be forced to login if "Force user login" is enabled (e.g. 192.168.1.1-254,192.168.2.5). Default: empty

**Disable CAS logout**: If checked, you will only be logged out from ownCloud/Nextcloud and not from your CAS instance. Default: off

**Autocreate user after first CAS login?**: If checked, users authenticated against CAS are automatically created. This means, users which did not exist in the database yet who authenticate against CAS will be created and stored in the ownCloud database on their first login. Default: on

**Update user data after each CAS login?**: If checked, the data provided by CAS is used to update ownCloud user attributes each time the user logs in. Default: off

**Disable CAS SingleSignout**: If checked, SingleSignout requests from your CAS server will be ignored. ownCloud/Nextcloud sessions will not be terminated because of SSO. Default: off

**SingleSignout Servers**: Provide a list of servers which can send SingleSignout requests for your CAS ticket (leave empty if you do not have to restrict logout to defined servers).

**Keep CAS-ticket-ids in URL?**: If checked, CAS-ticket-ids are not removed from the URL. Beware: Potential security risk! Only activate, if you know what you are doing. Default off

**Overwrite Login Button Label**: Overwrites the CAS-Login button label (only used in Nextcloud). Default: empty


<a name="mapping"></a>

Mapping
-------

If CAS provides extra attributes, `user_cas` can retrieve the values of them. Since their name differs in various setups it is necessary to map ownCloud-attribute-names to CAS-attribute-names.

**Email**: Name of email attribute in CAS. Default: empty

**Display Name**: Name of display name attribute(s) in CAS (this might be the "real name" of a user or a combination of two fields like: firstnames+surnames). Default: empty

**Group**: Name of group attribute in CAS. Default: empty

**Quota**: Name of quota attribute in CAS. Quota can be a numeric byte value or a human readable string, like 1GB or 512MB. Default: empty


<a name="groups"></a>

Groups
------

**Locked Groups**: Groups that will not be unlinked. These groups are preserved, when updating a user after login and are not unlinked. Please provide a comma separated list without blanks (eg.: group1,group2). Default: empty

**Default group (when autocreating users)**: When auto creating users after authentication, these groups are set as default if the user has no CAS groups. Please provide a comma separated list without blanks (eg.: group1,group2). Default: empty

**Authorized CAS Groups**: Members of these groups are authorized to use the ownCloud instance. This setting is especially helpful, if your CAS instance is not handling authorization itself. Please provide a comma separated list without blanks (eg.: group1,group2). Default: empty

**Group Quotas**: Define quotas for groups of the users authenticated via CAS. Please provide a comma separated list without blanks and with : between group names and quotas (eg.: group1:10GB,group2:500MB). Default: empty

**Group Name Filter**: Define a filter (PHP RegExp syntax!) with only the allowed characters for a group name. Group names are cut after 63 characters per definition by ownCloud/Nextcloud core and appended by an horizontal ellipsis. Default when empty: `a-zA-Z0-9\.\-_ @`

**Group Name Replace Umlauts**: Activate to filter german umlauts out of the group’s name. Only works, if *Group* in "Mapping" is filled. Default: off



ECAS Settings:
--------------

Since Version 1.5 user_cas provides support for using a European Commission ECAS-Server implementation.

**Use ECAS Attribute Parser?**: Activate the ECAS attribute parser to enable the parsing of groups provided by the European Commission ECAS implementation (do **NOT** activate until you know what you are doing).

**Request full user details?**: Activate to request a full user profile in the ECAS callback (do **NOT** activate until you know what you are doing).

**ECAS Strength**: Set the authentication strength used by the ECAS instance when validating a user’s ticket (do **NOT** select until you know what you are doing).

**ECAS AssuranceLevel**: Set the assurance level used by the ECAS instance when validating a user’s ticket (do **NOT** select until you know what you are doing).

**Query ECAS groups**: Define which ECAS groups should be queried when validating a user’s ticket. Please provide a comma separated list without blanks (eg.: GROUP1,GROUP2 or use * for all groups). (Do **NOT** select until you know what you are doing).

**Don’t use Multi-Factor-Authentication on these client-IPs**: Comma separated list of client IP addresses (or address ranges), which won’t be forced to use Multi-Factor-Authentication if "ECAS AssuranceLevel" is at least MEDIUM (e.g. 192.168.1.1-254,192.168.2.5). (Do **NOT** fill until you know what you are doing).


Import CLI:
-----------

Since Version Version 1.7.2 user_cas provides support for importing users from an ActiveDirectory LDAP.

#### ActiveDirectory (LDAP): Provide the necessary information to connect to your AD LDAP Server

**LDAP-Host**: Provide the LDAP-host information. Set the protocol, host and port to use. Default: empty

**LDAP-User and Domain**: Provide the LDAP user and domain to authenticate the LDAP connection Default: empty

**LDAP-User Password**: Set the password for the user (see above). Default: empty

**LDAP Base DN**: Set the LDAP Base Distinguished Name (DN) for the query. Default: empty

**LDAP Sync Filter**: Define the filter to be used when querying the according user’s from LDAP. Default: empty

**LDAP Syn Pagesize (1-1500)**: Define the pagesize of the LDAP query response according to your LDAP server’s settings. Default: 1500

#### CLI Attribute Mapping: Provide the necessary information to map your AD LDAP users to ownCloud

**UID/Username**:  Name of uid/username attribute in your LDAP response. Default: empty

**Display Name**:  Name of display name attribute(s) in your LDAP response (this might be the "real name" of a user or a combination of two fields like: givenname+sn). Default: empty

**Email**: Name of email attribute in your LDAP response. Default: empty

**Group**: Name of group attribute in your LDAP response. Default: empty

**Group Name Field**: Name of the LDAP attribute in your group node to set a group’s name. If no name filed is set or found, the DN of the group will be used as the group’s name. Default: empty

**Quota**: Name of quota attribute in your LDAP response. Quota can be a numeric byte value or a human readable string, like 1GB or 512MB. Default: empty

**Enable**: Name of the enable attribute in your LDAP response. This sets the account to enabled/disabled on import, while enabled = 1 and disabled = 0. Default: empty

**Calculate Enable Attribute Bitwise AND with**: Provide to use a bitwise AND calculation to define the enabled status of the account. Only use if your LDAP’s enabled attribute value is not 0|1. Default: empty

**Merge Accounts**: Activate to enable account merging. Default: off

**Prefer Enabled over Disabled Accounts on Merge**: Activate to prefer enabled second accounts over disabled primary accounts. Only works, if *Merge Accounts* is enabled. Default: off

**Merge two active accounts by**: Name of the attribute in your LDAP response by which you want to merge two activated accounts. Only works, if *Merge Accounts* is enabled. Default: empty

**Merge two active accounts by: Filterstring**: Define a filterstring for the *Merge by* attribute, that defines which activated account should be preferred on merge. Only works, if *Merge by* is set and *Merge Accounts* is enabled. Default: empty



<!-- 
LDAP-Backend:
-------------

**Link to LDAP backend**: Link CAS authentication with LDAP users and groups backend to use the same ownCloud user as if the user was logged in via LDAP. 

-->


PHP-CAS Library
---------------

Setting up the PHP-CAS library options :

**Overwrite phpCAS path (CAS.php file)**: Set a custom path to a CAS.php file of the jasig/phpcas library version you want to use. Beginning at rootlevel of your server. Default: empty, meaning it uses the composer installed dependency in the `user_cas` folder.

**PHP CAS debug file**: Set path to a custom phpcas debug file. Beginning at rootlevel of your server. Default: empty


EXTRA INFO
==========

* If you enable the "Autocreate user after CAS login" option, a user will be created if he does not exist. If this option is disabled and the user does not exist, then the user will be not allowed to log into ownCloud. <!-- You might not want this if you check "Link to LDAP backend" -->

* If you enable the "Update user data" option, the app updates the user's Display Name, Email, Group Membership and Quota on each login.

By default the CAS App will unlink all the groups from a user and will provide the groups defined at the [**Mapping**](#mapping) attributes. If this mapping is not defined, the value of the *Default group* field will be used instead. If both are undefined, then the user will be set with no groups.
If you set the *Locked Groups* field, those groups will not be unlinked from the user.


OCC Commands
============

user_cas has the following OCC commands implemented:

* cas
    * cas:create-user (Adds a user_cas user to the database.)
    * cas:update-user (Updates an existing user and, if not yet a CAS user, converts the record to CAS backend.)
    * cas:import-users-ad (Imports users from an ActiveDirectory LDAP.)


Create a user:
--------------

    cas:create-user [--display-name [DISPLAY-NAME]] [--email [EMAIL]] [-g|--group [GROUP]] [-o|--quota [QUOTA]] [-e|--enabled [ENABLED]] [--] <uid>
    
- Parameters (required):
    - uid: the uid of the user
    
- Options (optional):
    - --display-name: The new display name of the user.
    - --email: The new email of the user.
    - -g | --group: The new group of the user, can be used multiple times (e.g. `-g Family -g Work`) to add multiple groups.
    - -o | --quota: The new quota of the user, either as numerical byte value or human readable value (e.g. 1GB)).
    - -e | --enabled: Enable or disable the user. Setting `-e 1` enables the user, setting `-e 0` disables the user.
    
**Notice: Protected groups will never be unlinked from the user! See also [Groups](#groups).**
    

Update a user:
--------------

    cas:update-user [--display-name [DISPLAY-NAME]] [--email [EMAIL]] [-g|--group [GROUP]] [-o|--quota [QUOTA]] [-e|--enabled [ENABLED]] [-c|--convert-backend [CONVERT-BACKEND]] [--] <uid>

- Parameters (required):
    - uid: the uid of the user
    
- Options (optional):
    - --display-name: The new display name of the user.
    - --email: The new email of the user.
    - -g | --group: The new group of the user, can be used multiple times (e.g. `-g Family -g Work`) to add multiple groups.
    - -o | --quota: The new quota of the user, either as numerical byte value or human readable value (e.g. 1GB)).
    - -e | --enabled: Enable or disable the user. Setting `-e 1` enables the user, setting `-e 0` disables the user.
    - -c | --convert-backend: Set if the user’s backend should be converted to CAS backend. Setting `-c 1` converts to backend to CAS. **WARNING: This is not revocable!**
    
**Notice: Protected groups will never be unlinked from the user! See also [Groups](#groups).**


Import users from ActiveDirectory (LDAP):
-----------------------------------------

    cas:import-users-ad [-d|--delta-update [1]] [-c|--convert-backend [1]]

- Options (optional):
    - -d | --delta-update: Enable or disable delta updates of accounts. Setting `-d 1` enables account updates.
    - -c | --convert-backend: Set if the user’s backend should be converted to CAS backend. Setting `-c 1` converts to backend to CAS. **WARNING: This is not revocable!**


**Additional Info:** If you want to automate the ActiveDirectory import, call this command in a cronjob of your webservers user (e.g. `www-data` on debian based linux systems).

**Additional Info:** If you want additional debug information, use the options `-vv` or `-vvv` in your command, to raise the debug level. To quiet the output, use the option `-q`

**Example for usage as daily cronjob with delta updates and backend conversion (if needed), output will be zeroed**: 

    0 0 * * * /usr/bin/php /path/to/owncloud/occ cas:import-users-ad -d 1 -c 1 -q >/dev/null 2>&1


Bugs and Support
==============

Please contribute bug reports and feedback to [GitHub Issues](https://github.com/felixrupp/user_cas/issues).  

Commercial support and feature implementation is available via [felixrupp.com](http://www.felixrupp.com).


ABOUT
=====

License
-------

AGPL 3.0 or later - http://www.gnu.org/licenses/agpl-3.0.html

Authors
-------

Current Version, since 1.4.0:
* Felix Rupp - [github.com/felixrupp](https://github.com/felixrupp)

Older Versions:
* Sixto Martin Garcia - [github.com/pitbulk](https://github.com/pitbulk)
* David Willinger (Leonis Holding) - [github.com/leoniswebDAVe](https://github.com/leoniswebDAVe)
* Florian Hintermeier (Leonis Holding)  - [github.com/leonisCacheFlo](https://github.com/leonisCacheFlo)
* brenard - [github.com/brenard](https://github.com/brenard)

Links
-------
* ownCloud - [owncloud.org](http://www.owncloud.org)
* ownCloud @ GitHub - [github.com/owncloud](https://github.com/owncloud)