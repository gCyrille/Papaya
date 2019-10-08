<script>
	$(document)
	.ready(function() {
		$('select.dropdown')
			.dropdown()
		;
		$('.button.dropdown')
			.dropdown({
				on: 'hover'
			})
		;
		<?php if (isset($download_this)): ?>
		document.location.assign("<?php echo $download_this; ?>");
		<?php endif; ?>

		$('.label.comment')
  			.popup()
		;
		$('.button.animated')
			.popup({
				position : 'bottom center',
				target   : '.button.animated',
				content  : 'Print payment sheet'
			})
		;
		
		$('.hideshow_qtt.checkbox')
  			.checkbox({
				onChecked: function() {
			  		$('tbody tr[data-qtt="0"]').fadeOut(250);
				},
				onUnchecked: function() {
				  	$('tbody tr[data-qtt="0"]').fadeIn(250);
				}
			})
		;
		
		$('.hideshow_qtt.checkbox').checkbox('<?php echo $big_total > 0 ? 'check' : 'uncheck'; ?>');
		
		var 
			$expertModal 	= $('.ui.modal.expert'),
			$action_link	= null
		;
		
		$expertModal.modal({
			closable  : true,
			centered: true,
			onApprove : function($element)
			{
				location.assign($action_link);
			}
		});
		
		$('.dropdown.expert')
			.dropdown({
				action: 'hide',
				on: 'click'
			})
		;
		
		$('.dropdown.expert .item').on('click', function() {
				$this = $(this);
			
				var msg = $this.data('confirm');
				$action_link = $this.data('link');
				$($expertModal[0]).find('.content p').text(msg);
				$($expertModal[0]).find('.header').html($this.html());
				$expertModal.modal('show');
				return false;
			})
		;
	})
	;
</script>
<style type="text/css">
	[data-tooltip]::after 
	{
		z-index: 3001;
	}
	[data-tooltip]::before 
	{
		z-index: 3002;
	}
</style>
<div class="ui one column grid container accordion">

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
		<div class="active section"><?php echo mysql_to_suff_date($delivery->delivery_date); ?></div>
	</div>
	
	<div class="ui hidden header divider"></div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column">
		<div class="ui top attached sub header secondary segment">
			<?php echo mysql_to_nice_date($delivery->delivery_date); ?>
			<div class="ui top left pointing dropdown circular basic icon button right floated expert">
				<i class="cog icon"></i>
				<div class="menu">
					<div class="header">Expert menu</div>
					<?php switch($delivery->status): 
						case 'closed': ?>
					<div class="item"
						 data-confirm="Do you want to re-open the delivery and change the accounting?"
						 data-link="<?php echo base_url('accounting/reopen_accounting/'.$delivery->id); ?>">
						<i class="undo icon"></i>Return to the accounting
					</div>
					<?php break; case 'accounting': ?>
					<div class="item"
						 data-confirm="Do you want to add a customer with a new order?"
						 data-link="<?php echo base_url('deliveries/add_order/'.$delivery->id); ?>">
						<i class="user plus icon"></i>Add a customer
					</div>
					<div class="item"
						 data-confirm="Do you want to return to the list of invoices?"
						 data-link="<?php echo base_url('accounting/undo_prepare/'.$delivery->id); ?>">
						<i class="undo icon"></i>Return to prepare
					</div>
					<?php break; case 'prepare': ?>
					<div class="item"
						 data-confirm="Do you want to cancel the invoices and modify the orders?"
						 data-link="<?php echo base_url('deliveries/back_to_collect/'.$delivery->id); ?>">
						<i class="undo icon"></i>Return to the collect
					</div>
					<?php break; endswitch; ?>
					<div class="item"
						 data-confirm="Do you want to delete this delivery and all the related orders?"
						 data-link="<?php echo base_url('deliveries/delete/'.$delivery->id); ?>">
						<i class="trash icon"></i>Delete this delivery
					</div>
				</div>
			</div>
		</div>
