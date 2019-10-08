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
						prompt	: '{name} field is required.'
					},
					{
						type   : 'length[3]',
						prompt : 'The {name} must be at least {ruleValue} characters'
					}
				]
				},
				email: {
					identifier  : 'email',
        			optional   : true,
					rules: [
						{
							type	: 'email',
							prompt	: 'The {name} must be a valid e-mail'
						}
					]
				},
				email: {
					identifier  : 'email_2',
        			optional   : true,
					rules: [
						{
							type	: 'email',
							prompt	: 'The Email alternate must be a valid e-mail'
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
		
		<?php if ($is_editing): ?>
		var 
			$pass_modal = $('.ui.modal.password'),
			$btn_locker	= $('button.locker'),
			$input_bal = $('input[name=current_balance]')
		;
		
		$pass_modal.modal({
			closable  : true,
			onApprove : function()
			{
				pass = $(this).find('input[name=password]').val();
				
				if ("<?php echo $edit_password; ?>" == pass) //password is true
				{
					$('input[name="edit_balance"]')[0].checked = true;
					$('input[name="current_balance"]').parent().parent().removeClass('disabled');
					
					$btn_locker.children('i').removeClass('unlock alternate');
					$btn_locker.children('i').addClass('lock open');
					$btn_locker.removeClass('secondary');
					$('.err_pass').hide();
				}
				else
				{
					$('.err_pass').show();
				}
				$('.form.pass_form').form('clear')
			}
		});
		
		$btn_locker
			.on('click', function() 
			{ // custom action 
				if($('input.hidden')[0].checked == true)
				{
					$('input[name="edit_balance"]')[0].checked = false;
					$('input[name="current_balance"]').parent().parent().addClass('disabled');
					$btn_locker.children('i').addClass('unlock alternate');
					$btn_locker.children('i').removeClass('lock open');
					$btn_locker.addClass('secondary');
				}
				else
				{
					$pass_modal.modal('show');
				}
			})
		;
		
		if ($input_bal.val() < 0)
		{
			$input_bal.prev('.label').addClass('green');
		}
		else if ($input_bal.val() > 0)
		{
			$input_bal.prev('.label').addClass('red');
		}
		
		$('input[name="edit_balance"]')[0].checked = false;
		$('.err_pass').hide();
		
		var 
			$modal 			= $('.ui.modal.delete')
		;
  	
		$modal.modal({
			closable  : true,
			onApprove : function()
			{
				location.assign('<?php echo base_url('/customers/delete/'.$customer['id']); ?>');
			}
		});
		
		$('.ui.form a.basic.negative')
			.on('click', function() 
			{ // custom action 
					var name = '<?php echo $customer['name']; ?>';
					$($modal[0]).find('.name').text(name);
					$modal.modal('show');
					return false;
			})
		;
		<?php endif; ?>
	})
	;
</script>

	<?php echo form_open($form_url, 'class="ui form column text container segment"'); ?>
		<?php echo $this->redirect->from_field(); ?>
		<h3 class="ui dividing header">Contact</h3>
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
		<div class="two fields">
			<div class="field required ten wide <?php if(form_error('name')) echo 'error';?>">
				<label>Name</label>
				<input name="name" placeholder="Name" type="text" value="<?php echo set_value('name', $customer['name']); ?>">
			</div>		
			<div class="field">
				<label>Contact name</label>
				<input name="contact_name" placeholder="Contact name" type="text" value="<?php echo set_value('contact_name', $customer['contact_name']); ?>">
			</div>
		</div>
		<div class="two fields">
			<div class="field <?php if(form_error('email')) echo 'error';?>">
				<label>Email</label>
				<input name="email" placeholder="john@doe.com" type="email" value="<?php echo set_value('email', $customer['email']); ?>">
			</div>
			<div class="field">
				<label>Email alternate</label>
				<input name="email_2" placeholder="Optional" type="email" value="<?php echo set_value('email_2', $customer['email_2']); ?>">
			</div>
		</div>

		<h3 class="ui dividing header">Delivering</h3>
		<div class="grouped fields">
			<div class="field <?php if(form_error('delivery_place')) echo 'error';?>">
				<label>Delivery place</label>
				<textarea name="delivery_place" placeholder="First place to deliver" rows="2"><?php echo set_value('delivery_place', $customer['delivery_place']); ?></textarea>
			</div>
			<div class="field">
				<label>Delivery place alternate</label>
				<textarea name="delivery_place_2" rows="2" placeholder="Second place"><?php echo set_value('delivery_place_2', $customer['delivery_place_2']); ?></textarea>
			</div>
		</div>
		
		<h3 class="ui dividing header">Delivery lists</h3>
			
		<div class="field  <?php if(form_error('lists')) echo 'error';?>">
			<label>Select one or more delivery lists</label>
			<?php echo form_multiselect('lists[]', $delivery_lists, set_value('lists[]', $lists), 'class="ui fluid search dropdown" multiple=""'); ?>
		</div>

		<?php if ($is_editing): ?>

		<h3 class="ui dividing header">Accounting</h3>

		<div class="equal width fields">
			<div class="inline field">
				<label>Total unpaids</label>
				<div class="ui left labeled input">
					<div class="ui basic label">Zmk</div>
					<input type="text" 
						   value="<?php echo format_kwacha($total_unpaid); ?>"
						   autocomplete="off"
						   readonly />
				</div>
			</div>
			<div class="inline field disabled <?php if(form_error('current_balance')) echo 'error';?>">
				<label><span style="color: #DB2828 !important;">Balance</span> or <span style="color: #21BA45 !important;">Credit</span></label>
				<div class="ui left labeled input">
					<div class="ui basic label">Zmk</div>
					<input name="current_balance" 
						   placeholder="0.00" 
						   type="text"
						   autocomplete="off"
						   value="<?php echo format_kwacha(set_value('current_balance', $customer['current_balance'])); ?>">
				</div>
			</div>
			<button class="ui icon secondary button locker" type="button">
				<i class="unlock alternate icon"></i>
			</button>
			<input tabindex="0" class="hidden" type="checkbox" name="edit_balance" hidden="hidden"/>
			<div class="inline field">
				<div class="err_pass ui basic red pointing prompt transition label">Wrong password!</div>
			</div>
			
		</div>

		<a class="ui left labeled icon teal button download payment"
		  href="<?php echo base_url('accounting/download_payments/'.$customer['id']); ?>">
			<i class="excel file icon"></i>
			Download payment summary
		</a>

		<?php endif; ?>
			
		<h3 class="ui dividing header"></h3>
			
		<button class="ui primary labeled icon button" type="submit">
			<i class="icon save"></i>
			<?php echo $submit_btn; ?>
		</button>
		<?php if ($is_editing): ?>
		<a href="<?php echo base_url('/customers/delete/'.$customer['id']); ?>" class="ui icon basic negative button right floated">
			<i class="right trash alternate outline icon"></i>
			Delete
		</a>
		<button class="ui icon basic button button right floated" type="reset">
			<i class="undo alternate icon"></i>
			Reset
		</button>
		<?php else: ?>
		<div class="ui checkbox">
			<input tabindex="0" class="hidden" type="checkbox" name="new_after">
	  		<label>After this, create a new</label>
		</div>
		
		<?php endif; ?>
	</form>
		
<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal delete">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="trash alternate icon" style="display:inline-block;"></i>
			Delete <span class="name">{name}</span>
		</div>
		<div class="content">
			<p>Would you like to delete the customer "<strong class="name">{name}</strong>"?</p>
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
	<div class="ui mini modal password">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="lock alternate icon" style="display:inline-block;"></i>
			Unlock balance field
		</div>
		<div class="content ui form pass_form">
			<div class="field">
				<label>Please enter the password</label>
				<div class="ui input">
					<input name="password" type="password">
				</div>
			</div>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				Cancel
			</div>
			<div class="ui green approve button">
				<i class="lock open icon"></i>
				Unlock
			</div>
		</div>
	</div>
