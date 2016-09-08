<?php

require_once __DIR__ . '/XTree.php';
require_once __DIR__ . '/Basic.php';

class BalanceRangeTree
{
    public $xTree;

    public function __construct()
    {
        $this->xTree = new XTree();
    }

    public function insert( Point $newPoint )
    {
        return $this->xTree->insert($newPoint);
    }

    public function remove( $pointUid )
    {
        return $this->xTree->remove( $pointUid );
    }

    public function range( Range $range )
    {
        return $this->xTree->range( $range );
    }

}