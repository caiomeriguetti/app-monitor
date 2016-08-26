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
		<div class="row">
			<div class="col-md-12">
				
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-3">
				<ul class="list-group" id="legends">
				  
				</ul>
			</div>
			<div class="col-md-9">
				<canvas id="myChart" width="600" height="500"></canvas>
			</div>
		</div>
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
				timePoints = [];
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
				
				var id;
				for (id in byId) {
					byId[id].sort(compare);
				}
				
				var list, i, lenlist, starttime, first;
				var aggregationsById = {}
				for (id in byId) {
					
					list = byId[id];
					lenlist = list.length;
					first = list[0];
					starttime = first.timestamp;
					
					var currentAggregation = {valueSum: first.value, nElements: 1, startTime: starttime, added: false};
					var allAggregations = [];
					var currentListElement = null;
					
					i = 1;
					while (i < lenlist) {
						
						currentListElement = list[i];
						
						if (currentListElement.timestamp - starttime <= 30) {
							currentAggregation.valueSum += currentListElement.value;
							currentAggregation.nElements += 1;
						} else {
							
							currentAggregation.average = currentAggregation.valueSum / currentAggregation.nElements;
							allAggregations.push(currentAggregation);
							currentAggregation.added = true;
							
							currentAggregation = {
								valueSum: currentListElement.value,
								nElements: 1,
								startTime: starttime
							};
							starttime = currentListElement.timestamp;
						}
						
						currentAggregation.endTime = currentListElement.timestamp;
						
						i++;
					}
					
					if (currentAggregation.added === false) {
						currentAggregation.average = currentAggregation.valueSum / currentAggregation.nElements;
						allAggregations.push(currentAggregation);
						currentAggregation.added = true;
					}
					
					aggregationsById[id] = allAggregations;
					
				}
				
				var datasets = [];
				var colorIndex = 0;
				var legendColors = {}
				
				for (var idToRender in aggregationsById) {
					var aggregations = aggregationsById[idToRender];
					var labels = [];
					var values = [];
					
					var currentAggregation;
					for (var i = 0; i < aggregations.length; i++) {
						currentAggregation = aggregations[i];
						labels.push(currentAggregation.startTime);
						values.push(currentAggregation.average);
					}
					
					legendColors[idToRender] = colors[colorIndex];
					
					datasets.push({
			            label: idToRender,
			            borderColor: "rgb"+colors[colorIndex]['rgb'],
			            backgroundColor: "rgba(0, 0, 0, 0)",
			            lineTension: 0,
			            data: values
			        });
			        
			        colorIndex ++;
				}
				
				$("#legends").children().remove();
				
				for (var id in legendColors) {
					var len = 20;
					var st = id.length - len;
					if (st < 0) st = 0; 
					var lb = id.substr(st, id.length - 1);
					$("#legends").append('<li class="list-group-item"><span class="badge" style="background-color:rgb'+legendColors[id]['rgb']+'">&nbsp</span>'+lb+'</li>');
				}
				
				var ctx = document.getElementById("myChart");
				var myChart = new Chart(ctx, {
				    type: 'line',
				    options: {
				    	responsive: true,
				    	maintainAspectRatio: false,
				    	legend: { display: false}
				    },
				    data: {
				        labels: labels,
				        datasets: datasets
				    }
				   
				});
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
				
			})
		</script>

	</body>
</html>