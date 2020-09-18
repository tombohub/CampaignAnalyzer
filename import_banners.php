<?php

//-- UPLOAD FILE --//
$variation = $_POST['variation'];
$campaign_id = $_POST['campaign_id'];
$upload_dir = "banners/";
$extension = substr($_FILES['file']['name'], -4);
$uploaded_file = $upload_dir.$_POST['variation'].$extension;


//-- IF VARIATION NOT EXISTS IMPORT --//
$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
$variation_exists_query = "SELECT EXISTS(SELECT id FROM banner_files WHERE variation='$variation')";
$variation_exists_fetch = $db->query($variation_exists_query)->fetch(PDO::FETCH_NUM);
$variation_exists = $variation_exists_fetch[0];

if($variation_exists == '0') {
	move_uploaded_file($_FILES['file']['tmp_name'], $uploaded_file);
	$sql = "INSERT INTO banner_files VALUES (DEFAULT, '$variation', '$campaign_id', '$uploaded_file')";
	$db->query($sql);
} elseif($variation_exists == '1') echo("banner already inside!!");

echo "done! <a href='index.php'>back</a>";
?>