<!--	Ribbon with steps and icons [Collect | Prepare | Accounting]	-->
		<div class="ui three attached small steps">
			<div class="<?php echo $collect_step; ?> step">
				<i class="shopping basket icon"></i>
				<div class="content">
					<div class="title">Collect vegetables</div>
					<div class="description">Enter orders and collect vegetables</div>
				</div>
			</div>
			<div class="<?php echo $prepare_step; ?> step">
				<i class="truck icon"></i>
				<div class="content">
					<div class="title">Prepare delivery</div>
					<div class="description">Print invoices and payment sheet</div>
				</div>
			</div>
			<div class="<?php echo $accounting_step; ?> step">
				<i class="calculator icon"></i>
				<div class="content">
					<div class="title">Accounting</div>
					<div class="description">Add paiment and print accounting page</div>
				</div>
			</div>
		</div>
<!-- Actions by steps: -->
		<div class="ui bottom attached segment">
<!--	#### Collect step	-->
		<?php if($delivery->status == 'collect'): ?>
			<a class="ui primary button"
			   href="<?php echo base_url('deliveries/add_order/'.$delivery->id); ?>">
				<i class="plus icon"></i>
				Add order
			</a>
			<?php if ($nb_orders > 0): ?>
			<div class="ui left pointing label"><?php echo $nb_orders; ?> order(s)</div>
			<a class="ui right floated button"
			   href="<?php echo base_url('invoices/generate_all/'.$delivery->id); ?>">
				Finish and print invoices
				<i class="right chevron icon"></i>
			</a>
			<?php else: ?>
			<a class="ui right floated disabled button"
			   href="#">
				Finish and print invoices
				<i class="right chevron icon"></i>
			</a>
			<?php endif; ?>
		<?php elseif($delivery->status == 'prepare'): ?>
<!--	#### Prepare step	-->
			<a class="ui primary button"
			   href="<?php echo base_url('invoices/print_all/'.$delivery->id); ?>">
				<i class="print icon"></i>
				Print invoices
			</a>
			<div class="ui teal label"><i class="fast shipping icon"></i>Waiting for delivery</div>
			<a class="ui right floated button"
			   href="<?php echo base_url('accounting/prepare/'.$delivery->id); ?>">
				Print payment sheet and deliver
				<i class="right chevron icon"></i>
			</a>
<!--	#### Accounting step	-->
		<?php elseif($delivery->status == 'accounting'): ?>
			<a class="ui primary button"
				 href="<?php echo base_url('accounting/register_all/'.$delivery->id); ?>">
				<i class="credit card icon"></i>
				Register all payments
			</a>
			<a class="ui animated vertical icon button"
			   href="<?php echo base_url('/accounting/print_payment_sheet/'.$delivery->id); ?>">
				<div class="visible content" style="margin-right: 0.7em;">
					<i class="th list icon"></i>
				</div>
				<div class="hidden content">
					<i class="print icon"></i>
				</div>
			</a>
			<?php if ($nb_payments > 0): ?>
				<div class="ui yellow label"><i class="hourglass half icon"></i> Accounting not ready</div>
				<div class="ui left pointing label">remaining <?php echo $nb_payments; ?> payments</div>
				<a class="ui right floated disabled button"
				   href="#">
					See accounting and close delivery
					<i class="right chevron icon"></i>
				</a>
			<?php else: ?>
				<div class="ui green label"><i class="check icon"></i> Accounting ready!</div>
				<a class="ui right floated button"
				   href="<?php echo base_url('accounting/generate/'.$delivery->id); ?>">
					See accounting and close delivery
					<i class="right chevron icon"></i>
				</a>
			<?php endif; ?>
<!--	#### Closed step	-->
		<?php elseif($delivery->status == 'closed'): ?>
			<a class="ui primary button"
			   href="<?php echo base_url('accounting/view/'.$delivery->id); ?>">
				<i class="calculator icon"></i>
				View accounting
			</a>
			<div class="ui left pointing black label"><i class="red minus circle icon"></i> Delivery closed</div>
		<?php endif; ?>
		</div>
	</div>
<!-- End of Actions -->
	
