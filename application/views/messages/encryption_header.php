<script language="Javascript" src="<?php echo base_url(); ?>assets/js/rsa.js" type="text/javascript"></script>
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/aes-enc.js" type="text/javascript"></script>
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/sha1.js" type="text/javascript"></script>
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/base64.js" type="text/javascript"></script>
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/PGpubkey.js" type="text/javascript"></script>
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/mouse.js" type="text/javascript"></script>
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/PGencode.js" type="text/javascript"></script>
<script language="Javascript" type="text/javascript">
//Encrypt the user's message with the recipients PGP key
function messageEncrypt()
{

 var keytyp = 0; //Only allow RSA encryption
 var keyid  = '';
 var pubkey = '';

 //Check if the recipient has a public key uploaded.
 if(document.sendMessageForm.public_key.value == ''){ //No pubkey. Just submit form
 	document.sendMessageForm.submit();
	return;	
 }

// Check if the message is already encrypted.
if(	(document.sendMessageForm.message.value.search('-----BEGIN PGP MESSAGE-----') !== -1) && (document.sendMessageForm.message.value.search('-----END PGP MESSAGE-----') !== -1 ) ){
    document.sendMessageForm.submit();	// Already encrypted, just submit.
    return;
}

 //Loads the public key from a hidden from field.
 var pu=new getPublicKey(document.sendMessageForm.public_key.value);
 
 if(pu.vers == -1) return;

 pubkey = pu.pkey.replace(/\n/g,'');

 keyid='0000000000000000';
 if(pu.keyid.length) keyid=pu.keyid;
 if(keyid.length != 16)
 {
   alert('Invalid Key Id');
   return;
 } 
 
 var text=document.sendMessageForm.message.value+'\r\n';
	document.sendMessageForm.message.value=doEncrypt(keyid, keytyp, pubkey, text);
	document.sendMessageForm.submit();
}

</script>
