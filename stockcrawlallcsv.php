<?php
set_time_limit (0);

$dir= '/home/csv/csv/.';

$files = scandir(dirname($dir),1);
while($subs = readdir($files))
{
	if ( $subs=='.' || $subs=='..' ) continue;

	if ( $subs=='csv'&&is_dir($dir."/".$subs) )
	{
	 	$dirlist = getDirContents($dir."/".$subs);
		print_r($dirlist);
		echo "\n";
	}
}

foreach($files as $file) {
	try
	{
	  	if ($file=='.' || $file=='..' || strpos($file, "Icon")) continue;
	  	$dire= '/home/csv/csv';
	  	$eachfile = fopen($dire."/".$file,"r");
		$arraytoImport = array();
		$sql ="";
		$column_headers = fgetcsv($eachfile);
		while(! feof($eachfile)) 
		{
			try
			{
				$csvrow = fgetcsv($eachfile);

				if ($csvrow[3] == ""|| $csvrow[0] =="") continue;	//this continue here will skip the following line
				//only when date and code are exist then create SQL query string
				//your sql here need to join the previous $sql, if not only the last $sql will execute.
				//but for your daily crawl, i mean subsequent run just $sql = willdo, coz you non eed to join many sql queries. one day only run one sql insert one da
				$sql="INSERT INTO stock_data (date, code, open, high, low, close, volume)
				VALUES ('".date('Y-m-d',strtotime($csvrow[3]))."',
				'".$csvrow[0]."', ".$csvrow[4].", ".$csvrow[5].", ".$csvrow[6].", ".$csvrow[7].", ".$csvrow[8].");";
				$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");
	 			$conn2 = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=*****");

				if (!$conn) 
				{
					$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "w");
					$txt = "Unable to connect to database.\n";
					fwrite($logfile, $txt);
				}

				$result = pg_query($conn, $sql);
				$result2 = pg_query($conn2, $sql);
				if (!$result) 
				{
			  		$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "w");
			  		$txt = "Query cannot be executed.\n";
					$txt.= "postgres SQL error: ". pg_last_error($conn);
			  		fwrite($logfile, $txt);
				}
			}
			catch(Exception $e) 
			{
					$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "w");
					$text = "error occured at crawling code: ".$code.", URL: ".$url.";\n";
					$text2 = 'Caught exception: '.  $e->getMessage(). "\n";
					fwrite($logfile, $text);
					fwrite($logfile, $text2);
					pg_close($conn);
					continue;
			}

		}
		fclose($eachfile);
	}
	catch(Exception $e) 
	{
		$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "w");
		$text = "error occured at crawling code: ".$code.", URL: ".$url.";\n";
		$text2 = 'Caught exception: '.  $e->getMessage(). "\n";
		fwrite($logfile, $text);
		fwrite($logfile, $text2);
	}
}
