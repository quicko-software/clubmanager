.. include:: /Includes.rst.txt
.. index:: Configuration; TypoScript reference
.. _typoscript:

====================
TypoScript reference
====================

.. important::
   Every setting can also be defined by TypoScript setup and constants. Normally
   you should change this at
   :file:`EXT:mysitepackage/Configuration/TypoScript/setup.typoscript`
   and / or
   :file:`EXT:mysitepackage/Configuration/TypoScript/constants.typoscript`
   in your sitepackage.

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
:ref:`Extension Configuration <extensionConfiguration>` with this typoscript
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

