.. include:: /Includes.rst.txt
.. index:: ! Records; Member
.. _recordMember:

======
Member
======
The member record is obviously the most important record in this extension. It
is used to organise the memberships of your club, association or company.

.. only:: html

   .. contents::
      :local:
      :depth: 1


.. _recordAddMember:

Add member record
===================
Members can be added in `ext:clubmanager` only in the `<List module>`.

.. note::

    A TYPO3 module with extensive search function, improved view and
    extended options, is available with the :ref:`ext:clubmanager_pro
    <clubmanagerPro>`!


.. _recordMemberData:

Member record data
==================
The member record is divided into tabs:

.. only:: html

   .. contents::
      :local:
      :depth: 2


.. _recordMemberTabGeneral:

Member Tab: General
-------------------
Here the general data concerning the membership are recorded.


.. _recordMemberTabGeneralMembership:

Membership
**********

Since version 2.0.0 the membership status and level are no longer edited directly in the member record. All changes are managed via the :ref:`Member Journal <member_journal>`.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Membership number (ident)
   :Description:
         Unique membership number. Must be set before the first activation. After activation this field is read-only for editors.
 - :Field:
         Membership status (derived)
   :Description:
         Derived from Member Journal entries. Not editable directly.
 - :Field:
         Level (derived)
   :Description:
         Derived from Member Journal entries. Not editable directly.
 - :Field:
         Reduced membership
   :Description:
         Purely descriptive flag. No automatic logic in Base.
 - :Field:
         Beginning of membership (derived)
   :Description:
         Automatically set on first activation. Not editable.
 - :Field:
         End of membership (derived)
   :Description:
         Automatically set when a cancellation becomes effective. Not editable.


.. _recordMemberMemberJournal:

Member Journal
==============

The Member Journal manages all status and level changes of a membership. It replaces the direct editing of the fields ``state``, ``level``, ``starttime`` and ``endtime``.

Each change of a membership is stored as a journal entry. The current state of a member record is always derived from the processed journal entries.

The following entry types are available:

* Status Change
* Level Change

See :ref:`Member Journal <member_journal>` for a complete functional description including lifecycle rules, validations and scheduler processing.


.. _recordMemberTabGeneralMemberPerson:

Member/Person
*************
Here is recorded whether the member is a person or a company,
as well as the name of the member or the direct contact person for the membership.
These data are also used for the postal address, for payment requests, or payment confirmations.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Type of person
   :Description:
         Membership can be requested by the following "person types" by default:

         - "natural person": private individual
         - "Legal person": company, association, etc.

 - :Field:
         Salutation
   :Description:
         Salutation of the member
 - :Field:
         Academic Title
   :Description:
         Academic title of the member
 - :Field:
         First Name
   :Description:
         First name of member
 - :Field:
         Middle Name
   :Description:
         Middle name of member
 - :Field:
         Last Name
   :Description:
         Last name of member
 - :Field:
         Date of birth
   :Description:
         Date of birth of the member, e.g. for automatic sending of
         congratulations.


.. _recordMemberTabGeneralAddress:

Address
*******
This is where the postal address data is recorded. For payment requests,
or payment confirmations, this address is also used, if in the
ref:`Tab Bank - Alternative Billing Address <recordMemberTabBankAltBillingAddress>`
no other address is stored.

.. important::

    For integrated payment management the module
    :ref:`ext:clubmanager_billing <clubmanagerBilling>` is required.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Company
   :Description:
         Member's company
 - :Field:
         Address suffix
   :Description:
         Member's address suffix
 - :Field:
         Street
   :Description:
         Street of the member
 - :Field:
         Postal code
   :Description:
         Postal code of the member
 - :Field:
         City
   :Description:
         Location of member
 - :Field:
         Country
   :Description:
         Country of member

.. note::

   By default a new member record is nearly empty. Only a few properties are
   set automatically. Membership status and level are not set directly in
   version 2.0.0 and are managed exclusively via the
   :ref:`Member Journal <member_journal>`.

   You can adjust default values for other fields using
   :ref:`TSconfig <domainModelMember>`



.. _recordMemberTabLocations:

Member Tab: Locations
---------------------
Different locations can be linked to each member. These are
used for presentation in the frontend and displayed in listings. With the
`ext:clubmanager` there is the possibility to display a listing of all locations of
members as well as to offer a categorization of member locations according to the
the city. See :ref:`plugins <plugins>` for more information.

.. note::

    How many locations are offered per member can, for example, be linked to the degree
    of membership. For example, the following offer can be displayed:

    * Basic: No locations
    * Bronze: Only main location
    * Silver: Main location + 1 additional location
    * Gold: Main site + X additional sites

    See also:
    :ref:`Degree of membership <recordMemberTabGeneralMembership>`


.. important::

    For clubs with many members there are the following additional modules
    :ref:`ext:clubmanager_zip_search <clubmanagerZipSearch>` and
    :ref:`ext:clubmanager_faceted_search <clubmanagerFacetedSearch>`.


.. _recordMemberTabLocationsMain:

Main Location
*************
See also :ref:`Record > Location <recordLocation>`


.. _recordMemberTabLocationsSub:

