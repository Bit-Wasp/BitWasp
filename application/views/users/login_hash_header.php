<script language="Javascript" src="<?php echo base_url(); ?>assets/js/sha512.js" type="text/javascript"></script>
<script language="Javascript" type="text/javascript">
function make_hash() {

	// Generate a proof of work for the password by taking the
	// SHA512 hash of the submitted passwords (done 10 times) and 
	// submit that instead of the password.
	var hash0 = '';
	var pw0 = document.loginForm.password.value;
	var i = 0;
	
	hash0 = SHA512(pw0);
	
	for(i = 0; i < 9; i++) {
		hash0 = SHA512(hash0);
	}
	
	document.loginForm.password.value=hash0; 
	document.loginForm.submit();
}
</script>
