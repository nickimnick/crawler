<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Crawler</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="js/jquery-2.1.1.min.js"></script>
<link href="css/main.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
	
	header('Content-type: text/html; charset=ISO-8859-1');
  ini_set('max_execution_time', 0);
  
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
	
		$sqlChk = "SELECT str_name, str_slug FROM ".$table." WHERE str_photo = 0 ORDER BY str_score DESC LIMIT 4993";
    $json = '[';
    
		if($q = $mysqli->query($sqlChk)){
      
      $stars = $q->fetch_all(MYSQLI_ASSOC);
			
      foreach($stars as $star){
        $json .= '{"str_name":"'.$star['str_name'].'", "str_slug":"'.$star['str_slug'].'"},';
      }
      
      $json = rtrim($json, ',').']';

    }
		
		$mysqli->close();
    
  }
	
	
?>
  
<div id="container" class="first">
<div class="form">
<ul>
<li><a class="getIDs" href="javascript:void(0);">START</a></li>
</ul>
<div class="fixer"></div>
</div>
<div class="result"></div>
</div>
<script type="text/javascript">
	var i = 0, str = <?php echo $json; ?>;
	
	function getStar(name, slug){

	
		if(typeof str[i] != 'undefined'){
			
			$.post('getActorPhoto.php?query='+name+'&slug='+slug, function(data){

				$('.result').prepend('<div>'+data+'</div>');
				
				i++;
				
				setTimeout(function(){getStar(str[i]['str_name'], str[i]['str_slug']);}, 100);
				
			});
			
		}else{
			
			$('.result').prepend('<h3>ALL DONE!!!</h3>');
			
		}
		
	}
	
	$('.getIDs').click(function(){
		getStar(str[i]['str_name'], str[i]['str_slug']);
	});
	
</script>
</body>
</html>
