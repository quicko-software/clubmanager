.. include:: /Includes.rst.txt
.. index:: Quick start; Create some initial content
.. _quickContent:

===========================
Create some initial content
===========================

.. note::

    The Quicko Clubmanager can be used for the pure (data) management of
    members, as well as for publicity-boosting listings of members and/or their
    locations.

.. _quickPageStructure:

Recommended page structure
==========================

Create at least the following pages:

*  "Home": Root page of the site, containing the start page content:
   :guilabel:`Normal page`.
*  "Member Storage": A folder to store the members in: :guilabel:`Folder`.
*  "Frontend User": A folder to store the automatic generated `fe_users` in:
   :guilabel:`Folder`.
*  "Sys Categories Storage": A folder to store the TYPO3 sys categories in,
   which are probably needed to categorize, sort and output members or locations
   with different categories: :guilabel:`Folder`.

Depending on your use-case you can also make use of:

*  "Member list": A list page to display all members on: :guilabel:`Normal page`.
*  "Member detail": A single page to display the member detail view on:
   :guilabel:`Normal page, hidden in menu`.
*  "Location list": A list page to display all members locations on:
   :guilabel:`Normal page`.
*  "Location detail": A single page to display the member locations detail view
   on: :guilabel:`Normal page, hidden in menu`.
*  "Cities list": A list page to display all cities with members on:
   :guilabel:`Normal page`.
*  "Members per city": A list page to display the list of all members in one
   city on: :guilabel:`Normal page, hidden in menu`.

.. important::

    After creating the page structure, configure your site settings (Site Sets)
    and then review :ref:`Global extension configuration <extensionConfiguration>`
    only for required global defaults / fallback values.

Your page tree could, for example look like that:

.. code-block:: none

   Home
   ├── Some page
   ├── ...
   ├── Member list
   │  └── Member detail
   ├── Location list
   │  └── Location detail
   ├── Cities list
   │  └── Members per city
   ├── ...
   ├── Login page
   │  └── Restricted content
   ├── Logout target page
   └── Storage
      ├── Member storage
      ├── Frontend User
      ├── Sys Categories Storage
      ├── Other Storage
      └── ...

.. important::

    If you want to make use of the onboard fe_users function for active members
    you have to make use of TYPO3 standard `felogin
    <https://docs.typo3.org/c/typo3/cms-felogin/main/en-us//Index.html>`__.
    Some special settings in this case are important. See
    :ref:`Config felogin <configFeLogin>` for configuration and
    :ref:`Howto setup felogin <setupFeLogin>` for integration.

.. _quickMemberRecords:

Create member records
=====================

Before any member record can be shown in the frontend those need to be
created.

#. Go to the module :guilabel:`Web > List`

#. Go to the "Member Storage" folder that you created in the first step.

#. Use the :guilabel:`+` icon in the topbar :guilabel:`Create new record`.

#. Then use the :guilabel:`New record` icon :guilabel:`Clubmanager > Member`.

#. Fill out all desired fields and click :guilabel:`Save`.

.. figure:: /Images/QuickStart/be-create-member.png
   :class: with-shadow
   :alt: Create new member with web > list module

   Create new member with :guilabel:`Web > List` module

More information about this record can be found here:
:ref:`member record <recordMember>`.

.. note::

    A TYPO3 module with extensive search function, improved view and
    extended options, is available with the
    :ref:`ext:clubmanager_pro <clubmanagerPro>`!

.. _quickMemberCategories:

Create member and location categories
=====================================

Categories are not mandatory but make it easier to structure member and location
records. If you want to make use of special categories for your members and their
locations have a look at the :ref:`category record <recordCategory>`.

.. _quickAddPlugins:

Add plugins: display members in the frontend
=============================================

Plugins are used to render a defined selection of member records in the frontend.

.. important::

    You can set the `Member storage pid` overall with Typoscript. See
    :ref:`TypoScript <typoscript>` for configuration.

Follow these steps to add a plugin respectively for list and detail views to
a page:

.. _quickAddList:

Member/Location list page
--------------------------------

While there are own templates for different listings, creating pages with these
listings is the same for all list views (Member/Location/Cities).

.. figure:: /Images/QuickStart/be-plugins.png
   :class: with-shadow
   :alt: Create new plugin with web > page module

   Create new plugin with :guilabel:`Web > Page` module

#. Go to module :guilabel:`Web > Page` and to the previously created page
   "Member list".

#. Add a new content element and select the entry, depending on your listing:
   :guilabel:`Clubmanager > Member list`.

#. Switch to the tab :guilabel:`Plugin` where you can define the plugins settings.

   #. The selected view is already :guilabel:`Member list`.

   #. The settings here are normally set global with the
      :ref:`Global extension configuration <extensionConfiguration>` and
      :ref:`TypoScript <typoscript>`!
      You can overwrite the global setting
      by selecting another :guilabel:`Record Storage Page` you created in the
      beginning of the tutorial, or use this if you don't use the global setting.

      .. figure:: /Images/QuickStart/be-plugin-options.png
         :class: with-shadow
         :alt: Change global settings with plugin options

         Change global settings with plugin options

   #. Save the plugin.

Member/Location detail page
---------------------------

#. Go to module :guilabel:`Web > Page` and to the previously created page
   "Member detail".

#. Add a new content element and select the entry
   :guilabel:`Plugins > Member single view`.

.. important::

    If you want to use global installation wide settings for your detail pages
    don't forget to set the ID's of your detail pages in :ref:`Global extension
    configuration <extensionConfiguration>`!

Cities list page
----------------

Although the inclusion of :guilabel:`Clubmanager > City output` is the same,
as described in :ref:`Member/Location list page <quickAddList>`, it has
a different function. The plugin shows all cities where members
have locations and thus enables a city filter. As a visitor
you get additionally displayed how many hits there are per city.

.. note::

   Screenshots and more information at :ref:`Plugins <plugins>`.

Member per city page
--------------------

Again, the inclusion of the plugin is identical with
:ref:`Member/Location list page <quickAddList>`. This list is displayed when
a frontend visitor comes over the `Cities list page` and has its own
fluid template.


Read more about the plugin configuration in chapter :ref:`Plugins <plugins>`.

Have a look at the frontend
===========================

Load the "Member list". "Location list" or "Cities list" page in the frontend and
you should now see the member records, location records or cities filter as output.
A click on the name or the detail-button should show the record on the detail page.
You want to change the way the records are displayed? Have a look
at the chapter :ref:`Templating <quickTemplating>`
