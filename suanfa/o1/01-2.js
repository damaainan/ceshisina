    function palindrome(str) {
        return str.replace(/[\W_]/g,'').toLowerCase() ===
               str.replace(/[\W_]/g,'').toLowerCase().split('').reverse().join('');
    }
    
    palindrome("almostomla");
    palindrome("five|\_/|four");
    palindrome("_eye");