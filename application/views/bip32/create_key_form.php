<script language="Javascript" src="<?php echo base_url(); ?>assets/js/bitcore.js" type="text/javascript"></script>
<script language="Javascript" type="text/javascript">
	function generate_key() {
		if(check_wallet_passphrase_set("wallet_passphrase") == false) {
		} else {
			var bitcore = require('bitcore');
			var password = document.getElementById('wallet_passphrase').value;
			var salt = document.getElementById('wallet_salt').value;
			var seed = bitcore.util.sha256(salt+password);
			var child = bitcore.HierarchicalKey.seed(seed).derive("m/0'/0").extendedPublicKeyString();
			unset_wallet_passphrase("wallet_passphrase");
			embed_extended_pubkey("js_extended_public_key", child);
		}
	}

	function check_wallet_passphrase_set(param) {
		if (document.getElementById(param).value == ''){
			return false;
		} 
		return true;
	}

	function unset_wallet_passphrase(param) {
		document.bip32Javascript.wallet_passphrase.value = '';
	}

	function embed_extended_pubkey(element_id, public_key) {
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", element_id);
		input.setAttribute("value", public_key);
		//append to form element that you want .
		document.getElementById("bip32Javascript").appendChild(input);
	}

</script>
