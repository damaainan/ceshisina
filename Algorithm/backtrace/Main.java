import java.util.Stack;

// javac -encoding utf-8 Main.java 
// java -Dfile.encoding=UTF-8 Main

/**
 * Created by chenjiabing on 17-5-5.
 */
class position{
    public int row;
    public int col;

    public position(int row,int col){
        this.col = col;
        this.row = row;
    }

    public position(){
        row = 0;
        col = 0;
    }

    public String toString(){
        return "(" + (row - 1) + " ," + (col - 1) + ")";
    }  //这里由于四周围上了墙，所以这里的输出就要在原来的基础上减一
}


class Main{
    private int[][] maze = null;
    private Stack<position> stack = null;  //创建一个栈用于存储状态
    private int row;   //行数
    private int col;
    boolean[][] p = null;    //这里的p是用来标记已经走过的点，初始化为false

    public boolean end(int i,int j){
        return i == row && j == col;
    }

    public Main(int[][] maze){
        stack = new Stack<position>();
        row = maze[0].length;// 行数
        col = maze.length;   //列数
        p = new boolean[row + 2][col + 2];
        for (int i = 0; i < row; i++) {
            for (int j = 0; j < col; j++) {
                p[i][j] = false;    //初始化
            }
        }
        this.maze = maze;


    }

    public void findPath(){

        //创建一个新的迷宫，将两边都围上墙，也就是在四周都填上1的墙，形成新的迷宫，主要的目的就是防止走到迷宫的边界的出口的位置还会继续向前走
        //因此需要正确的判断是否在边界线上，所以要在外围加上一堵墙,
        int[][] temp = new int[row + 2][col + 2];
        for (int i = 0; i < row + 2; i++) {
            for (int j = 0; j < col + 2; j++) {
                temp[0][j] = 1;   //第一行围上
                temp[row + 1][j] = 1;  //最后一行围上
                temp[i][0] = temp[i][col + 1] = 1;  //两边的围上
            }
        }


        // 将原始迷宫复制到新的迷宫中
        for (int i = 0; i < row; ++i) {
            for (int j = 0; j < col; ++j) {
                temp[i + 1][j + 1] = maze[i][j];
            }
        }


        int i = 1;
        int j = 1;
        p[i][j] = true;
        stack.push(new position(i, j));
        //这里是是将走到的点入栈，然后如果前后左右都走不通的话才出栈
        while (!stack.empty() && !end(i, j)) {


           //下面就开始在四周试探，如果有路就向前走，顺序分别是右，下，上，左，当然这是随便定义的，不过一般都是现向下和右的
            if (temp[i][j + 1] == 0 && p[i][j + 1] == false)//这里如果不在四周加上墙，那么在到达边界判断的时候就会出现超出数组的索引的错误，因为到达边界再加一就会溢出
            {
                p[i][j + 1] = true;
                stack.push(new position(i, j + 1));
                j++;
            } else if (temp[i + 1][j] == 0 && p[i + 1][j] == false)//如果下面可以走的话，讲当前点压入栈，i++走到下一个点
            {
                p[i + 1][j] = true;
                stack.push(new position(i + 1, j));
                i++;
            } else if (temp[i][j - 1] == 0 && p[i][j - 1] == false) {
                p[i][j - 1] = true;
                stack.push(new position(i, j - 1));
                j--;
            } else if (temp[i - 1][j] == 0 && p[i - 1][j] == false) {
                p[i - 1][j] = true;
                stack.push(new position(i - 1, j));
                i--;
            } else   //前后左右都不能走
            {
                System.out.println(i + "---------" + j);
                stack.pop();   //这个点不能走通，弹出
                if (stack.empty())      //如果此栈中已经没有点了，那么直接跳出循环
                {
                    System.out.println("没有路径了，出不去了");
                    return;    //直接退出了，下面就不用找了
                }
                i = stack.peek().row;   //获得最新点的坐标
                j = stack.peek().col;

            }

            //如果已经到达了边界，那么直接可以出去了，不需要继续向前走了，这里是规定边界的任意为0的位置都是出口
            //如果不加这个判断的话，那么当到达边界的时候，只有走到不能再走的时候才会输出路线，那种线路相对这个而言是比较长的
            if (j == temp[0].length - 2) {   //如果已经到达边界了，那么当前的位置就是出口，就不需要再走了
                Stack<position> pos = new Stack<position>();

                System.out.println("路径如下：");

                for (int count = 0; count < stack.size(); count++) {
                    System.out.println(stack.elementAt(count));
                }
            }
        }
    }

    public static void main(String args[]){
        int[][] maze = {
            {0, 1, 0, 0, 0},
            {0, 1, 0, 1, 0},
            {0, 0, 0, 0, 0},
            {0, 1, 1, 1, 0},
            {0, 0, 0, 1, 0}
        };
        Main main = new Main(maze);
        main.findPath();
    }
}