<?php

/****

* Simple PHP application for using the Bing Search API

*/
header('Content-type: text/html; charset=ISO-8859-1');

if(isset($_GET['query']) && isset($_GET['slug'])){
  
  $name = $_GET['query'];
  $slug = $_GET['slug'];
  $acctKey = 'wQ0a5neqOt8HCwqx6qPgElxxZwsTQ8Ad+3B2EUZIDOQ';
  $query = urlencode("'{$name}'");
  $requestUri = 'https://api.datamarket.azure.com/Bing/Search/Image?$format=json&Query='.$query.'&Options=%27DisableLocationDetection%27&Adult=%27Strict%27&ImageFilters=%27Size%3AMedium%2BAspect%3ATall%2BStyle%3APhoto%2BFace%3AFace%27';
  
  $auth = base64_encode("$acctKey:$acctKey");
  $data = array(
    'http' => array(
    'request_fulluri' => true,
    'ignore_errors' => true,
    'header' => "Authorization: Basic $auth")
  );
  
  $context = stream_context_create($data);
  $response = file_get_contents($requestUri, 0, $context);
  $jsonObj = json_decode($response);
  $imgPath = '';
  $img = '';
  
  foreach($jsonObj->d->results as $result){
    
    if(intval($result->Width) > 250 && $img = fopen($result->MediaUrl, 'r')){
      
      $imgPath = $result->MediaUrl;
      break;
    
    }
    
  } 
  
  if($imgPath != ''){
    
    file_put_contents("data/stars/".$slug.".jpg", $img);
    
    //DATABASE INFO
    $host = "localhost";
    $user = "root";
    $pass = "1079";
    $dbName = "crawler_db";
    $table = 'stars';
    $str = '';
  
    $mysqli = new mysqli($host,$user,$pass,$dbName);
    
    if ($mysqli->connect_errno){
      
      $result = $mysqli->connect_error;
      
      exit();
      
    }else{
    
      $sqlChk = "UPDATE mvs_dm_stars SET str_photo = 1 WHERE str_slug = '".$slug."' LIMIT 1";
      
      if($mysqli->query($sqlChk)){
        echo $name."'s photo is ok.";
      }else{
        echo $name."'s photo is not set.";
      }
      
      $mysqli->close();
      
    }
    
  }else{
    
    echo $name."'s photo is not ok.";
    
  }
  
}

?> 