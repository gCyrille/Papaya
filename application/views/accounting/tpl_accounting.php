<style type="text/css">
	.ui.table tr td,
	.ui.table tfoot th,
	.ui.table
	{
		border: none !important;
	}
	.ui.table thead 
	{
		text-align: center;
		vertical-align: middle;
		background: #C2C2C2 !important;
		white-space: normal;
	}
	
	.ui.table th.date 
	{
		font-size: 12.0pt;
		font-weight: 700;
	}
	.ui.table th.total
	{
		text-align: center;
		vertical-align: middle;
		font-weight: 100;
	}
	.ui.table th.paid
	{
		font-weight: 700;
		text-align: center;
		vertical-align: middle;
	}
	.ui.table th.cat 
	{
		font-weight: 100;
		font-style: italic;
		text-align: center;
		vertical-align: middle;
	}
	.ui.table tbody td:nth-child(-n+3)
	{
		background: #C2C2C2 !important;
	}
	.ui.table tbody td:nth-child(3)
	{
		font-weight: 700;
	}
	.ui.table tbody td
	{
		text-align: right;
	}
	.ui.table tfoot tr:first-child th
	{
		text-align: right;
		font-weight: 700;
		border-top: 0.5pt solid black !important;
		border-right: none !important;
		border-bottom: 2pt double black !important;
		border-left: none !important;
		background: #C2C2C2 !important;
	}
	
	.ui.table tr td:first-child,
	.ui.table tfoot tr:first-child th:first-child
	{
		text-align: left;
	}
	.ui.table tfoot th:nth-child(2)
	{
		font-weight: 700;
		text-align: right;
	}
	.ui.table tfoot th
	{
		padding: 0.4em;
	}
	.ui.table tfoot tr:nth-child(n+2) th
	{
		padding: 0.3em !important;
	}
	.ui.table tfoot th.total
	{
		border: 1.5pt solid black !important;
		padding: 0.1em 0.3em !important;
	}
	.ui.table tfoot th.change
	{
		color: red !important;
	}
	.ui.basic.table th:last-child,
	.ui.basic.table td:last-child
	{
		padding-right: 0.5em !important;
	}
	.ui.basic.table th:first-child,
	.ui.basic.table td:first-child
	{
		padding-left: 0.5em !important;
	}
	.ui.basic.table tr:first-child
	{
		padding-top: 0.5em !important;
	}

	@page {
	 	margin: 0.5cm;
		font-size: 0.7rem !important;
	}
</style>
<table class="ui single line very compact very basic table">
	<thead>
		<tr>
			<th class="date">{date}</th><!--Name column-->
			<th class="total">Total</th>
			<th class="paid">Paid</th>
			{accounting_cats}
			<th class="cat">{title}</th>
			{/accounting_cats}
		</tr>
	</thead>
	<tbody>
		{payments}
		<tr>
			<td>{customer}</td>
			<td>{total_due}</td>
			<td>{total_paid}</td>
			{details_row}
			<td>{value}</td>
			{/details_row}
		</tr>
		{/payments}
	</tbody>
	<tfoot>
		<tr>
			<th>Total</th>
			<th>{total_due}</th>
			<th>{total_paid}</th>
			{totals}
			<th>{value}</th>
			{/totals}
		</tr>
		<tr><th></th> </tr>
		<tr><th></th> </tr>
		<tr><th></th> </tr>
		<tr>
			<th colspan="2"></th>
			<th class="total">{total_paid}</th>
			<th colspan="3">Payment received</th>
		</tr>
		<tr>
			<th colspan="2"></th>
			<th>{expenses_cash}</th>
			<th colspan="3">Cash and cheques</th>
		</tr>
		{expenses}
		<tr>
			<th colspan="2"></th>
			<th>{value}</th>
			<th colspan="3">{title}</th>
		</tr>
		{/expenses}
		<tr>
			<th colspan="2"></th>
			<th class="change">{expenses_change}</th>
			<th colspan="3">Change given for delivery</th>
		</tr>
		<tr>
			<th colspan="2"></th>
			<th class="total">{expenses_total}</th>
			<th colspan="3">Total</th>
		</tr>
		<tr>
			<th colspan="2"></th>
			<th>{expenses_diff}</th>
			<th colspan="3">Result</th>
		</tr>
	</tfoot>
</table>
