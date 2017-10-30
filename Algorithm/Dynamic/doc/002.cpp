#include <iostream>  
#include <cstdio>  
#include <cstring>  
using namespace std;  
double dp[60][60][2510];  
int main()  
{  
#ifdef xxz  
    freopen("in","r",stdin);  
#endif // xxz  
    int T;  
    cin>>T;  
    while(T--)  
    {  
        int n,m;  
        cin>>n>>m;  
        memset(dp,0,sizeof(dp));  
        dp[0][0][0] = 1.0;  
        for(int  i = 1; i <= n ; i++)  
            for(int j = 1; j <= m; j++)  
                for(int k = 1; k <= n*m; k++)  
                {  
                    double temp = n*m - k + 1;  
                    if(i == n && j == m)  
                    {  
                        dp[i][j][k] = dp[i - 1][j - 1][k - 1] *(1.0*(n-i+1)*(m - j+1) / temp)  
                                      + dp[i-1][j][k-1]*(1.0*(n - i+1)*j/temp)  
                                      +dp[i][j-1][k-1]*(1.0*i*(m - j+1 )/temp);  
  
  
  
                    }  
                    else  
                    {  
                        dp[i][j][k] = dp[i - 1][j - 1][k - 1] *(1.0*(n-i+1)*(m - j+1) / temp)  
                                      + dp[i-1][j][k-1]*(1.0*(n - i+1)*j/temp)  
                                      +dp[i][j-1][k-1]*(1.0*i*(m - j+1 )/temp)  
                        +dp[i][j][k-1]*(1.0*(i*j - k+1)/temp);  
  
  
                    }  
  
                }  
  
        double ans = 0;  
        for(int i = 1; i <= n*m; i++)  
        {  
            ans += dp[n][m][i] * i;  
        }  
        printf("%.12lf\n",ans);  
  
    }  
    return 0;  
} 