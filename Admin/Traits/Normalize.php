<?php

namespace Librinfo\CoreBundle\Admin\Traits;

trait Normalize
{
    public function prePersistNormalize($object)
    {
        $this->normalizeFields($object);
    }

    public function preUpdateNormalize($object)
    {
        $this->normalizeFields($object);
    }

    /**
     * @param Object $object
     * @return Array
     */
    public function normalizeFields($object)
    {
        $param = $this->configParameter;
        $rc = $function = new \ReflectionClass($this->getClass());
        $class = $rc->getShortName();
        $config = $this->getConfigurationPool()->getContainer()->getParameter($param)[$class]['normalize'];
        $normalized = [];

        foreach($config as $field => $actions) {
            $normalized[$field] = $this->normalizeField($field, $actions);
            $setter = 'set'.ucfirst($field);
            if ($object && method_exists($object, $setter))
                $object->$setter($normalized[$field]);
        }

        return $normalized;
    }

    /**
     * @param string $field
     * @param array $actions (uppercase, titlecase...)
     * @return string|null
     */
    public function normalizeField($field, $actions)
    {
        if ( !$this->getForm()->has($field) )
            return null;
        $data = $this->getForm()->get($field)->getNormData();

        foreach($actions as $action)
        {
            switch ($action) {
                case 'uppercase':
                case 'upper':
                    $data = mb_strtoupper($data);
                    break;
                case 'titlecase':
                case 'title':
                    $data = mb_convert_case($data, MB_CASE_TITLE);
                    break;
            }
        }

        return $data;
    }
}

