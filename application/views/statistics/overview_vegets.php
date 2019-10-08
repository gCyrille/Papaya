<link rel="stylesheet" href="<?php echo base_url('assets/css/daterangepicker.css'); ?>">
<script type="text/javascript" src="<?php echo base_url('assets/javascript/sugar.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/javascript/daterangepicker.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/javascript/canvasjs.min.js'); ?>"></script>

<script>
	$(document)
	.ready(function() {
		$('select.dropdown')
			.dropdown()
		;
		
		$('div.groupBy.dropdown')
			.dropdown('set selected', 'week')
		;
		
		$('div.chartType.dropdown')
			.dropdown('set selected', 'bar')
		;
		
		// Set the default dates
		var startDate = Date.create().addDays(-30), // 31 days ago
			endDate = Date.create(); 				// today

		var range = $('#range');

		// Show the dates in the range input
		range.val(startDate.format('{MM}/{dd}/{yyyy}') + ' - ' + endDate.format('{MM}/{dd}/{yyyy}'));

		var getSales = function()
		{
			start = range.data('daterangepicker').startDate;
			end = range.data('daterangepicker').endDate;
			veget = $('select[name=veget]').val();
			list_ids = $('select[name=lists]').val();
			customers_excl = $('select[name=customers_excl]').val();
			group_by = $('input[name=group_by]').val();
			ajaxLoadSalesChart(start, end, veget, group_by, ['*'], list_ids, customers_excl, "true")
		};
		
		var getCustomers = function()
		{
			start = range.data('daterangepicker').startDate;
			end = range.data('daterangepicker').endDate;
			veget = $('select[name=veget]').val();
			list_ids = $('select[name=lists]').val();
			customers_excl = $('select[name=customers_excl]').val();
			ajaxLoadCustomerChart(start, end, veget, list_ids, customers_excl, 'income');
		};
		
		var getSummary = function()
		{
			start = range.data('daterangepicker').startDate;
			end = range.data('daterangepicker').endDate;
			veget = $('select[name=veget]').val();
			list_ids = $('select[name=lists]').val();
			customers_excl = $('select[name=customers_excl]').val();
			ajaxLoadSummary(start, end, veget, list_ids, customers_excl);
		}
		
		// Load date picker
		range.daterangepicker(
			{
				startDate: startDate,
				endDate: endDate,
				ranges: {
					'Last week': [Date.create().addDays(-6), 'today'],
					'Last month': [Date.create().addDays(-30), 'today'],
					'Last 2 months': [Date.create().addDays(-61), 'today'],
					'Last 6 months': [Date.create().addDays(-183), 'today']
				}
			}, 
			function(start, end) 
			{
				getCustomers();
				getSales();
				getSummary();
			}
		);
		
		// Update chart when inputs change
		$('select[name=veget]').on('change', function(){
			getCustomers();
			getSales();
			getSummary();
		});
		$('select[name=customers_excl]').on('change', function(){
			getCustomers();
			getSales();
			getSummary();
		});
		$('select[name=lists]').on('change', function(){
			getCustomers();
			getSales();
			getSummary();
		});

		// Create a new CanvaJS instance
		var chart = new CanvasJS.Chart("chartContainer", {
			animationEnabled: true,
			exportEnabled: true,
			zoomEnabled: true,
			axisY: {
				includeZero: true,
				prefix: "Zmk ",
				stripLines: [{
					value: 0,
					label: "Average",
				}]
			},
			axisX: {
				interval: 2,
				intervalType: "week",
				valueFormatString: "D MMMM YYYY"
			},
			legend: {
				verticalAlign: "top",
				dockInsidePlotArea: true
			},
			toolTip: {
				shared: true
			},
			theme: "light1",//light2
			data: [{
				// Change type to "bar", "splineArea", "area", "spline", "pie",etc.
				type: "splineArea",
				toolTipContent: "{x}: <strong>Zmk {y}</strong>",
				xValueType: "date",   
				dataPoints: []
			}]
		});
		
		// Create a new CanvaJS instance
		var chart2 = new CanvasJS.Chart("chartContainer2", {
			animationEnabled: true,
			exportEnabled: true,
			zoomEnabled: true,
			axisY2:{
				interlacedColor: "rgba(1,77,101,.2)",
				gridColor: "rgba(1,77,101,.1)",
//				prefix: "Zmk ",
				stripLines: [{
					value: 0,
					label: "Average",
				}]
			},
			axisX:{
				interval: 1,
				labelAngle: -20,
				labelFontSize: 14
			},
			theme: "light1",//light2
			data: [{
				// Change type to "bar", "splineArea", "area", "spline", "pie",etc.
				type: "bar",
				name: "customers",
				axisYType: "secondary",
//				color: "#014D65",
				toolTipContent: "{label}: <strong>{y} {unit}</strong>",
//				xValueType: "date",
				unit: "",
				dataPoints: []
			}]
		});
		
		// Function for loading data via AJAX and showing it on the chart
		var ajaxLoadCustomerChart = function(startDate, endDate, vegetId, listIds, customersExcl, sortedBy) 
		{
			// If no data is passed (the chart was cleared)
			if (!startDate || !endDate) 
			{
				chart2.options.data[0].dataPoints = null;
				chart2.render();
				return;
			}
			
			// Otherwise, issue an AJAX request
			$.post(
				"<?php echo base_url('statistics/get_customers'); ?>", 
				{
					start: startDate.format('{yyyy}-{MM}-{dd}'),
					end: endDate.format('{yyyy}-{MM}-{dd}'),
					vegetId: vegetId,
					customersExcl: customersExcl,
					sortedBy: sortedBy,
					listIds: listIds
				}, 
				function(data) 
				{
					if (data.success == 'false') 
					{
						$('#msg .header').html(data.message);
						$('#msg').show();
						chart2.options.data[0].dataPoints = null;
						chart2.render();
					} 
					else 
					{
						$('#msg .header').empty();
						$('#msg').hide();
						var set = [];
						data.data.reverse();
						for(i=0; i < Math.min(10, data.data.length); i++)
						{
							set.push({
								label: data.data[i].label,
								y: parseInt(data.data[i].value, 10),
								unit: data.data[i].unit
							});	
						}
						set.reverse();

						chart2.options.data[0].toolTipContent = "{label}: Zmk <strong>{y}</strong>";
						
						chart2.options.axisY2.stripLines[0].value = data.average;
						chart2.options.axisY2.stripLines[0].label = "Average: "+data.average;
						
						// With CanvaJS
						chart2.options.data[0].dataPoints = set;
						chart2.render();
					}
				},
				'json');
		}
		
		var ajaxLoadSalesChart = function(startDate, endDate, vegetId, groupBy, customerIds, listIds, customersExcl, separateLists) 
		{
			// If no data is passed (the chart was cleared)
			if (!startDate || !endDate) 
			{
				chart.options.data[0].dataPoints = null;
				chart.render();
				return;
			}
			
			// Otherwise, issue an AJAX request
			$.post(
				"<?php echo base_url('statistics/get_sales'); ?>", 
				{
					start: startDate.format('{yyyy}-{MM}-{dd}'),
					end: endDate.format('{yyyy}-{MM}-{dd}'),
					vegetId: vegetId,
					groupBy: groupBy,
					customerIds: customerIds,
					customersExcl: customersExcl,
					listIds: listIds,
					separateLists: separateLists
				}, 
				function(data) 
				{
					if (data.success == 'false') 
					{
						$('#msg .header').html(data.message);
						$('#msg').show();
						chart.options.data = null;
						chart.render();
					} 
					else 
					{
//						$('#msg .header').empty();
//						$('#msg').hide();
						var set = [];
						$.each(data.series, function() {
							
							serie = {
								// Change type to "bar", "splineArea", "area", "spline", "pie",etc.
								type: "stackedArea", //data.series.length > 1 ? "stackedArea" : "splineArea",
								name: this.name,
								showInLegend: true,
								connectNullData: true,
//								toolTipContent: "{x}: <strong>Zmk {y}</strong>",
								xValueType: "date",   
								dataPoints: []
							}
							
							$.each(this.data, function() {
								serie.dataPoints.push({
									x: new Date(this.label),
									y: parseInt(this.value, 10),
								});		
							});
							
							set.push(serie);
						});
						
						chart.options.axisY.stripLines[0].value = data.average;
						chart.options.axisY.stripLines[0].label = "Average: "+data.average;
						
						// With CanvaJS
						chart.options.data = set;
						chart.render();
					}
				},
				'json');
		}
		
		var ajaxLoadSummary = function(startDate, endDate, vegetId, listIds, customersExcl) 
		{
			// If no data is passed (the chart was cleared)
			if (!startDate || !endDate) 
			{
				chart.options.data[0].dataPoints = null;
				chart.render();
				return;
			}
			
			// Otherwise, issue an AJAX request
			$.post(
				"<?php echo base_url('statistics/get_veget_summary'); ?>", 
				{
					start: startDate.format('{yyyy}-{MM}-{dd}'),
					end: endDate.format('{yyyy}-{MM}-{dd}'),
					vegetId: vegetId,
					customersExcl: customersExcl,
					listIds: listIds
				}, 
				function(data) 
				{
					start = range.data('daterangepicker').startDate;
					end = range.data('daterangepicker').endDate;
					
					if (data.success == 'false') 
					{
						$('#msg .header').html(data.message);
						$('#msg').show();

					} 
					
					if ( ! isNaN(data.vegetable.unit[0]))
					{
						unit = "x " + data.vegetable.unit;
					}
					else
					{
						unit = data.vegetable.unit;
					}

					$(".sub.header.date").html("From " + start.toLocaleDateString() + " to "+end.toLocaleDateString());
					$("#vegName").html(data.vegetable.name);
					$("#vegUnit").html(data.vegetable.unit);
					$("#vegPrice").html(data.vegetable.price);
					$("#vegAvaibility").html(data.vegetable.avaibility.length > 0 ? data.vegetable.avaibility.join() : 'None');
					$("#vegUrl")[0].href = data.vegetable.url;
					$("#summTotalQtt").html(data.summary.total_quantity + " " + unit);
					$("#summTotalInc").html(data.summary.total_income);
					$("#summDelAvgQtt").html(data.summary.per_delivery.avg_quantity + " " + unit);
					$("#summDelAvgInc").html(data.summary.per_delivery.avg_income);
					$("#summOrdAvgQtt").html(data.summary.per_order.avg_quantity + " " + unit);
					$("#summOrdAvgInc").html(data.summary.per_order.avg_income);
					$("#scoreAvaibility").html(Math.round(data.summary.scores.avaibility * 100));
					$("#scoreDelivery").html(Math.round(data.summary.scores.delivery * 100));
					$("#scoreOrder").html(Math.round(data.summary.scores.order * 100));
				},
				'json');
		}
		
		$('div.chartType.dropdown')
			.dropdown({
			 onChange: function(value, text, $selectedItem) {
				 // custom action
				 chart2.options.data[0].type = value;
				 chart2.render();
			 }
		})
		;
		
		$('div.groupBy.dropdown')
			.dropdown({
			 onChange: function(value, text, $selectedItem) {
				 // custom action
				 switch(value)
					 {
						 case 'day':
							chart.options.axisX.interval = 7;
							chart.options.axisX.valueFormatString = "D MMMM YYYY";
							 break;
						 case 'week':
							chart.options.axisX.interval = 2;
							chart.options.axisX.valueFormatString = "D MMMM YYYY";
							 break;
						 case 'month':
							chart.options.axisX.interval = 1;
							chart.options.axisX.valueFormatString = "MMMM YYYY";
							 break;
							 
					 }
				 chart.options.axisX.intervalType = value;
				getSales();
			 }
		})
		;
		
		$('select[name=veget]').change();
//		range.data('daterangepicker').notify();
	})
	;
