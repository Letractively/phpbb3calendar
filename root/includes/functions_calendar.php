<?php
/** 
*
* @package phpBB3
* @version $Id: functions_calendar.php,v ALPHA 3 2007/09/18 10:00:00 Jcc264 Exp $
* @copyright (c) 2007 M and J Media 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

//Build a 2-d array with the month layout
function genarate_month_array($month_offset)
{
	//Get information about today
	$today_is = getdate();
	
	//What month are we building for
	$months_since_zeroAD = $today_is['mon'] + (12 * $today_is['year']) + $month_offset;
	
	$build_year = floor($months_since_zeroAD/12);
	$build_month = $months_since_zeroAD-($build_year*12);
	
	if($build_month == 0 ) 
	{
		$build_month=12; 
	}
	
	//Find First day of the month
	$first_day = date('w', mktime(0, 0, 0, $build_month, 1, $build_year));
	
	$week_day=$first_day;
	
	//Number of days this month
	$num_days= date('t', mktime(0, 0, 0, $build_month, 1, $build_year));
	
	$week=1;
	$day=1;
	
	$month = array();
	$month[$week] = array();
	
	while( $day <= $num_days )
	{
		if ($week_day==7) 
		{ 
			$week_day=0; 
			$week++;
			$month[$week] = array();
		}
	
		$month[$week][$week_day] = $day;
	
		$day++;
		$week_day++;
	}
	$return_array = array();
	$return_array['build'] = $month;
	$return_array['month'] = $build_month;
	$return_array['year'] = $build_year;
	
	return $return_array;
}


function get_events($day, $month, $year, $user_data=false)
{
	global $db, $user, $auth;
	$user_id = $user->data['user_id'];
	
	if ($day<10)
	{
		$day = '0'.$day;
	}
	if ($month<10)
	{
		$month = '0'.$month;
	}
	//Get Groups user is in atm
	$sql = 'SELECT group_id  FROM ' . USER_GROUP_TABLE . '
			WHERE user_id='.$user->data['user_id'].' AND user_pending!=1';
	$result = $db->sql_query($sql);
	$groups = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$groups[$row['group_id']]=true;
	}
	
	$sql = "SELECT * FROM phpbb_calendar c
			INNER JOIN ". USERS_TABLE ." u
			ON c.user_id=u.user_id
			WHERE c.event_start_day='$year-$month-$day' 
			ORDER BY c.event_start_time ASC";
			
			
	$result = $db->sql_query($sql);
	$event_array = array();
	$event_count=0;
	while ($row = $db->sql_fetchrow($result))
	{
		$event_groups = array_flip(split(';', $row['event_groups']));
		foreach($event_groups as $key => $value)
		{
			$event_groups[$key]=true;
		}
				
		if($auth->acl_get('u_view_event') && ( ($event_groups[-1]==true && $user_id==$row['user_id']) || $event_groups[0]==true || count(array_intersect_assoc($groups, $event_groups))>0) )
		{
			$event_count++;
			$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
	
			$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] . '" class="username-coloured"' : '';
			$username_full = ($row['user_type'] != USER_IGNORE) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : '<span' . $user_colour . '>' . $row['username'] . '</span>';
	
			$event_array[$event_count]=array_merge($row, array(
				'DESC' 			=> generate_text_for_display($row['event_desc'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']),
				'USERNAME_FULL'	=> $username_full,
				'START_TIME'	=> $row['event_start_time'],
				'START_DATE'	=> $month.'/'.$day.'/'.$year,
			));	
		}
	}
	
	$db->sql_freeresult($result);
	return $event_array;
}

function get_birthdays($day, $month, $year)
{
	global $db;
	if ($day<10)
	{
		$day = '0'.$day;
	}
	if ($month<10)
	{
		$month = '0'.$month;
	}
	
	$sql = 'SELECT user_id, username, user_colour, user_birthday
		FROM ' . USERS_TABLE . "
		WHERE user_birthday LIKE '" . $db->sql_escape(sprintf('%2d-%2d-', intval($day), intval($month) )) . "%'
		AND user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
	$result = $db->sql_query($sql);
	$birthday_array = array();
	$birthday_count=0;
	while ($row = $db->sql_fetchrow($result))
	{
		$birthday_count++;
		$birthday_array[$birthday_count]=array(
			'USERNAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'AGE' 			=> date("Y") - intval(substr($row['user_birthday'], -4)) ,
		);
		
	}
	$db->sql_freeresult($result);
	return $birthday_array;
}

function gen_db_time($input_time)
{
		list($hour, $min, $ampm) = split('[: ]', $input_time);	

		$hour = intval($hour);
		$min = intval($min);
			
		if(($hour > 12 && $hour < 1)||($min > 59 && $min < 0))
		{
			return false;
		}

		if(($ampm != 'AM') && ($ampm != 'PM'))
		{
			return false;
		}
		elseif($ampm == 'PM')
		{
			$hour = $hour + 12;
		}	

		if ($hour <10)
		{
			$hour = '0'.$hour;
		}
		if ($min <10)
		{
			$min = '0'.$min;
		}
		return $hour . ':' . $min .':00';
}

function gen_db_date($input_date)
{
	list($month, $day, $year) = split('[-]', $input_date);
	
	if(isset($day) && isset($month) && isset($year))
	{
		return $year.'-'.$month.'-'.$day;
	}
	return false;
}

function gen_display_date($input_date)
{
	list($year, $month, $day ) = split('[-]', $input_date);
	if(isset($day) && isset($month) && isset($year))
	{
		return $month . '-' . $day . '-' . $year;
	}
	return false;
}

function gen_display_time($input_time)
{
	list($hour, $min, $sec) = split('[:]', $input_time);
	if(isset($hour) && isset($min))
	{
		$hour = intval($hour);
		$min = intval($min);
	
		if(($hour > 12 && $hour < 1)||($min > 59 && $min < 0))
		{
			return false;
		}
		if ($hour >12)
		{
			$hour = $hour-12;
			$ampm = 'PM';
		}
		else
		{
			$ampm = 'AM';
		}
		if ($hour <10)
		{
			$hour = '0'.$hour;
		}
		if ($min <10)
		{
			$min = '0'.$min;
		}
		if(isset($hour) && isset($min) && isset($ampm))
		{
			return $hour . ':' . $min . ' ' . $ampm;
		}
	}
	return false;
}
			

?>