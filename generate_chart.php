 <html>

 <head>
 	<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
 	<script src="http://code.highcharts.com/highcharts.js"></script>


 </head>

 <body>


 	<?php

		$variation = $_GET['variation'];
		$db = new PDO('mysql:host=localhost;dbname=amb;charset=utf8', 'root', '');


		$sql = "SELECT * FROM banners_eclick JOIN banners_prosper ON banners_prosper.variation=banners_eclick.variation 
	AND banners_eclick.date=banners_prosper.date 
	WHERE banners_eclick.variation='$variation' ORDER BY banners_eclick.date";
		$rows = $db->query($sql)->fetchAll();
		foreach ($rows as $row) {
			$dates[] = "'" . $row['date'] . "'";
			$ctrs[] = round($row['ctr'], 2);
			$impressions[] = $row['impressions'];
			$cpms[] = round($row['cpm'], 3);
			$epms[] = round($row['revenue'] * 1000 / $row['impressions'], 3);
		}
		?>
 	<script>
 		var options = {
 			chart: {
 				renderTo: 'container',
 				type: 'line'
 			},
 			title: {
 				text: <?php echo $variation ?>
 			},
 			xAxis: [{
 				categories: [<?php echo join(',', $dates) ?>]
 			}],
 			yAxis: [{
 				title: {
 					text: '%'
 				}
 			}, {
 				title: {
 					text: 'Impressions'
 				},
 			}, {
 				title: {
 					text: 'CPM'
 				}
 			}],
 			series: [{
 					name: 'Impressions',
 					yAxis: 1,
 					data: [<?php echo join(',', $impressions) ?>],
 					type: 'column'
 				},
 				{
 					name: 'CTR',
 					yAxis: 0,
 					data: [<?php echo join(',', $ctrs) ?>],
 					tooltip: {
 						valueSuffix: '%'
 					}
 				},
 				{
 					name: 'CPM',
 					yAxis: 2,
 					data: [<?php echo join(',', $cpms) ?>],
 					tooltip: {
 						valueSuffix: '$'
 					}
 				},
 				{
 					name: 'EPM',
 					yAxis: 2,
 					data: [<?php echo join(',', $epms) ?>],
 					tooltip: {
 						valueSuffix: '$'
 					}
 				}
 			],
 			tooltip: {
 				shared: true
 			}
 		};

 		$(document).ready(function() {
 			var chart = new Highcharts.Chart(options);
 		});
 	</script>
 	<div id='container'></div>