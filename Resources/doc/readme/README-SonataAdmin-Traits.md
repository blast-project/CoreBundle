# Improving the [SonataAdminBundle](https://github.com/sonata-project/SonataAdminBundle) in a generic way

## Why are we doing this ?

Out-of-the-box, Sonata Admin modules are not capable of dealing with foreign references in ManyToOne / OneToMany and ManyToMany relations. Then we tried to design a simple way to use and deploy a Sonata Admin module (especially its FormMapper), minimizing the things to do, the code to write...

## Using the ```libre-informatique/core-bundle``` features

#### Make your ```*Admin``` extend the ```Blast\Bundle\CoreBundle\Admin\CoreAdmin```

When you want to create a Sonata module for an existing entity, you usually start from the command line executing :

```
app/console sonata:admin:generate
```

Then you create/modify/validate your new service in your ```src/AcmeBundle/Resources/config/admin.yml```. At this point, things are working as a Sonata Admin module "Vanilla".

To be able to use the logic provided by the ```libre-informatique/core-bundle```, you need to deviate inheritance tree of your Sonata Admin. So you will change your ```src/AcmeBundle/Admin/MyAdmin.php``` that way:

```
    // src/AcmeBundle/Admin/MyAdmin.php
    // ...
    use Blast\Bundle\CoreBundle\Admin\CoreAdmin;
    
    class MyAdmin extends CoreAdmin
    {
        // ...
    }
```

Your ```MyAdmin``` is now a ```Blast\Bundle\CoreBundle\Admin\CoreAdmin``` before being a ```Sonata\AdminBundle\Admin\Admin``` (the ```CoreAdmin``` extends it). Things are getting serious, it sounds good.

### Define your module without writing a line of PHP

You have to create a class that :
1. extends your ```*Admin``` (eg. ```MyAdmin```)
2. is registered in the ```Sonata``` service instead of your ```*Admin``` (eg. ```MyAdmin```)
3. uses more logic from the ```libre-informatique/core-bundle``` (the trait ```Blast\Bundle\CoreBundle\Admin\Traits\Base```)

Eg.:
```
// src/AcmeBundle/Admin/MyAdminConcrete.php
namespace AcmeBundle\Admin;

use Blast\Bundle\CoreBundle\Admin\Traits\Base as BaseAdmin;

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

We have already seen the ```Blast\Bundle\CoreBundle\Admin\Traits\Base``` in the previous example. It is really simple and simply overrides the methods ```Admin::configureDatagridFilters()```, ```Admin::configureListFields()```, ```Admin::configureFormFields()``` and ```Admin::configureShowFields()``` to use the logic from ```Blast\Bundle\CoreBundle\Admin\CoreAdmin```.

This, then, allows you not to write a line of PHP to define complex Sonata Admin forms and complete modules. You will find all the needed details in the [README.md](../../README.md) file of the ```libre-informatique/core-bundle```.

Any other trait uses this trait. So if you want to specialize your current Sonata Admin, you will not have to use the ```Base``` trait anymore (it prevents most of the possible conflicts, avoiding the need of resolutions).

#### EmbeddedAdmin

The ```Blast\Bundle\CoreBundle\Admin\Traits\EmbeddedAdmin``` trait is to be used when your ```CoreAdmin``` is embedded within a ```sonata_type_collection``` form type (at least in its FormMapper/ShowMapper mode).

```Embedded``` is the exact mirror of ```Embedding``` (which is being treated in the next title) and aims to be used as a twin of ```Embedding```.

This is done in YAML using :

```
parameters:
    librinfo:
        // ...
        AcmeBundle\Entity\My:
            // ...
            Sonata\AdminBundle\Form\FormMapper:
                add:
                    MyTab:
                        MyGroup:
                            children:
                                type: sonata_type_collection
                                by_reference: false             # required
                                type_options:
                                    required: false
                                    btn_add: false
                                required: false
                                label: false
                                _options:
                                    edit: inline                # required
                                    #inline: table
                                    allow_delete: true
        // ...
```

1. Simply use it in your ```CoreAdmin```:

```
// src/AcmeBundle/Admin/ChildAdminConcrete.php
namespace AcmeBundle\Admin;

use Blast\Bundle\CoreBundle\Admin\Traits\Embedded;

class ChildAdminConcrete extends ChildAdmin
{
    use Embedded;
}
```

The ```libre-informatique/core-bundle``` will take care of everything for you excepting :

2. Add some logic in your *parent* entity :

```
// src/AcmeBundle/Entity/My.php
namespace AcmeBundle\Entity;

