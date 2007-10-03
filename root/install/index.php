<?php
/** 
*
* @package phpBB3
* @version $Id: calendar.php,v 1.000 2007/09/18 10:00:00 Jcc264 Exp $
* @copyright (c) 2007 M and J Media 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$auth_admin = new auth_admin();

// Add permissions
$auth_admin->acl_add_option(array(
    'global'   => array('u_view_event', 'u_new_event', 'u_edit_event', 'u_delete_event', 
						'm_edit_event', 'm_delete_event', 'a_edit_event', 'a_delete_event')
));

$sql = "CREATE TABLE `phpbb_calendar` (
  `event_id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `event_name` varchar(255) collate utf8_bin NOT NULL default '',
  `event_desc` mediumtext collate utf8_bin NOT NULL,
  `event_groups` varchar(255) collate utf8_bin NOT NULL default '',
  `enable_bbcode` tinyint(1) unsigned NOT NULL default '1',
  `enable_html` tinyint(1) unsigned NOT NULL default '1',
  `enable_smilies` tinyint(1) unsigned NOT NULL default '1',
  `bbcode_bitfield` varchar(255) collate utf8_bin NOT NULL default '',
  `bbcode_uid` varchar(5) collate utf8_bin NOT NULL default '',
  `event_start_day` date NOT NULL default '0000-00-00',
  `event_start_time` varchar(10) collate utf8_bin NOT NULL default '0',
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";

$db->sql_query($sql);
trigger_error('Calendar Installed');
?>