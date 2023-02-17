.. include:: /Includes.rst.txt
.. index:: Quick start; Quick configuration
.. _quickConfiguration:

===================
Quick configuration
===================

.. note::

    The screenshots used in this documentation assume the use of
    `ext:bootstrap_package v12.0.7 <https://www.bootstrap-package.com/>`__
    by Benjamin Kott.
    Without this template extension you have to create your own views with the,
    `fluid` templates provided by the `ext:clubmanager`.


Include TypoScript template
===========================

It is necessary to include at least the basic TypoScript provided by this
extension.

.. tip::

    For a quick start, the use of
    `ext:bootstrap_package v12.0.7 <https://www.bootstrap-package.com/>`__
    by Benjamin Kott is recommended.

#. Go to module :guilabel:`Web > Template` and chose your root page. It should already contain a TypoScript template record.

#. Switch to view :guilabel:`Info/Modify` and click on :guilabel:`Edit the whole template record`.

#. Switch to tab :guilabel:`Includes` and add the following templates from the list to the right: :guilabel:`Clubmanager (clubmanager)`.

.. figure:: /Images/QuickStart/be-include-typoscript.png
   :class: with-shadow
   :alt: Include static Typoscript

   Include at least :guilabel:`Clubmanager (clubmanager)` Typoscript

Read more about possible configurations via TypoScript in the
:ref:`Configuration <configuration>` section.

Further reading
===============

*  :ref:`Global extension configuration <extensionConfiguration>`
*  :ref:`TypoScript <typoscript>`, mainly configuration for the frontend
*  :ref:`TsConfig <tsconfig>`, configuration for the backend
*  :ref:`Routing <routing>` for human readable URLs
*  :ref:`Templating <quickTemplating>` customize the templates



