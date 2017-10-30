#include <stdio.h>  
int c[10][100]={0};  
  
void knap(int m,int n){  
  
    int i,j,w[10],p[10];  
    for(i=1;i<n+1;i++)  
        scanf("%d,%d",&w[i],&p[i]);  
    for(j=0;j<m+1;j++)  
        for(i=0;i<n+1;i++)  
    {  
        if(j<w[i])  
        {  
            c[i][j]=c[i-1][j];  
            continue;  
        }else if(c[i-1][j-w[i]]+p[i]>c[i-1][j])  
            c[i][j]=c[i-1][j-w[i]]+p[i];  
        else  
            c[i][j]=c[i-1][j];  
    }  
      
}              
  
  
int main(){  
    int m,n;int i,j;  
    printf("input the max capacity and the number of the goods:\n");  
    scanf("%d,%d",&m,&n);  
    printf("Input each one(weight and value):\n");  
    knap(m,n);  
    printf("\n");  
   for(i=0;i<=n;i++)  
        for(j=0;j<=m;j++)  
       {  
     printf("%4d",c[i][j]);  
    if(m==j) printf("\n");  
    }  
} 