<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('vj_marquee_banner_options');
delete_option('elessi_topbar_banner_options');
delete_site_option('vj_marquee_banner_options');
delete_site_option('elessi_topbar_banner_options');
