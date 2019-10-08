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
		<a class="section" 
		   href="<?php echo base_url('lists/view/'.$list->id); ?>">
			<?php echo $list->name; ?>
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo  $title; ?></div>
	</div>
	
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo $back_link; ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<div class="ui hidden header divider"></div>
	
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
		
		<div class="field required <?php if(form_error('delivery_date')) echo 'error';?>">
			<label>Delivery date (dd/mm/yyyy)</label>
			<input name="delivery_date" placeholder="dd/mm/yyyy" type="date" value="<?php echo set_value('delivery_date', $delivery['delivery_date']); ?>">
		</div>
		
		<div class="ui hidden header divider"></div>
		<?php if ($is_editing): ?>
		<a href="<?php echo base_url('/deliveries/delete/'.$delivery['id']); ?>" class="ui icon basic negative button right">
			<i class="right trash alternate outline icon"></i>
			Delete
		</a>		
		<?php endif; ?>

		<button class="ui primary right labeled icon button right floated" type="submit">
			<i class="chevron right icon"></i>
			<?php echo $submit_btn; ?>
		</button>
	</form>
		
	</div>

</div>
