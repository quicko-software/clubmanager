.. include:: /Includes.rst.txt
.. index:: Configuration; Use Routing to rewrite URLs
.. _routing:

===========================
Use Routing to rewrite URLs
===========================

This section will show you how you can rewrite the URLs for clubmanager detail
views using **Routing Enhancers and Aspects**. TYPO3 Explained has an chapter
:ref:`Introduction to routing <t3coreapi:routing-introduction>` that you can read
if you are not familiar with the concept yet. You will no
longer need third party extensions like RealURL or CoolUri to rewrite and
beautify your URLs.

.. _how_to_rewrite_urls:

How to rewrite URLs with clubmanager parameters
-----------------------------------------------

On setting up your page you should already have created a **site configuration**.
You can do this in the backend module :guilabel:`Site Managements > Sites`.

Your site configuration will be stored in
:file:`/config/sites/<your_identifier>/config.yaml`. The following
configurations have to be applied to this file.

Any URL parameters can be rewritten with the Routing Enhancers and Aspects.
These are added manually in the :file:`config.yaml`:

#. Add a section :yaml:`routeEnhancers`, if one does not already exist.
#. Choose an unique identifier for your Routing Enhancer. It doesn't have
   to match any extension key.
#. :yaml:`type`: For clubmanager, the Extbase Plugin Enhancer (:yaml:`Extbase`)
   is used.
#. :yaml:`extension`: the extension key, converted to :code:`UpperCamelCase`.
#. :yaml:`plugin`: the plugin name of clubmanager could be *city*, *member* or
   *location*.
#. After that you will configure individual routes and aspects depending on
   your use case.

.. code-block:: yaml
   :linenos:
   :caption: :file:`/config/sites/<your_identifier>/config.yaml`

   routeEnhancers:
     ClubmanagerCity:
       type: Extbase
       extension: Clubmanager
       plugin: City
       # routes and aspects will follow here
     ClubmanagerMember:
       type: Extbase
       extension: Clubmanager
       plugin: Member
       # routes and aspects will follow here
     ClubmanagerMemberList:
       type: Extbase
       extension: Clubmanager
       plugin: MemberList
       # routes and aspects will follow here
     ClubmanagerLocation:
       type: Extbase
       extension: Clubmanager
       plugin: Location
       # routes and aspects will follow here
     ClubmanagerLocationList:
       type: Extbase
       extension: Clubmanager
       plugin: LocationList
       # routes and aspects will follow here

.. tip::

   If your routing doesn't work as expected, check the **indentation** of your
   configuration blocks.
   Proper indentation is crucial in YAML.

Using limitToPages
~~~~~~~~~~~~~~~~~~

It is recommended to limit :yaml:`routeEnhancers` to the pages where they are needed.
This will speed up performance for building page routes of all other pages.

.. code-block:: yaml
   :caption: :file:`/config/sites/<your_identifier>/config.yaml`
   :linenos:
   :emphasize-lines: 5-7

   routeEnhancers:
     ClubmanagerCity:
       type: Extbase
       extension: Clubmanager
       limitToPages:
         - 20
         - 21
       plugin: City
       # routes and aspects will follow here

About routes and aspects
~~~~~~~~~~~~~~~~~~~~~~~~

In a nutshell:

* :yaml:`routes` will extend an existing route
   (means: your domain and page path) with arguments from GET parameters, like
   the following controller/action pair of the location detail view.
* :yaml:`aspects` can be used to modify these arguments.
   You could for example map the ident of the current member or the slug (or
   better: the optimized slug) of the current location.
   Different types of *Mappers* and *Modifiers* are available, depending on
   the case.

1. URL of location detail page without routing:

.. code-block:: none

   https://www.example.com/location-list/location-detail/?tx_clubmanager_member[action]=detail&tx_clubmanager_member[controller]=Member&tx_clubmanager_member[location]=1&cHash=

2. URL of location detail page with routes:

.. code-block:: none

   https://www.example.com/location-list/location-detail/1/?cHash=

3. URL of location detail page with routes and aspects:

