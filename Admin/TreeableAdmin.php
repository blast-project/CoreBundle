<?php

namespace Librinfo\CoreBundle\Admin;

abstract class TreeableAdmin extends CoreAdmin
{
    /**
     * {@inheritdoc}
     */
    public function create($object)
    {

        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();

        $this->prePersist($object);
        foreach ($this->extensions as $extension)
            $extension->prePersist($this, $object);

        $object->setMaterializedPath('');
        $em->persist($object);


        if ($object->getParentNode() !== null)
            $object->setChildNodeOf($object->getParentNode());
        else
            $object->setParentNode(null);

        $object->setSortMaterializedPath($object->getRealMaterializedPath());

        $this->postPersist($object);
        foreach ($this->extensions as $extension)
            $extension->postPersist($this, $object);

        $em->flush();
        $this->createObjectSecurity($object);

        return $object;
    }
}

