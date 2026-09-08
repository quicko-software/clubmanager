.. include:: /Includes.rst.txt
.. index:: Upgrade; Mailjournal
.. _upgrade_mailjournal:

=================================
Mail queue → ext:mailjournal
=================================

Clubmanager no longer ships its own mail queue. All generated mails go through
`ext:mailjournal` (table ``tx_mailjournal_domain_model_mail_task``, scheduler
class ``Quicko\Mailjournal\Tasks\MailServiceTask``, backend module under
Admin/Tools).

Required after deploying this change
====================================

#. Ensure `ext:mailjournal` is installed (Composer requirement of
   `ext:clubmanager`).
#. Run the Upgrade Wizard
   :guilabel:`Clubmanager: Mail-Queue nach mailjournal übernehmen`.
   It copies rows from ``tx_clubmanager_domain_model_mail_task`` and rewrites
   existing scheduler records.
#. In the Database Analyzer, remove the obsolete table
   ``tx_clubmanager_domain_model_mail_task`` **after** the wizard ran.
#. Configure :guilabel:`mailTries` in the mailjournal extension settings
   (Clubmanager ``mailTries`` is unused).
#. The Clubmanager-Pro Mailtasks module is gone. Use the mailjournal module
   under :guilabel:`Admin` / :guilabel:`Tools`.
#. The Clubmanager Site Set depends on ``quicko/mailjournal``; enable that set
   if a site includes Clubmanager TypoScript without the set.

Backward compatibility
======================

Queued ``generator_class`` values
``Quicko\Clubmanager\Mail\Generator\GenericMailGenerator`` still resolve via a
deprecated subclass. New mails use ``GenericMemberMailGenerator``.
