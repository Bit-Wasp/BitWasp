            <div class="col-md-9">
                <h2>Upload Public Key</h2>

                {assign var="defaultMessage" value="For security reasons, you must upload your PGP public key to continue:"}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}
                {form method="open" action="register/pgp" attr=['class'=>'form-horizontal', 'name' => 'register_pgp']}
                    <fieldset>

                        <div class="form-group">
                            <label class="control-label col-xs-2" for="public_key">PGP Key</label>
                            <div class="col-xs-10">
                                <textarea class="form-control" name='public_key' rows="10"></textarea>
                            </div>
                            <span class="help-inline">{form method="form_error" field="public_key"}</span>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    <button type='submit' class="btn btn-primary">Proceed</button>
                                    {url type="anchor" url="logout" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                                </p>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
