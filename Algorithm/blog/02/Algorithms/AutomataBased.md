Imperative style:
```c
int main(void)
{
    int c;
    do {
        do
            c = getchar();
        while(c == ' ');
        while(c != EOF && !isspace(c) && c != '\n') {
            putchar(c);
            c = getchar();
        }
        putchar('\n');
        while(c != EOF && c != '\n')
            c = getchar();
    } while(c != EOF);
    return 0;
}
```
Automata style.

Only one reading. Only one loop instead of the four.
```c
int main(void)
{
    enum states {
        before, inside, after
    } state;
    int c;
    state = before;
    while((c = getchar()) != EOF) {
        switch(state) {
            case before:
                if(c != ' ') {
                    putchar(c);
                    if(c != '\n')
                        state = inside;
                }
                break; 
            case inside:
                if(!isspace(c))
                    putchar(c);
                else {
                    putchar('\n');
                    if(c == '\n')
                        state = before;
                    else
                        state = after;
                }
                break;
            case after:
                if(c == '\n')
                    state = before;
        }
    }
    return 0;
}
```
