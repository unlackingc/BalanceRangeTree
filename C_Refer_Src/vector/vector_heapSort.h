/******************************************************************************************
 * Data Structures in C++
 * ISBN: 7-302-33064-6 & 7-302-33065-3 & 7-302-29652-2 & 7-302-26883-3
 * Junhui DENG, deng@tsinghua.edu.cn
 * Computer Science & Technology, Tsinghua University
 * Copyright (c) 2006-2013. All rights reserved.
 ******************************************************************************************/

#pragma once

template <typename T> void Vector<T>::heapSort(Rank lo, Rank hi) { //0 <= lo < hi <= size
   /*DSA*/printf("\tHEAPsort [%3d, %3d)\n", lo, hi);
   PQ_ComplHeap<T> H(_elem, lo, hi); //取出待排序区间并建成完全二叉堆，O(n)
   while (!H.empty()) //反复迭代，直至堆空
      _elem[--hi] = H.delMax(); //摘除最大元并转移至原区间：等效于堆顶与末元素对换后下滤
}
