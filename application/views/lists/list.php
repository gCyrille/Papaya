<script>
	$(document)
	.ready(function() {
		$('.ui.segments').mouseenter(function(){ $(this).addClass('raised'); });
		$('.ui.segments').mouseleave(function(){ $(this).removeClass('raised'); });
	})
	;
</script>
<div class="ui one column grid container">

	<div class="ui huge breadcrumb header column">
		<div class="active section">Delivery lists</div>
	</div>
	
	<div class="ui column">
		<div class="ui primary right floated buttons">
			<a class="ui left labeled icon button" href="<?php echo base_url('/lists/create'); ?>"><i class="plus icon"></i>Create a list</a>
<!--			<button class="ui button">Two</button>-->
		</div>
	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	<div class="ui column">
		<h2 class="ui horizontal divider header">Lists</h2>

		<div class="ui divided very relaxed items">
			<?php foreach($lists_items as $list): ?>
			<div class="item">
				<div class="content">
					<a class="header" href="<?php echo base_url('/lists/view/'.$list['id']); ?>"><?php echo $list['name']; ?></a>
					<a class="ui right floated primary button"
					   href="<?php echo base_url('/deliveries/create/'.$list['id'].'?rdtfrom='.uri_string()); ?>">
						New delivery
						<i class="right plus icon"></i>
					</a>
					<a class="ui right floated basic button" 
					   href="<?php echo base_url('/lists/edit_vegetables/'.$list['id'].'?rdtfrom='.uri_string()); ?>">
						Vegetables 
						<i class="lemon outline right icon"></i>
					</a>
					<a class="ui right floated basic button" 
					   href="<?php echo base_url('/lists/edit/'.$list['id'].'?rdtfrom='.uri_string()); ?>">
						Edit details
						<i class="edit outline right icon"></i>
					</a>
					<div class="meta">
						<span class="day"><?php echo $list['day_of_week']; ?></span>
						<span class="customers"><?php echo $list['count'].' customers'; ?></span>
					</div>
					<div class="ui description">
						<?php echo $list['deliveries']; ?>
					</div>
					<div class="extra">
						<?php if ($list['previous'] != NULL): ?>
						<a class="ui label"
						   href="<?php echo $list['previous_link']; ?>">Previous: <?php echo $list['previous'];?> </a>
						<?php endif; ?>
				  	</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>