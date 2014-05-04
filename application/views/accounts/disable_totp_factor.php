          <div class="mainContent span7">
			<div class='row-fluid'>
				<h2>Two Factor Authentication</h2>
				  <?php echo form_open('account/disable_2fa', array('class' => 'form-horizontal')); ?>
				  <fieldset>
					<div class="alert">
					<?php if(isset($returnMessage)) { echo '<div class="alert'.((isset($success) && $success == TRUE) ? 'alert-sucecss' : '').'">'.$returnMessage.'</div>'; } else { ?>
To disable two factor authentication, enter the token as displayed on your app:
					<?php } ?>
					</div>
					
					
					<div class="control-group">
					  <label class="control-label" for="answer">Token</label>
					  <div class="controls">
						<input type="text" name='totp_token' size='12'/>
						<span class="help-inline"><?php echo form_error('totp_token'); ?></span>
					  </div>
					</div>

					<div class="form-actions">
						<input type='submit' class='btn btn-primary' name='disable_totp' value='Continue' />
					  <?php echo anchor('account/two_factor', 'Cancel', 'title="Cancel" class="btn"');?>
					</div>
				  <fieldset>
				</form>
			</div>
		</div>
