<script>
	$.fn.api.settings.api = {
		'update availibility' : '<?php echo base_url('vegetables/update_availibility/{vegetable_id}?lists={lists_ids}'); ?>'
	};
	
	$(document)
	.ready(function() {
		
		var 
			$vegeModal 	= $('#vegetList .ui.modal.delete'),
			$changeModal = $('.ui.modal.change')
		;
		
		var $listeners = {
			delete_link: function() 
			{ // custom action 
				var vege_name = $(this).parents('tr').data('name');
				$deleteLink = this.href;
				$($vegeModal[0]).find('.name').text(vege_name);
				$vegeModal.modal('show');
				return false;
			},
			register_all: function()
			{
				$table_body.find('.dropdown')
					.dropdown()
				;
				$table_body.find('.dropdown .item.delete')
					.click($listeners.delete_link)
				;
				
				$('td.selectable .icon')
					.mouseenter(handler.enter)
					.mouseleave(handler.exit)
					.click(handler.click);
			}
		};

		// Code for search filter
		var
			$table_body =  $('#vegetList table tbody'),
			$original_content = $table_body.children(),
			$tfooter = $('#vegetList table tfoot th>div'),
			$results = null;
		
		$tpl_row = $original_content.first().clone();
		$tpl_row.removeClass('highlight');
		
		$('.ui.search input.prompt')
			.on('focus', function(e) 
			{ 
				// Remove original table
//				console.log("Get focus");
			})
			.on('blur', function(e) 
			{ 
				// Restore original table if not results
//				console.log("Lost focus");
				if ($results.length == 0 || $('.ui.search input.prompt').val().length == 0)
				{
					$table_body.empty();
					$original_content.appendTo($table_body);
					$tfooter.show();
					
					$listeners.register_all();
				}
			})
		;
		
		$('.ui.search')
			.search({
				apiSettings: {
					url: '<?php echo base_url('vegetables/search/?q={query}'); ?>'
				},
				maxResults: 100,
				transition: 'drop',
				fullTextSearch: true,
				minCharacters: 3,
				cache: false, // more reactif filter if false
				onResults: function(response)
				{
					$results = response.results;
				},
				onResultsAdd: function(html)
				{
					$table_body.empty();
					$tfooter.hide();
					
					// For each result create a new table row
					$.each($results, function(index, item)
				  	{
						new_row = $tpl_row.clone();
						
						if (item.accounting_cat != 'veg')
						{
							new_row.addClass('highlight');
						}
						
						anchors = new_row.find('a');
						anchors.each(function()
						{
							href = $(this).prop('href').replace(/edit\/[0-9]+/, 'edit/'+item.id);
							href = href.replace(/delete\/[0-9]+/, 'delete/'+item.id);
							$(this).prop('href', href);
						});
						
						inputs = new_row.find('input:checkbox');
						inputs.each(function()
						{
							name = $(this).prop('name').replace(/availability\[[0-9]+\]\[\]/, 'availability['+item.id+'][]');
							$(this).prop('name', name);
						});
						
						new_row[0].dataset.id = item.id;
						new_row[0].dataset.name = item.title;
						
						name_anchor = new_row.children().eq(0).find('a');
						name_anchor.html(item.title);
						
						price = new_row.children().eq(1);
						price.html(item.price);
						
						unit = new_row.children().eq(2);
						unit.html(item.unit);
						
						// Uncheck all
						new_row.find('td.selectable .icon').each(function(){ handler.uncheck($(this));});
						//Check only suscribed
						$.each(JSON.parse(item.lists), function(index, value)
						{
							handler.check(new_row.find('td[data-list-id='+value+'] .icon'));
						});
						
						$table_body.append(new_row);
					});
					
					$listeners.register_all();
					return false; // Cancel default results view
				}
			})
		;

		// Code for delete button
		$vegeModal.modal({
				closable  : true,
				centered: true,
				onApprove : function($element)
				{
					location.assign($deleteLink);
				}
			});

		// Code for sortable table
		$('#vegetList table')
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
		
		// Code for modification of availability
		var handler = {
			cancel_exit: false,
			enter: function() 
			{
				handler.toggle($(this));
			},
			exit: function()
			{
				if (! handler.cancel_exit)
				{
					handler.toggle($(this));
				}
				
				handler.cancel_exit = false;
			},
			toggle: function($o)
			{
				if ($o.prev('input')[0].checked == true)
				{
					handler.uncheck($o);
				}
				else
				{
					handler.check($o);
				}
			},
			uncheck: function($o)
			{
				$o.prev('input')[0].checked = false;
				$o.removeClass('large green check circle ')
				  .addClass('grey ban');
			},
			check: function($o)
			{
				$o.prev('input')[0].checked = true;
				$o.addClass('large green check circle ')
				  .removeClass('grey ban');
			},
			click: function()
			{
				var tr = $(this).parents('tr').clone();// Clone table line and remove tools column
				tr.removeClass('highlight');
				tr.addClass('form');
				tr.find('td:last-child').remove();
				tr.find('td.selectable>div').each(function(){delete this.dataset.tooltip;});
				var vege_name = $(this).parents('tr').data('name');
				$changeModal.find('.name').text(vege_name);
				$changeModal.find('.content tbody').html(tr); // Insert table line into the model
				$changeModal.find('td .icon') // Interact with icon
					.mouseenter(handler.enter)
					.mouseleave(handler.exit)
					.click(handler.change);
				
				$changeModal.modal('show');
			},
			change: function()
			{
				handler.cancel_exit = true;
			}
		};
		
		$changeModal.modal({
				closable  : true,
				centered: true,
				onApprove : function($element)
				{
					var tr = $changeModal.find('tbody tr'),
						veg_id = tr.data('id'),
						veg_name = tr.data('name'),
						form = $changeModal.find('.form'),
						avail = form.form('get value', 'availability['+veg_id+']');
					
					$(this)
						.api({
							action: 'update availibility',
							on: 'now',
							urlData: {
								vegetable_id: veg_id,
								lists_ids: JSON.stringify(avail)
							},
							onSuccess: function(response) {
								// valid response and response.success = true
								var veg_id = response.data.id,
									lists = JSON.parse(response.data.lists),
									tr = $('#vegetList tr[data-id='+veg_id+']');
								
								// Uncheck all
								tr.find('td.selectable .icon').each(function(){ handler.uncheck($(this));});
								//Check only suscribed
								$.each(lists, function(index, value)
							  	{
									handler.check(tr.find('td[data-list-id='+value+'] .icon'));
								})
							}
						})
					;
				}
			});

		$listeners.register_all();
	})
	;
