<?php 
$stocks=array("A35","QL3", "QL2", "G3B", "ES3","QR9", "O9B","J0P", "S27", 
			  "IH3", "LF2", "P60", "K6K","JC5",
"CW4","H1P","QS0","N2F","KT3","J0M","H1N");
$date ='2015-10-29';
$logfilename = "logfile-".$date.".txt";
$stockjson = file_get_contents("http://www.sgx.com/JsonRead/JsonData?qryId=REtf&timeout=60");
//$search = array("{}&& ","'","identifier:","label:","items:", "ID:","N:","SIP:","NC:", "R:", "I:","M:", "LT:","VL:", "BV:", "B:","S:","SV:","SC:","PV:","IOPV:","CX:","BL:","P:","P_:","V_:", "C:","O:","H:","L:","V:");
//$replace = array("","\"","\"identifier\":","\"label\":","\"items\":", "\"ID\":","\"N\":","\"SIP\":","\"NC\":", "\"R\":", "\"I\":","\"M\":", "\"LT\":", "\"VL\":", "\"BV\":", "\"B\":","\"S\":","\"SV\":","\"SC\":","\"PV\":","\"IOPV\":","\"CX\":","\"BL\":","\"P\":","\"P_\":","\"V_\":","\"C\":","\"O\":","\"H\":","\"L\":","\"V\":");
$search=array("{}&& ",":", ",","'","{","}", "\"\"'\"\"'\"\"", "\"[\"","\"]\"","\"\"\'\"", "\"'\"\"", "\"\"'\"", "\"\",\"\"");
$replace=array("","\":\"", "\",\"","\"'\"","\"{\"","\"}\"","\"\"", "[","]","\"\"", "\"", "\"",",");

$forquot = stripslashes($stockjson);
$trimmed= str_replace($search, $replace,utf8_encode($stockjson));
$jsonObj = json_decode("{\"".substr($trimmed, strpos($trimmed,"items"), -1));
if(!is_object($jsonObj)){
	$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
	$text = "json is not an object;\n";
	$text2 = 'JSON ERROR: '.  json_last_error_msg(). "\n";
	fwrite($logfile, $text);
	fwrite($logfile, $text2);
}
else{
	foreach($jsonObj->items as $stock) {
	  try 
	  {
		/*$html = file_get_html('http://www.sharesinv.com/'.$stock.'/');
		$date = explode(" ",$html->find('td[class=last_update h4]')[0]->childNodes(0)->innertext()); //date
		$datearr = explode("-", $date[0]);
		$newdate = "20".$datearr[2]."-".$datearr[1]."-".$datearr[0];
		$open = $html->find('table[class=trading_stat 1 .0m]')[0]->childNodes(0)->childNodes(1)->childNodes(0)->innertext(); //Open
		$high = $html->find('table[class=trading_stat 1 .0m]')[0]->childNodes(1)->childNodes(1)->childNodes(0)->innertext(); //high
		$low = $html->find('table[class=trading_stat 1 .0m]')[0]->childNodes(2)->childNodes(1)->childNodes(0)->innertext(); //low
		$last = $html->find('table[class=trading_stat 1 .0m]')[0]->childNodes(3)->childNodes(1)->childNodes(0)->innertext(); //last
		$vol = $html->find('table[class=trading_stat 1 .0m]')[0]->childNodes(4)->childNodes(3)->childNodes(0)->innertext(); //vol*/
		if(!in_array($stock->NC,$stocks))continue;
		
		if(is_numeric($stock->O)&&is_numeric($stock->H)&&is_numeric($stock->L)&&is_numeric($stock->LT)&&is_numeric($stock->VL)){
				//create SQL query string
				$sql="INSERT INTO stock_data (date, code, open, high, low, close, volume)  
				VALUES ('".$date."', 
				'".$stock->NC."', ".$stock->O.", ".$stock->H.", ".$stock->L.", ".$stock->LT.", ".$stock->VL.");\n";
			
				$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");
			$sqlcheck = "SELECT * FROM stock_data WHERE code='".$stock->NC."' AND date = '".$date."'";
			$rowcount = pg_query($conn, $sqlcheck);
			if($rowcount && pg_num_rows($rowcount)>0){
				$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
				  $txt = "stock code : ".$stock->NC." is already available in database.\n";
				  fwrite($logfile, $txt);
				continue;
			}
				if (!$conn) {
				  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
				  $txt = "Unable to connect to database.\n";
				  fwrite($logfile, $txt);
				  //exit;
				}
				$result = pg_query($conn, $sql);
				if (!$result) {
				  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
				  $txt = "Query cannot be executed.\n".pg_result_error($result)."\n";
				  fwrite($logfile, $txt);
				}
	
			}else{
				$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
				  $txt = "No trading data for stock code: $stock.\n";
				  fwrite($logfile, $txt);
			}
		}catch(Exception $e) {
			$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
			$text = "error occured at crawling code: ".$code.", URL: ".$url.";\n";
			$text2 = 'Caught exception: '.  $e->getMessage(). "\n";
			fwrite($logfile, $text);
			fwrite($logfile, $text2);
		} 
	}
}
?>