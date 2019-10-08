<style type="text/css">
	.xl73 {
		text-align: right !important;
	}
	.xl76 {
		font-style:italic;
	}
	.xl77,
	.xl78 {
		font-style:italic;
		text-align: right !important;
	}
	.xl80 {
		font-size:14.0pt;
		font-weight:700;
	}
	.xl82 {
		text-align:right !important;
	}
	.xl83 {
		font-style:italic;
	}
	.xl83,
	.xl84,
	.xl85,
	.xl86,
	.xl97 {
		background:#D8D8D8 !important;
	}
	.xl85,
	.xl86 {
		font-style:italic;
		text-align: right !important;
	}
	.xl88,
	.xl89,
	.xl90,
	.xl91 {
		font-style: italic;
/*		border-top: 1pt solid windowtext !important;*/
		border-right: none;
/*		border-bottom: 1pt solid windowtext !important;*/
		border-left:none;
		background: #BFBFBF !important;
		font-weight: bold;
	}
	.xl88 {
/*		border-left: 1pt solid windowtext !important;*/
	}
	.xl89 {
	}
	.xl90 {
		text-align:center !important;
	}
	.xl91 {
		text-align:center !important;
/*		border-right: 1pt solid windowtext !important;*/
	}
	.xl92,
	.xl93,
	.xl94,
	.xl95,
	.xl96 {
		border-top: 0.5pt solid windowtext !important;
		border-right:none;
		border-bottom: 2.0pt double windowtext !important;
		border-left:none;
	}
	.xl96 {
		font-weight:700;
		text-align: right !important;
	}
	.xl97,
	.xl98 {
		text-align:center !important;
	}
	.xl99 {
		text-align:right !important;
		border-top:none;
		border-right:none;
		border-bottom:1pt solid windowtext !important;
		border-left:none;
	}
	.xl74,
	.xl82,
	.xl99 {
		vertical-align: bottom;
	}
	.xl100 {
		text-align:center !important;
	}
	.ui.table tr td {
		border: none;
	}
	.ui.basic.table thead {
		visibility: hidden;
	}
	.ui.basic.table td:last-child
	{
		padding-right: 0.5em !important;
	}
	.ui.basic.table td:first-child
	{
		padding-left: 0.5em !important;
	}
	#invoice>img {
		display: none;
	}
	#invoice>table {
		background: #FFF;
	}
	@page {
	 	margin: 0.6cm;
		font-size: 0.6rem !important;
	}
</style>
<img src="{header_url}" class="ui fluid image" />
<table class="ui single line compact very basic table">
	  <thead>
		<tr>
			<th class="seven wide"></th>
			<th class="two wide"></th>
			<th class="tree wide"></th>
			<th class="two wide"></th>
			<th class="two wide"></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>To</td>
			<td>&nbsp;</td>
			<td class="xl73">Chikowa</td>
			<td class="xl100">{date}</td>
			<td></td>
		</tr>
		<tr>
			<td class="xl80">{customer}</td>
			<td>&nbsp;</td>
			<td class="xl82">Invoice no.</td>
			<td class="xl99">{number}</td>
			<td class="xl74"></td>
		</tr>
		<tr>
			<td colspan="5"></td>
		</tr>
		<tr>
			<td class="xl88">Description</td>
			<td class="xl89">Unit</td>
			<td class="xl90">Qty</td>
			<td class="xl90">Price</td>
			<td class="xl91">Amount</td>
		</tr>
		{non_vegets}
		<tr>
			<td class="xl83">{description}</td>
			<td class="xl84">{unit}</td>
			<td class="xl97">{qty}</td>
			<td class="xl85">{price}</td>
			<td class="xl86">{amount}</td>
		</tr>
		{/non_vegets}
		{vegets}
		<tr>
			<td class="xl76">{description}</td>
			<td>{unit}</td>
			<td class="xl98">{qty}</td>
			<td class="xl77">{price}</td>
			<td class="xl78">{amount}</td>
		</tr>
		{/vegets}
		<tr>
			<td class="xl92">&nbsp;</td>
			<td class="xl93">&nbsp;</td>
			<td class="xl94">Total</td>
			<td class="xl95">&nbsp;</td>
			<td class="xl96">{total}</td>
		</tr>
		<tr>
			<td colspan="5"></td>
		</tr>
		<?php if (count($unpaids) > 0): ?>
		{unpaids}
		<tr>
			<td colspan="2"></td>
			<td>{date}</td>
			<td></td>
			<td class="xl78" align="right">{total}</td>
		</tr>
		{/unpaids}
		<tr>
			<td colspan="5"></td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td class="xl94">Total</td>
			<td class="xl95">&nbsp;</td>
			<td class="xl96">{total_balances}</td>
		</tr>
		<tr>
			<td colspan="5"></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>