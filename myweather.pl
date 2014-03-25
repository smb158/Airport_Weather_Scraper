#!/usr/bin/perl -w


#Steven Bauer
#Feb 10th 2013
#myweather

use Getopt::Long;
use LWP::Simple;
use POSIX;
#use Data::Dumper;

#initialize the variables
my $airport;
my $year;
my $month;
my $days;

#commands
my $average;
my $highest;
my $lowest;
my $precipitation;
my $conditions;

#grab the commandline arguments
my $result = GetOptions (
	"airport=s" => \$airport,
	"year=i" => \$year,
	"month=i" => \$month,
	"average" => \$average,
	"highest" => \$highest,
	"lowest" => \$lowest,
	"precipitation" => \$precipitation,
	"conditions" => \$conditions,
) or die "An error has occured in parsing the arguments";

#exit 1;

if($month <= 0 || $month > 12){
	print "Invalid month entered\n";
}

#determine number of days in given month
if($month == 9 || $month == 4 || $month ==  6 || $month == 11){
	$days = 30;
}
elsif($month == 2){
	$days = 28;
}
else{
	$days = 31;
}

#ensure that the airport entered is valid before proceeding
testAirport();

#output
print "Station: $airport\n";

#determine which command we are doing
if(defined $average){
	print "Query: average\n";
	average();
}
elsif(defined $highest){
	print "Query: highest\n";
	highest();
}
elsif(defined $lowest){
	print "Query: lowest\n";
	lowest();
}
elsif(defined $precipitation){
	print "Query: precipitation\n";
	precipitation();
}
elsif(defined $conditions){
	print "Query: conditions\n";
	conditions();
}
else{
	print "Invalid command specified.\n";
	exit 1;
}


#average temperature function
#iterate though and grab the second value of each line, add them up and divide by the total
sub average {
	my $totaltemp = 0.0;
	my $highesttemp = -100;
	my $lowesttemp = 100;
	my %temphash = ();
	
	#iterate through the days of the month and collect results
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		
		#set total temp to zero
		$totaltemp = 0.0;
	
		#grab the content for this day
		my $content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		die "Couldn't retrieve the data" unless defined $content;
		
		my @temperaturerecords = split("\n", $content);
		
		#shift off the unwanted data
		shift(@temperaturerecords);
		shift(@temperaturerecords);

		foreach my $record (@temperaturerecords){
			my @currentrecord = split(",", $record);
			if($currentrecord[0] =~ m/No daily or hourly history data available/){
				print "Possible incorrect airport code. No daily or hourly history data available for this date\n";
			}
			elsif (defined $currentrecord[1] && $currentrecord[1] =~ m/^[+-]?\d+(\.\d+)?$/) {
				$totaltemp += $currentrecord[1];
			}
		}
		my $numofrecs = @temperaturerecords;
		my $theaverage = int($totaltemp/$numofrecs);
		
		print returnMonth($month)." ".$currentday.", ".$year.": ".$theaverage."F\n";
		
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
	#print out highest averages
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $highesttemp){
			print "Highest Average: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
	#print out lowest averages
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $lowesttemp){
			print "Lowest Average: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
}

#highest temperature function
#iterate through and overwrite the current temperature if the new one is higher
sub highest {
	my $highesttempday = -100;
	my $highesttempmonth = -100;
	my $lowesthighestmonth = 500;
	my %temphash = ();
	
	#iterate through the days of the month and collect results
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		#reset highesttempday
		$highesttempday = -100;
		
		#grab the content for this day
		my $content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		die "Couldn't retrieve the data" unless defined $content;
		
		my @temperaturerecords = split("\n", $content);
		
		#shift off the unwanted data
		shift(@temperaturerecords);
		shift(@temperaturerecords);

		foreach my $record (@temperaturerecords){
			my @currentrecord = split(",", $record);
			if($currentrecord[0] =~ m/No daily or hourly history data available/){
				print "Possible incorrect airport code. No daily or hourly history data available for this date\n";
			}
			elsif (defined $currentrecord[1] && $currentrecord[1] =~ m/^[+-]?\d+(\.\d+)?$/) {
				#check if this temperature is the highest seen for this day
				
				#convert to an integer
				$currentrecord[1] = int($currentrecord[1]);
				
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
		
		print returnMonth($month)." ".$currentday.", ".$year.": ".$highesttempday."F\n";
		
		#record the average into the hash
		$temphash{$currentday} = $highesttempday;
		
	}
	#print out highest days
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $highesttempmonth){
			print "Highest Highest: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
	#print out lowest highest
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $lowesthighestmonth){
			print "Lowest Highest: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
}

#lowest temperature function
#iterate through and overwrite the current temperature if the new one is lower
sub lowest {
	my $lowesttempday = 500;
	my $lowesttempmonth = 500;
	my $highestlowestmonth = -100;
	my %temphash = ();
	
	#iterate through the days of the month and collect results
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		#reset lowesttempday
		$lowesttempday = 500;
		
		#grab the content for this day
		my $content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		die "Couldn't retrieve the data" unless defined $content;
		
		my @temperaturerecords = split("\n", $content);
		
		#shift off the unwanted data
		shift(@temperaturerecords);
		shift(@temperaturerecords);

		
		foreach my $record (@temperaturerecords){
			my @currentrecord = split(",", $record);
			if($currentrecord[0] =~ m/No daily or hourly history data available/){
				print "Possible incorrect airport code. No daily or hourly history data available for this date\n";
			}
			elsif (defined $currentrecord[1] && $currentrecord[1] =~ m/^[+-]?\d+(\.\d+)?$/) {
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
		
		print returnMonth($month)." ".$currentday.", ".$year.": ".$lowesttempday."F\n";
		
		#record the temp into the hash
		$temphash{$currentday} = $lowesttempday;
		
	}

	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $highestlowestmonth){
			print "Highest Lowest: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		if($temphash{$currentday} == $lowesttempmonth){
			print "Lowest Lowest: ".$temphash{$currentday}."F (".returnMonth($month)." ".$currentday.", ".$year.")\n";
		}
	}
}

