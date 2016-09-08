/******************************************************************************************
 * Data Structures in C++
 * ISBN: 7-302-33064-6 & 7-302-33065-3 & 7-302-29652-2 & 7-302-26883-3
 * Junhui DENG, deng@tsinghua.edu.cn
 * Computer Science & Technology, Tsinghua University
 * Copyright (c) 2006-2013. All rights reserved.
 ******************************************************************************************/

#pragma once

#include "../Entry/Entry.h"
#define QuadlistNodePosi(T)  QuadlistNode<T>* //��ת��ڵ�λ��

template <typename T> struct QuadlistNode{ //QuadlistNodeģ����
   T entry; //�������
   QuadlistNodePosi(T) pred;  QuadlistNodePosi(T) succ;  //ǰ�������
   QuadlistNodePosi(T) above; QuadlistNodePosi(T) below; //���ڡ�����
   QuadlistNode //������
      ( T e = T(), QuadlistNodePosi(T) p = NULL, QuadlistNodePosi(T) s = NULL,
      QuadlistNodePosi(T) a = NULL, QuadlistNodePosi(T) b = NULL)
      : entry(e), pred(p), succ(s), above(a), below(b) {}
   QuadlistNodePosi(T) insertAsSuccAbove //�����½ڵ㣬�Ե�ǰ�ڵ�Ϊǰ�����Խڵ�bΪ����
      (T const & e, QuadlistNodePosi(T) b = NULL);
};

#include "QuadlistNode_implementation.h"