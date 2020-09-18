

<?php
$date = $_POST['date'];
$campaign_id = $_POST['campaign_id'];
$path = "C:\Users\\tom\Downloads\\";
$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');

foreach (glob($path . "*.xls") as $excel_file) {
	$substr = substr($excel_file, 23, 4);
	if ($substr == "T202") {
		$csv_file = str_replace(".xls", ".csv", $excel_file);
		exec("$path" . "csv.vbs $excel_file $csv_file");
		unlink($excel_file);
	}
}



//-- FUNCTIONS FOR INSERTING DATA --//

function banners_eclick($campaign_id, $date, $csv_file, $db)
{
	$data = array_map('str_getcsv', file($csv_file));
	for ($i = 1; $i < count($data); $i++) {
		$values = implode("','", $data[$i]);
		$sql = "INSERT INTO banners_eclick VALUES (DEFAULT, '$campaign_id', '$date', 'not_set', '$values')";
		$db->query($sql);
	}
}

function banners_prosper($campaign_id, $date, $csv_file, $db)
{
	$data = array_map('str_getcsv', file($csv_file));
	for ($i = 1; $i < count($data); $i++) {
		$row = array_slice($data[$i], 0, 10);
		unset($row[4], $row[5], $row[6], $row[7], $row[8]);
		$row = array_values($row);
		$values = implode("','", $row);
		$sql = "INSERT INTO banners_prosper VALUES (DEFAULT, '$campaign_id', '$date', 'not_set', '$values')";
		$db->query($sql);
	}
}

function hours_eclick($campaign_id, $date, $csv_file, $db)
{
	$data = array_map('str_getcsv', file($csv_file));
	$day = date('l', strtotime($date));
	for ($i = 1; $i < count($data); $i++) {
		$values = implode("','", $data[$i]);
		$sql = "INSERT INTO hours_eclick VALUES (DEFAULT, '$campaign_id', '$date', '$day', '$values')";
		$db->query($sql);
	}
}

function check_date($campaign_id, $date, $table, $db)
{
	$date_exists_query = "SELECT EXISTS(SELECT id FROM $table WHERE date='$date' AND campaign_id='$campaign_id')";
	$date_exists_fetch = $db->query($date_exists_query)->fetch(PDO::FETCH_NUM);
	$date_exists = $date_exists_fetch[0];
	return $date_exists;
}


//-- INSERTING DATA INTO DATABASE --//

foreach (glob($path . "*.csv") as $csv_file) {
	switch ($csv_file) {
		case (preg_match('/variation.*/', $csv_file) ? true : false):
			$date_exists = check_date($campaign_id, $date, 'banners_eclick', $db);
			if ($date_exists == '0') {
				banners_eclick($campaign_id, $date, $csv_file, $db);
				unlink($csv_file);
				echo "$date variations uslo<br>";
			} else echo "$date varijacije postoje!<br>";
			break;
		case (preg_match('/T202_keywords.*/', $csv_file) ? true : false):
			$date_exists = check_date($campaign_id, $date, 'banners_prosper', $db);
			if ($date_exists == '0') {
				banners_prosper($campaign_id, $date, $csv_file, $db);
				unlink($csv_file);
				echo "$date t202 uslo<br>";
			} else echo "$date t202 postoji!<br>";
			break;
		case (preg_match('/hour.*/', $csv_file) ? true : false):
			$date_exists = check_date($campaign_id, $date, 'hours_eclick', $db);
			if ($date_exists == '0') {
				hours_eclick($campaign_id, $date, $csv_file, $db);
				unlink($csv_file);
				echo "$date hours uslo<br>";
			} else echo "$date sati eclick postoje!<br>";
			break;
	}
}



echo "<br>done!";

?>


