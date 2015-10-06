<?php
	
	$dt = new DateTime();
	$now = $dt->format('d-m-Y H:i:s');
	$logFile = fopen("logs/logs.txt", "a+") or die("Unable to open file!");
	
	fwrite($logFile, " @@@@@ ".$now." @@@@@ \r\n");
	fclose($logFile);
	
?>
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
<div id="container" class="first cast">
<div class="form">
<ul>
<li><label for="id">IMDB ID</label> <input type="text" name="id" id="id" required="required" /><div class="fixer"></div></li>
<li><a class="getCast" href="javascript:void(0);">GET CAST</a></li>
</ul>
<div class="fixer"></div>
</div>
<div class="result"></div>
</div>
<script type="text/javascript">
	var id = '';
	
	function validate(){
		var check = true;
		
		$('.form li').each(function(){
			if($('input[required="required"]', this).val() == '')
				check = false;
		});
		
		return check;
			
	}
	
  function getCast(id){
    $.post('getDetailedCast.php?id='+id, function(data){
  
      $('.result').append('<div>'+data+'</div>');
      
    });
  }
  
  $('a.getCast').click(function(){
      if(validate()){
        id = $('input#id').val();
        
        getCast(id);
      }
  });
	
</script>
</body>
</html>
