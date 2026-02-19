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

.. note::

    This TypoScript-based approach is available for legacy/fallback use-cases.
    Site Sets and Site Settings are the preferred configuration method.

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

    For use with `ext:bootstrap_package ^16.0 <https://www.bootstrap-package.com/>`__
    use Site Set settings instead of the old bootstrap constants file. See:

    .. code-block:: none

       EXT:clubmanager/Configuration/Sets/Clubmanager/settings.yaml
