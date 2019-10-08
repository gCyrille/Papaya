<script>
	$(document)
	.ready(function() {
		$('select.dropdown')
			.dropdown()
		;
		$('.ui.checkbox')
  			.checkbox()
		;
		
		// Pay all balances switch
		$('.ui.checkbox:has(input[name="pay_all"])')
			.checkbox({
				onChecked: function() {
					$('.column.payment .field').addClass('disabled');
					$('.field.total-paid').removeClass('disabled');
				},
				onUnchecked: function() {
					$('.column.payment .field').removeClass('disabled');
					$('.field.total-paid').addClass('disabled');
				}
		})
		;
		
		var $balances = $('.column.payment input');
		var $total_paid = $('input[name="total_paid"]');
		var $extra = $('input[name="paid_extra"]');
		var $curr_balance = Number($extra.data('balance'));
		var $total_due = Number($total_paid.data('total-due'));
		
		// Update balances with total paid
		$total_paid.on('input', function( event ) {
			$this = $(this);

			var total_paid = Number($this.val());
			if ($curr_balance < 0)
			{ 
				// Adjust total paid by adding balance (negative = overpayment from last time)
				total_paid -= $curr_balance; // equals: "add the (negative) balance AS part of today payment"
			}

			var sub_paid = total_paid;
			var total_received = 0;
			$balances.each(function (index) {
				if(this.name != "paid_extra" && this.name != "paid_total")
				{
					var amount = $(this).data('amount');
					if (sub_paid >= amount)
					{
						this.value = amount;
					} else {
						this.value = sub_paid;
					}
					sub_paid -= Number(this.value);
					total_received += Number(this.value);
				} else if (this.name == "paid_extra") {
					if ($curr_balance < 0)
					{
						if (sub_paid > 0)
						{
							// If remains some money, it's over paid. 
							// The balance is already included in the total_paid
							// So to keep total received with a true value (corresponding to the cash), add the balance to this overpaid value
							this.value = sub_paid + $curr_balance;
						} else {							
							// Show the balance in the Extra
							this.value = $curr_balance;
						}
					} else {
						if (sub_paid > 0)
						{
							this.value = sub_paid ;
							//$new_bal = $curr_balance - $sub_paid;
						} else {
							this.value = 0;
						}
					}
					total_received += Number(this.value);
				} else {
					this.value = total_received;
				}
			})
			;
		})
		;
		
		$balances.on('input', function( event ) {
			
			var diff = Number($(this).data('amount')) - Number(this.value);
			var paid = 0;
			//var over = $extra.val();
//			if (this.name != "paid_extra") {
				$balances.each(function (index) {
					if(this.name != "paid_extra" && this.name != "paid_total")
					{
						var diff = Number($(this).data('amount')) - Number(this.value);
						if (diff >= 0)
						{
							//paiment less or equals to amount
							paid += Number(this.value);
						} else {
							//overpayment
							//over -= diff;
							this.value = $(this).data('amount');
						}
					} else if (this.name == "paid_extra"){
						paid += Number(this.value);
					} else {
						this.value = paid;
					}
				})
				;
				//$extra.val(over);
				$total_paid.val(paid);
//			}
		})
		;
		
		$('input') 
				.keydown(function( event ) {
					if ( event.key == 'Enter' ) { // cancel ENTER key to avoir submission errors
						event.preventDefault();
						event.stopImmediatePropagation();
					}
				})
			;
	})
	;
</script>
<style type="text/css">
	.ui.input input[readonly] {
		pointer-events: none;
	}
	.ui.input.transparent {
		padding: 0.67857143em 1em;
	}
