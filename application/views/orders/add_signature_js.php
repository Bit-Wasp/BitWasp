<noscript><style> .jsonly { display: none } </style></noscript>

<!--<script language="Javascript" src="<?php echo base_url(); ?>assets/js/bitcore.js" type="text/javascript"></script>-->
<script language="Javascript" src="<?php echo base_url(); ?>assets/js/bitcore-0.1.26.js" type="text/javascript"></script>


<script language="Javascript" type="text/javascript">
	
	
function sign_raw_transaction()
{
	
	var bitcore = require('bitcore');
	
	<?php echo $crafted_html;
	// Js generates
	// Pubkeys
	// Outs
	// Hasmap
	?>
	
	var password = document.getElementById('wallet_passphrase').value;
	var salt = document.getElementById('wallet_salt').value;
	var parent_pubkey = document.getElementById('extended_public_key').value;
	var signing_key_index = document.getElementById('key_index').value;

	var seed = bitcore.util.sha256(salt+password);
	var hkey = bitcore.HierarchicalKey.seed(seed);
	var child = hkey.derive("m/0'/0");      	// child is the public key we stored!

    var optsb = opts;
    optsb.spendUnconfirmed = true;

	if(child.extendedPublicKeyString() == parent_pubkey) {

		var signing_key = hkey.derive(signing_key_index);
		var buf = new bitcore.buffertools.Buffer(signing_key.eckey.private, 'hex');
		var priv_key = new bitcore.PrivateKey(bitcore.networks.livenet.privKeyVersion, buf, true);
		var wallet_key = new bitcore.WalletKey({network: bitcore.networks.livenet});
		var wif  = priv_key.as('base58');
		wallet_key.fromObj({ priv: wif });

		var b = new bitcore.TransactionBuilder(optsb)
		  .setUnspent(utxos)
		  .setHashToScriptMap(hashMap)
		  .setOutputs(outs)
		  .sign([wallet_key]);

		var tx = b.build();
		var hex = tx.serialize().toString('hex');
		unset_wallet_passphrase();
        embed_tx('js_transaction', hex);
	} else {
		alert ('Entered wrong passphrase!');
        event.preventDefault();
	}   
}

	function check_wallet_passphrase_set(param) {
		if (document.getElementById(param).value == ''){
			return false;
		} 
		return true;
	}

	function unset_entry(field_id) {
		document.getElementById(field_id).value = '';
	}

	function clear_form() {
		unset_entry('wallet_passphrase');
		unset_entry('extended_public_key');
		unset_entry('unsigned_transaction');
		unset_entry('partially_signed_transaction');
		unset_entry('key_index');
		unset_entry('wallet_salt');
		unset_entry('wallet_passphrase');
	}

	function unset_wallet_passphrase() {
		document.sign_transaction.wallet_passphrase.value = '';
	}
	function embed_tx(element_id, tx) {
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", element_id);
		input.setAttribute("value", tx);
		//append to form element that you want .
		document.getElementById("sign_transaction").appendChild(input);
	}


	function embed_extended_pubkey(element_id, public_key) {
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", element_id);
		input.setAttribute("value", public_key);
		//append to form element that you want .
		document.getElementById("bip32Javascript").appendChild(input);
	}

	function extract_tx(optsb, transaction_string) {
		var bitcore = require('bitcore');
		var raw_transaction = new bitcore.buffertools.Buffer(transaction_string, 'hex');
		
		// Full tx, containing signatures. 
		var transaction = new bitcore.Transaction();
		transaction.parse(raw_transaction);
		//console.log("extract_tx: hex tx: ");
		//console.log(transaction.serialize().toString('hex'));
		
		// Present tx, for merging later.
		var current_tx = new bitcore.TransactionBuilder(optsb);
		current_tx.tx = transaction;
		return current_tx;
	}
</script>
