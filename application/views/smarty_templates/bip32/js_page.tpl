            <div class="col-md-9">
                <h2>Public Key</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <p align="justify">You are currently using password based wallet in order to create your keys. This means the marketplace can create order addresses automatically, and all you need to do is enter a password to authorize payments.</p>

                <div class="panel panel-default" id="bip32panel">
                    <div class="panel-heading" id="bip32panelheading">BIP32 key (watch only)</div>
                    <textarea class="form-control" id="bip32key" name="bip32key" readonly>{$key.key}</textarea>
                    <div class="panel-body" id="panel_body">
                        <p>If you wish to generate your master private key in order to sign transactions with another client, enter your passphrase and import the BIP32 key that is displayed above.</p>

                        <input type="hidden" name="wallet_salt" id="wallet_salt" value="{$wallet_salt}" />
                        <input type="hidden" name="extended_public_key" id="extended_public_key" value="{$key.key|escape:"html":"UTF-8"}" />
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-md-3">Wallet Passphrase</label>
                            <div class="col-xs-12 col-md-7">
                                <input type="password" class="form-control" name="wallet_passphrase" id="wallet_passphrase" value="" />
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <button type="button" class="btn btn-primary" onclick="get_master_key()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                {$key_usage_html}
            </div>