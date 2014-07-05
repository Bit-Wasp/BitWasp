		<div class="col-md-9" id="vendor_public_keys">

            <h2>Bitcoin Public Keys</h2>

            {assign var="defaultMessage" value=""}
            {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}

            {form method="open" action="accounts/public_keys" attr=['class'=>'form-horizontal']}
                <p>Use this form to enter public keys in advance of orders. Public keys should be separated by a new line. They must also be unique - any keys used previously will be discarded.</p>

                <div class='form-group'>
                    <div class="col-xs-12">
                        <label class="control-label col-xs-2" for="public_key_list">Public Keys</label>
                        <div class='col-md-8'>
                            <textarea id="public_key_list" name="public_key_list" class='form-control'></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-2" for="submit"></label>
                    <div class="col-xs-6">
                        <p align="center">
                            <input type='submit' name='submit_public_keys' value='Upload Public Keys' class='btn btn-primary' />
                            {url type="anchor" url="account" text="Cancel" attr='class="btn btn-default"'}
                        </p>
                    </div>
                </div>
            </form>

            {if $available_public_keys }
            {form method="open" action="account/public_keys" attr=['class'=>'form-horizontal']}
                <legend>Current Public Keys</legend>
                <p>You have {count($available_public_keys)} public keys available.</p>

                <div class='row col-md-offset-1'>
                    {foreach from=$available_public_keys item=public_key}
                    <div class='row'>
                        {$public_key.public_key|escape:"html":"UTF-8"}<br />
                        Bitcoin Address: {$public_key.address}
                    </div>
                    {/foreach}
                </div>
            </form>
            {/if}
        </div>