#precipitation function
#This should add up the precipitation for everyday of the month and print the total precip
#replace T (short for trace) from the weather data with a 0.0 value
sub precipitation {
	my $mytotalprecipmonth = 0.0;
	my $totalprecipday = 0.0;
	
	#iterate through the days of the month and collect results
	for(my $currentday = 1; $currentday < $days+1; $currentday++){
		
		#set total precip for the day to zero
		$totalprecipday = 0.0;
	
		#grab the content for this day
		my $content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		die "Couldn't retrieve the data" unless defined $content;
		
		my @therecords = split("\n", $content);
		
		#shift off the unwanted data
		shift(@therecords);
		shift(@therecords);

		foreach my $record (@therecords){
			my @currentrecord = split(",", $record);
			if($currentrecord[0] =~ m/No daily or hourly history data available/){
				print "Possible incorrect airport code. No daily or hourly history data available for this date\n";
			}
			elsif (defined $currentrecord[9] && $currentrecord[9] =~ m/^[+-]?\d+(\.\d+)?$/) {
				$totalprecipday += $currentrecord[9];
			}
		}
		
		#add this days total to the monthly total
		$totalprecipmonth += $totalprecipday;
		
		#output the data for this day
		print returnMonth($month)." ".$currentday.", ".$year.": ".sprintf("%.2f",$totalprecipday)." in\n";
	}
	#output the data for the entire month
	print "Total precip: ".sprintf("%.2f",$totalprecipmonth)." in\n";
}

#conditions frequency function
#this should collect the conditions data for the entire month and report their frequency
#displaying them in order of decreasing frequency (most frequent conditions first)
sub conditions {

	#initialize the hash table
	my %count = ();
	
	my $conditioncount = 0;

	#iterate through the days of the month and collect results
	for(my $currentday = 1; $currentday < $days+1; $currentday++){

		#grab the content for this day
		my $content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/".$currentday."/DailyHistory.html?format=1");
		die "Couldn't retrieve the data" unless defined $content;
		
		my @therecords = split("\n", $content);
		
		#shift off the unwanted data
		shift(@therecords);
		shift(@therecords);

		foreach my $record (@therecords){
			my @currentrecord = split(",", $record);
			#check if current condition is valid
			if (defined $currentrecord[11]) {
				if($currentrecord[0] =~ m/No daily or hourly history data available/){
					print "Possible incorrect airport code. No daily or hourly history data available for this date\n";
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
	my @conditions = keys(%count);
    my @frequency = sort { $count{$b} <=> $count{$a} } @conditions;
	foreach my $currcondition (@frequency){
		my $percentage = (($count{$currcondition})/$conditioncount);
		print ($percentage."% ".$currcondition."\n");
	}
	
	#print Dumper(\%count);
	
}

sub returnMonth {
	my($month) = @_;
	
	if($_[0] == 1){ return "January"; }
	elsif($_[0] == 2){ return "February"; }
	elsif($_[0] == 3){ return "March"; }
	elsif($_[0] == 4){ return "April"; }
	elsif($_[0] == 5){ return "May"; }
	elsif($_[0] == 6){ return "June"; }
	elsif($_[0] == 7){ return "July"; }
	elsif($_[0] == 8){ return "August"; }
	elsif($_[0] == 9){ return "September"; }
	elsif($_[0] == 10){ return "October"; }
	elsif($_[0] == 11){ return "November"; }
	elsif($_[0] == 12){ return "December"; }
}

sub testAirport {

		my $content = get("http://www.wunderground.com/history/airport/".$airport."/".$year."/".$month."/1/DailyHistory.html?format=1");
		die "Couldn't retrieve the data" unless defined $content;
		
		my @therecords = split("<br />", $content);
		
		#shift off the unwanted data
		shift(@therecords);
		
		if($therecords[0]=~ m/No daily or hourly history data available/){
			print "Location might not be correct!\n";
			exit 1;
		}


}

