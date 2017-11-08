Configuring a standalone bundle
===============================

If you want a standalone bundle, eventually published for composer, and deployed in your vendor directory, you can incorporate the configuration of your ```DemoAdmin``` component within the bundle. Here is an example taken from the [libre-informatique/crm-bundle](https://github.com/libre-informatique/SymfonySilCRMBundle) :

```php
<?php
// vendor/libre-informatique/crm-bundle/DependencyInjection/LibrinfoCRMExtension.php
namespace Sil\Bundle\CRMBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Blast\Bundle\CoreBundle\DependencyInjection\LibrinfoCoreExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LibrinfoCRMExtension extends LibrinfoCoreExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('admin.yml');
        
        $this->mergeParameter('blast', $container, __DIR__.'/../Resources/config');
    }
}
```

Then create a ```Resources/config/blast.yml``` file in your bundle, matching the previous specifications.

You'll notice the ```use Blast\Bundle\CoreBundle\DependencyInjection\LibrinfoCoreExtension;```, the ```class LibrinfoCRMExtension extends LibrinfoCoreExtension``` and the ```$this->mergeParameter('blast', $container, __DIR__.'/../Resources/config');``` that loads the new configuration file, overloading the configuration of the parent bundle (here, ```Blast\Bundle\CoreBundle```).

#### Keeping the original/default SonataAdmin configuration (fields)

In fact when you generate an ```Admin``` component, it comes with the full list of fields representing the object you want to create/edit/list/show... So if you want, for any reason, to keep this configuration available, the best practice with ```Blast\Bundle\CoreBundle``` is to extend this ```Admin``` component as: ```DemoAdmin``` -> ```DemoAdminConcrete```.

```php
<?php
// src/AcmeBundle/Admin/DemoAdminConcrete.php
namespace AcmeBundle\Admin;

use Blast\Bundle\CoreBundle\Admin\Traits\Base as BaseAdmin;

class DemoAdminConcrete extends DemoAdmin
{
    use BaseAdmin;
}
```

Then to use your ```*AdminConcrete``` class, simply change your ```services``` file as:

```
# app/config/services.yml
services:
# ...
    app.admin.demo:
        class: AcmeBundle\Admin\DemoAdminConcrete
        # ...
```
