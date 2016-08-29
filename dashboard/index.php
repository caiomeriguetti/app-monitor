<html>
	<head>
		<title>Teste</title>
		<script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
		<script src="node_modules/chart.js/dist/Chart.min.js"></script>
		<script src="colors.js"></script>
		
		<link rel="stylesheet" type="text/css" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="style.css">
		
	</head>
	
	<body>
		<div class="container full">
			<div class="row">
				<div class="col-md-12">
					<canvas id="myChart" width="600" height="500"></canvas>
				</div>
			</div>
		</div>
		<script>
			var currentData = null;
			var myChart = null;
			var intervalEndTimes = null;
			var legendColors = {};

			function compare(a, b) {
				if (a.startTime > b.startTime) {
					return 1;
				} else if (a.startTime < b.startTime) {
					return -1;
				}

				return 0;
			}

			function redrawChart () {
				var value, id;
				var byId = {};
				timePoints = [];

				$(currentData.hits.hits).each(function (index, item) {

					var id = item._source.signalId;

					if (!byId[id]) {
						byId[id] = [];
					}
					
					byId[id].push(item._source);
				});

				var datasets = [];
				var colorIndex = 0;
				
				var globalLabels = [];
				var nowTime = (Math.floor(Date.now() / 1000));
				nowTime = nowTime - (nowTime%30);

				intervalTimes = [];
				for (var i = 0; i < 10; i++) {
					var time = nowTime - i*30;
					intervalTimes.unshift(time);
				}

				for (var i = 0; i < intervalTimes.length; i++) {
					var time = intervalTimes[i];
					var date = new Date(time*1000);
					var hours = date.getHours();
					var minutes = "0" + date.getMinutes();
					var seconds = "0" + date.getSeconds();
					var formattedTimebyId = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
					globalLabels.push(formattedTimebyId);
				}

				var labels = globalLabels;

				for (var idToRender in byId) {

					var aggregationsByLabel = {};
					
					for (var j = 0; j < intervalTimes.length; j++) {
						var intervalTime = intervalTimes[j];
						aggregationsByLabel[String(intervalTime)] = {"sum": 0, "nElements": 0};
					}

					var aggregations = byId[idToRender];

					var currentAggregation;
					for (var i = 0; i < aggregations.length; i++) {
						currentAggregation = aggregations[i];

						var aggTime = currentAggregation.timestamp - (currentAggregation.timestamp % 30);
						var aggAverage = (currentAggregation.valueSum/currentAggregation.elementsNumber);

						if (aggregationsByLabel[String(aggTime)]) {
							aggregationsByLabel[String(aggTime)]["sum"] += aggAverage;
							aggregationsByLabel[String(aggTime)]["nElements"] += 1;
						}
					}
					
					var values = [];
					for (var j = 0; j < intervalTimes.length; j++) {
						var time = intervalTimes[j];
						var timeStr = String(time);
						var v = 0;
						if (aggregationsByLabel[timeStr]["nElements"] > 0) {
							v = aggregationsByLabel[timeStr]["sum"] / aggregationsByLabel[timeStr]["nElements"];
						}
						values.push(v);
					}

					if (!legendColors[idToRender]) {
						legendColors[idToRender] = colors[colorIndex];
						colorIndex ++;
					}
					
					datasets.push({
            label: idToRender,
            borderColor: "rgb"+legendColors[idToRender]['rgb'],
            backgroundColor: "rgba(0, 0, 0, 0)",
            lineTension: 0,
            data: values
	        });

				}
				
				if (myChart == null) {
					var ctx = document.getElementById("myChart").getContext('2d');
					myChart = new Chart.Line(ctx, {
					    type: 'line',
					    options: {
					    	animation: false,
					    	responsive: true,
					    	maintainAspectRatio: false,
					    	legend: { display: true }
					    },
					    data: {
					        labels: labels,
					        datasets: datasets
					    }
					});
				} else {
					myChart.data.datasets = datasets;
					myChart.data.labels = labels;
					myChart.update();
				}
				
			}
			
			function refresh () {
				$.ajax({
					url: 'data.php?deltaMins=120',
					type: 'get',
					success: function (data) {
						currentData = $.parseJSON(data);
						redrawChart();
					}, error: function () {
						
					}
				});
			}
			
			$(function () {
				refresh();
				
				
				setInterval(function () {
					refresh();
				}, 5000);
				
			})
		</script>

	</body>
</html>