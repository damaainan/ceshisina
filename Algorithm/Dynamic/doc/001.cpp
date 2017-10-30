#include <iostream>
#include <cstring>
#include <algorithm>
#include <cstdio>
using namespace std;
const int Max = 100005+10;
int dp[Max][2];
int main()
{
   #ifdef xxz
    freopen("in","r",stdin);
   #endif // xxz
 
    int T;
    cin>>T;
    while(T--)
    {
        memset(dp,0,sizeof(dp));
        string str;
        cin>>str;
        int len = str.length();
 
        if(str[0] == '?')
        {
            dp[0][1] = dp[0][0] = 1;
        }
        else {
            int temp = str[0] -'0';
            dp[0][temp] = 1;
        }
 
        for(int i = 1; i < len; i++)
        {
            if(str[i] == '?' || str[i] == '1')
            {
                dp[i][1] = dp[i-1][0] + 1;
            }
 
            if(str[i] =='?' || str[i] == '0')
            {
                dp[i][0] = dp[i-1][1] + 1;
            }
        }
 
        int ans = 0;
 
        for(int i = 0; i < len;i ++)
        {
            ans = max(ans,max(dp[i][0],dp[i][1]));
        }
 
        cout<<ans<<endl;
    }
    return 0;
}