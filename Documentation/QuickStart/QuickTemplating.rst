.. include:: /Includes.rst.txt
.. index:: Quick start; Quick templating in Fluid
.. _quickTemplating:

=========================
Quick templating in Fluid
=========================

EXT:clubmanager is using Fluid as templating engine. If you are not experienced
with Fluid yet you can read more about it in the chapter
:ref:`Templates <templates>`.

Copy the Fluid templates that you want to adjust to your
:ref:`sitepackage extension <templatesSitepackage>`.

You find the original templates in :file:`EXT:clubmanager/Resources/Private/Templates/`
and the partials in :file:`EXT:clubmanager/Resources/Private/Partials/`.

.. important::

    Never change these templates directly!

To override the standard clubmanager templates
with your own you can use the TypoScript **constants** to set the
paths:

.. code-block:: typoscript
   :caption: TypoScript constants

   plugin.tx_clubmanager {
      view {
         templateRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Templates/
         partialRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Partials/
         layoutRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Layouts/
      }
      mailView {
         templateRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Templates/Email
         partialRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Partials/Email
         layoutRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Layouts/Email
      }
   }

Add these lines to the file
:file:`EXT:mysitepackage/Configuration/TypoScript/constants.typoscript` in your
sitepackage.

Of course all :doc:`ViewHelpers provided by TYPO3 <t3viewhelper:Index>` can
be used.

.. tip::

    For use with the `ext:bootstrap_package v12.0.7 <https://www.bootstrap-package.com/>`__
    here you can find the most important constants to customize in your sitepackage:

    .. code-block:: none

       EXT:clubmanager/Configuration/TypoScript/Bootstrap_package/constants.typoscript
