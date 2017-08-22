Managing entities dashboard groups
==================================

Add in your ```config.yml``` this configuration :

```yml
# app/config/config.yml
sonata_admin:
    dashboard:
        groups:
            Customers Relationship Management: []
            Application Management: []
```

With that configuration, you can hide the default User and Group dashboard groups.

You can now add your entities in these groups by defining services as below :

```yml
# yourBundle/Resources/config/services.yml
services:
    app.admin.user:
        class: Librinfo\UserBundle\Admin\UserAdminConcrete
        arguments: [~, Librinfo\UserBundle\Entity\User, SonataAdminBundle:CRUD]
        tags:
            - {name: sonata.admin, manager_type: orm, group: Application Management, label: Users}
```

The User class will be available for managing inside menu under the group named ```Application Management```.
Feel free to create and compose your groups as you want

Using Blast Dashboard
=====================

Enable Blast Dashboard
----------------------

Import blast core config file in your application config file :
```yml
# app/config/config.yml

imports:
    # [ ... ]
    - { resource: "@BlastCoreBundle/Resources/config/config.yml" }
    # [ ... ]

    # Your configuration below
```
This configuration file sets sonata_blocks and sonata_admin.dashboard base block

Create your dashboard block class
---------------------------------

Your class must/should extends class `Blast\CoreBundle\Dashboard\AbstractDashboardBlock`

```php
<?php

namespace MyBundle\Dashboard;

use Blast\CoreBundle\Dashboard\AbstractDashboardBlock;

class MyBundleDashboardBlock extends AbstractDashboardBlock
{
    public function handleParameters()
    {
        $this->templateParameters = [
            'myParameter'=> 'MyValue',
        ];
    }
}
```

Define your class as a service
------------------------------

```yml
services:
    my_bundle.dashboard.main:
        parent: blast_core.dashboard.block
        class: MyBundle\Dashboard\MyBundleDashboardBlock
        arguments:
            - 'MyBundle:Dashboard:my_dashboard.html.twig'
        tags:
            - { name: blast.dashboard_block }
```

Create your block template
--------------------------

```twig
<strong>My Block</strong>
{{ myParameter }}
```

And Voila !
