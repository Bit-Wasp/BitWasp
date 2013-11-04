	    <div class="span9 mainContent" id="admin-users-list">
		  <h2><?php echo anchor('admin/users', 'Back', 'class="btn"'); ?> User List</h2>
			  
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>

		  <p><?php echo anchor('admin/users/list#Search','Click here to search for a user'); ?></p>
		 
<?php if($users !== FALSE) { ?>
		  <table class='table' cellspacing='0'>
			<thead>
			  <tr>
				<th>Name</th>
			    <th>Role</th>
			    <th></th>
			    <th>Activated</th>
			    <th>Banned?</th>
			    <th>Delete?</th>
			  </tr>
			</thead>
			<tbody>
<?php foreach($users as $user) { ?>
			  <tr>
				<td><?php echo anchor('user/'.$user['user_hash'], $user['user_name']); ?></td>
				<td><?php echo $user['user_role']; ?></td>
				<td>Registered: <?php echo $user['register_time_f']; ?><br />Last Login: <?php echo $user['login_time_f']; ?></td>
				<td><?php echo ($user['entry_paid']) ? 'yes' : ''; ?></td>
				<td><?php echo ($user['banned'] == '1') ? 'yes' : ''; ?></td>
				<td><?php echo anchor('admin/users/delete/'.$user['id'], 'Delete?', 'class="btn btn-mini"'); ?></td>
			  </tr>
<?php } ?>
			</tbody>
		  </table>
<?php } else {
	if(isset($search_fail) && $search_fail == TRUE) { echo 'No users found in your query'; }
	else { echo 'There are no users at this time.'; }
} ?>


		  <?php echo form_open('admin/users/list', array('class' => 'form-horizontal')); ?>		 
		  </form>
		  
		  <div class='row-fluid' id='Search'></div>

		  <?php echo form_open('admin/users/list', array('class' => 'form-horizontal')); ?>		 
		    <div class='row-fluid'>
			  <div class='span5 offset2'><strong>Search For User</strong></div>
			</div>
			
			<div class='row-fluid'>
			  <div class='span2 offset1'>User Name:</div>
			  <div class='span4'><input type='text' name='user_name' value='' /></div>
			  <div class='span2'><input type='submit' name='search_username' value='Search' class='btn'/></div>
		    </div>
		    
		    <div class='row-fluid'>
		      <div class='offset3'>or</div>
		    </div>
		    
			<div class='row-fluid'>
			  <div class='span2 offset1'>Search For</div>
			  <div class='span4'>
				<select name='search_for'>
				  <option value=''></option>
				  <option value='all_users'>All Users</option>
				  <option value='buyers'>Buyer Users</option>
				  <option value='vendors'>Vendor Users</option>
				  <option value='admins'>Admin Users</option>
				</select>
			  </div>			  
			  <div class='span2'><input type='submit' name='list_options' value='Search' class='btn' /></div>
			</div>
			
			<div class='row-fluid'>
			  <div class='span2 offset1'>That Are</div>
			  <div class='span7'>
				<select name='with_property'>
				  <option value=''></option>
				  <option value='activated'>Activated</option>
				  <option value='not_activated'>Not Activated</option>
				  <option value='banned'>Banned</option>
				  <option value='not_banned'>Not Banned</option>
				</select>
			  </div>
			</div>

		    <div class='row-fluid'>
			  <div class='span2 offset1'>Order By</div>
			  <div class='span7'>
				<select name='order_by'>
			      <option value=''></option>
				  <option value='id'>User ID</option>
				  <option value='user_name'>User Name</option>
				  <option value='register_time'>Time Registered</option>
				  <option value='login_time'>Login Time</option>
				  <option value='banned'>Banned?</option>
				</select>
			  </div>
		    </div>
		    
		    <div class='row-fluid'>
			  <div class='span2 offset1'>List</div>
			  <div class='span7'>
				<label class='inline'><input type='radio' name='list' value='ASC' /> Ascending </label>
				<label class='inline'><input type='radio' name='list' value='DESC' /> Descending </label>
				<label class='inline'><input type='radio' name='list' value='random' /> Random</label>
			  </div>
		    </div>
		    
		  </form>
		  
		</div>
