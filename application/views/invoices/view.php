<script type="text/javascript">
$(document)
	.ready(function() {
		$('.ui.dropdown')
		  .dropdown()
		;
	})
	;
</script>
<style type="text/css">
	#invoice > .ui.container > img
	{
		display: none !important;
	}
</style>
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
	.ui.main>.ui.column.container>.column:not(#invoice),
	#invoice > *
	{
		display: none !important;
	}
	#invoice > .container 
	{
		display: block !important;
		box-shadow: none;
		border: none;
	}
	#invoice > .ui.container > img
	{
		display: block !important;
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
		<div class="active section">View invoice</div>
	</div>
	
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo base_url($this->redirect->build_url('/deliveries/view/'.$delivery->id)); ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
		<a class="ui basic button right floated" href="<?php echo base_url('/customers/edit/'.$order->c_id.'?rdtfrom='.uri_string()); ?>">
			<i class="user outline icon"></i>
			<?php echo $order->customer; ?>
		</a>
		<div class="ui buttons large right floated">
			<?php if(isset($prev_url)): ?>
			<a class="ui left icon button"
			   href="<?php echo $prev_url; ?>">
				<i class="left arrow icon"></i>
			</a>
			<?php endif; ?>
			<?php if (isset($next_url)): ?>
			<a class="ui right icon button"
			   href="<?php echo $next_url; ?>">
				<i class="right arrow icon"></i>
			</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="ui column">
		<div class="ui buttons">
			<?php if( ! in_array($order->status, array('printed', 'not_paid', 'paid'))): ?>
			<a class="ui primary left labeled icon button"
			   href="<?php echo base_url('invoices/print/'.$order->id); ?>">
				<i class="print icon"></i>
				Print
			</a>
			<?php else: ?>
			<a class="ui primary left labeled icon button"
			   href="<?php echo base_url('invoices/print/'.$order->id); ?>">
				<i class="file pdf outline icon"></i>
				Save
			</a>
			<?php endif; ?>
		</div>
		<?php if(! $payment_registred): ?>
		<div class="ui icon dropdown right floated button">
			<i class="wrench icon"></i>
			<div class="menu">
				<a class="item"
				   href="<?php echo base_url('orders/edit/'.$order->id.'?rdtfrom='.uri_string()); ?>">Modify the order</a>
				<a class="item"
				   href="<?php echo base_url('invoices/regenerate/'.$order->id.'?rdtfrom='.uri_string()); ?>">Regenerate invoice</a>
			</div>
		</div>
		<?php endif; ?>
	</div>	
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column" id="invoice">
		<h2 class="ui horizontal divider header">Invoice for <?php echo $order->customer; ?></h2>
		<div class="ui container segment" >
			<?php echo $invoice; ?>
		</div>
	</div>
	
	<div class="ui column">
		<div class="ui buttons">
			<?php if( ! in_array($order->status, array('printed', 'not_paid', 'paid'))): ?>
			<a class="ui primary left labeled icon button"
			   href="<?php echo base_url('invoices/print/'.$order->id); ?>">
				<i class="print icon"></i>
				Print
			</a>
			<?php else: ?>
			<a class="ui primary left labeled icon button"
			   href="<?php echo base_url('invoices/print/'.$order->id); ?>">
				<i class="file pdf outline icon"></i>
				Save
			</a>
			<?php endif; ?>
		</div>
		<div class="ui buttons large right floated">
			<?php if(isset($prev_url)): ?>
			<a class="ui left icon button"
			   href="<?php echo $prev_url; ?>">
				<i class="left arrow icon"></i>
			</a>
			<?php endif; ?>
			<?php if (isset($next_url)): ?>
			<a class="ui right icon button"
			   href="<?php echo $next_url; ?>">
				<i class="right arrow icon"></i>
			</a>
			<?php endif; ?>
		</div>
	</div>		
</div>
		