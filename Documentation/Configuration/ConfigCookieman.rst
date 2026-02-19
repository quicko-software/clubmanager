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
   `ext:clubmanager` provides a preconfigured Site Set and TypoScript fallback
   for
   `ext:cookieman <https://docs.typo3.org/p/dmind/cookieman/main/en-us/>`__.

.. only:: html

   .. contents:: Important steps
      :depth: 1
      :local:

.. _configCookiemanSetup:

Cookieman: Enable Site Set
==========================

#. `Install ext:cookieman <https://docs.typo3.org/p/dmind/cookieman/main/en-us/Installation/Index.html>`__

#. Open your site config:
   :file:`config/sites/<your-site>/config.yaml`

#. Add the cookieman set dependency:

   .. code-block:: yaml

      dependencies:
        - quicko/clubmanager
        - quicko/clubmanager-cookieman

.. info::

   Static TypoScript templates are no longer used. Configure values via site
   settings.

.. _configCookiemanSiteSettings:

Cookieman: Site settings
========================

Set cookieman-related values in:
:file:`config/sites/<your-site>/settings.yaml`

.. code-block:: yaml

   clubmanagerCookieman.contentBlockerMode: 'cookieman'
   plugin.tx_cookieman.settings.theme: 'bootstrap5-modal'
   clubmanagerCookieman.links.dataProtectionDeclarationPid: 10
   clubmanagerCookieman.links.imprintPid: 11

See also :ref:`Site sets reference <siteSetsConfiguration>`.


.. _configCookiemanTyposcript:

Cookieman TypoScript (fallback / optional overrides)
====================================================

.. _configCookiemanTyposcriptSetup:

Setup
-----

Have a look at the `ext:clubmanager > cookieman` TypoScript Setup and change wording etc. in your
own sitepackage to your needs.

.. code-block:: none
      :caption: Path to cookieman setup

      EXT:clubmanager/Configuration/TypoScript/Cookieman/setup.typoscript

.. important::

   Keep `contentBlockerMode = cookieman` active, otherwise the content blocker
   integration in :file:`EXT:clubmanager/Resources/Private/Partials/Youtube.html`
   no longer works as intended.

   .. code-block:: typoscript
      :caption: TypoScript setup

      plugin.tx_clubmanager.settings.contentBlockerMode = cookieman

The `plugin.tx_cookieman.settings` are preset for `ext:clubmanager` defaults.
Use site settings first and keep TypoScript changes minimal.

.. _configCookiemanTyposcriptConstants:

Constants
---------

Have a look at the `ext:clubmanager > cookieman` TypoScript constants and change wording etc. in your
own sitepackage to your needs.

.. code-block:: none
      :caption: Path to cookieman constants

      EXT:clubmanager/Configuration/TypoScript/Cookieman/constants.typoscript

.. tip::

   Legacy projects can still use the following constants block. With Site Sets,
   prefer `clubmanagerCookieman.links.dataProtectionDeclarationPid` and
   `clubmanagerCookieman.links.imprintPid` in site settings.

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
