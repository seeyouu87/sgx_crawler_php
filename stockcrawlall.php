<?php 


$dir= 'C:\\xampp\\htdocs\\stockcrawler\\csv\\.';
$files = scandir(dirname($dir),1);
/*while($subs = readdir($files)){
	if ( $subs=='.' || $subs=='..' ) continue;
	 
	 if ( $subs=='SGX ETFs data'&&is_dir($dir."/".$subs) ){w
	 	$dirlist = getDirContents($dir."/".$subs);
print_r($dirlist);
echo "\n";
}
} */

foreach($files as $file) 
{
	try 
	{
		$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");
	  	if ($file=='.' || $file=='..' || strpos($file, "Icon")) continue;
	  	$dire= 'C:\\xampp\\htdocs\\stockcrawler\\csv';
	  	$eachfile = fopen($dire."\\".$file,"r");
		$arraytoImport = array();
		$sql ="";
		$column_headers = fgetcsv($eachfile);
		
		while(! feof($eachfile)) 
		{
			$csvrow = fgetcsv($eachfile);
			
			if ($csvrow[3] == ""|| $csvrow[0] =="") continue;	//this continue here will skip the following line
			
			if(!is_numeric($csvrow[8])||$csvrow[8]=='0')continue;
			
			$sqlcheck = "SELECT * FROM stock_data WHERE code='".$csvrow[0]."' AND date = '".date('Y-m-d',strtotime($csvrow[3]))."'";
			$rowcount = pg_query($conn, $sqlcheck);
			if($rowcount && pg_num_rows($rowcount)>0)continue;
			//only when date and code are exist then create SQL query string
			//your sql here need to join the previous $sql, if not only the last $sql will execute.
			//but for your daily crawl, i mean subsequent run just $sql = willdo, coz you non eed to join many sql queries. one day only run one sql insert one da
				$sql="INSERT INTO stock_data (date, code, open, high, low, close, volume)  
				VALUES ('".date('Y-m-d',strtotime($csvrow[3]))."', 
				'".$csvrow[0]."', ".$csvrow[4].", ".$csvrow[5].", ".$csvrow[6].", ".$csvrow[7].", ".$csvrow[8].");\n";

			if (!$conn) 
			{
			  $logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "a");
			  $txt = "Unable to connect to database.\n";
			  fwrite($logfile, $txt);
			  //exit;
			}
			
			// check if already exist
			//$checkdatabasesql = "SELECT
			if ($sql == "") {
				$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "a");
			    $txt = "sql empty.\n";
			    fwrite($logfile, $txt);
			} else {
				$result = pg_query($conn, $sql);
				if (!$result) {
			  		$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "a");
			  		$txt = "Query cannot be executed.\n";
			  		fwrite($logfile, $txt);
			  
				}
			}
			
		}
		fclose($eachfile);

		pg_close($conn);

	}
	catch(Exception $e) 
	{
		$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "a");
		$text = "error occured at crawling code: ".$code.", URL: ".$url.";\n";
		$text2 = 'Caught exception: '.  $e->getMessage(). "\n";
		fwrite($logfile, $text);
		fwrite($logfile, $text2);
		pg_close($conn);
	}  
}