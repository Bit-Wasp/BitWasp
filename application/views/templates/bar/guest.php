
          <div class="nav-collapse collapse">
            <ul class="nav pull-right">
			  <?php 
			  if($allow_guests == TRUE){ ?>
              <li><?php echo anchor('', 'Home', 'title="Home"'); ?></li>
              <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
              <?php } ?>
              <li><?php echo anchor('login', 'Login', 'title="Login"'); ?></li>
              <li><?php echo anchor('register', 'Register', 'title="Register"');?></li>
            </ul>
          </div>
