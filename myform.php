<html>
<title>
The Weather Underground
</title>

<body>

<?php
 $arg_firsttime = 0;
 import_request_variables("p", "arg_");
 
 if(! $arg_firsttime){
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
  function: <input type="text"
                name="day"> <br/>
  <input type="submit" name="submit"
         value="Submit!">
</form>

<?php
} else {

	$days = 0;

	#ensure entered month is valid
	if($arg_month <= 0 || $arg_month > 12){
		echo "Invalid month entered\n";
		exit 1;
	}
	
	
	#determine number of days in given month
	if($arg_month == 9 || $arg_month == 4 || $arg_month ==  6 || $arg_month == 11){
		$days = 30;
	}
	else if($month == 2){
		$days = 28;
	}
	else{
		$days = 31;
	}

	#ensure that the airport entered is valid before proceeding
	testAirport($arg_airport);

	#output
	echo "Station: $airport\n";
	
	#determine which command we are doing
	if($arg_function == 'average'){
		echo "Query: average\n";
		average();
	}
	else if($arg_function == 'highest'){
		echo "Query: highest\n";
		highest();
	}
	else if($arg_function == 'lowest'){
		echo "Query: lowest\n";
		lowest();
	}
	else if($arg_function == 'precipitation'){
		echo "Query: precipitation\n";
		precipitation();
	}
	else if($arg_function == 'conditions'){
		echo "Query: conditions\n";
		conditions();
	}
	else{
		echo "Invalid command specified.\n";
		exit 1;
	}
	
	function average($airport, $year, $month){
		$totaltemp = 0.0;
		$highesttemp = -100;
		$lowesttemp = 100;
		temphash = ();
		
		for($currentday = 1; $currentday < $days+1; $currentday++){
		
			#set total temp to zero
			$totaltemp = 0.0
			
			#grab the content for the day 
			$content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
			
			$temperaturerecords = split("\n", $content);
			
			#shift off the unwanted data
			shift($temperaturerecords);
			shift($temperaturerecords);
			
			foreach $record ($temperaturerecords){
				$currentrecord = split(",", $record);
				if($currentrecord[0] =~ m/No daily or hourly history data available/){
					echo "Possible incorrect airport code. No daily or hourly history data available for this date\n";
				}
				else if(defined $currentrecord[1] && $currentrecord[1] =~ m/^[+-]?\d+(\.\d+)?$/){
					$totaltemp += $currentrecord[1];
				}
			}
			$numofrecs = $temperaturerecords;
			$theaverage = int($totaltemp/$numofrecs);
			
			echo returnMonth($month)." ".$currentday.", ".$year.": ".$theaverage."F\n";
			
			#check found average against the running high and low
			if($theaverage > $highesttemp){
				$highesttemp = $theaverage;
			}
			if($theaverage < $lowesttemp){
				$lowesttemp = $theaverage;
			}
		
			#record the average into the hash
			$temphash{$currentday} = $theaverage;
		}
		
		#echo out highest averages
		for(my $currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash{$currentday} == $highesttemp){
				echo "Highest Average: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
			}
		}
		#print out lowest averages
		for($currentday = 1; $currentday < $days+1; $currentday++){
			if($temphash{$currentday} == $lowesttemp){
				echo "Lowest Average: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
			}
		}
	}

