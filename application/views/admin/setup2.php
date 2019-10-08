<div class="ui grid container">
	<h1 class="ui dividing header sixteen wide column"><i class="magic icon"></i>Setup Papaya</h1>

	<div class="ui one column row">
		
		<?php if (! $error): ?>
		<div class="ui icon negative message">
			<i class="exclamation triangle icon"></i>
			<div class="content">
				<div class="header">
					Error during the creation of the database
				</div>
				<p>The application failed to create the database.</p>
				<p>Please check XAMPP configuration or ask help to the developper.</p>
			</div>
		</div>		
		<?php else: ?>
		<div class="ui icon yellow message">
			<i class="database icon"></i>
			<div class="content">
				<div class="header">
					Database content (tables)
				</div>
				<p>Database name: <strong><?php echo $db_name; ?></strong></p>
				<p>The database exists. The setup will create the tables that the database needs.</p>
				<p><br /></p>
				<a href="<?php echo base_url('administration/setup/step3'); ?>"
				   class="ui button">Continue the installation
			<i class="right arrow icon"></i></a>
			</div>
		</div>		
		<?php endif; ?>
	</div>
</div>