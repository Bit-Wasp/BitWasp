		<div class="col-md-9" id="vendor_public_keys">

            <h2>{$coin.name} Payout Address</h2>

            {assign var="defaultMessage" value=""}
            {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

            <p>Your payout address is where funds should be sent when they are ready to be moved from the order address. Be sure to double check before you set this!</p>

            {if is_array($address) == TRUE}
                <p>Your earnings are being sent to {$address.address|escape:"html":"UTF-8"} (as of {$address.time_f})</p>
            {/if}

            <div class="col-xs-12">&nbsp;</div>
            <div class="col-xs-12">&nbsp;</div>
            {form method="open" action="accounts/payout" attr=['class'=>'form-horizontal']}

                <div class='form-group'>
                    <div class="col-xs-12">
                        <label for="address" class="control-label col-xs-3">{$coin.name} address</label>
                        <div class="col-xs-6">
                            <input type='text' class="form-control" name='address' value='' />
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="col-xs-7 col-xs-offset-2">
                            {form method="form_error" field="address"}
                        </div>
                    </div>
                </div>

                <div class='form-group'>
                    <div class="col-xs-12">
                        <label for="address" class="control-label col-xs-3">Password</label>
                        <div class="col-xs-6">
                            <input type='password' class="form-control" name='password' value='' />
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="col-xs-7 col-xs-offset-2">
                            {form method="form_error" field="password"}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-2" for="submit"></label>
                    <div class="col-xs-6">
                        <p align="center">
                            <input type='submit' name='submit_payout_address' value='Submit' class='btn btn-primary' />
                            {url type="anchor" url="account" text="Cancel" attr='class="btn btn-default"'}
                        </p>
                    </div>
                </div>
            </form>

        </div>
