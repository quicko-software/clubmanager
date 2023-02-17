.. include:: /Includes.rst.txt
.. index:: Records; Categories
.. _recordCategory:

==========
Categories
==========

Categories are not mandatory but make it easier to structure member and location
records. The category records themselves are supplied by the TYPO3 Core and might
be shared with other extensions.

Create member and location categories
=====================================

#. Go to the module :guilabel:`Web > List`

#. Go to the "Sys Categories Storage" folder that you created in the first step
   at :ref:`Recommended page structure <quickPageStructure>` or alternatively
   use your "Member Storage".

#. Use the :guilabel:`+` icon in the topbar :guilabel:`Create new record`.

#. Then use the :guilabel:`New record` icon :guilabel:`Content > Category`.

   .. figure:: /Images/QuickStart/be-create-category.png
      :class: with-shadow
      :alt: Create new sys category with web > list module

      Create new sys category with :guilabel:`Web > List` module

#. First of all create two parent categories: `Locations` & `Members` and
   :guilabel:`Save` them.

#. Now you have to remember the ID's of these two categories and enter them in the
   :ref:`Global extension configuration <extensionConfiguration>`! The
   ID's can be found out by hovering the mouse pointer over the icon of the
   the respective category.

   .. figure:: /Images/QuickStart/be-id-of-category.png
      :class: with-shadow
      :alt: Find out id of important member and location sys category

      Find out id of important member and location sys category

#. Now create the required member and location categories and
   in the field :guilabel:`Parent` set the check mark for the parent category.

.. important::

    Don't forget to enter the ID's of the parent categories `Locations` & `Members`
    as described here:
    :ref:`Global extension configuration <extensionConfiguration>`!

.. _propertiesCategory:

Important properties for sys categories
=======================================

.. t3-field-list-table::
 :header-rows: 1

 - :Field: Important Fields
   :Description: Description

 - :Field:
         Title
   :Description:
         Title of the category. This field is required!

 - :Field:
         Parent category
   :Description:
         The parent category is used to build a category tree. Therefore
         select the parent of the current category.

         .. important::

             For all categories that are to be assigned to members,
             the checkbox "Members" must be set here.
             For all categories that should be assigned to locations,
             the checkbox "Locations" must be set here.
