<?php
/**
 * Created by PhpStorm.
 * User: houyx
 * Date: 2016/9/8
 * Time: 13:52
 */

require_once __DIR__ . '/Redis.php';

class XNode
{
    public $uid;

        //本段代码由于激活自动补全，需在运行时删除
        public $leftChild;
        public $rightChild;
        public $height;
        public $Ty;

        public $parent;
        public $isLeft;

        public $x;

    public function __construct( $uid )
    {
        $this -> uid = $uid;
    }

    /**
     * tested
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        /*if( $GLOBALS['redis'] instanceof \Redis)
        {
        echo "name:\t".$name."\tvalue:\t".$value."\n\r";*/
        $GLOBALS['redis']->hSet( $this->uid, $name, $value );
         /*}*/
    }

    /**
     * tested
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        /*        if( $GLOBALS['redis'] instanceof \Redis)
                {*/
        return $GLOBALS['redis']->hGet( $this->uid, $name );
        /*      }
        */
    }


    /**
     * 根据父节点和给定数据生成并插入子节点
     * @param Point $point
     * @param XNode $_hot
     */
    public function init( Point $point, XNode & $_hot )
    {
        $this->leftChild = null;
        $this->rightChild = null;
        $this->height = 0;
        $this->Ty = null;
        $this->parent = null;
        $this->isLeft = false;
        $this->x = null;

        $this->uid = $point->uid;
        $this->x = $point->x;
        $this->parent = $_hot->uid;

        if( $point->x < $_hot->x )
        {
            $this->isLeft = true;
            $_hot->leftChild = $point->uid;
        }
        else
        {
            $this->isLeft = false;
            $_hot->rightChild = $point->uid;
        }
    }


}