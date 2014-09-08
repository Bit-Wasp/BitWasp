            <div class="col-md-9">
                <h2>Register</h2>
                {assign var="defaultMessage" value="Enter your details to register an account: "}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}
                {form method="open" action="register" attr=['class'=>'form-horizontal', 'name' => 'registerForm']}

                    <fieldset>

                        <div class="form-group">
                            <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="user_name">Username</label>
                            <div class="col-xs-5">
                                <input type='text' class="form-control" id="user_name" name='user_name' value="{form method="set_value" field="user_name"}" size='12' />
                            </div>
                            </div>
                            <div class="col-xs-12">{form method="form_error" field="user_name"}</div>
                        </div>

                        {if $request_emails == TRUE}
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2" for="email_address">Email Address</label>
                                    <div class="col-xs-5">
                                        <input type='email_address' class="form-control" id="email_address" name='email_address' value='' autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="email_address"}</div>
                            </div>
                        {/if}

                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="password0">Password</label>
                                <div class="col-xs-5">
                                    <input type='password' class="form-control" id="password0" name='password0' value='' autocomplete="off" />
                                </div>
                            </div>
                            <div class="col-xs-12">{form method="form_error" field="password0"}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="password1">Password (confirm)</label>
                            <div class="col-xs-5">
                                <input type='password' class="form-control" id="password1" name='password1' value='' autocomplete="off" />
                            </div>
                            </div>
                            <div class="col-xs-12">{form method="form_error" field="password1"}</div>
                        </div>

{if isset($token_info) && is_array($token_info) }
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="user_type">Role</label>
                                <div class="col-xs-5">
                                    <label class='control-label'>{$token_info.user_type.txt}</label>
                                    <input type='hidden' id="user_type" name='user_type' value='{$token_info.user_type.int}' />
                                </div>
                            </div>
                        </div>
{else}
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="user_type">Role</label>
                                <div class="col-xs-5">
                                    <select name='user_type' id="user_type" class="form-control" >
                                        <option value=''></option>
                                        <option value='1'>Buyer</option>
                                        {if $vendor_registration_allowed == TRUE}<option value='2'>Vendor</option>{/if}
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                {form method="form_error" field="user_type"}
                            </div>
        				</div>
{/if}
                        {if $vendor_registration_allowed == TRUE AND $force_vendor_pgp == TRUE}
                        <div class="form-group">
                            <label class="control-label col-xs-2" for="force_pgp_warning"></label>
                            <div class="col-xs-5">
                                If you are registering as a vendor, it is required you upload a PGP public key. Please have one ready on your first login.
                            </div>
                        </div>
                        {/if}

                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="location">Location</label>
                                <div class="col-xs-5">{$locations_select}</div>
                            </div>
                            <div class="col-xs-12">
                                {form method="form_error" field="location"}
                            </div>
                        </div>
                
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="local_currency">Local Currency</label>
                                <div class="col-xs-5">
                                    <select name='local_currency' class="form-control">
                                        {foreach from=$currencies item=currency}
                                            <option value='{$currency.id}'{if $currency.id == '0'} selected="selected"{/if}>{$currency.name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12">{form method="form_error" field="local_currency"}</div>
                        </div>

                        {if is_string($terms_of_service) == TRUE}
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="terms_of_service">Terms of Service</label>
                                <div class="col-xs-5">
                                    <textarea class='form-control' cols='6' rows='7' readonly>{$terms_of_service|escape:'html':'UTF-8'}</textarea>
                                    <br />
                                    <input type='checkbox' name='tos_agree' value='1' /> Click to agree to the terms of service.
                                </div>
                            </div>
                        </div>
                        {/if}

                        <!-- Captcha -->
                        <div class="form-group">
                            <label class="control-label col-xs-2" for="captcha_img">Captcha</label>
                            <div class="col-xs-5">
                                <p align="center">
                                    {$captcha}
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="captcha"></label>
                                <div class="col-xs-5">
                                    <input type="text" class="form-control" id="captcha" name="captcha" />
                                </div>
                            </div>
                            <div class="col-xs-12">{form method="form_error" field="captcha"}</div>
                        </div>
                        <!-- /Captcha -->

			            <noscript><div style="display:none"><input type='hidden' name='js_disabled' value='1' /></div></noscript>

                        <div class="form-group">
                            <label class="control-label col-xs-2" for="submit"></label>
                            <div class="col-xs-5">
                                <p align="center">
                                    <input type='submit' name='register_user' class="btn btn-primary" value="Register" />
                                    {url type="anchor" url="login" text="Cancel?" attr='title="Cancel" class="btn"'}
                                </p>
                            </div>
                        </div>

                    </fieldset>
                </form>
            </div>
