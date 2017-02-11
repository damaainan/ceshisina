public interface ILetterProcess {
    //首先要写信的内容
    public void writeContext(String context);
    //其次写信封
    public void fillEnvelope(String address);
    //把信放到信封里
    public void letterInotoEnvelope();
    //然后邮递
    public void sendLetter();
}