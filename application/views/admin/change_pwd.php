<script>
	$(document)
	.ready(function() {
		$('.ui.form')
			.form({
				fields: {
					passold: {
						rules: [ {
							type	: 'empty',
							prompt	: 'The Old password field is required.'
						}]
					},
					password: {
						rules: [
							{
								type	: 'minLength[4]',
								prompt	: 'The New password field must be at least 4 characters in length.'
							},
							{
								type	: 'empty',
								prompt	: 'The New password field is required.'
							},
							{
								type	: 'regExp',
								value	: /^[a-zA-Z0-9]+$/,
								prompt	: 'The New password field may only contain alpha-numeric characters.'
							}
						]
					},
					passconf: {
						rules: [
							{
								type	: 'empty',
								prompt	: 'The Password Confirmation field is required.'
							}, 
							{
								type	: 'match',
								value	: 'password',
								prompt	: 'The Password Confirmation doesn\'t match the New password field.'
							}
						]
					}
				}
			})
		;
		
	})
	;
</script>

<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="lock icon"></i><?php echo $title; ?></h1>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<?php echo form_open(base_url('administration/change_password'), 'class="ui form column text container"');?>

		<?php if (empty($err_msg = validation_errors('<li>', '</li>'))): ?>
		<div class="column">
			<div class="ui error message"></div>
		</div>
		<?php else: ?>
		<div class="column">
			<div class="ui error message" style="display:block;">
				<ul class="list">
					<?php echo $err_msg; ?>
				</ul>
			</div>
		</div>
		<?php endif; ?>	
		<br />
		
		<div class="fields">
			<div class="field <?php echo(form_error('passold') ? 'error' : ''); ?>">
				<label>Old password</label>
				<input name="passold" type="password" value="<?php echo set_value('passold'); ?>">
			</div>
		</div>
		<div class="fields">
			<div class="field <?php echo(form_error('password') ? 'error' : ''); ?>">
				<label>New password</label>
				<input name="password" type="password" value="<?php echo set_value('password'); ?>">
			</div>
			<div class="field <?php echo(form_error('passconf') ? 'error' : ''); ?>">
				<label>Confirmation</label>
				<input name="passconf" type="password" value="<?php echo set_value('passconf'); ?>">
			</div>
		</div>
		<button class="ui primary button" type="submit">Change <i class="right arrow icon"></i></button>

	</form>	
</div>