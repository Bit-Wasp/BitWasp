            <div class="col-md-9" id="edit_general">
                {$nav}

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <legend>Categories</legend>
                {form method="open" action="admin/edit/items" attr='class="form-horizontal"'}

                    <div class="form-group">

                        <div class="col-xs-12">
                            <label class="control-label col-md-1">Add:</label>
                            <div class="col-md-9">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Name</i></span>
                                        <input type='text' class="form-control" name='create_name' value='' />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Parent</i></span>
                                        {$categories_add_select}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2"><input type='submit' name='add_category' value='Add' class='btn btn-primary btn-block' /></div>
                        </div>
                        <div class="col-xs-8 col-xs-offset-1">{form method="form_error" field="create_name"}
                            {form method="form_error" field="category_parent"}</div>
                    </div>


                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-md-1">Rename:</label>
                            <div class="col-md-9">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Name</i></span>
                                        {$categories_rename_select}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>New</i></span>
                                        <input type='text' name='category_name' class="form-control" value='' />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2"><input type='submit' name='rename_category' value='Rename' class='btn btn-primary btn-block' /></div>
                        </div>
                        <div class="col-xs-8 col-xs-offset-1">{form method="form_error" field="rename_id"}
                            {form method='form_error' field='category_name'}</div>

                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-md-1">Delete:</label>
                            <div class="col-md-9">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i>Name</i></span>
                                        {$categories_delete_select}
                                    </div>
                                </div>
                                <div class="col-md-6">

                                </div>
                            </div>
                            <div class="col-md-2"><input type='submit' name='delete_category' value='Delete' class='btn btn-primary btn-block' /></div>
                        </div>
                        <div class="col-xs-8 col-xs-offset-1">{form method="form_error" field="delete_id"}</div>
                    </div>

                </form>
            </div>