<?php
/**
*
* common [English]
*
* @package language
* @version $Id: calendar.php,v ALPHA 3 2007/10/02 12:00:00 jcc264 Exp $
* @copyright (c) 2007 M and J Media 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
 
'calendar'=> array(
		'day'=> array(
			0 => 'Su', 
			1 => 'Mo', 
			2 => 'Tu', 
			3 => 'We', 
			4 => 'Th', 
			5 => 'Fr',
			6 => 'Sa',
			),
			
 		'long_day'=> array(
			0 => 'Sunday', 
			1 => 'Monday', 
			2 => 'Tuesday', 
			3 => 'Wednesday', 
			4 => 'Thursday', 
			5 => 'Friday',
			6 => 'Saturday',
			),
			
		'month'=> array(
			1 => 'Jan',
			2 => 'Feb', 
			3 => 'Mar',
			4 => 'Apr',
			5 => 'May', 
			6 => 'Jun', 
			7 => 'Jul', 
			8 => 'Aug', 
			9 => 'Sep', 
			10 => 'Oct', 
			11 => 'Nov', 
			12 => 'Dec',
		), 

		'long_month'=> array(
			1 => 'January',
			2 => 'February', 
			3 => 'March',
			4 => 'April',
			5 => 'May', 
			6 => 'June', 
			7 => 'July', 
			8 => 'August', 
			9 => 'September', 
			10 => 'October', 
			11 => 'November', 
			12 => 'December',
		), 
	),

'calendar_add_event' 		=> 'Add Event',
'calendar_delete_event' 	=> 'Delete Event',
'calendar_delete_warn'		=> 'Once deleted the event cannot be recovered',
'calendar_event_name' 		=> 'Event Name',
'calendar_event_desc'		=> 'Event Description',
'calendar_event_desc_exp'	=> 'Enter your event description here, it may contain no more than 255 characters',
'calendar_event_end'		=> 'Event End',
'calendar_event_start'		=> 'Event Start',
'calendar_upcoming_event' 	=> 'Upcoming Events',

'calendar_post_name_error'	=> 'You must specify an event name when posting a new event.',
'calendar_edit_name_error'	=> 'You must specify an event name when editing an event.',
'calendar_post_time_error'	=> 'You must specify a valid event start time when posting a new event.',
'calendar_edit_time_error'	=> 'You must specify a valid event start time when editing an event.',
'calendar_post_date_error'	=> 'You must specify a valid event start date when posting a new event.',
'calendar_edit_date_error'	=> 'You must specify a valid event start date when editing an event.',
'calendar_post_desc_error'	=> 'You must include an event description when posting a new event.',
'calendar_edit_desc_error'	=> 'You must include an event description when editing an event.',

));

?>