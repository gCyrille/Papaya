<script>
	$(document)
	.ready(function() {

		$('.label.comment')
  			.popup()
		;
		
		var 
			$modal		= $('.ui.modal.close'),
			$oldFields 	= $('form').form('get values')
		;

		$modal.modal({
			closable  : true,
			onApprove : function($element)
			{
				$('form').form('submit');
			},
			onDeny : function($element)
			{
				location.assign($closeLink);
			}
		});
		
		$('.ui.modal.close .closem.button').click(function(){ $modal.modal('hide'); })
		
		$('.button.close').click(function(event) {
			newFields = $('form').form('get values');
			
			modified = false;
			
			$.each($oldFields, function( index, value ) {
				modified = modified || (newFields[index].toString() !== value.toString());
			});
	
			if (modified)
			{
				event.preventDefault();
				$closeLink = this.href;
				$modal.modal('show');
				return false;
			}
		})
		;
		
		var 
			$pass_modal2 = $('.ui.modal.password2'),
			$btn_close_inv	= $('a.button.close_inv'),
			$href_close_inv = null
		;
		
		$pass_modal2.modal({
			closable  : true,
			onApprove : function()
			{
				pass = $(this).find('input[name=password]').val();
				
				if ("<?php echo $edit_password; ?>" == pass) //password is true
				{
					location.assign($href_close_inv);
				}
				else
				{
					$href_close_inv = null;
				}
				$('.form.pass_form').form('clear')
			}
		});
		
		$btn_close_inv
			.on('click', function() 
			{ // custom action 
				event.preventDefault();
				$href_close_inv = this.href;
				$pass_modal2.modal('show');
			})
		;
		
	})
	;
