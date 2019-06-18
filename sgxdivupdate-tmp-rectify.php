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

//$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=G01nvest");
$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=******");

if (!$conn) {
  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
  $txt = "Unable to connect to database.\n";
  fwrite($logfile, $txt);
  //exit;
}
$find = pg_query($conn, "SELECT * FROM stock_div WHERE ret IS NULL OR ret=0");
while($res = pg_fetch_array($find))	
{
	$currency = substr($res["div_value"],0,3);
	$sgd_value = str_replace("USD", "", $res["div_value"]);
	if(!empty($currency)&&$currency!='SGD')
	{
		$sgd_value = convertCurrency($sgd_value, $currency, 'SGD');
	}
	$latestprice = "SELECT close, date, code from stock_data WHERE code='".$res["code"]."' AND close>0 ORDER BY date DESC LIMIT 1";
	$price_res = pg_query($conn, $latestprice);
	$arr = pg_fetch_array($price_res);
	$ret = ($arr["close"]!=0)?floatval(str_replace("SGD","",$sgd_value))/floatval($arr["close"]):0;
	$sql="UPDATE stock_div SET ret='".$ret."' WHERE key='".$res["key"]."'";

	
	$result = pg_query($conn, $sql);
	if (!$result) 
	{
	  echo "can't execute query: ".$sql;
	}
}
function isExist($key){
	$sql="SELECT * FROM stock_div WHERE key='$key'";
	$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");

	if (!$conn) {
	  $logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
	  $txt = "Unable to connect to database.\n";
	  fwrite($logfile, $txt);
	  return false;
	}
	$result = pg_query($conn, $sql);
	if (!$result) 
	{
		$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
		$txt = "Query cannot be executed.\n".pg_result_error($result)."\n";
		fwrite($logfile, $txt);
		return false;
	}
	else
	{
		return (pg_num_rows($result)>0);
	}
}
function convertCurrency($amount, $from, $to)
{
    $url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
    $data = file_get_contents($url);
    preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
    $converted = preg_replace("/[^0-9.]/", "", $converted[1]);
    return round($converted, 4);
}
?>
