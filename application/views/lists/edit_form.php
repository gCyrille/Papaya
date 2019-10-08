<script>
	$(document)
	.ready(function() {
		$('.ui.form')
		.form({
			fields: {
				name: {
			  	identifier  : 'name',
				rules: [
					{
						type	: 'empty',
						prompt	: 'The {name} field is required.'
					},
					{
						type   : 'length[3]',
						prompt : 'The {name} must be at least {ruleValue} characters'
					}
				]
				}
			}
		})
		;
		$('select.dropdown')
			.dropdown()
		;
		$('.ui.checkbox')
  			.checkbox()
		;
	})
	;
</script>

<div class="ui grid container">

	<div class="ui huge breadcrumb header sixteen wide column">
		<a class="section" 
		   href="<?php echo base_url('lists'); ?>">
			Delivery lists
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $title; ?></div>
	</div>
		
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo $back_link; ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<div class="ui one column row">
	<?php echo form_open($form_url, 'class="ui form column text container segment"'); ?>
		
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
		
		<h3 class="ui dividing header">List details</h3>
		
		<div class="field <?php if(form_error('name')) echo 'error';?>">
			<label>Name</label>
			<input name="name" placeholder="Name" type="text" value="<?php echo set_value('name', $list['name']); ?>">
		</div>
		<div class="field  <?php if(form_error('day_of_week')) echo 'error';?>">
			<label>Day of the week</label>
			<?php echo form_dropdown('day_of_week', $days, set_value('day_of_week', $list['day_of_week']), 'class="ui search dropdown"'); ?>
		</div>
		
		<h3 class="ui dividing header">Customers</h3>
			
		<div class="field  <?php if(form_error('customers')) echo 'error';?>">
			<label>Select one or more customers</label>
			<?php echo form_multiselect('customers[]', $customers_list, set_value('customers[]', $customers), 'class="ui fluid search dropdown" multiple=""'); ?>
		</div>
			
		<button class="ui primary labeled icon button right floated " type="submit">
			<i class="icon save"></i>
			<?php echo $submit_btn; ?>
		</button>
		<?php if ($is_editing): ?>
		<a href="<?php echo base_url('/lists/delete/'.$list['id']); ?>" class="ui icon basic negative button ">
			<i class="right trash alternate outline icon"></i>
			Delete
		</a>		
		<?php endif; ?>
	</form>
		
	</div>

</div>