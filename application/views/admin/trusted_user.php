		  <div class="span9 mainContent" id="admin-trusted-user">
			<h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Trusted User Settings</h2>

			<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>

			<div class='row-fluid span10'>
				<p align='justify'>This form allows you to define what makes a 'trusted user'. This is used to determine if a vendor should be allowed to request up-front payment for particular items, or early finalization of escrow orders. </p>
				<p align='justify'>To ignore a particular attribute simply set it to zero.</p>
			</div>
			<br /><br />
			
			<?php echo form_open('admin/trusted_user',array('class' => 'form-horizontal')); ?>
              <div class="control-group">
                <label class="control-label" for="name">Required rating:</label>
                <div class="controls">
                  <input type='text' class='span3' name='trusted_user_rating' value="<?php echo $config['trusted_user_rating']; ?>" />
                  <span class="help-inline"><?php echo form_error('rating'); ?></span>
                </div>
              </div>

			  <div class="control-group">
                <label class="control-label" for="name">Review count:</label>
                <div class="controls">
                  <input type='text' class='span3' name='trusted_user_review_count' value="<?php echo $config['trusted_user_review_count'] ?>" />
                  <span class="help-inline"><?php echo form_error('trusted_user_review_count'); ?></span>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label" for="name">Minimum complete orders:</label>
                <div class="controls">
                  <input type='text' class='span3' name='trusted_user_order_count' value="<?php echo $config['trusted_user_order_count']; ?>" />
                  <span class="help-inline"><?php echo form_error('trusted_user_order_count'); ?></span>
                </div>
              </div>
              
              <div class="form-actions">
                <input type="submit" name='trusted_user_update' value="Update" class="btn btn-primary" />
                <?php echo anchor("admin/items","Cancel", 'class="btn"'); ?>
              </div>
			</form>
		</div>
