            <div class="col-md-9">
                <h2>Authorize Request</h2>

                {assign var="defaultMessage" value="As this page has heightened security, you must enter your login details to continue."}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="authorize" attr=['class'=>'form-horizontal', 'name' => 'authorizeForm']}

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="password">Password</label>
                            <div class="col-xs-5">
                                <input type="password" class="form-control" name="password" value="" />
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="password"}</div>
                    </div>

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
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="captcha"}</div>
                    </div>
                    <!-- /Captcha -->

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" class="btn btn-primary" value="Continue" />
                                {url type="anchor" url="home" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
            </div>