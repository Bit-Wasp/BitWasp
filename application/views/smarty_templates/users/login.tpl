
            <div class="col-md-9">
                <h2>Login</h2>
                {assign var="defaultMessage" value="Enter your details to continue: "}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}
                {form method="open" action="login" attr=['class'=>'form-horizontal', 'name' => 'loginForm']}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-md-2" for="user_name">Username</label>
                                <div class="col-md-5">
                                    <input type='text' class="form-control" name='user_name' value="{form method="set_value" field="user_name"}" />
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-offset-2 col-md-10">{form method="form_error" field="user_name"}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-md-2" for="password">Password</label>
                                <div class="col-md-5">
                                    <input type='password' class="form-control" name='password' value='' autocomplete="off" />
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-offset-2 col-md-10">{form method="form_error" field="password"}</div>
                        </div>

                        {if $display_captcha eq TRUE}<!-- Captcha -->
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
                            <div class="col-xs-12 col-md-offset-2 col-md-10">{form method="form_error" field="captcha"}</div>
                        </div>
                        <!-- /Captcha --> {/if}

                        <noscript><div style="display:none"><input type='hidden' name='js_disabled' value='1' /></div></noscript>

                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    <input type="submit" class="btn btn-primary" value="Login" />
                                    {url type="anchor" url="register" text="Register?" attr='title="Register" class="btn btn-default"'}
                                </p>
                            </div>
                        </div>

                    </fieldset>
                </form>
            </div>
