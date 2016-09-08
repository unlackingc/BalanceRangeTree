/******************************************************************************************
 * Data Structures in C++
 * ISBN: 7-302-33064-6 & 7-302-33065-3 & 7-302-29652-2 & 7-302-26883-3
 * Junhui DENG, deng@tsinghua.edu.cn
 * Computer Science & Technology, Tsinghua University
 * Copyright (c) 2006-2013. All rights reserved.
 ******************************************************************************************/

/*DSA*/#include "../Bitmap/Bitmap.h"

int primeNLT(int low, int n, char *file) { //����file�ļ��еļ�¼����[low, n)��ȡ��С������
   Bitmap B(file, n);
   while (low < n)
      if (B.test(low)) low++;
      else return low;
   return low; //��û������������������n
}
