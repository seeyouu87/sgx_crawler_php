<?php 
// loop through all csv files 
function previousDate($daysago) {
    $past_time = time() - ($daysago * 24 * 60 * 60);
	return date("Ymd", $past_time);
}
$x1 = previousDate(1);
$x2 = previousDate(2);
$x3 = previousDate(3);
$x4 = previousDate(4);
$x5 = previousDate(5);


$dir= '/.';
$files = scandir(dirname($dir),1);
/*while($subs = readdir($files)){
	if ( $subs=='.' || $subs=='..' ) continue;
	 
	 if ( $subs=='SGX ETFs data'&&is_dir($dir."/".$subs) ){w
	 	$dirlist = getDirContents($dir."/".$subs);
print_r($dirlist);
echo "\n";
}
} */

foreach($files as $file) {
	try 
	{
	  	if ($file=='.' || $file=='..' || strpos($file, "Icon")) continue;
	  	$dire= '/SGX ETFs data';
	  	$eachfile = fopen($dire."/"."history_ISASIABNDSD_20151013.csv","r");
		$arraytoImport = array();
		$sql ="";
		//$column_headers = fgetcsv($eachfile);
		while(! feof($eachfile)) 
		{
			$csvrow = fgetcsv($eachfile);
			echo "able to read Ql2";
			print_r($csvrow);
			if ($csvrow[3] == $x3) {	
			//create SQL query string
				$sql="INSERT INTO stock_data (date, code, open, high, low, close, volume)  
				VALUES ('".date('Y-m-d',strtotime($csvrow[3]))."', 
				'".$csvrow[0]."', ".$csvrow[4].", ".$csvrow[5].", ".$csvrow[6].", ".$csvrow[7].", ".$csvrow[8].");\n";
			}
		}
		fclose($eachfile);
		if ( $file == "history_ISASIABNDSD_20151013.csv") 
		{
			die("QL2 query:".$sql);
		}
		$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres");
		//pg_connect("host=goinvestweb.cloudapp.net port=5432 dbname=goinvest user=postgres password=G01nvest");

		if (!$conn) {
		  $logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "w");
		  $txt = "Unable to connect to database.\n";
		  fwrite($logfile, $txt);
		  //exit;
		}
		
		// check if already exist
		//$checkdatabasesql = "SELECT
		

		$result = pg_query($conn, $sql);
		if (!$result) {
		  //$logfile = fopen(dirname(__FILE__)."/"."logfile.txt", "w");
		  //$txt = "Query cannot be executed.\n";
		  //fwrite($logfile, $txt);
		  echo "what the hell happened";
		}

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