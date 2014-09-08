            {capture name="t_delete_url"}admin/delete_item/{$item.hash}{/capture}
            {capture name="t_cancel_url"}item/{$item.hash}{/capture}
            <div class="col-md-9" id="admin_delete_item">

                <h2>Remove Item: {$item.name|escape:"html":"UTF-8"}</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action=$smarty.capture.t_delete_url attr='class="form-horizontal"'}

                    <p>Complete the following form to inform {$item.vendor.user_name|escape:"html":"UTF-8"} why this listing is going to be removed.</p>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="reason_for_removal">Reason for Removal</label>
                            <div class="col-xs-7">
                                <textarea name="reason_for_removal" id="reason_for_removal" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            {form method='form_error' field='reason_for_removal'}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="submit"></label>
                            <div class="col-xs-5">
                                <p align="center">
                                    <button type='submit' class="btn btn-primary">Remove</button>
                                    {url type="anchor" url=$smarty.capture.t_cancel_url text="Cancel" attr='class="btn btn-default"'}
                                </p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
