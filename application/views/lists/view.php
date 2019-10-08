<script>
	$(document)
	.ready(function() {
		var 
			$modalRemove 			= $('.remove.ui.modal'),
			$modalAdd				= $('.add.ui.modal'),
			$deleteLink				= null
			$addCustomersBaseUrl 	= "<?php echo base_url('/lists/add_customers/'.$list->id.'/'); ?>";
		;

		$modalRemove.modal({
				closable  : true,
				onApprove : function($element)
				{
					location.assign($deleteLink);
				}
			});
		$modalAdd.modal({
				closable  : false,
				onApprove : function($element)
				{
					select = $(this).find('select[name="customers[]"]')[0];
					nb_customer = select.selectedOptions.length;
					$ids= Array();
					for (i = 0; i < nb_customer; i++)
					{
						$ids.push(select.selectedOptions[i].value);
					}
					if ($ids.length > 0)
					{
						location.assign($addCustomersBaseUrl + encodeURIComponent(JSON.stringify($ids)));
					}
				}
			});
		
		$('select.dropdown')
			.dropdown()
		;

		$('table .button.delete')
			.on('click', function() 
			{ // custom action 
				var name = $(this).data('name');
				$deleteLink = this.href;
				$($modalRemove[0]).find('.name').text(name);
				$modalRemove.modal('show');
				return false;
			})
		;
		
		$('.customers .button.add')
			.on('click', function() 
			{ // custom action 
				$('.add.ui.modal .dropdown').dropdown('clear');
				$modalAdd.modal('show');
				return false;
			})
		;
		
		if ($(location.hash)[0] !== undefined)
		{
			$(location.hash).addClass('active');
			$(location.hash).next('.content').addClass('active');
			$(location.hash)[0].scrollIntoView(true);
		}
		
		$('.ui.segments').mouseenter(function(){ $(this).addClass('raised'); });
		$('.ui.segments').mouseleave(function(){ $(this).removeClass('raised'); });
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
		<div class="active section"><?php echo $list->name; ?></div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>

	<div class="ui column">
			<a class="ui right labeled basic icon button" 
			   href="<?php echo base_url('/lists/edit/'.$list->id); ?>">
				Edit details
				<i class="edit outline icon"></i>
			</a>
	</div>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header title active" id="open_deliveries"><i class="dropdown icon"></i>Open deliveries</h2>
		<div class="ui content active">
			<div class="ui right labeled icon button" style="visibility:hidden;">TRICKY SPACER FOR RIGHT FLOATING BUTTON</div>
			<a class="ui right floated primary button"
			   href="<?php echo base_url('/deliveries/create/'.$list->id); ?>">
				New delivery
				<i class="right plus icon"></i>
			</a>
			<?php echo $deliveries_open; ?>
		</div>
	</div>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header title" id="customers"><i class="dropdown icon"></i>Customers</h2>
		<div class="customers ui column content text container">
			<!--<div class="ui primary basic buttons right floated">-->
			<div class="ui right labeled icon button" style="visibility:hidden;">TRICKY SPACER FOR RIGHT FLOATING BUTTON</div>
			<a class="add ui right labeled icon primary right floated button" href="<?php echo base_url('/lists/edit/'.$list->id); ?>">
				Add<i class="user plus icon"></i>
			</a>
			<!--</div>-->

			<table class="ui selectable celled table">
				<thead>
					<tr>
						<th class="">Name</th>
						<th class="one wide">Unpaids</th>
						<th class="one wide">Balance</th>
						<th class="one wide"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($customers as $customer): ?>
					<tr class="">
						<td class="ui selectable">
							<a href="<?php echo base_url('customers/edit/'.$customer->id.'?rdtfrom='.uri_string()); ?>">
								<?php echo ucfirst($customer->name); ?>
							</a>
						</td>
						<td class="ui"><?php echo format_kwacha($customer->total_unpaid); ?></td>
						<td class="ui"><?php echo format_kwacha($customer->current_balance); ?></td>
						<td>
							<a href="<?php echo base_url('lists/remove_customer/'.$list->id.'/'.$customer->id); ?>"
							   data-name="<?php echo $customer->name; ?>"
							   class="delete ui icon negative basic compact button">
								<i class="icons">
								<i class="user outline icon"></i>
								<i class="times corner icon"></i>
									</i>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr><th colspan="5">
						<?php echo $c_pagination ?>
					</th></tr>
				</tfoot>
			</table>
		</div>
	</div>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header title" id="vegetables"><i class="dropdown icon"></i>Vegetables</h2>
		<div class="vegetables ui content text container">
			<div class="ui right labeled icon button" style="visibility:hidden;">TRICKY SPACER FOR RIGHT FLOATING BUTTON</div>
			<a class="edit_vege ui icon right labeled primary button right floated" href="<?php echo base_url('/lists/edit_vegetables/'.$list->id); ?>">
				Edit the list 
				<i class="lemon  icon"></i>
			</a>

			<table class="ui selectable celled table">
				<thead>
					<tr>
						<th class="">Name</th>
						<th class="one wide">Price</th>
						<th class="one wide">Unit</th>
						<th class="one wide"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($vegetables as $veget): ?>
					<tr class="<?php if($veget->accounting_cat !== NULL && $veget->accounting_cat !== 'veg'){echo 'highlight';} ?>">
						<td class="ui"><?php echo ucfirst($veget->name); ?></td>
						<td class="ui right aligned"><?php echo sprintf('%01.2f', $veget->price); ?></td>
						<td><?php echo element(strtolower($veget->unit), $units); ?></td>

						<td>
							<a href="<?php echo base_url('lists/remove_vegetable/'.$list->id.'/'.$veget->id); ?>" 
							   data-name="<?php echo $veget->name; ?>"
							   class="delete ui icon negative basic compact button">
								<i class="icons">
									<i class="lemon outline icon"></i>
									<i class="times corner icon"></i>
								</i>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr><th colspan="4">
						<?php echo $v_pagination ?>
					</th></tr>
				</tfoot>
			</table>
		</div>
	</div>
		
	<div class="ui column">
		<h2 class="ui horizontal divider header title" id="prev_deliveries"><i class="dropdown icon"></i>Previous deliveries</h2>
		<div class="ui content">
			<?php echo $deliveries_closed; ?>
			<?php if ($show_more != -1): ?>
			<div class="ui basic segment">
				<a href="<?php echo base_url(uri_string().'?more='.$show_more); ?>#prev_deliveries">
					<i class="angle double down icon"></i>Show more
				</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<!--	Folowing code for modal and other javascripts -->
<div class="remove ui tiny modal delete">
	<i class="close icon"></i>
	<div class="ui header">
		<i class="trash alternate icon" style="display:inline-block;"></i>
		Remove <span class="name">{name}</span>
	</div>
	<div class="content">
		<p>Would you like to remove <strong class="name">{name}</strong> from the list ?</p>
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
<div class="add ui tiny modal delete">
	<i class="close icon"></i>
	<div class="ui header">
		<i class="user plus icon"></i>
		Add customers to the list
	</div>
	<div class="content">
		<div class="field">
			<label>Select one or more customers</label>
			<?php echo form_multiselect('customers[]', $add_customers_list, array(), 'class="ui fluid search dropdown" multiple=""'); ?>
		</div>
	</div>
	<div class="actions">
		<div class="ui red cancel inverted button">
			<i class="cancel icon"></i>
			Cancel
		</div>
		<div class="ui green approve button">
			<i class="plus icon"></i>
			Add
		</div>
	</div>
</div>