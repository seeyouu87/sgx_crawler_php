<?php
set_time_limit (0);
/*http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=2&e=26&f=2016&g=d&a=2&b=25&c=2016&ignore=.csv
*/
//get today date:
$dayTo = date('j', time()+15*3600);
$mthTo = date('n', time()+15*3600)-1;
$yrTo = date('Y', time()+15*3600);
$yrFrom = date('Y', time()-31*24*3600+15*3600);
$mthFrom = date('n', time()-31*24*3600+15*3600)-1;
$dayFrom = date('j', time()-31*24*3600+15*3600);

$url="http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&a=11&b=28&c=1987&d=03&e=8&f=2016&g=w&ignore=.csv";
//"http://chartapi.finance.yahoo.com/instrument/1.0/%5ESTI/chartdata;type=quote;range=1d/csv";
//"http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=".$mthTo."&e=".$dayTo."&f=".$yrTo."&g=d&a=".$mthFrom."&b=".$dayFrom."&c=".$yrFrom."&ignore=.csv";
//"http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=3&e=7&f=2016&g=d&a=1&b=28&c=2016&ignore=.csv"; 
//"http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=".$mthTo."&e=".$dayTo."&f=".$yrTo."&g=d&a=".$mthFrom."&b=".$dayFrom."&c=".$yrFrom."&ignore=.csv";

$eachfile = file_get_contents($url);

$sql ="";
$lines = explode("\n", $eachfile);

$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");

foreach($lines as $key=>$data) 
{
	try
	{
		//only when date and code are exist then create SQL query string
		//your sql here need to join the previous $sql, if not only the last $sql will execute.
		//but for your daily crawl, i mean subsequent run just $sql = willdo, coz you non eed to join many sql queries. one day only run one sql insert one da
		if($key==0)continue;
			
		$csvrow = str_getcsv($data);
		if(empty($csvrow[1])||empty($csvrow[2])||empty($csvrow[3])||empty($csvrow[4]))continue;
		$sql=$sql="INSERT INTO weekly_data (date, open, high, low, close, vol, adjclose, code)
			VALUES ('".date("Y-m-d", strtotime($csvrow[0]))."',
			'".$csvrow[1]."', ".$csvrow[2].", ".$csvrow[3].", ".$csvrow[4].", ".$csvrow[5].",".$csvrow[6].", '^STI');";
		//"INSERT INTO daily_data (date, close, high, low, open, vol, code)	VALUES ('".date('Y-m-d',time()+15*3600)."',	".$c.", ".$h.", ".$l.", ".$o.", 0, '^STI');";

		$res = pg_query($conn, $sql);
		if(!$res){
			$logfile = fopen(dirname(__FILE__)."/"."sticrawllog.txt", "a+");
			$text = date("Y-m-d H:i:s", time()+15*3600).": error occured at crawling URL: ".$url.";\n";
			$text .= "postgres SQL error: ". pg_last_error($conn). "\n";
			fwrite($logfile, $text);
		}
			
	}catch(Exception $e) 
	{
		$logfile = fopen(dirname(__FILE__)."/"."sticrawllog.txt", "a+");
		$text = date("Y-m-d H:i:s", time()+15*3600).": error occured at crawling URL: ".$url.";\n";
		$text .= 'Caught exception: '.  $e->getMessage(). "\n";
		$text .= "postgres SQL error: ". pg_last_error($conn). "\n";
		fwrite($logfile, $text);

		continue;
	}

}	
exit();
