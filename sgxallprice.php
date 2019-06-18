<?php 
if(date('w', time()+15*3600)==0 || date('w', time()+15*3600)==6 ||date('H', time()+15*3600)>17||date('H', time()+15*3600)<9){
$logfile = fopen(dirname(__FILE__)."/"."logfile-".date('Y-m-d', time()+15*3600).".txt", "a+");
		  		$txt = "not a trading hour\n";
		  		fwrite($logfile, $txt);
//echo "not a trading hour";	
exit();
}
//exit();
$data = file_get_contents("http://sgx.com/JsonRead/JsonData?qryId=RStock&timeout=60");
$data = substr($data, 5, strlen($data)-1);
//$data = str_replace("\$", "\\\$", $data);
$data = str_replace("'", "\"", $data);
//$data = str_replace("identifier:'ID', label:'As at 15-03-2016 10:32 AM',", "", $data);
//echo $data;
$s = array("identifier:", "label:","SC:","PV:","CLO:","BL:","items:","ID:","N:","SIP:","NC:","R:","I:","M:","LT:","C:","VL:","BV:","B:","S:","SV:","O:","H:","L:","V:","PTD:","EX:","EJ:","P:","P_:","V_:");
$r = array("\"identifier\":", "\"label\":","\"SC\":","\"PV\":","\"CLO\":","\"BL\":","\"items\":","\"ID\":","\"N\":","\"SIP\":","\"NC\":","\"R\":","\"I\":","\"M\":","\"LT\":","\"C\":","\"VL\":","\"BV\":","\"B\":","\"S\":","\"SV\":","\"O\":","\"H\":","\"L\":","\"V\":","\"PTD\":","\"EX\":","\"EJ\":","\"P\":","\"P_\":","\"V_\":");
$data = str_replace($s, $r, $data);

$jsondata = json_decode($data);

$recorddatetime = date('Y-m-d H:i:s',strtotime(str_replace("As at ", "", $jsondata->label)));

foreach($jsondata->items as $stock){
	$sql="INSERT INTO sgx5min (id, code, name, lastprice, change, buyvol, buyprice, sellvol, sellprice, totalvol, value, 
	sectorcode, prevclose, ptd, recorddatetime, changepercent, open, high, low)
				VALUES ('".$stock->ID."',
				'".$stock->NC."', '".$stock->N."', ".(empty($stock->LT)?0:$stock->LT).", 
				".(empty($stock->C)?0:$stock->C).", ".(empty($stock->BV)?0:$stock->BV).", 
				".(empty($stock->B)?0:$stock->B).", ".(empty($stock->SV)?0:$stock->SV).",
				".(empty($stock->S)?0:$stock->S).", ".(empty($stock->VL)?0:$stock->VL).", 
				".(empty($stock->V)?0:$stock->V).", '".(empty($stock->SC)?0:$stock->SC)."', 
				".(empty($stock->PV)?0:$stock->PV).", '".date("Y-m-d",strtotime($stock->PTD))."', 
				'".$recorddatetime."', ".(empty($stock->P)?0:$stock->P).", ".(empty($stock->O)?0:$stock->O).", 
				".(empty($stock->H)?0:$stock->H).", ".(empty($stock->L)?0:$stock->L).");";
				$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");

			$result1 = pg_query($conn, $sql);
			$result2 = pg_query($conn2, $sql);
}
?>
