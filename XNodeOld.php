<?php
/**
 * Created by PhpStorm.
 * User: houyx
 * Date: 2016/9/8
 * Time: 13:52
 */

require_once __DIR__ . '/Redis.php';


class XNodeOld
{
    public $uid;


    public $leftChild;
    public $rightChild;
    public $height;
    public $Ty;

    public $parent;
    public $isLeft;

    public $x;


    public function __construct( $uid )
    {
        $this -> height = 0;
        $this -> uid = $uid;
    }

    public function init( Point $point, & $_hot )
    {
        $this->uid = $point->uid;
        $this->x = $point->x;
        $this->parent = $_hot;

        $_hotNode = new XNode( $_hot );
        $_hotNode->read();

        if( $point->x < $_hotNode->x )
        {
            $this->isLeft = true;
            $_hotNode->leftChild = $point->uid;
        }
        else
        {
            $this->isLeft = false;
            $_hotNode->rightChild = $point->uid;
        }

        $this->write();
        $_hotNode->write();
    }

    public function read()
    {
        if( $GLOBALS['redis'] instanceof \Redis)
        {
            $temp = $GLOBALS['redis']->hGetAll( $this->uid );

            $this->leftChild = $temp["leftChild"];
            $this->rightChild = $temp["rightChild"];
            $this->height = $temp["height"];
            $this->Ty = $temp["Ty"];

            $this->parent = $temp["parent"];
            $this->isLeft = $temp["isLeft"];

            $this->x = $temp["x"];

        }

    }

    public function write()
    {
        $temp = array(
            "leftChild" => $this->leftChild,
            "rightChild" => $this->rightChild,
            "height" => $this->height,
            "Ty" => $this->Ty,

            "parent" => $this->parent,
            "isLeft" => $this->isLeft,
            "x" => $this->x,
        );

        if( $GLOBALS['redis'] instanceof \Redis)
        {
            $GLOBALS['redis']->hMset( $this->uid, $temp );
        }
    }
}