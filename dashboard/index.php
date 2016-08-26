<html>
	<head>
		<title>Teste</title>
		<script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script src="node_modules/chart.js/dist/Chart.min.js"></script>
	</head>
	
	<body>
		<canvas id="myChart" width="1000" height="1000" style="display: block; width: 1000px; height: 1000px;"></canvas>
		<script>
		var currentData = null;
		function compare(a,b) {
		  if (a.timestamp < b.timestamp)
		    return -1;
		  if (a.timestamp > b.timestamp)
		    return 1;
		  return 0;
		}

		function redrawChart () {
			var value, id;
			var byId = {};
			$(currentData.hits.hits).each(function (index, item) {
				
				var id = item._source.id;
				
				if (!byId[id]) {
					byId[id] = [];
				}
				
				byId[id].push(item._source);
				
				var date = new Date(item._source.timestamp*1000);
				var hours = date.getHours();
				var minutes = "0" + date.getMinutes();
				var seconds = "0" + date.getSeconds();
				var formattedTimebyId = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
			});
			
			for (var id in byId) {
				byId[id].sort(compare);
			}
			
			console.log(byId);
		}
		
		function refresh () {
			$.ajax({
				url: 'data.php?deltaMins=3600',
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
			
			var ctx = document.getElementById("myChart");
			var myChart = new Chart(ctx, {
			    type: 'line',
			    data: {
			        labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
			        datasets: [{
			            label: 'Bisca1',
			            borderColor: "rgba(75,192,55,1.0)",
			            backgroundColor: "rgba(75,192,192,0.0)",
			            data: [12, 19, 3, 5, 2, 3]
			        }, {
			            label: 'Bisca2',
			            borderColor: "rgba(75,192,192,1.0)",
			            backgroundColor: "rgba(75,192,192,0.0)",
			            data: [5, 15, 1, 20, 6, 11]
			        }]
			    }
			   
			});
			
		})
		</script>

	</body>
</html>