var __ = require('lodash');


    var C = 150                            //背包最大承重
    var WEIGHT = [35,30,60,50,40,10,25]    //物品重量
    var POWER = [10,40,30,50,35,40,30]     //物品价值
    var LEN = 7                            //基因长度
    var maxPower = 0                       //保存最大值方案
    var maxGene = []
    var maxi = 0;                          //最大值最初出现的进化代数
    const POPMAX = 32,                     //种群数量
        P_XOVER = 0.8,                     //遗传概率
        P_MUTATION = 0.15,                 //变异概率
        MAXGENERATIONS = 20                //总的进化代数
    var pop = []                           //种群所有对象



    class Gene{
        constructor(gene){
            this.gene = gene;            //基因，数组
            this.fitness = 0;
            this.rf = 0;
            this.cf = 0;
        }
    }



    function initGenes(){
        let count = 0, maxFit = 100;    //随机生成的基因适应度的最大值
        while(count < POPMAX){
            let tmp = [],pall = 0;
            for(let j = 0; j<LEN; j++){
                let pow = Math.round(Math.random())    //随机生成0，1
                tmp.push(pow);
                if(pow == 1)
                    pall += POWER[j]
            }
            if(pall < maxFit){
                let g = new Gene(tmp)
                pop.push(g)
                count++
            }
        }
    }

    function envaluateFitness(max){            //max参数只是用来记录进化代数
        let totalFitness = 0;
        for(let i=0; i<POPMAX; i++ ){
            let tw = 0;
            pop[i].fitness = 0;
            for(let j=0; j<LEN; j++){
                if(pop[i].gene[j]){
                    tw += WEIGHT[j]
                    pop[i].fitness += POWER[j]
                }
            }
            if(tw > C){                    //基因不符合要求，适应降到1，让其自然淘汰
                pop[i].fitness = 1;
            }else{
                if(pop[i].fitness > maxPower){            //保存阶段最优值
                    maxPower = pop[i].fitness;
                    maxGene = __.cloneDeep(pop[i].gene);  //使用lodash库
                    maxi = max;
                }
            }
            totalFitness += pop[i].fitness
        }
        return totalFitness;
    }


    function selectBetter(totalFitness){
        let lastCf = 0;
        let newPop = []
        for(let i = 0; i<POPMAX; i++){        //计算个体选择概率和累积概率
            pop[i].rf = pop[i].fitness / totalFitness;
            pop[i].cf = lastCf + pop[i].rf;
            lastCf = pop[i].cf;
        }
        for(let i=0; i<POPMAX; i++){        //轮盘赌式选择
            let p = Math.random();
            if(p < pop[0].cf){
                newPop[i] = pop[0];
            }else{
                for(var j = 0; j<POPMAX-1; j++){
                    if(p >= pop[j].cf && p < pop[j+1].cf){
                        newPop[i] = pop[j+1];
                        break;
                    }
                }
            }
        }
        pop = []         //种群替换，坑在这，直接 pop=__.cloneDeep(newPop)不对，高手给解释下，谁研究过lodash的源码？
        for(let i=0; i< newPop.length; i++){    
            pop.push(__.cloneDeep(newPop[i]))
        }
    }


    function crossover(){
        let first = -1;
        for(let i=0; i<POPMAX; i++){
            let p = Math.random();
            if(p < P_XOVER){
                if(first < 0){
                    first = i;
                }else{    //选择了两个随机个体，进行基因交换
                    exChgOver(first,i)
                    first = -1;
                }
            }
        }
    }
    function exChgOver(first,second){            //基因交换函数
        let ecc = Math.round(Math.random() * LEN)
        for(let i=0; i<ecc; i++){
            let idx = Math.floor(Math.random() * LEN)
            let tg = pop[first].gene[idx]
            pop[first].gene[idx] = pop[second].gene[idx]
            pop[second].gene[idx] = tg
        }
    }

    function mutation(){
        for(let i=0; i<POPMAX; i++){
            let p = Math.random();
            if(p < P_MUTATION){        //只有当随机数小于变异概率才进行变异操作
                reverseGene(i)
            }
        }
    }
    function reverseGene(index){        //变异操作函数
        let mcc = Math.round(Math.random() * LEN)
        for(let i = 0; i < mcc; i++){
            let gi = Math.floor(Math.random() * LEN) 
            pop[index].gene[gi] = 1 - pop[index].gene[gi]
        }
    }


    initGenes();
    var f = envaluateFitness(0)
    for(let i=0; i<MAXGENERATIONS; i++){
        selectBetter(f)
        crossover()
        mutation()
        f= envaluateFitness(i)
    }
    console.log(maxi + '--' + maxPower + ' <=> ' + maxGene.join(','));