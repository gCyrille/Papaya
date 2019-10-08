<div class="ui grid container">
	<h1 class="ui dividing header sixteen wide column"><i class="magic icon"></i>Setup Papaya</h1>

	<div class="ui one column row">
		
		<div class="ui icon yellow message">
			<i class="database icon"></i>
			<div class="content">
				<div class="header">
					Create database
				</div>
				<?php echo form_open(base_url('administration/setup/step2'), 'class="ui form container"', array('override_db' => $override_db ? 'true' : 'false')); ?>
				 
					<div class="field">
						<label>Database name</label>
						<input name="dbname" placeholder="papaya" type="text">
					</div>
					<button type="submit"
							class="ui button">
						Continue the installation
						<i class="right arrow icon"></i>
					</button>
				</form>
			</div>
		</div>		
		
	</div>
</div>