        <div class="span9 mainContent" id="admin-panel">
		  
		  <?php echo $nav; ?>
			
<?php if($jobs == FALSE) { ?>
There are no autorun jobs. These are stored in ./application/libraries/Autorun. Either
this folder is empty or you have not configured your cron daemon to trigger
the jobs. Add this following line to your users crontab to activate the jobs:
<pre>*/1 * * * * curl <?php echo base_url('callback/autorun'); ?></pre>
<br />
<?php } else { 

	foreach($jobs as $index => $job) { 
		if($index == 'price_index' && $config['price_index'] == 'Disabled')
			continue;
?>
			<div class="row-fluid">
			  <div class="span3"><?php echo $job['name']; ?></div>
			  <div class="span2"><?php 
			  
			  if($job['interval'] == '0'){
			      echo 'Disabled';
		      } else {	
				  echo $job['interval']." ".$job['interval_type']; 
			  }?></div>
			  <div class="span4">Last Run: <?php echo $job['time_f']; ?>.</div>
			</div>
<?php } } ?>
		</div>
