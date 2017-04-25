INTRODUCTION
============

This App provide CAS authentication support, using the phpCAS library of Jasig.


INSTALLATION
============

DEPENDENCIES
-------------------

This app does not require a standalone version of jasigs phpCAS. The library is shipped within the vendor folder.


STEPS
-----

1. Copy the 'user_cas' folder into the ownCloud's apps folder and make sure to set correct permissions for Apache.
2. Access the ownCloud web interface with an user with admin privileges.
3. Access the applications panel and enable the CAS app.
4. Access the administration panel and configure the CAS app.


CONFIGURATION
=============

The app is configured by using the administration panel. Please make sure to configure with an admin user, authenticated locally against your owncloud instance (and not against CAS). Make sure to fill in all the fields provided.


CAS Server
----------

**CAS Server Version**: Default is version 2.0, if you have no special configuration leave it as is.

**CAS Server Hostname**: The host name of the webserver hosting your CAS, lookup /etc/hosts or your DNS configuration or ask your IT-Department.

**CAS Server Port**: The port CAS is listening to. Default for HTTPS is 443.

**CAS Server Path**: The directory of your CAS. In common setups this path is /cas 

**Service URL**: Service URL used to CAS authentication and redirection. Useful when behind a reverse proxy.

**Certification file**: If you don't want to validate the certificate (i.e. self-signed certificates) then leave this blank. Otherwise enter the path to the certificate on the server, beginning at root level.


Basic
-----

**Force user login using CAS?**: If checked, you will only be logged from your CAS instance.

* **Redirect after logout**: Only if force user login using CAS checked. Redirect to spécific url.

* **Disable CAS logout**: If checked, you will only be logged out from owncloud and not from your CAS instance.

**Autocreate user**: This option enables automatic creation of users authenticated against CAS. This means, users which do not exist in the database yet authenticate against CAS and the app will create and store them in the database on their first login. Default: on.

**Update user**: This option uses the data provided by CAS to update Owncloud user attributes each time they log in.

**Groups that will not be unlinked**: These groups are preserved, when updating a user after login and are not unlinked.

**Default group when autocreating users**: When autocreating users after authentication, these groups are set as default.

<!-- **Link to LDAP backend**: Link CAS authentication with LDAP users and groups backend to use the same owncloud user as if the user was logged in via LDAP. -->


Mapping
-------

If CAS provides extra attributes, user_cas can retrieve the values of them. Since their name differs in various setups it is necessary to map owncloud-attribute-names to CAS-attribute-names.

**Email**: Name of email attribute in CAS.

**Display Name**: Name of display name attribute in CAS (this might be the "real name" of a user).

**Group**: Name of group attribute in CAS.


PHP-CAS Library
---------------

Setting up the PHP-CAS library options :

<!-- **PHP CAS path (CAS.php file)**: Set path to CAS.php file of the library to use. Usually the path will be /usr/share/php/CAS.php -->

**PHP CAS debug file**: Set path to the debug file.

EXTRA INFO
==========

* If you enable the "Autocreate user after CAS login" option, a user will be created if he does not exist. If this option is disabled and the user does not exist, then the user will be not allowed to log in ownCloud. <!-- You might not want this if you check "Link to LDAP backend" -->

* If you enable the "Update user data" option, the app updates the user's email and group membership.

By default the CAS App will unlink all the groups from a user and will provide the group defined at the groupMapping attribute. If the groupMapping is not defined, the value of the defaultGroup field will be used instead. If both are undefined, then the user will be set with no groups.
If you set the "protected groups" field, those groups will not be unlinked from the user.

Bugs and Support
==============

Please contribute bug reports and feedback to https://github.com/felixrupp/user_cas/issues 
If you are observing undesired behaviour, think it is a bug and want to tell us about, please include following parts:
* What led up to the situation?
* What exactly did you do (or not do) that was effective (or ineffective)?
* What was the outcome of this action?
* What outcome did you expect instead?

Also please provide basic information of your Owncloud instance:
* Owncloud Version
* PHP Version
* CAS Version

ABOUT
=====

License
-------

AGPL - http://www.gnu.org/licenses/agpl-3.0.html

Authors
-------

Current Version, since 1.4.0:
* Felix Rupp - https://github.com/felixrupp

Older Versions:
* Sixto Martin Garcia - https://github.com/pitbulk
* David Willinger (Leonis Holding)  - https://github.com/leoniswebDAVe
* Florian Hintermeier (Leonis Holding)  - https://github.com/leonisCacheFlo
* brenard - https://github.com/brenard

Links
-------
* ownCloud - http://owncloud.org
* ownCloud @ GitHub - https://github.com/owncloud