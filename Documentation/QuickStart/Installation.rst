.. include:: /Includes.rst.txt
.. index:: Quick start; Quick installation
.. _quickInstallation:

==================
Quick installation
==================

.. important::

    EXT:clubmanager supports TYPO3 v13.4 (LTS).

In a :ref:`composer-based TYPO3 installation <t3start:install>` you can install
the extension EXT:clubmanager via composer:

.. code-block:: bash

    composer require quicko/clubmanager

In TYPO3 v13.4 composer-based installations the extension is installed
automatically. You do not have to activate it manually.

If you have a legacy installation without composer, you can download and
install it via the "Extensionmanager"

Update the database scheme
--------------------------

Open your TYPO3 backend with system maintainer permissions.

In the module menu to the left navigate to :guilabel:`Admin Tools > Maintenance`,
then click on :guilabel:`Analyze database` and create all.

Clear all caches
----------------

In the same module :guilabel:`Admin Tools > Maintenance` you can also
conveniently clear all caches by clicking the button :guilabel:`Flush cache`.
