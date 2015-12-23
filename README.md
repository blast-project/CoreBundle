# SymfonyLibrinfoCoreBundle
This is the core of Libre Informatique Symfony2 projects

Installation
============

Prequiresites
-------------

- having a working Symfony2 environment
- having created a working Symfony2 app (including your DB and your DB link)
- having composer installed (here in /usr/local/bin/composer, with /usr/local/bin in the path)

Downloading
-----------

  $ composer require libre-informatique/core-bundle dev-master

This will download and install :
* knplabs/knp-menu
* knplabs/knp-menu-bundle
* cocur/slugify
* sonata-project/core-bundle
* sonata-project/cache
* sonata-project/block-bundle
* sonata-project/exporter
* twig/extensions
* sonata-project/admin-bundle
* sonata-project/doctrine-orm-admin-bundle
* libre-informatique/core-bundle
* libre-informatique/base-entities-bundle
* twig/twig ^1.22.1

Sonata bundles
--------------

Please refer to the Sonata Project's instructions, foundable here :
https://sonata-project.org/bundles/admin/2-3/doc/reference/installation.html

PostgreSQL
----------

Create the database needed

If you are using PostgreSQL as your main database, you'll need to install postgresql-contrib and load the "uuid-ossp" extension :

```
  $ sudo apt-get install postgresql-contrib
  $ echo 'CREATE EXTENSION "uuid-ossp";' | psql [DB]
```

The "libre-informatique" bundles
--------------------------------

Edit your app/AppKernel.php file and add your "libre-informatique" bundle, for instance the "libre-informatique/core-bundle" :

```php
    // app/AppKernel.php
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            
            // The libre-informatique bundles
            new Librinfo\CoreBundle\LibrinfoCoreBundle(),
            
            // your personal bundles
        );
    }
```

Usages
======

Configuring your SonataAdminBundle interfaces with YAML properties
------------------------------------------------------------------

[Discover the configuration of the ```CoreBundle```](Resources/doc/README-Usages.md).

Going further...
----------------

#### Read more about the CoreBundle, and how to improve the SonataAdminBundle

[Improving the SonataAdminBundle in a generic way](Resources/doc/README-SonataAdmin-Traits.md)


#### Configuring a standalone bundle

[Discover the configuration of a standalone bundle](Resources/doc/README-StandaloneBundle.md).

#### Going further using the CoreAdmin

Instead of the ```Librinfo\CoreBundle\Admin\Traits\Base``` trait, you might be interested in :
* ```Librinfo\CoreBundle\Admin\Traits\Embedded```: if the current ```CoreAdmin``` aims to be embedded
* ```Librinfo\CoreBundle\Admin\Traits\Embedding```: if the current ```CoreAdmin``` aims to embed other forms and you want its embedding fields to be treated automatically

#### Using Sonata Project extensions

If needed, you can easly use the LibrinfoCoreBundle in combination with other bundles from the Sonata Project, for instance :

* [sonata-project/intl-bundle](https://sonata-project.org/bundles/intl/master/doc/index.html)
* [sonata-project/notification-bundle](https://sonata-project.org/bundles/notification/master/doc/index.html)
* [sonata-project/user-bundle](https://sonata-project.org/bundles/user/2-2/doc/index.html)
* [sonata-project/formatter-bundle](https://sonata-project.org/bundles/formatter/2-2/doc/index.html)

Please refer yourself to the proper documentation from the Sonata Project...

#### Managing entities dashboard groups

[Discover the configuration of the dashboard](Resources/doc/README-Dashboard.md).

#### Using LibrinfoCore command tools for patches

[Discover how to implement patches](Resources/doc/README-Patches-HowTo.md)
