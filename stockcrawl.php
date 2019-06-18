<?php 
/*
Nikko AM Singapore STI ETF=== G3B.SG URL: https://sg.finance.yahoo.com/q/hp?s=G3B.SI&a=01&b=25&c=2009&d=08&e=23&f=2015&g=d
CIMB FTSE ASEAN40 ETF=== M62.SI URL: http://real-chart.finance.yahoo.com/table.csv?s=M62.SI&d=8&e=23&f=2015&g=d&a=10&b=30&c=2007&ignore=.csv
CIMB S&P Ethical Asia Pacific Dividend ETF === URL: http://real-chart.finance.yahoo.com/table.csv?s=P5P.SG&d=8&e=23&f=2015&g=d&a=3&b=23&c=2014&ignore=.csv
iShares Barclays Capital USD Asia High Yield Bond Index ETF ===SG2D83975482.SI URL: https://sg.finance.yahoo.com/q/hp?s=SG2D83975482.SI&a=07&b=25&c=2015&d=08&e=23&f=2015&g=d---- no data link available
ABF Singapore Bond Index ETF=== A35.SI URL: http://real-chart.finance.yahoo.com/table.csv?s=A35.SI&d=8&e=23&f=2015&g=d&a=11&b=31&c=2007&ignore=.csv	
Lyxor ETF FTSE EPRA/NAREIT Asia Ex-Japan === LYPN.DE URL: http://real-chart.finance.yahoo.com/table.csv?s=LYPN.DE&d=8&e=23&f=2015&g=d&a=2&b=25&c=2010&ignore=.csv

QL3      iShares Barclays Capital USD Asia High Yield Bond Index ETF
A35      ABF Singapore Bond Index ETF 
QL2      iShares J.P. Morgan USD Asia Credit Bond Index ETF 
G3B      Nikko AM Singapore STI ETF 
ES3      SPDR Straits Times Index ETF 
QR9      CIMB FTSE ASEAN40 ETF 
O9B      db x-trackers MSCI AC Asia-Pacific Ex Japan Index UCITS ETF 
J0P      db x-trackers MSCI World Index UCITS ETF 
S27      SPDRs® S&P 500® ETF 
IH3      db x-trackers MSCI Europe Index UCITS ETF (DR) 
LF2      db x-trackers MSCI Japan UCITS Index (DR) 
P60      Lyxor ETF MSCI AC Asia-Pacific Ex Japan 
K6K      db x-trackers S&P 500 UCITS ETF
JC5      Lyxor ETF MSCI Europe 
CW4     Lyxor ETF Japan (Topix) 
H1P      Lyxor ETF MSCI World 
QS0      CIMB S&P Ethical Asia Pacific Dividend ETF 
N2F      db x-trackers MSCI AC Asia Ex Japan High Dividend Yield Index UCITS ETF 
KT3      db x-trackers Stoxx® Global Select Dividend 100 UCITS ETF 
J0M      db x-trackers MSCI Emerging Markets Index UCITS ETF 
H1N      Lyxor ETF MSCI Emerging Markets
*/
$csvlink= "http://real-chart.finance.yahoo.com/table.csv?s={stockcode}&d=8&e=23&f=2015&g=d&a=10&b=30&c=2005&ignore=.csv";
$stocks=array("QL3.SI","A35.SI", "QL2.SI", "G3B.SI", "ES3.SI","QR9.SI", "O9B.SI", 
"J0P.SI", "S27.SI", "IH3.SI", "LF2.SI", "P60.SI", "K6K.SI","JC5.SI",
"CW4.SI","H1P.SI","QS0.SI","N2F.SI","KT3.SI","J0M.SI","H1N.SI");

 function csvimport($url, $code)
 {
	try
	{
		$file = fopen($url,"r");
		$arraytoImport = array();
		$sql ="";
		$column_headers = fgetcsv($file);
		while(! feof($file))
		{
			$csvrow = fgetcsv($file);
			
			//create SQL query string
			$sql.="INSERT INTO stock_data (date, code, open, high, low, close, volume, adj)  VALUES ('".date('Y-m-d',strtotime($csvrow[0]))."', 
			'".$code."', ".$csvrow[1].", ".$csvrow[2].", ".$csvrow[3].", ".$csvrow[4].", ".$csvrow[5].", ".$csvrow[6].");\n";
		}
		fclose($file);
		//PHP to pg-sql connection
		$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=******");
		if (!$conn) 
		{
		  echo "An error occurred.\n";
		  exit;
		}

		$result = pg_query($conn, $sql);
		if (!$result) 
		{
		  echo "An error occurred.\n";
		  exit;
		}
	}
	catch(Exception $e) 
	{
		echo "error occured at crawling code: ".$code.", URL: ".$url.";\n";
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
 }
 
 foreach($stocks as $v)
 {
	//eg call(working): csvimport("http://real-chart.finance.yahoo.com/table.csv?s=M62.SI&d=8&e=23&f=2015&g=d&a=10&b=30&c=2007&ignore=.csv", "M62.SI");
	//csvimport("http://real-chart.finance.yahoo.com/table.csv?s={$v}&d=8&e=23&f=2015&g=d&a=10&b=30&c=2007&ignore=.csv", $v);
	$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=******");
	if (!$conn) {
		echo "An error occurred.\n";
		exit;
	}

	$result = pg_query($conn, "SELECT COUNT(*) FROM stock_data where code='".$v."';");
	if (!$result) 
	{
	  echo "can't find data.code:{$v}\n";
	  exit;
	}
	else
	{
		$row = pg_fetch_array($result);
		echo "row count for code {$v} = ".$row[0];
	}
 }
?>
