<style type="text/css">
	.ui.input input[readonly] {
		pointer-events: none;
	}
	.ui.input.transparent {
		padding: 0.67857143em 1em;
	}
	form .row {
		background-color: #FFF;
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
		<div class="active section">Register payment</div>
	</div>

	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo base_url('/deliveries/view/'.$delivery->id); ?>">
				<i class="cancel icon"></i>
				Close
			</a>
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
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<h2 class="ui horizontal divider header">Payment for <?php echo $order->customer; ?></h2>
	
	<div class="ui one column row">
		<div class="one column row">
			<div class="ui icon success message">
				<i class="handshake icon"></i>
				<div class="content">
					<div class="header">
						Payment registered
						<a class="ui negative right floated basic button" href="<?php echo base_url('accounting/delete/'.$order->id); ?>">
							<i class="trash alternate left icon"></i>
							Delete the payment
						</a>
						<a class="ui right floated basic secondary button" href="<?php echo $modify_url; ?>">
							<i class="redo alternate left icon"></i>
							Edit the payment
						</a>
					</div>
					<ul class="list">
						<li>Total due: <?php echo $total_due; ?></li>
						<li>Total received: <?php echo $total_received; ?></li>
						<li>Paiment details:
							<ul>
							<?php foreach ($paids as $paid): ?>
								<li><strong><?php echo $paid->date; ?></strong> <i class="long arrow alternate right icon"></i><?php echo $paid->payment; ?></li>
							<?php endforeach; ?>
							</ul>
						</li>
					</ul>
					<?php if ($order->delivery_status != 'closed'): ?>
					<p>
					</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		
	</div>
	<div class="ui column">
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
