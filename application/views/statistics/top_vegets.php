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
		$('div.chartType.dropdown')
			.dropdown('set selected', 'bar')
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
				vegets_excl = $('select[name=vegets_excl]').val();
				list_ids = $('select[name=lists]').val();
				customers_excl = $('select[name=customers_excl]').val();
				sorted_by = $('select[name=sortedby]').val();
				ajaxLoadChart(start, end, list_ids, vegets_excl, customers_excl, sorted_by);
			}
		);
		
		// Update chart when customer changes
		$('select[name=vegets_excl]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		$('select[name=customers_excl]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		$('select[name=lists]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		$('select[name=sortedby]').on('change', function(){
			range.data('daterangepicker').notify();
		});
		
		// Create a new CanvaJS instance
		var chart2 = new CanvasJS.Chart("chartContainer", {
			animationEnabled: true,
			exportEnabled: true,
			zoomEnabled: true,
			axisY2:{
				interlacedColor: "rgba(1,77,101,.2)",
				gridColor: "rgba(1,77,101,.1)",
//				prefix: "Zmk ",
				stripLines: [{
					value: 0,
					label: "Median",
				},
				{
					value: 0,
					label: "< 80%",
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
				name: "vegetables",
				axisYType: "secondary",
//				color: "#014D65",
				toolTipContent: "{label}: <strong>{y} {unit}</strong>",
//				xValueType: "date",
				unit: "",
				dataPoints: []
			}]
		});
		
		// Function for loading data via AJAX and showing it on the chart
		var ajaxLoadChart = function(startDate, endDate, listIds, vegetsExcl, customersExcl, sortedBy) 
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
				"<?php echo base_url('statistics/get_vegetables'); ?>", 
				{
					start: startDate.format('{yyyy}-{MM}-{dd}'),
					end: endDate.format('{yyyy}-{MM}-{dd}'),
					vegetsExcl: vegetsExcl,
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
						$.each(data.data, function() {
							set.push({
								label: this.label,
								y: parseInt(this.value, 10),
								unit: this.unit
							});
						});
						sorted_by = $('select[name=sortedby]').val();
						if (sorted_by == 'quantity')
						{
							chart2.options.data[0].toolTipContent = "{label}: <strong>{y} {unit}</strong>";
						}
						else
						{
							chart2.options.data[0].toolTipContent = "{label}: Zmk <strong>{y}</strong>";
						}
						
						chart2.options.axisY2.stripLines[0].value = data.median;
						chart2.options.axisY2.stripLines[0].label = "50% < "+data.median;
						chart2.options.axisY2.stripLines[1].value = data.per80;
						chart2.options.axisY2.stripLines[1].label = "80% < "+data.per80;
						
						// With CanvaJS
						chart2.options.data[0].dataPoints = set;
						chart2.render();
					}
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
					<label>Sort by</label>
					<select class="ui dropdown" name="sortedby">
						<option value="income">Income</option>
						<option value="quantity">Quantity</option>
					</select>
				</div>
				<div class="field">
					<label>Show:</label>
					<div class="ui normal selection dropdown chartType">
						<input name="chart_type" type="hidden">
						<i class="dropdown icon"></i>
						<div class="default text">Show </div>
						<div class="menu">
<!--						<select class="ui labeled icon dropdown" name="chart_type">-->
						<div class="item"  data-value="pie"><i class="chart pie icon"></i> Pie chart</div>
						<div class="item"  data-value="bar"><i class="chart bar icon"></i> Bar chart</div>
<!--						</select>-->
						</div>
					</div>
				</div>
			</div>
			<div class="equal width fields">
				<div class="field">
					<label>Exlude vegetables:</label>
					<?php echo form_multiselect('vegets_excl', $vegets_list, NULL, 'class="ui search dropdown" multiple=""'); ?>
				</div>
				<div class="field">
					<label>Exlude customers:</label>
					<?php echo form_multiselect('customers_excl', $customers_list, NULL, 'class="ui fluid search dropdown" multiple=""'); ?>
				</div>
				<div class="field">
					<label>Filter by list:</label>
					<?php echo form_multiselect('lists', $delivery_lists, NULL, 'class="ui search dropdown" multiple=""'); ?>
				</div>
			</div>
		</form>

		<div class="ui small negative message hidden" id="msg">
			<div class="header"></div>
		</div>

		<div class="ui segment container">
			<div id="chartContainer" style="height: 500px;width: 100%;"></div>
		</div>
			
		<div class="ui hidden header divider"></div>
	</div>

</div>
