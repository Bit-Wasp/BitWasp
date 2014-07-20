
                    <div class="col-xs-12">&nbsp;</div>
                    <!-- Display Unsigned/Partially signed transaction -->
                    {if $display_sign_form == TRUE}
                    <div class="row">
                        <label class="col-xs-3"></label>
                        <div class="col-xs-9">
                            {$display_sign_msg}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-3" for="wallet_passphrase">Scan QR</label>
                        <div class="col-xs-4">
                            <img src='data:image/png;base64,{$onchain_sign.qr}' style="width:100%">
                        </div>
                    </div>
                    {/if}
                    {if ($display_sign_form == FALSE AND $order.partially_signed_transaction == null) OR $order.partially_signing_user_id == $current_user.user_id}
                        <label class="col-xs-3" for="message"></label>
                        <div class="col-xs-9">
                            Waiting on the other user to sign.
                        </div>
                    {/if}

                    <!-- Buttons -->
                    <div class="row">
                        <label class="col-xs-3" for="submit"></label>
                        <div class="col-xs-9">
                            {if $can_refund == TRUE}
                                {capture name='t_refund_url'}orders/refund/{$order.id}{/capture}
                                {url type="anchor" url=$smarty.capture.t_refund_url text='Issue Refund' attr='class="btn btn-success"'}
                            {/if}
                            {url type="anchor" url=$cancel_page text="Back" attr='title="Back" class="btn btn-default"'}
                        </div>
                    </div>
                    <!-- End Buttons -->
