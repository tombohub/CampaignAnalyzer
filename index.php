<html>

<body>

    <form method="post" action='import_stats.php'>
        Import stats
        <br><br>Campaign:<br>
        <select name="campaign_id">
            <?php get_campaigns('yes') ?>
        </select>
        <br><br>Date:<br>
        <input type="date" name="date">
        <br><br>
        <input type="submit" value="Submit">
    </form>

    <hr>
    <hr>
    <form method="post" enctype="multipart/form-data" action='import_banners.php'>
        Import banners
        <br><br>Campaign:<br>
        <select name="campaign_id">
            <?php get_campaigns('yes') ?>
        </select>
        <br><br>Variation:<br>
        <input type="text" name="variation">
        <br><br>File:<br>
        <input type="file" name="file">
        <br><br>
        <input type="submit" value="Submit">
    </form>

    <hr>
    <hr>
    <form method="post" enctype="multipart/form-data" action='banner_alias.php'>
        Add banner alias
        <br><br>Original:<br>
        <input type="text" name="original">
        <br><br>Alias:<br>
        <input type="text" name="alias">
        <br><br>
        <input type="submit" value="Submit">
    </form>

    <hr>
    <hr>
    <form method="post" action='report.php'>
        <select name="campaign_id">
            <?php get_campaigns('active') ?>
        </select>
        <input type="submit" value="Report">
    </form>

    <?php
	function get_campaigns($active)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		if (isset($active))
			$sql = "SELECT id,name FROM campaigns WHERE active='$active'";
		else
			$sql = "SELECT id,name FROM campaigns";
		foreach ($db->query($sql) as $row) {
			echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
		}
	}
	?>

</body>

</html>