# 4 种字符串查找算法总结

 时间 2017-03-02 11:30:47

原文[http://blog.jobbole.com/110429/][2]


**字符串搜索算法** （ **String searching algorithms** ）又称 **字符串比对算法** （ **string matching algorithms** ）是一种搜索算法，是字符串算法中的一类，用以试图在一长字符串或文章中，找出其是否包含某一个或多个字符串，以及其位置。—— 维基百科 

字符串匹配是字符串的一种基本操作：给定一个长度为 M 的文本和一个长度为 N 的模式串，在文本中找到一个和该模式相符的子字符串，并返回该字字符串在文本中的位置。

KMP 算法，全称是 Knuth-Morris-Pratt 算法，以三个发明者命名，开头的那个K就是著名科学家 Donald Knuth 。KMP 算法的关键是求 next 数组。next 数组的长度为模式串的长度。next 数组中每个值代表模式串中当前字符前面的字符串中，有多大长度的相同前缀后缀。

Boyer-Moore 算法在实际应用中比 KMP 算法效率高，据说各种文本编辑器的”查找”功能（Ctrl+F），包括 linux 里的 grep 命令，都是采用 Boyer-Moore 算法。该算法有“坏字符”和“好后缀”两个概念。主要特点是字符串从后往前匹配。

Sunday 算法跟 KMP 算法一样，是从前往后匹配。在匹配失败时，关注文本串中参加匹配的最末位字符的下一位字符，如果该字符不在模式串中，则整个模式串移动到该字符之后。如果该字符在模式串中，将模式串右移使对应的字符对齐。

关于这几种算法的详细介绍，可参考 [该博客][4] ，和《 [简洁高效的 Boyer-Moore 算法][5] 》、《 [字符串匹配的 KMP 算法][6] 》和《 [我理解的 KMP 算法][7] 》。 

下面分别给出暴力匹配、KMP 算法、Boyer-Moore 算法和 Sunday 算法的 Java 实现。

### 暴力匹配：

```java
    public static int forceSearch(String txt, String pat) {
        int M = txt.length();
        int N = pat.length();
        for (int i = 0; i <= M - N; i++) {
            int j;
            for (j = 0; j < N; j++) {
                if (txt.charAt(i + j) != pat.charAt(j))
                    break;
            }
            if (j == N)
                return i;
        }
        return -1;
    }
    
```
### KMP 算法：

```java
    public class KMP {
        public static int KMPSearch(String txt, String pat, int[] next) {
            int M = txt.length();
            int N = pat.length();
            int i = 0;
            int j = 0;
            while (i < M && j < N) {
                if (j == -1 || txt.charAt(i) == pat.charAt(j)) {
                    i++;
                    j++;
                } else {
                    j = next[j];
                }
            }
            if (j == N)
                return i - j;
            else
                return -1;
        }
        public static void getNext(String pat, int[] next) {
            int N = pat.length();
            next[0] = -1;
            int k = -1;
            int j = 0;
            while (j < N - 1) {
                if (k == -1 || pat.charAt(j) == pat.charAt(k)) {
                    ++k;
                    ++j;
                    next[j] = k;
                } else
                    k = next[k];
            }
        }
        public static void main(String[] args) {
            String txt = "BBC ABCDAB CDABABCDABCDABDE";
            String pat = "ABCDABD";
            int[] next = new int[pat.length()];
            getNext(pat, next);
            System.out.println(KMPSearch(txt, pat, next));
        }
    }
    
```

### Boyer-Moore 算法

```java
    public class BoyerMoore {
        public static void getRight(String pat, int[] right) {
            for (int i = 0; i < 256; i++){
                right[i] = -1;
            }
            for (int i = 0; i < pat.length(); i++) {
                right[pat.charAt(i)] = i;
            }
        }
        public static int BoyerMooreSearch(String txt, String pat, int[] right) {
            int M = txt.length();
            int N = pat.length();
            int skip;
            for (int i = 0; i <= M - N; i += skip) {
                skip = 0;
                for (int j = N - 1; j >= 0; j--) {
                    if (pat.charAt(j) != txt.charAt(i + j)) {
                        skip = j - right[txt.charAt(i + j)];
                        if (skip < 1){
                            skip = 1;
                        }
                        break;
                    }
                }
                if (skip == 0)
                    return i;
            }
            return -1;
        }
        public static void main(String[] args) {
            String txt = "BBC ABCDAB AACDABABCDABCDABDE";
            String pat = "ABCDABD";
            int[] right = new int[256];
            getRight(pat,right);
            System.out.println(BoyerMooreSearch(txt, pat, right));
        }
    }
    
```

### Sunday算法

```java
    public class Sunday {
        public static int getIndex(String pat, Character c) {
            for (int i = pat.length() - 1; i >= 0; i--) {
                if (pat.charAt(i) == c)
                    return i;
            }
            return -1;
        }
        public static int SundaySearch(String txt, String pat) {
            int M = txt.length();
            int N = pat.length();
            int i, j;
            int skip = -1;
            for (i = 0; i <= M - N; i += skip) {
                for (j = 0; j < N; j++) {
                    if (txt.charAt(i + j) != pat.charAt(j)){
                        if (i == M - N)
                            break;
                        skip = N - getIndex(pat, txt.charAt(i + N));
                        break;
                    }
                }
                if (j == N)
                    return i;
            }
            return -1;
        }
        public static void main(String[] args) {
            String txt = "BBC ABCDAB AACDABABCDABCDABD";
            String pat = "ABCDABD";
            System.out.println(SundaySearch(txt, pat));
        }
    }
```

[2]: http://blog.jobbole.com/110429/
[4]: http://blog.csdn.net/v_july_v/article/details/7041827
[5]: http://blog.jobbole.com/104854/
[6]: http://blog.jobbole.com/39066/
[7]: http://blog.jobbole.com/90576/