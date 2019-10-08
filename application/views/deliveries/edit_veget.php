<script>
	$(document)
	.ready(function() {
		$('select.dropdown')
			.dropdown()
		;
		$('.ui.checkbox')
  			.checkbox()
		;
		$('.button[type="reset"]')
			.on('click', function() {
				$('.ui.dropdown')
					.dropdown('restore defaults')
				;
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
		<a class="section" 
		   href="<?php echo base_url('deliveries/view/'.$delivery->id); ?>">
			<?php echo mysql_to_suff_date($delivery->delivery_date); ?>
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section">Vegetables</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>

	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo base_url('/deliveries/view/'.$delivery->id); ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<div class="vegetables ui column">
	<h2 class="ui horizontal divider header" id="vegetables">Vegetables</h2>

		<?php echo form_open($form_url, 'class="ui form column container"'); ?>

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
					<th class="two wide">Price</th>
					<th class="one wide">Unit</th>
					<th class="collapsing">       
						Available
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($vegetables_all as $veget): ?>
				<tr class="<?php if($veget->accounting_cat !== NULL && $veget->accounting_cat !== 'veg'){echo 'highlight';} ?>">
					<td ><?php echo ucfirst($veget->name); ?></td>
					<td>
						<div class="ui fluid input">
							<?php echo form_input('vegetables['.$veget->id.'][price]', sprintf("%01.2f", $vegetables_prices[$veget->id]), 'class="right aligned" placeholder="0.00" type="text"'); ?>
						</div>
					</td>
					<td>
						<div class="ui fluid input">
							<?php echo form_dropdown('vegetables['.$veget->id.'][unit]', $units, strtolower($vegetables_units[$veget->id]), 'class="ui search dropdown"'); ?>
						</div>
					</td>
					<td class="">
						<div class="ui toggle checkbox">
							<?php echo form_checkbox('vegetables['.$veget->id.'][available]', TRUE, in_array($veget->id, $vegetables_ids)); ?>
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
