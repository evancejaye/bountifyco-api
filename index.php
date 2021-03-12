<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://bountify.co/bounties',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);

//load the html response for parsing
libxml_use_internal_errors(true);
$dom = new DOMDocument();
@ $dom->loadHTML($response);
libxml_clear_errors();
$xpath = new DOMXpath($dom);
$ret = array();
$ret['ret'] = 400;
$ret['msg'] = "No new data!";

$questions = $xpath->query("//div[contains(@class,'question')]");
$bounties =     $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[3]/div/div[1]/div[1]");
$solutions =    $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[3]/div/div[2]/div[1]/div");
$title =        $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[3]/div/div[3]/div[1]/div/a");
$expiry =        $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[3]/div/div[4]");


//open cache file


for($i = 3; $i < 8; $i++){
    $bounties =     $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[$i]/div/div[1]/div[1]");
    $solutions =    $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[$i]/div/div[2]/div[1]/div");
    $title =        $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[$i]/div/div[3]/div[1]/div/a");
    $expiry =       $xpath->query("/html/body/div[2]/div[1]/div/div/form/div[2]/div[$i]/div/div[4]");

    $expiry[0]->textContent = str_replace("left", " left", preg_replace("/[^a-zA-Z0-9 ]/", "",$expiry[0]->textContent));

    //find ones that have not been done
    $flag = $bounties[0]->getAttribute('class');
    if(strpos($flag, "alive")){
        //the bounty is on, but was it sent preciously?
        if(sent($title[0]->getAttribute('href')) == false ){
            $message = "BOUNTY:\n{$title[0]->textContent} [{$bounties[0]->textContent} USD]. {$solutions[0]->textContent} solutions posted. {$expiry[0]->textContent } till expiry.\nLink: https://bountify.co/{$title[0]->getAttribute('href')}";
            $to = "255756166367";
            echo sendRouteMobileSMS($to, $message);
        }else{
            echo "Bounty found on cache.";
        }
    
    }

}

//function to check is bounty was previously sent
function sent($url){
    $url = md5($url);
    $cache_file = "cache.txt";
    $cache = file_get_contents($cache_file);
    if(substr_count($cache,$url) > 0){
        return true;
    }else{
        file_put_contents($cache_file, $url, FILE_APPEND | LOCK_EX);
        return false;
    }

    
}

//function to send sms
function sendRouteMobileSMS($to, $sms){
	$sms = urlencode($sms);
	$curl = curl_init();
	curl_setopt_array($curl, array(
	CURLOPT_URL => "http://api.rmlconnect.net/bulksms/bulksms?username=evance&password=SMVw2MPu&type=5&dlr=1&destination=$to&source=EVANCEJAYE&message=$sms",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	));

	$response = curl_exec($curl);
	curl_close($curl);
    return $response;
}

//print_r($questions);