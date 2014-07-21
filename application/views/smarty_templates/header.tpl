<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="{$header.site_description|escape:'html':'UTF-8'}" />
    <title>{$header.title|escape:'html':'UTF-8'} | {$header.site_title|escape:'html':'UTF-8'}</title>
    <link rel="stylesheet" type="text/css" href="{url type="site" url="assets/css/bootstrap.css"}">
    <link rel="stylesheet" type="text/css" href="{url type="site" url="assets/css/style.css"}">
    <!-- JavaScript -->
    <script src="{url type="site" url="assets/js/jquery-1.8.1.min.js"}"></script>
    <script src="{url type="site" url="assets/js/bootstrap.js"}"></script>
    {$header.header_meta}
</head>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                {url type="anchor" url="" text=$header.site_title|escape:"html":"UTF-8" attr="class='navbar-brand'"}
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav navbar-right">
                    {if $current_user.user_role eq 'Admin'}
                        <li>{url type="anchor" url="" text="Home" attr="title='Home'"}</li>
                        <li>{url type="anchor" url="items" text="Items" attr="title='Items'"}</li>
                        <li>{url type="anchor" url="admin" text="Admin" attr="title='Admin'"}</li>
                        <li>{url type="anchor" url="inbox" text="Inbox{if $count_unread_messages gt 0} ($count_unread_messages){/if}" attr='title="Inbox"'}</li>
                        <li>{url type="anchor" url="account" text="Account" attr="title='Account'"}</li>
                        <li>{url type="anchor" url="logout" text="Logout" attr="title='Logout'"}</li>

                    {elseif $current_user.user_role eq 'Buyer'}
                        <li>{url type="anchor" url="" text="Home" attr="title='Home'"}</li>
                        <li>{url type="anchor" url="items" text="Items" attr="title='Items'"}</li>
                        <li>{url type="anchor" url="inbox" text="Inbox{if $count_unread_messages gt 0} ($count_unread_messages){/if}" attr='title="Inbox"'}</li>
                        <li>{url type="anchor" url="account" text="Account" attr="title='Account'"}</li>
                        <li>{url type="anchor" url="logout" text="Logout" attr="title='Logout'"}</li>

                    {elseif $current_user.user_role == 'Vendor'}
                        <li>{url type="anchor" url="" text="Home" attr="title='Home'"}</li>
                        <li>{url type="anchor" url="items" text="Items" attr="title='Items'"}</li>
                        <li>{url type="anchor" url="inbox" text="Inbox{if $count_unread_messages gt 0} ($count_unread_messages){/if}" attr='title="Inbox"'}</li>
                        <li>{url type="anchor" url="account" text="Account" attr="title='Account'"}</li>
                        <li>{url type="anchor" url="logout" text="Logout" attr="title='Logout'"}</li>

                    {elseif $current_user.user_role == 'half'}
                        {if $allow_guests eq TRUE}
                            <li>{url type="anchor" url="" text="Home" attr="title='Home'"}</li>
                            <li>{url type="anchor" url="items" text="Items" attr="title='Items'"}</li>
                        {/if}
                        <li>{url type="anchor" url="logout" text="Logout" attr="title='Logout'"}</li>
                    {else}
                        {if $allow_guests eq TRUE}
                            <li>{url type="anchor" url="" text="Home" attr="title='Home'"}</li>
                            <li>{url type="anchor" url="items" text="Items" attr="title='Items'"}</li>
                        {/if}
                        <li>{url type="anchor" url="login" text="Login" attr="title='Login'"}</li>
                        <li>{url type="anchor" url="register" text="Register" attr="title='Register'"}</li>
                    {/if}
                </ul>
            </div>
            <!-- /.navbar-collapse -->

        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Begin: Menu -->
            <div class="col-md-3">
                {if in_array($current_user.user_role, ['guest','half']) eq FALSE}
                <!-- Logged in bar-->
                <div class="list-group">
                    {capture name="t_user_link"}user/{$current_user.user_hash}{/capture}
                    {url type="anchor" url=$smarty.capture.t_user_link text=$current_user.user_name|escape:"html":"UTF-8" attr="class='list-group-item' title='Your Profile'"}

                    {if $current_user['user_role'] eq 'Vendor'}
                        {url type="anchor" url="listings" text="My Listings" attr="class='list-group-item' title='My Listings'"}
                        {url type="anchor" url="orders" text="Orders" attr="class='list-group-item' title='Orders'"}
                    {elseif $current_user['user_role'] eq 'Admin'}
                        {url type="anchor" url="admin/orders" text="Orders" attr="class='list-group-item' title='Orders'"}
                        {url type="anchor" url="admin/disputes" text="Disputes" attr="class='list-group-item' title='Disputes'"}
                    {elseif $current_user['user_role'] eq 'Buyer'}
                        {url type="anchor" url="purchases" text="My Purchases" attr="class='list-group-item' title='Your Purchases'"}
                    {/if}
                </div>
            {/if}

            {if $category_data.block eq FALSE}<div class="well sidebar-nav">
            <ul class="nav nav-list">
                <li class="nav-header">Categories</li>
                {$category_data.cats}
            </ul>
        </div>{/if}

            </div>
