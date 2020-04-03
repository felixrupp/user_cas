CHANGELOG
=========

Version 1.8.4
-------------

* Adds option to disable SingleSignout requests to the ownCloud/Nextcloud instance in admin panel
* Adds missing translations for options and readme entries


Version 1.8.3
-------------

* Fixes SingleSignOut feature - thanks to [@pingou2712](https://github.com/pingou2712>)
* Fixes wrong link to Nextcloud AppStore in Readme - thanks to [@Emi94](https://github.com/Emi94)


Version 1.8.2
-------------

* Raises compatibility to ownCloud 10.4
* Adds section in README to describe how to disable code integrity checks on dev setups


Version 1.8.1
-------------

* Raises compatibility to Nextcloud 18

* Fixes Nextcloud 18 WebDAV bug - thanks to [@landryb](https://github.com/landryb)
* Fixes internal ip checks for *»Don’t use Multi-Factor-Authentication on these client-IPs«*
* Fixes typo in acceptedStrength \[ECAS\]


Version 1.8.0
-------------

* Drops compatibility for Nextcloud 13
* Raises compatibility to Nextcloud 17

* Fixes a redirect error with enabled enforce-authentication and non-authorized users
* Fixes a security bug, where a user, not authorized by any groups, still had the possibility to gain access to a read only account
* Fixes deprecated quota handling: all quotas should now be handled correctly (even 'none' and 'default')

* Adds option to overwrite the CAS login button’s label
* Adds option to keep CAS-ticket-ids in URL (see [Issue #17](https://github.com/felixrupp/user_cas/issues/17))
* Adds feature to use CAS Single-Logout 

* Bumps phpCAS library to 1.3.8


Version 1.7.4
-------------

* Fixes CAS logout service backlink


Version 1.7.3
-------------

* Raises compatibility to ownCloud 10.3
* Adds support for cas server path "/" - thanks to [@dattn](https://github.com/dattn)


Version 1.7.2
-------------

* Fixes double synchronisation of DisplayName, causing overlays in DisplayName on first login
* Fixes problems with local database users being converted to user_cas backend by accident
* Fixes logout handling to not need a list of Logout Servers anymore

* Adds error handling when CAS basic config is missing or truncated
* Adds the license file for AGPL 3.0 or later
* Adds ActiveDirectory import OCC command, let’s you import specific LDAP users as user_cas users to your ownCloud/Nextcloud instance
* Adds options for group name filter and group umlaut replacement in "Groups" panel


Version 1.7.1
-------------

* Raises compatibility to ownCloud 10.2 and Nextcloud 16
* Fixes Create-/UpdateCommand and synchronisation with the user_cas-Backend
* Fixes user autocreation process and synchronisation with the user_cas-Backend
* Fixes Nextcloud detection algorithm, it does not depend on a user-editable string anymore


Version 1.7.0
-------------

* Adds OCC commands to create and update CAS users directly via command line
* Adds user quota mapping input in Settings Panel - user quota overwrites group quotas
* Adds a filter to disable multi-factor-authentication for internal IP addresses on ECAS instances
* Raises the ECAS client-version to 2.1.3
* Fixes postLogin signals signature


Version 1.6.2
-------------
* Changes default value of the autocreate option to "on"
* Fixes postLogin signals signature


Version 1.6.1
-------------
* Fixes 5min logout bug on Nextcloud
* Unifies log levels in LoggingService
* Moves hook registration to a higher level to ensure working postLoginHook


Version 1.6.0
-------------
* Adds compatibility for ownCloud 10.1 and Nextcloud 15
* Fixes app initialization to minimize incompatibilities with other apps
* Removes hard app stopping when no phpCAS library is found
* Updates jasig/phpcas to 1.3.6


Version 1.5.6
-------------
* Changes most of the INFO log writes to DEBUG


Version 1.5.5
-------------
* Adds Support for ownCloud 10.0.10 and Nextcloud 14.0.0


Version 1.5.4
-------------
* Fixes Nextcloud log flood bug
* Fixes documentation links in info.xml
* Fixes ownCloud/Nextcloud Session Token-Password bug
* Fixes enforce authentication issues with Database Backend
* Fixes enforce authentication issues with public gallery interface


* Adds support for ownCloud until version 10.0.9 and Nextcloud until version 13.0.6
* Adds documentation for basic installation via release archive


* Removes hardcoded PHP version requirements


Version 1.5.3
-------------
* Hotfixes the IP address range separator in the exclude specific IPs field and changes it from "/" to "-"
* Fixes ownCloud 10.0.8 bug and raises compatibility to 10.0.8

Version 1.5.2
-------------
* Add settings field to exclude specific Ips and/or IP-ranges from force login
* Nextcloud: Move settings panel to section "Security" (was in "Additional" before)
* Remove the signature from repo (if you need a signed version, please use one of the release packages or download from ownCloud Market/Nextcloud AppStore)

Version 1.5.1
-------------
* Hotfixes wrong links in 403 error page if enforce authentication was on
* Hotfixes wrong translation in 403 error page for ECAS instances
* Removes return type hints not compatible with PHP 5.6
* Adds functionality to provide more than one mapping field to ownCloud userdata fields (e.g. DisplayName can now be concatenated by a firstname and a lastname CAS-field)

Version 1.5.0
-------------
* Drop ownCloud 9 support
* Major source code optimizations, fix several errors associated with redirection after login
* Add ECAS support
* Add authorization feature via groups
* Add error views for when not authorized or when the CAS-Client throws errors
* Support for ownCloud oauth2 App, it’s now possible to authenticate a desktop or mobile phone client with CAS via oauth2 provider

Version 1.4.9
-------------
* Hotfixes the autocreate bug, mentioned in Issue [#13](https://github.com/felixrupp/user_cas/issues/13).

Version 1.4.8
-------------
* Hotfixes the current 1.4 version to fix a major bug preventing the OCS-Api to work, while the user_cas app is installed and enabled.

Version 1.4.7
-------------
* Hotfixes the min version and **lowers it to 9.1.6**

Version 1.4.6
-------------
* Hotfix for app initialization

Version 1.4.5
-------------
* Fix for autocreate bug
* Re-add phpcas path to use custom phpcas library, if wanted
* Remove GIT submodule for jasig phpcas
* Add composer dependencies instead
* **Raise minimum Owncloud Version to 10.0**

Version 1.4.2, 1.4.3, 1.4.4
---------------------------
* Hotfixes for logging

Version 1.4.1
-------------
* Hotfix for group and protected group handling

Version 1.4.0
-------------
* Completely rewritten in object oriented code, based on Owncloud 9.1 app programming guidelines

Version 0.1.1
-------------
* Added CSRF protection on setting form
* Use openssl_random_pseudo_bytes instead of mt_rand (if available)

Version 0.1
-------------
* Initial plugin
