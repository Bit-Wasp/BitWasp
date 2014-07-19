<script language="Javascript" src="<?php echo base_url(); ?>assets/js/bitcore.js" type="text/javascript"></script>
<script language="Javascript" type="text/javascript">

    function get_master_key() {
        if(check_wallet_passphrase_set("wallet_passphrase") == false) {
        } else {
            var bitcore = require('bitcore');

            var extended_public_key = document.getElementById('extended_public_key').value;
            var password = document.getElementById('wallet_passphrase').value;
            var salt = document.getElementById('wallet_salt').value;
            var seed = bitcore.util.sha256(salt+password);
            var hkey = bitcore.HierarchicalKey.seed(seed);

            var child = hkey.derive("m/0'/0").extendedPublicKeyString();
            if(child == extended_public_key) {
                console.log(hkey.extendedPrivateKeyString());
                document.getElementById('bip32panel').className="panel panel-danger";
                document.getElementById('bip32panelheading').innerHTML="BIP32 key (private key)";
                document.getElementById('bip32key').value="Private Key: "+hkey.extendedPrivateKeyString();
            } else {
                document.getElementById('bip32key').value="Incorrect passphrase..";
            }

        }
    }
    function check_wallet_passphrase_set(param) {
        if (document.getElementById(param).value == ''){
            return false;
        }
        return true;
    }

    function embed_extended_pubkey(form_id, element_id, public_key) {
        var input = document.createElement("div");
        input.setAttribute("class","form-group");
        input.setAttribute("name", "extended_");
        input.setAttribute("value", public_key);

        //append to form element that you want .
        document.getElementById(form_id).appendChild(input);
    }

</script>
