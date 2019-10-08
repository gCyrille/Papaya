<div class="ui segments">
	<div class="ui top attached secondary segment">
		<a href="{url_details}" class="ui large ribbon grey label">
			{date}
		</a>
	</div>
	<div class="ui three attached small steps">
		<div class="completed step">
			<i class="shopping basket icon"></i>
			<div class="content">
				<div class="title">Collect vegetables</div>
				<div class="description">Enter orders and collect vegetables</div>
			</div>
		</div>
		<div class="completed step">
			<i class="truck icon"></i>
			<div class="content">
				<div class="title">Prepare delivery</div>
				<div class="description">Print invoices and payment sheet</div>
			</div>
		</div>
		<div class="active step">
			<i class="calculator icon"></i>
			<div class="content">
				<a class="title" href="{url_details}">Accounting</a>
				<div class="description">Add paiment and print accounting page</div>
			</div>
		</div>
	</div>
	<div class="ui bottom attached segment">
		<a class="ui  right floated button"
		   href="{url_details}">
			View details
			<i class="right chevron icon"></i>
		</a>
		<a class="ui primary button"
			 href="{url_add_payment}">
			<i class="credit card icon"></i>
			Register all payments
		</a>
		<?php if ($nb_payments > 0): ?>
			<div class="ui yellow label"><i class="hourglass half icon"></i> Accounting not ready</div>
		<?php else: ?>
			<div class="ui green label"><i class="check icon"></i> Accounting ready!</div>
		<?php endif; ?>
		<div class="ui left pointing label">remaining {nb_payments} payment(s)</div>
	</div>	
</div>