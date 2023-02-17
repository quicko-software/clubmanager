.. include:: /Includes.rst.txt
.. index:: ! Plugins
.. _plugins:

=======
Plugins
=======

The clubmanager plugin is used to output a defined list of member or, more
useful, location records.

It can be created by adding a content element from tab
:guilabel:`Clubmanager` and by selecting the desired plugin type.

.. figure:: /Images/UsersManual/insert-ce-clubmanager-plugin.png
  :class: with-shadow
  :alt: Adding a content element for clubmanager listings

  Adding a content element for clubmanager listings


.. only:: html

   The available actions are:

   .. contents::
        :local:
        :depth: 2


.. _plugin-memberList:

List of members
===============
Displays an unfiltered list of all member records.


.. _plugin-memberSingleView:

Members single view
------------------------
This is the detail view to the :ref:`member-list <plugin-memberList>`.

.. note::

    The global configuration:
    :ref:`Extension Configuration: Default member detail page <extensionConfigurationMemberDefault>`
    can be overridden in the :ref:`Member list plugin <plugin-memberList>`.


.. _plugin-locationList:

Location list
=============
Displays an unfiltered list of all location records.


.. _plugin-locationSingleView:

Location single output
---------------------------
This is the detail view to the :ref:`Location list <plugin-locationList>`.

.. note::

    The global configuration:
    :ref:`Extension Configuration: Default location detail page <extensionConfigurationLocationDefault>`
    can be overridden in the :ref:`location list plugin <plugin-locationList>`.


.. _plugin-cityList:

City overview
=============
Displays a list of all cities with location records.


.. _plugin-locationsPerCity:

Locations per city
-------------------
This is the list view to the :ref:`City overview <plugin-cityList>`.

.. note::

    The global configuration:
    :ref:`Extension Configuration: Default city detail page <extensionConfigurationCityDefault>`
    can be overridden in the :ref:`city output plugin <plugin-cityList>`.
