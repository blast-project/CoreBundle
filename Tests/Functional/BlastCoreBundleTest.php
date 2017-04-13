<?php


namespace Blast\CoreBundle\Tests\Functional;

#use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;


class BlastCoreBundleTest extends KernelTestCase
{

    protected $container;
    
    protected function setUp()
    {
        static::bootKernel();
        
        /** @var Container $container */
        $this->container = self::$kernel->getContainer();
    }

    public function testServicesAreInitializable()
    {

        $serviceIds = array_filter($this->container->getServiceIds(), function ($serviceId) {
            return 0 === strpos($serviceId, 'blast_core.');
        });

        foreach ($serviceIds as $serviceId) {
            $this->assertNotNull($this->container->get($serviceId));
        }

        $this->assertContains('blast_core.form.type.entity_code',$serviceIds);
    }


      public function testServicesExists()
      {

          /*
           * @Todo is it usefull to test if service exist or not
          */
          
          $this->assertContains('blast_core.code_generator_factory',$this->container->getServiceIds());
          $this->assertContains('blast_core.code_generators',$this->container->getServiceIds());
          $this->assertContains('blast_core.form.type.entity_code',$this->container->getServiceIds());
          $this->assertContains('blast_core.label.strategy.librinfo',$this->container->getServiceIds());
              

      }
}
