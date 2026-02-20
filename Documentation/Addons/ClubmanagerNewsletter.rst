.. include:: /Includes.rst.txt
.. index:: Addons; ClubmanagerNewsletter
.. _clubmanagerNewsletter:

=======================
Clubmanager Newsletter
=======================

Newsletter subscriptions for members
------------------------------------

- Frontend plugin :guilabel:`Newsletter List` for subscribe/unsubscribe and resend confirmation
- Integration into member management (`Newsletters` tab in member records)
- Dashboard integration for members (`Newsletters` module)
- Subscription sync based on member lifecycle:
  active membership, verified email and newsletter consent
- Support for mandatory and optional newsletters, including sign-in type
  for new members
- REST API integration (base URL + API key) for newsletter provider communication
- Optional mapping of member fields to newsletter custom fields

.. note::
    The extension depends on `ext:clubmanager` and `ext:clubmanager_pro`.
    TYPO3 `ext:form` can be used for consent and registration form integration.

.. tip::
    We support a self-hosted `Sendy <https://sendy.co/>`__ setup on
    `AWS <https://aws.amazon.com/>`__ for newsletter delivery.
    If needed, you can also rent a ready-to-use brand with newsletter
    sending quota from us.
    `Contact us / Request product <https://quicko.software/en/contact-us/>`__.
