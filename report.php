<!DOCTYPE HTML>
<html>

<head>
	<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="javascript/tooltip.js"></script>
	<link rel="stylesheet" href="css/tooltip.css" type="text/css" />
	<link rel="stylesheet" href="css/table.css" type="text/css" />



</head>

<body>

	<div class="CSSTableGenerator" style="width:650px;height:200px;">
		<table>
			<?php update_alias() ?>
			<?php foreach (get_variation_data() as $row) : array_map('htmlentities', $row); ?>
				<tr>
					<td><?php echo implode('</td><td>', $row); ?></td>
				</tr>
			<?php endforeach; ?>

		</table>
	</div>


	<?php
	//-- First update alias banner variations --//
	function update_alias()
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql_select = "SELECT variation FROM banners_eclick WHERE original_variation='not_set'";
		$not_set_variations = $db->query($sql_select);
		foreach ($not_set_variations as $not_set_variation) {
			$not_set_variation = $not_set_variation['variation'];
			$alias_exists_query = "SELECT EXISTS(SELECT * FROM banners_eclick JOIN banner_alias 
								ON banners_eclick.variation=banner_alias.alias 
								WHERE banners_eclick.variation='$not_set_variation')";
			$alias_exists_fetch = $db->query($alias_exists_query)->fetch(PDO::FETCH_NUM);
			$alias_exists = $alias_exists_fetch[0];
			if ($alias_exists == 1) {
				$sql_update_eclick = "UPDATE banners_eclick SET banners_eclick.original_variation=
								(SELECT original FROM banner_alias WHERE banner_alias.alias=banners_eclick.variation) 
								WHERE banners_eclick.variation='$not_set_variation'";
				$db->query($sql_update_eclick);
				$sql_update_prosper = "UPDATE banners_prosper SET banners_prosper.original_variation=
								(SELECT original FROM banner_alias WHERE banner_alias.alias=banners_prosper.variation) 
								WHERE banners_prosper.variation='$not_set_variation'";
				$db->query($sql_update_prosper);
			} elseif ($alias_exists == 0) {
				$sql_update_eclick = "UPDATE banners_eclick SET original_variation=variation WHERE variation='$not_set_variation'";
				$db->query($sql_update_eclick);
				$sql_update_prosper = "UPDATE banners_prosper SET original_variation=variation WHERE variation='$not_set_variation'";
				$db->query($sql_update_prosper);
			}
		}
	}

	//-- Collect all the data --//
	function get_variation_data()
	{
		$campaign_id = $_POST['campaign_id'];
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT DISTINCT original_variation  FROM banners_eclick WHERE campaign_id='$campaign_id'";
		$variations = $db->query($sql);
		$variation_data[] = array(
			"Variation", "Chart", 'Clicks', 'CTR', 'Impressions', "Click Throughs", 'LP CTR',
			'Conversions', "CR", "EPM", "Funnel koef."
		); //create column titles
		foreach ($variations as $variation) {
			$variation = $variation['original_variation'];
			$chart = "<a href=generate_chart.php?variation=" . $variation . ">chart</a>";
			$clicks = get_clicks($variation);
			$impressions = get_impressions($variation);
			$ctr = round(($clicks / $impressions) * 100, 2) . "%";
			$click_throughs = get_click_throughs($variation);
			$lp_ctr = get_lp_ctr($variation, $clicks);
			$conversions = get_conversions($variation);
			$cr = ($click_throughs != 0 ? round($conversions * 100 / $click_throughs, 2) . "%" : 0);
			//$cr = round($conversions*100/$click_throughs, 2)."%";
			$epm = get_epm($variation, $impressions);
			$funnel_koef = round($ctr * $lp_ctr, 1);

			$variation = get_banner_image($variation); //add image tooltip
			$variation_data[] = array(
				$variation, $chart, $clicks, $ctr, $impressions, $click_throughs, $lp_ctr, $conversions,
				$cr, $epm, $funnel_koef
			);
		}
		return $variation_data;
	}
	//var_dump(get_variation_data());


	function get_clicks($variation)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT SUM(clicks) AS total_clicks FROM banners_eclick WHERE original_variation='$variation'";
		$total_clicks = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		return $total_clicks['total_clicks'];
	}


	function get_impressions($variation)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT SUM(impressions) AS impressions FROM banners_eclick WHERE original_variation='$variation'";
		$impressions = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		return $impressions['impressions'];
	}

	function get_click_throughs($variation)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT SUM(click_throughs) AS click_throughs FROM banners_prosper WHERE original_variation='$variation'";
		$sql_fetch = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$click_throughs = $sql_fetch['click_throughs'];
		return $click_throughs;
	}

	function get_lp_ctr($variation, $clicks)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT SUM(click_throughs) AS click_throughs, SUM(clicks) AS clicks FROM banners_prosper WHERE original_variation='$variation'";
		$sql_fetch = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$click_throughs = $sql_fetch['click_throughs'];
		$clicks = $sql_fetch['clicks'];
		$lp_ctr = ($clicks != 0 ? round(($click_throughs / $clicks) * 100, 2) . "%" : 0);
		return $lp_ctr;
	}

	function get_conversions($variation)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT SUM(conversions) AS conversions FROM banners_eclick WHERE original_variation='$variation'";
		$conversions = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		return $conversions['conversions'];
	}

	function get_banner_image($variation)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT banner_image FROM banner_files WHERE variation='$variation'";
		$banner_image_fetch = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$banner_image = $banner_image_fetch['banner_image'];
		return "<a href='" . $banner_image . "' class='preview'>" . $variation . "</a>";
		//return "<span class=\"tooltip\" onmouseover=\"tooltip.pop(this, '<img src=".$banner_image." />')\">".$variation."</span>";

	}

	function get_epm($variation, $impressions)
	{
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');
		$sql = "SELECT SUM(revenue) AS revenue FROM banners_prosper WHERE original_variation='$variation'";
		$payout_fetch = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		$revenue = $payout_fetch['revenue'];
		return round($revenue / ($impressions / 1000), 3) . "$";
	}




	?>


</body>

</html>