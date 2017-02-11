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
		$start = ($_GET['start'] == '') ? '' : " AND m_release >= '".$_GET['start']."'";
	
		if($ys != '' && $ye != ''){
			
			//DATABASE INFO
			$host = "localhost";
			$user = "root";
			$pass = "1079";
			$dbName = "crawler_db";
			$table = 'omdb_tbl';
			$result = '';
			
			$mysqli = new mysqli($host,$user,$pass,$dbName);

			if($mysqli->connect_errno){
				
				$result = $mysqli->connect_error;
				exit();
				
			}else{
				
				$query = "SELECT imdb_id FROM omdb_tbl WHERE (m_year >= $ys AND m_year <= $ye) AND m_release <> '' AND m_release IS NOT NULL AND m_release <> '0000-00-00' AND m_release <= CURDATE()$start ORDER BY m_release ASC";

				if ($q = $mysqli->query($query)) {

						/* fetch associative array */
						while($row = $q->fetch_assoc()) {
							$result .= $row['imdb_id'].',';
						}
				
						/* free result set */
						$q->free();
				}

				$result = trim($result, ',');
				echo $result;
				//logger("----- Movies ".$start." - ".($start+$mpp-1)." -----");
				
				$mysqli->close();

			}
			
			//include_once('simple_html_dom/simple_html_dom.php');
			//
			//$mpp = 250;
			//$target_url = 'http://www.imdb.com/search/title?sort=release_date_us,asc&view=simple&count='.$mpp.'&start='.$start.'&title_type=feature&year='.$ys.','.$ye;
			//$html = new simple_html_dom();
			//$html->load_file($target_url);
			//$result = '';
			//$temp = explode(' ', trim($html->find('#main #left', 0)->plaintext, ' '));
			//$total = $temp[2];
			//$temp = explode('-', $temp[0]);
			//$end = $temp[1];
			//
			//foreach($html->find('table.results td.title') as $tr){
			//	
			//	$id = explode('/', $tr->find('a[href^=/title/]', 0)->href);
			//	$result .= $id[2].',';
			//	
			//}
			//
			//$result = trim($result, ',');
			//echo '{"end":"'.$end.'","total":"'.$total.'","ids":"'.$result.'"}';
			//logger("----- Movies ".$start." - ".($start+$mpp-1)." -----");
			
			
		}
	}

	
	
?>