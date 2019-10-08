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
		<script src="<?php echo base_url('assets/javascript/simple_cookies.js'); ?>"></script>
	</head>
	<style type="text/css">
		body {
			background-image: linear-gradient(rgba(255,255,255,1), rgba(255,255,255,.85)), url('<?php echo base_url('/assets/images/papaya_trees.png'); ?>');
			background-repeat: no-repeat;
			background-position: bottom;
			background-attachment: fixed;
			background-size: cover;
		}
		.main.container {
			margin-top: 3em;
			padding-left: 12rem !important;
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
		.ui.menu .ui.dropdown .menu > .item .icon:not(.dropdown) {
			margin: 0em 0.75em 0em 0em !important;
		}
		.ui.labeled.icon.menu .item > a > .icon:not(.dropdown) {
			height: 1em;
			display: block;
			font-size: 1.71428571em !important;
			margin: 0em auto 0.5rem !important;
		}
		.ui.vertical.menu {
			width: 9rem !important;
		}
	</style>
	<script>
		$(document)
		.ready(function() {
			var msg_time = $('.message.transition').data('time');
			if (!isNaN(msg_time)) {
				$('.message.transition')
					.delay(20000)
					.fadeOut(200);
				;
			}
		})
		;
	</script>
	<body>
		<?php
			$CI = &get_instance();
			$CI->load->model('lists_model');
			$lists_items = $CI->lists_model->get_list();
			foreach($lists_items as $list)
			{
				$list->deliveries = $CI->lists_model->get_open_deliveries($list->id);
			}
		?>
		<div class="ui left fixed vertical inverted labeled icon menu">
			<div class="item">
				<a href="<?php echo base_url(); ?>">
					<img class="ui tiny centered image" src="<?php echo base_url('assets/images/logo_inverted.png'); ?>">
				</a>
			</div>
			<a href="<?php echo base_url(); ?>" class="item">
				<i class="home icon"></i>
				Home
			</a>

			<a href="<?php echo base_url('vegetables'); ?>" class="item">
				<i class="lemon icon"></i>
				Vegetables
			</a>
			
			<a class="item" href="<?php echo base_url('customers'); ?>"><i class="users icon"></i>Customers</a>
			
			<?php foreach($lists_items as $list): ?>
			<div class="ui left pointing floating dropdown icon item">
				<a href="<?php echo base_url('/lists/view/'.$list->id); ?>" class="">
					<i class="shipping fast icon"></i>
					<?php echo $list->name; ?>
					<div class="menu" tabindex="-1">
						<?php foreach($list->deliveries as $deliv): ?>
						<a href="<?php echo base_url('/deliveries/view/'.$deliv->id); ?>" class="item">
							<?php 
							switch($deliv->status)
							{
								case 'collect':
									echo '<i class="shopping basket icon"></i>';
									break;
								case 'prepare':
									echo '<i class="truck icon"></i>';
									break;
								case 'accounting':
									echo '<i class="calculator icon"></i>';
									break;
							}
							?>
							<?php echo mysql_to_nice_date($deliv->delivery_date); ?>
						</a>
						<?php endforeach; ?>
					</div>
				</a>
			</div>
			<?php endforeach; ?>
			
			<a class="item" href="<?php echo base_url('lists'); ?>"><i class="list icon"></i>All lists</a>
			
		</div>
		<div class="ui bottom fixed vertical inverted big compact menu">
			<a class="item" href="<?php echo base_url('statistics/index'); ?>"><i class="chart line icon"></i>Statistics</a>
			<a class="item" href="<?php echo base_url('admin'); ?>"><i class="sliders horizontal icon"></i>Settings</a>
		</div>
		<div class="ui main fluid container">
			