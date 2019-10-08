<style type="text/css">
.ui.table th,
.ui.table td
	{
		border: 0.75px solid black !important;
	}
	* {
		font-size: 0.9em;
	}
</style>
<table class="ui tree column celled striped definition very padded large table">
	<thead>
		<tr>
			<th rowspan="2">{date}</th>
			<th colspan="2">Cash change: </th>
		</tr>
		<tr>
			<th>Money due</th>
			<th>Money received</th>
		</tr>
	</thead>
	<tbody>
		{orders}
		<tr>
			<td class="">{customer}</td>
			<td class="center aligned">{total}</td>
			<td class=""></td>
		</tr>
		{/orders}
	</tbody>
</table>
