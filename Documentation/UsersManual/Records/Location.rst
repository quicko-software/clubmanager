.. include:: /Includes.rst.txt
.. index:: ! Records; Location
.. _recordLocation:

========
Location
========

Location data records are always linked to a member data record
and can only be created and edited via the associated member record.
Basically, :guilabel:`Main location` and :guilabel:`Other locations`
have the same data structure. While there can be only one main site, there are
many :guilabel:`Other locations` possible.
With the `:ext:clubmanager` there is the possibility to output a list of all locations of
members as well as to offer a categorization of member locations by
the city. See :ref:`plugins <plugins>` for more information.

.. only:: html

   .. contents::
      :local:
      :depth: 1

.. important::

    The locations are used to provide a register of the members in the frontend
    to make the locations accessible and searchable for visitors.
    Additional search and sort functions are available with the add-ons
    :ref:`ext:clubmanager_zip_search <clubmanagerZipSearch>` and
    :ref:`ext:clubmanager_faceted_search <clubmanagerFacetedSearch>`.


.. _recordAddLocation:

Add location record
===================
Location data records are always linked to a member data record
and can only be created and edited via the associated member record.
So first open the member record with the 'List module' and switch
to the tab :guilabel:`Locations`.

.. note::

    A TYPO3 module with extensive search function, improved view and
    extended options, is available with the :ref:`ext:clubmanager_pro
    <clubmanagerPro>`!


.. _recordLocationData:

Location record data
====================
The location record is divided into the tabs:

.. only:: html

   .. contents::
      :local:
      :depth: 2


.. _recordLocationTabGeneral:

Location Tab: General
---------------------
This is where the general data concerning the site is recorded.


.. _recordLocationTabGeneralSlug:

Slug
****
The URL fragment for the member's single view. It is formed by default from the
following data from the location record:
{Firstname}-{Lastname}-{Company}-{City}

.. _recordLocationTabGeneralAddress:

Address
*******
The address of the location is recorded here. From these data :guilabel:`Longitude`
and :guilabel:`Latitude` in the
ref:`Tab Geography <recordGeographyTab>` are determined.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Company
   :Description:
         Company of the site
 - :Field:
         Address suffix
   Description:
         Address suffix of the location
 - Field:
         Street, house number
   :Description:
         Street of the location
 - :Field:
         ZIP CODE
   :Description:
         Postal code of the location
 - :Field:
         City
   :Description:
         City of the location
 - :Field:
         Country
   :Description:
         Country of the location
 - :Field:
         State
   :Description:
         State of the location


.. _recordLocationTabGeneralContact:

Contact person
***************
The contact person for the location is entered here. This can vary from location to
location and may differ from the actual member.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Salutation
   :Description:
         Salutation of contact person
 - :Field:
         Academic Title
   :Description:
         Academic title of contact person
 - :Field:
         First Name
   :Description:
         First name of contact person
 - :Field:
         Middle Name
   Description:
         Middle name of contact person
 - Field:
         Last Name
   :Description:
         Last name of contact person


.. _recordGeographieTab:

Location Tab: Geography
------------------------

From the address of the location here automatically
:guilabel:`longitude` and :guilabel:`latitude`.
is determined. This is done either directly with the :guilabel:`Save` of the
member or manually by clicking :guilabel:`Search GPS coordinates`.

.. important::

    With the module :ref:`ext:clubmanager_zip_search <clubmanagerZipSearch>`
    a radius search can be realized in the frontend for visitors of the website.
    After entering your own zip code you will be shown the locations
    in the immediate vicinity.


.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Longitude
   :Description:
         Longitude to determine the location with a map provider.
 - :Field:
         Latitude
   :Description:
         Latitude to determine the location with a map provider.
 - Field:
         Search GPS coordinates
   :Description:
         Searches for the coordinates to the specified location address


.. _recordLocationTabMeta:

Location Tab: Meta
------------------
Detailed additional information about a location, primarily for display in the
`Detail View` of the member.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Additional Information
   :Description:
         RTE field for detailed descriptions of a location and the ability to format them.
 - :Field:
         Image
   :Description:
         Image of the member to display in `List-View` and/or `Detail-View`.
 - :Field:
         Youtube Video ID
   :Description:
         Youtube Video ID to be displayed in `List-View` and/or `Detail-View`.
 - Field:
         Categories
   :Description:
         Sites can be additionally categorized here, e.g. by sport, subject, etc.

         .. note::

             Only the TYPO3 system categories are displayed here, which are defined via the
             :ref:`Extension Configuration uidCategoryLocation <extensionConfigurationUidParentLocation>`
             are made available. See also
             :ref:`How to create location categories <recordCategory>`!

         .. important::

             With the module
             :ref:`ext:clubmanager_faceted_search <clubmanagerFacetedSearch>`
             locations can be searched very easily by their categories.


.. _recordLocationTabContact:

Location Tab: Contact
---------------------
This tab handles the contact data for a location.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Phone
   :Description:
         Phone number (will be linked automatically in the frontend).
 - :Field:
         Mobile
   :Description:
         Mobile number (will be linked automatically in the frontend).
 - :Field:
         FAX
   :Description:
         Fax number
 - :Field:
         E-mail
   :Description:
         E-mail address (will be linked automatically in the frontend).
 - :Field:
        Url
   :Description:
         Website address (will be linked automatically in the frontend)


.. _recordLocationTabSocialMedia:

Location Tab: Social Media
--------------------------
Each location can have any number of different social media relations.
See :ref:`Relation: Social Media <recordLocationRelationsSocialMedia>`.


.. _recordLocationTabAccess:

Location Tab: Access
--------------------

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Start
   :Description:
         If a location is only to be displayed for a limited time, the start time can be set here.
 - Field:
         Stop
   Description:
         If a location is only to be displayed for a limited time, the stop time can be set here.


.. _recordLocationRelations:

Relations
=========

Location records can have relations to other records. These
are described in more detail here.

.. only:: html

   .. contents::
        :local:
        :depth: 2


.. _recordLocationRelationsSocialMedia:

Social Media
------------
This relation handles the social media data for a location.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Visible
   :Description:
         If contact data is set to invisible with this switch,
         they will not appear in the frontend listing and will not be used for internal purposes
         like for example for internal exports.
 - :Field:
         Type
   :Description:
         Social Media Relation Type:

         * Facebook
         * Instagram
         * Youtube
         * Twitter
         * ...
 - :Field:
         Url
   :Description:
         Url for linking to social media service.
