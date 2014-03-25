<?php

	echo "TRANSCRIPT-START<br>";

	$studentspage = file_get_contents("m1.students.txt");
	$studentslines = explode("\n",$studentspage);
	
	$gradespage = file_get_contents("m1.grades.txt");
	$gradeslines = explode("\n",$gradespage);
	
	echo "STUDENT FILE:<br>";
	foreach ($studentslines as $currentline){
		if(preg_match("/^#/", $currentline) == 1){
		}
		else{
			echo $currentline."<br>";
		}
	}
	echo "<br><br>";
	echo "GRADES FILE:<br>";
	foreach ($gradeslines as $currentline){
		if(preg_match("/^#/", $currentline) == 1){
		}
		else{
			echo $currentline."<br>";
		}
	}

	echo "TRANSCRIPT-END<br>";
	
?>