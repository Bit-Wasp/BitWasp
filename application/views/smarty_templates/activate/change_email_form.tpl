
            <div class="col-md-9">
                <h2>Email Activation</h2>
                {assign var="defaultMessage" value="Enter your email and activation token to update your email"}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="activate/change_email" attr=['class'=>'form-horizontal']}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-md-3" for="email_address">Email Address</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" id="email_address" name="email_address" value="" />
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-offset-3 col-md-8">{form method="form_error" field="email_address"}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-md-3" for="activation_hash">Activation Token</label>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" id="activation_hash" name="activation_hash" value="" autocomplete="off" />
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-offset-3 col-md-8">{form method="form_error" field="activation_hash"}</div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    <input type="submit" name="submit_email_activation" class="btn btn-primary" value="Activate" />
                                    {url type="anchor" url="" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                                </p>
                            </div>
                        </div>

                    </fieldset>
                </form>
            </div>
