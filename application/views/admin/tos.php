        <div class="span9 mainContent" id="admin-tos">
		  <h2>Terms Of Service</h2>

			<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
		  
		  <div class='row-fluid'>
			<div class='span8'>Here you can set whether a terms of service must be agreed to before
users can register an account. Once the setting is enabled, the terms of service
agreement can be edited.
			</div>
		  </div>
		  <br />

		  <?php echo form_open('admin/tos', array('class' => 'form-horizontal')); ?>
		  		    
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Terms Of Service</div>
			  <div class='span7'>
			    <textarea class='span8' name='terms_of_service' rows='7' ><?php echo $tos; ?></textarea>
			  </div>
		    </div>
			<br />
			
		    <div class='row-fluid'>
			  <div class='span2 offset1'>Display TOS?</div>
			  <div class='span7'>
			    <input type='radio' name='terms_of_service_toggle' value='0' <?php echo ($config['terms_of_service_toggle'] == FALSE) ? 'checked="checked"' : NULL; ?>/> Disabled <br />
			    <input type='radio' name='terms_of_service_toggle' value='1' <?php echo ($config['terms_of_service_toggle'] == TRUE) ? 'checked="checked"' : NULL; ?>/> Enabled
			  </div>
			</div>
			
			<div class="form-actions">
			  <input type='submit' name='tos_update' value='Update' class='btn btn-primary' />
			  <?php echo anchor('admin/edit/','Cancel', array('class'=>'returnLink btn'));?>
			</div>		  

		  </form>
		</div>