#lowest temperature function
#iterate through and overwrite the current temperature if the new one is lower
function lowest {
	$lowesttempday = 500;
	$lowesttempmonth = 500;
	$highestlowestmonth = -100;
	%temphash = ();
	
	#iterate through the days of the month and collect results
	for($currentday = 1; $currentday < $days+1; $currentday++){
		#reset lowesttempday
		$lowesttempday = 500;
		
		#grab the content for this day
		$content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		
		$temperaturerecords = split("\n", $content);
		
		#shift off the unwanted data
		shift(@temperaturerecords);
		shift(@temperaturerecords);

		
		foreach $record ($temperaturerecords){
			$currentrecord = split(",", $record);
			if($currentrecord[0] =~ m/No daily or hourly history data available/){
				echo "Possible incorrect airport code. No daily or hourly history data available for this date\n";
			}
			else if (defined $currentrecord[1] && $currentrecord[1] =~ m/^[+-]?\d+(\.\d+)?$/) {
				#check if this temperature is the highest seen for this day
				
				#convert to an integer
				$currentrecord[1] = int($currentrecord[1]);
				
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
		
		echo returnMonth($month)." ".$currentday.", ".$year.": ".$lowesttempday."F\n";
		
		#record the temp into the hash
		$temphash{$currentday} = $lowesttempday;
		
	}

	for($currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $highestlowestmonth){
			echo "Highest Lowest: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
	for($currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $lowesttempmonth){
			echo "Lowest Lowest: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
}

#precipitation function
#This should add up the precipitation for everyday of the month and print the total precip
#replace T (short for trace) from the weather data with a 0.0 value
function precipitation {
	$mytotalprecipmonth = 0.0;
	$totalprecipday = 0.0;
	
	#iterate through the days of the month and collect results
	for($currentday = 1; $currentday < $days+1; $currentday++){
		
		#set total precip for the day to zero
		$totalprecipday = 0.0;
	
		#grab the content for this day
		$content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		
		$therecords = split("\n", $content);
		
		#shift off the unwanted data
		shift($therecords);
		shift($therecords);

		foreach $record ($therecords){
			$currentrecord = split(",", $record);
			if($currentrecord[0] =~ m/No daily or hourly history data available/){
				echo "Possible incorrect airport code. No daily or hourly history data available for this date\n";
			}
			else if (defined $currentrecord[9] && $currentrecord[9] =~ m/^[+-]?\d+(\.\d+)?$/) {
				$totalprecipday += $currentrecord[9];
			}
		}
		
		#add this days total to the monthly total
		$totalprecipmonth += $totalprecipday;
		
		#output the data for this day
		echo returnMonth($month)." ".$currentday.", ".$year.": ".sprintf("%.2f",$totalprecipday)." in\n";
	}
	#output the data for the entire month
	echo "Total precip: ".sprintf("%.2f",$totalprecipmonth)." in\n";
}

#conditions frequency function
#this should collect the conditions data for the entire month and report their frequency
#displaying them in order of decreasing frequency (most frequent conditions first)
function conditions {

	#initialize the hash table
	%count = ();
	
	$conditioncount = 0;

	#iterate through the days of the month and collect results
	for($currentday = 1; $currentday < $days+1; $currentday++){

		#grab the content for this day
		$content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		
		$therecords = split("\n", $content);
		
		#shift off the unwanted data
		shift($therecords);
		shift($therecords);

		foreach $record ($therecords){
			$currentrecord = split(",", $record);
			#check if current condition is valid
			if (defined $currentrecord[11]) {
				if($currentrecord[0] =~ m/No daily or hourly history data available/){
					echo "Possible incorrect airport code. No daily or hourly history data available for this date\n";
				}
				else{
					$conditioncount++;
					#check if current condition has already been seen
					if(defined($count{$currentrecord[11]})){
						#condition has already been seen, increase the count
						$count{$currentrecord[11]}++;
					}
					#the condition has not been seen, initialize it
					else{
						$count{$currentrecord[11]} = 1;
					}
				}
			}
		}
	}
	#output the data for the entire month
	$conditions = keys(%count);
    $frequency = sort { $count{$b} <=> $count{$a} } $conditions;
	foreach $currcondition ($frequency){
		$percentage = (($count{$currcondition})/$conditioncount);
		echo ($percentage."% ".$currcondition."\n");
	}
	
	#print Dumper(\%count);
	
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

		$content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/1/DailyHistory.html?format=1");
		
		$therecords = split("<br />", $content);
		
		#shift off the unwanted data
		shift($therecords);
		
		if($therecords[0]=~ m/No daily or hourly history data available/){
			print "Location might not be correct!\n";
			exit 1;
		}
}

?>
</body>
</html>
