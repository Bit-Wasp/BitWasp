
			<div class="col-md-9">
                <h2>Two Factor Authentication</h2>

                <div class='well'>
                    {assign var="defaultMessage" value=""}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                    <div class='row'>
                        <div class='col-md-2'><strong>Current Setting</strong></div>
                        <div class='col-md-4'><u>{if $two_factor_setting == TRUE}Enabled{else}Disabled{/if}</u></div>
                    </div>
                    <hr />

                    <div class='row'>
                        <div class='col-md-6'>
                            <div class='col-xs-12'>
                                <strong>Time-based Two Factor Tokens</strong>

                                {if $two_factor.totp == TRUE}
                                <div class="col-md-12">
                                    You are currently using time based two-factor tokens to authorize logins.<br />
                                    {url type="anchor" url="account/disable_2fa" text="Click here to disable this!" attr='class="btn btn-danger"'}
                                </div>
                                {else}

                                {form method="open" action="account/two_factor" attr=['class'=>'form-horizontal','name' => 'totp_form']}
                                    <div class='row'>
                                        <p>Time-based two factor authentication restricts access to your account by asking you for a token from your Authenticator app on each sign-in. Follow these two steps to get set up:</p>
                                        <p>1 - Scan the QR code to import it your app. Write down the secret key in case you lose your device.</p>
                                        <div class='col-md-9 col-md-offset-3 col-xs-12'><img src='data:image/png;base64,{$qr}'></div>
                                        <div class='col-md-10 col-md-offset-2 col-xs-12'>Secret Key: {$secret}</div>
                                        <p>2 - Enter the generated token and your password to confirm:</p>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <label class="control-label col-xs-2" for="password"></label>
                                            <div class="col-xs-6">
                                                <input type='password' id='password' class="form-control" name='password' placeholder="Password" />
                                            </div>
                                        </div>
                                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="password"}</div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <label class="control-label col-xs-2" for="token"></label>
                                            <div class="col-xs-6">
                                                <input type='text' class="form-control" name='totp_token' value='' placeholder="Token" autocomplete="off" />
                                            </div>
                                        </div>
                                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="totp_token"}</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-xs-2" for="submit"></label>
                                        <div class="col-xs-5">
                                            <p align="center">
                                                <input type='submit' class='btn btn-primary' name='submit_totp_token' value='Setup' />
                                            </p>
                                        </div>
                                    </div>

                                </form>
                                {/if}
                            </div>
                        </div>

                        <div class='col-md-6'>
                            <div class='col-xs-12'>
                                {if isset($two_factor.pgp) == TRUE}
                                <strong>PGP Two Factor Authentication</strong>

                                <div class='row'>
                                    {if $two_factor.pgp == TRUE}
                                        You are currently using PGP two-factor challences to authorize logins. <br />
                                        {url type="anchor" url="account/disable_2fa" text="Click here to disable this!" attr='class="btn btn-danger"'}
                                    {else}
                                        <div class='col-md-12'>PGP-based two factor challenges ensure that your account can only be accessed by someone able to decrypt messages encrypted with your PGP public key.</div>
                                        <div class='col-md-12'>{url type="anchor" url="account/pgp_factor" text="Setup" attr='class="btn btn-default"'}</div>
                                    {/if}
                                </div>
                                {else}
                                <i>Add a PGP key to enable PGP two factor authentication!</i>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

            </div>