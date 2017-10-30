#include <cstdio>  
#include <iostream>  
#include <algorithm>  
#include <cstring>  
using namespace std;  
int a[2000][2000];  
int main()  
{  
    int t,n,i,j;  
    while(~scanf("%d",&n))  
    {  
   
        for(i=0; i<n; i++)  
            for(j=0; j<=i; j++)  
                scanf("%d",&a[i][j]);  
        for(i=n-1; i>0; i--)  
            for(j=0; j<i; j++)  
                a[i-1][j]+=max(a[i][j],a[i][j+1]);  
        printf("%d\n",a[0][0]);  
    }  
    return 0;  
} 