<?php
$currency = $siteconfiguration['currency'];
$currencySettings = $siteconfiguration['currencysettings'];
?>
<div class="callsbycustomerandtype index">
	<h2><?php echo __('Calls per customer and type');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th>CustomerID</th>
			<th>Call Type</th>
			<th>Calls</th>
			<th>Raw Minutes</th>
			<th>Billed Minutes</th>
			<th>Retail Price</th>
	</tr>
	<?php
	$i = 0;
	foreach ($data as $item):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $item['Callrecordmaster']['customerid']?></td>
		<td><?php echo $item['Callrecordmaster']['calltype']?></td>
		<td><?php echo $this->Number->currency($item[0]['Calls'],'',array('places'=>0));?></td>
		<td><?php echo $item[0]['RawMinutes']?></td>
		<td><?php echo $item[0]['BilledMinutes']?></td>
		<td><?php echo $this->Number->currency($item[0]['RetailPrice'], $currency,$currencySettings);?></td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' =>  __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%')
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link('Back', '/');?></li>
		<li><?php echo $this->Html->link(__('Export Table', true), array('action' => 'tocsv')); ?></li>
	</ul>
</div>