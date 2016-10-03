<?php

namespace Librinfo\CoreBundle\Twig\Extensions;  

use Html2Text\Html2Text;

class Html2textExtension extends \Twig_Extension 
{
    public function getName()
    {
        return 'html2text';
    }
    
    public function getFilters()
    {
       return [new \Twig_SimpleFilter('html2text', array($this, 'transform'))];
    }

    public function transform($value)
    {
        $html2T = new Html2Text($value);
        
        return $html2T->getText();
    }
}