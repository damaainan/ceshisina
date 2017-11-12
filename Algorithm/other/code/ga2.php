<?php 

/*
    prosedur algoritma genetika
    1. tentukan parameter
        - popsize populasi = berapa banyak populasi yang akan di generate
        - peluang crossover
        - peluang mutasi
    2. pengkodean/representasi kromosom
        - diubah ke 0 1 atau bentuk yang lain sesuai dengan aturan
    3. hitung fitness untuk menentukan parent ( ambil 2 terbaik)
    4. crossover
        - penentuan brp titik yang akan di crossover
        - hasilnya di seleksi untuk menghasilkan yang paling optimal untuk di pilih jadi parent
    5. mutasi 
        - nilai optimal yang didapat dari crossover di mutasi ( 0 jadi 1 dan sebaliknya)
    6. roulette wheel
        - semua hasil mutasi yang nilainya mendekati 1 di roulette
        - dilihat yang mana yan paling banyak muat ke tangki
 */

/*
    1. misal untuk penentuan parameter : 
        - popsize = 100
        - peluang crossover : 0.2
        - peluang mutasi : 0.4
    2. Pengkodean
 */

$jumlahBarang = 5;
$harga = [20,5,50,30,53];

function generateKromosome($panjangKromosome)
{
    $randomBool = rand(0,1);
    for ($i=0; $i < $panjangKromosome -1 ; $i++) { 
        $randomBool .= rand(0,1);
    }
    return $randomBool;
}


$populasi = array();
for ($i=0; $i < $jumlahBarang ; $i++) { 
    $populasi[$i] = generateKromosome($jumlahBarang);
}


echo "Kromosom-kromosom : ";
echo "\r\n";
echo "\r\n";
foreach ($populasi as $key => $value) {
    echo $value;
    echo "\r\n";
}


/*$nilai = array();
$nilai[0] = [0,1,1,0];
$nilai[1] = [1,1,1,1];
$nilai[2] = [1,0,1,0];
$nilai[3] = [1,0,1,0];*/

$fitness = array();
//$fitness = $fitness + nilai baris;
for ($baris = 0; $baris < count($populasi) ; $baris++) {
    $fitness[$baris] = 0;
    for ($kolom = 0; $kolom < count($populasi) ; $kolom++) {
        $fitness[$baris] = $fitness[$baris] + ($populasi[$baris][$kolom] * $harga[$baris]);
    }
}
echo "\r\n";
echo "Fitness masing-masing kromosome : ";
echo "\r\n";
echo "\r\n";
foreach ($fitness as $key => $value) {
    echo $value;
    echo "\r\n";
}
$fitness_sementara = $fitness;
$populasi_sementara = $populasi;
/*$max1 = $fitness_sementara[0];
$max2 = $fitness_sementara[1];
$gen1 = 0;
$gen2 = 0;
for ($i=1; $i < count($populasi) ; $i++) { 
    if($fitness_sementara[$i] > $max1){
        $max1 = $fitness_sementara[$i];
    }
}*/

$temp_fitness = 0;
$temp_populasi = 0;
$k=0;
while($k <= count($populasi)-2)
{
    $i=0;
    while($i <=count($populasi)-2 - $k)
    {
        if ($fitness_sementara[$i] < $fitness_sementara[$i+1])
        {
            //tukar fitness
            $temp_fitness = $fitness_sementara[$i];
            $fitness_sementara[$i] = $fitness_sementara[$i+1];
            $fitness_sementara[$i+1] = $temp_fitness;

            //tukar populasi
            $temp_populasi = $populasi_sementara[$i];
            $populasi_sementara[$i] = $populasi_sementara[$i+1];
            $populasi_sementara[$i+1] = $temp_populasi;
        }
        $i++;
    }
    $k++;
}

echo "\r\n";
echo "Gen Terpilih : ";
echo "\r\n";
echo "\r\n";
$parent1 = $fitness_sementara[0];
$kromosome1 = $populasi_sementara[0];
$parent2 = $fitness_sementara[1];
$kromosome2 = $populasi_sementara[1];
echo $parent1;
echo "\r\n";
echo "Kromosom 1 terpilih : ".$kromosome1;
echo "\r\n";
echo "\r\n";
echo "\r\n";
echo $parent2;
echo "\r\n";
echo "Kromosom 2 terpilih : ".$kromosome2;

echo "\r\n";
echo "\r\n";
echo "Hasil Crossover : ";
$popSize = 50;
for ($i=0; $i < $popSize ; $i++) { 
    //hitung cross over
}