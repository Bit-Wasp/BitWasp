            <div class="col-md-9">
                <h2>Setup Keys</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <p align="justify">In order to create your order addresses, you need to set up an extended public key. This lets us set up unique and secure payment addresses for each order. You can choose a number of ways to create one of these keys.</p>
                <ul>
                    <!--<li>Using Onchain.io's transaction signer (Android)</li>-->
                    <li>Using an external wallet</li>
                    <li>By entering a password on our website</li>
                </ul>
                <br />

                {form method="open" action="bip32" attr=['class'=>'form-horizontal', 'name' => 'bip32Javascript', 'id' => 'bip32Javascript']}
                    <legend>Create a key from a passphrase</legend>
                    You can create a key from a passphrase, and can simply just enter this when processing orders. This key is only used for signing, and funds are stored only in multi-signature address. Choose a strong password, but also something you won't forget, as it cannot be recovered, and complicate orders being completed.
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="wallet_passphrase">Wallet passphrase:</label>
                            <div class="col-xs-7">
                                <input type="password" class="form-control" name="wallet_passphrase" id="wallet_passphrase" value="" />
                            </div>
                            <div class="col-xs-2">
                                <input type="submit" name="js_submit" value="Submit" class="btn btn-primary" onclick="generate_key()"/>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="manual_public_key"}</div>
                        <input type="hidden" name="wallet_salt" id="wallet_salt" value="{$wallet_salt}" />
                    </div>
                </form>
                <div class="col-xs-12">&nbsp;</div>
<!--
                {form method="open" action="bip32" attr=['class'=>'form-horizontal', 'name' => 'authorizeForm']}
                    <legend>Using Onchain.io's transaction signer</legend>
                    <div class="row">
                        <div class="col-xs-9">
                            <p align="justify">Onchain.io is a multi-signature wallet service which provides a general purpose Android app for creating BIP32 keys and signing transactions by scanning a QR code. This also adds two-factor protection to your funds, as keys are stored on a separate device.</p>
                            <p align="justify">Download the Onchain.io transaction signer from Google Play Store, scan the QR and refresh!</p>
                        </div>
                        <div class="col-xs-3">
                            {if $display_onchain_qr == TRUE}
                            <img src='data:image/png;base64,{$onchain_mpk.qr}' style="width:100%">
                            {/if}
                        </div>
                    </div>
                </form>
                <div class="col-xs-12">&nbsp;</div>
-->
                {form method="open" action="bip32" attr=['class'=>'form-horizontal', 'name' => 'authorizeForm']}
                    <legend>Using an external wallet</legend>
                    <p align="justify">Using an external wallet is only recommended for advanced users, because most wallets don't support BIP32 yet. You can create BIP32 master keys on websites like {url type="anchor" url="http://bip32.org/" text="BIP32.org" attr='title="BIP32.org"'}, and importing the necessary private keys into Bitcoin Core to add a signature.</p>
                    <p align="justify">The BIP32 website can be downloaded and run securely on an offline computer if you wish!</p>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="manual_public_key">Extended public key:</label>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="manual_public_key" value="" />
                            </div>
                            <div class="col-xs-2">
                                <input type="submit" name="manual_submit" value="Submit" class="btn btn-primary"/>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="manual_public_key"}</div>
                    </div>

                </form>
            </div>