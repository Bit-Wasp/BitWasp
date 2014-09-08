            <div class="col-md-9" id="replace-pgp">
                <h2>Replace PGP key</h2>

                {assign var="defaultMessage" value="Enter your replacement PGP key below. "}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="pgp/replace" attr=['class'=>'form-horizontal']}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="public_key">Public Key</label>
                            <div class="col-xs-10">
                                <textarea name='public_key' id='public_key' rows='10' class='form-control'></textarea>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="public_key"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type='submit' value='Update' class='btn btn-primary' />
                                {url type="anchor" url="account" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
            </div>