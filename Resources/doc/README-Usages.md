Building your Sonata Admin module within a YAML file
====================================================

This feature is provided by Blast\CoreBundle to ease the deployment of new bundles & entities. It works quite closely to the  [Sonata\AdminBundle\MapperBaseMapper](https://github.com/sonata-project/SonataAdminBundle/blob/master/Mapper/BaseMapper.php) & [Sonata\AdminBundle\MapperBaseGroupedMapper](https://github.com/sonata-project/SonataAdminBundle/blob/master/Mapper/BaseGroupedMapper.php) (and their children like [Sonata\AdminBundle\Form\FormMapper](https://github.com/sonata-project/SonataAdminBundle/blob/master/Form/FormMapper.php)) ```add()```, ```with()```/```tab()``` & ```remove()``` methods.

The YAML reference
------------------

So it is structured as :

```yaml
# app/config/config.yml (or any other file that is loaded by your bundle)
parameters:
    blast:
        configuration: #global configuration
            search: #list of admin services that will be available in the global search box (they will appear in the same order)
                - librinfo_crm.admin.contact
                - librinfo_crm.admin.organism
                - librinfo_email.admin.email
        AcmeBundle\Admin\DemoAdmin:               # The Admin class extension
            manage:
                collections: []                   # Array of collections that need to be managed, in relation with the embeded objects (e.g. House::$doors -> [doors])
                manyToMany: []                    # Array of many-to-many relations that need to be managed, to perform correct updates both on the ownerSide and on the inverseSide
                                                  # ↘ An other way to do the same things automagically is to use the trait Blast\CoreBundle\Admin\Traits\HandlesRelationsAdmin within your
                                                  # Sonata Admin form instead of the Blast\CoreBundle\Admin\Traits\Base
            Sonata\AdminBundle\Form\FormMapper:   # The class of objects that needs to be configured (here the edit/create form)
                title: this is the form view title 
                titleTemplate: 'myBundle:Dir:myEditTemplate.html.twig'
                remove:                           # The fields that need to be removed from inheritance (array)
                    - name: ~
                    - createdAt: ~
                add:                              # What we want to display (associative array)
                    text:                         # The name of a field that needs to be directly injected (without any tab)
                        type: textarea            # The type of field to display
                        required: false           # Other options refering to the BaseMapper super-class used
                        only_new: true            # This field will not be used in edit forms
                                                  # Alternatively you can set only_not_new: true to remove the field from create forms
                    gfx_tab:                      # A first tab
                        _options:                 # ... with its options (cf. BaseGroupedMapper::with() options)
                            description: tab
                            groupsOrder: [gfx_group2, gfx_group]
                            hideTitle: false      # remove graphically the title of the tab (false by default)
                        gfx_group:                # A first group inside the "tab"
                            _options:             # ... with its options (cf. BaseGroupedMapper::with() options)
                                description: with
                                fieldsOrder:      # You can defined fields order in this key.
                                    - text
                                    - otherField
                                hideTitle: false  # remove graphically the title of the group (false by default)
                            title: ~              # Adding a field, with no option
                            description:
                                type: textarea
                                label: Descriiiiiiption
                                _options:         # Extra options ("fieldDescriptionOptions" in the BaseMapper::add super-class)
                                    translation_domain: fr
                        gfx_group2:
                            field2: ~
                    _options:
                        tabsOrder: []
            Sonata\AdminBundle\Show\ShowMapper:   # The class of objects that needs to be configured (here the "show" view)
                title: this is the show view title 
                titleTemplate: 'myBundle:Dir:myShowTemplate.html.twig'
                _copy: Sonata\AdminBundle\Form\FormMapper # indicates to take the configuration of an other class of the current Admin class extension (including its parents configuration)
                remove: [field2]
                add:
                    gfx_tab:
                        gfx_group2:
                            field2:
                                template: LibrinfoCRMBundle:CRUD:field_subobject.html.twig # this allows you
                                                # to display this field using a specific template of your own.
            Sonata\AdminBundle\Datagrid\DatagridMapper:   # The class of objects that needs to be configured (here the "filters")
                add:
                    _options:
                        orderFields: [title, name]
                    name: ~
                    title:
                        type: XXX
                        filterOption1: xxx
                        filterOption2: yyy
                        field_options:
                            fieldOption1: value1
                            fieldOption2: value2
                        fieldDescriptionOptions1: aaa
                        fieldDescriptionOptions2: bbb
                        field_type: fieldType
                        #_option: fieldType # can replace "field_type"
            Sonata\AdminBundle\Datagrid\ListMapper:   # The class of objects that needs to be configured (here the "list" view)
                title: this is the list view title 
                titleTemplate: 'myBundle:Dir:myListTemplate.html.twig'
                remove:
                createdAt: ~                          #removing a simple field
                    _batch_actions: [delete]          # removes bacth actions (bottom of the list view)
                    _list_actions: [create]           # removes list actions (generic actions in top bar of the list view)
                    _export_formats: ~                # resets the export formats as it was before using any customized CoreAdmin
                add:
                    name: ~
                    _actions:
                        show: ~
                        edit: ~
                        delete: ~
                        myAction:
                            template: AcmeBundle:list__action_myaction.html.twig 
                            route: myaction # defaults to action name if not set
                    _batch_actions:         # batch actions
                        merge:
                            action: myAction # points to a custom action previously defined in _actions key
                            label: merge    # optional, used for translation. if not specified the label is built on "batch_action_[merge]"
                            translation_domain: LibrinfoCRMBundle
                                            # optional
                            ask_confirmation:   false
                                            # true by default, and then ask for a user confirmation
                    _export_format:         # exportable formats
                        csv: [name, url]    # adds the CSV format, with fields name & url (and overwrites the previous configurations!!)
                        xml: [id, name, url]# adds the XML format, with fields id, name & url
                        json: ~             # adds the json format, with default fields
                        xls: csv            # adds the XLS format, using the CSV fields (copying)
                        -pdf: ~             # removes the PDF format (unavailable by default)
                        vcard: [name, positions.label, positions.organism.name]
                                            # if a "vcard" format is defined in your custom Exporter, you can use it here
                                            # if this format support multi-dimensional data, you can build 2 levels data
                                            #    using collections ; then [collection].name will iterate on each element
                                            #    to get back its name an produce a 2 dimension array
                    _list_action: # add custom action to generic top bar actions in list view
                        test:
                            action: create      # can be an action or a route
                            translation_domain: LibrinfoCRMBundle # if no translation_domain is defined, then the label will not be translated
                            label: test_test    # if no label is specified, the name (key) of the action is used instead
                        pouet:
                            route: admin_librinfo_crm_contact_list # can be a route or an action
                            params: []          # parameters to be used when calling the route
                            translation_domain: LibrinfoCRMBundle
                        glop:
                            route: admin_librinfo_crm_contact_list
                            label: Glop         # no translation_domain given, then the label is used without any translation

```

Playing with your Sonata Admin modules, Entity inheritance, traits...
---------------------------------------------------------------------

The use of your librinfo.yml configuration files is done in a specific order, allowing inheritance, properties sharing, etc. It starts from a working CRUD generated by Sonata, extending the ```Blast\CoreBundle\Admin\CoreAdmin``` and extended by a ```MyEntityAdminConcrete``` as explained in the next title. Then the ```libre-informatique/core-bundle``` works this way:

1. All the ```librinfo.yml``` files are loaded, across your bundles, in the order of their definition in your ```app/AppKernel.php```.
2. The system starts from the traits used in your entity (directly or indirectly through inheritance), reading the properties that has to be applied on them
3. Then it reads the properties defined for the tree of inheritance of your entity, starting by the *oldest ancestor* to finish with the ending child (→ your entity itself)
4. And to finish, it processes the properties defined for the tree of inheritance of your current Sonata Admin, from *oldest ancestor* to the ending child (→ your Admin itself, eg. ```MyEntityAdminConcrete```)

And this builds up a configured Sonata Admin module, without a word of PHP code, embedding reusability, scalability, performance in the development process, strength and robustness.

How to use the Blast\CoreBundle features ?
=============================================

After having installed properly the bundle, and learning the configuration reference, just use the ```Blast\CoreBundle\Admin\CoreAdmin``` as the parent class of your ```Admin/*Admin.php``` modules:

```php
<?php
// src/AcmeBundle/Admin/DemoAdmin.php
// ...
use Blast\CoreBundle\Admin\CoreAdmin;
// ...
class DemoAdmin extends CoreAdmin
{
    // ... empty everything original... and if you want to extend those methods, always call parent::METHOD(); somewhere
}
```

Then you will have to create a "Concrete" (or any other keyword) Admin :

```php
<?php
// src/AcmeBundle/Admin/DemoAdminConcrete.php
// ...
use Blast\CoreBundle\Admin\Trait\Base as BaseAdmin;
// ...
class DemoAdminConcrete extends DemoAdmin
{
    use BaseAdmin;
}
```

To finish this, register your service properly in your ```admin.yml``` file:
```yaml
services:
    acme.demo:
        class: AcmeBundle\Admin\DemoAdminConcrete
        arguments: [~, AcmeBundle\Entity\Demo, SonataAdminBundle:CRUD]
        tags:
            - {name: sonata.admin, manager_type: orm}
```

[Back to the README file](../../README.md)
