class AlgoFrame{

    constructor(g2d, title, canvasWidth, canvasHeight) {
        this.g2d = g2d;
        this.canvasWidth = canvasWidth;
        this.canvasHeight = canvasHeight;
    }

    getCanvasWidth() {
        return this.canvasWidth;
    }

    getCanvasHeight() {
        return this.canvasHeight;
    }

    repaint(){
        // 具体绘制
        this.g2d.clearRect(0, 0, this.canvasWidth, this.canvasHeight);

        var w = this.canvasWidth / this.data.N();

        for (var i = 0; i < this.data.N(); i++) {
            if(i < this.data.orderedIndex){
                AlgoVisHelper.setColor(this.g2d, AlgoVisHelper.Red);
            }else{
                AlgoVisHelper.setColor(this.g2d, AlgoVisHelper.Grey);
            }
            if(i == this.data.currentCompareIndex){
                AlgoVisHelper.setColor(this.g2d, AlgoVisHelper.LightBlue);
            }
            if(i == this.data.currentMinIndex){
                AlgoVisHelper.setColor(this.g2d, AlgoVisHelper.Indigo);
            }
            AlgoVisHelper.fillRectangle(this.g2d, i * w, this.canvasHeight - this.data.get(i), w - 1, this.data.get(i));
        }
    }

    render(data) {
       this.data = data;    
       this.repaint();
    }
}