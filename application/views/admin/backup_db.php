<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="database icon"></i><?php echo $title; ?></h1>

	<div class="ui column content container">
		<h4 class="ui dividing header">Save the current database</h4>
		
		<div class="ui buttons">
			<a class="ui button"
			   href="<?php echo base_url('administration/backup_db/download/'); ?>">
				<i class="download icon"></i>Download the full backup
			</a>
			<div class="or"></div>
			<a class="ui button"
			   href="<?php echo base_url('administration/backup_db/download?what=customers'); ?>">
				<i class="download icon"></i>Only customers
			</a>
			<div class="or"></div>
			<a class="ui button"
			   href="<?php echo base_url('administration/backup_db/download?what=vegetables'); ?>">
				<i class="download icon"></i>Only vegetables
			</a>
			<div class="or"></div>
			<a class="ui button"
			   href="<?php echo base_url('administration/backup_db/download?what=customers,vegetables'); ?>">
				<i class="download icon"></i>Customers and vegetables
			</a>
		</div>
	</div>
	
	<div class="ui column content container">
		<h4 class="ui dividing header">Restore the database from a previous backup</h4>
		
		<?php if ( ! empty($error)): ?>
		<div class="ui negative icon message">
			<i class="close icon"></i>
			<i class="frown icon"></i>
			<div class="content">
				<div class="header">
					We're sorry we can't restore the backup
				</div>
				<p>
					<?php echo $error; ?>
				</p>
			</div>
		</div>
		<?php elseif ( ! empty($success)): ?>
		<div class="ui positive icon message">
			<i class="close icon"></i>
			<i class="check icon"></i>
			<div class="content">
				<div class="header">
					Backup restored!
				</div>
				<p>
					<?php echo $success; ?>
				</p>
			</div>
		</div>
		<?php else: ?>
		<div class="ui icon warning message">
			<i class="close icon"></i>
  			<i class="exclamation triangle icon"></i>
			<div class="content">
				<div class="header">
					Warning!
				</div>
				<p>
					The current data (Vegetables, deliveries, customers, balances, invoices...) <strong>will be erased</strong> by the data of the backup.
				</p>
			</div>
		</div>
		<?php endif; ?>
		
		<?php echo form_open_multipart('administration/backup_db/upload/', 'class="ui form"');?>
			<div class="inline fields">
				<div class="ui input field">
					<input name="upload_backup" accept=".zip,.sql" type="file">
				</div>
				<div class="field">
					<button class="ui fluid button left icon" name="upload_btn">
						<i class="upload icon"></i>
						Upload the backup
					</button>
				</div>
			</div>
		</form>
	</div>
</div>