<!-- Start of vegetables list -->
	<div class="ui column">
		<h2 class="ui horizontal divider header title" id="vegetables"><i class="dropdown icon"></i>Vegetables</h2>
		<div class="vegetables ui content container text">
			<a class="ui left labeled button"
			   href="<?php echo base_url('/deliveries/export_list/'.$delivery->id); ?>">
				<span class="ui basic right pointing label">
					<i class="excel file outline icon"></i> Excel list
				</span>
				<div class="ui icon button " 
				   data-tooltip="Download Excel list for mailing"
				   data-position="bottom left">
					<i class="download icon"></i>
				</div>
			</a>

			<div class="ui left labeled button">
				<span class="ui basic right pointing label">
					<i class="print icon"></i> Collect list
				</span>
				<div class="ui buttons">
					<a class="ui button"
					   href="<?php echo base_url('/deliveries/print_list/'.$delivery->id); ?>"
					   data-tooltip="Print list detailed per customers"
					   data-position="bottom left">
						Full
					</a>
					<div class="or"></div>
					<a class="ui button"
					   href="<?php echo base_url('/deliveries/print_list/'.$delivery->id.'?short=true'); ?>"
					   data-tooltip="Print compact list with only totals"
					   data-position="bottom left">
						Compact
					</a>
				</div>
			</div>

			<?php if($delivery->status == 'collect'): ?>
			<a class="edit_vege ui icon right labeled primary button right floated" href="<?php echo base_url('/deliveries/edit_vegetables/'.$delivery->id); ?>">
					Edit the list 
					<i class="lemon icon"></i>
			</a>
			<?php endif; ?>

			<table class="ui selectable celled table">
				<thead>
					<tr>
						<th colspan="4">
							<div class="ui mini buttons right floated">
								<div class="ui slider checkbox hideshow_qtt">
									<input name="public" type="checkbox" autocomplete="off" id="hideshow_qtt">
									<label for="hideshow_qtt">Show only quantities to collect</label>
								</div>
							</div>
						</th>
					</tr>
					<tr>
						<th class="">Name</th>
						<th class="one wide">Price</th>
						<th class="one wide">Unit</th>
						<th class="one wide">Quantity</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($vegetables as $veget): ?>
					<tr class="<?php
							   if(isset($veget->not_from_list) AND $veget->not_from_list === TRUE)
							   {
								   echo 'error';
							   }
							   elseif($veget->accounting_cat !== NULL && $veget->accounting_cat !== 'veg')
							   {
								   echo 'highlight';
							   } ?>"
						data-qtt="<?php echo $delivery->quantities[$veget->id]; ?>"> <!-- Qtt data for filtering the table-->
						<td class="ui"><?php echo ucfirst($veget->name); ?></td>
						<td class="ui right aligned"><?php echo sprintf('%01.2f', $veget->price); ?></td>
						<td><?php echo element(strtolower($veget->unit), $units); ?></td>

						<td class="ui right aligned">
							<?php echo sprintf('%01.2f', $delivery->quantities[$veget->id]); ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr><th colspan="4">
						<?php //echo $v_pagination ?>
					</th></tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div class="ui column">
		<h2 class="ui horizontal divider header title active" id="orders"><i class="dropdown icon"></i>Orders</h2>
		<div class="orders ui content container text active">
			<div class="ui piled segments">
				<div class="ui attached segment">
					<div class="ui relaxed divided list">
						<?php foreach ($orders as $order): ?>
						<div class="item">
	<!--	Show order items according to the status	-->
							<?php 
							switch($delivery->status):
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
									   href="<?php echo base_url('/orders/view/'.$order->id); ?>">
										<?php echo $order->customer; ?></a>
									<div class="description">
										<?php echo count($order->vegetables).' item(s), '; ?>
							<?php break; 
								case 'prepare': ?>
								<div class="right floated content">
									<a class="ui labeled icon button"
									   href="<?php echo base_url('/invoices/view/'.$order->id); ?>">
										<?php if ($order->status == 'printed'): ?>
										<i class="file pdf icon"></i>
										Open invoice
										<?php else: ?>
										<i class="print alternate icon"></i>
										Print invoice
										<?php endif; ?>
									</a>
								</div>
								<i class="shipping icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id); ?>">
										<?php echo $order->customer; ?></a>
									<div class="description">
										<span class="ui small horizontal teal label"><?php echo ($order->status === 'printed') ? 'Printed' : 'Not printed'; ?></span>
							<?php break;
								case 'accounting': ?>
								<div class="right floated content">
									<a class="ui labeled icon button"
										href="<?php echo base_url('/accounting/register/'.$order->id); ?>">
										<i class="credit card outline icon"></i>
										<?php if ($order->status !== 'paid' && ! $order->payment_registred): ?>
										Register payment
										<?php else: ?>
										View payment
										<?php endif; ?>
									</a>
								</div>
								<i class="calculator icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id); ?>">
										<?php echo $order->customer; ?></a>
									<div class="description">
										<span class="ui small horizontal teal label"><?php echo ($order->status === 'paid') ? 'PAID' : 'NOT PAID'; ?></span>
							<?php break; 
								default: ?>
								<i class="lock icon"></i>
								<div class="content">
									<a class="header"
									   href="<?php echo base_url('/orders/view/'.$order->id); ?>">
										<?php echo $order->customer; ?></a>
									<div class="description">
										<span class="ui small horizontal teal label"><?php echo ($order->status === 'paid') ? 'PAID' : 'NOT PAID'; ?></span>
							<?php endswitch; ?>
									<span class="ui small horizontal green basic label">
										Zmk<span class="detail" style="margin-left: 0.3em;"><?php echo format_kwacha($order->total); ?></span>	
									</span> 
									<?php if(! empty($order->comments)): ?>
									<span class="ui small left pointing label comment"
										  data-title="For <?php echo $order->customer; ?>" 
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
	<!-- Example for collect -->
	<!--					<div class="item">
							<div class="right floated content">
								<div class="ui labeled icon button">
									<i class="shopping cart icon"></i>
									Modify order
								</div>
							</div>
							<i class="shopping basket icon"></i>
							<div class="content">
								<a class="header">John Doe</a>
								<div class="description">
									4 items, Total: 30.00
									<span class="ui tag small label comment"
										  data-title="From {contact_name}" 
										  data-content="Hello. This is a very wide pop-up which allows for lots of content with additional space. You can fit a lot of words here and the paragraphs will be pretty wide." 
										  data-variation="very wide basic"
										  style="margin-top: -3em;margin-left: 2em;">
										<i class="comment outline icon"></i>Comment
									</span>
								</div>
							</div>
						</div>-->
	<!-- Example for prepare -->
	<!--					<div class="item">
							<div class="right floated content">
								<div class="ui labeled icon button">
									<i class="print alternate icon"></i>
									Print invoice
								</div>
							</div>
							<i class="shipping icon"></i>
							<div class="content">
								<a class="header">Colin B.</a>
								<div class="description">[Not printed] Total: 60.00</div>
							</div>
						</div>-->
	<!-- Example for accounting -->
	<!--					<div class="item">
							<div class="right floated content">
								<div class="ui labeled icon button">
									<i class="credit card outline icon"></i>
									Register payment
								</div>
							</div>
							<i class="calculator icon"></i>
							<div class="content">
								<a class="header">Thomas M.</a>
								<div class="description">[NOT PAID] Total: 60.00</div>
							</div>
						</div>-->
	<!-- Example for closed -->
	<!--					<div class="item">
							<i class="lock icon"></i>
							<div class="content">
								<a class="header">Barthelemy</a>
								<div class="description">[PAID] Total: 89.00</div>
							</div>
						</div>-->
					</div>
				</div>
				<div class="ui bottom attached secondary segment">
					<p><?php echo count($orders).' customer(s), Total: '.sprintf('%01.2f', $big_total); ?> kwatcha</p>
				</div>
			</div>
		</div>
	</div>
</div>
<!--	Folowing code for modal and other javascripts -->
<div class="ui tiny modal expert">
	<i class="close icon"></i>
	<div class="ui header">
		<i class="question circle icon" style="display:inline-block;"></i>
		<span>Please confirm the action</span>
	</div>
	<div class="content">
		<p>confirm message</p>
	</div>
	<div class="actions">
		<div class="ui red cancel inverted button">
			<i class="cancel icon"></i>
			No
		</div>
		<div class="ui green approve button">
			<i class="check icon"></i>
			Yes
		</div>
	</div>
</div>