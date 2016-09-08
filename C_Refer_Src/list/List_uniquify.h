/******************************************************************************************
 * Data Structures in C++
 * ISBN: 7-302-33064-6 & 7-302-33065-3 & 7-302-29652-2 & 7-302-26883-3
 * Junhui DENG, deng@tsinghua.edu.cn
 * Computer Science & Technology, Tsinghua University
 * Copyright (c) 2006-2013. All rights reserved.
 ******************************************************************************************/

#pragma once

template <typename T> int List<T>::uniquify() { //�����޳��ظ�Ԫ�أ�Ч�ʸ���
   if (_size < 2) return 0; //ƽ���б���Ȼ���ظ�
   int oldSize = _size; //��¼ԭ��ģ
   ListNodePosi(T) p; ListNodePosi(T) q; //����ָ����ڵĸ��Խڵ�
   for (p = header, q = p->succ; trailer != q; p = q, q = q->succ) //����������ɨ��
      if (p->data == q->data) { remove(q); q = p; } //��p��q��ͬ����ɾ������
   return oldSize - _size; //�б��ģ�仯��������ɾ��Ԫ������
}