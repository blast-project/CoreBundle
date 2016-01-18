Creating new field types
=========================

To create a new field type, you need :

1. to use this type in your Sonata Admin ```librinfo.yml``` definitions
2. defining this field type in your ```librinfo.yml``` file
3. (if necessary) creating a new Twig template
4. test it

e.g.:

1. Use the new field type
-------------------------

Let's define the ```email``` field type, which by-the-way is already defined in this bundle :

```yaml
# Resources/config/librinfo.yml
parameters:
    librinfo:
        Librinfo\DoctrineBundle\Entity\Traits\Emailable:
            Sonata\AdminBundle\Datagrid\ListMapper:
                _copy: Sonata\AdminBundle\Datagrid\DatagridMapper
            Sonata\AdminBundle\Datagrid\DatagridMapper:
                add:
                    email:
                        type: email
            Sonata\AdminBundle\Show\ShowMapper:
                _copy: Sonata\AdminBundle\Form\FormMapper
            Sonata\AdminBundle\Form\FormMapper:
                add:
                    General:
                        '':
                            email:
                                type: email
                                required: false
```

2. Define the new field type
----------------------------

```yaml
# Resources/config/librinfo.yml
parameters:
    librinfo:
        configuration:
            templates:
                show:
                    email: LibrinfoCoreBundle:CRUD:list_field_email.html.twig
                list:
                    email: LibrinfoCoreBundle:CRUD:list_field_email.html.twig
```

You can that the definition of a new field type is set within the ```configuration``` librinfo parameter, using the ```templates``` keyword. Then the subkey is set by the action it refers to (```show``` or ```list```), and it is componed by pairs of ```type``` → ```template``` (written in the standard Symfony notation).

3. Create a template (if needed) for it
---------------------------------------

In ```Resources/views/CRUD/list_field_email.html.twig```:

```twig
{#

This file is part of the Sonata package.

(c) Baptiste SIMON <beta@e-glop.net>
(c) Libre Informatique [http://www.libre-informatique.fr/]

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

#}

{% extends 'SonataAdminBundle:CRUD:base_show_field.html.twig' %}

{% block field %}
<a href="mailto:{{ value }}">{{ value }}</a>
{% endblock %}
```

4. Test it
----------

Go to your dashboard, find your list/show action, and verify that it's working...

[Back to README](../../README.md)
