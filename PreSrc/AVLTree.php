<?php
/**
 * Created by PhpStorm.
 * User: unlockingc
 * Date: 16-4-14
 * Time: 上午11:44
 */
use Ramsey\Uuid\Uuid;

class Node
{
    public $nodeId;
    public $maxChildValue;
    public $minChildValue;
    public $leftChild;
    public $rightChild;
    public $parent;
    public $value;
    public $height;
    public $next;
    public $pre;

    public function __construct()
    {
        $this->nodeId = null;
        $this->maxChildValue = null;
        $this->minChildValue = null;
        $this->leftChild = null;
        $this->rightChild = null;
        $this->parent = null;
        $this->value =  null;
        $this->height = null;
        $this->next = null;
        $this->pre  =  null;
    }

    public function SetArg( $dataInArray )
    {
        $this->nodeId = $dataInArray['nodeId'];
        $this->maxChildValue = $dataInArray['maxChildValue'];
        $this->minChildValue = $dataInArray['minChildValue'];
        $this->leftChild = $dataInArray['leftChild'];
        $this->rightChild = $dataInArray['rightChild'];
        $this->parent = $dataInArray['parent'];
        $this->value = $dataInArray['value'];
        $this->height = $dataInArray['height'];
        $this->next =   $dataInArray['next'];
        $this->pre  = $dataInArray['pre'];
    }

    public function SetToRedis( $redis )
    {
        $redis->hMset($this->nodeId,
            array(  'maxChildValue'=>$this->maxChildValue,
                    'minChildValue'=>$this->minChildValue,
                    'leftChild'=>$this->leftChild,
                    'rightChild'=>$this->rightChild,
                    'parent'=>$this->parent,
                    'value'=>$this->value,
                    'height' => $this->height,
                    'next'  => $this->next,
                    'pre'   => $this->pre,
            )
        );
    }

    public function GetFromRedis( $redis,$nodeId )
    {
        $dataInArray = $redis->hGetAll($nodeId);
        $this->nodeId = $dataInArray['nodeId'];
        $this->maxChildValue = $dataInArray['maxChildValue'];
        $this->minChildValue = $dataInArray['minChildValue'];
        $this->leftChild = $dataInArray['leftChild'];
        $this->rightChild = $dataInArray['rightChild'];
        $this->parent = $dataInArray['parent'];
        $this->value = $dataInArray['value'];
        $this->height = $dataInArray['height'];
        $this->next =   $dataInArray['next'];
        $this->pre  = $dataInArray['pre'];
    }

    public function GetValueFromRedis( $redis,$nodeId )
    {
        $dataInArray = $redis->hGet( $nodeId, 'value');
        $this->nodeId = $dataInArray['nodeId'];
        $this->value = $dataInArray['value'];
    }

}


class AVLTree
{
    //redis-php在得不到返回值时返回false
    public $redis;
    public $rootName;

