.. include:: /Includes.rst.txt
.. index:: Configuration; TypoScript reference
.. _typoscript:

===============================
TypoScript reference (fallback)
===============================

.. important::
   TYPO3 Site Sets and Site Settings are the preferred configuration method.
   Use TypoScript mainly as fallback for legacy projects or as targeted
   override in your sitepackage at
   :file:`EXT:mysitepackage/Configuration/TypoScript/setup.typoscript`
   and / or
   :file:`EXT:mysitepackage/Configuration/TypoScript/constants.typoscript`.
   See :ref:`Site sets reference <siteSetsConfiguration>`.

.. only:: html

   .. contents:: Properties
      :depth: 1
      :local:

General setup
=============

Set this in your :file:`EXT:mysitepackage/Configuration/TypoScript/setup.typoscript`

.. _tsPageScss:

Change your theme css
---------------------

.. code-block:: typoscript
   :caption: Set your own CSS-theme by changing page.includeCSS.theme

   page {
	   includeCSS {
		   theme = EXT:clubmanager/Resources/Public/Scss/Theme/Theme.scss
	   }
   }


General constants
=================

Set this in your :file:`EXT:mysitepackage/Configuration/TypoScript/constants.typoscript`

.. _tsPersistanceStoragePid:

Persistence storage folder `storagePid`
---------------------------------------

.. confval:: storagePid

   :type: int
   :Default: 0
   :Path: plugin.tx_clubmanager.persistence
   :Scope: Plugin, TypoScript Setup

   Define the storage folder pid for your member data

   ::

      plugin.tx_clubmanager.persistence.storagePid = 887366

Settings
========

It is possible to overwrite existing
:ref:`Extension Configuration <extensionConfiguration>` with these TypoScript
settings.

.. _tsFeUsersLoginPid:

Frontend users login pid `feUsersLoginPid`
------------------------------------------

.. confval:: feUsersLoginPid

   :type: int
   :Default: 0
   :Path: plugin.tx_clubmanager.settings
   :Scope: Plugin, TypoScript Setup

   Define the default fe_users login page where users are directed to via
   e-mail link. Overwrites :ref:`Extension Configuration feUsersLoginPid
   <extensionConfigurationFeUsersLogin>`!

   ::

      plugin.tx_clubmanager.settings.feUsersLoginPid = 458788

.. _tsPasswordRecoveryLifeTime:
.. _tsPasswortRecoveryLifeTime:

Password recovery link lifetime `passwordRecoveryLifeTime`
----------------------------------------------------------

.. confval:: passwordRecoveryLifeTime

   :type: int
   :Default: 48
   :Path: plugin.tx_clubmanager.settings
   :Scope: Plugin, TypoScript Setup

   Define the lifetime (hours) of password recovery links sent by e-mail.
   Overwrites :ref:`Extension Configuration passwordRecoveryLifeTime
   <extensionConfigurationLifetimePwRecoveryLink>`.

   ::

      plugin.tx_clubmanager.settings.passwordRecoveryLifeTime = 48
