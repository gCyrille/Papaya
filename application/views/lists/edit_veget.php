<script>
	$(document)
	.ready(function() {
		
		$('td.selectable')
		.on('click', function() 
			{ // custom action 
				$(this).parent().find('input[type=checkbox]')[0].checked = ! $(this).parent().find('input[type=checkbox]')[0].checked;
				return false;
			})
		;
		$('.ui.toggle.checkbox').checkbox('attach events', '.toggle.button');
		$('.ui.toggle.checkbox').checkbox('attach events', '.check.button', 'check');
		$('.ui.toggle.checkbox').checkbox('attach events', '.uncheck.button', 'uncheck');
		
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
		   href="<?php echo base_url('lists/view/'.$list->id); ?>">
			<?php echo $list->name; ?>
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section">Vegetables</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>

	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo base_url('/lists/view/'.$list->id); ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<h2 class="ui horizontal divider header" id="vegetables">Vegetables</h2>
	<div class="vegetables ui column">

		<?php echo form_open($form_url, 'class="ui form column text container content active"'); ?>

		<table class="ui selectable celled table">
			<thead>
				<tr>
					<th colspan="4">   
						<button class="ui primary labeled icon button" type="submit">
							<i class="icon check"></i>
							Save
						</button>
						<div class="ui mini basic buttons right floated">
							<div class="ui check button">Select all</div>
							<div class="ui uncheck button">Unselect all</div>
							<div class="ui toggle button">Invert selection</div>
							<button type="reset" class="ui button">Reset</button>
						</div>
					</th>
				</tr>
				<tr>
					<th class="">Name</th>
					<th class="one wide">Price</th>
					<th class="one wide">Unit</th>
					<th class="collapsing">       

					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($vegetables_all as $veget): ?>
				<tr class="<?php if($veget->accounting_cat !== NULL && $veget->accounting_cat !== 'veg'){echo 'highlight';} ?>">
					<td class="ui selectable"><a href="#"><?php echo ucfirst($veget->name); ?></a></td>
					<td class="ui right aligned"><?php echo sprintf('%01.2f', $veget->price); ?></td>
					<td><?php echo element(strtolower($veget->unit), $units); ?></td>
					<td class="">
						<div class="ui toggle checkbox">
							<?php echo form_checkbox('vegetables[]', $veget->id, in_array($veget->id, $vegetables_ids)); ?>
							<label></label>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>					
					<th colspan="4">  
						<button class="ui primary labeled icon button" type="submit">
							<i class="icon check"></i>
							Save
						</button>     
						<div class="ui mini basic buttons right floated">
							<div class="ui check button">Select all</div>
							<div class="ui uncheck button">Unselect all</div>
							<div class="ui toggle button">Invert selection</div>
							<button type="reset" class="ui button">Reset</button>
						</div>
					</th>
				</tr>
			</tfoot>
		</table>
		</form>
	</div>
		
</div>
