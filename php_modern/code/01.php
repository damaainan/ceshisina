<?php 
interface Printable{
	public function print(string $name);
}

(function (Printable $printer, string $name){
	$printer->print($name);
})(new class("Hello") implements Printable{
	private $greeting;

	public function __construct(string $greeting){
		$this->greeting = $greeting;
	}

	public function print(string $name){
		print("{$this->greeting}, $name!" . PHP_EOL);
	}
}, "Gua");




if($recSql){
    $exWhere = $exWhere . " AND (" . $recSql . ") ";
}
if($s_phones){
    $service_phone = array_diff($service_phone, $s_phones);
    $exWhere = "AND (1=1 " .$exWhere . " AND recommender_phone not in (" . DbHandler::escape_array($service_phone) . ")) OR (1=1 " . $exWhere . " AND recommender_phone in (" . DbHandler::escape_array($s_phones) . ")) " ;
}


if($s_phones){
    $service_phone = array_diff($service_phone, $s_phones);
    if($recSql){
    	$orwhere = " ((" . $recSql . ") or recommender_phone in (" . DbHandler::escape_array($s_phones) . ")) " ;
    }else{
    	$orwhere = " recommender_phone in (" . DbHandler::escape_array($s_phones) . ") " ;
    }
}else{
	if($recSql){
		$orwhere = " (" . $recSql . ") ";
	}
}
$exWhere .= $orwhere;

SELECT	* FROM	shop_join WHERE
	( 1 = 1 and  ( cell_phone = '13995471567' OR recommender_phone = '13995471567' ) AND shop_status != 2 ) 
	OR 
	(
	(( (recommender_phone in ('18649609369','18621062379','18551626550','18664641353','18911239608','17332639035','13581573010','18500419333')  or shop_type=3 )   AND shop_status = 2)
	and ( ( cell_phone = '13995471567' OR recommender_phone = '13995471567' ) AND shop_status = 2 ))
	AND recommender_phone NOT IN ( '15801252395', '18638113355', '15108295438', '18999999258', '18655143021', '13113143911', '13985034846', '18817140888', '13756887787', '13807917771', '17727312903' ) 
	) 
	OR 
	(
	(( (recommender_phone in ('18649609369','18621062379','18551626550','18664641353','18911239608','17332639035','13581573010','18500419333')  or shop_type=3  ) AND shop_status = 2) or (( cell_phone = '13995471567' OR recommender_phone = '13995471567' ) 
	AND shop_status = 2 ))
	AND recommender_phone IN ('18983637778') 
	)


if($exWhere){
    if($s_phones){
        $service_phone = array_diff($service_phone, $s_phones);
        $exWhere = " AND ( 1=1 " . $exWhere . " AND shop_status != 2 )" . " 
        OR ((((" . $lexWhere . ")  AND shop_status = 2 ) and ( 1=1 and " . $exWhere . " AND shop_status = 2 ))  AND recommender_phone not in (" . DbHandler::escape_array($service_phone) . ") ) 
        OR(( ((" . $lexWhere . ") AND shop_status = 2 ) or ( 1=1 and" . $exWhere . " AND shop_status = 2 ) AND recommender_phone in (" . DbHandler::escape_array($s_phones) . ") ) ";

    }else{
        $exWhere = " AND ( 1=1 " . $exWhere . " AND shop_status != 2 )" . " 
        OR ((((" . $lexWhere . ")  AND shop_status = 2 ) and ( 1=1 and " . $exWhere . " AND shop_status = 2 ))  AND recommender_phone not in (" . DbHandler::escape_array($service_phone) . ") ) 
        OR( ((" . $lexWhere . ") AND shop_status = 2 ) or ( 1=1 and" . $exWhere . " AND shop_status = 2 ) ) ";

    }
}else{
    if($s_phones){
        $service_phone = array_diff($service_phone, $s_phones);
        $exWhere = " AND ( shop_status != 2 )" . " OR ((" . $lexWhere . ") AND shop_status = 2 AND recommender_phone not in (" . DbHandler::escape_array($service_phone) . ")) OR ((" . $lexWhere . ") and shop_status = 2 AND recommender_phone in (" . DbHandler::escape_array($s_phones) . ")) ";
    }else{
        $exWhere = " AND ( shop_status != 2 )" . " OR ((" . $lexWhere . ") AND shop_status = 2) ";
    }
}