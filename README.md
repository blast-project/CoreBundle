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

Twig
----

Then you'll probably need to force a higher version of Twig (≥ 1.22.1). To do that, edit the file "composer.json" in the root of your project to add or modify a line like:

```
  "twig/twig": "^1.22.1",
```

Then :

```
  $ composer update
```

Sonata bundles
--------------

Please refer to the Sonata Project's instructions, foundable here :
https://sonata-project.org/bundles/admin/2-3/doc/reference/installation.html

PostgreSQL
----------

Create the database needed

If you are using PostgreSQL as your main database, you'll need to install postgresql-contrib and load the "uuid-ossp" extension :

```
  $ apt-get install postgresql-contrib
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
            new Librinfo\CoreBundle\CoreBundle(),
            
            // your personal bundles
        );
    }
```
