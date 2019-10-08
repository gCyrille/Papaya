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
		
		$('.checkbox')
			.checkbox()
		;
		// Set the default dates
		var startDate = Date.create().addDays(-30), // 31 days ago
			endDate = Date.create(); 				// today

		var range = $('#range');

		// Show the dates in the range input
		range.val(startDate.format('{MM}/{dd}/{yyyy}') + ' - ' + endDate.format('{MM}/{dd}/{yyyy}'));

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
				group_by = $('input[name=group_by]').val();
				customer_ids = $('select[name=customers]').val();
				customers_excl = $('select[name=customers_excl]').val();
				list_ids = $('select[name=lists]').val();
				stacked = $('.checkbox.stacked').checkbox('is checked');
				ajaxLoadChart(start, end, group_by, customer_ids, list_ids, customers_excl, stacked);
			}
		);
		
		// Update chart when customer changes
		$('select[name=customers]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		$('select[name=customers_excl]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		$('select[name=lists]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		$('.checkbox.stacked').on('change', function(){
			range.data('daterangepicker').notify();
		});

		// Create a new CanvaJS instance
		var chart2 = new CanvasJS.Chart("chartContainer", {
			animationEnabled: true,
			exportEnabled: true,
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
		
		// Function for loading data via AJAX and showing it on the chart
		var ajaxLoadChart = function(startDate, endDate, groupBy, customerIds, listIds, customersExcl, separateLists) 
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
				"<?php echo base_url('statistics/get_sales'); ?>", 
				{
					start: startDate.format('{yyyy}-{MM}-{dd}'),
					end: endDate.format('{yyyy}-{MM}-{dd}'),
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
						chart2.options.data[0].dataPoints = null;
						chart2.render();
					} 
					else 
					{
						$('#msg .header').empty();
						$('#msg').hide();
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
						
						chart2.options.axisY.stripLines[0].value = data.average;
						chart2.options.axisY.stripLines[0].label = "Average: "+data.average;
						
						// With CanvaJS
						chart2.options.data = set;
						chart2.render();
					}
				},
				'json');
		}
		
		$('div.groupBy.dropdown')
			.dropdown({
			 onChange: function(value, text, $selectedItem) {
				 // custom action
				 switch(value)
					 {
						 case 'day':
							chart2.options.axisX.interval = 7;
							chart2.options.axisX.valueFormatString = "D MMMM YYYY";
							 break;
						 case 'week':
							chart2.options.axisX.interval = 2;
							chart2.options.axisX.valueFormatString = "D MMMM YYYY";
							 break;
						 case 'month':
							chart2.options.axisX.interval = 1;
							chart2.options.axisX.valueFormatString = "MMMM YYYY";
							 break;
							 
					 }
				 chart2.options.axisX.intervalType = value;
				 range.data('daterangepicker').notify();
			 }
		})
		;
		
		range.data('daterangepicker').notify();
	})
	;
</script>
<div class="ui one column grid container">
	
	<div class="ui huge breadcrumb header column">
		<a class="section" 
		   href="<?php echo base_url('statistics/index/'); ?>">
			Statistics
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $title; ?></div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column">
		<form class="ui form">
			<div class="inline fields">
				<div class="field">
					<label>Select a period:</label>
					<div class="ui left icon input">
						<input placeholder="Select a date range" type="text" id="range" name="range">
						<i class="calendar icon"></i>
					</div>
				</div>
				<div class="field">
					<label>Group by</label>
					<div class="ui normal selection dropdown groupBy">
						<i class="calendar outline icon"></i>
						<input name="group_by" type="hidden">
						<i class="dropdown icon"></i>
						<div class="default text">Show </div>
						<div class="menu">
						<div class="item"  data-value="day">Day</div>
						<div class="item"  data-value="week">Week</div>
						<div class="item"  data-value="month">Month</div>
						</div>
					</div>
				</div>
				<div class="field">
					<div class="ui checkbox stacked">
						<input name="stacked" type="checkbox" autocomplete="off">
						<label>Use stacked are chart</label>
					</div>
				</div>
			</div>
			<div class="inline equal width fields">
				<div class="field">
					<label>Include customers:</label>
					<?php echo form_multiselect('customers', $customers_list, '*', 'class="ui fluid search dropdown" multiple=""'); ?>
				</div>
				<div class="field">
					<label>Exlude customers:</label>
					<?php echo form_multiselect('customers_excl', $customers_list, NULL, 'class="ui fluid search dropdown" multiple=""'); ?>
				</div>
				<div class="field">
					<label>Filter by list:</label>
					<?php echo form_multiselect('lists', $delivery_lists, NULL, 'class="ui fluid search dropdown" multiple=""'); ?>
				</div>
			</div>
		</form>

		<div class="ui small negative message hidden" id="msg">
			<div class="header"></div>
		</div>

		<div class="ui segment container">
			<div id="chartContainer" style="height: 400px;width: 100%;"></div>
		</div>
			
		<div class="ui hidden header divider"></div>
	</div>

</div>
