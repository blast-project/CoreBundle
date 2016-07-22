# Unit Test


Installation
============

Prerequiresites
---------------

* PHPUnit

Downloading
-----------

  $ composer require --dev doctrine/doctrine-fixtures-bundle


Configuring unit test
---------------------

Add the directory which contains your tests

```
<!-- app/phpunit.xml -->
<testsuites>
    <testsuite name="<Your Project Name>">
        <!-- ... -->
        <directory>../src/*/<YourBundle>Bundle/Tests</directory>
        <!-- ... -->
    </testsuite>
</testsuites>
```

Doctrine Fixtures bundle
------------------------

Please refer to instructions from [official DoctrineFixturesBundle](http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html)


Configuring your Database use for Unit Test
-------------------------------------------

```
# app/config/parameters_test.yml
parameters:
    database_host: DB_HOST
    database_port: DB_PORT
    database_name: DB_NAME
    database_user: DB_USER
    database_password: DB_PASSWD
```

###### Note:
Override your parameters to use test database 
```
# app/config/config_test.yml
imports:
    - { resource: config_dev.yml }
    - { resource: parameters_test.yml }
# ...
```


Updating your schema for environment test
-----------------------------------------

    $ app/console doctrine:schema:validate --env=test
    $ app/console doctrine:schema:update --dump-sql --env=test
    $ app/console doctrine:schema:update --force --env=test
    

Usages
======

Configuring your data fixtures properties
-----------------------------------------

Create new file configuration datafixtures.yml

```
# <YourBundle>Bundle/Resources/config/datafixtures.yml
parameters:
    librinfo.datafixtures:
        user:
            username: USERNAME
            password: PASSWORD
            email: EMAIL
            role: ROLE
```

###### Note:
Define custom parameters in order to use then in your unit test.


Call new configuration in environment test
------------------------------------------

Load datafixtures.yml using Yaml File Loader

```php
// <YourBundle>Bundle/DependencyInjection/<YourBundle>Extension.php
// ...
if($container->getParameter('kernel.environment') == 'test')
{
    if(!$container->hasParameter('librinfo.datafixtures')){
        $container->setParameter('librinfo.datafixtures', array());
    }
    $this->mergeParameter('librinfo.datafixtures', $container, __DIR__.'/../Resources/config/','datafixtures.yml');
}
// ...
```


Initialise data in database test
--------------------------------

Define your data to initialise user test informations.

```php
// <YourBundle>Bundle/DataFixtures/ORM/LoadUserData.php
// ...
$fixturesData = $this->container->getParameter('librinfo.datafixtures');
$userAdmin = new User();
$userAdmin->setUsername($fixturesData['user']['username']);
$userAdmin->setPassword($fixturesData['user']['password']);
$userAdmin->setEmail($fixturesData['user']['email']);
$userAdmin->addRole($fixturesData['user']['role']);
$manager->persist($userAdmin);
$manager->flush();
// ...
```

Run initialise database test

    $ app/console doctrine:fixtures:load --env=test
    
###### Note:
Don't forget the option "--env=test", this command clears your database before inserting data.


Test using security users
-------------------------

Authenticates user defined in your configuration in order to allow testing of security based traitments.

```php
// <YourBundle>Bundle/Tests/Features/<YourTest>.php
// ...
/** @var User $user */
$client = static::createClient();
$datafixtures = $client->getContainer()->getParameter('librinfo.datafixtures');
$user = $client->getContainer()->get('librinfo_core.services.authenticate')->authencicateUser($datafixtures['user']['username']);
// ...
```


Finish
------

Run your test

    $ phpunit -c app
