        <div class="span9 mainContent" id="admin_edit_items">

		  <h2><?php echo anchor('admin/items', 'Back', 'class="btn"'); ?>Fees Configuration</h2>

		  <?php if(isset($returnMessage)) { ?><div class='alert'><?php echo $returnMessage; ?></div><?php } ?>

		  <?php echo form_open('admin/items/fees', array('class' => 'form-horizontal')); ?>
		  
		    <div class='row-fluid'>
			  <div class='span5 offset2'><strong>Basic Settings</strong></div>
		    </div>
		  
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Minimum Fee</div>
			  <div class='span3'>
				<div class="input-prepend">
				  <span class="add-on span1"><i>BTC</i></span>
				  <input type='text' name='minimum_fee' value='<?php echo $config['minimum_fee']; ?>' class='span11' />
				</div>
			  </div>
			</div>
		  
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Default Rate</div>
			  <div class='span7'><input type='text' name='default_rate' value='<?php echo $config['default_rate']; ?>' /></div>
		    </div>
		    
			<div class='row-fluid'>
			  <div class='span5 offset3'><input type='submit' name='update_config' value='Update' class='btn' /></div>
		    </div>
		  </form>

<?php if($fees !== FALSE) { ?>
	    <div class='span7'>
		  <table class='table' cellspacing='0'>
			<thead>
			  <tr>
				<th class='span2'>Lower Limit</th>
			    <th class='span2'>Upper Limit</th>
			    <th class='span2'>% Rate</th>
			    <th class='span2'></th>
			  </tr>
			</thead>
			<tbody>
<?php	foreach($fees as $fee){ ?>
			<?php echo form_open('admin/items/fees'); ?>
			  <tr>
				<td><?php echo $fee['low']; ?></td>
				<td><?php echo $fee['high']; ?></td>
				<td><?php echo $fee['rate']; ?></td>
				<td><input type='submit' name='delete_rate[<?php echo $fee['id']; ?>]' value='Delete' class="btn btn-mini" /></td>
			  </tr>
			</form>
<?php } ?>
		    </tbody>
		  </table>  
		</div>
<?php } ?>
	
		  <?php echo form_open('admin/items/fees', array('class' => 'form-horizontal')); ?>
		  
		    <div class='row-fluid'>
			  <div class='span5 offset2'><strong>Create Fee Rate</strong></div>
		    </div>
		  
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Lower Limit</div>
			  <div class='span7'><input type='text' name='lower_limit' value='' /></div>
		    </div>
		  
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Upper Limit</div>
			  <div class='span7'><input type='text' name='upper_limit' value='' /></div>
		    </div>
		    
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Percentage Fee</div>
			  <div class='span7'><input type='text' name='percentage_fee' value='' /></div>
		    </div>
		    
		    <div class='row-fluid'>
			  <div class='span5 offset3'><input type='submit' name='create_fee' value='Add' class='btn' /></div>
		    </div>
		  
		  </form>
				
		</div>
