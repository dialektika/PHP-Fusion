<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: footer.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

use PHPFusion\PermalinksDisplay;

require_once INCLUDES."footer_includes.php";

define("CONTENT", ob_get_clean()); //ob_start() called in header.php

// Cron Job (6 MIN)
if ($settings['cronjob_hour'] < (time()-360)) {
	dbquery("DELETE FROM ".DB_FLOOD_CONTROL." WHERE flood_timestamp < '".(time()-360)."'");
	dbquery("DELETE FROM ".DB_CAPTCHA." WHERE captcha_datestamp < '".(time()-360)."'");
	dbquery("DELETE FROM ".DB_USERS." WHERE user_joined='0' AND user_ip='0.0.0.0' and user_level='103'");
	dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".time()."' WHERE settings_name='cronjob_hour'");
}
// Cron Job (24 HOUR)
if ($settings['cronjob_day'] < (time()-86400)) {
	$new_time = time();
	dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE notify_datestamp < '".(time()-1209600)."'");
	dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_datestamp < '".(time()-86400)."'");
	dbquery("DELETE FROM ".DB_EMAIL_VERIFY." WHERE user_datestamp < '".(time()-86400)."'");
	$usr_inactive = dbcount("(user_id)", DB_USERS, "user_status='3' AND user_actiontime!='0' AND user_actiontime < '".time()."'");
	if ($usr_inactive) {
		require_once INCLUDES."sendmail_include.php";
		$result = dbquery("SELECT user_id, user_name, user_email FROM ".DB_USERS."
			WHERE user_status='3' AND user_actiontime!='0' AND user_actiontime < '".time()."'
			LIMIT 10");
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$data['user_id']."'");
			$subject = $locale['global_451'];
			$message = str_replace("USER_NAME", $data['user_name'], $locale['global_452']);
			$message = str_replace("LOST_PASSWORD", $settings['siteurl']."lostpassword.php", $message);
			sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
		}
		if ($usr_inactive > 10) {
			$new_time = $settings['cronjob_day'];
		}
	}
	$usr_deactivate = dbcount("(user_id)", DB_USERS, "user_actiontime < '".time()."' AND user_actiontime!='0' AND user_status='7'");
	if ($usr_deactivate) {
		$result = dbquery("SELECT user_id FROM ".DB_USERS."
			WHERE user_actiontime < '".time()."' AND user_actiontime!='0' AND user_status='0'
			LIMIT 10");
		if ($settings['deactivation_action'] == 0) {
			while ($data = dbarray($result)) {
				dbquery("UPDATE ".DB_USERS." SET user_actiontime='0', user_status='6' WHERE user_id='".$data['user_id']."'");
			}
		} else {
			while ($data = dbarray($result)) {
				dbquery("DELETE FROM ".DB_USERS." WHERE user_id='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to='".$data['user_id']."' OR message_from='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_NEWS." WHERE news_name='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_THREADS." WHERE thread_author='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_POSTS." WHERE post_author='".$data['user_id']."'");
				dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE notify_user='".$data['user_id']."'");
			}
		}
		if ($usr_deactivate > 10) {
			$new_time = $settings['cronjob_day'];
		}
	}
	dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$new_time."' WHERE settings_name='cronjob_day'");
}
// Error handling
$footerError = (iADMIN && checkrights("ERRO") && count($_errorHandler) > 0 && !defined('no_debugger'))
	?  "<button title='".$locale['err_102']."' id='turbo_debugger' class='btn btn-sm btn-default m-r-10'><i class='fa fa-bug fa-lg'></i></button> ".str_replace("[ERROR_LOG_URL]", ADMIN."errors.php".$aidlink, $locale['err_101'])." <span class='badge'>".count($_errorHandler)."</span>\n ".fusion_turbo_debugger().""
	: '';

if (!isset($fusion_jquery_tags)) {
	$fusion_jquery_tags = '';
}
require_once __DIR__.(defined('ADMIN_PANEL') ? '/admin_layout.php' : '/layout.php');
$output = ob_get_contents(); //ob_start() called in maincore
if (ob_get_length() !== FALSE) {
	ob_end_clean();
}
// Do the final output manipulation
$output = handle_output($output);

if (!defined("ADMIN_PANEL") && $settings['site_seo']) {
	$output = PermalinksDisplay::getInstance()->getOutput($output);
}
if (isset($permalink)) { unset($permalink); }

// Output the final complete page content
echo $output;

if (ob_get_length() !== FALSE) {
	ob_end_flush();
}

// Shows allocated memory and the actual memory used for generatig a page
//echo "Memory: alloc. ".(memory_get_peak_usage(true)/1024/1024)." MB / used ".number_format((memory_get_peak_usage(false)/1024/1024), 2)." MB";
//var_dump($_SESSION);
?>