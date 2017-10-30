#include <stdio.h>  
#include <string.h>  
const int N = 50005;  
const int mod = 1e9+7;  
  
int dp[N][2];  
int main()  
{  
    int n,a,b;  
    while(scanf("%d%d%d",&n,&a,&b)!=EOF)  
    {  
        memset(dp,0,sizeof(dp));  
        dp[0][1]=1;  
        dp[0][0]=1;  
        for(int i=1;i<=n;i++)  
        {  
            for(int j=1;j<=i&&j<=a;j++)  
            {  
                dp[i][1]+=dp[i-j][0];  
                dp[i][1]%=mod;  
            }  
            for(int j=1;j<=i&&j<=b;j++)  
            {  
                dp[i][0]+=dp[i-j][1];  
                dp[i][0]%=mod;  
            }  
        }  
        printf("%d\n",(dp[n][0]+dp[n][1])%mod);  
    }  
  
    return 0;  
}  