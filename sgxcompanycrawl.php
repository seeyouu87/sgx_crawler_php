<?php 
//for price can use this:
//http://sgx-api-lb-195267723.ap-southeast-1.elb.amazonaws.com/sgx/price?callback=jQuery111002445511727128178_1456758659661&json=%7B%22id%22%3A%2240E%22%7D&_=1456758659663
$sticode = array("A17U","BN4","BS6",
"C07","C09","C31",
"C38U","C52","C6L",
"CC3","D05","E5H",
"F34","G13","H78",
"MC0","N21","NS8U",
"O39","S51","S58",
"S59","S63","S68",
"T39","U11","U14",
"U96","Y92","Z74");
$date =date('Y-m-d',time());
$logfilename = "logfile-".$date.".txt";
$logfile = fopen(dirname(__FILE__)."/".$logfilename, "a+");
$conn = pg_connect("host=localhost port=5432 dbname=goinvest user=postgres password=******");
if(!$conn){
  $txt = "today is Sunday!.\n";
  fwrite($logfile, $txt);
}
foreach($sticode as $code){
$companyapi = "http://sgx-api-lb-195267723.ap-southeast-1.elb.amazonaws.com/sgx/company?callback=j&json=%7B%22id%22%3A%22".$code."%22%7D";
$companydata = file_get_contents($companyapi);
$jsonData =  json_decode(substr($companydata,2,strlen($companydata)-4));
//$eval($companydata);
if(isset($jsonData->company->companyInfo)&&is_object($jsonData->company->companyInfo)){
	/*pg_query($conn, "INSERT INTO company_data (code,eps,\"peRatio\",\"previousCloseDate\",\"previousClosePrice\",
\"priceToBookRatio\",\"sharesOutstanding\",\"companyName\",\"marketCap\",\"netProfitMargin\",\"dividendYield\") VALUES('".$jsonData->company->companyInfo->tickerCode."','".$jsonData->company->companyInfo->eps."','".(isset($jsonData->company->companyInfo->peRatio)?$jsonData->company->companyInfo->peRatio:0)."','".date("Y-m-d",strtotime($jsonData->company->companyInfo->
previousCloseDate))."','".$jsonData->company->companyInfo->previousClosePrice."','".$jsonData->company->companyInfo->priceToBookRatio."','".$jsonData->company->companyInfo->sharesOutstanding."','".$jsonData->company->companyInfo->companyName."','".$jsonData->company->companyInfo->marketCap."','".$jsonData->company->companyInfo->netProfitMargin."','".(isset($jsonData->company->companyInfo->dividendYield)?$jsonData->company->companyInfo->dividendYield:0)."')");
*/
	try{
	/*	$executed = pg_query($conn, "UPDATE company_data SET eps='".$jsonData->company->companyInfo->eps."',\"peRatio\"='".(isset($jsonData->company->companyInfo->peRatio)?$jsonData->company->companyInfo->peRatio:0)."',\"previousCloseDate\"='".date("Y-m-d",strtotime($jsonData->company->companyInfo->previousCloseDate))."',\"previousClosePrice\"='".$jsonData->company->companyInfo->previousClosePrice."',
		\"priceToBookRatio\"='".$jsonData->company->companyInfo->priceToBookRatio."',\"sharesOutstanding\"='".$jsonData->company->companyInfo->sharesOutstanding."',\"companyName\"='".$jsonData->company->companyInfo->companyName."',\"marketCap\"='".$jsonData->company->companyInfo->marketCap."',\"netProfitMargin\"='".$jsonData->company->companyInfo->netProfitMargin."',\"dividendYield\"='".(isset($jsonData->company->companyInfo->dividendYield)?$jsonData->company->companyInfo->dividendYield:0)."' WHERE code='".$jsonData->company->companyInfo->tickerCode."'");
	*/
$executed = pg_query($conn, "INSERT INTO company_data (code,eps,\"peRatio\",\"previousCloseDate\",\"previousClosePrice\",
\"priceToBookRatio\",\"sharesOutstanding\",\"companyName\",\"marketCap\",\"netProfitMargin\",\"dividendYield\") VALUES('".$jsonData->company->companyInfo->tickerCode."','".$jsonData->company->companyInfo->eps."','".(isset($jsonData->company->companyInfo->peRatio)?$jsonData->company->companyInfo->peRatio:0)."','".date("Y-m-d",strtotime($jsonData->company->companyInfo->
previousCloseDate))."','".$jsonData->company->companyInfo->previousClosePrice."','".$jsonData->company->companyInfo->priceToBookRatio."','".$jsonData->company->companyInfo->sharesOutstanding."','".$jsonData->company->companyInfo->companyName."','".$jsonData->company->companyInfo->marketCap."','".$jsonData->company->companyInfo->netProfitMargin."','".(isset($jsonData->company->companyInfo->dividendYield)?$jsonData->company->companyInfo->dividendYield:0)."')");
	$sql="INSERT INTO stock_data (date, code, open, high, low, close, volume) VALUES ('".date("Y-m-d",strtotime($jsonData->company->companyInfo->
previousCloseDate))."','".$jsonData->company->companyInfo->tickerCode."', ".$jsonData->company->companyInfo->openPrice.", ".$jsonData->company->companyInfo->highPrice.", ".$jsonData->company->companyInfo->lowPrice.", ".$jsonData->company->companyInfo->previousClosePrice.", ".$jsonData->company->companyInfo->volume.");";

			if(!$executed){
				fwrite($logfile, pg_last_error($conn)."\n");
			}
	$hist= pg_query($conn, $sql);
 if(!$hist){
                                fwrite($logfile, pg_last_error($conn)."\n");
                        }

	}catch(Exception $e) {
		$text = 'Caught exception: '.  $e->getMessage(). "\n";
		fwrite($logfile, $text);
	}
}else{
	die("error while fetching data");
}

}
pg_close($conn);

$txt = "SGX Crawler task completed at ".date('Y-m-d H:i:s',time()).".\n";
fwrite($logfile, $txt);
fclose($logfile);

?>