    public function __construct( $rootName_ = 'rootTest' )
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->rootName = $rootName_;
    }

    public function hasNode( $node )
    {
        return $this->redis->exists($node);
    }

    /**
     * @param $rootName_
     */
    public function setRootName( $rootName_ )
    {
        $this->redis->set( $rootName_, $this->redis->get($this->rootName));
        $this->rootName = $rootName_;
    }

    public function setRootId( $nodeId )
    {
        $this->redis->set( $this->rootName, $nodeId);
    }

    public function getRootId()
    {
        return $this->redis->get( $this->rootName );
    }


    /**
     * 两者等高则返回与父亲同侧者
     * @param $gId
     * @return int|string
     */
    private function _tallerChild( $gId )
    {
        $leftChild =  $this->redis->hGet($gId,'leftChild');
        $rightChild = $this->redis->hGet($gId,'rightChild');

        $leftHeight = $leftChild? $this->redis->hGet($leftChild,'height') : 0;
        $rightHeight = $rightChild? $this->redis->hGet($rightChild,'height') : 0;

        return ( ($leftHeight > $rightHeight)? $leftChild : ($leftHeight < $rightHeight)? $rightHeight: $this->_isLeftChild($gId)? $leftChild : $rightHeight  );
    }

    private function _rotateAt( $vid )
    {
        $pid = $this->redis->hGet($vid,'parent');
        $gid = $this->redis->hGet($pid,'parent');

        if( $this->_isLeftChild($pid) )
        {
            if( $this->_isLeftChild($vid) )
            {
                $this->redis->hSet($pid,'parent',$this->redis->hGet($gid,'parent'));
                return $this->_connect34( $vid,$pid,$gid, $this->redis->hGet($vid,'leftChild'),$this->redis->hGet($vid,'rightChild'),$this->redis->hGet($pid,'rightChild'),$this->redis->hGet($gid,'rightChild') );
            }
            else
            {
                $this->redis->hSet($vid,'parent',$this->redis->hGet($gid,'parent'));
                return $this->_connect34( $pid,$vid,$gid, $this->redis->hGet($pid,'leftChild'),$this->redis->hGet($vid,'leftChild'),$this->redis->hGet($vid,'rightChild'),$this->redis->hGet($gid,'rightChild') );
            }
        }
        else
        {
            if( $this->_isLeftChild($vid) )
            {
                $this->redis->hSet($vid,'parent',$this->redis->hGet($gid,'parent'));
                return $this->_connect34( $gid,$vid,$pid, $this->redis->hGet($gid,'leftChild'),$this->redis->hGet($vid,'leftChild'),$this->redis->hGet($vid,'rightChild'),$this->redis->hGet($pid,'rightChild') );
            }
            else
            {
                $this->redis->hSet($pid,'parent',$this->redis->hGet($gid,'parent'));
                return $this->_connect34( $vid,$pid,$gid, $this->redis->hGet($gid,'leftChild'),$this->redis->hGet($pid,'leftChild'),$this->redis->hGet($vid,'leftChild'),$this->redis->hGet($vid,'rightChild') );
            }
        }
    }

    private function _connect34($aId,$bId,$cId,$child0,$child1,$child2,$child3)
    {

        $this->redis->hSet($aId,'leftChild',$child0);
        if( !($child0 == null || $child0 == false) )
        {
            $this->redis->hSet($child0,'parent',$aId);
        }

        $this->redis->hSet($aId,'rightChild',$child1);
        if( !($child1 == null || $child1 == false) )
        {
            $this->redis->hSet($child1,'parent',$aId);
        }

        $this->_updateHeight($aId);
        $this->_updateMaxMin($aId);


        $this->redis->hSet($cId,'leftChild',$child2);
        if( !($child2 == null || $child2 == false) )
        {
            $this->redis->hSet($child2,'parent',$cId);
        }

        $this->redis->hSet($cId,'rightChild',$child3);
        if( !($child3 == null || $child3 == false) )
        {
            $this->redis->hSet($child3,'parent',$cId);
        }

        $this->_updateHeight($cId);
        $this->_updateMaxMin($cId);

        $this->redis->hSet($bId,'leftChild',$aId);
        $this->redis->hSet($bId,'rightChild',$cId);
        $this->redis->hSet($aId,'parent',$bId);
        $this->redis->hSet($cId,'parent',$bId);

        $this->_updateHeight($bId);
        $this->_updateMaxMin($bId);

        return $bId;
    }

    private function _isLeftChild( $gId )
    {
        $parent = $this->redis->hGet($gId,'parent');
        return ($this->redis->hGet($parent,'leftChild') == $gId);
    }

    private function _isRightChild( $gId )
    {
        $parent = $this->redis->hGet($gId,'parent');
        return ($this->redis->hGet($parent,'rightChild') == $gId);
    }

    private function  _avlBalanced( $gId )
    {
        $leftChild =  $this->redis->hGet($gId,'leftChild');
        $rightChild = $this->redis->hGet($gId,'rightChild');

        $leftHeight = $leftChild? $this->redis->hGet($leftChild,'height') : 0;
        $rightHeight = $rightChild? $this->redis->hGet($rightChild,'height') : 0;

        return ( abs( $leftHeight- $rightHeight) <= 1 );
    }

    private function _updateHeight( $gId )
    {
        $leftChild =  $this->redis->hGet($gId,'leftChild');
        $rightChild = $this->redis->hGet($gId,'rightChild');

        $leftHeight = $leftChild? $this->redis->hGet($leftChild,'height') : 0;
        $rightHeight = $rightChild? $this->redis->hGet($rightChild,'height') : 0;

        $this->redis->hSet($gId, 'height', max( $leftHeight,$rightHeight ) + 1 );

    }

    /**
     * 经过交换后wId 指向的就是树结构中的wId，但是其value和ia却是xId的，这是因为在真实树木中，id是和用户相连的，所以必须和value保持一致！！
     * @param $wId
     * @param $xId
     */
    private  function _swap( & $wId, & $xId )
    {
        $xParentId = $this->redis->hGet($xId,'parent');
        $wParentId = $this->redis->hGet($wId,'parent');

        $isWLeft = $this->_isLeftChild($wId);
        //因为index实际上时用户的uid,所以必须与其数据一致。
        if( $xParentId )
        {
            $this->redis->hSet($xParentId,$this->_isLeftChild($xId)?'leftChild':'rightChild',$wId);
        }

        if( $wParentId )
        {
            $this->redis->hSet($wParentId,$isWLeft?'leftChild':'rightChild',$xId);
        }


        $temp1 = $this->redis->hGetAll($wId);
        $temp2 = $this->redis->hGetAll($xId);

        $this->redis->hMset($xId,
            array(  'maxChildValue'=>$temp1['maxChildValue'],
                'minChildValue'=>$temp1['minChildValue'],
                'leftChild'=>$temp1['leftChild'],
                'rightChild'=>$temp1['rightChild'],
                'parent'=>$temp1['parent'],
                'value'=>$temp2['value'],
                'height' => $temp1['height'],
                'next' => $temp2['next'],
                'pre' => $temp2['pre'],
            )
        );

        $this->redis->hMset($wId,
            array(  'maxChildValue'=>$temp2['maxChildValue'],
                'minChildValue'=>$temp2['minChildValue'],
                'leftChild'=>$temp2['leftChild'],
                'rightChild'=>$temp2['rightChild'],
                'parent'=>$temp2['parent'],
                'value'=>$temp1['value'],
                'height' => $temp2['height'],
                'next' => $temp1['next'],
                'pre' => $temp1['pre'],
            )
        );

        $temp = $wId;
        $wId = $xId;
        $xId = $temp;

    }


    /**
     * @param $wId
     * @return string
     */
    private function _findSuccessor( $wId )
    {
        /*BinNodePosi(T) s = this; //记录后继的临时变量
   if (rChild) { //若有右孩子，则直接后继必在右子树中，具体地就是
       s = rChild; //右子树中
       while (HasLChild(*s)) s = s->lChild; //最靠左（最小）的节点
   } else { //否则，直接后继应是“将当前节点包含于其左子树中的最低祖先”，具体地就是
       while (IsRChild(*s)) s = s->parent; //逆向地沿右向分支，不断朝左上方移动
      s = s->parent; //最后再朝右上方移动一步，即抵达直接后继（如果存在）
   }
  return s;*/

        $ret = $wId;

        if( ($rightChild = $this->redis->hGet($wId,'rightChild')) != false)
        {
            $ret = $rightChild;
            while( $ret )
            {
                $ret = $this->redis->hGet($ret,'leftChild');
            }
        }
        else
        {
            while($this->_isRightChild($ret))
            {
                $ret = $this->redis->hGet($ret,'parent');
            }
        }

        return $ret;

    }

    private function _removeAt($xId,& $parentId)
    {
        //在这里将即将删除节点的next,pre直接修正
        $pre = $this->redis->hGet($xId,'pre');
        $next = $this->redis->hGet( $xId, 'next' );

        if( $pre )
        {
            $this->redis->hSet($pre, 'next', $next);
        }

        if( $next )
        {
            $this->redis->hSet($next, 'pre', $pre);
        }

        $wId = $xId;
        $successor = null;
        $uId = $this->redis->hGet( $wId, 'parent' );


        if( $this->redis->hGet($xId,'leftChild') == null )
        {
            $successor = $xId = $this->redis->hGet($xId,'rightChild');
        }
        else
        {
            if(  $this->redis->hGet($xId,'rightChild') == null  )
            {
                $successor = $xId = $this->redis->hGet($xId,'leftChild');
            }
            else
            {
                $wId = $this->_findSuccessor($wId);
                $uId = $this->redis->hGet( $wId, 'parent' );

                $successor = $this->redis->hGet($wId,'RightChild');

                if( $uId == $xId )
                {
                    $this->redis->hSet($uId,'rightChild',$successor);
                }
                else
                {
                    $this->redis->hSet($uId,'leftChild',$successor);
                }


                $this->_swap( $wId,$xId );

            }

        }


        $parentId = $this->redis->get( $wId, 'parent' );

        if( $successor )
        {
            $this->redis->hSet($successor,'parent', $parentId);
        }

        $this->redis->delete( $wId );

        return $successor;

    }

    public function search( $value, & $parent = null )
    {
        return $this->searchIn( $this->getRootId( $this->rootName ), $value, $parent );
    }

    /**
     * todo 注意redis取出的数据与value的判等操作
     * where node mean nodeId
     * @param $node
     * @param $value
     * @param $hot
     * @return null
     */
    private function searchIn( $node, $value, & $hot )
    {
        if( (! $this->redis->exists($node))  )
        {
            return null;
        }

        $data = $this->redis->hGet($node,'value');

        if( ($data == $value) )
        {
            return $node;
        }

        $hot = $node;

        return $this->searchIn( (($value < $data)? $this->redis->hGet($node,'leftChild') : $this->redis->hGet($node,'rightChild') ),$value,$hot );

    }


    private function _firstCover( $value, $det, & $parent = null )
    {
        return $this->_firstCoverIn( $this->getRootId( $this->rootName ), $value, $det,$parent );
    }

    private function _firstCoverIn( $node, $value, $det, & $hot )
    {
        if( (! $this->redis->exists($node))  )
        {
            return null;
        }

        $data = $this->redis->hGet($node,'value');

        if( ($data <= $value+$det) && ($data >= $value-$det) )
        {
            return $node;
        }

        $hot = $node;

        return $this->searchIn( (($value < $data)? $this->redis->hGet($node,'leftChild') : $this->redis->hGet($node,'rightChild') ),$value,$hot );

    }


    /**
     * todo 实现的正确性和效率有待论证
     * @param $firstCover
     * @param $leftBound
     * @param $rightBound
     * @return array|null
     */
    private function _visitChildrenInRange( $firstCover,$leftBound,$rightBound )
    {
        $data = $this->redis->hGet($firstCover,'value');

        if( $firstCover == null )
        {
            return null;
        }

        if( $data > $rightBound )
        {
            return array( $this->_visitChildrenInRange($this->redis->hGet($firstCover,'leftChild'),$leftBound,$rightBound) );
        }

        if( $data < $leftBound )
        {
            return array( $this->_visitChildrenInRange($this->redis->hGet($firstCover,'rightChild'),$leftBound,$rightBound) );
        }

        return array_merge( $this->_visitChildrenInRange($this->redis->hGet($firstCover,'leftChild'),$leftBound,$rightBound),array($firstCover),$this->_visitChildrenInRange($this->redis->hGet($firstCover,'rightChild'),$leftBound,$rightBound) );
    }

    /**
     * @param $value
     * @param $det
     * @return array|null
     */
    public function range(  $value, $det )
    {
        //find the first container
        $coverParent = null;
        $firstCover = $this->_firstCover($value,$det, $coverParent);

        //准备中序遍历，返回结果。只用在边界判断一次，超出即可终止
        return $this->_visitChildrenInRange( $firstCover,$value-$det,$value + $det );
    }


    private function _insertWithParentId( Node $node, $x, $parentId )
    {
        if( $x != null )
        {
            return $x;
        }

        if( $parentId == null )
        {
            //树大小为0,插入根节点
            $node->parent = null;
            $node->SetToRedis($this->redis);
            $this->setRootId( $node->nodeId );

        }
        else {
            //插入非根节点
            if( $this->redis->hGet($parentId, 'value') > $node->value )
            {
                $this->redis->hSet($parentId, 'leftChild', $node->nodeId);
                $pre = $this->redis->hGet($parentId,'pre');

                if( $pre ) {
                    $this->redis->hSet($pre, 'next', $node->nodeId);
                    $node->pre  = $pre;
                    $node->next = $parentId;
                    $this->redis->hSet($parentId,'pre',$node->nodeId);
                }
            }
            else
            {
                $this->redis->hSet($parentId, 'rightChild', $node->nodeId);
                $next = $this->redis->hGet($parentId,'next');

                if( $next ) {
                    $this->redis->hSet($next, 'pre', $node->nodeId);
                    $node->pre  = $parentId;
                    $node->next = $next;
                    $this->redis->hSet($parentId,'next',$node->nodeId);
                }
            }

            $node->parent = $parentId;
            $node->SetToRedis($this->redis);
        }

        $this->_updateMaxMin($node->nodeId);

        for( $gId = $parentId; $gId != null; $gId = $this->redis->hGet($gId,'parent') )
        {
            if( !$this->_avlBalanced($gId) )
            {
                $parentIdIn = $this->redis->hGet($gId,'parent');
                $temp = $this->_rotateAt( $this->_tallerChild ( $this->_tallerChild ( $gId ) ) );
                if( $parentIdIn != null )
                {
                    $this->redis->hSet( $parentIdIn, ($this->_isLeftChild($gId)?'leftChild':'rightChild'),$temp);
                }
                $this->_updateHeight($gId); //是否不必要更新
                break;
            }
            else
            {
                $this->_updateHeight($gId);
            }
        }

        $this->_updateMaxMinAbove($node->nodeId);

        return $node->nodeId;
    }

    /**
     * todo 必须加入对于maxmin的更新,加入对直接前继和直接后继的维护
     * @param Node $node
     * @return null
     */
    public function insert( Node $node )
    {
        $parentId = null;
        $x = $this->search( $node->value, $parentId );

        return $this->_insertWithParentId($node,$x,$parentId);
    }


    /**
     * 相对于左/右孩子，该节点的最小/最大值一定是自身，所以每次只需要单项更新
     * @param $node
     */
    private function _updateMaxMin( $node )
    {
        $leftChild = $this->redis->hGet($node, 'leftChild');
        $rightChild = $this->redis->hGet($node, 'rightChild');

        if($leftChild)
        {
            $this->redis->hSet($node,'minChildValue',$this->redis->hGet($leftChild,'minChildValue'));
        }
        else
        {
            $this->redis->hSet($node,'minChildValue',$this->redis->hGet($node,'value'));
        }

        if($rightChild)
        {

            $this->redis->hSet($node,'maxChildValue',$this->redis->hGet($rightChild,'maxChildValue'));
        }
        else
        {
            $this->redis->hSet($node,'maxChildValue',$this->redis->hGet($node,'value'));
        }
    }

    private function _updateMaxMinAbove( $node )
    {
           while( $node )
           {
               $max = $this->redis->hGet($node,'maxChildValue');
               $min = $this->redis->hGet($node,'minChildValue');

               $this->_updateMaxMin( $node );

               if( $max == $this->redis->hGet($node,'maxChildValue') && $min == $this->redis->hGet($node,'minChildValue') )
               {
                   break;
               }
               else
               {
                   $node = $this->redis->hGet($node,'parent');
               }
           }
    }

    /**
     * 因为高度和balance是息息相关的
     * todo 必须加入对max min 的更新
     * @param $nodeId
     * @return bool
     */
    public function remove( $nodeId )
    {
        $parentId = null;
        $x = $this->search( $nodeId, $parentId );
        if( $x == null )
        {
            return false;
        }

        $this->_removeAt($x,$parentId);


        for( $gId = $parentId; $gId != null; $gId = $this->redis->hGet($gId,'parent')  )
        {
            if( !$this->_avlBalanced($gId) )
            {
                $parentIdIn = $this->redis->hGet($gId,'parent');
                $temp = $this->_rotateAt( $this->_tallerChild ( $this->_tallerChild ( $gId ) ) );
                if( $parentIdIn != null )
                {
                    $this->redis->hSet( $parentIdIn, ($this->_isLeftChild($gId)?'leftChild':'rightChild'),$temp);
                }
                $gId = $temp;
            }
            $this->_updateHeight($gId);
            $this->_updateMaxMin($gId);
        }

        return true;
    }

    //以下都是涉及到near方法的函数


    private function _getCoverNode( $nodeId, $value )
    {

        while( $nodeId );
        {
            if( $this->redis->hGet($nodeId,'maxChildValue') >= $value && $this->redis->hGet($nodeId,'minChildValue') <= $value )
            {
                return $nodeId;
            }
            else
            {
                $nodeId = $this->redis->hGet($nodeId, 'parent');
            }
        }
        return $nodeId;
    }

    public function searchNear( $nodeId, $value, & $parentId )
    {
        $coverNode = $this->_getCoverNode( $nodeId, $value );
        if($coverNode == false)
        {
            return null;
        }
        return $this->searchIn($coverNode,$value,$parentId);
    }


    private function _getDistance( $value1, $value2 )
    {
        return abs($value1-$value2);
    }

    /**
     *
     * 语义上是指按顺序返回count个离给出node最近的节点
     * @param $nodeId
     * @param $det
     * @param $count
     * @return array
     */
    public function rangeNear( $nodeId,$det,$count )
    {
        $next = $this->redis->hGet($nodeId,'next');
        $pre  = $this->redis->hGet($nodeId,'pre');
        $nextValue = $this->redis->hGet($next,'value');
        $preValue  = $this->redis->hGet($pre,'value');
        $value = $this->redis->hGet($nodeId, 'value');
        $returnArray = array();

        for( $i = 0; $i < $count; $i++ )
        {

            if( $next == null )
            {
                if( $pre == null )
                {
                    return $returnArray;
                }
                else
                {
                    if( $this->_getDistance( $value,$preValue ) <= $det )
                    {
                        array_push($returnArray,$pre);
                        $pre = $this->redis->hGet($pre,'pre');
                        $preValue = $this->redis->hGet( $pre, 'value' );
                    }
                    else
                    {
                        return $returnArray;
                    }
                }

            }
            else
            {
                if( $pre == null )
                {
                    if( $this->_getDistance( $value,$nextValue ) <= $det )
                    {
                        array_push($returnArray,$next);
                        $next = $this->redis->hGet($next,'next');
                        $nextValue = $this->redis->hGet( $next, 'value' );
                    }
                    else
                    {
                        return $returnArray;
                    }
                }
                else
                {
                    if( $this->_getDistance( $value,$nextValue ) <= $this->_getDistance( $value,$preValue )  )
                    {
                        if( $this->_getDistance( $value,$nextValue ) <= $det )
                        {
                            array_push($returnArray,$next);
                            $next = $this->redis->hGet($next,'next');
                            $nextValue = $this->redis->hGet( $next, 'value' );
                        }
                        else
                        {
                            return $returnArray;
                        }
                    }
                    else
                    {
                        if( $this->_getDistance( $value,$preValue ) <= $det )
                        {
                            array_push($returnArray,$pre);
                            $pre = $this->redis->hGet($pre,'pre');
                            $preValue = $this->redis->hGet( $pre, 'value' );
                        }
                        else
                        {
                            return $returnArray;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $nodeId
     * @param $value
     */
    public function updateNode( $nodeId, $value )
    {
        $next = $this->redis->hGet($nodeId,'next');
        $pre  = $this->redis->hGet($nodeId,'pre');
        $nextValue = $this->redis->hGet($next,'value');
        $preValue  = $this->redis->hGet($pre,'value');

        if( $next == null )
        {
            if($pre == null)
            {
                $this->redis->hSet($nodeId,'value',$value);
                return;
            }
            else
            {
                if( $value >= $preValue )
                {
                    $this->redis->hSet($nodeId,'value',$value);
                    return;
                }
                else
                {
                    $this->remove($nodeId);
                    $newNode = new Node();
                    $newNode->GetValueFromRedis($this->redis,$nodeId);
                    $this->insertNear( $newNode,$pre);
                }
            }
        }
        else
        {
            if($pre == null)
            {
                if( $value <= $nextValue )
                {
                    $this->redis->hSet($nodeId,'value',$value);
                    return;
                }
                else
                {
                    $this->remove($nodeId);
                    $newNode = new Node();
                    $newNode->GetValueFromRedis($this->redis,$nodeId);
                    $this->insertNear( $newNode,$next);
                }
            }
            else
            {
                if( $value <= $nextValue && $value >= $preValue )
                {
                    $this->redis->hSet($nodeId,'value',$value);
                    return;
                }
                else
                {
                    $this->remove($nodeId);
                    $newNode = new Node();
                    $newNode->GetValueFromRedis($this->redis,$nodeId);
                    $this->insertNear( $newNode,$pre);
                }
            }
        }

    }

    /**
     * @param Node $nodeToInsert
     * @param $nodeIdNear
     * @return mixed
     */
    public function insertNear( Node $nodeToInsert,$nodeIdNear )
    {
        $parentId = null;
        $x = $this->searchNear( $nodeIdNear,$nodeToInsert->value, $parentId );

        return $this->_insertWithParentId($nodeToInsert,$x,$parentId);
    }
}