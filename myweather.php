<html>
<title>
The Weather Underground
</title>

<body>

<h1>The Weather Underground PHP Scraper</h1>
<hr>

<?php
 $arg_firsttime = 0;
 import_request_variables("p", "arg_");
 
 if($arg_firsttime == 0){
?>
<form action="myweather.php" method="post">
  <input type="hidden" 
         name="firsttime"
         value="1">
  airport: <input type="text" 
               name="airport"> <br/>
  year: <input type="text"
                name="year"> <br/>
  month: <input type="text"
                name="month"> <br/>
  day: <input type="text"
                name="day"> <br/>
  function: <input type="text"
                name="function"> <br/>
  <input type="submit" name="submit"
         value="Submit!">
</form>

<?php
} else {

	$days = 0;

	#ensure entered month is valid
	if($arg_month <= 0 || $arg_month > 12){
		echo "Invalid month entered\n";
		exit(1);
	}
	
	#ensure entered year is valid
	if($arg_year <= 1900 || $arg_year > 3000){
		echo "Invalid year entered\n";
		exit(1);
	}

	#ensure that the airport entered is valid before proceeding
	testAirport($arg_airport,$arg_year,$arg_month);

	#output
	echo "Station: $arg_airport</br>";
	
	#determine which command we are doing
	if($arg_function == 'average'){
		echo "Query: average</br>";
		average($arg_airport,$arg_year,$arg_month,$arg_day);
	}
	else if($arg_function == 'highest'){
		echo "Query: highest</br>";
		highest($arg_airport,$arg_year,$arg_month,$arg_day);
	}
	else if($arg_function == 'lowest'){
		echo "Query: lowest</br>";
		lowest($arg_airport,$arg_year,$arg_month,$arg_day);
	}
	else if($arg_function == 'conditions'){
		echo "Query: conditions</br>";
		conditions($arg_airport,$arg_year,$arg_month,$arg_day);
	}
	else{
		echo "Invalid command specified.</br>";
		exit(1);
	}
	echo "<hr>";
}
	function average($airport, $year, $month, $day){
		$totaltemp = 0.0;
		$highesttemp = -100;
		$lowesttemp = 100;
		$temphash = array();
		$days = determineDays($month);

		$currentday = 1;
		
		if($day != null){
			if($day < 1 || $day > $days){
				echo "Specific day is out of range.<br />";
				exit(1);
			}
			$currentday = $day;
			$days = $day;
		}

		for($currentday; $currentday < ($days+1); $currentday++){
		
			#set total temp to zero
			$totaltemp = 0.0;
			
			#grab the content for the day 
			$thestring = '';
			$thestring = 'http://www.wunderground.com/history/airport/'.$airport.'/'.$year.'/'.$month.'/'.$currentday.'/DailyHistory.html?format=1';
			
			$content = file_get_contents($thestring);
			
			$temperaturerecords = explode("\n", $content);
			
			#shift off the unwanted data
			array_shift($temperaturerecords);
			array_shift($temperaturerecords);
			$j = 0;
			for($j = 0; $j < count($temperaturerecords); $j++)
			{
				$record = $temperaturerecords[$j];
				$currentrecord = explode(",", $record);
				
				if(isset($currentrecord[1]))
				{
					preg_match("/^[+-]?\d+(\.\d+)?$/", $currentrecord[1], $matches2);
					if($matches2 >= 1){
						$totaltemp += floatval($currentrecord[1]);
					}
				}
			}
			

			$numofrecs = count($temperaturerecords);
			$theaverage = ($totaltemp/$numofrecs);
			
			echo returnMonth($month)." ".$currentday.", ".$year.": ".round($theaverage, 1, PHP_ROUND_HALF_UP)."F</br>";
			
			#check found average against the running high and low
			if($theaverage > $highesttemp){
				$highesttemp = $theaverage;
			}
			if($theaverage < $lowesttemp){
				$lowesttemp = $theaverage;
			}
		
			#record the average into the hash
			$temphash[$currentday] = $theaverage;
		}
		
		if($day == null)
		{
		
		#echo out highest averages
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash[$currentday] == $highesttemp){
				echo "Highest Average: ".round($temphash[$currentday], 1, PHP_ROUND_HALF_UP)."F (".returnMonth($month)." ".$currentday.", ".$year.")</br>";
			}
		}
		#print out lowest averages
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash[$currentday] == $lowesttemp){
				echo "Lowest Average: ".round($temphash[$currentday], 1, PHP_ROUND_HALF_UP)."F (".returnMonth($month)." ".$currentday.", ".$year.")</br>";
			}
		}
		}
	}

	function lowest($airport, $year, $month){
		$lowesttempday = 500;
		$lowesttempmonth = 500;
		$highestlowestmonth = -100;
		$temphash = array();
		$days = determineDays($month);
		
		for($currentday = 1; $currentday < ($days+1); $currentday++){
		
			#reset lowesttempday
			$lowesttempday = 500;
			
			#grab the content for the day 
			$thestring = '';
			$thestring = 'http://www.wunderground.com/history/airport/'.$airport.'/'.$year.'/'.$month.'/'.$currentday.'/DailyHistory.html?format=1';
			
			$content = file_get_contents($thestring);
			
			$temperaturerecords = explode("\n", $content);
			
			#shift off the unwanted data
			array_shift($temperaturerecords);
			array_shift($temperaturerecords);
			$j = 0;
			for($j = 0; $j < count($temperaturerecords); $j++)
			{
				$record = $temperaturerecords[$j];
				$currentrecord = explode(",", $record);
				
				if(isset($currentrecord[1]))
				{
					if($currentrecord[1] < $lowesttempday){
						$lowesttempday = $currentrecord[1];
					}
				}
			}
			
			if($lowesttempday < $lowesttempmonth){
				$lowesttempmonth = $lowesttempday;
			}
			if($lowesttempday > $highestlowestmonth){
				$highestlowestmonth = $lowesttempday;
			}
			
			echo returnMonth($month)." ".$currentday.", ".$year.": ".round($lowesttempday, 1, PHP_ROUND_HALF_UP)."F</br>";
			
			#record the temp into the hash
			$temphash[$currentday] = $lowesttempday;
		}
		
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash[$currentday] == $highestlowestmonth){
				echo "Highest Lowest: ".round($temphash[$currentday], 1, PHP_ROUND_HALF_UP)."F (".returnMonth($month)." ".$currentday.", ".$year.")</br>";
			}
		}
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash{$currentday} == $lowesttempmonth){
				echo "Lowest Lowest: ".round($temphash[$currentday], 1, PHP_ROUND_HALF_UP)."F (".returnMonth($month)." ".$currentday.", ".$year.")</br>";
			}
		}
	}
	
	function highest($airport, $year, $month){
		$highesttempday = -100;
		$highesttempmonth = -100;
		$lowesthighestmonth = 500;
		$temphash = array();
		$days = determineDays($month);
		
		for($currentday = 1; $currentday < ($days+1); $currentday++){
		
			#reset highesttmpday
			$highesttempday = -100;
			
			#grab the content for the day 
			$thestring = '';
			$thestring = 'http://www.wunderground.com/history/airport/'.$airport.'/'.$year.'/'.$month.'/'.$currentday.'/DailyHistory.html?format=1';
			
			$content = file_get_contents($thestring);
			
			$temperaturerecords = explode("\n", $content);
			
			#shift off the unwanted data
			array_shift($temperaturerecords);
			array_shift($temperaturerecords);
			$j = 0;
			for($j = 0; $j < count($temperaturerecords); $j++)
			{
				$record = $temperaturerecords[$j];
				$currentrecord = explode(",", $record);
				
				if(isset($currentrecord[1]))
				{
					if($currentrecord[1] > $highesttempday){
						$highesttempday = $currentrecord[1];
					}
				}
			}
			
			#check if this is the highest temp of the month
			if($highesttempday > $highesttempmonth){
				$highesttempmonth = $highesttempday;
			}
			#check if this is the lowest highest temp of the month
			if($highesttempday < $lowesthighestmonth){
				$lowesthighestmonth = $highesttempday;
			}
			
			echo returnMonth($month)." ".$currentday.", ".$year.": ".round($highesttempday, 1, PHP_ROUND_HALF_UP)."F</br>";
			
			#record the temp into the hash
			$temphash[$currentday] = $highesttempday;
		}
		
		#print out highest days
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash[$currentday] == $highesttempmonth){
				echo "Highest Highest: ".round($temphash[$currentday], 1, PHP_ROUND_HALF_UP)."F (".returnMonth($month)." ".$currentday.", ".$year.")</br>";
			}
		}
		#print out lowest highest
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash[$currentday] == $lowesthighestmonth){
				echo "Lowest Highest: ".round($temphash[$currentday], 1, PHP_ROUND_HALF_UP)."F (".returnMonth($month)." ".$currentday.", ".$year.")</br>";
			}
		}
		
	
	}
	
	function conditions($airport, $year, $month){
	
		$count = array();
		$conditioncount = 0;
		$days = determineDays($month);
		
		for($currentday = 1; $currentday < ($days+1); $currentday++){
			#grab the content for the day 
			$thestring = '';
			$thestring = 'http://www.wunderground.com/history/airport/'.$airport.'/'.$year.'/'.$month.'/'.$currentday.'/DailyHistory.html?format=1';
			
			$content = file_get_contents($thestring);
			
			$temperaturerecords = explode("\n", $content);
			
			#shift off the unwanted data
			array_shift($temperaturerecords);
			array_shift($temperaturerecords);
			$j = 0;
			for($j = 0; $j < count($temperaturerecords); $j++)
			{
				$record = $temperaturerecords[$j];
				$currentrecord = explode(",", $record);
				
				if(isset($currentrecord[1]))
				{
					$conditioncount++;
					if(array_key_exists($currentrecord[11], $count)){
						#condition has already been seen, increase the count
						$count[$currentrecord[11]]++;
					}
					#else the condition has not been seen, initialize it
					else{
						$count[$currentrecord[11]] = 1;
					}
				}
			}
		}
		#output the data for the entire month
		arsort($count);
		foreach($count as $key=>$value) 
		{ 
			$percentage = (($count[$key])/count($count));
			echo (round($percentage, 1, PHP_ROUND_HALF_UP)."% ".$key."</br>");
		} 
	}

