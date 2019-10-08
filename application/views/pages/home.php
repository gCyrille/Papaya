<script>
	$(document)
	.ready(function() {
		var papaya_ver = '<?php echo APP_VERSION; ?>';
		var ret = docCookies.getItem('papaya_news');
		if (ret == null || ret != papaya_ver)
		{
			//show modal news
			$('.news.modal')
				.modal({
					closable  : true,
					onApprove : function() {
						docCookies.setItem('papaya_news', papaya_ver, Infinity);
					}
				})
				.modal('show')
			;
			
		}
		
//		docCookies.removeItem('papaya_news');
		
		$('.ui.segments').mouseenter(function(){ $(this).addClass('raised'); });
		$('.ui.segments').mouseleave(function(){ $(this).removeClass('raised'); });
	})
	;
</script>
<div class="ui one column grid container">

	<h1 class="ui header column">
		<img src="<?php echo base_url('assets/images/papaya_green.png'); ?>" class="" />
		<div class="content">
			Welcome into Papaya!
			<div class="sub header">The software to manage your vegetables deliveries</div>
		</div>
		<img src="<?php echo base_url('assets/images/logo.png'); ?>" class="ui image right floated" style="width:4.1em;" />
	</h1>
	
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>
	
	
	<div class="ui column">
		<h2 class="ui horizontal divider header">Quick view</h2>
		
		<div class="ui divided very relaxed items">
			<?php foreach($lists_items as $list): ?>
			<div class="item">
				<div class="content">
					<a class="header" href="<?php echo base_url('/lists/view/'.$list['id']); ?>"><?php echo $list['name']; ?></a>
					<div class="ui description">
						<?php echo $list['deliveries']; ?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>

		</div>
		
	<div class="ui hidden header divider"></div>
	</div>
	
	<div class="ui news modal">
		<div class="header"><span class="ui big green label"><?php echo APP_VERSION; ?></span> New version of Papaya!</div>
		<div class="image content">
			<div class="ui small image">
				<img src="<?php echo base_url('assets/images/papaya_green.png'); ?>" >
			</div>
			<div class="description">
				<div class="ui header">What's new?</div>
				<div class="ui list">
					<div class="item">
						<i class="chart line icon"></i>
						<div class="content">
							Statistics are now available!
						</div>
					</div>
					<div class="item">
						<i class="question icon"></i>
						<div class="content">
							Ask the user to save before closing the edition of an order.
						</div>
					</div>
					<div class="item">
						<i class="excel file icon"></i>
						<div class="content">
							Add possibility to download the summary of a customer's payments.
						</div>
					</div>
				</div >
			</div>
		</div>
		<div class="actions">
			<div class="ui approve button">Great! Let's try<i class="right chevron icon"></i></div>
		</div>
	</div>
</div>