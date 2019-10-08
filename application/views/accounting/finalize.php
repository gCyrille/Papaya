<script>
	$(document)
	.ready(function() {
		$('.ui.form')
			.form({
				fields: {
					cash: {
						rules: [
							{
								type	: 'empty',
								prompt	: 'Please enter the remaining cash'
							},
							{
								type	: 'decimal'
							}
						]
					},
					change: {
						rules: [
							{
								type	: 'empty',
								prompt	: 'Please enter the initial change'
							},
							{
								type	: 'decimal'
							}
						]
					}
					<?php foreach($expenses_cats as $cat_code => $cat_title): ?>
					,
					<?php echo $cat_code; ?>: {
						optional: true,
						identifier: 'expenses[<?php echo $cat_code; ?>]',
						rules: [
							{
								type	: 'decimal'
							}
						]
					}
					<?php endforeach; ?>
				}
		})
		;
		$('select.dropdown')
			.dropdown()
		;
		$('.ui.checkbox')
  			.checkbox()
		;
		
		var $inputs = $('form input:not([readonly])');
		var $result = $('form input[name="result"]');
		var $cash = $('form input[name="cash"]');
		var $received = Number($('form input[name="received"]').val());
		var update_count = function()
		{
			var diff = 0;
			$inputs.each(function (index) {
				if(this.name != "change")
				{
					diff += Number(this.value);
				} else {
					diff -= Number(this.value);
				}
			})
			;
			//$extra.val(over);
			$result.val(diff - $received);
		};
		
		$inputs.on('input', function( event ) {
			update_count();
		})
		;
		
		update_count();
		
		// Modal
		var 
			$modal_notes 	= $('.ui.modal.notes'),
			$inputs_modal 	= $('.ui.modal input:not([readonly])'),
			$results_modal 	= $('.ui.modal input[readonly]:not([name=result])'),
			$total_modal	= $('.ui.modal input[name=result]')
		;
		
		$modal_notes.modal({
			closable  : true,
			centered: true,
			onApprove : function($element)
			{
				//TODO
				$cash.val($total_modal.val());
			}
		});
		
		$('#count_notes_btn')
			.on('click', function() 
			{ // custom action 
					$modal_notes.modal('show');
					return false;
			})
		;
		$('.ui.modal .ui.form')
			.form({
			fields: {
				cash_100 : 'number'
			},
    		inline : true,
			on     : 'blur'
		})
		;
		$inputs_modal.on('input', function( event ) {
			$total = 0;
			$inputs_modal.each(function (index) {
				val = 0;
				if(this.name == 'cheque' || this.name == 'transfer')
				{
					val = Number(this.value);
				}
				else
				{
					val = Number(this.value) * Number($(this).data('value'));
				}
				
				$total += val;
				$results_modal[index].value = val;
			})
			;
			$total_modal.val($total);
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
	form .row {
		background-color: #FFF;
	}
	.modal .ui.form .field
	{
		margin-bottom: 0.3em !important;
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
		<div class="active section">Finalize accounting</div>
	</div>
	
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo base_url('/deliveries/view/'.$delivery->id); ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header">Accounting</h2>

		<?php echo form_open($form_url, 'class="ui form text grid container segment"'); ?>
			
			<div class="ui row">
				<div class="ui column">
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
				</div>
			</div>
			<div class="ui row">
				<div class="two wide column">
				</div>
				<div class="ten wide right aligned column">
					<div class="inline field">
						<label>Payments received:</label>
						<div class="ui transparent left incon input">
							<i class="icon"></i>
							<input name="received" style="text-align:right;" value="<?php echo $total_received; ?>" type="text" readonly size="6">
						</div>
					</div>
					<div class="inline required field <?php echo(form_error('cash') ? 'error' : ''); ?>">
						<label>Cash and cheques</label>
						<div class="ui action input">
							<input name="cash" 
								   style="text-align:right;" 
								   value="<?php echo set_value('cash'); ?>" 
								   placeholder="0.00" 
								   autocomplete="off"
								   type="text" size="4">
							<button class="ui icon basic button" id="count_notes_btn">
								<i class="calculator icon"></i>
							</button>
						</div>
					</div>
					<?php foreach($expenses_cats as $cat_code => $cat_title): ?>
					<div class="inline field <?php echo(form_error('expenses['.$cat_code.']') ? 'error' : ''); ?>">
						<label><?php echo $cat_title; ?></label>
						<div class="ui left icon input">
							<i class="plus small icon"></i>
							<input name="expenses[<?php echo $cat_code; ?>]" 
								   style="text-align:right;" 
								   value="<?php echo set_value('expenses['.$cat_code.']'); ?>" 
								   placeholder="0.00" 
								   autocomplete="off"
								   type="text" size="6">
						</div>
					</div>
					<?php endforeach; ?>
					<div class="inline required field <?php echo(form_error('change') ? 'error' : ''); ?>">
						<label>Change given for delivery</label>
						<div class="ui left icon input">
							<i class="minus small icon"></i>
							<input name="change" 
								   style="text-align:right;" 
								   value="<?php echo set_value('change'); ?>" 
								   placeholder="0.00" 
								   autocomplete="off"
								   type="text" size="6">
						</div>
					</div>
					<div class="ui divider"></div>
					<div class="inline field">
						<label>Result:</label>
						<div class="ui transparent left incon input">
							<i class="icon"></i>
							<input name="result" 
								   style="text-align:right;" 
								   value="<?php echo $total_received; ?>" 
								   type="text" 
								   readonly size="6">
						</div>
					</div>
				</div>
			</div>
		<div class="row">
			<div class="right aligned column">
				<button class="ui primary labeled icon button right floated" type="submit">
					<i class="right chevron icon"></i>
					Next
				</button>
			</div>
		</div>

		</form>
	</div>
</div>
		
<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal notes">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="calculator icon"></i>
			Count notes</div>
		<div class="content">
			<div class="ui form grid">
				<div class="row">
					<div class="ten wide right aligned column">
						<div class="inline field">
							<label>100.00 x</label>
							<input name="cash_100" placeholder="0" type="text" size="6" data-value="100">
						</div>
						<div class="inline field">
							<label>50.00 x</label>
							<input placeholder="0" type="text" size="6" data-value="50">
						</div>
						<div class="inline field">
							<label>20.00 x</label>
							<input placeholder="0" type="text" size="6" data-value="20">
						</div>
						<div class="inline field">
							<label>10.00 x</label>
							<input placeholder="0" type="text" size="6" data-value="10">
						</div>
						<div class="inline field">
							<label>5.00 x</label>
							<input placeholder="0" type="text" size="6" data-value="5">
						</div>
						<div class="inline field">
							<label>2.00 x</label>
							<input placeholder="0" type="text" size="6" data-value="2">
						</div>
						<div class="inline field">
							<label>1.00 x</label>
							<input placeholder="0" type="text" size="6" data-value="1">
						</div>
						<div class="inline field">
							<label>0.50 x</label>
							<input placeholder="0" type="text" size="6" data-value="0.5">
						</div>
						<div class="inline field">
							<label>Cheques</label>
							<input name="cheque" placeholder="0.00" type="text" min="0" step="0.5" size="6">
						</div>
						<div class="inline field">
							<label>Bank transfer</label>
							<input name="transfer" placeholder="0.00" type="text" min="0" step="0.5" size="6">
						</div>
					</div>
					<div class="six wide column">
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
						<div class="inline field">
							<div class="ui transparent left incon input">
								<input 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="fourteen wide right aligned column">
						<div class="inline field" style="border-top: 2px black solid;">
							<label>Total:</label>
							<div class="ui transparent left incon input">
								<input name="result" 
									   value="0" 
									   type="text" 
									   readonly size="4">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				Cancel
			</div>
			<div class="ui green approve button">
				<i class="check icon"></i>
				Ok
			</div>
		</div>
	</div>
