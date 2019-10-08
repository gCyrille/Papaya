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
				},
				price: {
					identifier  : 'price',
					rules: [
						{
							type	: 'empty',
							prompt	: 'The {name} field is required.'
						},
						{
							type	: 'number'
						}
					]
				},
				unit: {
					identifier  : 'unit',
					rules: [
						{
							type	: 'empty',
							prompt	: 'The {name} field is required.'
						},
						{
							type	: 'not[0]',
							prompt	: 'The {name} field is required.'
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
		var 
			$vegeModal 			= $('.ui.modal'),
			$closeModal		= $('.ui.modal.close'),
			$oldFields 	= $('form').form('get values')
		;
  	
		$vegeModal.modal({
			closable  : true,
			onApprove : function()
			{
				location.assign('<?php echo base_url('/vegetables/delete/'.$veget['id']); ?>');
			}
		});
		
		$('.ui.form a.basic.negative')
			.on('click', function() 
			{ // custom action 
					var vege_name = '<?php echo $veget['name']; ?>';
					$($vegeModal[0]).find('.vege_name').text(vege_name);
					$vegeModal.modal('show');
					return false;
			})
		;

		$closeModal.modal({
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
		
		$('.ui.modal.close .closem.button').click(function(){ $closeModal.modal('hide'); })
		
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
				$closeModal.modal('show');
				return false;
			}
		})
		;
	})
	;
</script>

<div class="ui grid container" id="vegetList">

	<div class="ui huge breadcrumb header sixteen wide column">
		<a class="section" 
		   href="<?php echo base_url('vegetables/list/'); ?>">
			Vegetables
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $title; ?></div>
	</div>
	
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon close button" href="<?php echo $back_link; ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<div class="ui one column row">
	<?php echo form_open($form_url, 'class="ui form column text container segment"'); ?>
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
		
		<h3 class="ui dividing header">Details</h3>
		
		<div class="field <?php if(form_error('name')) echo 'error';?>">
			<label>Name</label>
			<input name="name" placeholder="Name" type="text" value="<?php echo set_value('name', $veget['name']); ?>">
		</div>
		<div class="field  <?php if(form_error('price')) echo 'error';?>">
			<label>Price</label>
			<input name="price" 
				   placeholder="0.00" 
				   type="number" 
				   value="<?php echo sprintf("%01.2f", set_value('price', $veget['price'])); ?>"
				   min="0"
				   step="0.25">
		</div>
		<div class="field">
			<label>Unit</label>
			<?php echo form_dropdown('unit', $units, strtolower(set_value('unit', $veget['unit'])), 'class="ui search dropdown"'); ?>
		</div>
		
		<h3 class="ui dividing header">Accounting</h3>
		
		<div class="field">
			<label>Select a category</label>
			<?php echo form_dropdown('accounting_cat', $accounting_cats, strtolower(set_value('accounting_cat', $veget['accounting_cat'])), 'class="ui search dropdown"'); ?>
		</div>
		
		<h3 class="ui dividing header">Available for</h3>
			
		<div class="field  <?php if(form_error('lists')) echo 'error';?>">
			<label>Select one or more delivery lists</label>
			<?php echo form_multiselect('lists[]', $delivery_lists, set_value('lists[]', $lists), 'class="ui fluid search dropdown" multiple=""'); ?>
		</div>
		
		<button class="ui primary labeled icon button" type="submit">
			<i class="icon save"></i>
			<?php echo $submit_btn; ?>
		</button>
		<?php if ($is_editing): ?>
		<a href="<?php echo base_url('/vegetables/delete/'.$veget['id']); ?>" class="ui icon basic negative button right floated ">
			<i class="right trash alternate outline icon"></i>
			Delete
		</a>
		<?php else: ?>
		<div class="ui checkbox">
			<input tabindex="0" class="hidden" type="checkbox" name="new_after">
	  		<label>After this, create a new</label>
		</div>
		
		<?php endif; ?>
	</form>
		
	</div>
<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal delete">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="trash alternate icon" style="display:inline-block;"></i>
			Remove <span class="vege_name">{vegetable}</span>
		</div>
		<div class="content">
			<p>Would you like to remove <strong class="vege_name">{vegetable}</strong> from the vegetable list?</p>
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
</div>