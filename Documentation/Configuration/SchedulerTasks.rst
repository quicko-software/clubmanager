.. include:: /Includes.rst.txt
.. index:: Configuration; Scheduler tasks
.. _schedulerTasks:

===============
Scheduler tasks
===============

.. only:: html

   .. contents:: Scheduler tasks provided by `ext:clubmanager`
      :depth: 1
      :local:

.. _schedulerMailServiceTask:

Mail service task
=================

The `ext:clubmanager` automatically generates e-mails for certain actions. To
send them automatically, the scheduler task :guilabel:`EMail Service
(clubmanager)` must be set up and triggered in short intervals if possible.

#. Go to the module :guilabel:`System > Scheduler`

#. Use the :guilabel:`+` icon in the topbar :guilabel:`Create new task`

#. Set dropdown :guilabel:`Class` to :guilabel:`clubmanager > EMail Service` (1.)

#. Set task :guilabel:`frequency` (2.) to a short interval.
   :guilabel:`*/1 * * * *` ensures that the task is executed every minute.

#. Set :guilabel:`Maximum number of emails per run` (3.) to a number, which fits
   to the needs of your club. If the number of members is high, it makes sense
   to increase the :guilabel:`Maximum number of emails per run`.

#. After you have filled in all required fields click :guilabel:`Save`.

.. note::

   Note that with 20 mails every minute (as given in the example)
   the task may try to send 1.200 mails per hour. Please check if your
   provider limits the number of outgoing mails and make sure the limit
   is not reached by the mail task configuration.

.. figure:: /Images/Configuration/be-edit-mail-service-task.png
   :class: with-shadow
   :alt: Create new task `EMail Service (clubmanager)`

   Create new task :guilabel:`clubmanager > EMail Service`

.. note::

   See section :ref:`E-Mail Tasks <recordEmailTask>` for more information on
   emails send by `ext:clubmanager`!

   A TYPO3 module with improved view of all email tasks and extended
   options, is available with the :ref:`ext:clubmanager_pro <clubmanagerPro>`!

.. _schedulerMemberLoginReminderTask:

Member login reminder task
==========================

The basic version of `ext:clubmanager` provides the scheduler Task
:guilabel:`Reminder Email (clubmanager)`, which can be used by
which members who have never logged in to the member area before
get a reminder e-mail after a predefined interval to do so.

#. Go to the module :guilabel:`System > Scheduler`

#. Use the :guilabel:`+` icon in the topbar :guilabel:`Create new task`

#. Set dropdown :guilabel:`Class` to :guilabel:`clubmanager > Member login
   reminder` (1.)

#. Set task :guilabel:`frequency` (2.) to the shortest possible interval.
   :guilabel:`0 7 * * 2` ensures that the task is executed every tuesday at 7am.

#. Set :guilabel:`Days until reminder` (3.) to a number of days, after which a
   member is to be reminded again. The lower the number of days, the higher the
   probability that a member will perceive the message as spam.

#. Set :guilabel:`List of PIDs for members, comma-separated` (4.) if only
   members from certain folders should receive a reminder email. If the field is
   empty, all members who have never logged in will receive an email.

#. After you have filled in all required fields click :guilabel:`Save`.

.. figure:: /Images/Configuration/be-edit-login-reminder-task.png
   :class: with-shadow
   :alt: Create new task Reminder email (clubmanager)

   Create new task :guilabel:`clubmanager > Reminder email`

At the set time, the scheduler runs Task
:guilabel:`Reminder Email (clubmanager)` and creates a new reminder email for all
`active(!)` members who have never logged in before, a new
:ref:`Email Tasks <recordEmailTask>` `Member Login Reminder`. During the
next automatic execution of the
:ref:`EMail Service scheduler task <schedulerMailServiceTask>`, all open
:ref:`Email Tasks <recordEmailTask>` will be processed one after the other.

.. figure:: /Images/Configuration/be-member-login-reminder-table.png
   :class: with-shadow
   :alt: Email task table with member login reminder emails

   Email task table with `Member Login Reminder` emails

.. note::

   You can find all your :ref:`E-Mail Tasks <recordEmailTask>` in your TYPO3
   installation root at id=0!

   A TYPO3 module with improved view of all email tasks and extended
   options, is available with the :ref:`ext:clubmanager_pro <clubmanagerPro>`!

.. important::

   For all members for whom a reminder e-mail has been generated,
   the :guilabel:`First login reminder email` field in the linked user record
   is automatically provided with the current timestamp. A
   new reminder email will be generated after the days specified in the Task
   :guilabel:`Reminder Email (clubmanager)` under :guilabel:`Days until reminder`
   and only if the member has still not logged in!

.. figure:: /Images/Configuration/be-member-login-reminder-timestamp.png
   :class: with-shadow
   :alt: Timestamp for last reminder email for the first registration in frontend-user data

   Timestamp for :guilabel:`Reminder email for the first registration` in
   frontend-user data

.. note::

   See section :ref:`Global felogin configuration <configFeLoginGlobal>` and
   :ref:`Testing your felogin setup <setupFeLoginSetupTesting>` for more
   information on how login email automation works with `ext:clubmanager`!
