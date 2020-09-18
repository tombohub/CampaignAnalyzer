<?php


$original = $_POST['original'];
$alias = $_POST['alias'];

//-- IF ORIGINAL EXISTS CREATE ALIAS --//
$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
$variation_exists_query = "SELECT EXISTS(SELECT id FROM banner_files WHERE variation='$original')";
$variation_exists_fetch = $db->query($variation_exists_query)->fetch(PDO::FETCH_NUM);
$variation_exists = $variation_exists_fetch[0];

if($variation_exists == '1') {
	$sql = "INSERT INTO banner_alias VALUES (DEFAULT, '$original', '$alias');
			UPDATE banners_eclick SET original_variation='$original' WHERE variation='$alias';
			UPDATE banners_prosper SET original_variation='$original' WHERE variation='$alias';";
	$db->query($sql);
} elseif($variation_exists == '0') echo("Original doesn't exist!!");

echo "done! <a href='index.php'>back</a>";
