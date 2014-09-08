            <div class="col-md-9">
                <h2>Two Factor Authentication</h2>

                {assign var="defaultMessage" value="To disable two factor authentication, enter the token as displayed on your app:"}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="account/disable_2fa" attr=['class'=>'form-horizontal']}
                    <div class="form-group">
                        <div class="col-xs-12"">
                            <label class="control-label col-md-2" for="totp_token">Token</label>
                            <div class="col-md-5">
                                <input type="text" name="totp_token" class="form-control" />
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="totp_token"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" class="btn btn-primary" name="disable_totp" value="Continue" />
                                {url type="anchor" url="account/two_factor" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
		    </div>
