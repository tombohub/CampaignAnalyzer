<?php
$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
$sql = "SELECT DISTINCT date FROM hours_eclick";
$dates = $db->query($sql);
foreach ($dates as $date) {
	$date = $date['date'];
	$day = date('l', strtotime($date));
	$update = "UPDATE hours_elick SET day='$day' WHERE date='$date'";
	$db->query($update);
}
echo $day;
