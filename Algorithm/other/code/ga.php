<?php 

// 遗传算法 解决 背包问题

    /* yang belum :

    memilih 2 parent terbaik untuk lanjut ke generasi selanjutnya
    lakukan crossover (buatkan fungsi crossover)
    mutasi (fungsi mutasi dengan masukan mutation rate)
    dan lakukan termination ketika sudah mencapai kriteria yaitu :
    tidak melebihi kontainer dengan nilai fitness paling tinggi
    serta paling banyak barang yang na ambil
    dan yang paling penting adalah KAPAN Sistemnya BERHENTI?
    parameter apa yang menentukan berhentiny?
    fitness? atau parameter yang lain?

    solusi :

    best fit setiap generasi di simpan dalam variabel array
    kemudian bandingkan seluruh best fit
    jika best fit dengan fitness paling besar dan paling banyak barang yang terambil
    berarti itu solusi optimal

    */
    $POPULATION = [];
    $POPULATION_SIZE = 200; //pop size
    $MUTATION_RATE = 0.01; //peluang mutasi
    $CROSSOVER_RATE = 0.2; //peluang persilangan. bisa di definisakan sendiri. bisa by system
    $DNA_SIZE = '';
    $GEN_COUNT = 1;
    $TEST_COUNT = 0;
    $BATASKONTAINER = 350;
    $bestGen = [];


    $ITEMS = []; // array untuk menyimpan nama,harga,dan berat barang
    $ITEMS[] = item("beras",80.0,30,0);
    $ITEMS[] = item("beras A",100.0,50,0);
    $ITEMS[] = item("beras B",20.0,30,0);
    $ITEMS[] = item("beras C",70.0,90,0);
    $ITEMS[] = item("beras D",110.0,20,0);
    $ITEMS[] = item("beras E",30.0,35,0);
    $ITEMS[] = item("jagung",90.0,15,0);
    $ITEMS[] = item("ubi",15.0,40,0);
    $ITEMS[] = item("kacang",45.0,40,0);
    $ITEMS[] = item("kentang",30.0,5,0);
    $ITEMS[] = item("wortel",40.0,10,0);
    $ITEMS[] = item("wortel 2",80.0,15,0);
    $ITEMS[] = item("wortel 3",90.0,12,0);
    $ITEMS[] = item("wortel 4",110.0,18,0);
    $ITEMS[] = item("beras A",100.0,50,0);
    $ITEMS[] = item("beras B",20.0,30,0);
    $ITEMS[] = item("beras C",70.0,90,0);
    $ITEMS[] = item("beras D",110.0,20,0);
    $ITEMS[] = item("beras E",30.0,35,0);
    $ITEMS[] = item("beras A",100.0,50,0);
    $ITEMS[] = item("beras B",20.0,30,0);
    $ITEMS[] = item("beras C",70.0,90,0);
    $ITEMS[] = item("beras D",110.0,20,0);
    $ITEMS[] = item("beras E",30.0,35,0);

    foreach ($ITEMS as $key => $value) {
        echo $value->name." dengan berat : ";
        echo $value->weight." Kg";
        echo "\r\n";
    }
    echo "\r\n";
    genInitPopulation();

    echo "\r\n";
    echo "KROMOSOM TERBAIK TIAP GENERASI";
    echo "\r\n";
    echo "-----------------------------------";
    echo "\r\n";
    $i = 0;
    while ($i < 200) {
        naturalSelection();
        recreatePopulation();
        $i++;
    }
    echo "\r\n";
    echo "Yang Terbaik";
    echo "\r\n";
    echo "-----------------------------------";
    echo "\r\n";
    $maxGene = $bestGen[0][1];
    $bestGenMax = $bestGen[0][0];
    for ($i=0; $i < count($bestGen) ; $i++) {
        if($maxGene < $bestGen[$i][1]){
            $maxGene = $bestGen[$i][1];
            $bestGenMax = $bestGen[$i][0];
        }
    }
    echo $bestGenMax;
    echo " dengan Fitness : ".$maxGene;


    //=========================================FUNCTIONS=======================
    function item($name,$survivalPoints,$weight){
        $item = new stdClass();
        $item->name = $name;
        $item->survivalPoints = $survivalPoints;
        $item->weight = $weight;

        return $item;
    }

    function genInitPopulation(){
        global $POPULATION,$POPULATION_SIZE;
        //200
        for ($i=0; $i < $POPULATION_SIZE ; $i++) {
            $individual = randomIndividual();
            array_push($POPULATION, array($individual,fitness($individual)));
        }
    }

    function randomIndividual(){
        global $ITEMS,$POPULATION,$BATASKONTAINER;
        $gene = 0;
        $kromosom = '';
        for ($i=0; $i < count($ITEMS) ; $i++) {
            if($ITEMS[$i]->weight >= $BATASKONTAINER){ //jika melebihi batas kontainer kasih 0 genotype
                $gene = 0;
            }else{
                $gene = newBiner();
            }
            //bagian penggabungan
            $kromosom .= $gene;
        }
        return $kromosom;
    }

    function newBiner(){
        $random = random_int( 0,1);
        return $random;
    }

    function fitness($individual){
        global $ITEMS,$GEN_COUNT,$TEST_COUNT,$BATASKONTAINER;
        $TEST_COUNT++;
        $fitness = 0;
        $total_fitness = 0;
        $berat = 0;
        $total_berat = 0;
        for ($i=0; $i < count($ITEMS); $i++) {
            $fitness = $ITEMS[$i]->survivalPoints * $individual[$i];
            $total_fitness += $fitness;
            $berat = $ITEMS[$i]->weight * $individual[$i];
            $total_berat += $berat;
        }

        if($total_berat > $BATASKONTAINER){ //jika melebihi BATASKONTAINER
            $total_fitness = 0;
        }

        return $total_fitness;
    }

    function urutkan($a, $b){
        if($a[1] == $b[1]) return 0;
        return ($a[1] > $b[1]) ? -1 : 1;
    }

    function naturalSelection(){
        global $POPULATION,$POPULATION_SIZE,$GEN_COUNT,$bestGen;

        usort($POPULATION, "urutkan");
        array_splice($POPULATION, ceil($POPULATION_SIZE/2));
        array_push($bestGen, array($POPULATION[0][0], $POPULATION[0][1]));
        // echo 'Best fit gen '.$GEN_COUNT.': '.$POPULATION[0][0].' (Fitness : '.$POPULATION[0][1].')'."\n";
        // echo '<br>';

    }

    function recreatePopulation(){
        global $POPULATION, $POPULATION_SIZE, $GEN_COUNT;
        //echo '* Recreating population by reproducing randomly...'."\n";
        $GEN_COUNT++;
        $c = count($POPULATION);
        for ($i=$c; $i<$POPULATION_SIZE; $i++) {
            $a = rand(0, $c-1);
            $b = rand(0, $c-1);
            array_push($POPULATION, reproduction($POPULATION[$a][0], $POPULATION[$b][0]));
        }
    }

    function reproduction($ia, $ib){
        global $DNA_SIZE, $ITEMS;
        $jumlahItems = count($ITEMS);
        $crosspoint   = rand(0, $jumlahItems-1);
        $ia_before_cp = substr($ia, 0, $crosspoint);
        //$ia_after_cp  = substr($ia[0], $crosspoint);
        //$ib_before_cp = substr($ib[0], 0, $crosspoint);
        $ib_after_cp  = substr($ib, $crosspoint);
        $child = $ia_before_cp.$ib_after_cp;
        $child = mutate($child);
        return array($child, fitness($child));
    }

    function mutate($s) {
        global $DNA_SIZE, $ITEMS, $MUTATION_RATE;
        $sample = randomIndividual();
        for ($i=0; $i<count($ITEMS); $i++) {
            if (rand(0,100) == 100) {
                // $s[$i] = $sample[$i];
                if($s[$i] == 0){
                    $s[$i] = 0;
                }else{
                    $s[$i] = 1;
                }
            }
        }
        return $s;
    }

    function averageFitness(){
        global $POPULATION, $POPULATION_SIZE, $ITEMS;
        $fitness = 0;

        for ($i=0; $i < $POPULATION_SIZE ; $i++) {
            $fitness += $POPULATION[$i][1];
        }

        $averageFitness = $fitness / count($POPULATION);

        return $averageFitness;
    }
