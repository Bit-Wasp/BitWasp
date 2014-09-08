            <div class="col-md-9">
                <h2>Public Key</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <p align="justify">You have set up a BIP32 extended key generated from another wallet. You will need to sign raw transactions manually and paste them back onto the website. Key indexes (eg, m/0'/0/10) are displayed so you know which private key to use to sign.</p>

                <div class="panel panel-default" id="bip32panel">
                    <div class="panel-heading" id="bip32panelheading">BIP32 key (watch only)</div>
                    <textarea class="form-control" id="bip32key" name="bip32key" readonly>{$key.key|escape:"html":"UTF-8"}</textarea>
                </div>

                {$key_usage_html}
            </div>