<?php 
$stocks=array("A35"=>"ABF SPORE BOND INDEX FUND ETF",
"KT4"=>"DBXT CSI300 ETF 10",
"J0R"=>"DBXT MSCI RUSSIA CAP 25 ETF 10",
"N2F"=>"DBXT MSCI ASIA EXJAP HDY ETF10",
"KT3"=>"DBXT STOXX GLOB DIV 100 ETF 10",
"O9Q"=>"ISHARES ASIA LC 1-3Y BOND ETF",
"N6L"=>"ISHARES ASIA LOC CUR BOND ETF",
"QL2"=>"ISHARES USD ASIA BOND ETF",
"O9P"=>"ISHARES USD ASIA HY BOND ETF",
"NF3"=>"LYXOR ETF ASIA INFRASTRUCT 10",
"NF5"=>"LYXOR ETF ASIA FINANCIALS 10",
"NF7"=>"LYXOR ETF ASIA MATERIALS 10",
"MT7"=>"LYXOR ETF ASIA REAL ESTATE 10",
"JC6"=>"LYXOR ETF DOW JONES IA 10",
"H1M"=>"LYXOR ETF EASTERN EUROPE 10",//delisted??
"H1N"=>"LYXOR ETF MSCI EMERGING MKT 10",
"G1K"=>"LYXOR ETF MSCI ASIA APEX 50 10",
"G1M"=>"LYXOR ETF MSCI MALAYSIA 10",
"P2P"=>"LYXOR ETF MSCI THAILAND 10",
"H1P"=>"LYXOR ETF MSCI WORLD 10",
"H1Q"=>"LYXOR ETF NASDAQ100 10",
"JC7"=>"LYXOR ETF RUSSIA 10",
"G1N"=>"LYXOR INDIA NIFTY ETF 10",
"H1O"=>"LYXORETF MSCI EM LATIN AMERICA",
"G3B"=>"NIKKO AM SINGAPORE STI ETF",
"D07"=>"SPDR DJIA ETF TRUST",
"S27"=>"SPDR S&P 500 ETF TRUST",
"ES3"=>"STI ETF");
/*“A35” => ”ABF SPORE BOND INDEX FUND ETF",
“KT4” => ”DBXT CSI300 ETF 10",
“J0R” => ”DBXT MSCI RUSSIA CAP 25 ETF 10",
“N2F” => ”DBXT MSCI ASIA EXJAP HDY ETF10",
“KT3” => ”DBXT STOXX GLOB DIV 100 ETF 10",
“O9Q” => ”ISHARES ASIA LC 1-3Y BOND ETF",
“N6L” => ”ISHARES ASIA LOC CUR BOND ETF",
"QL2"=>"ISHARES USD ASIA BOND ETF",
“O9P” => ”ISHARES USD ASIA HY BOND ETF",
“NF3” => ”LYXOR ETF ASIA INFRASTRUCT 10",
“NF5” => ”LYXOR ETF ASIA FINANCIALS 10",
“NF7” => ”LYXOR ETF ASIA MATERIALS 10",
“MT7” => ”LYXOR ETF ASIA REAL ESTATE 10",
“JC6” => ”LYXOR ETF DOW JONES IA 10",
“H1M” => ”LYXOR ETF EASTERN EUROPE 10" delisted???, 
”H1N” => ”LYXOR ETF MSCI EMERGING MKT 10",
“G1K” => ”LYXOR ETF MSCI ASIA APEX 50 10",
“G1M” => ”LYXOR ETF MSCI MALAYSIA 10",
“P2P” => ”LYXOR ETF MSCI THAILAND 10",
“H1P” => ”LYXOR ETF MSCI WORLD 10",
“H1Q” => ”LYXOR ETF NASDAQ100 10",
“JC7” => ”LYXOR ETF RUSSIA 10",
“G1N” => ”LYXOR INDIA NIFTY ETF 10",
“H1O” => ”LYXORETF MSCI EM LATIN AMERICA",
“G3B” => ”NIKKO AM SINGAPORE STI ETF",
“D07” => ”SPDR DJIA ETF TRUST",
“S27” => “SPDR S&P 500 ETF TRUST"
“ES3” => “STI ETF"*/
$date =date('Y-m-d',time());
$logfilename = "logfile-div-".$date.".txt";
foreach($stocks as $code=>$companyName)
{
	$url = "http://www.sgx.com/proxy/SgxDominoHttpProxy?timeout=100&dominoHost=http%3A%2F%2Finfofeed.sgx.com%2FApps%3FA%3DCow_CorporateInformation_Content%26B%3DCorpDistributionByCompanyNameCategoryAndExYear%26R_C%3D".rawurlencode($companyName)."%26C_T%3D20";
	$stockjson = file_get_contents($url);

	$trimmed = str_replace("{}&&", "", $stockjson);

	$jsonObj = json_decode($trimmed);
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
		$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");

		if (!$conn) 
		{
		  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
		  $txt = "Unable to connect to database.\n";
		  fwrite($logfile, $txt);
		}
		foreach($jsonObj->items as $stock) 
		{
			try 
			{		
				
				if(!empty($stock->CompanyName)&&!empty($stock->Annc_Type)&&!empty($stock->Ex_Date)&&!empty($stock->Record_Date)&&!empty($stock->DatePaid_Payable)&&!empty($stock->Particulars))
				{
					$str = $stock->Particulars;
					preg_match_all('!\d+(?:\.\d{1,6})?!', $str, $matches); //find dividend values
					$div_value = is_array($matches)?$matches[0][0]:0;
					$sgd_value =$div_value;
					if(isExist($stock->key))
					{
						$particulars_arr = explode(" ",$stock->Particulars);
						$currency = "";
						for($i=0;$i<sizeof($particulars_arr);$i++)
						{
							if($particulars_arr[$i]==$sgd_value)
							{
								$currency = $particulars_arr[$i-1];
								$div_value= $currency.$div_value;
							}
						}

						if(!empty($currency)&&$currency!='SGD'){
							$sgd_value = convertCurrency($sgd_value, $currency, 'SGD');
						}
						$latestprice = "SELECT close, date, code from stock_data WHERE code='".$code."' AND close>0 ORDER BY date DESC LIMIT 1";
						$price_res = pg_query($conn, $latestprice);
						$arr = pg_fetch_array($price_res);
						$ret = ($arr["close"]!=0)?floatval(str_replace("SGD","",$sgd_value))/floatval($arr["close"]):0;
						$sql="UPDATE stock_div SET div_value='".$div_value."', sgd_value='".$sgd_value."', ret='".$ret."' WHERE key='".$stock->key."'";
					}
					else
					{
						//create SQL query string
						$sql="INSERT INTO stock_div (name, type, ex_date, record_date, paid_date, particulars, key, code, div_value, sgd_value)  
						VALUES ('".$stock->CompanyName."', '".$stock->Annc_Type."', '".date('Y-m-d',strtotime($stock->Ex_Date))."', '".date('Y-m-d',strtotime($stock->Record_Date))."', '".date('Y-m-d',strtotime($stock->DatePaid_Payable))."','".$stock->Particulars."', '".$stock->key."', '".$code."','".$div_value."','".$sgd_value."');\n";
					}
					
					$result = pg_query($conn, $sql);
					if (!$result) 
					{
					  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
					  $txt = "Query cannot be executed.\n".pg_result_error($result)."\n";
					  fwrite($logfile, $txt);
					}
		
				}else
				{
					$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
					  $txt = "No div data for company: ".$companyName.".\n";
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
}
function isExist($key){
	$sql="SELECT * FROM stock_div WHERE key='$key'";
	$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=G01nvest");
	//$conn = pg_connect("host=goinvestweb.cloudapp.net port=5432 dbname=goinvest user=postgres password=G01nvest");

	if (!$conn) {
	  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
	  $txt = "Unable to connect to database.\n";
	  fwrite($logfile, $txt);
	  return false;
	}
	$result = pg_query($conn, $sql);
	if (!$result) {
	  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
	  $txt = "Query cannot be executed.\n".pg_result_error($result)."\n";
	  fwrite($logfile, $txt);
	  return false;
	}else{
		return (pg_num_rows($result)>0);
	}
}
function convertCurrency($amount, $from, $to){
    $url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
    $data = file_get_contents($url);
    preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
    $converted = preg_replace("/[^0-9.]/", "", $converted[1]);
    return round($converted, 4);
}
?>
