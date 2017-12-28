<?php
// Worker 与 Stackable
class SQLQuery extends Stackable {

    public function __construct($sql) {
        $this->sql = $sql;
    }

    public function run() {
        $dbh = $this->worker->getConnection();
        $row = $dbh->query($this->sql);
        while ($member = $row->fetch(PDO::FETCH_ASSOC)) {
            print_r($member);
        }
    }

}

class ExampleWorker extends Worker {
    public static $dbh;
    public function __construct($name) {
    }

    /*
     * The run method should just prepare the environment for the work that is coming ...
     */
    public function run() {
        self::$dbh = new PDO('mysql:host=192.168.2.1;dbname=example', 'www', '123456');
    }
    public function getConnection() {
        return self::$dbh;
    }
}

$worker = new ExampleWorker("My Worker Thread");

$work = new SQLQuery('select * from members order by id desc limit 5');
$worker->stack($work);

$table1 = new SQLQuery('select * from demousers limit 2');
$worker->stack($table1);

$worker->start();
$worker->shutdown();