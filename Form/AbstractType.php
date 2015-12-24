<?php

namespace Librinfo\CoreBundle\Form;

use Symfony\Component\Form\AbstractType as SymfonyAbstractType;

class AbstractType extends SymfonyAbstractType
{
    /**
     * {@inheritdoc}
     *
     * @todo Remove when dropping Symfony <2.8 support
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
    
    /**
     * getBlockPrefix
     *
     * When $this is a AppBundle\Form\Type\MyType, it returns app_my
     * When $this is a Librinfo\AppBundle\Form\Type\MyType, it returns librinfo_app_my
     *
     * @return  string
     */
    public function getBlockPrefix()
    {
        $rc = new \ReflectionClass($this);
        
        // Non-greedy ("+?") to match "type" suffix, if present
        $fqcn = preg_replace(
            array('/([^\\\\])(Bundle)?\\\\Form\\\\Type(\\\\[^\\\\]+?)(Type)?$/i', '/\\\\/'),
            array('\\1\\3', '_'),
            $rc->getName()
        );
        return strtolower($fqcn);
    }
}
