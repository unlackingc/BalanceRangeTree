<?php
/**
 * Created by PhpStorm.
 * User: houyx
 * Date: 2016/9/8
 * Time: 13:52
 */

require_once __DIR__ . '/Redis.php';

class YNode
{
    public $pre;
    public $next;

    public $selfInXLeft;
    public $selfInXRight;

    public $y;

}

class YRootNode
{
    public $uid;


    public $leftChild;
    public $rightChild;
    public $height;

    public $parent;
    public $isLeft;
    public $pre;
    public $next;

    public $selfInXLeft;
    public $selfInXRight;

    public $y;
}