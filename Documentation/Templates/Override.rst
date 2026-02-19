.. include:: /Includes.rst.txt
.. index:: Templates; Override
.. _templates-override:

Overriding templates
====================

In the most common case, where you want to override the standard clubmanager
template with your own templates you can use the TypoScript **constants** to set the
paths:

.. note::

   This TypoScript-based template override is still the supported approach for
   Fluid template paths. Site Sets are used for extension configuration, while
   template root path overrides stay in your sitepackage TypoScript.

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

If needed, multiple fallbacks can be defined with TypoScript setup:

.. code-block:: typoscript
   :caption: TypoScript setup

   plugin.tx_clubmanager {
      view {
         templateRootPaths {
            99 = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Templates/
         }
         partialRootPaths {
            99 = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Partials/
         }
         layoutRootPaths {
            99 = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Layouts/
         }
      }
   }
