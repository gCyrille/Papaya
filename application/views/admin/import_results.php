<div class="ui one column grid container">
	<h1 class="ui dividing header column"><i class="excel file icon"></i><?php echo $title; ?></h1>

	<div class="ui icon message">
		<div class="content">
			<div class="header">
				Results
			</div>
			<p>Number of fetched items: <?php echo count($results); ?></p>
			<p>Number of imported items: <?php echo $nb_imported;?></p>
		</div>
	</div>	

	<div class="ui column content container">
		<table class="ui celled table">
			<thead>
				<tr>
					<?php foreach($table_header as $header): ?>
					<th><?php echo $header; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($results as $row): ?>
				<tr class="<?php echo ($row['imported'] == 'false') ? 'error' : (($row['imported'] == 'true') ? 'positive': 'warning'); ?>">
					<?php foreach ($row as $cell): ?>
					<td><?php echo $cell; ?></td>
					<?php endforeach; ?>
<!--
					<td><?php echo $row['name']; ?></td>
					<td><?php echo sprintf('%.2f', $row['price']); ?></td>
					<td><?php echo $row['unit']; ?></td>
					<td><?php echo $row['accounting_cat']; ?></td>
					<td><?php echo $row['imported']; ?></td>
					<td><?php echo $row['issue']; ?></td>
-->
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
	</div>
</div>