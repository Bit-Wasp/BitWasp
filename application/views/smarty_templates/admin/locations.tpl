            <div class="col-md-9" id="admin-locations-form">

                <h2>{url type="anchor" url='admin' text='Back' attr='class="btn btn-default"'} Locations</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="col-xs-12">
                    <div class="col-xs-10">
                        When users are entering locations on the site, they can use the
                        default country list, or you can configure the site to a specific
                        area.
                    </div>
                </div>

                <div class="col-xs-12">&nbsp;</div>

                <legend>Location List</legend>

                {form method="open" action="admin/locations" attr='class="form form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="location_source">List</label>
                            <div class="col-xs-6">
                                <select name="location_source" id="location_source" class="form-control">
                                    <option value=""></option>
                                    <option value="Default"{if $list_source == 'Default'} selected="selected"{/if}>Default</option>
                                    <option value="Custom"{if $list_source == 'Custom'} selected="selected"{/if}>Custom</option>
                                </select>
                            </div>
                            <div class="col-xs-3"></div>
                            <div class="col-xs-1"><input type='submit' name='update_location_list_source' value='Submit' class="btn btn-primary"/></div>
                        </div>
                        <div class='col-xs-12 col-md-8 col-md-offset-2'>
                            {form method="form_error" field="location_source"}
                        </div>
                    </div>
                </form>

                <div class='col-xs-12'>&nbsp;</div>

                <legend>Custom Locations</legend>
                {form method="open" action="admin/locations" attr='class="form-horizontal"'}

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="location_source">Add:</label>
                            <div class="col-xs-9">
                                <div class="col-xs-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Name</i></span>
                                        <input type='text' class="form-control" name='create_location' value='' />
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Parent</i></span>
                                        {$locations_parent}
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-1"><input type='submit' name='add_custom_location' value='Submit' class="btn btn-primary"/></div>
                        </div>
                        <div class='col-xs-12 col-md-8 col-md-offset-2'>{form method="form_error" field="create_location"}{form method='form_error' field='location'}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="location_delete">Delete:</label>
                            <div class="col-xs-9">
                                <div class="col-xs-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Name</i></span>
                                        {$locations_delete}
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-1"><input type="submit" name="delete_custom_location" value="Submit" class="btn btn-primary" /></div>
                        </div>
                        <div class='col-xs-12 col-md-8 col-md-offset-2'>{form method="form_error" field="location_delete"}</div>
                    </div>
                </form>

                <legend>List Preview</legend>
                    {$locations_human_readable}

            </div>