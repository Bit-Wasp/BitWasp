		<div class="span9 mainContent" id="manage_items">
			<h2>Shipping Costs: <?php echo $item['name']; ?></h2>
			<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
          		  
<?php if($shipping_costs !== FALSE) { ?>
			<?php echo form_open('listings/shipping/'.$item['hash'], array('class' => 'form-horizontal')); ?>
  <?php if(strlen(validation_errors()) > 0) echo '<div class="alert danger">'.validation_errors().'</div>';  ?>
				<div class='row-fluid span8'>
					<div class='well'>
						<strong>Review Shipping Costs</strong>  
						<div class='row-fluid'>
							<div class='span4'><strong>Destination</strong></div>
							<div class='span4'><strong>Cost</strong></div>
						<div class='span4'><strong>Offered?</strong></div>
					</div>
					<div class='row-fluid'>
<?php $c = 0; foreach($shipping_costs as $cost) { ?>
						<div class='row-fluid'>
							<div class='span4'><?php echo $cost['destination_f']; ?></div>
							<div class='span4'>
								<div class="input-prepend">
									<span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span><input type="text" class='span8' name="cost[<?php echo $cost['id']; ?>][cost]" value="<?php echo $cost['cost']; ?>" />
								</div>
							</div>
							<div class='span4'><input type="checkbox" name="cost[<?php echo $cost['id']; ?>][enabled]" value="1" <?php if($cost['enabled'] == '1') echo 'checked '; ?>/></div>
						</div>
<?php } ?>
					</div>
					<div class="form-actions">
						<input type='submit' name='update_shipping_cost' value='Update' class='btn btn-primary' />
					</div>
				</div>
			</div>
		</form>
		
<?php } ?>

		<?php echo form_open('listings/shipping/'.$item['hash'], array('class' => 'form-horizontal')); ?>		    
		
		<div class='row-fluid span8'>
			<div class='well'>
				<strong>New Shipping Cost</strong>
				<div class='row-fluid'>
					<div class='span4'><strong>Destination</strong></div>
					<div class='span4'><strong>Cost</strong></div>
				</div>
				<div class='row-fluid'>
					<div class='span4'><?php echo $locations; ?></div>
					<div class="input-prepend">
					   <span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span>
					   <input type='text' class='span8' name='add_price' value="<?php echo ($item['currency']['id'] == '0') ? '0.003' : '10'; ?>" />
					</div>
				</div>
				<div class="form-actions">
					<input type='submit' name='add_shipping_cost' value='Add' class='btn btn-primary' />
					<?php echo anchor('listings/edit/'.$item['hash'],'Cancel', array('class'=>'returnLink btn'));?>
				</div>
			</div>
		  </div>
		</form>
	</div>
