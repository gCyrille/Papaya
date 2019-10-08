<script>
	$(document)
	.ready(function() {
		$('.ui.search')
		.search({
			apiSettings: {
      			url: '<?php echo base_url('customers/search/?q={query}'); ?>'
    		},
			transition: 'drop'
		})
		;
		var 
			$modal 			= $('.ui.modal'),
			$customerId		= -1
		;

		$modal.modal({
				closable  : true,
				onApprove : function($element)
				{
					location.assign($deleteLink);
				}
			});

		$('table .dropdown')
			.dropdown()
		;
		$('table .dropdown .item.delete')
			.on('click', function() 
			{ // custom action 
					var name = $(this).data('name');
					$deleteLink = this.href;
					$($modal[0]).find('.name').text(name);
					$modal.modal('show');
					return false;
			})
		;

		$('table')
			.tablesort({
				compare:function(t,e)
				{
					a = Number(t);
					b = Number(e);
					if (isNaN(a) || isNaN(b))
					{
						return t>e?1:t<e?-1:0
					}
					else
					{
						return a>b?1:a<b?-1:0;
					}
				}
			})
		;
	})
	;
</script>
<div class="ui one column grid container">

	<div class="ui huge breadcrumb header column">
		<div class="active section">Customers</div>
	</div>
	
	<div class="two column stackable row">
		<div class="column">
			<div class="ui fluid search">
				<div class="ui icon fluid input">
					<input class="prompt" placeholder="Name or email..." type="text">
					<i class="search icon"></i>
				</div>
				<div class="results"></div>
			</div>
		</div>
		
		<div class="right floated column">
			<div class="ui primary right floated buttons">
					<a class="ui left labeled icon button"
					   href="<?php echo base_url('/customers/new'); ?>">
						<i class="plus icon"></i>
						Create
					</a>
			</div>
		</div>
	</div>
	<?php echo $this->service_message->to_html('<div class="ui column">', '</div>'); ?>

	<div class="ui text container column">
		<h2 class="ui horizontal divider header">Full list</h2>
		
		<table class="ui selectable sortable celled table">
			<thead>
				<tr>
					<th class="sorted ascending">Name</th>
					<th class="one wide">Total unpaids</th>
					<th class="no-sort one wide"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($customers as $customer): ?>
				<tr class="">
			  		<td class="ui selectable">
						<a href="<?php echo base_url('customers/edit/'.$customer->id); ?>">
							<?php echo ucfirst($customer->name); ?>
						</a>
					</td>
					<td class="ui"><?php echo format_kwacha($customer->total_unpaid); ?></td>
					<td>
						<div class="ui small buttons">
							<a href="<?php echo base_url('customers/edit/'.$customer->id); ?>" 
							   class="ui icon button"><i class="edit outline icon"></i></a>
							<div class="ui compact top right pointing dropdown icon button">
								<i class="dropdown icon"></i>
								<div class="menu">
									<a href="<?php echo base_url('customers/edit/'.$customer->id); ?>" class="item">
										<i class="edit outline icon"></i>
										Edit
									</a>
									<a href="<?php echo base_url('customers/delete/'.$customer->id); ?>" 
									   data-name="<?php echo $customer->name; ?>"
									   class="item delete">
										<i class="trash alternate outline icon"></i>
										Delete
									</a>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr><th colspan="5">
					<?php echo $this->pagination->create_links(); ?>
				</th></tr>
			</tfoot>
		</table>
	</div>
<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal delete">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="trash alternate icon" style="display:inline-block;"></i>
			Remove <span class="name">{customer}</span>
		</div>
		<div class="content">
			<p>Would you like to remove the customer <strong class="name">{customer}</strong> ?</p>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				No
			</div>
			<div class="ui green approve button">
				<i class="trash icon"></i>
				Yes
			</div>
		</div>
	</div>
	
</div>