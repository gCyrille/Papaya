
<div class="ui one column grid container">
	<div class="ui huge breadcrumb header column">
		<a class="section" 
		   href="<?php echo base_url('customers/list/'); ?>">
			Customers
		</a>
		<i class="right angle icon divider"></i>
		<div class="active section"><?php echo $title; ?></div>
	</div>
	
	<div class="ui column">
		<div class="ui buttons">
			<a class="ui left labeled icon button" href="<?php echo $back_link; ?>">
				<i class="cancel icon"></i>
				Close
			</a>
		</div>
	</div>
	
	<?php include('edit_form.php'); ?>
</div>