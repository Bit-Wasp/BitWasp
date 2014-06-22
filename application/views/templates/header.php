<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta name="description" content="<?php echo $site_description; ?>" />
            <title><?php echo $title; ?> | <?php echo $site_title ;?></title>
            <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/bootstrap.css">
            <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css">

            <style type="text/css">
            body {
                padding-top: 60px;
                padding-bottom: 40px;
              }
              .sidebar-nav {
                padding: 9px 0;
              }
            </style>
            <?php echo $header_meta; ?>
        </head>
        <body>
            <div class="navbar navbar-inverse navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="container-fluid">

                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>

                        <a class="brand" href="<?php echo site_url(); ?>"><?php echo $site_title; ?><?php echo ($maintenance_mode == TRUE) ? ' (maintenance mode)' : ''; ?></a>
                        <a class="brand" href="<?php echo site_url(); ?>"><?php echo $site_title; ?>
                        <?php echo ($maintenance_mode == TRUE) ? ' (maintenance mode)' : ''; ?></a>

<?php   if($role == 'admin') {  ?>
                        <div>
                            <ul>
                                <li><?php echo anchor('', 'Home', 'title="Home"'); ?></li>
                                <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
                                <li><?php echo anchor('admin', 'Admin', 'title="Admin"'); ?></li>
                                <?php $inbox_str = 'Inbox'; if($count_unread_messages > 0) $inbox_str .= " ($count_unread_messages)"; ?>
                                <li><?php echo anchor('inbox', $inbox_str, 'title="Inbox"'); ?></li>
                                <li><?php echo anchor('account', 'Account', 'title="Account"'); ?></li>
                                <li><?php echo anchor('logout', 'Logout', 'title="Logout"'); ?></li>
                            </ul>
                        </div>
<?php   } else if($role == 'buyer') { ?>
                        <div>
                            <ul>
                                <li><?php echo anchor('', 'Home', 'title="Home"');?></li>
                                <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
                                <?php $inbox_str = 'Inbox'; if($count_unread_messages > 0) $inbox_str .= " ($count_unread_messages)"; ?>
                                <li><?php echo anchor('inbox', $inbox_str, 'title="Inbox"'); ?></li>
                                <li><?php echo anchor('account', 'Account', 'title="Account"');?></li>
                                <li><?php echo anchor('logout', 'Logout', 'title="Logout"');?></li>
                            </ul>
                        </div>
<?php   } else if($role == 'vendor') { ?>
                        <div>
                            <ul>
                                <li><?php echo anchor('', 'Home', 'title="Home"'); ?></li>
                                <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
                                <?php $inbox_str = 'Inbox'; if($count_unread_messages > 0) $inbox_str .= " ($count_unread_messages)"; ?>
                                <li><?php echo anchor('inbox', $inbox_str, 'title="Inbox"'); ?></li>
                                <li><?php echo anchor('account', 'Account', 'title="Account"'); ?></li>
                                <li><?php echo anchor('logout', 'Logout', 'title="Logout"'); ?></li>
                            </ul>
                        </div>
<?php   } else if($role == 'half') { ?>
                        <div>
                            <ul>
                                <?php
                                if($allow_guests == TRUE) { ?>
                                    <li><?php echo anchor('', 'Home', 'title="Home"'); ?></li>
                                    <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
                                <?php } ?>

                                <li><?php echo anchor('logout', 'Logout', 'title="Logout"');?></li>
                            </ul>
                        </div>
<?php   } else if($role == 'guest') { ?>
                        <div>
                            <ul>
                                <?php if($allow_guests == TRUE){ ?>
                                <li><?php echo anchor('', 'Home', 'title="Home"'); ?></li>
                                <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
                                <?php } ?>
                                <li><?php echo anchor('login', 'Login', 'title="Login"'); ?></li>
                                <li><?php echo anchor('register', 'Register', 'title="Register"');?></li>
                            </ul>
                        </div>
<?php   } ?>
                    </div>
                </div>
            </div>
            <!-- End: Header -->