</script>
<style type="text/css">
	td.selectable .icon
	{
		cursor: pointer;
	}
</style>
<div class="ui one column grid container" id="vegetList">

	<div class="ui huge breadcrumb header column">
		<div class="active section">Vegetables</div>
	</div>
		
	<div class="two column stackable row">
		
		<div class="column">
			<div class="ui fluid search">
				<div class="ui icon fluid input">
					<input class="prompt" placeholder="Name..." type="text">
					<i class="search icon"></i>
				</div>
				<div class="results"></div>
			</div>
		</div>
		
		<div class="right floated column">
			<div class="ui primary right floated buttons">
				<a class="ui left labeled icon button" href="<?php echo base_url('/vegetables/new'); ?>">
					<i class="plus icon"></i>
					Add new
				</a>
			</div>
		</div>

	</div>
	
	<?php echo $this->service_message->to_html('<div class="ui sixteen wide column">', '</div>'); ?>

	<div class="ui column">
		<h2 class="ui horizontal divider header">Full list</h2>

		<table class="ui selectable sortable structured celled table">
			<thead>
				<tr>
					<th rowspan="2" class="ten wide sorted ascending">Name</th>
					<th rowspan="2" class="one wide">Price</th>
					<th rowspan="2" class="one wide">Unit</th>
					<th colspan="<?php echo $lists_count; ?>" class="no-sort">Avaibility</th>
					<th rowspan="2" class="one wide no-sort"></th>
				</tr>
				<tr>
					<?php 
						$list_column = array();
						foreach($delivery_lists as $list)
						{
							echo '<th class="no-sort">',$list->name,'</th>';
							$list_column[] = $list->id;
						}
						if ($lists_count == 0)
						{
							$list_column[] = -1;
							$lists_count = 1;
							$delivery_lists['-1'] = (object)array('name' => 'None');
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php foreach($veget_items as $i => $veget): ?>
				<tr class="<?php if($veget->accounting_cat !== NULL && $veget->accounting_cat !== 'veg'){echo 'highlight';} ?>"
					data-name="<?php echo $veget->name; ?>"
					data-id="<?php echo $veget->id; ?>">
			  		<td class="ui selectable">
						<a href="<?php echo base_url('vegetables/edit/'.$veget->id.'?rdtfrom='.uri_string()); ?>" >
							<?php echo ucfirst($veget->name); ?>
						</a>
					</td>
				  	<td class="ui right aligned">
						<?php echo format_kwacha($veget->price); ?>
					</td>
				  	<td>
						<?php echo element(strtolower($veget->unit), $units); ?>
					</td>
					<?php foreach($list_column as $list_id): ?>
					<td class="ui center aligned selectable availability"
						data-list-id="<?php echo $list_id; ?>">
						
						<div style="display:inline-block;"
							 data-tooltip="<?php echo $delivery_lists[$list_id]->name; ?>"
							 data-position="top center"
							 data-inverted="true">
							<?php if (in_array($list_id, $avaibilities[$veget->id])): ?>
							<input tabindex="0" 
								   class="hidden" 
								   type="checkbox"
								   autocomplete="off"
								   hidden
								   value="<?php echo $list_id; ?>"
								   checked
								   name="<?php echo 'availability['.$veget->id.'][]'; ?>">
							<i class="large green check circle icon"></i>
							<?php else: ?>
							<input tabindex="0" 
								   class="hidden" 
								   type="checkbox"
								   autocomplete="off"
								   hidden
								   value="<?php echo $list_id; ?>"
								   name="<?php echo 'availability['.$veget->id.'][]'; ?>">
							<i class="grey ban icon"></i>
							<?php endif; ?>
						</div>
					</td>
					<?php endforeach; ?>
					<td>
						<div class="ui small buttons">
							<a href="<?php echo base_url('vegetables/edit/'.$veget->id.'?rdtfrom='.uri_string()); ?>" 
							   class="ui icon button"><i class="edit outline icon"></i></a>
							<div class="ui compact top right pointing dropdown icon button">
								<i class="dropdown icon"></i>
								<div class="menu">
									<a href="<?php echo base_url('vegetables/edit/'.$veget->id.'?rdtfrom='.uri_string()); ?>" 
									   class="item">
										<i class="edit outline icon"></i>
										Edit
									</a>
									<a href="<?php echo base_url('vegetables/delete/'.$veget->id.'?rdtfrom='.uri_string()); ?>"
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
				<tr><th colspan="<?php echo ($lists_count + 4); ?>">
					<a class="ui left labeled icon button"
					   href="<?php echo base_url('/vegetables/new'); ?>">
						<i class="plus icon"></i>
						Add new
					</a>
					<?php 
						$pagin = $this->pagination->create_links(); 
						if ($pagin == NULL): ?>
					<a class="ui basic button right floated"
					   href="<?php echo base_url('/vegetables/list/page/0'); ?>">Show per pages</a>
					<?php else: echo $pagin; ?>
					<a class="ui basic button right floated"
					   href="<?php echo base_url('/vegetables/list/all'); ?>">Show all</a>
					<?php endif; ?>
				</th></tr>
			</tfoot>
		</table>
	</div>

<!--	Folowing code for modal and other javascripts -->
	<div class="ui tiny modal delete">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="trash alternate icon" style="display:inline-block;"></i>
			Remove <span class="name">{vegetable}</span>
		</div>
		<div class="content">
			<p>Would you like to remove <strong class="name">{vegetable}</strong> from the vegetable list?</p>
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
		<div class="ui small modal change">
		<i class="close icon"></i>
		<div class="ui header">
			<i class="edit outline icon" style="display:inline-block;"></i>
			Change availability for <i class="name">{vegetable}</i>
		</div>
		<div class="content">
			<table class="ui padded celled table">
				<thead>
				<tr>
					<th>Name</th>
					<th class="one wide">Price</th>
					<th class="one wide">Unit</th>
					<?php 
						foreach($delivery_lists as $list)
						{
							echo '<th class="one wide">',$list->name,'</th>';
						}
					?>
				</tr>
			</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="actions">
			<div class="ui red cancel inverted button">
				<i class="cancel icon"></i>
				Cancel
			</div>
			<div class="ui green approve button">
				<i class="save icon"></i>
				Save change
			</div>
		</div>
	</div>
</div>