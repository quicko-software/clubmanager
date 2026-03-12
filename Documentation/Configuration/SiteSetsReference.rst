.. include:: /Includes.rst.txt
.. index:: Configuration; Site sets
.. _siteSetsConfiguration:

===================
Site sets reference
===================

`ext:clubmanager` is configured via TYPO3 Site Sets and Site Settings.
This is the recommended and default approach.

See the TYPO3 main documentation for Site Sets:
`Site sets <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/SiteHandling/SiteSets.html>`__.

.. only:: html

   .. contents:: Properties
      :depth: 1
      :local:

Enable Site Sets
================

#. Open your site config:
   :file:`config/sites/<your-site>/config.yaml`

#. Add the needed dependencies:

   .. code-block:: yaml

      dependencies:
        - quicko/clubmanager
        # Optional:
        # - quicko/clubmanager-login
        # - quicko/clubmanager-cookieman

Configure Site Settings
=======================

Add your project-specific values in:
:file:`config/sites/<your-site>/settings.yaml`

Base set `quicko/clubmanager`
=============================

.. confval:: clubmanager.memberJournalStoragePid

   :type: int
   :Default: 0
   :Path: settings.yaml

   Page/folder where new member journal entries are stored.
   If set to `0`, entries are stored in the same folder as the member record.

.. confval:: page.theme.cookieconsent.enable

   :type: bool
   :Default: false
   :Path: settings.yaml

   The Clubmanager base set disables the Bootstrap Package cookie consent
   feature by default.
   This avoids conflicts when `quicko/clubmanager-cookieman` or `ext:cookieman`
   is used for consent handling.
   Existing project site settings can still override this value explicitly.

Login set `quicko/clubmanager-login`
====================================

Use this set if you use frontend login features.

.. code-block:: yaml

   clubmanagerLogin.storagePid: 123
   clubmanagerLogin.loginFormPid: 45
   clubmanagerLogin.memberProfilePage: 67
   clubmanagerLogin.emailFrom: 'demo@clubmanager.software'
   clubmanagerLogin.emailFromName: 'Clubmanager Demo'
   clubmanagerLogin.replyToEmail: 'post@quicko.software'
   clubmanagerLogin.redirectPageLogout: 10
   clubmanagerLogin.label.login: 'Login'
   clubmanagerLogin.label.profile: 'Profil'
   clubmanagerLogin.label.logout: 'Logout'

Cookieman set `quicko/clubmanager-cookieman`
============================================

Use this set if you integrate `ext:cookieman`.

.. code-block:: yaml

   clubmanagerCookieman.contentBlockerMode: 'cookieman'
   plugin.tx_cookieman.settings.theme: 'bootstrap5-modal'
   clubmanagerCookieman.links.dataProtectionDeclarationPid: 10
   clubmanagerCookieman.links.imprintPid: 11

The base set already keeps `page.theme.cookieconsent.enable = false` by
default, so Bootstrap Package cookie consent stays disabled unless a project
explicitly enables it again.
The legacy TypoScript constants imported by the Cookieman set also enforce
`page.theme.cookieconsent.enable = 0` for the same reason.

Fallback behavior (legacy)
==========================

Site Settings are preferred.
For backward compatibility, existing TypoScript and extension configuration
values are still used as fallback in these cases:

* `clubmanagerLogin.storagePid = 0` falls back to
  `plugin.tx_clubmanager.settings.feUsersStoragePid`
* `clubmanagerLogin.loginFormPid = 0` falls back to
  `plugin.tx_clubmanager.settings.feUsersLoginPid`
* `clubmanagerCookieman.links.* = 0` keeps existing values from your legacy
  cookieman TypoScript/constants configuration
