<?php
/** 
*
* @package phpBB3
* @version $Id: calendar_view.php,v ALPHA 3 2007/10/02 10:00:00 Jcc264 Exp $
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
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_calendar.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/calendar');

$day = request_var('day',0); 
$month = request_var('month',0);
$year = request_var('year',0);

if( checkdate($month, $day, $year) )
{
	//SEND DATE TO TEMPLATE
	$template->assign_vars(array(
		'I_MONTH' 	=> $month,
		'I_DAY'		=> $day,
		'I_YEAR'	=> $year,

		'PM_IMG' 			=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
		'EMAIL_IMG' 		=> $user->img('icon_contact_email', 'SEND_EMAIL'),
		'WWW_IMG' 			=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
		'MSN_IMG' 			=> $user->img('icon_contact_msnm', 'MSNM'),
		'ICQ_IMG' 			=> $user->img('icon_contact_icq', 'ICQ'),
		'YIM_IMG' 			=> $user->img('icon_contact_yahoo', 'YIM'),
		'AIM_IMG' 			=> $user->img('icon_contact_aim', 'AIM'),
		'JABBER_IMG'		=> $user->img('icon_contact_jabber', 'JABBER') ,
		
		'EDIT_IMG' 			=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_POST'),
		
	));
	
	//Get Events
	$events_array = get_events($day, $month, $year);
	if (count($events_array) == 0)
	{
		$template->assign_var('S_NO_EVENTS', true);
	}
	else
	{
		foreach ($events_array as $row)
		{

			$template->assign_block_vars('event_row', array(
				'NAME'			=> $row['event_name'],
				'DESC' 			=> $row['DESC'],
				'AUTHOR'		=> $row['USERNAME_FULL'],
				'POSTER_ID'		=> $row['user_id'],
				'START_TIME'	=> $row['START_TIME'],
				'START_DATE'	=> $row['START_DATE'],
				'EVENT_ID'		=> $row['event_id'],
				'POSTER_AVATAR'	=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'POSTER_POSTS'	=> $row['user_posts'], 
				'POSTER_JOINED'	=> $user->format_date($row['user_regdate']),
				'POSTER_FROM'	=> (!empty($row['user_from'])) ? $row['user_from'] : '',
				
				'U_EMAIL'		=> (!empty($row['user_allow_viewemail']) || $auth->acl_get('a_email'))?($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=".$row['user_id']) : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']):'',	
				'U_PM'			=> ($row['user_id'] != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['user_id']) : '',
				'U_WWW'			=> $row['user_website'],
				'U_AIM'			=> ($row['user_aim'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=aim&amp;u=".$row['user_id']) : '',
				'U_MSN'			=> ($row['user_msnm'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=msnm&amp;u=".$row['user_id']) : '',
				'U_YIM'			=> ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '',
				'U_ICQ'			=> (!empty($row['user_icq']))?'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq']:'',
				'U_JABBER'		=> ($row['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=".$row['user_id']) : '',
				
				'S_EDIT'		=> (((($user->data['user_id']==$row['USER_ID']) && ($auth->acl_get('u_edit_event'))) || ($auth->acl_get('m_edit_event')) || ($auth->acl_get('a_edit_event')))?true:false),
				'S_DELETE'		=> (((($user->data['user_id']==$row['USER_ID']) && ($auth->acl_get('u_delete_event'))) || ($auth->acl_get('m_delete_event')) || ($auth->acl_get('a_delete_event')))?true:false),
			));

	

	/*

	
	RANK_TITLE
	RANK_IMG
	
	*/

		}
	}
	
	//Get Birthday 
	$birthday_array = get_birthdays($day, $month, $year);
	if (count($birthday_array) == 0)
	{
		$template->assign_var('S_NO_BIRTHDAY', true);
	}
	else
	{
		foreach ($birthday_array as $row)

		$template->assign_block_vars('birthday_row', array(
			'USERNAME'		=> $row['USERNAME'],
			'AGE' 			=> $row['AGE'],
		));	
	}
}
else
{
	header('Location:calendar.php');
}

//Genarate the Page
$page_title="Calendar";
page_header($page_title);
$template->set_filenames(array(
	'body' => 'calendar_view.html')
);
page_footer();

?>