Sub Location
************
See also :ref:`Record > Location <recordLocation>`


.. _recordMemberTabBank:

Member Tab: Bank
----------------
The member's bank details are recorded here if the member participates in the
direct debit, which is usually the standard for clubs and associations.
For payment requests, or payment confirmations an alternative billing address can be created.


.. _recordMemberTabBankDirectDebit:

Direct Debit
************

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Participates in direct debit
   :Description:
         Is 'true' or 'false'. If 'true', then the fields with the account data must also be filled in.
 - :Field:
         Account holder
   :Description:
         Name of the account holder for direct debit.
 - :Field:
         IBAN
   Description:
         IBAN of the direct debit account
 - Field:
         BIC
   Description:
         BIC of the account for the direct debit procedure


.. _recordMemberTabBankAltBillingAddress:

Alternative Billing Address
***************************

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Name
   :Description:
         Name for payment requests, or payment confirmations.
 - :Field:
         Street
   Description:
         Street for payment requests, or payment confirmations.
 - Field:
         ZIP CODE
   Description:
         Postal code for payment requests, or payment confirmations.
 - Field:
         City
   Description:
         City for payment requests, or payment confirmations.
 - Field:
         Country
   Description:
         Country for payment requests, or payment confirmations.
 - Field:
         Alternative e-mail
   Description:
         Alternative e-mail address for payment requests, or payment confirmations.


.. _recordMemberTabCategories:

Member Tab: Categories
----------------------
Here, members can be additionally categorized, e.g., according to completed
certifications, as members of the board of directors or membership of other bodies.

.. note::

    Only the TYPO3 system categories are displayed here, which are made available via the
    :ref:`Extension Configuration uidCategoryMember <extensionConfigurationUidParentMember>`.
    See also
    :ref:`How to create member categories <recordCategory>`!

.. important::

    With the module
    :ref:`ext:clubmanager_faceted_search <clubmanagerFacetedSearch>` can be used to search for
    members can be searched very easily according to their categories.


.. _recordMemberTabAdditionalDate:

Member Tab: Additional Data
---------------------------
In this tab, 6 fields are available for free assignment by default.

- customfield1
- customfield2
- customfield3
- customfield4
- customfield5
- customfield6

The designation can be changed with the following TCEFORM:

.. code-block:: bash

    TCEFORM {
        tx_clubmanager_domain_model_member {
            customfield1 {
                label = LLL:EXT:your_sitepackage/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield1.default
            }
        }
    }

.. note::

    More information on how to use the fields in the fluid template,
    can be found here: :ref:`How to: Templates <templates>`!


.. _recordMemberTabAccess:

Member Tab: Access
------------------

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
 - :Field:
         Visible
   :Description:
         Only visible member records are displayed in the frontend. In addition, frontend output depends on the effective :guilabel:`membership status` derived from the Member Journal.
 - :Field:
         Record created on
   :Description:
         Invariant timestamp for the initial creation of the
         member record.


.. _recordMemberRelations:

Relations
=========

Member records can have relations to other records. These
are described in more detail here.

.. only:: html

   .. contents::
        :local:
        :depth: 2


.. _recordMemberRelationsFrontendUser:

Frontend-Benutzer
-----------------
This relation handles the frontend-user for a member.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field:
   :Description:
         Description:
  - :Field:
         Username
   :Description:
         The frontend username is derived from the membership number
         (:guilabel:`Membership number (ident)`).

         It is read-only for editors. Administrators may change it
         if required.
 - :Field:
         Password (Empty = Regenerate + email to user).
   :Description:
         Passwords are never sent in plain text.

         When a member is activated for the first time via the
         Member Journal, a frontend user is created automatically.
         If no password is set, the member receives an email asking
         to define a password.

         If an existing password is removed and the member record
         is saved, a new password reset email is triggered.
 - :Field:
         User Groups
   :Description:
         Frontend users are automatically assigned to group
         :guilabel:`clubmanager_FrontendUserGroup`.
 - :Field:
         Last login
   :Description:
         Shows the date of the last login. If a member has never
         logged in, it will say :guilabel:`01-01-70 00:00`. In this case
         the member will receive a reminder e-mail for the first login when
         the corresponding
         :ref:`Member login reminder task <schedulerMemberLoginReminderTask>`
         is set up.
 - :Field:
         First login reminder email.
   :Description:
         If a
         :guilabel:`First login reminder email` is generated for a member,
         the timestamp of this e-mail is set here. The member will then receive
         a new :guilabel:`First login reminder` e-mail only
         after the :guilabel:`days until reminder` has expired in the
         :ref:`Member login reminder task <schedulerMemberLoginReminderTask>`.

.. note::

   An email address is required for the standard login workflow
   (initial password setup and password reset). Without an email
   address, frontend login is only possible if an administrator
   sets a password manually and communicates it through another channel.


.. _recordMemberRelationsLocation:

Locations
--------
Basically, :guilabel:`Main location` and :guilabel:`Other locations` have
the same data structure. While there can be only one main location, many
:guilabel:`Other locations` are possible.
See :ref:`Record > Location <recordLocation>` for more information.
