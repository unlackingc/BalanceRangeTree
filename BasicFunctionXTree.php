<?php
/**
 * Created by PhpStorm.
 * User: houyx
 * Date: 2016/9/19
 * Time: 21:25
 */

require_once __DIR__ . '/XNode.php';
require_once __DIR__ . '/Basic.php';

/**
 * 要保证null节点在redis中的height为0
 * @param XNode $g
 * @return bool
 */
function avlBalanced( XNode $g )
{
    $leftChild = new XNode($g->leftChild);
    $rightChild = new XNode($g->rightChild);
    if( ($leftChild->height - $rightChild->height < 2) && ($leftChild->height - $rightChild->height > -2) )
    {
        return true;
    }
    else
    {
        return false;
    }
}

/*
#define HeightUpdated(x) 高度更新常规条件
        ((x).height == 1 + max(stature((x).lChild), stature((x).rChild)))
#define Balanced(x) (stature((x).lChild) == stature((x).rChild)) //理想平衡条件
#define BalFac(x) (stature((x).lChild) - stature((x).rChild)) //平衡因子
#define AvlBalanced(x) ((-2 < BalFac(x)) && (BalFac(x) < 2)) //AVL平衡条件
*/

function tallerChild( XNode $g )
{
    return $g;
}

function rotateAt( XNode $g )
{
    return $g;
}

function updateHeight($g)
{

}