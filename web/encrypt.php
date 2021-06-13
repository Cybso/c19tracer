<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	die("Only POST request allowed");
}

// Konfiguration
$dataPath = __DIR__.'/../covid19.data';
$gpgKeyring = __DIR__.'/../covid19.gnupg';
$gpgId = '8F22EAADE444C77C';
if (!file_exists($dataPath)) {
	echo "$dataPath";
	die("Data storage path does not exist");
}
if (!file_exists($gpgKeyring)) {
	die("GPG Keyring path does not exist.");
}

// Werte erfassen
$now = time();
$target = "$dataPath/visitors-".date('Y-m-d').".csv.gpg";
$token = $_POST['token'];
$values = array(
	'vorname' => substr($_POST['vorname'], 0, 100),
	'nachname' => substr($_POST['nachname'], 0, 100),
	'anschrift' => substr($_POST['anschrift'], 0, 100),
	'plz' => substr($_POST['plz'], 0, 100),
	'ort' => substr($_POST['ort'], 0, 100),
	'tel' => substr($_POST['tel'], 0, 100),
	'datum' => date('c', time()),
	'token' => substr($_POST['token'], 0, 100),
);

// Unerwünschte Zeichen aus den Eingaben entfernen
foreach ($values as &$value) {
	$value = trim($value);
	$value = preg_replace("~^[\W]+~", '', $value);
	// Verbiete außerdem den String BEGIN und END
	if (strpos($value, 'BEGIN') !== FALSE || strpos($value, 'END') !== FALSE) {
		die("Invalid characters in input string");
	}
}


// GPG-Verschlüsselung durchführen
$cmd = "gpg -ae -r ".escapeshellarg($gpgId)." --homedir=".escapeshellarg($gpgKeyring);
$descriptorspec = array(
   0 => array("pipe", "r"),
   1 => array("pipe", "w"),
);

$proc = proc_open($cmd, $descriptorspec, $pipes);
if (!is_resource($proc)) {
	die("Failed to open GPG process");
}

if (!file_exists($target)) {
	fputcsv($pipes[0], array_keys($values));
}

fputcsv($pipes[0], $values);
fclose($pipes[0]);

// Read output...
$output = "";
while (($line = fgets($pipes[1], 4096)) !== false) {
	$output .= $line;
}

// Write atomic - kein Locking notwendig unter Linux
file_put_contents($target, $output, FILE_APPEND | LOCK_EX);

$return_value = proc_close($proc);
if ($return_value === 0) {
	header("Location: index.html?success=".urlencode($token));
}
