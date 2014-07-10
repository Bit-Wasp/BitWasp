
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
	var signed_tx = document.getElementById('partially_signed_transaction').value;
	var unsigned_tx = document.getElementById('unsigned_transaction').value.trim();
	
	var signing_key_index = document.getElementById('key_index').value;
	var seed = bitcore.util.sha256(salt+password);
	var hkey = bitcore.HierarchicalKey.seed(seed);
	
	// child is the public key we stored!
	var child = hkey.derive("m/0'/0");
	if(child.extendedPublicKeyString() == parent_pubkey) {
		var signing_key = hkey.derive("m/0'/0/6");

		var buf = new bitcore.buffertools.Buffer(signing_key.eckey.private, 'hex');
		var priv_key = new bitcore.PrivateKey(bitcore.networks.livenet.privKeyVersion, buf, true);
		var wallet_key = new bitcore.WalletKey({network: bitcore.networks.livenet});
		var wif  = priv_key.as('base58');
		wallet_key.fromObj({ priv: wif });
		
		var optsb = opts;
		optsb.spendUnconfirmed = true;
		
		var b = new bitcore.TransactionBuilder(optsb)
		  .setUnspent(utxos)
		  .setHashToScriptMap(hashMap)
		  .setOutputs(outs)
		  .sign([wallet_key]);	
		
		if(signed_tx.length > 0) {						
			// Stupid solution to getting working builder obj with:
			// Duplicate now signed tx, which is set up by the builder, and replace all sigs.
			var partial_builder = b;
			var signed_builder = extract_tx(optsb, signed_tx);
			var l = partial_builder.tx.ins.length;
			for(var i = 0; i < l; i++) {
				partial_builder.tx.ins[i].s = signed_builder.tx.ins[i].s;
				partial_builder.signaturesAdded++;
			}
			
			console.log(partial_builder);
			b.merge(partial_builder);
		} else {
			alert("unsigned transaction. ");
		}
		console.log(b);
		var tx = b.build();
		console.log(tx.serialize().toString('hex'));
	} else {
		alert ('wrong passphrase');
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
