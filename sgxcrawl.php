<?php 
$stocks=array("A35","QL3", "QL2", "G3B", "ES3","QR9", "O9B","J0P", "S27", 
			  "IH3", "LF2", "P60", "K6K","JC5",
"CW4","H1P","QS0","N2F","KT3","J0M","H1N", "D07","MT7","JC6","JC7","O9P","O9Q","H1M","N6L","KT4","G1K","G1M","H1O","H1Q","J0R","NF7","NF3","NF5","P2P",
//here onwards STI components:
"A17U",
"BN4",
"BS6",
"C07",
"C09",
"C31",
"C38U",
"C52",
"C6L",
"CC3",
"D05",
"E5H",
"F34",
"G13",
"H78",
"MC0",
"N21",
"NS8U",
"O39",
"S51",
"S58",
"S59",
"S63",
"S68",
"T39",
"U11",
"U14",
"U96",
"Y92",
"Z74"
);
$date =date('Y-m-d',time());
$logfilename = "logfile-".$date.".txt";
if(date('w',time())==0)
{
	$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
                                          $txt = "today is Sunday!.\n";
                                          fwrite($logfile, $txt);
	exit();
}

if(date('w',time())==6)
{
	$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
                                          $txt = "today is Saturday!.\n";
                                          fwrite($logfile, $txt);
	exit();
}

$stockjson = file_get_contents("http://www.sgx.com/JsonRead/JsonData?qryId=REtf&timeout=60");

$search=array("{}&& ",":", ",","'","{","}", "\"\"'\"\"'\"\"", "\"[\"","\"]\"","\"\"\'\"", "\"'\"\"", "\"\"'\"", "\"\",\"\"");
$replace=array("","\":\"", "\",\"","\"'\"","\"{\"","\"}\"","\"\"", "[","]","\"\"", "\"", "\"",",");

$forquot = stripslashes($stockjson);
$trimmed= str_replace($search, $replace,utf8_encode($stockjson));
$jsonObj = json_decode("{\"".substr($trimmed, strpos($trimmed,"items"), -1));
if(!is_object($jsonObj))
{
	$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
	$text = "json is not an object;\n";
	$text2 = 'JSON ERROR: '.  json_last_error_msg(). "\n";
	fwrite($logfile, $text);
	fwrite($logfile, $text2);
}
else
{
	foreach($jsonObj->items as $stock) 
	{
		try
		{
		  	if(!in_array($stock->NC,$stocks))continue;
			
			if(is_numeric($stock->O)&&is_numeric($stock->H)&&is_numeric($stock->L)&&is_numeric($stock->LT)&&is_numeric($stock->VL))
			{
				//skip if values = 0:
				if($stock->H=="0"||$stock->L=="0"||$stock->VL=="0"||$stock->LT=="0")
				{
					$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
					$txt = "stock code : ".$stock->NC." has no trading data today.\n";
				  	fwrite($logfile, $txt);
					continue;
				}
				else
				{
					//create SQL query string
					$sql="INSERT INTO stock_data (date, code, open, high, low, close, volume)  
					VALUES ('".$date."', 
					'".$stock->NC."', ".$stock->O.", ".$stock->H.", ".$stock->L.", ".$stock->LT.", ".$stock->VL.");\n";
			
					$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=******");
					$sqlcheck = "SELECT * FROM stock_data WHERE code='".$stock->NC."' AND date = '".$date."'";
					$rowcount = pg_query($conn, $sqlcheck);
					if($rowcount && pg_num_rows($rowcount)>0)
					{
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
					if (!$result) 
					{
					  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
					  $txt = "Query cannot be executed.\n".pg_result_error($result)."\n";
					  fwrite($logfile, $txt);
					}
			    }
			}
			else
			{
				$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
				$txt = "No trading data for stock code: $stock->.\n";
				fwrite($logfile, $txt);
			}
		}
		catch(Exception $e) 
		{
			$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
			$text = "error occured at crawling code: ".$code.", URL: ".$url.";\n";
			$text2 = 'Caught exception: '.  $e->getMessage(). "\n";
			fwrite($logfile, $text);
			fwrite($logfile, $text2);
		} 
	}
}
$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
$txt = "SGX Crawler task completed at ".date('Y-m-d H:i:s',time()).".\n";
fwrite($logfile, $txt);
fclose($logfile);
?>
