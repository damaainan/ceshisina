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