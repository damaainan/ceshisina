class AlgoVisHelper{
    // 构造函数
    constructor() {}

    static fillRectangle(g2d, x, y, w, h){
        g2d.fillRect(x, y, w, h);
    }

    static setColor(g2d, color){
        g2d.fillStyle = color;
    }
}

// 类中的静态属性
AlgoVisHelper.Red = "#F44336";
AlgoVisHelper.Pink = "#E91E63";
AlgoVisHelper.Purple = "#9C27B0";
AlgoVisHelper.DeepPurple = "#673AB7";
AlgoVisHelper.Indigo = "#3F51B5";
AlgoVisHelper.Blue = "#2196F3";
AlgoVisHelper.LightBlue = "#03A9F4";
AlgoVisHelper.Cyan = "#00BCD4";
AlgoVisHelper.Teal = "#009688";
AlgoVisHelper.Green = "#4CAF50";
AlgoVisHelper.LightGreen = "#8BC34A";
AlgoVisHelper.Lime = "#CDDC39";
AlgoVisHelper.Yellow = "#FFEB3B";
AlgoVisHelper.Amber = "#FFC107";
AlgoVisHelper.Orange = "#FF9800";
AlgoVisHelper.DeepOrange = "#FF5722";
AlgoVisHelper.Brown = "#795548";
AlgoVisHelper.Grey = "#9E9E9E";
AlgoVisHelper.BlueGrey = "#607D8B";
AlgoVisHelper.Black = "#000000";
AlgoVisHelper.White = "#FFFFFF";