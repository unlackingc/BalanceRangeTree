<?php
/**
 * Created by PhpStorm.
 * User: houyx
 * Date: 2016/9/8
 * Time: 13:52
 */

require_once __DIR__ . '/XNode.php';
require_once __DIR__ . '/Basic.php';
require_once __DIR__ . '/BasicFunctionXTree.php';

class XTree
{
    public $root;

    private function _searchIn( XNode $v, Point &$point, XNode &$_hot  )
    {
        if( $point->x == $v->x )
        {
            return $v;
        }

        $_hot = $v;
        return $this->_searchIn( (($point->x < $v->x)? new XNode($v->leftChild) : new XNode($v->rightChild) ), $point, $_hot );
    }

    private function _search( Point $point, XNode &$_hot )
    {
        return $this->_searchIn( new XNode($this->root), $point, $_hot );
    }

    /**
     * 先调整x树的结构，再插入Y仍然无法避免对y树其他节点的调节和重构
     * @param Point $point
     * @return void|XNode
     */
    public function insert( Point $point )
    {
        $_hot = new XNode("");//父节点的uid
        $x =  $this->_search( $point, $_hot );
        if( $x != null )
        {
            return $x;
        }

        $xNode = new XNode($point->uid);
        $xNode->init($point, $_hot);


        //reBalance xTree
        for( $g = $_hot; $g != null; $g = $g->parent  )
        {
            if( avlBalanced($g) == false )
            {
                FromParentTo($g) = rotateAt(tallerChild(tallerChild($g)));
            }
            else
            {
                updateHeight($g);
            }
        }


        //insert y

        return $xNode;
    }
    /*
    template <typename T> BinNodePosi(T) AVL<T>::insert(const T& e) { //将关键码e插入AVL树中
   BinNodePosi(T) & x = search(e); if (x) return x; //确认目标节点不存在（留意对_hot的设置）
   x = new BinNode<T>(e, _hot); _size++; //创建节点x（此后，其父_hot可能增高，祖父可能失衡）
   for (BinNodePosi(T) g = _hot; g; g = g->parent) { //从x之父出发向上，逐层检查各代祖先g
      if (!AvlBalanced(*g)) { //一旦发现g失衡，则（采用“3 + 4”算法）使之复衡
         FromParentTo(*g) = rotateAt(tallerChild(tallerChild(g))); //将该子树联至原父亲
         break; //g复衡后，局部子树高度必然复原；其祖先亦必如此，故调整随即结束
      } else //否则（g依然平衡），只需简单地
         updateHeight(g); //更新其高度（注意：即便g未失衡，高度亦可能增加）
   } //至多只需一次调整；若果真做过调整，则全树高度必然复原
   return x; //返回新节点
} //无论e是否存在于原树中，返回时总有x->data == e

     * */


    public function remove()
    {

    }

    public function range()
    {

    }
}