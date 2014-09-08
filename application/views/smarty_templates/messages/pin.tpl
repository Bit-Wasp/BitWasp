            <div class="col-md-9">
                <h2>Message's PIN</h2>

                {assign var="defaultMessage" value="You must enter your PIN to decrypt your messages"}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="message/pin" attr=['class'=>'form-horizontal']}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="col-xs-2 control-label" for="pin">PIN</label>
                            <div class="col-xs-6">
                                <input type='password' name='pin' id="pin" class="form-control" value="" autocomplete="off"/>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="pin"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                        <div class="col-sm-5 col-lg-5 col-md-5">
                            <p align="center">
                                <button type='submit' class="btn btn-primary">Submit</button>
                                {url type="anchor" url="home" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>
            </div>
