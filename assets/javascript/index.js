
// namespace
window.semantic = {
  handler: {}
};

window.console.clear = function(){};

// ready event
semantic.ready = function() {

	// selector cache
	var
		$document			= $(document),
    	$container			= $('.main.container'),
		$menu				= $('.left.menu'),
		$dropdown         	= $menu.find('.ui.dropdown'),
		handler
	;

	// event handlers
	handler = {

	};

	$dropdown
		.dropdown({
			on: 'hover'
		})
	;
	$('.ui.accordion')
  		.accordion({
			exclusive: false,
		animateChildren:false
	})
	;
	// Service messages
	$('.message .close')
		.on('click', function() {
			$(this)
				.closest('.message')
				.transition('fade')
			;
		})
	;
	
};


// attach ready event
$(document)
  .ready(semantic.ready)
;
