<html>
<body>

<?php
// FILE: filter.php

$my_url = "http://db.cs.pitt.edu/courses/cs1520/fall2012/exams/m1.transactions.txt";


if (isset($_GET['account'])) {
	$account = $_GET['account'];
	print "<h1>Account number $account</h1> <hr>\n";

	$ch = curl_init($my_url);

	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_REFERER, $my_url);

	$answer = curl_exec ($ch);
	
	print "<pre>\n$answer\n</pre><hr>";

	$lines = preg_split("/\n/", $answer);
	print ("<pre>\n");
	print_r($lines);
	print ("</pre><hr>\n");

	foreach ($lines as $oneline) {
		print ("<pre>BEFORE: $oneline</pre>\n");
		$oneline = preg_replace("/#.*$/", "", $oneline);
		print ("<pre>AFTER : $oneline</pre>\n");
	}
	print ("<pre>\n");
	print_r($lines);
	print ("</pre><hr>\n");

	$newlines = preg_replace("/#.*$/", "", $lines);
	print ("<pre>\n");
	print_r($newlines);
	print ("</pre><hr>\n");
}

?>

</body>

