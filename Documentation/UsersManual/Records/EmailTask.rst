.. include:: /Includes.rst.txt
.. index:: ! Records; Email Tasks
.. _recordEmailTask:

===========
Email Tasks
===========

.. note::

   You can find all your :ref:`E-Mail Tasks <recordEmailTask>` in your TYPO3
   installation root at id=0!

   A TYPO3 module with improved view of all email tasks and extended
   options, is available with the :ref:`ext:clubmanager_pro <clubmanagerPro>`!

.. important::

   For automatic sending of open `E-Mail Task` you have
   to configure the :ref:`EMail Service scheduler task <schedulerTasks>` as
   described there.

.. only:: html

   .. contents:: Important steps
      :depth: 1
      :local:

.. _recordEmailTaskPasswordRecovery:

Automatic password recovery email
=================================

#. Open an existing member and open the connected :guilabel:`Frontend User`.
   Clear the :guilabel:`Password` Input and save the member data. Now you can
   see, that a new password is automatically generated.

#. What happens next is, that an "E-Mail Task" is generated which will send an
   E-Mail to the "Frontend Users" so he can reset the password.

   .. important::

      Make sure, that the :guilabel:`Scheduler Task` :ref:`EMail Service scheduler task <schedulerTasks>`
      is running at short intervals

.. _recordEmailTaskChangeCoreMailSettings:

Change core mail settings
=========================

The "ext:clubmanager" uses the TYPO3 core mail settings, which have to be changed
in the :guilabel:`LocalConfiguration.php`.
Therefor open :guilabel:`Admin Tools` > :guilabel:`Settings` > :guilabel:`Configure Installation-Wide Options`
> :guilabel:`Mail` and adjust the following settings to your needs:

#. [MAIL] defaultMailFromAddress > your-email@your-site.tld

#. [MAIL] defaultMailFromName > Your Name

#. [MAIL] defaultMailReplyToAddress > no-reply@your-site.tld

#. [MAIL] defaultMailReplyToName > Your Name

.. _recordEmailTaskChangeClubmanagerMailTemplates:

Change clubmanager mail templates
=================================

To override the standard `ext:clubmanager` mail templates with your own you can use
the TypoScript **constants** to set the paths. Add these lines to the file
EXT:mysitepackage/Configuration/TypoScript/constants.typoscript in your sitepackage.
and fit the templates to your needs.

.. code-block:: typoscript
   :caption: TypoScript constants

   plugin.tx_clubmanager {
      mailView {
         templateRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Templates/Email
         partialRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Partials/Email
         layoutRootPath = EXT:mysitepackage/Resources/Private/Extensions/Clubmanager/Layouts/Email
      }
   }

.. _recordEmailTaskRecord:

Email Task Record
==================

Email task are always generated, when emails were send out by the `ext:clubmanager`
itself. The reason for this is to have an overview of the club's email processes
and to be able to follow up in case of doubt if there were errors in the dispatch.

Email tasks are titled after the process that triggered them and could not be
renamed by an editor.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Visible
   :Description:
         Only visible tasks are processed by the :ref:`EMail Service scheduler task <schedulerTasks>`
 - :Field:
         Status
   :Description:
         Shows if an email task is ``Open``, ``Done`` or ``Failed``
 - :Field:
         Open attempts for delivery
   :Description:
         Shows how many attemps for delivery have taken place and how often
         the delivery is still attempted
 - :Field:
         E-Mail Generator Class
   :Description:
         Shows the used mail generator
 - :Field:
         E-Mail Generator Arguments
   :Description:
         Shows the used mail generator arguments
 - :Field:
         Last processing
   :Description:
         Shows the datetime of the last processing
 - :Field:
         Last error time
   :Description:
         Shows the datetime of the last failure
 - :Field:
         Error message
   :Description:
         In case of a failed processing the reason is displayed here
