<script language="Javascript" src="<?php echo base_url(); ?>assets/js/sha512.js" type="text/javascript"></script>
<script language="Javascript" type="text/javascript">
function make_hash() {

	// Generate a proof of work for the password by taking the
	// SHA512 hash of the submitted passwords (done 10 times) and 
	// submit that instead of the password.
	var hash0 = '';
	var hash1 = '';
	var pw0 = document.registerForm.password0.value;
	var pw1 = document.registerForm.password1.value;
	var i = 0;
	
	hash0 = SHA512(pw0);
	hash1 = SHA512(pw1);
	
	for(i = 0; i < 9; i++) {
		hash0 = SHA512(hash0);
		hash1 = SHA512(hash1);
	}
	
	document.registerForm.password0.value=hash0;
	document.registerForm.password1.value=hash1;
	document.registerForm.submit();
}
</script>
