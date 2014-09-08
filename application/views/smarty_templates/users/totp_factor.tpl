            <div class="col-md-9">
                <div class="row-fluid">
                    <h2>Two Factor Authentication</h2>

                    {assign var="defaultMessage" value="Enter the code displayed by your mobile device to continue:"}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}
                    {form method="open" action="login/totp_factor" attr=['class'=>'form-horizontal', 'name' => 'register_pgp']}
                        <fieldset>
                            <div class='row-fluid'>
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <label class="control-label col-md-2" for="totp_token">Token</label>
                                        <div class="col-md-5">
                                            <input type="text" id="totp_token" name='totp_token' class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="totp_token"}</div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-2" for="submit"></label>
                                    <div class="col-xs-5">
                                        <p align="center">
                                            <input type="submit" class="btn btn-primary" name="submit_totp_token" value="Continue" />
                                            {url type="anchor" url="logout" text="Cancel" attr='class="btn btn-default"'}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                    </form>
                </div>
			</div>

