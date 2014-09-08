            <div class="col-md-9" id="add-public-key">

                <h2>Add PGP Public Key</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="pgp/add" attr='class="form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="public_key">Public Key</label>
                            <div class="col-xs-10">
                                <textarea name="public_key" id="public_key" class="form-control" rows="10" ></textarea>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method='form_error' field='public_key'}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" value="Submit" class="btn btn-primary" />
                                {url type="anchor" url="account" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
            </div>
