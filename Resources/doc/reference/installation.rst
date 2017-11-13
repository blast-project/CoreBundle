Installation
============

Blast CoreBundle can be installed at any moment during a project's lifecycle,
whether it's a clean Symfony installation or an existing project.

Prerequisites
-------------

-  having a working Symfony2 environment
-  having created a working Symfony2 app (including your DB and your DB
   link)
-  having composer installed (here in /usr/local/bin/composer, with
   /usr/local/bin in the path)

Downloading
-----------
.. code-block:: bash

  $ composer require blast-project/core-bundle dev-master

This will download and install :

-  knplabs/knp-menu
-  knplabs/knp-menu-bundle
-  cocur/slugify
-  sonata-project/core-bundle
-  sonata-project/cache
-  sonata-project/block-bundle
-  sonata-project/exporter
-  twig/extensions
-  sonata-project/admin-bundle
-  sonata-project/doctrine-orm-admin-bundle
-  blast-project/core-bundle
-  libre-informatique/base-entities-bundle
-  twig/twig ^1.22.1

Third party bundles, Sonata bundles
-----------------------------------

| Please refer to the Sonata Project’s instructions, foundable here :
| https://sonata-project.org/bundles/admin/3-x/doc/reference/installation.html
| https://sonata-project.org/bundles/user/3-x/doc/reference/installation.html

And follow the installation guides.

At the end, you should have a ``app/AppKernel.php`` that looks like
that:

.. code-block:: php

    use Symfony\\Component\\HttpKernel\\Kernel;
    use Symfony\\Component\\Config\\Loader\\LoaderInterface;


.. _Libre Informatique: https://github.com/libre-informatique/
.. _BlastBaseEntitiesBundle: https://github.com/libre-informatique/SymfonyBlastBaseEntitiesBundle
