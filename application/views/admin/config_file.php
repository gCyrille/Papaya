<script>
	$(document)
	.ready(function() {
		$('.ui.form')
			.form({
				fields: {}
			})
		;
		
		var $what = "<?php echo ucfirst($what); ?>";
		
		var add_rules_code = function()
		{
			$('input[data-validate=unitcode]')
				.each(function(i, e) {
					$('.ui.form').form('add rule', $(e).attr('name'), {
						rules: [
								{
									type	: 'empty',
									prompt	: 'Please enter a code'
								},
								{
									type	: 'regExp[/^[a-z0-9_-]+$/]',
									prompt	: 'The internal code can contain only numbers, small letters, dashs and underscores'
								}
							]
					});
				})
			;
		
		}
		
		var add_rules_name = function()
		{
			$('input[data-validate=unitname]')
				.each(function(i, e) {
					$('.ui.form').form('add rule', $(e).attr('name'), {
						rules: [
							{
								type	: 'empty',
								prompt	: 'Please enter a display name'
							},
							{
								type	: 'regExp[/^[ ,A-Za-z0-9_-]+$/]',
								prompt	: 'The display name can contain only numbers, small letters, spaces, dashs and underscores'
							}
						]
					});
				})
			;
		}
		
		var update_num = function(i, row)
		{
			row.find('label').text($what+' '+(i+1)+':');
			iname = row.find('input[data-validate=unitname]');
			icode = row.find('input[data-validate=unitcode]');
			iname.attr('name', 'units['+i+'][name]');
			icode.attr('name', 'units['+i+'][code]');

			del = row.find('.button.delete');
			del[0].dataset['num'] = i;
		}
		
		var reg_delete = function(e)
		{
			row = $(e.currentTarget).parentsUntil('.ui.form').last();
			row.remove();
			$('.row.unit')
			.each(function(i, e) {
				update_num(i, $(e));
			});
		}
		
		add_rules_code();
		add_rules_name();
		
		$('.button.delete')
			.click(reg_delete);
		
		$('.button.add')
		.click(function(){
			rows = $('.row.unit');
			last = rows.last();
			row = last.clone();
			update_num(rows.length, row);
			row.find('input').val(null);
			row.insertAfter(last);

			del = row.find('.button.delete');
			del.click(reg_delete);
			
			//Reset form validation
			$('.ui.form')
				.form({
					fields: {}
				})
			;
			add_rules_name();
			add_rules_code();
		})
		;
		
		
	})
	;
</script>

<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="edit icon"></i><?php echo $title; ?></h1>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<div class="ui column content text container center aligned">
		<h4 class="ui dividing header">Edit existing lines or add a new line:</h4>
		
		
		<?php echo form_open($form_url, 'class="ui form centered padded grid"');?>
			<div class="inline fields row twelve wide column">
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
			</div>
			<div class="inline fields row twelve wide column"
				 style="padding-top: 0; padding-bottom: 0;">
				<div class="three wide field"></div>
				<div class="five wide field">
					<label>Internal code</label>
				</div>
				<div class="seven wide field">
					<label>Display name</label>
				</div>
				<div class="one wide field"></div>
			</div>
			<?php $i=0; foreach($units as $code => $name): ?>
			<div class="inline fields unit row twelve wide column"
				 style="padding-top: 0; padding-bottom: 0;">
				<div class="three wide field">
					<label><?php echo ucfirst($what).' '.($i+1); ?>:</label>
				</div>
				<div class="five wide field <?php echo(form_error('units['.$i.'][code]') ? 'error' : ''); ?>">
					<div class="ui input">
						<input name="<?php echo 'units['.$i.'][code]'; ?>" 
							   data-validate="unitcode"
							   type="text" 
							   value="<?php echo set_value('units['.$i.'][code]', $code); ?>">
					</div>
				</div>
				<div class="seven wide field <?php echo(form_error('units['.$i.'][name]') ? 'error' : ''); ?>">
					<div class="ui input">
						<input name="<?php echo 'units['.$i.'][name]'; ?>" 
							   data-validate="unitname"
							   type="text" 
							   value="<?php echo set_value('units['.$i.'][name]', $name);; ?>">
					</div>
				</div>
				<div class="one wide field">
					<div data-num="<?php echo $i; ?>"
							class="ui icon basic negative compact button delete">
						<i class="trash icon"></i>
					</div>
				</div>
			</div>
			<?php $i++; endforeach; ?>
			<div class="inline fields row twelve wide column">
				<div class="three wide field"></div>
				<div class="twelve wide field">
					<div class="ui basic icon fluid primary button add">
						<i class="add icon"></i>
					</div>
				</div>
				<div class="one wide field"></div>
			</div>
			<div class="inline fields row twelve wide column">
				<div class="four wide field">
					<button class="ui fluid button" type="reset">
						<i class="undo icon"></i>
						Reset
					</button>
				</div>
				<div class="twelve wide field">
					<button class="ui fluid primary button" type="submit">
						<i class="save icon"></i>
						Save
					</button>
				</div>
			</div>
		</form>
	</div>
</div>