</script>
<div class="ui one column grid container accordion">
	<div class="ui huge breadcrumb header column">
		<a class="section" 
		   href="<?php echo base_url('customers/list/'); ?>">
			Customers
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $customer['name']; ?></div>
	</div>
		
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon close button" href="<?php echo $back_link; ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header title active"><i class="dropdown icon"></i>Details</h2>
		<div class="content active">
			<?php include('edit_form.php'); ?>
		 </div>
	</div>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header title active" id="orders"><i class="dropdown icon"></i>Last orders</h2>
		<div class=" content active">
			<div class="ui piled segments">
				<div class="ui segment">
					<div class="ui relaxed divided list">
						<?php foreach($orders as $order): ?>
						<div class="item">
							<?php 
							switch($order->delivery_status):
								case 'collect': ?>
								<div class="right floated content">
									<a class="ui labeled icon button"
									   href="<?php echo base_url('/orders/edit/'.$order->id); ?>">
										<i class="shopping cart icon"></i>
										Modify order
									</a>
								</div>
								<i class="shopping basket icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id.'?rdtfrom='.uri_string()); ?>">
										<?php echo $order->delivery_name.' - '.mysql_to_nice_date($order->delivery_date); ?></a>
									<div class="description">
										<span class="ui small horizontal teal label">
											Vege<span class="detail"><?php echo count($order->vegetables); ?></span>	
										</span>
										Collecting vegetables... 
							<?php break; 
								case 'prepare': ?>
								<div class="right floated content">
									<a class="ui labeled icon button"
									   href="<?php echo base_url('invoices/view/'.$order->id); ?>">
										<i class="pdf file icon"></i>
										View invoice
									</a>
								</div>
								<i class="shipping icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id.'?rdtfrom='.uri_string()); ?>">
										<?php echo $order->delivery_name.' - '.mysql_to_nice_date($order->delivery_date); ?></a>
									<div class="description">
										<span class="ui small horizontal teal label">
											<?php echo ($order->status === 'printed') ? 'Printed' : 'Not printed'; ?>
										</span>
										<span class="ui small horizontal green basic label">
											Zmk<span class="detail" style="margin-left: 0.3em;"><?php echo format_kwacha($order->total); ?></span>	
										</span> 
										Not yet delivered...
							<?php break;
								case 'accounting': ?>
								<?php if ($order->status === 'not_paid' && ! $order->payment_registered): ?>
								<div class="right floated content">
									<a class="ui labeled icon button"
									   href="<?php echo base_url('accounting/register/'.$order->id); ?>">
										<i class="credit card outline icon"></i>
										Register payment
									</a>
								</div>
								<?php endif; ?>
								<i class="calculator icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id.'?rdtfrom='.uri_string()); ?>">
										<?php echo $order->delivery_name.' - '.mysql_to_suff_date($order->delivery_date); ?></a>
									<div class="description">
										<span class="ui small horizontal teal label">
											<?php echo ($order->status === 'paid')? 'PAID' : 'NOT PAID'; ?>
										</span>
										<span class="ui small horizontal green basic label">
											Zmk<span class="detail" style="margin-left: 0.3em;"><?php echo format_kwacha($order->total); ?></span>	
										</span> 
										<?php echo ($order->status === 'paid')? 'Everything is good!' : 'Wainting for payment...'; ?>
							<?php break; 
								default: ?>
								<?php if ($order->status === 'not_paid'): ?>
								<div class="right floated content">
									<a class="ui labeled icon button close_inv"
									   href="<?php echo base_url('accounting/close_invoice/'.$order->id.'?rdtfrom='.uri_string()); ?>">
										<i class="handshake outline icon"></i>
										Close Invoice
									</a>
								</div>
								<?php endif; ?>
								<i class="lock icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id.'?rdtfrom='.uri_string()); ?>">
										<?php echo $order->delivery_name.' - '.mysql_to_nice_date($order->delivery_date); ?></a>
									<div class="description">
										<span class="ui small horizontal teal label">
											<?php echo ($order->status === 'paid') ? 'PAID' : 'NOT PAID'; ?>
										</span>
										<span class="ui small horizontal green basic label">
											Zmk<span class="detail" style="margin-left: 0.3em;"><?php echo format_kwacha($order->total); ?></span>	
										</span> 
							<?php endswitch; ?>
									<?php if(! empty($order->comments)): ?>
									<span class="ui small left pointing label comment"
										  data-title="For <?php echo $customer['name']; ?>" 
										  data-content="<?php echo $order->comments; ?>" 
										  data-variation="very wide basic"
										  style="">
										<i class="comment outline icon"></i>Comment
									</span>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
	<!--
						<div class="item">

							<div class="right floated content">
								<div class="ui labeled icon button">
									<i class="shopping cart icon"></i>
									Modify order
								</div>
							</div>
							<i class="shipping icon"></i>
							<div class="content">
								<a class="header">Mfuwe Thursday - 23/08/2018</a>
								<div class="description">Not yet delivered</div>
							</div>
						</div>
						<div class="item">
							<div class="right floated content">
								<div class="ui labeled icon button">
									<i class="credit card outline icon"></i>
									Register payment
								</div>
							</div>
							<i class="hourglass half icon"></i>
							<div class="content">
								<a class="header">Mfuwe Monday - 20/08/2018</a>
								<div class="description">Total: k60.00, NOT PAID</div>
							</div>
						</div>
						<div class="item">
							<i class="shopping basket icon"></i>
							<div class="content">
								<a class="header">Mfuwe Thursday - 16/08/2018</a>
								<div class="description">Total: k89.00, PAID</div>
							</div>
						</div>
						<div class="item">
							<i class="shopping basket icon"></i>
							<div class="content">
								<a class="header">Mfuwe Thursday - 09/08/2018</a>
								<div class="description">Total: k56.50, PAID</div>
							</div>
						</div>
	-->
					</div>
				</div>
				<?php if ($show_more != -1): ?>
				<div class="ui secondary segment">
					<a href="<?php echo base_url(uri_string().'?more='.$show_more); ?>#orders">
						<i class="angle double down icon"></i>Show more
					</a>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="ui hidden divider"></div>
							
	<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal close">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="save icon" style="display:inline-block;"></i>
			Save the modifications
		</div>
		<div class="content">
			<p>Would you like to save the modifications before to close?</p>
		</div>
		<div class="actions">
			<div class="ui basic left floated closem button">
				Cancel
			</div>
    		<div class="ui red deny inverted button">
				<i class="cancel icon"></i>
				Quit
			</div>
			<div class="ui green approve button">
				<i class="save icon"></i>
				Save
			</div>
		</div>
	</div>
							
	<div class="ui mini modal password2">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="lock alternate icon" style="display:inline-block;"></i>
			Close invoice
		</div>
		<div class="content ui form pass_form">
			<div class="field">
				<label>Please enter the password</label>
				<div class="ui input">
					<input name="password" type="password">
				</div>
			</div>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				Cancel
			</div>
			<div class="ui green approve button">
				<i class="lock open icon"></i>
				Unlock
			</div>
		</div>
	</div>
</div>