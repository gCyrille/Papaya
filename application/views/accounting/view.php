<script>
	$(document)
	.ready(function() {
		
	})
	;
</script>
<style type="text/css" media="print"> 
	body
	{
		background: none !important;
	}
	.ui.left.fixed.menu,
	.ui.bottom.fixed.menu,
	.ui.vertical.footer
	{
		display: none !important;
	}
	.ui.main.container 
	{
		 margin-top: 0em; 
		 padding-left: 0rem !important; 
		 padding-right: 0rem; 
		 margin-bottom: 0em; 
	}
	.ui.main>.ui.column.container>.column:not(#acc_sheet),
	#acc_sheet > *
	{
		display: none !important;
	}
	#acc_sheet > .container 
	{
		display: block !important;
		box-shadow: none;
		border: none;
	}
</style>
<div class="ui one column grid container">
	<div class="ui huge breadcrumb header column">
		<a class="section" 
		   href="<?php echo base_url('lists'); ?>">
			Delivery lists
		</a>
		<i class="right angle icon divider"></i>
		<a class="section" 
		   href="<?php echo base_url('lists/view/'.$delivery->l_id); ?>">
			<?php echo $delivery->list_name; ?>
		</a>
		<i class="right angle icon divider"></i>
		<a class="section" 
		   href="<?php echo base_url('deliveries/view/'.$delivery->id); ?>">
			<?php echo mysql_to_suff_date($delivery->delivery_date); ?>
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section">View accounting</div>
	</div>
	
	<div class="ui column">
		<div class="ui small buttons">
			<a class="ui positive left labeled icon button"
			   href="<?php echo base_url('deliveries/view/'.$delivery->id); ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
		<div class="ui buttons right floated">
			<a class="ui primary left labeled icon button"
			   href="<?php echo base_url('accounting/print_accounting_sheet/'.$delivery->id); ?>">
				<i class="print icon"></i>
				Print
			</a>
		</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	<div class="ui column" id="acc_sheet">
		<h2 class="ui horizontal divider header">Accounting</h2>
		<div class="ui container segment">
			<?php echo $template; ?>
		</div>
	</div>
	
	<div class="ui hidden column divider"></div>
</div>
