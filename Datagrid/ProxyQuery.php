<?php

namespace Librinfo\CoreBundle\Datagrid;

use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery as BaseProxyQuery;
use Doctrine\ORM\Query;

class ProxyQuery extends BaseProxyQuery
{
    /**
     * query hints that will be added just before the query execution
     * @var array
     */
    protected $_hints = array();

     /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        // always clone the original queryBuilder
        $queryBuilder = clone $this->queryBuilder;

        // todo : check how doctrine behave, potential SQL injection here ...
        if ($this->getSortBy()) {
            $sortBy = $this->getSortBy();
            if (strpos($sortBy, '.') === false) { // add the current alias
                $sortBy = $queryBuilder->getRootAlias().'.'.$sortBy;
            }
            $queryBuilder->addOrderBy($sortBy, $this->getSortOrder());
        } else {
            $queryBuilder->resetDQLPart('orderBy');
        }

        // Use ILIKE instead of LIKE for Postgresql
        if ( 'pdo_pgsql' == $queryBuilder->getEntityManager()->getConnection()->getDriver()->getName() )
            $this->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Librinfo\CoreBundle\DoctrineExtensions\LibrinfoWalker');

        $query = $this->getFixedQueryBuilder($queryBuilder)->getQuery();

        foreach( $this->_hints as $name => $value )
            $query->setHint($name, $value);

        return $query->execute($params, $hydrationMode);
    }

    /**
     * Sets a query hint that will be used just before the query execution.
     *
     * @param string $name  The name of the hint.
     * @param mixed  $value The value of the hint.
     *
     * @return static This instance.
     */
    public function setHint($name, $value)
    {
        $this->_hints[$name] = $value;
        return $this;
    }

}

