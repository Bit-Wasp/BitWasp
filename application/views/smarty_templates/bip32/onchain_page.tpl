            <div class="col-md-9">
                <h2>Public Keys</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <p align="justify">You are using Onchain to sign your multisignature transactions. Private keys are stored on your device, so you should make sure your seed is copied exactly as displayed on the app in case you lose your phone.</p>

                <div class="panel panel-default" id="bip32panel">
                    <div class="panel-heading" id="bip32panelheading">BIP32 key (watch only)</div>
                    <textarea class="form-control" id="bip32key" name="bip32key" readonly>{$key.key|escape:"html":"UTF-8"}</textarea>

                </div>

                {$key_usage_html}
            </div>
