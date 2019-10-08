<script>
	$(document)
	.ready(function() {
		var $price_qtt = [];
		
		// Compute the sum of an array
		const reducer = (accumulator, currentValue) => accumulator + currentValue;
		
		// Function to update the price from the qtt input form
		var update_qtt = function($this) {
			var qtt = Number($this.val());
			var price = Number($this.data('price'));
			var id = $this.data('id');
			var target = $('input[data-target="'+id+'"]')[0];
			var value = qtt * price;
			$price_qtt[id] = value;
			target.value = value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
			$('input#total')[0].value = $price_qtt.reduce(reducer).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
		};
		
		var update_all_qtt = function() {
			$('tbody input:not([readonly])') // When document loaded, update total
				.each(function (index) {
					if (this.value == 0) {
						this.value = '';
					}
					update_qtt($(this));
				})
			;	
		};
		
		var set_listerners = function() {
			$('tbody input') 
				.keydown(function( event ) {
					if ( event.key == 'Enter' ) { // cancel ENTER key to avoir submission errors
						event.preventDefault();
						event.stopImmediatePropagation();
						$(this).parentsUntil('tbody').last().next().find('input:not([readonly])').focus();
					} else if (event.key == 'ArrowDown') { // Give focus to next line
						event.preventDefault();
						$(this).parentsUntil('tbody').last().next('tr:visible').find('input:not([readonly])').focus();
					} else if (event.key == 'ArrowUp') { // Give focus to previous line
						event.preventDefault();
						$(this).parentsUntil('tbody').last().prev('tr:visible').find('input:not([readonly])').focus();
					}
				})
			;
			// Highlight lines according to focus :
			$('tbody input:not([readonly])') 
				.focusin(function(event) {
					$(this).parentsUntil('tbody').last().addClass('active');
			});
			$('tbody input:not([readonly])')
				.focusout(function(event) {
					$(this).parentsUntil('tbody').last().removeClass('active');
			});

			$('tbody input:not([readonly])') // Update total when qtt changes
				.on('input', function( event ) {
					//$this = $(this);
					update_qtt($(this));
				})
			;
		};
		
		$('select.dropdown')
			.dropdown('set selected', '<?php echo set_value('customer', $customer_id); ?>')
		;
		$('.button[type="reset"]')
			.on('click', function(event) {
				$('.ui.dropdown')
					.dropdown('restore defaults');
				$('form')[0].reset();
				update_all_qtt();
				event.preventDefault();
			})
		;

		set_listerners();
		
		$('.hideshow.checkbox')
  			.checkbox({
				onChecked: function() {
			  		$('tbody input:not([readonly])').each(function(index){
				  		if ($(this).val() == 0)
					  		$(this).parentsUntil('tbody').last().fadeOut(200);
				  	});
//					$(this).next().text('Hide empty')
				},
				onUnchecked: function() {
				  	$('tbody input:not([readonly])').each(function(index){
						if ($(this).val() == 0)
							$(this).parentsUntil('tbody').last().fadeIn(200);
				  	});
//					$(this).next().text('Show empty')
				}
			})
		;
		
		// From control
		$('.ui.form')
		.form({
			fields: {
				name: {
			  	identifier  : 'customer',
				rules: [
					{
						type	: 'empty',
						prompt	: 'The {name} field is required.'
					}
				]
				}
			}
		})
		;
		
		// Modal for delete
		var 
			$modal_delete 	= $('.ui.modal.delete'),
			$modal_add		= $('.ui.modal.vege')
		;

		$modal_delete.modal({
			closable  : true,
			centered: true,
			onApprove : function($element)
			{
				location.assign($deleteLink);
			}
		});
		
		$modal_add.modal({
			closable  : true,
			centered: false,
			onApprove : null
		});

		$('.column.actions .button.delete')
			.on('click', function() 
			{ // custom action 
					var name = $(this).data('name');
					$deleteLink = this.href;
					$($modal_delete[0]).find('.name').text(name);
					$modal_delete.modal('show');
					return false;
			})
		;
		
		$('.button.add.vege')
			.on('click', function() 
			{ // custom action 
					$modal_add.modal('show');
					return false;
			})
		;
		
		$('.modal.vege .search')
		.search({
			apiSettings: {
      			url: '<?php echo base_url('vegetables/search/?q={query}'); ?>'
    		},
			fields: {
				url: '',
				action: ''
			},
			selector : {
				results: '.resultspanel'
			},
			transition: 'drop',
			onSelect: function(result, response)
			{
				// Hide and reset modal
				$modal_add.find('input.prompt')[0].value = '';
				$modal_add.search('hide results');
				$modal_add.search('cancel query');
				$modal_add.modal('hide');
				
				// Insert response
				row = $('tbody tr:last-child').clone();
				row.appendTo('tbody');
				row = $('tbody tr:last-child');
				row.addClass('error');
				row.find('td:first-child').text(result.title);
				row.find('td:nth-child(2)').text(result.price);
				row.find('td:nth-child(3)').text(result.description);
				input_qtt = row.find('td:nth-child(4) input')[0];
				input_qtt.value = null;
				input_qtt.defaultValue = null;
				input_qtt.dataset.id = result.id;
				input_qtt.name = "vegetables["+result.id+"]";
				input_qtt.dataset.price = result.price;
				input_tt = row.find('td:nth-child(5) input')[0];
				input_tt.dataset.target = result.id;
				input_tt.value = null;
				
				set_listerners();
				return false;
			}
		})
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
		
		// When document is loaded and everything ready
		update_all_qtt();
	})
	;