.. code-block:: none

   https://www.example.com/location-list/location-detail/firstname-lastname-company-city/

The following example will only provide routing for the location list and detail
view:

.. code-block:: yaml
   :caption: :file:`/config/sites/<your_identifier>/config.yaml`
   :linenos:

   routeEnhancers:
     ClubmanagerLocation:
       type: Extbase
       extension: Clubmanager
       plugin: Location
       routes:
         - routePath: '/{location}'
           _controller: 'Location::detail'
           _arguments:
             location: location
       aspects:
         location:
           type: PersistedAliasMapper
           tableName: tx_clubmanager_domain_model_location
           routeFieldName: slug

Please note the placeholder :code:`{location}` in :yaml:`routeEnhancer`
:code:`ClubmanagerLocation`:

#. First, you assign the value of the location parameter (:code:`tx_clubmanager[location]`)
   in :yaml:`_arguments`.
#. Next, in :yaml:`routePath` you add it to the existing route.
#. Last, you use :yaml:`aspects` to map the :code:`slug` of the
   given argument.

Both routes and aspects are only available within the current Routing Enhancer.

The names of placeholders are freely selectable.

Common routeEnhancer configurations
-----------------------------------

Basic setup (including Member, MemberList, Location, LocationList, Cities)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Prerequisites:**

The plugins for member :guilabel:`List View`, member :guilabel:`Detail View`,
location :guilabel:`List View`, location :guilabel:`Detail View`,
cities :guilabel:`List View` and members per city :guilabel:`List View`
are on separate pages.

**Result:**

* Member Detail view: ``https://www.example.com/member-list/member-detail/ident/``
* Pagination: ``https://www.example.com/member-list/page/2/``
* Location Detail view: ``https://www.example.com/location-list/location-detail/firstname-lastname-company-city/``
* Pagination: ``https://www.example.com/location-list/page/2/``
* Members per city List view: ``https://www.example.com/cities-list/members-per-city/city/``

.. code-block:: yaml
   :caption: :file:`/config/sites/<your_identifier>/config.yaml`
   :linenos:

   routeEnhancers:
     ClubmanagerCity:
       type: Extbase
       extension: Clubmanager
       limitToPages:
         - 90
         - 91
       plugin: City
       routes:
         -
           routePath: '/{city}'
           _controller: 'Cities::detail'
           _arguments:
             city: city
       aspects:
         city:
           type: SanitizeValue
           tableName: tx_clubmanager_domain_model_location
           columnName: city
     ClubmanagerMember:
       type: Extbase
       extension: Clubmanager
       plugin: Member
       routes:
         -
           routePath: '/{ident}'
           _controller: 'Member::detail'
           _arguments:
             ident: location
       aspects:
         ident:
           type: PersistedAliasMapper
           tableName: tx_clubmanager_domain_model_member
           routeFieldName: ident
     ClubmanagerMemberList:
       type: Extbase
       extension: Clubmanager
       plugin: MemberList
       routes:
         -
           routePath: '/page/{currentPage}'
           _controller: 'Member::list'
           _arguments:
             currentPage: currentPage
       defaultController: 'Member::list'
       defaults:
         currentPage: ''
       aspects:
         currentPage:
           type: StaticRangeMapper
           start: '1'
           end: '100'
     ClubmanagerLocation:
       type: Extbase
       extension: Clubmanager
       plugin: Location
       routes:
         - routePath: '/{location}'
           _controller: 'Location::detail'
           _arguments:
             location: location
       aspects:
         location:
           type: PersistedAliasMapper
           tableName: tx_clubmanager_domain_model_location
           routeFieldName: slug
     ClubmanagerLocationList:
       type: Extbase
       extension: Clubmanager
       plugin: LocationList
       routes:
         -
           routePath: '/page/{currentPage}'
           _controller: 'Location::list'
           _arguments:
             currentPage: currentPage
       defaultController: 'Location::list'
       defaults:
          currentPage: ''
       aspects:
          currentPage:
           type: StaticRangeMapper
           start: '1'
           end: '100'

Localized pagination
~~~~~~~~~~~~~~~~~~~~

Not done yet


