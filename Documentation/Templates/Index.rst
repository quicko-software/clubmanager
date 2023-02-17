.. include:: /Includes.rst.txt
.. index:: ! Templates

.. _templates:

=========
Templates
=========

`EXT:clubmanager` is using Fluid as templating engine. If you are used to Fluid
already, you might skip this section. You can get more information in the TYPO3
Documentation :ref:`TYPO3 Explained: Fluid <t3coreapi:fluid>`.


.. _templatesSitepackage:

Use a site package extension
============================

It is recommended to always store overwritten templates in a custom TYPO3
extension. Usually this is done in a special extension called the
**site package**.

If you do not have a site package yet you can create one manually following
this :doc:`Official Tutorial: Site Package <t3sitepackage:Index>`.

There is also a `site package generator <https://sitepackagebuilder.com/>`__
available (Provided by Benjamin Kott).

Create a directory called
:file:`EXT:mysitepackage/Resources/Private/Extensions/Clubmanager` for example
and create 3 directories therein called :file:`Templates`, :file:`Partials` and
:file:`Layouts`. In these directories you can store your version of the Fluid
templates that you need to override.

.. tip::

    You can find the original templates of the
    `EXT:clubmanager` in the directory :file:`EXT:clubmanager/Resources/Private/`.


If you want to change a template, copy the desired files to the
directory in your site package. If the template is in a sub folder you have to
preserve the folder structure.

For example the template:

.. code-block:: none

   EXT:clubmanager/Resources/Private/Templates/Location/Detail.html

would have to be copied to

.. code-block:: none

   EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Templates/Location/Detail.html

Since your site package extends the extension clubmanager you should require clubmanager in
your :file:`composer.json`:

.. code-block:: json
   :caption: :file:`EXT:mysitepackage/composer.json`

   {
      "require": {
         "quicko/clubmanager": "^1.0"
      }
   }

And / or :file:`ext_emconf.php`:

.. code-block:: php
   :caption: :file:`ext_emconf.php`

   $EM_CONF[$_EXTKEY] = [
       // ...
       'constraints' => [
           'depends' => [
               'clubmanager' => '1.0.0-1.99.99',
           ],
           // ...
       ],
   ];

ViewHelpers
-----------

It is common to use the Fluid ViewHelper with the Xml-namespace :html:`<f:`.
The view helpers supplied by TYPO3 are documented in
the :doc:`ViewHelper Reference <t3viewhelper:Index>`.

Any other ViewHelpers from other extensions can be used by using a
namespace declaration like

.. code-block:: html

   <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
        xmlns:cm="http://typo3.org/ns/Quicko/Clubmanager/ViewHelpers"
        xmlns:x="http://typo3.org/ns/Vendor/SomeExtension/ViewHelper"
        data-namespace-typo3-fluid="true">
   ...
   </html>


Then ViewHelpers of EXT:clubmanager can be used with any
Xml-namespace you like to declare but we recommend to use the prefix :html:`cm:`.

.. toctree::
   :maxdepth: 5
   :titlesonly:

   Override
   ViewHelpers
