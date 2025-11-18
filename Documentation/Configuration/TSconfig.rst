.. include:: /Includes.rst.txt
.. index:: Configuration; TSconfig reference
.. _tsconfig:

==================
TSconfig reference
==================

You can change the backend forms for the records :ref:`member <recordMember>`
& :ref:`location <recordLocation>` with the following Page & User
TSconfig

.. only:: html

   .. contents:: Important TSconfig
      :depth: 2
      :local:


.. _domainModelMember:

Clubmanager member record
=========================

Change the User TSconfig to disable not needed fields in member records for your
backend editors. Use Page TSconfig to remove not needed elements in
dropdown-fields or to add some and to rename field labels.

In addition, the member record has five predefined fields that you can use for
your individual data.

- customfield1
- customfield2
- customfield3
- customfield4
- customfield5

.. _domainModelMemberUserTsconfig:

User TSconfig
-------------

.. code-block:: bash

    page.TCEFORM.tx_clubmanager_domain_model_member {
        starttime.disabled = 0
        endtime.disabled = 0
        reducedRate.disabled = 0
        state.disabled = 0
        feuser.disabled = 0
        directDebit.disabled = 0
        iban.disabled = 0
        bic.disabled = 0
        account.disabled = 0
        mainLocation.disabled = 0
        subLocations.disabled = 0
        altBillingName.disabled = 0
        altBillingStreet.disabled = 0
        altBillingZip.disabled = 0
        altBillingCity.disabled = 0
        altBillingCountry.disabled = 0
        ident.disabled = 0
        title.disabled = 0
        firstname.disabled = 0
        midname.disabled = 0
        lastname.disabled = 0
        zip.disabled = 0
        street.disabled = 0
        city.disabled = 0
        country.disabled = 0
        email.disabled = 0
        phone.disabled = 0
        company.disabled = 0
        personType.disabled = 0
        salutation.disabled = 0
        level.disabled = 0
        addAddressInfo.disabled = 0
        dateofbirth.disabled = 0
        categories.disabled = 0
    }

.. _domainModelMemberPageTsconfig:

Page TSconfig
-------------

.. code-block:: bash

    TCEFORM {
        tx_clubmanager_domain_model_member {
            // Set this to 0 if your member categories have no subcategories.
            // 0,1 is useful when using the placeholder data.
            categories.config.treeConfig.appearance.nonSelectableLevels = 0,1

            salutation {
                removeItems =
                altLabels {
                    default = LLL:EXT:your_sitepackage/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.default
                }
                addItems {
                    divers = LLL:EXT:your_sitepackage/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.divers
                }
            }
            customfield1 {
                label = LLL:EXT:your_sitepackage/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield1.default
            }
        }
    }

.. _domainModelLocation:

Clubmanager location record
===========================

Change the User TSconfig to disable not needed fields in location records for
your backend editors. Use Page TSconfig to remove not needed elements in
dropdown-fields or to add some and to rename field labels.

.. _domainModelLocationUserTsconfig:

User TSconfig
-------------

.. code-block:: bash

    page.TCEFORM.tx_clubmanager_domain_model_location {
        salutation.disabled = 0
        title.disabled = 0
        firstname.disabled = 0
        midname.disabled = 0
        lastname.disabled = 0
        company.disabled = 0
        street.disabled = 0
        addAddressInfo.disabled = 0
        zip.disabled = 0
        city.disabled = 0
        state.disabled = 0
        country.disabled = 0
        latitude.disabled = 0
        longitude.disabled = 0
        image.disabled = 0
        info.disabled = 0
        categories.disabled = 0
        phone.disabled = 0
        mobile.disabled = 0
        fax.disabled = 0
        email.disabled = 0
        website.disabled = 0
        socialmedia.disabled = 0
        youtubeVideoUrl.disabled = 0
    }

.. _domainModelLocationPageTsconfig:

Page TSconfig
-------------


.. code-block:: bash

    TCEFORM {
        tx_clubmanager_domain_model_location {
            salutation {
                removeItems =
                altLabels {
                    default = LLL:EXT:your_sitepackage/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.salutation.default
                }
                addItems {
                    divers = LLL:EXT:your_sitepackage/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.salutation.divers
                }
            }
        }
    }
