public class Test {

    public static void main(String[] args) { 
        String str1 = "和谐"; 
        String str2 = "社会"; 
        String str3 = "和谐社会"; 
        String str4; 

        str4 = str1 + str2; 
        System.out.println(str3 == str4); 

        str4 = (str1 + str2).intern(); 
        System.out.println(str3 == str4); 
    } 
}