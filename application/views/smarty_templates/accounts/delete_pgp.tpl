            <div class="col-md-9" id="delete-pgp">

                <h2>Delete PGP Key</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="pgp/delete" attr=['class'=>'form-horizontal']}
                    Your PGP key can be used to protect your account with two-factor authentication, and for automatic encryption of private messages. Removing your PGP key will disable these features. Confirm that you wish to delete your key:

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="delete">Are you sure?</label>
                            <div class="col-xs-5">
                                <label class="radio-inline">
                                    <input type="radio" name="delete" value="0" checked /> No
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="delete" value="1" /> Yes
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="delete"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" value="Update" class="btn btn-primary" />
                                {url type="anchor" url="account" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
            </div>
