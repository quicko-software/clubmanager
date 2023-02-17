.. include:: /Includes.rst.txt
.. index:: Configuration; How to setup felogin
.. _setupFeLogin:

====================
How to setup felogin
====================

.. important::

   If not done yet you have to install `ext:bootstrap_package v12.0.7 <https://www.bootstrap-package.com/>`__ and
   `felogin <https://docs.typo3.org/c/typo3/cms-felogin/main/en-us//Index.html>`__ first.
   See :ref:`Quick configuration <quickConfiguration>` :ref:`Config felogin <configFeLogin>`!

.. only:: html

   .. contents:: Important steps
      :depth: 1
      :local:


.. _setupFeLoginPageTree:

Recommended felogin pagetree
============================

Your page tree for the automatisation of felogin should now look like that:

.. code-block:: none

   Home
   ├── ...
   ├── Login page
   │  └── Restricted content
   ├── Logout target page
   ├── ...
   └── Storage
      ├── ...
      ├── Frontend User
      └── ...

.. important::

   Make sure you did the :ref:`Global extension configuration <extensionConfiguration>`
   and set the important global UID's there.


.. _setupFeLoginDefaultContent:

Default content elements
========================

#. In your pagetree click on `Login page`

#. Insert :guilabel:`+ Content` some default content which is shown if members are not logged in yet.

#. Set Tab :guilabel:`Access` > :guilabel:`Usergroup Access Rights` > :guilabel:`Hide at login`.

#. :guilabel:`Save` the contentelement and create a :guilabel:`+ New` one.

#. Insert a welcome message and stuff which is shown if members are successfully logged in.

#. Set Tab :guilabel:`Access` > :guilabel:`Usergroup Access Rights` > :guilabel:`Show at any login`.

#. Create new pages beneath your login page and :guilabel:`Edit page properties`.

#. See Tab :guilabel:`Access` and set :guilabel:`Usergroup Access Rights` to
   your :guilabel:`felogin_usergroup` you created here: `:ref:Create default frontend user group <configFeLoginCreateDefaultFeuserGroup>`.

#. At least insert :guilabel:`+ Content` > :guilabel:`Text` > :guilabel:`Text & Media` to say "Logout successful"
   on page `Logout target page`.


.. _setupFeLoginSetupTesting:

Testing your felogin setup
==========================

#. Go to the module :guilabel:`Web > List`

#. Go to the "Member Storage" folder that you created in :ref:`Create some
   initial content <quickMemberRecords>`.

#. Open a test member with :guilabel:`Edit record` and have a look at section
   :guilabel:`Frontend User`. Now you can (:guilabel:`+ Create new`) create a
   :ref:`Frontend User <recordMemberRelationsFrontendUser>` for this member or
   edit an exiting one.

   .. figure:: /Images/Configuration/be-edit-existing-feuser.png
      :class: with-shadow
      :alt: Connected frontend user for member

      Connected frontend user for member

#. Set/Change :guilabel:`Username` and change the :guilabel:`Password` to some
   easy to remember string for testing. Then save the member data.

   .. important::

      If you clear the Password input field and save an
      :ref:`E-Mail Task <recordEmailTask>` is generated. For automatic sending
      of created :ref:`E-Mail Tasks <recordEmailTask>` you have to configure the
      :ref:`EMail Service scheduler task <schedulerMailServiceTask>` as
      described there.

#. Load the "Login page" in the frontend and try to login.


.. _setupMemberLoginReminderTask:

Setup member login reminder task
================================

.. note::

   If you want to send regular reminders to members who have never logged in,
   you have to create the appropriate scheduler task to do this. See section
   :ref:`Member login reminder task <schedulerMemberLoginReminderTask>` for how
   to setup the scheduler task.




