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
		
		if($q->num_rows == 0) $check = FALSE;
		
		$mysqli->close();
		
		if($check){
			
			$imdb_url = 'http://www.imdb.com/title/'.$urlId.'/fullcredits';
			
      include_once('simple_html_dom/simple_html_dom.php');
      
      $html = file_get_html($imdb_url);

    if($html){
      
      //DATA FROM IMDB
      foreach($html->find('.cast_list') as $tag){
        
        $cast = '';
        $characters = '';
        $cast_imdb_id = '';
        $cast_photo = '';
      
        foreach($tag->find('td.primary_photo') as $subtag){
      
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
        
        foreach($tag->find('td.character') as $subtag){
      
          $characters .= '|'.trim(str_replace("'", "\'", trim($subtag->find('div', 0)->plaintext, ' ')), ' ').'|';
      
        }
        
        $mysqli = new mysqli($host,$user,$pass,$dbName);
      
        if ($mysqli->connect_errno){
          
          $result = '<span class="error">Movie: <i>('.$urlId.')</i> is failed!!! -> '.$mysqli->connect_error.'</span>';
          exit();
          
        }else{
          
          $sql = "UPDATE mvs_movies_new SET cast=N'".$cast."', cast_imdb_id=N'".$cast_imdb_id."', cast_photo=N'".$cast_photo."', characters=N'".$characters."' WHERE imdb_id='".$urlId."'";
            
          if($mysqli->query($sql)){
            
            $result = 'Movie: <i>('.$urlId.')</i> detailed cast list is done!!!';
            
            
          }else{
            
            $result = '<span class="error">Movie: <i>('.$urlId.')</i> is failed!!! -> '.$mysqli->error.'</span>';
            
          }
          
          $mysqli->close();
          
        }
        
      }
      
      $html->clear(); 
      unset($html);
    }else{
      
      $result = 'Can`t reach the page!!!';
      
    }
	
		}else{
			
			$result = 'Movie not found!!!';
				
		}
		
		echo $result;
		logger($result);

	}

?>
