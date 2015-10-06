<?php

	ini_set('max_execution_time', 0);

	function logger($data){
		
		$dt = new DateTime();
		$now = $dt->format('d-m-Y H:i:s');
		
		$logFile = fopen("logs/logs.txt", "a+") or die("Unable to open file!");
		
		fwrite($logFile, $data." --> ".$now."\r\n");
		fclose($logFile);
			
	}

	if(isset($_GET['ys']) && isset($_GET['ye']) && isset($_GET['start'])){
		$ys = $_GET['ys'];
		$ye = $_GET['ye'];
		$start = $_GET['start'];
	
		if($ys != '' && $ye != ''){
			
			include_once('simple_html_dom/simple_html_dom.php');
			
			$mpp = 250;
			$target_url = 'http://www.imdb.com/search/title?sort=release_date_us,asc&view=simple&count='.$mpp.'&start='.$start.'&title_type=feature&year='.$ys.','.$ye;
			$html = new simple_html_dom();
			$html->load_file($target_url);
			$result = '';
			$temp = explode(' ', trim($html->find('#main #left', 0)->plaintext, ' '));
			$total = $temp[2];
			$temp = explode('-', $temp[0]);
			$end = $temp[1];
			
			foreach($html->find('table.results td.title') as $tr){
				
				$id = explode('/', $tr->find('a[href^=/title/]', 0)->href);
				$result .= $id[2].',';
				
			}
			
			$result = trim($result, ',');
			echo '{"end":"'.$end.'","total":"'.$total.'","ids":"'.$result.'"}';
			logger("----- Movies ".$start." - ".($start+$mpp-1)." -----");
			
		}
	}

	
	
?>