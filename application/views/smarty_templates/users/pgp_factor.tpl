        <div class="col-md-9">
            <div class='row'>
				<h2>Two Factor Authentication</h2>

                {assign var="defaultMessage" value="Decrypt the following PGP text and enter it below: "}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="login/pgp_factor" attr=['class'=>'form-horizontal', 'name' => 'pgp_factor']}
                    <fieldset>
                        <div class="form-group">
                            <label class="control-label col-md-1" for="challenge"></label>
                            <div class="col-md-10 col-xs-12">
                                <pre class="well">{$challenge}</pre>
                            </div>
                        </div>

					    <div class="form-group">
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2" for="answer">Token</label>
                                <div class="col-xs-5">
                                    <input type="text" id="answer" class="form-control" name='answer' size='12'/>
                                </div>
                            </div>
                            <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="answer"}</div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-xs-2" for="submit"></label>
                            <div class="col-xs-5">
                                <p align="center">
                                    <input type="submit" class="btn btn-primary "name="submit_pgp_token" value="Continue" />
                                    {url type="anchor" url="logout" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                                </p>
                            </div>
                        </div>

				    </fieldset>
				</form>
			</div>
		</div>
