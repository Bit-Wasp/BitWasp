<div class="col-xs-12">&nbsp;</div>

<!-- Display Unsigned/Partially signed transaction -->
{if $display_sign_form == TRUE}
    <div class="row jsonly">
        <label class="col-xs-3"></label>
        <div class="col-xs-9">
            {$display_sign_msg}
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <label class="col-xs-3" for="wallet_passphrase">Wallet Passphrase</label>
                <div class="col-xs-9">
                    <input type="password" name="wallet_passphrase" id="wallet_passphrase" class="form-control" value="" />
                </div>
            </div>
            <div class="col-xs-9 col-xs-offset-3">
                {form method="form_error" field="js_transaction"}
            </div>
        </div>
    </div>

    <noscript>
        <div class="row">

            <div class="form-group">
                <label class="col-xs-3" for="tx">Raw Transaction</label>
                <div class="col-xs-9">
                    <textarea name="tx" id="tx" class="form-control">{if strlen($order.partially_signed_transaction) > 0}{$order.partially_signed_transaction}{else}{$order.unsigned_transaction}{/if}</textarea>
                </div>
            </div>

            <div class="col-xs-9 col-xs-offset-3">
                {$display_sign_msg}
            </div>

            <div class="form-group">

                <label class="col-xs-3" for="partially_signed_transaction">Wallet Passphrase</label>
                <div class="col-xs-9">
                    <textarea name="partially_signed_transaction" id="partially_signed_transaction" class="form-control"></textarea>
                    Sign with key {$signing_info.key_index}
                </div>

                <div class="col-xs-9 col-xs-offset-3">
                    {form method="form_error" field="partially_signed_transaction"}
                </div>
            </div>

        </div>
    </noscript>
{/if}

{if ($display_sign_form == FALSE AND $order.partially_signed_transaction == null) OR $order.partially_signing_user_id == $current_user.user_id}

<label class="col-xs-3" for="message"></label>
<div class="col-xs-9">
    Waiting on the other user to sign.
</div>
{/if}
<!-- End Display Unsigned/Partially signed transaction -->
<input type="hidden" name="extended_public_key" id="extended_public_key" value="{$signing_info.parent_extended_public_key|escape:"html":"UTF-8"}" />
<input type="hidden" name="key_index" id="key_index" value="{$signing_info.key_index}" />
<input type="hidden" name="wallet_salt" id="wallet_salt" value="{$wallet_salt}" />

<!-- Buttons -->
<div class="row">
    <label class="col-xs-3" for="submit"></label>
    <div class="col-xs-9">
        {if $display_sign_form == TRUE}

            <input type="submit" name="submit_js_signed_transaction" class="btn btn-primary jsonly" value="Submit Transaction" onclick="sign_raw_transaction()" />
            <noscript>
                <input type="submit" name="submit_signed_transaction" class="btn btn-primary" value="Submit Transaction" />
            </noscript>
        {/if}

        {if $can_refund == TRUE}
            {capture name='t_refund_url'}orders/refund/{$order.id}{/capture}
            {url type="anchor" url=$smarty.capture.t_refund_url text='Issue Refund' attr='class="btn btn-success"'}
        {/if}
        {url type="anchor" url=$cancel_page text="Back" attr='title="Back" class="btn btn-default"'}
    </div>
</div>
<!-- End Buttons -->
