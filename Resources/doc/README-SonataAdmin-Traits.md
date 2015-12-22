# Improving how [SonataAdminBundle](https://github.com/sonata-project/SonataAdminBundle) can be improved in a generic way

## Why are we doing this ?

Out-of-the-box, Sonata Admin modules are not capable to deal with foreign references in ManyToOne / OneToMany and ManyToMany relations. Then we tried to design a simple way to use and deploy a Sonata Admin module (especially its FormMapper), minimizing the things to do, the code to write...

## Using the ```libre-informatique/core-bundle``` features

#### Make your ```*Admin``` extend the ```Librinfo\CoreBundle\Admin\CoreAdmin```

When you want to create a Sonata module for an existing entity, you usually starts from the command line executing :

```
app/console sonata:admin:generate
```

Then you create/modify/validate your new service in your ```src/AcmeBundle/Resources/config/admin.yml```. At this point, things are working as a Sonata Admin module "Vanilla".

To be able to use the logic provided by the ```libre-informatique/core-bundle```, you need to deviate inheritance tree of your Sonata Admin. So you will change your ```src/AcmeBundle/Admin/MyAdmin.php``` that way:

```
    // src/AcmeBundle/Admin/MyAdmin.php
    // ...
    use Librinfo\CoreBundle\Admin\CoreAdmin;
    
    class MyAdmin extends CoreAdmin
    {
        // ...
    }
```

Your ```MyAdmin``` is now a ```Librinfo\CoreBundle\Admin\CoreAdmin``` before being a ```Sonata\AdminBundle\Admin\Admin``` (the ```CoreAdmin``` extends it). Things are getting serious, it sounds good.

### Define your module without writing a line of PHP

You have to create a class that :
1. extends your ```*Admin``` (eg. ```MyAdmin```)
2. is registered in the ```Sonata``` service instead of your ```*Admin``` (eg. ```MyAdmin```)
3. uses more logic from the ```libre-informatique/core-bundle``` (the trait ```Librinfo\CoreBundle\Admin\Traits\Base```)

Eg.:
```
// src/AcmeBundle/Admin/MyAdminConcrete.php
namespace Librinfo\CRMBundle\Admin;

use Librinfo\CoreBundle\Admin\Traits\Base as BaseAdmin;

class MyAdminConcrete extends MyAdmin
{
    use BaseAdmin;
}

// src/AcmeBundle/Resources/config/admin.yml
    acme.my:
        class: AcmeBundle\Admin\MyAdminConcrete
        arguments: [~, AcmeBundle\Entity\My, SonataAdminBundle:CRUD]
        tags:
            - {name: sonata.admin, manager_type: orm}
```

## Using Traits to simplify the creation of new Sonata Admin modules

### Embeddable traits

#### Base

We have already seen the ```Librinfo\CoreBundle\Admin\Traits\Base``` in the previous example. It is really simple and simply overrides the methods ```Admin::configureDatagridFilters()```, ```Admin::configureListFields()```, ```Admin::configureFormFields()``` and ```Admin::configureShowFields()``` to use the logic from ```Librinfo\CoreBundle\Admin\CoreAdmin```.

This, then, allows you not to write a line of PHP to define complex Sonata Admin forms and complete modules. You will find all the needed details in the [README.md](../../README.md) file of the ```libre-informatique/core-bundle```.

Any other trait uses this trait. So if you want to specialize your current Sonata Admin, you will not have to use the ```Base``` trait anymore (it avoids

#### Embedded

If you need to use the ```Embedded``` trait, k

#### Embedding

### Traits used directly by the ```Librinfo\CoreBundle\Admin\CoreAdmin```

#### Mapper

#### CollectionsManager





## How it deeply works
