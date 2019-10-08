<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="excel file icon"></i><?php echo $title; ?></h1>
	
	<?php echo $this->service_message->to_html('<div class="ui one column row"><div class="column">', '</div></div>'); ?>
	
	<div class="ui column content container">
		<h3 class="ui dividing header">Import <u>vegetables</u> into database</h3>
		
		<?php echo form_open_multipart(base_url('administration/do_upload_vege'), 'class="ui form"');?>

		<div class="field">
			<label>Select a file that contains the list (*.xls, *.xlsx)</label>
			<?php echo form_upload('userfile', '', 'accept=".xls, .xlsx, .xlsm" required'); ?>
		</div>
		
		<div class="fields">
			<div class="field">
				<label>First row</label>
				<input placeholder="5" type="text" name="first_row" required>
			</div>
			<div class="field">
				<label>Last row</label>
				<input placeholder="125" type="text" name="last_row" required>
			</div>
			<div class="field">
				<label>Name column</label>
				<input placeholder="A" type="text" name="name_col" required>
			</div>
			<div class="field">
				<label>Price column</label>
				<input placeholder="B" type="text" name="price_col" required>
			</div>
			<div class="field">
				<label>Unit column</label>
				<input placeholder="C" type="text" name="unit_col" required>
			</div>
			<div class="field">
				<label>Category column</label>
				<input placeholder="D" type="text" name="accounting_col" required>
			</div>
		</div>

		<button class="ui primary button" type="submit"><i class="upload icon"></i> Upload</button>
		</form>	
		
	</div>
	<div class="ui column content container">
		<h3 class="ui dividing header">Import <u>customers</u> into database</h3>
		
		<?php echo form_open_multipart(base_url('administration/do_upload_customers'), 'class="ui form"');?>

		<div class="field">
			<label>Select a file that contains the list (*.xls, *.xlsx)</label>
			<?php echo form_upload('userfile', '', 'accept=".xls, .xlsx, .xlsm" required'); ?>
		</div>
		
		<div class="fields">
			<div class="field">
				<label>First row</label>
				<input placeholder="5" type="text" name="first_row" required>
			</div>
			<div class="field">
				<label>Last row</label>
				<input placeholder="125" type="text" name="last_row" required>
			</div>
			<div class="field">
				<label>Name column</label>
				<input placeholder="A" type="text" name="name_col" required>
			</div>
			<div class="field">
				<label>Contact column</label>
				<input placeholder="B" type="text" name="contact_col" required>
			</div>
			<div class="field">
				<label>Email column</label>
				<input placeholder="C" type="text" name="email_col" required>
			</div>
			<div class="field">
				<label>Place column</label>
				<input placeholder="D" type="text" name="place_col" required>
			</div>
		</div>

		<button class="ui primary button" type="submit"><i class="upload icon"></i> Upload</button>
		</form>	
		
	</div>
</div>