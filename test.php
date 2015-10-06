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
	
	//DATABASE INFO
	$host = "localhost";
	$user = "root";
	$pass = "1079";
	$dbName = "movies";
	$result = '';
	
	if(isset($_GET['id'])) $urlId = $_GET['id'];	

	$check = TRUE;
	
	$mysqli = new mysqli($host,$user,$pass,$dbName);

	if ($mysqli->connect_errno){
		
		$result = $mysqli->connect_error;
    	exit();
		
	}else{
	
		$sqlChk = "SELECT imdb_id FROM mvs_movies_new WHERE imdb_id ='".$urlId."'";
		$q = $mysqli->query($sqlChk);
		
		//if($q->num_rows > 0) $check = FALSE;
		
		$mysqli->close();
		
		if($check){
			
			include_once('simple_html_dom/simple_html_dom.php');
			
			$imdb_url = 'http://www.imdb.com/title/'.$urlId;
			$omdb_url = "http://www.omdbapi.com/?i=".$urlId."&tomatoes=true";
			$html = file_get_html($imdb_url);
			$json = json_decode(file_get_contents($omdb_url));
			
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
				$audience = checkStr($json->Rated, false);
				$release_date = checkStr($json->Released, false);
				$runtime = checkStr(str_replace(" min", "", $json->Runtime), false);
				$genre = '|'.str_replace(", ", "||", $json->Genre).'|';
				$plot = checkStr(str_replace("'", "\'", $json->Plot), false);
				$country = checkStr(str_replace(", ", "||", $json->Country), true);
				$languages = checkStr(str_replace("'", "\'", str_replace(", ", "||", $json->Language)), true);
				$awards = checkStr(str_replace("'", "\'", $json->Awards), false);
				$poster = checkStr($json->Poster, false);
				$metascore = checkStr($json->Metascore, false);
				$imdb_rating = checkStr($json->imdbRating, false);
				$tomato_meter = checkStr($json->tomatoMeter, false);
				$box_office = checkStr($json->BoxOffice, false);
				$production = checkStr($json->Production, false);
				$website = checkStr($json->Website, false);
				$poster_path = '';
				
				if($poster != ''){
					file_put_contents("data/movies/".$urlId.".jpg", fopen($poster, 'r'));
					$poster_path = "/data/movies/".$urlId.".jpg";
				}
				
				if($release_date != ''){
					$release_date = date("Y-m-d", strtotime($release_date));
				}
				
				//DATA FROM IMDB
				foreach($html->find('#pagecontent') as $tag){
					
					$original_title = trim(str_replace("'", "\'", $tag->find('h1.header span', 0)->plaintext), ' ');
					
					if($title == $original_title) $original_title = "";
	
					if($tag->find('a.title-trailer', 0) != '') $imdb_trailer = $tag->find('a.title-trailer', 0)->getAttribute('data-video'); else $imdb_trailer = '';
					
					if($country == ''){
					
						foreach($tag->find('#titleDetails .txt-block a[href^=/country/]') as $subtag){
		
							$country .= '|'.$subtag->plaintext.'|';
				
						}
						
					}
					
					if($languages == ''){
						
						if($tag->find('#titleDetails .txt-block a[href^=/language/]', 0) != ''){
							
							foreach($tag->find('#titleDetails .txt-block a[href^=/language/]') as $subtag){
			
								$languages .= '|'.$subtag->plaintext.'|';
					
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
					$cast_photo = '';
					
					foreach($tag->find('#titleStoryLine .txt-block') as $subtag){

						if($subtag->find('h4', 0) != '' && $subtag->find('h4', 0)->plaintext == "Taglines:")
							$tagline = trim(str_replace("'", "\'", str_replace("See more&nbsp;&raquo;", "", str_replace("Taglines:", "", $subtag->plaintext))), " ");
			
					}
			
					foreach($tag->find('#titleCast .cast_list td.primary_photo') as $subtag){
			
						$cast .= '|'.trim(str_replace("'", "\'", $subtag->find('img', 0)->getAttribute('alt')), ' ').'|';
						$temp = explode('/', $subtag->find('a', 0)->href);
						$cast_imdb_id .= '|'.$temp[2].'|';
						$photo = $subtag->find('img', 0)->getAttribute('loadlate');
						
						if($photo != ''){
							if(!file_exists("data/actors/".$temp[2].".jpg")) 
								file_put_contents("data/actors/".$temp[2].".jpg", fopen($photo, 'r'));
							$cast_photo .= "|/data/actors/".$temp[2].".jpg|";	
						}else{
							$cast_photo .= "|NONE|";	
						}
			
					}
			
					foreach($tag->find('#titleCast .cast_list td[itemprop=actor]') as $subtag){
			
						$cast .= '|'.trim(str_replace("'", "\'", $subtag->find('a', 0)->plaintext), ' ').'|';
						$temp = explode('/', $subtag->find('a', 0)->href);
						$cast_imdb_id .= '|'.$temp[2].'|';
			
					}
					
					foreach($tag->find('#overview-top div[itemprop=director] a[href^=/name/]') as $subtag){
						
						$director .= '|'.trim(str_replace("'", "\'", $subtag->find('span[itemprop=name]', 0)->plaintext), ' ').'|';
						$temp = explode('/', $subtag->href);
						$director_imdb_id .= '|'.$temp[2].'|';
					
					}
					
					foreach($tag->find('#overview-top div[itemprop=creator] a[href^=/name/]') as $subtag){
						
						$writer .= '|'.trim(str_replace("'", "\'", $subtag->find('span[itemprop=name]', 0)->plaintext), ' ').'|';
						$temp = explode('/', $subtag->href);
						$writer_imdb_id .= '|'.$temp[2].'|';
					
					}
					
					foreach($tag->find('#titleCast .cast_list td.character') as $subtag){
			
						$characters .= '|'.trim(str_replace("'", "\'", trim($subtag->find('div', 0)->plaintext, ' ')), ' ').'|';
			
					}
					
					$result = $title;
					
					/*$mysqli = new mysqli($host,$user,$pass,$dbName);
	
					if ($mysqli->connect_errno){
						
						$result = '<span class="error">Movie: <i>'.$title.'</i> is failed!!! -> '.$mysqli->connect_error.'</span>';
						exit();
						
					}else{
					
						$sql = "INSERT INTO mvs_movies_new(title, original_title, poster_path, year, plot, tagline, runtime, genre, country, languages, imdb_rating, director, director_imdb_id, writer, writer_imdb_id, cast, cast_imdb_id, cast_photo, characters, audience, imdb_trailer, imdb_id, release_date, awards, metascore, tomato_meter, box_office, production, website) VALUES (N'".$title."',N'".$original_title."','".$poster_path."','".$year."',N'".$plot."',N'".$tagline."','".$runtime."',N'".$genre."',N'".$country."',N'".$languages."','".$imdb_rating."',N'".$director."','".$director_imdb_id."',N'".$writer."','".$writer_imdb_id."',N'".$cast."','".$cast_imdb_id."','".$cast_photo."',N'".$characters."','".$audience."','".$imdb_trailer."','".$urlId."','".$release_date."',N'".$awards."','".$metascore."','".$tomato_meter."',N'".$box_office."',N'".$production."','".$website."')";
							
						if($mysqli->query($sql)){
							
							$result = 'Movie: <i>'.$title.'</i> is done!!!';
							
							
						}else{
							
							$result = '<span class="error">Movie: <i>'.$title.'</i> is failed!!! -> '.$mysqli->error.'</span>';
							
						}
						
						$mysqli->close();
						
					}*/
					
				}
				
				$html->clear(); 
				unset($html);
				
			}else{
			
				$result = 'Movie: <i>'.$urlId.'</i> not found in OMDB API!!!';
					
			}
	
		}else{
			
			$result = 'Movie already exist!!!';
				
		}
		
		echo $result;
		//logger($result);

	}

?>
