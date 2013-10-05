          <div class="mainContainer span9">
            <h2>Upload Public Key</h2>
            <?php echo form_open('register/pgp', array('class' => 'form-horizontal')); ?>
              <fieldset>
                <div class="alert">
                <?php if(isset($returnMessage)) { echo $returnMessage; } else { ?>
				  For security reasons, you must upload your PGP public key to continue.
				<?php } ?>
				</div>

				<textarea class="span9" name='public_key' rows="10"></textarea><br />
                <div class="form-actions">
                  <button type='submit' class="btn btn-primary">Proceed</button>
                  <?php echo anchor('logout', 'Cancel', 'title="Cancel" class="btn"');?>
                </div>
              <fieldset>
            </form>
          </div>
