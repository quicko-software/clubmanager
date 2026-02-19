.. include:: /Includes.rst.txt
.. index:: Configuration; Config Frontend Login
.. _configFeLogin:

==============
Config felogin
==============

.. important::

   If you want to make use of the onboard fe_users function for active members
   you have to install `felogin
   <https://docs.typo3.org/c/typo3/cms-felogin/main/en-us//Index.html>`__ first.
   See `ext:felogin` Documentation (Link above).

.. only:: html

   .. contents:: Important steps
      :depth: 1
      :local:

.. _configFeloginSetup:

Felogin: Enable Site Set
========================

#. `Install ext:felogin <https://docs.typo3.org/c/typo3/cms-felogin/main/en-us//Index.html>`__

#. Open your site config:
   :file:`config/sites/<your-site>/config.yaml`

#. Add the login set dependency:

   .. code-block:: yaml

      dependencies:
        - quicko/clubmanager
        - quicko/clubmanager-login

.. info::

   Static TypoScript templates are no longer used. Configure values via site
   settings.

.. _configFeloginSiteSettings:

Felogin: Site settings
======================

Set login-related values in your site settings file:
:file:`config/sites/<your-site>/settings.yaml`

.. code-block:: yaml

   clubmanagerLogin.storagePid: 123
   clubmanagerLogin.loginFormPid: 45
   clubmanagerLogin.memberProfilePage: 67
   clubmanagerLogin.label.login: 'Login'
   clubmanagerLogin.label.profile: 'Profil'
   clubmanagerLogin.label.logout: 'Logout'

See also :ref:`Site sets reference <siteSetsConfiguration>`.


.. _configFeLoginPagetree:

Felogin pagetree
================

Your page tree should look like that:

.. code-block:: none

   Home
   ├── ...
   ├── Login page
   │  └── Restricted content
   ├── Logout target page
   ├── ...
   └── Storage
      ├── ...
      ├── Frontend User
      └── ...


.. _configFeLoginCreateDefaultFeuserGroup:

Create default frontend user group
==================================

#. Go to the module :guilabel:`Web > List`

#. Move to :guilabel:`Storage` > :guilabel:`Frontend User` in your pagetree.

#. Click on :guilabel:`+ Create new record` on top of the page.

#. Click on :guilabel:`Frontend Access` > :guilabel:`Website Usergroup`.

#. Fill in the :guilabel:`Group Title` and :guilabel:`Save`.


.. _configFeLoginGlobal:

Important global configuration
==============================

.. note::

   After creating your page tree and the default frontend user group, configure
   login-related page UIDs in site settings (preferred) and keep extension
   configuration in sync for legacy fallback where needed.

#. Configure these values in
   :file:`config/sites/<your-site>/settings.yaml`:

   .. code-block:: yaml

      clubmanagerLogin.storagePid: 123
      clubmanagerLogin.loginFormPid: 45
      clubmanagerLogin.memberProfilePage: 67

#. Go to :guilabel:`Admin Tools > Settings > Extension Configuration`.

#. Choose :guilabel:`clubmanager` and then Tab :guilabel:`Fe-user-login`.

#. Set the uid of your default fe_users group
   :guilabel:`defaultFeUserGroupUid` (this is still required for member to
   fe_user assignment).

#. Optional fallback values (used if matching site settings are `0`):
   :guilabel:`feUsersStoragePid`, :guilabel:`feUsersLoginPid`,
   :guilabel:`defaultTargetLogoutPage`.

#. In case you want to change it, set the lifetime of password recovery links
   in hours: :guilabel:`passwordRecoveryLifeTime`.

#. In tab :guilabel:`Mail` set the number of attempts for a mail delivery.

   .. important::

      For more information see section :ref:`E-Mail Task <recordEmailTask>` and
      for automatisation :ref:`scheduler task <schedulerTasks>`.


.. _configFeloginTyposcript:

Felogin TypoScript
==================

TypoScript is optional and mainly relevant as legacy fallback / targeted
override in your own sitepackage.


.. _configFeloginTyposcriptSetup:

Setup
-----

Have a look at the `ext:clubmanager > felogin` localization TypoScript Setup and
change wording in your own sitepackage to your needs.

.. code-block:: none
      :caption: Path to felogin setup

      EXT:clubmanager/Configuration/TypoScript/Felogin/setup.typoscript

.. important::

   Copy and change only the localization TypoScript if needed. Keep functional
   values in site settings where possible.

   .. code-block:: typoscript
      :caption: TypoScript setup

         plugin.tx_felogin_login {
            _LOCAL_LANG.de  {
               //Change the existing localization text as you want them
         }


.. _configFeloginTyposcriptConstants:

Constants
---------

Have a look at the `ext:clubmanager > felogin` TypoScript constants and change
them in your own sitepackage to your needs.

.. code-block:: none
      :caption: Path to felogin constants

      EXT:clubmanager/Configuration/TypoScript/Felogin/constants.typoscript

.. important::

   You can still use the following constants in legacy projects. Prefer site
   settings for the documented login page and storage page UIDs.

   .. code-block:: typoscript
      :caption: TypoScript constants

         styles.content.loginform {
            emailFrom = your-email@your-site.tld
            emailFromName = Your Name
            replyToEmail = no-reply@your-site.tld

            redirectPageLogout = ###UID-OF-YOUR-LOGOUT-TARGET-PAGE###

            //Login|Logout button label on every page. Change the labels if you want to.
            label {
               login = Login
               logout = Logout
            }
         }


.. _configFeLoginMailTemplate:

Change default core mail templates
==================================

.. note::

   If you want to change the standard TYPO3 email templates you have to make changes
   in the :guilabel:`LocalConfiguration.php`.
   Therefor open :guilabel:`Admin Tools` > :guilabel:`Settings` > :guilabel:`Configure Installation-Wide Options`
   > :guilabel:`Mail` and adjust the following settings to your needs:

#. [MAIL] layoutRootPaths:
   EXT:core/Resources/Private/Layouts/,EXT:backend/Resources/Private/Layouts/,EXT:mysitepackage/Resources/Private/Extensions/Sysmail/Layouts/

#. [MAIL] partialRootPaths:
   EXT:core/Resources/Private/Partials/,EXT:backend/Resources/Private/Partials/,EXT:mysitepackage/Resources/Private/Extensions/Sysmail/Partials/

#. [MAIL] templateRootPaths:
   EXT:core/Resources/Private/Templates/Email/,EXT:backend/Resources/Private/Templates/Email/,EXT:mysitepackage/Resources/Private/Extensions/Sysmail/Templates/

Afterwards you can overwrite the core templates in your sitepackage and fit them
to your needs!

.. note::

   How to setup `ext:clubmanager` to work with `ext:felogin` as expected, see
   :ref:`How to setup felogin <setupFeLogin>`!