</script>
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
		<a class="section" 
		   href="<?php echo base_url('deliveries/view/'.$delivery->id); ?>">
			<?php echo mysql_to_suff_date($delivery->delivery_date); ?>
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $breadcrumb_title; ?></div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>

	<div class="ui column actions">
		<div class="ui basic buttons">
			<a class="ui left labeled icon close button" href="<?php echo base_url($this->redirect->build_url('/deliveries/view/'.$delivery->id)); ?>">
				<i class="left caret icon"></i>
				Back
			</a>
		</div>
		
		<?php if ($is_editing): ?>
		<a href="<?php echo base_url('orders/delete/'.$order_id); ?>" 
		   data-name="<?php echo $customer_name; ?>"
		   class="ui icon basic negative button right floated delete">
			<i class="trash alternate outline icon"></i>
			Delete this order
		</a>
		<?php endif; ?>
		
		<button class="ui primary labeled icon right floated button" 
				type="submit"
				form="order_form">
			<i class="check icon"></i>
			Save order
		</button>
	</div>
	
	<div class="ui one column row">
		<?php echo form_open_multipart($form_url, 'class="ui form column text container" id="order_form"'); ?>
		
			<?php echo $this->redirect->from_field(); ?>

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
   
			<h3 class="ui dividing header">Customer<div class="sub header">For who is it ?</div></h3>

			<div class="field  <?php if(form_error('customers')) echo 'error';?>">
				<?php echo form_dropdown('customer', $customers_list, set_value('customer', $customer_id), 'class="ui fluid search selection dropdown"'); ?>
			</div>

			<h4 class="ui dividing header">Comments</h4>
			<div class="field">
				<textarea rows="3" 
						  maxlength="255"
						  placeholder="Add comments if needed"
						  name="comments"
						  spellcheck="true"><?php echo set_value('comments', $comments); ?></textarea>
			</div>
		
			<h3 class="ui dividing header" id="vegetables">Vegetable list</h3>
		
			<h4 class="ui dividing header">Import a list</h4>
			<div class="inline fields">
				<div class="ui input twelve wide field">
					<?php echo form_upload('import_list', '', 'accept=".xls, .xlsx"'); ?>
				</div>
				<div class="four wide field">
					<input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
					<input type="hidden" name="delivery_id" value="<?php echo $delivery->id; ?>" />
					<button class="ui fluid button left icon" 
							formaction="<?php echo base_url('/orders/upload_list/'); ?>" 
							name="upload_list">
						<i class="download icon"></i>
						Import
					</button>
				</div>
				<?php if ($is_editing): ?>
				<div class="field">
					<div class="ui checkbox">
						<input name="replace" id="import_replace" type="checkbox" autocomplete="off" checked="">
						<label for="import_replace">Replace the list</label>
					</div>
				</div>
				<?php endif; ?>
			</div>
		
			<h4 class="ui dividing header">Or edit the list</h4>
			<div class="vegetables field">
				<table class="ui small selectable celled table">
					<thead>
						<tr>
							<th colspan="5">   
								<button class="ui primary labeled icon button" type="submit">
									<i class="check icon"></i>
									Save order
								</button>
								<div class="ui mini basic buttons right floated">
									<div class="ui slider checkbox button hideshow">
										<input name="public" type="checkbox" autocomplete="off">
										<label>Hide zeros</label>
									</div>
									<button type="reset" class="ui button">Reset</button>
								</div>
							</th>
						</tr>
						<tr>
							<th class="eight wide">Description</th>
							<th class="two wide">Price</th>
							<th class="one wide">Unit</th>
							<th class="tree wide">Order</th>
							<th class="one wide">Kwacha</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($vegetables_all as $veget): ?>
						<tr class="<?php
						   if(isset($veget->not_from_list) AND $veget->not_from_list === TRUE)
						   {
							   echo 'error';
						   }
						   elseif($veget->accounting_cat !== NULL && $veget->accounting_cat !== 'veg')
						   {
							   echo 'highlight';
						   } ?>">
							<td ><?php echo ucfirst($veget->name); ?></td>
							<td><?php echo sprintf("%01.2f", $veget->price); ?></td>
							<td><?php echo element(strtolower($veget->unit), $units); ?></td>
							<td class="">
								<div class="ui fluid input">
									<input type="number"
										   class="right aligned"
										   name="<?php echo 'vegetables['.$veget->id.']';?>"
										   placeholder="0.0"
										   min="0.0"
										   step="0.25"
										   value="<?php echo set_value('vegetables['.$veget->id.']', $vegetables[$veget->id]); ?>"
										   data-price="<?php echo $veget->price; ?>"
										   data-id="<?php echo $veget->id; ?>"/>
								</div>
							</td>
							<td>
								<div class="ui fluid transparent input">
									<input placeholder="0.00" 
										   type="text"
										   readonly=""
										   data-target="<?php echo $veget->id; ?>">
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="3">
							</th>
							<th colspan="1" class="right aligned">
								Total
							</th>
							<th colspan="2">
								<div class="ui fluid transparent input">
									<input placeholder="0.00" type="text" id="total">
								</div>
							</th>

						</tr>
						<tr>					
							<th colspan="3">  
								<button class="ui primary labeled icon button" type="submit">
									<i class="check icon"></i>
									Save order
								</button>    
								<button class="ui right floated small button add vege" type="button">
									Add a vegetable
									<i class="large icons">
										<i class="lemon outline icon"></i>
										<i class="plus corner icon"></i>
									</i>
								</button>
							</th>
							<th colspan="2">  
								<div class="ui mini basic buttons right floated">
									<button type="reset" class="ui button">Reset</button>
								</div>
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</form>
	</div>
		<div class="ui hidden divider"></div>
</div>

		
<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal delete">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="trash alternate icon"></i>
			Delete order</div>
		<div class="content">
			<p>Are you sure to delete this order for <strong class="name">{name}</strong> ?</p>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				No
			</div>
			<div class="ui green approve button">
				<i class="trash icon"></i>
				Yes
			</div>
		</div>
	</div>

	<div class="ui tiny modal vege">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="large icons">
				<i class="lemon outline icon"></i>
				<i class="plus corner icon"></i>
			</i>
			Add a vegetable</div>
		<div class="content">
			<p>Select a vegetable to add:</p>
			<div class="ui fluid search">
				<div class="ui icon fluid input">
					<input class="prompt" placeholder="Name..." type="text" autocomplete="off">
					<i class="search icon"></i>
				</div>
				<div class="resultspanel results"></div>
			</div>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				Cancel
			</div>
		</div>
	</div>
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