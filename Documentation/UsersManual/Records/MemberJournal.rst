.. include:: /Includes.rst.txt
.. index:: ! Records; Member Journal
.. _member_journal:

==============
Member Journal
==============

The Member Journal manages all membership status and level changes in
`ext:clubmanager` since version 2.0.0.

.. contents::
   :local:
   :depth: 2


.. _member_journal_architecture:

Architecture Principle
======================

Since version 2.0.0 the Member Journal is the single source of truth
for membership status and level management.

The member record itself only reflects the current effective state.
All changes are stored as journal entries and processed according
to defined lifecycle rules.

Core principles:

* The member record shows only the current effective state.
* The journal contains the complete historical record.
* Sorting of entries is based on ``entry_date``.
* Effectiveness is determined by ``effective_date``.
* Processing occurs either immediately or via scheduler.

Processing modes:

* Immediate processing if the effective date is today or in the past.
* Scheduled processing via the command
  ``clubmanager:journal:process`` if the effective date lies in the future.


.. _member_journal_member_fields:

Member Fields (Derived Behaviour)
=================================

The following fields of the member record are derived from
processed journal entries and are not edited directly.

.. t3-field-list-table::
   :header-rows: 1

   - :Field:
         Field
     :Description:
         Behaviour

   - :Field:
         ``starttime``
     :Description:
         Set automatically on first activation. Once set, it is never modified again.

   - :Field:
         ``endtime``
     :Description:
         Set when a cancellation becomes effective. Cleared on reactivation.

   - :Field:
         ``state``
     :Description:
         Always reflects the latest processed status change.

   - :Field:
         ``level``
     :Description:
         Always reflects the latest processed level change.

   - :Field:
         :guilabel:`Reduced membership`
     :Description:
         Purely descriptive flag without automatic logic in Base.

   - :Field:
         :guilabel:`Membership number (ident)`
     :Description:
         Functional primary identifier. Must be set before first activation.

The membership number (:guilabel:`Membership number (ident)`) is editable
until the first activation. After activation it becomes read-only for editors.
Administrators may modify it if required.

The frontend username is derived from the membership number and
is read-only for editors.


.. _member_journal_entry_types:

Journal Entry Types
===================

Two entry types are available in the Member Journal:

* Status Change
* Level Change


.. _member_journal_entry_type_status_change:

Status Change
=============

A status change updates the effective membership status.

Allowed statuses in Base:

* ``active``
* ``resting``
* ``cancelled``

.. important::

   The status ``requested`` is not part of Clubmanager Base. It is created
   automatically by Clubmanager Pro during frontend registration and must not
   be set manually in Base.


.. _member_journal_entry_type_level_change:

Level Change
============

A level change updates the effective membership level.

Fields:

.. t3-field-list-table::
   :header-rows: 1

   - :Field:
         Field
     :Description:
         Description

   - :Field:
         ``old_level``
     :Description:
         Read-only field reflecting the currently effective level at the time the entry is created.

   - :Field:
         ``new_level``
     :Description:
         New level to become effective. This field is mandatory.


.. _member_journal_processing_logic:

Processing Logic
================

Each journal entry is evaluated based on its ``effective_date``.


.. _member_journal_processing_immediate:

Immediate Processing
--------------------

If the ``effective_date`` is today or in the past, the entry is processed
immediately after saving.

Processing includes:

* Updating the effective member state or level
* Setting derived fields such as ``starttime`` or ``endtime``
* Marking the entry as processed


.. _member_journal_processing_scheduled:

Scheduled Processing
--------------------

If the ``effective_date`` lies in the future, the entry remains pending.

Pending entries are processed by a TYPO3 scheduler task.

The recommended setup uses the console command:

``clubmanager:journal:process``

See :ref:`Member Journal processing task <schedulerMemberJournalProcessTask>`
for configuration details.

Once the effective date is reached, the system applies the change
and marks the entry as processed.


.. _member_journal_processing_planned_cancellation:

Planned Cancellation
--------------------

If a cancellation is scheduled for a future date:

* ``endtime`` is set immediately to the defined ``effective_date``
* The ``state`` remains unchanged until the effective date is reached
* The final transition to ``cancelled`` is executed by the scheduler


.. _member_journal_status_lifecycle:

Status Lifecycle and Side Effects
=================================

