<div class="ui segments">
	<div class="ui top attached secondary segment">
		<a href="{url_details}" class="ui large ribbon grey label">
			{date}
		</a>
	</div>
	<div class="ui three attached small steps">
		<div class="active step">
			<i class="shopping basket icon"></i>
			<div class="content">
				<a class="title" href="{url_details}">Collect vegetables</a>
				<div class="description">Enter orders and collect vegetables</div>
			</div>
		</div>
		<div class="disabled step">
			<i class="truck icon"></i>
			<div class="content">
				<div class="title">Prepare delivery</div>
				<div class="description">Print invoices and payment sheet</div>
			</div>
		</div>
		<div class="disabled step">
			<i class="calculator icon"></i>
			<div class="content">
				<div class="title">Accounting</div>
				<div class="description">Add paiment and print accounting page</div>
			</div>
		</div>
	</div>
	<div class="ui bottom attached segment">
		<a class="ui right floated button"
			 href="{url_details}">
			View details
			<i class="right chevron icon"></i>
		</a>
		<a class="ui primary button"
		   href="{url_add_order}">
			<i class="plus icon"></i>
			Add order
		</a>
		<?php if ($nb_orders > 0): ?>
		<div class="ui teal label"><i class="print icon"></i> Collect list available</div>
		<div class="ui left pointing label">{nb_orders} order(s)</div>
		<?php endif; ?>
	</div>
</div>