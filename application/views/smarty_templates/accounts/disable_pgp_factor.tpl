            <div class="col-md-9">
                <h2>Disable Two Factor Authentication</h2>

                {assign var="defaultMessage" value="Decrypt the following PGP message to remove two factor challenge on login:"}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="account/disable_2fa" attr=['class'=>'form-horizontal']}

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="challenge"></label>
                        <div class="col-xs-8">
                            <pre class="well">{$challenge}</pre>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="answer">Token</label>
                            <div class="col-xs-5">
                                <input type="text" id="answer" class="form-control" name="answer" />
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="answer"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" class="btn btn-primary" name="disable_pgp" value="Continue" />
                                {url type="anchor" url="account/two_factor" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
    		</div>
