<html>
	<head>
		<title><?php echo $title; ?></title>
		  <!-- Standard Meta -->
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<!--		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">-->

		<!-- Site Properties -->
		<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/semantic.css'); ?>">
		
		<script src="<?php echo base_url('assets/library/jquery-3.0.0.js'); ?>"></script>
		<script src="<?php echo base_url('assets/library/tablesort.min.js'); ?>"></script>
		<script src="<?php echo base_url('assets/semantic.js'); ?>"></script>
		<script src="<?php echo base_url('assets/javascript/index.js'); ?>"></script>
	</head>
	<style type="text/css">
		body {
/*			background-color: #FFFFFF;*/
		}
		.main.container {
			margin-top: 3em;
			padding-left: 17rem !important;
			padding-right: 2rem;
			margin-bottom: 2em;
		}
		.wireframe {
			margin-top: 2em;
		}
		.ui.footer.segment {
			margin: 5em 0em 0em;
			padding: 5em 0em;
		}
		.ui.table tr.highlight, .ui.table td.highlight {
			background: #F1F1F1;
			color: rgba(0, 0, 0, 0.75);
		}
	</style>
	<script>
		$(document)
		.ready(function() {
			$('.message.transition')
				.delay(20000)
				.fadeOut(200);
			;
		})
		;
	</script>
	<body>
		<div class="ui left fixed vertical inverted blue menu">
			<div class="item">
				<a href="<?php echo base_url(); ?>">
					<img class="ui tiny centered image" src="<?php echo base_url('assets/images/logo_inverted.png'); ?>">
				</a>
			</div>
			<a href="<?php echo base_url(); ?>" class="item"><i class="arrow left icon"></i>Back to Papaya</a>
			<a href="<?php echo base_url('admin'); ?>" class="item"><i class="sliders horizontal icon"></i>General settings</a>
			<a href="<?php echo base_url('administration/about'); ?>" class="item"><i class="info circle icon"></i>About Papaya</a>
			
			<?php if ($this->admin_model->is_db_ready() !== FALSE): ?>
			
			<div class="active item header">Configuration</div>
			
			<a href="<?php echo base_url('administration/config_file/units'); ?>" class="item">Edit vegetable units <i class="edit icon"></i></a>
			<a href="<?php echo base_url('administration/config_file/accounting_cats'); ?>" class="item">Edit accounting cats <i class="edit icon"></i></a>
			<a href="<?php echo base_url('administration/config_file/expenses_cats'); ?>" class="item">Edit cash expenses cats <i class="edit icon"></i></a>
			<a href="<?php echo base_url('administration/change_password'); ?>" class="item">Change the password <i class="lock icon"></i></a>
			<a href="<?php echo base_url('administration/backup_config'); ?>" class="item">Backup configuration <i class="save icon"></i></a>
			
			<div class="active item header">Excel templates</div>
			
			<a href="<?php echo base_url('administration/tpl_veget_lists'); ?>" class="item">Vegetables lists <i class="excel file icon"></i></a>
			<a href="<?php echo base_url('administration/tpl_collect_list'); ?>" class="item">Collection list <i class="excel file icon"></i></a>
			
			<div class="active item header">Database</div>
			
			<a href="<?php echo base_url('administration/import_file'); ?>" class="item"><i class="excel file icon"></i>Import an Excel file</a>
			<a href="<?php echo base_url('administration/backup_db'); ?>" class="item"><i class="database icon"></i>Database backup</a>
			
			<?php elseif ($this->admin_model->db_exists()): ?>
			<a href="<?php echo base_url('administration/setup/step0'); ?>" class="item"><i class="magic icon"></i>Configure Papaya</a>
			<?php else: ?>
			<a href="<?php echo base_url('install'); ?>" class="item"><i class="magic icon"></i>Configure Papaya</a>
			<?php endif; ?>
			
			
		</div>
		<div class="ui main fluid container">
			