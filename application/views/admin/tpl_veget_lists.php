<script>
	$(document)
	.ready(function() {
		
		$('select.dropdown')
			.dropdown({
				allowAdditions: true
			})
		;
		$('.ui.checkbox')
			.checkbox()
		;
		
		$(".ui.checkbox.use_file").checkbox({
			fireOnInit: true,
			onChecked: function() {
				$('.form.export .tpl_details').children().removeClass('disabled');
			},
			onUnchecked: function() {
				$('.form.export .tpl_details').children().addClass('disabled');
			}
		})
		;
		$(".ui.checkbox.use_export").checkbox({
			fireOnInit: true,
			onChecked: function() {
				$('.form.import .tpl_details').children().addClass('disabled');
				$('.form.import').form('set values', $('.form.export').form('get values'));
			},
			onUnchecked: function() {
				$('.form.import .tpl_details').children().removeClass('disabled');
				$('.form.import .dropdown').dropdown('restore defaults');
			}
		})
		;
	})
	;
</script>

<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="excel file icon"></i><?php echo $title; ?></h1>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<div class="ui column content container">
		<h2 class="ui dividing header">Export vegetable list</h2>
		
		<?php echo form_open_multipart(base_url('administration/tpl_veget_lists/export'), 'class="ui form export"');?>
		
		<?php if (empty($err_msg = validation_errors('<li>', '</li>'))): ?>
<!--		<div class="column">-->
			<div class="ui error message"></div>
<!--		</div>-->
		<?php else: ?>
<!--		<div class="column">-->
			<div class="ui error message" style="display:block;">
				<ul class="list">
					<?php echo $err_msg; ?>
				</ul>
			</div>
<!--		</div>-->
		<?php endif; ?>	
		
		<div class="ui top attached segment">
			<div class="field">
				<div class="ui toggle checkbox use_file">
<!--					<input name="use_file" type="checkbox" autocomplete="off" checked="checked">-->
					<?php echo form_checkbox('use_file', 'TRUE', $use_tpl,"autocomplete='off'" ); ?>
					<label>Use a template file</label>
				</div>
			</div>
		</div>
		
		<div class="ui attached segment tpl_details">
			<h4	 class="ui dividing header">Select a template file</h4>
			<div class="field">
				<label>Upload an Excel file that will be the template (*.xls, *.xlsx)</label>
				<?php echo form_upload('userfile', '', 'accept=".xls, .xlsx, .xlsm"'); ?>
			</div>
			<div class="inline field">
				<label>Current file: </label>
				<em><?php echo $tpl_export_veg_list['filename']; ?></em>
			</div>

			<h4	 class="ui dividing header">Set the columns and rows</h4>
			<div class="five fields">
				<div class="field">
					<label>Name column</label>
					<?php echo form_dropdown('name_col', $letters, $tpl_export_veg_list['column_name'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Price column</label>
					<?php echo form_dropdown('price_col', $letters, $tpl_export_veg_list['column_price'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Unit column</label>
					<?php echo form_dropdown('unit_col', $letters, $tpl_export_veg_list['column_unit'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Quantity column</label>
					<?php echo form_dropdown('qtt_col', $letters, $tpl_export_veg_list['column_order'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Total column</label>
					<?php echo form_dropdown('total_col', $letters, $tpl_export_veg_list['column_total'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
			</div>
			<div class="five fields">
				<div class="field">
					<label>Base row for vegetables</label>
					<?php echo form_dropdown('base_row_vege', $numbers, $tpl_export_veg_list['base_row_vege'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Base row for others</label>
					<?php echo form_dropdown('base_row_other', $numbers, $tpl_export_veg_list['base_row_other'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="ui message">
					<p>The <em>"base row"</em> is the row that will be copied to insert a new line in the list.</p>
				</div>
			</div>
		</div>

		<div class="ui hidden divider"></div>
		<button class="ui primary button" type="submit"><i class="save icon"></i> Save for Export list</button>
		</form>	
		
	</div>
	<div class="ui column content container">
		<h2 class="ui dividing header">Import vegetable list</h2>
		
		<?php echo form_open_multipart(base_url('administration/tpl_veget_lists/import'), 'class="ui form import"');?>
		
		<?php if (empty($err_msg = validation_errors('<li>', '</li>'))): ?>
<!--		<div class="column">-->
			<div class="ui error message"></div>
<!--		</div>-->
		<?php else: ?>
<!--		<div class="column">-->
			<div class="ui error message" style="display:block;">
				<ul class="list">
					<?php echo $err_msg; ?>
				</ul>
			</div>
<!--		</div>-->
		<?php endif; ?>	
		
		<div class="ui top attached segment">
			<div class="field">
				<div class="ui toggle checkbox use_export">
					<?php echo form_checkbox('use_export', 'TRUE', $use_export,"autocomplete='off'" ); ?>
					<label>Use the same parameters than the <em>Export list</em></label>
				</div>
			</div>
		</div>
		
		<div class="ui attached segment tpl_details">
			<h4	 class="ui dividing header">Set the columns and rows</h4>
			<div class="five fields">
				<div class="field">
					<label>Name column</label>
					<?php echo form_dropdown('name_col', $letters, $tpl_import_veg_list['column_name'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Quantity column</label>
					<?php echo form_dropdown('qtt_col', $letters, $tpl_import_veg_list['column_order'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
			</div>
			<div class="five fields">
				<div class="field">
					<label>Base row for vegetables</label>
					<?php echo form_dropdown('base_row_vege', $numbers, $tpl_import_veg_list['base_row_vege'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Base row for others</label>
					<?php echo form_dropdown('base_row_other', $numbers, $tpl_import_veg_list['base_row_other'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="ui message">
					<p>The <em>"base row"</em> is the first row that contains a vegetable (or other).</p>
				</div>
			</div>
		</div>

		<div class="ui hidden divider"></div>
		<button class="ui primary button" type="submit"><i class="save icon"></i> Save for Import list</button>
		</form>	
		
	</div>
</div>