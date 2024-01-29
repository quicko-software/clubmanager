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

Felogin: Include static TypoScript template
===========================================

#. `Install ext:felogin <https://docs.typo3.org/c/typo3/cms-felogin/main/en-us//Index.html>`__

#. Go to module :guilabel:`Web > Template` and chose your root page. It should already contain a TypoScript template record.

#. Switch to view :guilabel:`Info/Modify` and click on :guilabel:`Edit the whole template record`.

#. Switch to tab :guilabel:`Includes` and add the following templates from the list to the right: :guilabel:`Clubmanager Felogin (clubmanager)`.

.. figure:: /Images/Configuration/be-include-felogin-typoscript.png
   :class: with-shadow
   :alt: Include static felogin typoscript

   Include at least :guilabel:`Clubmanager Felogin (clubmanager)` typoscript

.. info::

   Now you're ready to setup the pagetree, if not done yet, the default felogin usergroup and to configure some
   TypoScript!


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

   After you created your page tree and the default frontend user group you
   need to insert some main page uid as well as the uid of the default frontend
   usergroup into the :ref:`Global extension configuration <extensionConfiguration>`!

#. Go to :guilabel:`Admin Tools > Settings > Extension Configuration`

#. Choose :guilabel:`clubmanager` and then Tab :guilabel:`Fe-user-login`.

#. Set the uid where your fe_users should be stored > :guilabel:`feUsersStoragePid`

#. Set the uid of your default fe_users group  > :guilabel:`defaultFeUserGroupUid`

#. Set the uid of your default fe_users login page to generate the correct link send out by e-mail  > :guilabel:`feUsersLoginPid`

#. Set the uid of your default target page after logout > :guilabel:`defaultTargetLogoutPage`

#. In case you want to change it, set also the lifetime of the password recovery
   links send out by e-mail > :guilabel:`passwordRecoveryLifeTime`

#. In tab :guilabel:`Mail` set the number of attempts for a mail delivery.

   .. important::

      For more information see section :ref:`E-Mail Task <recordEmailTask>` and
      for automatisation :ref:`scheduler task <schedulerTasks>`.


.. _configFeloginTyposcript:

Felogin TypoScript
==================

Change the following TypoScript in your own Sitepackage!


.. _configFeloginTyposcriptSetup:

Setup
-----

Have a look at the `ext:clubmanager > felogin` localization TypoScript Setup and
change wording in your own sitepackage to your needs.

.. code-block:: none
      :caption: Path to felogin setup

      EXT:clubmanager/Configuration/TypoScript/Felogin/setup.typoscript

.. important::

   Copy and change only the localization TypoScript. Don't touch the rest if you
   want to use `ext:clubmanager > felogin` configuration out-of-the-box!

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

   You can use the following constants code-block in your sitepackage. Simply
   change the constants to your needs and you are ready to go.

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
