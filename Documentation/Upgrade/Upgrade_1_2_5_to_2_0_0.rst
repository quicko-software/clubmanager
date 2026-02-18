.. include:: /Includes.rst.txt
.. index:: ! Upgrade; 1.2.5 to 2.0.0
.. _upgrade_1_2_5_to_2_0_0:

=================================
Upgrade from 1.2.5 to 2.0.0
=================================

This upgrade introduces breaking changes to the data model of `ext:clubmanager`. The main architectural change is the introduction of the **Member Journal**, which replaces direct manipulation of membership status and level fields.

.. contents::
   :local:
   :depth: 2


Breaking Changes
================

The following fields are no longer editable directly:

* ``starttime``
* ``endtime``
* ``state``
* ``level``

These values are now exclusively derived from **Member Journal Entries**. The database field ``cancellation_wish`` has been removed.


.. _architectural_change_member_journal:

Architectural Change: Member Journal
=====================================

Version 2.0.0 introduces the **Member Journal** as the single source of truth for all membership status and level changes.

Instead of directly modifying the fields ``state``, ``level``, ``starttime`` and ``endtime`` inside the member record, all changes are now created as journal entries. The system derives the effective membership state from these entries.

This architectural change ensures:

* Complete historical tracking of status changes
* Complete historical tracking of level changes
* Statistically correct member development over time
* Persistence of historical data even if a member record is deleted

The Member Journal forms the foundation for advanced features such as statistics, billing and frontend self-service functionality.


.. _field_behaviour_changes:

Field Behaviour Changes
=======================

The following member fields are no longer editable directly in version 2.0.0. Their values are exclusively derived from Member Journal entries.

.. _field_starttime:

starttime
---------

The field ``starttime`` is set automatically when a member receives the status ``active`` for the first time. Once set, this value is never modified again, even if the member later changes status (e.g. becomes ``resting`` or ``cancelled``).

.. _field_endtime:

endtime
-------

The field ``endtime`` is set automatically when a status change to ``cancelled`` becomes effective. If the effective date lies in the future, the value is stored but the final status transition is processed by the scheduler task.

.. _field_state:

state
-----

The field ``state`` is no longer editable. It always reflects the latest processed status change from the Member Journal.

.. _field_level:

level
-----

The field ``level`` is no longer editable. Level changes must be performed via Member Journal entries of type ``Level Change``.


.. _upgrade_procedure:

Upgrade Procedure
=================

.. warning::

   Do **not** run "Analyze Database" or "Database Compare" before executing the upgrade wizard. The field ``cancellation_wish`` is removed in version 2.0.0 and must be migrated first.


Step 1: Update Extension
------------------------

Update `ext:clubmanager` to version **2.0.0** via Composer or the TYPO3 Extension Repository.


Step 2: Create Missing Tables and Fields
----------------------------------------

Run the TYPO3 upgrade wizard **Create Missing Tables and Fields** to create the new table:

``tx_clubmanager_domain_model_memberjournalentry``

This table stores all future status and level changes independently from the member record itself.


Step 3: Configure Member Journal Storage Page
----------------------------------------------

Before running the migration wizard, you must define where Member Journal
entries will be stored.

#. Go to :guilabel:`Site Management > Settings`

#. Select your site configuration

#. Open section :guilabel:`Clubmanager > Member Journal`

#. Set the field :guilabel:`Member Journal Storage Page` to a dedicated
   sysfolder UID

If this value is set to ``0``, journal entries will be stored in the same
folder as the corresponding member record.

.. important::

   The storage page must be configured **before** executing the upgrade wizard.
   Otherwise initial journal entries may be created in unintended locations.


Step 4: Run Upgrade Wizard
--------------------------

Execute the following upgrade wizard:

   **Clubmanager: Initiale Member-Journal Einträge**

This wizard creates initial journal entries for all existing members based on their current ``state``, ``starttime`` and ``endtime``. Existing cancellation wishes are migrated into journal entries before the old field is removed.

The wizard should only be executed once.


Step 5: Database Compare
------------------------

After the wizard has completed successfully, you may run "Analyze Database" and "Database Compare".


