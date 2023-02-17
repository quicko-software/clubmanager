.. include:: /Includes.rst.txt
.. index:: Quick start; Create placeholder data
.. _quickPlaceholderData:

=======================
Create placeholder data
=======================

.. important::

    This data is only intended for testing purposes in a fresh TYPO3 installation.
    **Do not run the upgrade wizard `Create member placeholder data
    for clubmanager extension` with "Yes" in a live installation, because it will
    empty the following tables and refill them with placeholder data**:

    * sys_category
    * fe_users
    * fe_groups
    * tx_clubmanager_domain_model_member
    * tx_clubmanager_domain_model_location

WARNING
=======

.. warning::

    **Run the upgrade wizard `Create member placeholder data
    for clubmanager extension` only in a test environment with "Yes "**.


.. note::

    The :guilabel:`Upgrade Wizard` can only run if in the global
    extension settings the
    ref:`UID of the default storage page for members <extensionConfigurationstoragPid>`
    has been set!

Run upgrade wizard
==================

* You must have admin rights.
* Go to :guilabel:`Admin Tools` > :guilabel:`Upgrade`.
* Click :guilabel:`Run Upgrade Wizard`
* Click on :guilabel:`Execute` in the wizard `Create member placeholder data for clubmanager extension`.
* Click :guilabel:`Yes` to confirm that you understand that the data of some tables will be deleted and rewritten!
* Click on :guilabel:`Perform updates!`

1000 member records with associated location and frontend user record were created.
In addition, system categories were created for sorting of member and site records.

.. note::

    The placeholder categories must still be defined via
    :ref:`Extension Configuration uidCategoryMember <extensionConfigurationUidParentMember>`
    and
    :ref:`Extension Configuration uidCategoryLocation <extensionConfigurationUidParentLocation>`.