/*
	form .row {
		background-color: #FFF;
	}
*/
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
	
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>

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
	
	<h2 class="ui horizontal divider header">Payment for <?php echo $order->customer; ?></h2>
	
	<div class="ui one column row">
	<?php echo form_open($form_url, 'class="ui form column text grid container segment"'); ?>
		
		<?php if (empty($err_msg = validation_errors('<li>', '</li>'))): ?>
			<div class="ui error message">
			</div>
			<?php else: ?>
			<div class="ui error message" style="display:block;">
				<ul class="list">
					<?php echo $err_msg; ?>
				</ul>
			</div>
		<?php endif; ?>
		<div class="four column row">
			<div class="eight wide column right aligned">
				<div class="field">
					<label>Total due with balance for this invoice</label>
					<div class="ui transparent input">
						<input style="text-align:right;" value="<?php echo $total_due; ?>" type="text" readonly>
					</div>
				</div>
			</div>
			<div class="column">
				<div class="field  <?php if(form_error('total_paid')) echo 'error';?> total-paid">
					<label>Total received</label>
					<div class="ui input">
						<input name="total_paid" placeholder="0.00" 
							   type="number" min="0" step="0.5"
							   value="<?php echo $total_paid; ?>"
							   data-total-due="<?php echo $total_due; ?>">
					</div>
				</div>
			</div>
			<div class="column bottom aligned">
				<div class="field">
					<div class="ui checked checkbox">
						<input name="pay_all" type="checkbox" checked autocomplete="off">
						<label>Include all balances</label>
					</div>
				</div>
			</div>
		</div>
		<div class="one column row">
			<div class="ui icon message">
				<i class="exclamation circle icon"></i>
				<div class="content">
					<p>The following amounts may not all written on the invoice but they are the current unpaid invoices and balances.</p>
					<p><strong>The 'Extra' value IS NOT the new balance.</strong> The new balance will be adjusted according to the received amount, the total due and the current balance.</p>
				</div>
			</div>
		</div>
		<div class="four column row">
			<div class="two wide column">
			</div>
			<div class="six wide right aligned column">
				<div class="inline field">
					<label>Invoice date</label>
					<label>&emsp;</label>
					<label>Amount due</label>
				</div>
				<?php foreach($to_pay as $row): ?>
				<div class="inline field">
					<label><?php echo $row->date; ?></label>
					<div class="ui transparent input">
						<input style="text-align:right;" value="<?php echo $row->total; ?>" type="text" readonly size="6">
					</div>
				</div>
				<?php endforeach; ?>
				<div class="inline field">
					<label>Extra</label>
					<div class="ui transparent input">
						<input style="text-align:right;" value="<?php echo $balance; ?>" type="text" readonly size="6">
					</div>
				</div>
				<div class="inline field">
					<label>Totals</label>
					<div class="ui transparent input">
						<input style="text-align:right;" value="<?php echo $total_due; ?>" type="text" readonly size="6">
					</div>
				</div>
			</div>
			<div class="column payment">
				<div class="inline field disabled">
					<label>Money received</label>
				</div>
				<?php foreach($to_pay as $i => $row): ?>
				<div class="field disabled">
					<input name="pay[<?php echo $row->id;?>]" placeholder="0.00" 
						   type="number" min="0" step="0.5"
						   value="<?php echo $payments[$row->id]; ?>"
						   data-amount="<?php echo $row->total; ?>" max="<?php echo $row->total; ?>">
				</div>
				<?php endforeach; ?>
				<div class="field disabled">
					<input name="paid_extra" placeholder="0.00" 
						   type="number"  step="0.5"
						   value="<?php echo $payments['extra']; ?>"
						   data-balance="<?php echo $balance; ?>">
					<!--min="0"-->
				</div>
				<div class="field disabled">
					<div class="ui transparent input">
						<input name="paid_total" placeholder="0.00"
							   value="<?php echo $total_paid; ?>">
					<!--min="0"-->
					</div>
				</div>
			</div>
		</div>

		<div class="ui hidden divider"></div>
		<?php if(isset($cancel_url)): ?>
		<a class="ui negative button"
		   href="<?php echo $cancel_url; ?>">
			<i class="cancel icon"></i>
			Cancel
		</a>
		<?php endif; ?>
		<button class="ui primary labeled icon button right floated" type="submit">
			<i class="icon save"></i>
			Save
		</button>
	</form>
		
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
