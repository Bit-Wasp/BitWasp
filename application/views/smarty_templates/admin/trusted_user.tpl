
		    <div class="col-md-9" id="admin-trusted-user">
                <h3>{url type="anchor" url="admin/items" text="Back" attr='class="btn btn-default"'} Trusted User Settings</h3>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class='row'>
                    <p align='justify'>This form allows you to define what makes a 'trusted user'. This is used to determine if a vendor should be allowed to request up-front payment for particular items, or early finalization of escrow orders. </p>
                    <p align='justify'>To ignore a particular attribute simply set it to zero.</p>
                </div>


                {form method="open" action="admin/trusted_user" attr='class="form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="trusted_user_rating">Required rating:</label>
                            <div class="col-xs-7">
                                <input type='text' class='form-control' id='trusted_user_rating' name='trusted_user_rating' value="{$config.trusted_user_rating|escape:"html":"UTF-8"}" />
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field="trusted_user_rating"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="review_count">Review count:</label>
                            <div class="col-xs-7">
                                <input type='text' class='form-control' id='review_count' name='trusted_user_review_count' value="{$config.trusted_user_review_count|escape:"html":"UTF-8"}" />
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field='trusted_user_review_count'}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="completed_orders">Minimum complete orders:</label>
                            <div class="col-xs-7">
                                <input type='text' class='form-control' id='completed_orders' name='trusted_user_order_count' value="{$config.trusted_user_order_count|escape:"html":"UTF-8"}" />
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field='trusted_user_order_count'}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" name='trusted_user_update' value="Update" class="btn btn-primary" />
                                {url type="anchor" url="admin/items" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>
            </div>