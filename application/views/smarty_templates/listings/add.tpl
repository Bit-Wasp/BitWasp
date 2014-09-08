            <div class="col-md-9" id="add_item">
                <h2>Add Item</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action="listings/add" attr=['class'=>'form-horizontal']}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label for="name" class="control-label col-xs-2">Name</label>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Name">
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="name"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label for="name" class="control-label col-xs-2">Description</label>
                            <div class="col-xs-5">
                                <textarea class="form-control" name='description' placeholder="Description"></textarea>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="description"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="price">Price</label>
                            <div class="col-xs-5">
                                <div class="input-group">
                                    <span class="input-group-addon"><i>{$current_user.currency.code}</i></span>
                                    <input type='text' class="form-control" name='price' value="{form method="set_value" field="price"}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="price"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label for="name" class="control-label col-xs-2">Category</label>
                            <div class="col-xs-5">
                                {$categories}
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="category"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="ship_from">Ship From</label>
                            <div class="col-xs-5">
                                {$locations}
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="ship_from"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="hidden">Invisible Listing</label>
                            <div class="col-xs-5">
                                <select name='hidden' class='form-control' autoselection='off'>
                                    <option value=''></option>
                                    <option value='0'>No</option>
                                    <option value='1'>Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="hidden"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="prefer_upfront">Prefer up-front payment?</label>
                            <div class="col-xs-5">
                                <select name='prefer_upfront' class="form-control" autoselection='off'>
                                    <option value=''></option>
                                    <option value='0'>No</option>
                                    <option value='1'>Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="prefer_upfront"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" value="Create" class="btn btn-primary" />
                                {url type="anchor" url="listings" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>
            </div>