class My
{
    // ...
    /*
     * @var Collection
     */
    private $children;
    
    /**
     * @param Child $children
     * @return self
     */
    public function addChild(Child $child)
    {
        $rc = new \ReflectionClass($this);
        $child->setMy($this);
        $this->children->add($child);
        return $this;
    }
    
    /**
     * @param Child $children
     * @return self
     */
    public function removeChild(Child $child)
    {
        $this->children->removeElement($child);
        return $this;
    }
        
    /**
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
    // ...
}
```

Eventually, if many entities are using this embedded Admin (meaning that many entities have children), you can think of writing a trait with this logic, which will allow you to write things about this trait in your ```librinfo.yml```... preventing many descriptions of the same ```FormMapper```.

#### HandlesRelationsAdmin

The ```Blast\Bundle\CoreBundle\Admin\Traits\HandlesRelationsAdmin``` trait is to be used when your ```CoreAdmin``` embeds other ```CoreAdmin``` using ```sonata_type_collection``` form types (at least in its FormMapper/ShowMapper mode) or when it has many-to-many related collections.

In fact, ```Embedding``` is the exact mirror of ```Embedded``` and aims to be used as a twin of ```Embedded```.

It subscribes all the ```sonata_type_collection``` to the ```Blast\Bundle\CoreBundle\Admin\Trait\CollectionsManager::managedCollections```, avoiding the registration of collections in the [```librinfo.yml``` definition](../../README.md#configuring-your-sonataadminbundle-interfaces-with-yaml-properties).

It also finds all the many-to-many relationships in your form form. It takes care or deleting linked entities when the Admin entity is on the inverse side of the many-to-many relationship.

### How to create your own traits

1. Create a trait file in your ```MyBundle/Admin/Traits/``` directory
2. Write the needed PHP code to implement your logic, for instance in the ```CoreAdmin::configureFormFields()``` method
3. If some logic needs to be executed within the ```CoreAdmin::prePersist()``` or ```CoreAdmin::preUpdate()``` calls, create methods that fit the convention: ```[MyTrait]::pre[Persist|Update][MyTrait]()``` (replace ```[text]``` to fit your needs).
4. Optionally, your trait can use ```Blast\Bundle\CoreBundle\Admin\Traits\Base``` or others, if this is correct to do so.
5. Use your trait (maybe in addition with others) in your ```*AdminConcrete``` class.

### Traits used directly by the ```Blast\Bundle\CoreBundle\Admin\CoreAdmin```

Some traits are here only to make the ```Blast\Bundle\CoreBundle\Admin\CoreAdmin``` more readable, and consistant.

#### Mapper

The ```Blast\Bundle\CoreBundle\Admin\Traits\Mapper``` trait embeds all the logic that parses the ```librinfo.yml``` files and generate a matching ```Sonata\AdminBundle\Admin\Admin``` without writing a line of PHP.

#### CollectionsManager

The ```Blast\Bundle\CoreBundle\Admin\Traits\CollectionsManager``` trait treats the collections that would be left untouched after a change in an embedded form (a ```sonata_type_collection``` form type). It uses definitions found in the ```librinfo.yml``` files.

eg.:
```
# app/config/config.yml
parameters:
    librinfo:
        AcmeBundle\Admin\MyAdmin:
            managedCollections: [children]
```

#### ManyToManyManager

The ```Blast\Bundle\CoreBundle\Admin\Traits\ManyToManyManager``` trait removes the many-to-many links that would be left untouched after a change in the related collection widget. 
It is not necessary to use it when the Admin entity is on the owning side of the many-to-many relationship.
It uses definitions found in the ```librinfo.yml``` files with the managedManyToMany keyword.

eg.:
```
# app/config/config.yml
parameters:
    librinfo:
        AcmeBundle\Admin\Worker:
            managedManyToMany: [task, tool]
```

If you want your Admin to handle those many-to-many relationships automatically, use the ```HandlesRelationsAdmin``` trait.

#### PreEvents

The ```Blast\Bundle\CoreBundle\Admin\Traits\PreEvents``` trait embeds the ```Sonata\AdminBundle\Admin\Admin::preUpdate($object)``` and ```Sonata\AdminBundle\Admin\Admin::prePersist($object)``` methods. It comes with the ability to define new "behaviors" in traits. When called, those methods try to execute every methods componed as:

```
[MyTrait]::[prePersist|preUpdate][MyTrait]($object)
```
