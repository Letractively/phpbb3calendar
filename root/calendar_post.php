<?php
/** 
*
* @package phpBB3
* @version $Id: calendar_post.php,v ALPHA 3 2007/10/02 10:00:00 Jcc264 Exp $
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
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_calendar.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('posting', 'mods/calendar'));
$user_id = $user->data['user_id'];

//Permisions
$POST_NEW = $auth->acl_get('u_new_event');
$EDIT_SELF = $auth->acl_get('u_edit_event');
$DELETE_SELF = $auth->acl_get('u_delete_event');
$EDIT_OTHERS = ($auth->acl_get('m_edit_event') || $auth->acl_get('a_edit_event'));
$DELETE_OTHERS = ($auth->acl_get('m_delete_event') || $auth->acl_get('a_delete_event'));

$event_id = request_var('event_id',0);	
$sql = "SELECT * FROM phpbb_calendar 
		WHERE event_id='$event_id'";
$result = $db->sql_query($sql);
$event_row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);	
$poster_id = (isset($event_row['user_id'])?$event_row['user_id']:$user_id);
$IS_EDIT = (empty($event_row))?false:true;

//Kll people who dont belong here
if($IS_EDIT && (!$EDIT_OTHERS || (!$EDIT_SELF && $poster_id == $user_id)))
{
	//This kills edit attemps with no permisions
	header('Location:calendar.php');
	exit;
}
if(!$IS_EDIT && !$POST_NEW)
{
	//Kills new post attempt
	header('Location:calendar.php');
	exit;
}
if(isset($_POST['cancel']))
{
	//returns after a post cancel
	header('Location:calendar.php');
	exit;
}

//$template->assign_var('S_NEW_MESSAGE', true);

//Get users group info
$sql = 'SELECT ug.group_id, g.group_name FROM ' . USER_GROUP_TABLE . '  ug
		INNER JOIN ' . GROUPS_TABLE . ' g
		ON ug.group_id=g.group_id
		WHERE ug.user_id='.$poster_id.' AND ug.user_pending!=1
		ORDER BY ug.group_id';
$result = $db->sql_query($sql);
$user_groups = array();
$user_groups[-1] = 'Personal';
$user_groups[0] = 'Public';
while($grow = $db->sql_fetchrow($result))
{
	$user_groups[$grow['group_id']] = $grow['group_name'];
}
$db->sql_freeresult($result);
//*******************************************************************************************************************************
//Check for delete requests
if((request_var('delete',0) == 1) && $IS_EDIT && ($DELETE_OTHERS || (!$DELETE_SELF && $poster_id == $user_id)))
{
		$sql = "DELETE FROM phpbb_calendar WHERE event_id=$event_id";
		$message = 'This event has been deleted successfully.';
		$db->sql_query($sql);
		$meta_info = 'calendar.php';
		meta_refresh(3, $meta_info);
		trigger_error($message);
		exit;
}
//*******************************************************************************************************************************
//User is posting a edit or a new event
if(isset($_POST['post']))
{	
	//Check for missing information
	$event_name = utf8_normalize_nfc(request_var('name','', true));
	if($event_name == '')
	{
		$error[]=(($IS_EDIT)?$user->lang['calendar_edit_name_error']:$user->lang['calendar_post_name_error']);
	}		
	if(!$event_start_time = gen_db_time(request_var('time','')))
	{
		$error[]=(($IS_EDIT)?$user->lang['calendar_edit_time_error']:$user->lang['calendar_post_time_error']);
	}	
	if(!$event_start_date = gen_db_date(request_var('date','')))
	{
		$error[]=(($IS_EDIT)?$user->lang['calendar_edit_date_error']:$user->lang['calendar_post_date_error']);
	}
	$event_desc = utf8_normalize_nfc(request_var('message', '', true));
	if($event_desc == '')
	{
		$error[]=(($IS_EDIT)?$user->lang['calendar_edit_desc_error']:$user->lang['calendar_post_desc_error']);
	}
	
	if (isset($error))
	{
		if ($IS_EDIT)
		{
			//this is a edit
			$template->assign_vars(array(
				'S_HIDDEN_FIELDS'		=> '<input type="hidden" name="event_id" value="'.$event_id.'">',
				'S_EDIT'				=> true,						
			));
		}
	
		$posted_groups = (empty($_POST['group'])?array():$_POST['group']);
		foreach($user_groups as $g_num => $g_name)
		{
			$template->assign_block_vars('group_row',array(	
				'GROUP_ID'		=> $g_num,
				'GROUP_NAME'	=> ucwords(strtolower( str_replace('_', ' ',$g_name))),
				'GROUP_SELECT'	=> (in_array($g_num, $posted_groups)?'selected':''),
			));
		}
		
		$template->assign_vars(array(
			'ERROR' 				=> implode("<br/>", $error),
			'NUM_GROUPS'			=> count($user_groups),
			'S_SMILIES_CHECKED'		=> (request_var('disable_smilies',0)) ? ' checked="checked"':'',	
			'S_BBCODE_CHECKED'		=> (request_var('disable_bbcode',0)) ? ' checked="checked"':'',	
			'S_MAGIC_URL_CHECKED'	=> (request_var('disable_magic_url',0)) ? ' checked="checked"':'',	
			'SUBJECT'				=> $event_name,
			'DATE_IN'				=> gen_display_date($event_start_date),
			'TIME_IN'				=> gen_display_time($event_start_time),
			'MESSAGE'				=> $event_desc,

		));
		
	}
	else
	{
		//no error 
		//put event to db
		if (empty($_POST['group']))
		{
			$groups = 0;
		}
		else
		{
			$groups=implode(";", $_POST['group']);
		}
		
		$smilies	= (request_var('disable_smilies',0)?false:true);	
		$bbcode 	= (request_var('disable_bbcode',0)?false:true);	
		$urls		= (request_var('disable_magic_url',0)?false:true);	
	
		$text = utf8_normalize_nfc(request_var('message', '', true));
		
		$bitfield = $options = $bbcode_user_id = ''; // will be modified by generate_text_for_storage

		generate_text_for_storage($text, $bbcode_user_id, $bitfield, $options, $bbcode, $urls, $smilies);
	
		$sql_ary = array(
			'user_id'			=> $poster_id,
			'event_name'		=> $event_name,	
			'event_desc'   		=> $text,
			'event_groups'		=> $groups,
			'enable_bbcode'     => $bbcode,
			'enable_html'       => $urls,
			'enable_smilies'    => $smilies,
			'bbcode_uid'        => $bbcode_user_id,
			'bbcode_bitfield'   => $bitfield,
			'event_start_day'	=> $event_start_date,
			'event_start_time'	=> $event_start_time,
		);
		if ($IS_EDIT)
		{
			$sql = 'UPDATE phpbb_calendar
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE event_id = ' . $event_id;
			$message = 'This event has been edited successfully.';
		}
		else
		{
			$sql = 'INSERT INTO phpbb_calendar ' . $db->sql_build_array('INSERT', $sql_ary);
			$message = 'This event has been posted successfully.';
		}
		$db->sql_query($sql);
		$meta_info = 'calendar.php';
		meta_refresh(3, $meta_info);
		trigger_error($message);
		exit;
	}
}
//*******************************************************************************************************************************
else
{
	if($IS_EDIT)
	{
		//Display edit only information
		
		decode_message($event_row['event_desc'], $event_row['bbcode_uid']);
		
		$event_groups = explode(';',$event_row['event_groups']);

		foreach($user_groups as $g_num => $g_name)
		{
			$template->assign_block_vars('group_row',array(	
				'GROUP_ID'		=> $g_num,
				'GROUP_NAME'	=> ucwords(strtolower( str_replace('_', ' ',$g_name))),
				'GROUP_SELECT'	=> (in_array($g_num, $event_groups)?'selected':''),
			));
		}		
		
		$template->assign_vars(array(
			'NUM_GROUPS'			=> count($user_groups),
			'S_SMILIES_CHECKED'		=> ($event_row['enable_smilies']) ? '':' checked="checked"',	
			'S_BBCODE_CHECKED'		=> ($event_row['enable_bbcode']) ? '':' checked="checked"',	
			'S_MAGIC_URL_CHECKED'	=> ($event_row['enable_html']) ? '':' checked="checked"',	
			'S_EDIT'				=> true,
			
			'SUBJECT'				=> $event_row['event_name'],
			'DATE_IN'				=> gen_display_date($event_row['event_start_day']),
			'TIME_IN'				=> gen_display_time($event_row['event_start_time']),
			'MESSAGE'				=> $event_row['event_desc'],
			'S_HIDDEN_FIELDS'		=> '<input type="hidden" name="event_id" value="'.$event_id.'">',
		));	
	}
//*******************************************************************************************************************************
	else
	{
		$event_groups = explode(';',$event_row['event_groups']);
		
		foreach($user_groups as $g_num => $g_name)
		{
			$template->assign_block_vars('group_row',array(	
				'GROUP_ID'		=> $g_num,
				'GROUP_NAME'	=> ucwords(strtolower( str_replace('_', ' ',$g_name))),
			));
		}
		$template->assign_vars(array(
			'NUM_GROUPS'			=> count($user_groups),
			'S_SMILIES_CHECKED'		=> ($smilies_checked) ? ' checked="checked"' : '',	
			'S_BBCODE_CHECKED'		=> ($bbcode_checked) ? ' checked="checked"' : '',	
			'S_MAGIC_URL_CHECKED'	=> ($urls_checked) ? ' checked="checked"' : '',	
		));	
	}
}
	
$smilies_status	= ($config['allow_smilies']) ? true : false;
$bbcode_status	= ($config['allow_bbcode']) ? true : false;
$url_status		= ($config['allow_post_links']) ? true : false;

$template->assign_vars(array(	
	'U_POST_ACTION' 		=> 'calendar_post.php',
	
	'S_BBCODE_ALLOWED'		=> $bbcode_status,	
	'S_LINKS_ALLOWED'		=> $url_status,	
	'S_SMILIES_ALLOWED' 	=> $smilies_status,
				
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'URL_STATUS'			=> ($url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
		
	'L_ADD_EVENT'			=> $user->lang['calendar_add_event'], 
	'L_DELETE_EVENT'		=> $user->lang['calendar_delete_event'], 
	'L_DELETE_EVENT_WARN'	=> $user->lang['calendar_delete_warn'],
	'L_EVENT_NAME'			=> $user->lang['calendar_event_name'], 
	'L_EVENT_DESC'			=> $user->lang['calendar_event_desc'], 
	'L_EVENT_DESC_EXPLAIN'	=> $user->lang['calendar_event_desc_exp'], 
	'L_EVENT_START'			=> $user->lang['calendar_event_start'],
	'L_EVENT_END'			=> $user->lang['calendar_event_end'],
));

generate_smilies('inline', 1);

//Genarate the Page
$page_title="Calendar";
page_header($page_title);
$template->set_filenames(array(
	'body' => 'calendar_post.html')
);
page_footer();
?>