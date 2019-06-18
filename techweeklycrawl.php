<?php
set_time_limit (0);
/*http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=2&e=26&f=2016&g=d&a=2&b=25&c=2016&ignore=.csv
*/

//get today date:
$dayTo = date('j', time()+15*3600);
$mthTo = date('n', time()+15*3600)-1;
$yrTo = date('Y', time()+15*3600);
$yrFrom = date('Y', time()-8*365*24*3600+15*3600);
$mthFrom = date('n', time()-8*365*24*3600+15*3600)-1;
$dayFrom = date('j', time()-8*365*24*3600+15*3600);

$sql ="";


$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=******");
$readcode = pg_query($conn, "SELECT DISTINCT code from sgx5min");
while($row = pg_fetch_array($readcode)){
	try
	{
		$url= "http://real-chart.finance.yahoo.com/table.csv?s=".$row["code"].".SI&d=03&e=".$dayTo."&f=".$yrTo."&g=w&a=".$mthFrom."&b=".$dayFrom."&c=".$yrFrom."&ignore=.csv";
		$eachfile = file_get_contents($url);
		$lines = explode("\n", $eachfile);
		foreach($lines as $key=>$data) 
		{
			try
			{
				//only when date and code are exist then create SQL query string
				//your sql here need to join the previous $sql, if not only the last $sql will execute.
				//but for your daily crawl, i mean subsequent run just $sql = willdo, coz you non eed to join many sql queries. one day only run one sql insert one data
				if($key==0)continue;
				
				$csvrow = str_getcsv($data);
				if(empty($csvrow[1])||empty($csvrow[2])||empty($csvrow[3])||empty($csvrow[4]))continue;
				$sql="INSERT INTO weekly_data (date, open, high, low, close, vol, adjclose, code)
				VALUES ('".date("Y-m-d", strtotime($csvrow[0]))."',
				'".$csvrow[1]."', ".$csvrow[2].", ".$csvrow[3].", ".$csvrow[4].", ".$csvrow[5].",".$csvrow[6].", '".$row["code"]."');";
					

				$res = pg_query($conn, $sql);
				if(!$res){
					$logfile = fopen(dirname(__FILE__)."/"."techcrawllog.txt", "a+");
					$text = date("Y-m-d H:i:s", time()+15*3600).": error occured at crawling URL: ".$url.";\nStock Code: ".$row["code"]. "\n";
					$text .= "postgres SQL error: ". pg_last_error($conn). "\n";
					fwrite($logfile, $text);
				}
					
			}
			catch(Exception $e) 
			{
				$logfile = fopen(dirname(__FILE__)."/"."techcrawllog.txt", "a+");
				$text = date("Y-m-d H:i:s", time()+15*3600).": error occured at crawling URL: ".$url.";\nStock Code: ".$row["code"]. "\n";
				$text .= 'Caught exception: '.  $e->getMessage(). "\n";
				$text .= "postgres SQL error: ". pg_last_error($conn). "\n";
				fwrite($logfile, $text);
			}
			
		}
	}catch(Exception $e) 
	{
		$logfile = fopen(dirname(__FILE__)."/"."techcrawllog.txt", "a+");
		$text = date("Y-m-d H:i:s", time()+15*3600).": failed to read data with stock Code: ".$row["code"]. "\n";
		$text .= 'Caught exception: '.  $e->getMessage(). "\n";
		fwrite($logfile, $text);
	}
}