Status changes may trigger additional side effects such as setting
membership dates or managing the associated frontend user.


.. _member_journal_lifecycle_first_activation:

First Activation
----------------

Prerequisites:

* The membership number (:guilabel:`Membership number (ident)`) must be set.
  If it is missing, activation is blocked.
* If the member email is missing, activation is still possible but a warning
  is shown because no email can be sent.

Side effects:

* ``starttime`` is set automatically (one-time only)
* A frontend user is created automatically if missing
* The frontend username is set to the membership number (ident)
* The user is assigned to the configured frontend user group
* If an email address exists, an email is sent to request a password


.. _member_journal_lifecycle_resting:

Resting
---------

If the status becomes ``resting``:

* ``state`` is set to ``resting``
* ``endtime`` remains empty
* The associated frontend user is disabled
* Login is not possible


.. _member_journal_lifecycle_cancelled:

Cancelled
---------

Immediate cancellation:

* ``state`` becomes ``cancelled``
* ``endtime`` is set to the ``effective_date``
* The associated frontend user is disabled

Planned cancellation:

* The entry remains pending until the effective date
* ``endtime`` is set immediately to the defined ``effective_date``
* ``state`` changes to ``cancelled`` when the scheduler processes the entry


.. _member_journal_lifecycle_reactivation:

Reactivation
------------

Reactivation is performed by creating a new status change to ``active``.

Effects:

* ``endtime`` is cleared
* ``starttime`` remains unchanged
* The associated frontend user is enabled again


.. _member_journal_validation_rules:

Validation Rules
================

The Member Journal enforces strict validation rules to ensure
data consistency and historical integrity.


Status Validation
-----------------

* A status change to the currently effective status is not allowed.
* Only one pending status change is allowed at a time.
* The membership number (:guilabel:`Membership number (ident)`)
  must be set before the first activation.
* Activation without an email address triggers a warning but is allowed.
* If ``effective_date`` lies in the past, a warning is shown.


Level Validation
----------------

* ``new_level`` must not equal ``old_level``.
* If ``effective_date`` lies in the past, a warning is shown.


Entry Integrity
---------------

* Entries marked as hidden are not processed.
* Hidden entries are excluded from statistics and billing.
* A hidden entry cannot be reactivated if newer journal entries exist
  (based on ``entry_date``).


.. _member_journal_visibility_logic:

Visibility Logic (Hidden Entries)
=================================

The :guilabel:`Hidden` flag of a journal entry controls whether
an entry is processed.

If a journal entry is marked as hidden:

* It is excluded from processing.
* It is excluded from statistics and billing.
* It is treated as if it did not exist for lifecycle evaluation.

The hidden flag is the official mechanism to withdraw
a pending journal entry.

.. important::

   The hidden flag must not be used to restore outdated
   historical states. If newer entries exist, older hidden
   entries cannot be reactivated.


.. _member_journal_permissions:

Permissions
===========

Permissions differ between administrators and editors.


Administrator
-------------

Administrators may:

* Delete journal entries
* Modify the membership number after activation


Editor (Office)
---------------

Editors may:

* Create new journal entries
* Set ``effective_date``
* Add notes

Editors may not:

* Modify processed entries
* Edit ``entry_date``
* Edit ``old_level``
* Delete journal entries
* Modify the membership number after activation


.. _member_journal_edge_cases:

Edge Cases
==========

The following scenarios require special attention.


Activation without membership number
-------------------------------------

Activation is blocked if the
:guilabel:`Membership number (ident)` is not set.


Activation without email address
---------------------------------

Activation is allowed without an email address, but no
password email can be sent. A warning is shown.


Reactivation after cancellation
--------------------------------

When reactivating a previously cancelled membership:

* ``endtime`` is cleared
* ``starttime`` remains unchanged
* The frontend user is re-enabled


Retroactive changes
-------------------

If ``effective_date`` lies in the past, a warning is displayed.
Retroactive changes may affect billing and statistics.


Multiple status changes on the same day
---------------------------------------

If multiple status changes share the same ``effective_date``,
processing order is determined by ``entry_date``.


Starttime fixation
------------------

Once ``starttime`` has been set during first activation,
it is never modified again.


Historical consistency
----------------------

Journal entries must reflect the chronological order of events.
Older hidden entries cannot be reactivated if newer entries exist.
