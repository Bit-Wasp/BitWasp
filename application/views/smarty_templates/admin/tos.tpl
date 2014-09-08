            <div class="col-md-9" id="admin-tos-form">

                <h2>{url type="anchor" url='admin' text='Back' attr='class="btn btn-default"'} Terms Of Service</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="col-xs-12">
                    <div class="col-xs-10">Here you can set whether a terms of service must be agreed to before
                        users can register an account. Once the setting is enabled, the terms of service
                        agreement can be edited.
                    </div>
                </div>

                {form method="open" action="admin/tos" attr='class="form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label for="terms_of_service" class="control-label col-xs-3">Terms of Service</label>
                            <div class="col-xs-9"><textarea class="form-control" name="terms_of_service" id="terms_of_service" rows="7" >{$tos|escape:"html":"UTF-8"}</textarea></div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">
                            {form method="form_error" field="terms_of_service"}
                        </div>
                    </div>

                    <div class='form-group'>
                        <div class="col-xs-12">
                            <label for="terms_of_service_toggle" class="control-label col-xs-3">Display TOS?</label>
                            <div class="col-xs-7">
                                <label class="radio-inline">
                                    <input type="radio" id="terms_of_service_toggle" name="terms_of_service_toggle" value="0" {if $config.terms_of_service_toggle == FALSE}checked="checked"{/if} /> Disabled
                                </label>

                                <label class="radio-inline">
                                    <input type='radio' id="terms_of_service_toggle" name='terms_of_service_toggle' value='1' {if $config.terms_of_service_toggle == TRUE}checked="checked"{/if}/> Enabled
                                </label
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">
                            {form method="form_error" field="terms_of_service_toggle"}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                        <div class="col-sm-5 col-lg-5 col-md-5">
                            <p align="center">
                                <input type='submit' name='tos_update' value='Update' class='btn btn-primary' />
                                {url type="anchor" url="admin/edit" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>
            </div>