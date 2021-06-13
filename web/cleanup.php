<?php

$maxAgeSec = 4*7*24*60*60;
$today = time();
$dataPath = dirname(__FILE__).'/../covid19.data';
$files = scandir($dataPath);
foreach ($files as $file) {
	if (preg_match('/^visitors-(\\d\\d\\d\\d-\\d\\d-\\d\\d).csv.gpg$/', $file, $matches)) {
		$date = $matches[1];
		$age = $today - strtotime($date);
		if ($age > $maxAgeSec) {
			unlink("$dataPath/$file");
		}
	}
}
