.. include:: /Includes.rst.txt
.. index:: Configuration; Config Cookieman
.. _configCookieman:

================
Config cookieman
================

Out-of-the-box `ext:clubmanager` has integrated a content blocker for Youtube
in the Fluid-template for the detail view of the locations and thus offers a
2-click solution for the display of Youtube movies within the
Clubmanager list and detail views.

.. code-block:: none

   EXT:clubmanager/Resources/Private/Partials/Youtube.html

.. important::

   If you want to make use of functionality for data protection of
   youtube and/or google maps integration with a cookie consent manager
   `ext:clubmanager` has preconfigured Typoscript for
   `ext:cookieman <https://docs.typo3.org/p/dmind/cookieman/main/en-us/>`__.

.. only:: html

   .. contents:: Important steps
      :depth: 1
      :local:

.. _configCookiemanSetup:

Cookieman: Include static TypoScript template
=============================================

#. `Install ext:cookieman <https://docs.typo3.org/p/dmind/cookieman/main/en-us/Installation/Index.html>`__

#. Go to module :guilabel:`Web > Template` and chose your root page. It should already contain a TypoScript template record.

#. Switch to view :guilabel:`Info/Modify` and click on :guilabel:`Edit the whole template record`.

#. Switch to tab :guilabel:`Includes` and add the following templates from the list to the right: :guilabel:`Clubmanager Cookieman (clubmanager)`.

.. figure:: /Images/Configuration/be-include-typoscript.png
   :class: with-shadow
   :alt: Include static cookieman typoscript

   Include at least :guilabel:`Clubmanager Cookieman (clubmanager)` typoscript

.. info::

   You do not need to integrate any cookieman static typoscript templates.
   All of this is done with :guilabel:`Clubmanager Cookieman (clubmanager)` Typoscript.


.. _configCookiemanTyposcript:

Cookieman TypoScript
====================

.. _configCookiemanTyposcriptSetup:

Setup
-----

Have a look at the `ext:clubmanager > cookieman` TypoScript Setup and change wording etc. in your
own sitepackage to your needs.

.. code-block:: none
      :caption: Path to cookieman setup

      EXT:clubmanager/Configuration/TypoScript/Cookieman/setup.typoscript

.. important::

   Do not change, the following TypoScript on your own, because it activates the
   Content-Blocker-Mode in :file:`EXT:clubmanager/Resources/Private/Partials/Youtube.html`

   .. code-block:: typoscript
      :caption: TypoScript setup

      plugin.tx_clubmanager.settings.contentBlockerMode = cookieman

The `plugin.tx_cookieman.settings` are set for all `EXT:clubmanager` requirements.
Better you do not change this but add the settings according to your specific needs
in your own Sitepackage.

.. _configCookiemanTyposcriptConstants:

Constants
---------

Have a look at the `ext:clubmanager > cookieman` TypoScript constants and change wording etc. in your
own sitepackage to your needs.

.. code-block:: none
      :caption: Path to cookieman constants

      EXT:clubmanager/Configuration/TypoScript/Cookieman/constants.typoscript

.. tip::

   You can use the following constants code-block in your sitepackage. Simply
   change the page pid's for your privacy policy and your legal disclosure and
   you are ready to go.

   .. code-block:: typoscript
      :caption: TypoScript constants

      plugin.tx_cookieman {
         settings {
            links {
               dataProtectionDeclarationPid = ###YOUR-DATA-PROTECTION-PID###
               imprintPid = ###YOUR-IMPRINT-PID###
            }
         }
      }
