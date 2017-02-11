<?php
	
	$dt = new DateTime();
	$now = $dt->format('d-m-Y H:i:s');
	$logFile = fopen("logs/logs.txt", "a+") or die("Unable to open file!");
	
	fwrite($logFile, " @@@@@ ".$now." @@@@@ \r\n");
	fclose($logFile);
	
	$type = 'get';
	$type = (isset($_GET['type'])) ? $_GET['type'] : $type;
	
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
<div id="container" class="first">
<div class="form">
<ul>
<li><label for="ys">Year Start</label> <input type="text" name="ys" id="ys" required="required" /><div class="fixer"></div></li>
<li><label for="ye">Year End</label> <input type="text" name="ye" id="ye" required="required" /><div class="fixer"></div></li>
<li><label for="strt">Starting Line</label> <input type="text" name="strt" id="strt" /><div class="fixer"></div></li>
<li><label for="ids">Custom IDs</label> <input type="text" name="ids" id="ids" /><div class="fixer"></div></li>
<li><a class="getIDs" href="javascript:void(0);">START</a></li>
</ul>
<div class="fixer"></div>
</div>
<div class="result"></div>
</div>
<script type="text/javascript">
	var ids = '', i = 0, ys, ye, end, start = 1, total, tCount = 1, done = true, idsL = 0;
	
	function validate(){
		var check = true;
		
		$('.form li').each(function(){
			if($('input[required="required"]', this).val() == '')
				check = false;
		});
		
		return check;
			
	}
	
	//function getMovie(){
	//
	//	if((i+1) <= ids.length && tCount <= total){
	//		$.post('getMovieData.php?id='+ids[i], function(data){
	//
	//			$('.result').prepend('<div>'+tCount+'. '+data+'</div>');
	//			
	//			i++;
	//			tCount++;
	//			
	//			if(data == 'Movie already exist!!!')
	//				getMovie();
	//			else
	//				setTimeout(function(){getMovie();}, 500);
	//			
	//		});
	//	}else if(tCount <= total){
	//		
	//		start = start+i;
	//		getIDs();
	//		i = 0;
	//			
	//	}
	//
	//	if(start > total){
	//		done = false;
	//		$('.result').prepend('<h3>ALL DONE!!!</h3>');
	//	}
	//}
	//
	//function getIDs(){
	//	
	//	var e;
	//	
	//	$('#container .result').prepend('<div class="loading"><img src="images/ajax-loader.gif" alt="loading" /></div>');
	//	
	//	$.post('getIdList.php?ys='+ys+'&ye='+ye+'&start='+start, function(data){
	//		e = $.parseJSON(data);
	//		ids = e.ids.split(',');
	//		end = parseFloat(e.end.replace(',', ''));
	//		total = parseFloat(e.total.replace(',', ''));
	//		
	//		$('#container .result .loading').remove();
	//		
	//
	//		if(done){
	//			$('.result').prepend('<h4>Starting films between '+(end-ids.length+1)+' - '+end+'</h4>');
	//			getMovie();
	//		}
	//
	//	});
	//}
	
	function getMovie(){
		
		if(i < idsL){
			$.post('<?php echo $type; ?>MovieData.php?id='+ids[i], function(data){

				$('.result').prepend('<div>'+(i+1)+'. '+data+'</div>');
				
				i++;
				
				if(data == 'Movie already exist!!!')
					getMovie();
				else
					setTimeout(function(){getMovie();}, 100);
				
			});
		}else{
			$('.result').prepend('<div>DATA TRANSFER COMPLETED!!!</div>');
		}

	}
	
	function getIDs(){
		
		$('#container .result').prepend('<div class="loading"><img src="images/ajax-loader.gif" alt="loading" /></div>');
		
		$.post('getIdList.php?ys='+ys+'&ye='+ye+'&start='+start, function(data){
			//e = $.parseJSON(data);
			ids = data.split(',');
			//end = parseFloat(e.end.replace(',', ''));
			//total = parseFloat(e.total.replace(',', ''));
			
			$('#container .result .loading').remove();
			

			if(done){
				//$('.result').prepend('<h4>Starting films between '+(end-ids.length+1)+' - '+end+'</h4>');
				idsL = ids.length;
				$('.result').before('<div>TOTAL MOVIE:'+idsL+'</div>');
				//i = start;
				i = 0;
				getMovie();
			}

		});
	}
	
	$('.getIDs').click(function(){
	
		if(validate()){
			ys = $('input#ys').val(),
			ye = $('input#ye').val(),
			strt = $('input#strt').val();
			//if(strt != '') start = parseFloat(strt); else start = 0;
			start = (strt != '') ? strt : 0;
			$('#container').removeClass("first");
			getIDs();
		}else if($('input#ids').val() != ''){

			$('#container').removeClass("first");
			
			ids = $('input#ids').val();
			ids = ids.split(',');
			
			$('#container .result .loading').remove();

			idsL = ids.length;
			i = 0;
			getMovie();

		}else{
			alert("Please fill all required fields.");
		}
			
	});
	
</script>
</body>
</html>
