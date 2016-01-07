<?php

namespace Librinfo\CoreBundle\DoctrineExtensions;

use Doctrine\ORM\Query\SqlWalker;

class LibrinfoWalker extends SqlWalker
{
    /**
     * Walks down a SelectClause AST node, thereby generating the appropriate SQL.
     *
     * @param $selectClause
     * @return string The SQL.
     */
    public function walkSelectClause($selectClause)
    {
        $sql = parent::walkSelectClause($selectClause);

        if ($this->getQuery()->getHint('librinfoWalker.noIlike') === false)
            $sql = str_replace(' LIKE ', ' ILIKE ', $sql);

        return $sql;
    }

    /**
     * Walks down a WhereClause AST node, thereby generating the appropriate SQL.
     *
     * @param $whereClause
     * @return string The SQL.
     */
    public function walkWhereClause($whereClause)
    {
        $sql = parent::walkWhereClause($whereClause);

        if ($this->getQuery()->getHint('librinfoWalker.noIlike') === false)
            $sql = str_replace(' LIKE ', ' ILIKE ', $sql);

        return $sql;
    }
}
