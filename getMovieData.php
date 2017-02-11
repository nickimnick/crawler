<?php
	
	header('Content-type: text/html; charset=utf-8');
	ini_set('max_execution_time', 0);
	
	function logger($data){
		
		$dt = new DateTime();
		$now = $dt->format('d-m-Y H:i:s');
		$data = strip_tags($data);
		$logFile = fopen("logs/logs.txt", "a+") or die("Unable to open file!");
		
		fwrite($logFile, "- ".$data." --> ".$now."\r\n");
		fclose($logFile);
			
	}
	
	//function check_url($url) {
	//		
	//		$result = FALSE;
	//		$headers = get_headers($url);
	//		
	//		if(substr($headers[0], 9, 3) == '200')
	//			$result = TRUE;
	//	
	//	return $result;
	//
	//}
	function check_url($url){
				$result = false;
				if($page = @file_get_contents($url)) {
						$result = $page;
				}
				
				return $result;
	}
	
	//DATABASE INFO
	$host = "localhost";
	$user = "root";
	$pass = "1079";
	$dbName = "crawler_db";
	$table = 'movies';
	$result = '';
	$now = date("Y-m-d", time());
	
	if(isset($_GET['id'])) $urlId = $_GET['id'];	

	$check = TRUE;
	
	$mysqli = new mysqli($host,$user,$pass,$dbName);

	if ($mysqli->connect_errno){
		
		$result = $mysqli->connect_error;
    	exit();
	
	}else{
	
		$sqlChk = "SELECT imdb_id FROM ".$table." WHERE imdb_id ='".$urlId."'";
		$q = $mysqli->query($sqlChk);
		
		if($q->num_rows > 0) $check = FALSE;
		
		$mysqli->close();
		
		if($check){
		
			$imdb_url = 'http://www.imdb.com/title/'.$urlId;
			$omdb_url = "http://www.omdbapi.com/?i=".$urlId."&tomatoes=true";
			$omdb_res = check_url($omdb_url);
			$imdb_res = check_url($imdb_url);
			
			if($omdb_res && $imdb_res){
					
				include_once('simple_html_dom/simple_html_dom.php');

				$json = json_decode(file_get_contents($omdb_url));
				$html = file_get_html($imdb_url);

				function checkStr($data){
					
					if($data == "N/A")
						return "";
					else
						return $data;
						
				}
				
				if($json->Response == "True"){
					
					//DATA FROM OMDB API
					$title = str_replace("'", "\'", $json->Title);
					$year = $json->Year;
					$audience = checkStr(str_replace("'", "\'", $json->Rated), false);
					$release_date = checkStr($json->Released, false);
					$runtime = checkStr(str_replace(" min", "", $json->Runtime), false);
					$genre = '|'.str_replace(", ", "||", $json->Genre).'|';
					$plot = checkStr(str_replace("'", "\'", $json->Plot), false);
					$country = checkStr(str_replace(", ", "||", str_replace("'", "\'", $json->Country)), true);
					$languages = checkStr(str_replace("'", "\'", str_replace(", ", "||", $json->Language)), true);
					$awards = checkStr(str_replace("'", "\'", $json->Awards), false);
					$poster = checkStr($json->Poster, false);
					$metascore = checkStr($json->Metascore, false);
					$imdb_rating = checkStr($json->imdbRating, false);
					$imdb_votes = checkStr($json->imdbVotes, false);
					$tomato_meter = checkStr($json->tomatoMeter, false);
					$box_office = str_replace("'", "\'", checkStr($json->BoxOffice, false));
					$poster_path = '';
					$status = 1;
					
					if($poster != ''){
						if($poster = @fopen($poster, 'r')){
							file_put_contents("data/movies/".$urlId.".jpg", $poster);
							$poster_path = 1;
						}
					}
					
					if($release_date != ''){
						$release_date = date("Y-m-d", strtotime($release_date));
					}
					
					//DATA FROM IMDB
					foreach($html->find('#pagecontent') as $tag){
						
						if($tag->find('.title_wrapper .subtext meta[itemprop="datePublished"]', 0) != NULL){
						
							$release_date = $tag->find('.title_wrapper .subtext meta[itemprop="datePublished"]', 0)->getAttribute('content');
							
							if($release_date > $now)
								$status = 0;
			
							if($tag->find('.imdbRating span[itemprop="ratingValue"]', 0) != NULL){
								$imdb_rating = $tag->find('.imdbRating span[itemprop="ratingValue"]', 0)->plaintext;
								$imdb_votes = $tag->find('.imdbRating span[itemprop="ratingCount"]', 0)->plaintext;
							}
							
							if($tag->find('.titleReviewBarItem metacriticScore span', 0) != NULL){
								$metascore = $tag->find('.titleReviewBarItem metacriticScore span', 0)->plaintext;
							}

							$original_title = ($tag->find('h1.header span', 0) != NULL) ? trim(str_replace("'", "\'", $tag->find('h1.header span', 0)->plaintext), ' ') : '';
							
							if($title == $original_title) $original_title = "";
							
							if($country == ''){
							
								foreach($tag->find('#titleDetails .txt-block a[href^=/country/]') as $subtag){
				
									$country .= '|'.str_replace("'", "\'", $subtag->plaintext).'|';
						
								}
								
							}
							
							if($languages == ''){
								
								if($tag->find('#titleDetails .txt-block a[href^=/language/]', 0) != ''){
									
									foreach($tag->find('#titleDetails .txt-block a[href^=/language/]') as $subtag){
					
										$languages .= '|'.str_replace("'", "\'", $subtag->plaintext).'|';
							
									}
									
								}
								
							}
							
							
							$cast = '';
							$director = '';
							$writer = '';
							$characters = '';
							$cast_imdb_id = '';
							$director_imdb_id = '';
							$writer_imdb_id = '';
							$tagline = '';
							
							foreach($tag->find('#titleStoryLine .txt-block') as $subtag){
							
								if($subtag->find('h4', 0) != '' && $subtag->find('h4', 0)->plaintext == "Taglines:")
									$tagline = trim(str_replace("'", "\'", str_replace("See more&nbsp;&raquo;", "", str_replace("Taglines:", "", $subtag->plaintext))), " ");
							
							}
					
							foreach($tag->find('#titleCast .cast_list td.primary_photo') as $subtag){
					
								$cast .= '|'.trim(str_replace("'", "\'", $subtag->find('img', 0)->getAttribute('alt')), ' ').'|';
								$temp = explode('/', $subtag->find('a', 0)->href);
								$cast_imdb_id .= '|'.$temp[2].'|';
					
							}
							
							foreach($tag->find('[itemprop=director] a[href^=/name/]') as $subtag){
								
								$director .= '|'.trim(str_replace("'", "\'", $subtag->find('span[itemprop=name]', 0)->plaintext), ' ').'|';
								$temp = explode('/', $subtag->href);
								$temp = explode('?', $temp[2]);
								$director_imdb_id .= '|'.$temp[0].'|';
							}
							
							foreach($tag->find('[itemprop=creator] a[href^=/name/]') as $subtag){
								
								$writer .= '|'.trim(str_replace("'", "\'", $subtag->find('span[itemprop=name]', 0)->plaintext), ' ').'|';
								$temp = explode('/', $subtag->href);
								$temp = explode('?', $temp[2]);
								$writer_imdb_id .= '|'.$temp[0].'|';
							
							}
							
							foreach($tag->find('#titleCast .cast_list td.character') as $subtag){
					
								$characters .= '|'.trim(str_replace("'", "\'", trim($subtag->find('div', 0)->plaintext, ' ')), ' ').'|';
					
							}
							
							if($poster == '' && $tag->find('.slate_wrapper .poster a', 0) != NULL){
								
								$poster_url = 'http://www.imdb.com'.$tag->find('.slate_wrapper .poster a', 0)->href;
								$poster_id = explode('/', parse_url($poster_url, PHP_URL_PATH));
								$poster_id = $poster_id[4];
								$ch = curl_init();
								$user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
								
								curl_setopt($ch, CURLOPT_URL, $poster_url);
								curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_HEADER, 0);
								
								$poster_html = str_get_html(curl_exec($ch));
								
								curl_close($ch);
									
								$poster_url = $poster_html->find('#imageJson', 0)->innertext;
								$poster_url = json_decode($poster_url);
								$poster = FALSE;
								
								foreach($poster_url->mediaViewerModel->allImages as $img){
									if($img->id == $poster_id)
										$poster = $img->msrc;
								}

								if($poster){

									$b64image = base64_encode(file_get_contents($poster));
									$fp = fopen('data/movies/'.$urlId.'.jpg', 'wb');

									fwrite($fp, base64_decode($b64image)); 
									fclose($fp);

									$poster_path = 1;
									
								}
								
							}
							
							$mysqli = new mysqli($host,$user,$pass,$dbName);
							
							if ($mysqli->connect_errno){
								
								$result = '<span class="error">Movie: <i>'.$title.' ('.$urlId.')</i> is failed!!! -> '.$mysqli->connect_error.'</span>';
								exit();
								
							}else{
								echo 'CHECK-4';	
								$sql = "INSERT INTO ".$table."(title, original_title, poster_path, year, plot, tagline, runtime, genre, country, languages, imdb_rating, imdb_votes, director, director_imdb_id, writer, writer_imdb_id, cast, cast_imdb_id, characters, audience, imdb_id, release_date, awards, metascore, tomato_meter, box_office, rel_status) VALUES (N'".$title."',N'".$original_title."','".$poster_path."','".$year."',N'".$plot."',N'".$tagline."','".$runtime."',N'".$genre."',N'".$country."',N'".$languages."','".$imdb_rating."','.$imdb_votes.',N'".$director."','".$director_imdb_id."',N'".$writer."','".$writer_imdb_id."',N'".$cast."','".$cast_imdb_id."',N'".$characters."','".$audience."','".$urlId."','".$release_date."',N'".$awards."','".$metascore."','".$tomato_meter."',N'".$box_office."', ".$status.")";
									
								if($mysqli->query($sql)){
									
									$result = 'Movie: <i>'.$title.' ('.$urlId.')</i> is done!!!';
									
									
								}else{
									
									$result = '<span class="error">Movie: <i>'.$title.' ('.$urlId.')</i> is failed!!! -> '.$mysqli->error.'</span>';
									
								}
								
								$mysqli->close();
								
							}
						
						}else{
							
							$result = 'Movie: <i>'.$title.' ('.$urlId.')</i> not released yet!!!';
							
						}
						
					}
					
					$html->clear(); 
					unset($html);
					
				}else{
				
					$result = 'Movie: <i>'.$urlId.'</i> not found in OMDB API!!!';
						
				}
			
			}else{
				$result = 'Can`t reach OMDB API!!!';
			}
	
		}else{
			
			$result = 'Movie already exist!!!';
				
		}
		
	}
	
	echo $result;
	logger($result);

?>
