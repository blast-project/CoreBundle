Managing entities dashboard groups
==================================

Add in your ```config.yml``` this configuration :

```
# app/config/config.yml
sonata_admin:
    dashboard:
        groups:
            Customers Relationship Management: []
            Application Management: []
```

With that configuration, you can hide the default User and Group dashboard groups.

You can now add your entities in these groups by defining services as below :

```
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
