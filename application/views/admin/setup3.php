<div class="ui grid container">
	<h1 class="ui dividing header sixteen wide column"><i class="magic icon"></i>Setup Papaya</h1>

	<div class="ui one column row">
		
		<?php if (isset($error)): ?>
		<div class="ui icon negative message">
			<i class="exclamation triangle icon"></i>
			<div class="content">
				<div class="header">
					Error during the creation of the tables
				</div>
				<p>The application failed to create the tables.</p>
				<p>Please check that the database exists or ask help to the developper.</p>
				<pre><?php echo $error; ?></pre>
			</div>
		</div>		
		<?php else: ?>
		<div class="ui icon positive message">
			<i class="check icon"></i>
			<div class="content">
				<div class="header">
					Finished
				</div>
				<p>The database now exists and is ready to use.</p>
				<p><br /></p>
				<a href="<?php echo base_url('/admin'); ?>"
				   class="ui button">Back to the administration
			<i class="right arrow icon"></i></a>
			</div>
		</div>		
		<?php endif; ?>
	</div>
</div>