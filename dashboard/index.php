<html>
	<head>
		<title>Teste</title>
		<script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
		<script src="node_modules/chart.js/dist/Chart.min.js"></script>
		<script src="chosen_v1.6.2/chosen.jquery.min.js"></script>
		<script src="colors.js"></script>
		
		<link rel="stylesheet" type="text/css" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="chosen_v1.6.2/chosen.css">
		<link rel="stylesheet" type="text/css" href="style.css">
		
	</head>
	
	<body>
		<div class="container full">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
					    <div class="input-group">
					      <input type="text" class="form-control" placeholder="Search for...">
					      <span class="input-group-btn">
					        <button class="btn btn-default" type="button">Save Visualization</button>
					      </span>
					    </div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="row">
						<div class="col-md-12">
							<br/>
							<div class="input-group">
						      <input id="signals-input" type="text" class="form-control" placeholder="Add a signal to visualize">
						      <span class="input-group-btn">
						        <button class="btn btn-default" type="button" id="add-signal-bt">+ Signal</button>
						      </span>
						    </div><!-- /input-group -->
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<br/>
							<ul class="list-group" id="selectedSignals">
							</ul>
						</div>
					</div>

					

					<div class="row">
						<div class="col-md-12">
							<br/>
							<select id="interval" data-placeholder="Choose an interval" style="width:100%;" class="chosen-select">
								<option value="5">5 min</option>
								<option value="10">10 min</option>
								<option value="30">30 min</option>
								<option value="60">1 h</option>
								<option value="240">4 h</option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<canvas id="myChart" width="600" height="500"></canvas>
				</div>
			</div>
		</div>
		<script>
			var currentData = null;
			var myChart = null;
			var intervalEndTimes = null;
			var selectedSignals = {};
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
				var colorIndex = 0;


				$(currentData.hits.hits).each(function (index, item) {

					var id = item._source.signalId;

					if (!byId[id]) {
						byId[id] = [];
					}
					
					byId[id].push(item._source);
				});

				var datasets = [];
				
				
				var globalLabels = [];
				var nowTime = (Math.floor(Date.now() / 1000));
				nowTime = nowTime - (nowTime%30);
				var interval = $("#interval").val();
				var intervals = parseInt(interval)*2;
				intervalTimes = [];
				for (var i = 0; i < intervals; i++) {
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
					    	legend: { display: false }
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
				var signals = [];
				for (var signal in selectedSignals) {
					if (selectedSignals[signal] === true) {
						signals.push(signal);
					}
				}

				if (signals.length == 0) {
					setTimeout(refresh, 2000);
					return;
				}

				$.ajax({
					url: 'data.php?deltaMins=120',
					type: 'get',
					data: {"signalId": signals},
					success: function (data) {
						currentData = $.parseJSON(data);
						redrawChart();
						setTimeout(refresh, 2000);
					}, error: function () {
						
					}
				});
			}

			function saveCurrentState () {
				var state = {"selectedSignals": selectedSignals, "interval": $("#interval").val()};
				localStorage.setItem("state", JSON.stringify(state));
			}

			function renderSelectedSignals () {
				
				$("#selectedSignals").children().remove();
				var signals = [];
				
				colorIndex = 0;

				for (var signal in selectedSignals) {
					
					legendColors[signal] = colors[colorIndex];
					colorIndex ++;
					var bgColor = legendColors[signal]['rgb'];
					var signalElement = $('<li class="list-group-item">\
							    <span class="badge" style="background:rgb'+bgColor+'">&nbsp;</span>\
							    '+signal+'\
							    <span style="float:left;padding-right: 10px" class="glyphicon glyphicon-remove" aria-hidden="true"></span>\
							  </li>');
					signalElement.data("signal", signal);

					if (selectedSignals[signal] === false) {
						signalElement.css("opacity", 0.5);
					} else if (selectedSignals[signal] === true) {
						signalElement.css("opacity", 1);
					}

					signalElement.on("click", ".glyphicon-remove", function (e) {
						var signal = $(this).parents(".list-group-item").data("signal");
						removeSignal(signal);
						e.stopPropagation();
						return false;
					});

					signalElement.on("click", ".badge", function (e) {
						var signal = $(this).parents(".list-group-item").data("signal");
						toggleSignal(signal);

						e.stopPropagation();
						return false;
					});

					$("#selectedSignals").append(signalElement);
				}

			}

			function toggleSignal (signal) {
				if (selectedSignals[signal] === false) {
					selectedSignals[signal] = true;
				} else if (selectedSignals[signal] === true) {
					selectedSignals[signal] = false;
				}

				saveCurrentState();
				renderSelectedSignals();
			}
			
			function disableSignal (signal) {
				selectedSignals[signal] = false;
				saveCurrentState();
				renderSelectedSignals();
			}

			function removeSignal (signal) {
				delete selectedSignals[signal];
				saveCurrentState();
				renderSelectedSignals();
			}

			function addSignal (signal) {
				selectedSignals[signal] = true;
				saveCurrentState();
				renderSelectedSignals();
			}

			$(function () {
				var savedState = localStorage.getItem("state");

				if (savedState) {
					var stateObject = $.parseJSON(savedState);
					selectedSignals = stateObject.selectedSignals;
					$("#interval").val(stateObject.interval);
					renderSelectedSignals();
				}
				
				$("#interval").change(function () {
					saveCurrentState();
				});

				$("#add-signal-bt").on("click", function () {
					var val = $("#signals-input").val();
					addSignal(val);
				});

				$("#interval").chosen();
				$("#signals-input").on("keyup", function (e) {
					if (e.keyCode == 13) {
						var val = $("#signals-input").val();
						addSignal(val);

						return false;
					}
				});
				refresh();
				
			})
		</script>

	</body>
</html>