function returnMonth($month) {
	if($month == 1){ return "January"; }
	else if($month == 2){ return "February"; }
	else if($month == 3){ return "March"; }
	else if($month == 4){ return "April"; }
	else if($month == 5){ return "May"; }
	else if($month == 6){ return "June"; }
	else if($month == 7){ return "July"; }
	else if($month == 8){ return "August"; }
	else if($month == 9){ return "September"; }
	else if($month == 10){ return "October"; }
	else if($month == 11){ return "November"; }
	else if($month == 12){ return "December"; }
}

function testAirport($airport,$year,$month) {

		$thestring = 'http://www.wunderground.com/history/airport/'.$airport.'/'.$year.'/'.$month.'/1/DailyHistory.html?format=1';
		$content = file_get_contents($thestring);
		
		$therecords = explode("<br />", $content);
		
		#shift off the unwanted data
		array_shift($therecords);

		
		if(preg_match("/No daily or hourly history data available/i",$therecords[0]))
		{
			echo "Location or year might not be correct!</br>";
			exit(1);
		}
}

function determineDays($month){
	#determine number of days in given month
	if($month == 9 || $month == 4 || $month ==  6 || $month == 11){
		$days = 30;
	}
	else if($month == 2){
		$days = 28;
	}
	else{
		$days = 31;
	}
	return $days;
}
?>
</body>
</html>

