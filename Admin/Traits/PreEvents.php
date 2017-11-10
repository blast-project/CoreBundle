<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

use Blast\Bundle\CoreBundle\Tools\Reflection\ClassAnalyzer;

trait PreEvents
{
    /**
     * function prePersist.
     *
     * @see CoreAdmin::prePersistOrUpdate()
     **/
    public function prePersist($object)
    {
        $this->prePersistOrUpdate($object, 'prePersist');
    }

    /**
     * function prePersist.
     *
     * @see CoreAdmin::prePersistOrUpdate()
     **/
    public function preUpdate($object)
    {
        $this->prePersistOrUpdate($object, 'preUpdate');
    }

    /**
     * function prePersistOrUpdate.
     *
     * Searches in every trait (as if they were kind of Doctrine Behaviors) some logical to be
     * executed during the self::prePersist() or self::preUpdate() calls
     * The logical is stored in the self::prePersist{TraitName}() method
     *
     * @param object $object (Entity)
     * @param string $method (the current called method, eg. 'preUpdate' or 'prePersist')
     *
     * @return CoreAdmin $this
     **/
    protected function prePersistOrUpdate($object, $method)
    {
        $analyzer = new ClassAnalyzer();
        foreach ($analyzer->getTraits($this) as $traitname) {
            $rc = new \ReflectionClass($traitname);
            if (method_exists($this, $exec = $method . $rc->getShortName())) {
                $this->$exec($object);
            } // executes $this->prePersistMyTrait() or $this->preUpdateMyTrait() method
        }

        return $this;
    }
}
