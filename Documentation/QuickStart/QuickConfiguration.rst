.. include:: /Includes.rst.txt
.. index:: Quick start; Quick configuration
.. _quickConfiguration:

===================
Quick configuration
===================

.. note::

    The screenshots used in this documentation assume the use of
    `ext:bootstrap_package <https://www.bootstrap-package.com/>`__
    by Benjamin Kott.
    Without this template extension you have to create your own views with the,
    `fluid` templates provided by the `ext:clubmanager`.


Enable Site Set
===============

This extension is configured via TYPO3 Site Sets. Static TypoScript templates
are no longer supported.
For details, see the TYPO3 main documentation:
`Site sets <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/SiteHandling/SiteSets.html>`__.

.. tip::

    For a quick start, the use of
    `ext:bootstrap_package ^16.0 <https://www.bootstrap-package.com/>`__
    by Benjamin Kott is recommended.

#. Open your site config:
   :file:`config/sites/<your-site>/config.yaml`

#. Add the base set dependency:

   .. code-block:: yaml

      dependencies:
        - quicko/clubmanager

Read more about configuration via Site Settings (preferred) and TypoScript
fallback in the :ref:`Configuration <configuration>` section.

Further reading
===============

*  :ref:`Global extension configuration <extensionConfiguration>`
*  :ref:`TypoScript <typoscript>`, mainly configuration for the frontend
*  :ref:`TsConfig <tsconfig>`, configuration for the backend
*  :ref:`Routing <routing>` for human readable URLs
*  :ref:`Templating <quickTemplating>` customize the templates