</script>
<div class="ui two column grid container">
	
	<div class="ui huge breadcrumb header sixteen wide column">
		<a class="section" 
		   href="<?php echo base_url('statistics/index/'); ?>">
			Statistics
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $title; ?></div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui sixteen wide column">
		<form class="ui form">
			<div class="equal width fields">
				<div class="field">
					<label>Select a vegetable:</label>
					<?php echo form_dropdown('veget', $vegets_list, NULL, 'class="ui search dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Select a period:</label>
					<div class="ui left icon input">
						<input placeholder="Select a date range" type="text" id="range" name="range">
						<i class="calendar icon"></i>
					</div>
				</div>
<!--
			</div>
			<div class="three wide fields">
-->
				<div class="field">
					<label>Filter by list:</label>
					<?php echo form_multiselect('lists', $delivery_lists, NULL, 'class="ui search dropdown" multiple=""'); ?>
				</div>
				<div class="field">
					<label>Exlude customers:</label>
					<?php echo form_multiselect('customers_excl', $customers_list, NULL, 'class="ui fluid search dropdown" multiple=""'); ?>
				</div>
			</div>
		</form>

		<div class="ui small negative message hidden" id="msg">
			<div class="header"></div>
		</div>
	</div>
	<div class="ui sixteen wide column">
		<h2 class="ui fitted divider"></h2>
	</div>
	<div class="ui four column row">
		<div class="column">
			<h2 class="ui veget section header" id="vegName"></h2>
			<div class="ui green segment">
				<div class="ui list">
					<div class="item"><strong>Unit</strong>: <span id="vegUnit"></span></div>
					<div class="item"><strong>Price</strong>: <span id="vegPrice"></span></div>
					<div class="item"><strong>Avaibility</strong>: <span id="vegAvaibility"></span></div>
				</div>
				<a class="ui basic fluid button" id="vegUrl" href="">Modify</a>
			</div>
		</div>
		<div class="twelve wide column">
			<h3 class="ui header">
				<i class="hand point right outline icon"></i>
				<div class="content">
					Summary
					<span class="ui sub header date">From --/--/---- to --/--/----</span>
				</div>
			</h3>
			<div class="ui horizontal segments">
				<div class="ui green segment">
					<span class="ui sub header">Totals</span>
					<div class="ui list">
						<div class="item"><span id="summTotalQtt"></span></div>
						<div class="item">Zmk <span id="summTotalInc"></span></div>
					</div>
				</div>
				<div class="ui green segment">
					<span class="ui sub header">Average per order</span>
					<div class="ui list">
						<div class="item"><span id="summOrdAvgQtt"></span></div>
						<div class="item">Zmk <span id="summOrdAvgInc"></span></div>
					</div>
				</div>
				<div class="ui green segment">
					<span class="ui sub header">Average per delivery</span>
					<div class="ui list">
						<div class="item"><span id="summDelAvgQtt"></span></div>
						<div class="item">Zmk <span id="summDelAvgInc"></span></div>
					</div>
				</div>
				<div class="ui green segment">
					<span class="ui sub header">Scores</span>
					<div class="ui list">
						<div class="item">Avaibility: <span id="scoreAvaibility"></span>%</div>
						<div class="item">Delivery: <span id="scoreDelivery"></span>%</div>
						<div class="item">Order: <span id="scoreOrder"></span>%</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="ui column">
		<h3 class="ui header">Sales</h3>
		<div class="ui form">
			<div class="inline field">
				<div class="ui fluid normal selection dropdown groupBy">
					<i class="calendar outline icon"></i>
					<input name="group_by" type="hidden">
					<i class="dropdown icon"></i>
					<div class="default text">Show </div>
					<div class="menu">
					<div class="item"  data-value="day">Group by day</div>
					<div class="item"  data-value="week">Group by week</div>
					<div class="item"  data-value="month">Group by month</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ui segment container">
			<div id="chartContainer" style="height: 500px;width: 100%;"></div>
		</div>
	</div>
	
	<div class="ui column">
		<h3 class="ui header">Top 10 customers</h3>
		<div class="ui form">
			<div class="inline field">
				<div class="ui fluid normal selection dropdown chartType">
					<input name="chart_type" type="hidden">
					<i class="dropdown icon"></i>
					<div class="default text">Show </div>
					<div class="menu">
						<div class="item"  data-value="pie"><i class="chart pie icon"></i> Pie chart</div>
						<div class="item"  data-value="bar"><i class="chart bar icon"></i> Bar chart</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ui segment container">
			<div id="chartContainer2" style="height: 500px;width: 100%;"></div>
		</div>
	</div>

</div>
