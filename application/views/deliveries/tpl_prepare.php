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
		<div class="active step">
			<i class="truck icon"></i>
			<div class="content">
				<a class="title" href="{url_details}">Prepare delivery</a>
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
		<a class="ui  right floated button"
		   href="{url_details}">
			View details
			<i class="right chevron icon"></i>
		</a>
		<a class="ui primary button"
		   href="{url_print_invoices}">
			<i class="print icon"></i>
			Print all invoices
		</a>
		<div class="ui teal label"><i class="fast shipping icon"></i>Waiting for delivery</div>
		<div class="ui left pointing red label">FAST FAST !!! Night is coming at {sunset}</div>
	</div>
</div>