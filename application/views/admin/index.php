<div class="ui grid container">
	<h1 class="ui dividing header sixteen wide column"><i class="sliders horizontal icon"></i>General settings</h1>

	<div class="ui two column stretched row stackable">
		<div class="column">
			<?php if ($db_ready === TRUE): ?>
			<div class="ui icon positive message">
				<i class="thumbs up icon"></i>
				<div class="content">
					<div class="header">Congratulation!</div>
					<p>Papaya is configured and ready to use.</p>
				</div>
			</div>
			<?php elseif ($db_ready === FALSE): ?>
			<div class="ui icon negative message">
				<i class="warning icon"></i>
				<div class="content">
					<div class="header">
						Database error
					</div>
					<p>Sorry ! We found that the database is not ready to run Papaya.<br /> Please check you configuration or configure the application.</p>
					<a href="<?php echo base_url('administration/setup/step0'); ?>"
						  class="ui button">
						<i class="magic icon"></i> Click here to configure Papaya</a>
				</div>
			</div>		
			<?php else: ?>
			<div class="ui icon yellow message">
				<i class="warning icon"></i>
				<div class="content">
					<div class="header">
						Database needs migration
					</div>
					<p>The application as been updated. You need to migrate the database to the new version before to use application.</p>
					<p>If you don't migrate the database you may not be able to use fully the application.</p>
					<p><a href="<?php echo base_url('admin/migrate'); ?>" 
						  class="ui button">
						<i class="magic icon"></i>Click here to migrate the database</a></p>
				</div>
			</div>		
			<?php endif; ?>
		</div>
		<div class="ui column">
			<div class="ui icon message">
				<i class="cogs icon"></i>
				<div class="content">
					<div class="header">
						Information
					</div>
					<ul class="ui bulleted list" style="margin-top: 0;">
						<li>Application environment: <em><?php echo ENVIRONMENT; ?></em></li>
						<li>Application version: <em><?php echo APP_VERSION; ?></em></li>
						<li>Database name: <em><?php echo $db_name; ?></em></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php if ($db_ready !== FALSE): ?>
	<div class="ui four cards doubling one column row">
			<a class="ui green card" 
			   href="<?php echo base_url('administration/config_file/units'); ?>">
				<div class="content">
					<div class="center aligned header">Edit vegetable units</div>
					<div class="center aligned description">
						<p>Change the list of available units for vegetables (Kg, Liters, Bunch...).</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="edit icon"></i>
						<span class="category">Configuration</span>
					</div>
					<div class="right floated">
						Edit <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui green card" 
			   href="<?php echo base_url('administration/config_file/accounting_cats'); ?>">
				<div class="content">
					<div class="center aligned header">Edit accounting categories</div>
					<div class="center aligned description">
						<p>Change the list of available categories for the accounting (Vege, Oil, Pork...).</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="edit icon"></i>
						<span class="category">Configuration</span>
					</div>
					<div class="right floated">
						Edit <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui green card" 
			   href="<?php echo base_url('administration/config_file/expenses_cats'); ?>">
				<div class="content">
					<div class="center aligned header">Edit cash expenses categories</div>
					<div class="center aligned description">
						<p>Change the list expenses used for the accounting (Diesel, Talk time...).</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="edit icon"></i>
						<span class="category">Configuration</span>
					</div>
					<div class="right floated">
						Edit <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui green card" 
			   href="<?php echo base_url('administration/change_password'); ?>">
				<div class="content">
					<div class="center aligned header">Change the password</div>
					<div class="center aligned description">
						<p>Change the password used to edit the balances and to do other secure actions.</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="edit icon"></i>
						<span class="category">Configuration</span>
					</div>
					<div class="right floated">
						Edit <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui blue card" 
			   href="<?php echo base_url('administration/import_file'); ?>">
				<div class="content">
					<div class="center aligned header">Import an Excel file</div>
					<div class="center aligned description">
						<p>Use an Excel file to import vegetables of customers into Papaya.</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="excel file icon"></i>
						<span class="category">Excel file</span>
					</div>
					<div class="right floated">
						Import <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui orange card" 
			   href="<?php echo base_url('administration/backup_config'); ?>">
				<div class="content">
					<div class="center aligned header">Configuration backup</div>
					<div class="center aligned description">
						<p>Export or import a configuration file as a backup.</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="save icon"></i>
						<span class="category">Configuration</span>
					</div>
					<div class="right floated">
						Backup <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui orange card" 
			   href="<?php echo base_url('administration/backup_db'); ?>">
				<div class="content">
					<div class="center aligned header">Database backup</div>
					<div class="center aligned description">
						<p>Export or import a full, or a partial, backup of the database.</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="database icon"></i>
						<span class="category">Database</span>
					</div>
					<div class="right floated">
						Backup <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<a class="ui red card" 
			   href="<?php echo base_url('install'); ?>">
				<div class="content">
					<div class="center aligned header">Reinstall Papaya</div>
					<div class="center aligned description">
						<p>Re-do installation step to make a clean install and to change the database name.</p>
					</div>
				</div>
				<div class="extra content">
					<div class="left floated meta">
						<i class="cogs icon"></i>
						<span class="category">System</span>
					</div>
					<div class="right floated">
						Reinstall <i class="right arrow icon"></i>
					</div>
				</div>
			</a>
			<div class="ui hidden header divider"></div>
	</div>
	<?php endif; ?>
</div>