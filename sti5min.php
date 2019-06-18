<?php
set_time_limit (0);
if(date('w', time()+15*3600)==0 || date('w', time()+15*3600)==6){
$logfile = fopen(dirname(__FILE__)."/"."sticrawllog.txt", "a+");
		  		$txt = "not a trading hour\n";
		  		fwrite($logfile, $txt);
//echo "not a trading hour";	
exit();
}
/*http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=2&e=26&f=2016&g=d&a=2&b=25&c=2016&ignore=.csv
*/
//get today date:
$dayTo = date('j', time()+15*3600);
$mthTo = date('n', time()+15*3600)-1;
$yrTo = date('Y', time()+15*3600);
$yrFrom = date('Y', time()-31*24*3600+15*3600);
$mthFrom = date('n', time()-31*24*3600+15*3600)-1;
$dayFrom = date('j', time()-31*24*3600+15*3600);

$url="http://chartapi.finance.yahoo.com/instrument/1.0/%5ESTI/chartdata;type=quote;range=1d/csv";
//"http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=".$mthTo."&e=".$dayTo."&f=".$yrTo."&g=d&a=".$mthFrom."&b=".$dayFrom."&c=".$yrFrom."&ignore=.csv";
//"http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=3&e=7&f=2016&g=d&a=1&b=28&c=2016&ignore=.csv"; 
//"http://real-chart.finance.yahoo.com/table.csv?s=%5ESTI&d=".$mthTo."&e=".$dayTo."&f=".$yrTo."&g=d&a=".$mthFrom."&b=".$dayFrom."&c=".$yrFrom."&ignore=.csv";

$eachfile = file_get_contents($url);

$sql ="";
$lines = explode("\n", $eachfile);

$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");
$c= 0.0;
$h=0.0;
$l=0.0;
$o=0.0;
$holidays = pg_query($conn, "SELECT date FROM holidays where nation='Singapore'");
while($holiday = pg_fetch_array($holidays)){
if($holiday["date"]==date('Y-m-d',time()+15*3600)){
	$logfile = fopen(dirname(__FILE__)."/"."sticrawllog.txt", "a+");
                                $txt = "not a trading hour\n";
                                fwrite($logfile, $txt);
exit();

}
}
foreach($lines as $key=>$data) {
	if($key==sizeof($lines)-2)
	{
		$c=explode(",", $data)[1];
	}
	if(strpos($data,"previous_close:") !== false&&$key<10)
	{
		$tmp = str_replace("previous_close:", "", $data);
		$o=explode(",", $tmp)[0];
	}
		
	if(strpos($data,"low:") !== false&&$key>10)
	{
		$tmp = str_replace("low:", "", $data);
		$l=explode(",", $tmp)[0];
		//$l = min(floatval($l), floatval($o), floatval($c));
	}
	if(strpos($data,"high:") !== false&&$key>10)
	{
		$h=explode(",", $data)[1];
		//$h = max(floatval($h), floatval($o), floatval($c));
	}

}	
	try
	{
		//make sure highest and lowest before constructing sql Query: 
		$l = min(floatval($l), floatval($o), floatval($c));
		$h = max(floatval($h), floatval($o), floatval($c));
		//only when date and code are exist then create SQL query string
		//your sql here need to join the previous $sql, if not only the last $sql will execute.
		//but for your daily crawl, i mean subsequent run just $sql = willdo, coz you non eed to join many sql queries. one day only run one sql insert one data
		$sql="INSERT INTO daily_data (date, close, high, low, open, vol, code)
		VALUES ('".date('Y-m-d',time()+15*3600)."',
		".$c.", ".$h.", ".$l.", ".$o.", 0, '^STI');";

		$res = pg_query($conn, $sql);
		if(!$res){
			$logfile = fopen(dirname(__FILE__)."/"."sticrawllog.txt", "a+");
			$text = date("Y-m-d H:i:s", time()+15*3600).": error occured at crawling URL: ".$url.";\n";
			$text .= "postgres SQL error: ". pg_last_error($conn). "\n";
			fwrite($logfile, $text);
		}
			
		 }catch(Exception $e) {
			$logfile = fopen(dirname(__FILE__)."/"."sticrawllog.txt", "a+");
			$text = date("Y-m-d H:i:s", time()+15*3600).": error occured at crawling URL: ".$url.";\n";
			$text .= 'Caught exception: '.  $e->getMessage(). "\n";
			$text .= "postgres SQL error: ". pg_last_error($conn). "\n";
			fwrite($logfile, $text);

			continue;
	  }

//}	
exit();
