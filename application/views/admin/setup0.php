<div class="ui grid container">
	<h1 class="ui dividing header sixteen wide column"><i class="magic icon"></i>Setup Papaya</h1>

	<div class="ui one column row">
		<?php if ($override_db): ?>
		<div class="ui icon red message">
			<i class="exclamation triangle icon"></i>
			<div class="content">
				<div class="header">
					Papaya is already installed!
				</div>
				<p>The software detects an existing installation of Papaya.</p>
				<p>If you continue with a new installation, you will lose the entire current database, including all customers, deliveries, orders, vegetables, etc.</p>
				<p><strong>PLEASE DO NOT CONTINUE IF YOU DON'T KNOW WHAT YOU ARE DOING!</strong></p>
			</div>
		</div>		
		
		<?php endif; ?>
		<div class="ui icon yellow message">
			<i class="bullhorn icon"></i>
			<div class="content">
				<div class="header">
					Please read carrefully
				</div>
				<p>This application aims to run with the XAMPP application installed on your computer. All the next steps will suppose that the configuration of XAMPP is the unchanged default configuration.</p>
				<p>This application will use the default 'root' user to connect to the MYSQL server and it will create all the databases and tables that it needs.</p>
				<p>If you want to setup the application database click the following button.</p>
				
				<a href="<?php echo base_url('administration/setup/step1'. ($override_db ? '?override_db=true' : '')); ?>"
				   class="ui button">Agreed and start the installation
			<i class="right arrow icon"></i></a>
			</div>
		</div>		
		
	</div>
</div>