<html>
	<head>
		<title>Teste</title>
		<script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script src="node_modules/chart.js/dist/Chart.min.js"></script>
		<script id="mockdata">
			{"took":2,"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":4,"max_score":1.0,"hits":[{"_index":"time-spent","_type":"log","_id":"AVbDcSGdHy03E8IXfeNA","_score":1.0,"_source":{"type": "time-spent", "value": 0.077982187271118, "id":"picpay-backend.core.Consumers.getActivities", "timestamp":1472157786}},{"_index":"time-spent","_type":"log","_id":"AVbDfs_2GWcZT1Ng0iWS","_score":1.0,"_source":{"type": "time-spent", "value": 0.077220916748047, "id":"picpay-backend.core.Consumers.getActivities", "timestamp":1472158683}},{"_index":"time-spent","_type":"log","_id":"AVbDcSG8Hy03E8IXfeNB","_score":1.0,"_source":{"type": "time-spent", "value": 0.27781295776367, "id":"picpay-webservice.api.getActivityStream", "timestamp":1472157786}},{"_index":"time-spent","_type":"log","_id":"AVbDftAQGWcZT1Ng0iWT","_score":1.0,"_source":{"type": "time-spent", "value": 0.25736594200134, "id":"picpay-webservice.api.getActivityStream", "timestamp":1472158683}}]}}
		</script>
	</head>
	
	<body>
		<canvas id="myChart" width="1000" height="1000" style="display: block; width: 1000px; height: 1000px;"></canvas>
		<script>
		$(function () {
			var data = $.parseJSON($("#mockdata").html());
			console.log(data);
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