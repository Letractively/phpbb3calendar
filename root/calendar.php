<?php
/** 
*
* @package phpBB3
* @version $Id: calendar.php,v ALPHA 3 2007/10/02 10:00:00 Jcc264 Exp $
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
include($phpbb_root_path . 'includes/functions_calendar.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/calendar');

$month_array = genarate_month_array(request_var('month',0));
foreach ( $month_array['build'] as $week)
{
	for($i =0 ; $i<=6; $i++)
	{
		$event_array = get_events($week[$i], $month_array['month'], $month_array['year']);
		$birthday_array = get_birthdays($week[$i], $month_array['month'], $month_array['year']);
		
		if( (count($event_array)>0) || (count($birthday_array)>0) )
		{
			//there is a event going on

			$output_text = '';
			
			if( isset($event_array[1]) )
			{
				$output_text .= '<a href="calendar_view.php?month='.$month_array['month'].'&day='.$week[$i].'&year='.$month_array['year'].'"  title="'.$event_array[1]['event_name'].'">'.mb_substr($event_array[1]['event_name'], 0, 15).(mb_strlen($event_array[1]['event_name'])>15?'...':'').'</a><br>';
			}
			else
			{
				$output_text .= '&nbsp;<br>';
			}
			if( isset($event_array[2]) )
			{
				$output_text .= '<a href="calendar_view.php?month='.$month_array['month'].'&day='.$week[$i].'&year='.$month_array['year'].'" title="'.$event_array[2]['event_name'].'">'.mb_substr($event_array[2]['event_name'], 0, 15).(mb_strlen($event_array[1]['event_name'])>15?'...':'').'</a><br>';
			}
			else
			{
				$output_text .= '&nbsp;<br>';
			}
			if( count($event_array) > 3)
			{
				$output_text .= '<a href="calendar_view.php?month='.$month_array['month'].'&day='.$week[$i].'&year='.$month_array['year'].'"  title="More Events...">More Events...</a><br>';
			}
			elseif( isset($event_array[3]) )
			{
				$output_text .= '<a href="calendar_view.php?month='.$month_array['month'].'&day='.$week[$i].'&year='.$month_array['year'].'"  title="'.$event_array[3]['event_name'].'">'.mb_substr($event_array[3]['event_name'], 0, 15).(mb_strlen($event_array[1]['event_name'])>15?'...':'').'</a><br>';
			}
			else
			{
				$output_text .= '&nbsp;<br>';
			}
			if( count($birthday_array) > 0 )
			{
				$output_text .= '<a href="calendar_view.php?month='.$month_array['month'].'&day='.$week[$i].'&year='.$month_array['year'].'" title="Birthday" >Birthday</a><br>';
			}
			else
			{
				$output_text .= '&nbsp;<br>';
			}		
			
			$week[$i] = array(
				'day' 	=> '<b><a href="calendar_view.php?month='.$month_array['month'].'&day='.$week[$i].'&year='.$month_array['year'].'">'.$week[$i].'</a></b>',
				'event' => $output_text,
			);
		}
		else
		{
			//This is a eventless day
			$week[$i] = array(
				'day' 	=> (isset($week[$i])?$week[$i]:''),
				'event' => '&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>',
			);
		}	
	}

	$template->assign_block_vars('calendar_week', array(
		'DAY0' => $week[0]['day'],
		'DAY1' => $week[1]['day'],
		'DAY2' => $week[2]['day'],
		'DAY3' => $week[3]['day'],
		'DAY4' => $week[4]['day'],
		'DAY5' => $week[5]['day'],
		'DAY6' => $week[6]['day'],
		'DAY0_EVENTS' => $week[0]['event'],
		'DAY1_EVENTS' => $week[1]['event'],
		'DAY2_EVENTS' => $week[2]['event'],
		'DAY3_EVENTS' => $week[3]['event'],
		'DAY4_EVENTS' => $week[4]['event'],
		'DAY5_EVENTS' => $week[5]['event'],
		'DAY6_EVENTS' => $week[6]['event'],
	));
}

$template->assign_vars(array(
	'L_CALENDAR' 	 	=> 'Calendar',
	'L_SUN' 			=> $user->lang['calendar']['long_day'][0],
	'L_MON'				=> $user->lang['calendar']['long_day'][1],
	'L_TUE'				=> $user->lang['calendar']['long_day'][2],
	'L_WED'				=> $user->lang['calendar']['long_day'][3],
	'L_THU'				=> $user->lang['calendar']['long_day'][4],
	'L_FRI'				=> $user->lang['calendar']['long_day'][5],
	'L_SAT'				=> $user->lang['calendar']['long_day'][6],
	'L_CALENDAR_MONTH' 	=> $user->lang['calendar']['long_month'][$month_array['month']]." ".$month_array['year'],
	'U_NEXT_MONTH'		=> append_sid($_SERVER["PHP_SELF"] . "?month=" . (request_var('month',0) + 1)),
	'U_PREV_MONTH' 		=> append_sid($_SERVER["PHP_SELF"] . "?month=" . (request_var('month',0) - 1)),
	
	'IMG_LEFT_ARROW' 	=> '&lt;&lt;',
	'IMG_RIGHT_ARROW' 	=> '&gt;&gt;',
	
	'S_NEW_EVENT' 		=> $auth->acl_get('u_new_event'),
));



$page_title="Calendar";
page_header($page_title);
$template->set_filenames(array(
	'body' => 'calendar_body.html')
);
page_footer();
?>