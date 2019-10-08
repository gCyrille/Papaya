<script>
	$(document)
	.ready(function() {
		
	})
	;
</script>
<div class="ui one column grid container">
	
	<div class="ui huge breadcrumb header column">
		<div class="active section">Statistics</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column">
		<div class="ui big selection divided list">
			<div class="item">
				<i class="icon big icons">
					<i class="balance icon"></i>
					<i class="chart line corner bottom left icon"></i>
				</i>
				<div class="content">
					<a class="header"
					   href="<?php echo base_url('statistics/sales'); ?>">
						Sales by customer
					</a>
					<div class="description">Over time with a simple line chart.</div>
				</div>
			</div>
			<div class="item">
				<i class="icon big icons">
					<i class="user icon"></i>
					<i class="chart bar corner bottom left icon"></i>
				</i>
				<div class="content">
					<a class="header"
					   href="<?php echo base_url('statistics/top_customers'); ?>">
						Top customers
					</a>
					<div class="description">Sorted list of customers.</div>
				</div>
			</div>
			<div class="item">
				<i class="icon big icons">
					<i class="lemon icon"></i>
					<i class="chart bar corner bottom left icon"></i>
				</i>
				<div class="content">
					<a class="header"
					   href="<?php echo base_url('statistics/top_vegetables'); ?>">
						Top vegetables
					</a>
					<div class="description">Sorted list of vegetables.</div>
				</div>
			</div>
			<div class="item">
				<i class="icon big icons">
					<i class="lemon icon"></i>
					<i class="eye corner bottom left icon"></i>
				</i>
				<div class="content">
					<a class="header"
					   href="<?php echo base_url('statistics/overview_vegetable'); ?>">
						Vegetable overview
					</a>
					<div class="description">General summary for vegetable.</div>
				</div>
			</div>
		</div>
		<div class="ui hidden header divider"></div>
	</div>

</div>
