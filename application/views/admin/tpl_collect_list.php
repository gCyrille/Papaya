<script>
	$(document)
	.ready(function() {
		
		$('select.dropdown')
			.dropdown({
				allowAdditions: true
			})
		;
	})
	;
</script>

<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="excel file icon"></i><?php echo $title; ?></h1>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<div class="ui column content container">
		<h2 class="ui dividing header">Collection list</h2>
		
		<?php echo form_open_multipart(base_url('administration/tpl_collect_list/save'), 'class="ui form"');?>
		
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
		
		<div class="ui attached segment tpl_details">
			<h4	 class="ui dividing header">Select a template file</h4>
			<div class="field">
				<label>Upload an Excel file that will be the template (*.xls, *.xlsx)</label>
				<?php echo form_upload('userfile', '', 'accept=".xls, .xlsx, .xlsm"'); ?>
			</div>
			<div class="inline field">
				<label>Current file: </label>
				<em><?php echo $tpl_collect_list['filename']; ?></em>
			</div>

			<h4	 class="ui dividing header">Set the columns and rows</h4>
			<div class="inline fields">
				<div class="field">
					<label>Position of the date</label>
					<?php echo form_dropdown('date_col', $letters, $tpl_collect_list['col_date'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<?php echo form_dropdown('date_row', $numbers, $tpl_collect_list['row_date'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
			</div>
			<div class="five fields">
				<div class="field">
					<label>Row to insert customers names</label>
					<?php echo form_dropdown('row_customer', $numbers, $tpl_collect_list['row_customer'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>First customer column</label>
					<?php echo form_dropdown('base_column', $letters, $tpl_collect_list['base_column'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Last customer column</label>
					<?php echo form_dropdown('last_column', $letters, $tpl_collect_list['last_column'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="ui message">
					<p>These columns are hidden to generate the short version of the list.</p>
				</div>
			</div>
			<div class="five fields">
				<div class="field">
					<label>Vegetable name column</label>
					<?php echo form_dropdown('column_desc', $letters, $tpl_collect_list['column_desc'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Unit column</label>
					<?php echo form_dropdown('unit_col', $letters, $tpl_collect_list['column_unit'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
				<div class="field">
					<label>Total column</label>
					<?php echo form_dropdown('total_col', $letters, $tpl_collect_list['column_total'], 'class="ui dropdown" autocomplete="off"'); ?>
				</div>
			</div>
			<div class="five fields">
				<div class="field">
					<label>Base row for vegetables</label>
					<?php echo form_dropdown('base_row', $numbers, $tpl_collect_list['base_row'], 'class="ui dropdown" autocomplete="off"'); ?>
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
</div>