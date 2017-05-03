# Blast CoreBundle

[![Scrutinizer](https://img.shields.io/scrutinizer/g/filp/whoops.svg?style=flat-square)][scrutinizer]
[![Travis](https://img.shields.io/travis/blast-project/CoreBundle.svg?style=flat-square)][travis]
[![Coveralls](https://img.shields.io/coveralls/blast-project/CoreBundle.svg?style=flat-square)][coveralls]
[![License](https://img.shields.io/github/license/blast-project/CoreBundle.svg?style=flat-square)][license]

The goal of this bundle is to make the use of SonataAdmin "view-models" possible without writting a line of PHP, without loosing a feature of Sonata, and importing the idea of composite settings using lots of characteristics of an admin (its direct inheritance tree, the traits used by its Entity, the inheritance tree of its Entity...), making things more flexible, extendable, reusable and maintenable through many bundles and uses.

This bundle is the next step after the SonataAdminBundle. Configure an entire backend bundle filling only YAML files... Try it!

It is also the core of [Libre Informatique](https://github.com/libre-informatique/)'s Symfony 2/3 projects.

Example
========

I want to design and create a bundle as a toolbox for other bundles' entities. It will provide traits for email addresses and phonenumbers, for instance (cf. [BlastBaseEntitiesBundle](https://github.com/libre-informatique/SymfonyBlastBaseEntitiesBundle)).

Using the BlastCoreBundle, your "base" bundle will carry the traits, but also the way to display properties given by its traits in a SonataAdmin (which becomes a CoreAdmin) CRUD. Then using the traits of your "base" bundle in the entities of other bundles (also implementing the BlastCoreBundle) will add the fields naturally, the columns in the list of objects, etc... as you set up for your trait in your "base" bundle, without having to write a line for this.

Imagine this feature appliable to 50 entities distributed in 10 bundles, and count in your mind the number of saved lines, the number of potential bugs avoided and the ease of maintenance when you want to change the nature of the field used by the provided email address or phonenumber... This is what the BlastCoreBundle permits.

Installation
============

Prerequisites
-------------

- having a working Symfony2 environment
- having created a working Symfony2 app (including your DB and your DB link)
- having composer installed (here in /usr/local/bin/composer, with /usr/local/bin in the path)

Downloading
-----------

  $ composer require blast-project/core-bundle dev-master

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
* blast-project/core-bundle
* libre-informatique/base-entities-bundle
* twig/twig ^1.22.1

Third party bundles, Sonata bundles
--------------

Please refer to the Sonata Project's instructions, foundable here :
https://sonata-project.org/bundles/admin/3-x/doc/reference/installation.html
https://sonata-project.org/bundles/user/3-x/doc/reference/installation.html

And follow the installation guides.

At the end, you should have a ```app/AppKernel.php``` that looks like that:

```php
<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            //...
            
            // Add your dependencies
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            //...

            // If you haven't already, add the storage bundle
            // This example uses SonataDoctrineORMAdmin but
            // it works the same with the alternatives
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),

            // You have 2 options to initialize the SonataUserBundle in your AppKernel,
            // you can select which bundle SonataUserBundle extends
            // Most of the cases, you'll want to extend FOSUserBundle though ;)
            // extend the ``FOSUserBundle``
            //new FOS\UserBundle\FOSUserBundle(),
            //new Sonata\UserBundle\SonataUserBundle('FOSUserBundle'),
            // OR
            // the bundle will NOT extend ``FOSUserBundle``
            //new Sonata\UserBundle\SonataUserBundle(),

            // Then add SonataAdminBundle
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Blast\CoreBundle\BlastCoreBundle(),
            
            //...
        ];
        //...
    }
    //...
}
```

And at the end of the ```app/config/config.yml``` file:

```yml
# ...
sonata_block:
    default_contexts: [cms]
    blocks:
        # Enable the SonataAdminBundle block
        sonata.admin.block.admin_list:
            contexts:   [admin]
        # Your other blocks
```

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
            new Blast\CoreBundle\BlastCoreBundle(),
            
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

Instead of the ```Blast\CoreBundle\Admin\Traits\Base``` trait, you might be interested in :
* ```Blast\CoreBundle\Admin\Traits\Embedded```: if the current ```CoreAdmin``` aims to be embedded
* ```Blast\CoreBundle\Admin\Traits\Embedding```: if the current ```CoreAdmin``` aims to embed other forms and you want its embedding fields to be treated automatically

[Creating new field types](Resources/doc/README-CreatingFieldTypes.md).

#### Using Sonata Project extensions

If needed, you can easly use the BlastCoreBundle in combination with other bundles from the Sonata Project, for instance :

* [sonata-project/intl-bundle](https://sonata-project.org/bundles/intl/master/doc/index.html)
* [sonata-project/notification-bundle](https://sonata-project.org/bundles/notification/master/doc/index.html)
* [sonata-project/user-bundle](https://sonata-project.org/bundles/user/2-2/doc/index.html)
* [sonata-project/formatter-bundle](https://sonata-project.org/bundles/formatter/2-2/doc/index.html)

Please refer yourself to the proper documentation from the Sonata Project...

#### Managing entities dashboard groups

[Discover the configuration of the dashboard](Resources/doc/README-Dashboard.md).

#### Using Blast Core command tools for patches

[Discover how to implement patches](Resources/doc/README-Patches-HowTo.md)



[scrutinizer]: https://scrutinizer-ci.com/g/blast-project/CoreBundle
[travis]: https://travis-ci.org/blast-project/CoreBundle
[license]: ./LICENSE.md
[contributing]: ./CONTRIBUTING.md
[coveralls]: https://coveralls.io/github/blast-project/CoreBundle?branch=master
