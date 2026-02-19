.. include:: /Includes.rst.txt
.. index:: Configuration; Extension Configuration
.. _extensionConfiguration:

=======================
Extension Configuration
=======================

Some general settings can be configured in the Extension Configuration.
For TYPO3 v13+ projects using Site Sets, prefer :ref:`Site settings
<siteSetsConfiguration>` where available and use extension configuration mainly
as global defaults / fallback.

#. Go to :guilabel:`Admin Tools > Settings > Extension Configuration`
#. Choose :guilabel:`clubmanager`

The settings are divided into several tabs and described here in detail:

.. only:: html

   .. contents:: Properties
        :local:
        :depth: 2

Basic
=====

.. _extensionConfigurationstoragPid:

UID of the default storage page for members `storagePid`
--------------------------------------------------------

.. confval:: storagePid

   :type: int
   :Default: 0

   Define the parent folder for member records. Important if you want to generate
   placeholder data with :guilabel:`Upgrade wizard`. See
   :ref:`Create placeholder data <quickPlaceholderData>` for details.

.. _extensionConfigurationUidParentMember:

UID of category parent for member `uidCategoryMember`
-----------------------------------------------------

.. confval:: uidCategoryMember

   :type: int
   :Default: 0

   Define the parent category for member categories

.. _extensionConfigurationUidParentLocation:

UID of category parent for location `uidCategoryLocation`
---------------------------------------------------------

.. confval:: uidCategoryLocation

   :type: int
   :Default: 0

   Define the parent category for location categories

.. _extensionConfigurationCityDefault:

UID of the default city detail page `defaultDetailCityPage`
-----------------------------------------------------

.. confval:: defaultDetailCityPage

   :type: int
   :Default: 0

   Define the default city detail page

.. _extensionConfigurationMemberDefault:

UID of the default member detail page `defaultDetailMemberPage`
-----------------------------------------------------

.. confval:: defaultDetailMemberPage

   :type: int
   :Default: 0

   Define the default member detail page

.. _extensionConfigurationLocationDefault:

UID of the default location detail page `defaultDetailLocationPage`
-----------------------------------------------------

.. confval:: defaultDetailLocationPage

   :type: int
   :Default: 0

   Define the default location detail page

Fe-user-login
==============

.. _extensionConfigurationFeUsersStorage:

UID of the Default fe_users storage page `feUsersStoragePid`
-----------------------------------------------------

.. confval:: feUsersStoragePid

   :type: int
   :Default: 0

   Define the storage folder where fe_users are automatically stored.
   Used as fallback when `clubmanagerLogin.storagePid` is `0`.

.. _extensionConfigurationDefaultFeUserGroupUid:

UID of the default UserGroup for FE Users  `defaultFeUserGroupUid`
---------------------------------------------------------

.. confval:: defaultFeUserGroupUid

   :type: int
   :Default: 0

   Define the default UserGroup for FE Users in Member records

.. _extensionConfigurationFeUsersLogin:

UID of the Default fe_users login page `feUsersLoginPid`
-----------------------------------------------------

.. confval:: feUsersLoginPid

   :type: int
   :Default: 0

   Define the default fe_users login page where users are directed to via
   e-mail link. Used as fallback when `clubmanagerLogin.loginFormPid` is `0`.
   Can also be overwritten by :ref:`TypoScript feUsersLoginPid
   <tsFeUsersLoginPid>`.

.. _extensionConfigurationLogoutDefault:

UID of the default target page after logout `defaultTargetLogoutPage`
-----------------------------------------------------

.. confval:: defaultTargetLogoutPage

   :type: int
   :Default: 0

   Define the default target page after logout from frontend login.
   Used as fallback when `styles.content.loginform.redirectPageLogout` is `0`.

.. _extensionConfigurationLifetimePwRecoveryLink:

Lifetime of the passwort recovery link `passwordRecoveryLifeTime`
-----------------------------------------------------

.. confval:: passwordRecoveryLifeTime

   :type: int
   :Default: 48

   Define the lifetime of a password recovery link send out by e-mail in hours.
   Can be overwritten by :ref:`TypoScript passwordRecoveryLifeTime
   <tsPasswordRecoveryLifeTime>`.

Mail
====

.. _extensionConfigurationMailTries:

Number of attempts for a mail delivery  `mailTries`
-----------------------------------------------------

.. confval:: mailTries

   :type: int
   :Default: 6

   Define how often a mail delivery via :guilabel:`System > Scheduler`-Task is
   tried, when it failed before. See section :ref:`Mail service task
   <schedulerMailServiceTask>` for more information.
