var now = new Date;
var current_day = now.getDay();
var current_month=now.getMonth();
var current_year=now.getFullYear();

var current_mins='00';
var current_hour='01';
var current_ampm='AM';

var month_array=new Array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
var month_days=new Array('31','28','31','30','31','30','31','31','30','31','30','31');
var month_days_leap=new Array('31','29','31','30','31','30','31','31','30','31','30','31');

//Event cetcher
document.addEventListener('click',check_click,false);

function check_click(event_obj) 
{
	s=event_obj.target;
	while(s) {
		if (s==document.getElementById('date_java')) 
		{
			time_close();
			return;
		}
		if (s==document.getElementById('time_java')) 
		{
			date_close();
			return;
		}
		s=s.parentNode;
	}
	time_close();
	date_close();
}

//Show Hide Functions
function time_show(obj)
{
	date_close();
	document.getElementById('time_java').style.left=Left(obj)+"px";
	document.getElementById('time_java').style.top=Top(obj)+"px";
	document.getElementById('time_java').style.display='';
}

function time_close()
{
	document.getElementById('time_java').style.display='none';
}

function date_show(obj)
{
	time_close();	
	prepcalendar(current_day, current_month, current_year);
	document.getElementById('date_java').style.left=Left(obj)+"px";
	document.getElementById('date_java').style.top=Top(obj)+"px";
	document.getElementById('date_java').style.display='';
}

function date_close()
{
	document.getElementById('date_java').style.display='none';
}

//Positioning functions
function Left(obj)
{
    var offsetLeft = 0;
    while (obj) 
	{
        offsetLeft += obj.offsetLeft;
        obj = obj.offsetParent;
    }
	return offsetLeft;
}

function Top(obj)
{
    var offsetTop = 20;
    while (obj) 
	{
        offsetTop += obj.offsetTop;
        obj = obj.offsetParent;
    }
	return offsetTop;
}

//Display functions
function prepcalendar(display_day,display_month,display_year) {
	td=new Date();
	td.setDate(1);
	td.setFullYear(display_year);
	td.setMonth(display_month);
	var day_of_week=td.getDay();
	
	document.getElementById('month_name').innerHTML=month_array[display_month]+ ' ' + display_year;
	var this_month_array=((display_year%4)==0)?month_days_leap:month_days;
	for(var d=1;d<=42;d++)
	{
		set_style(document.getElementById('d'+d));
		if ( (d>=(day_of_week+1)) && (d<=(parseInt(this_month_array[display_month])+parseInt(day_of_week))) )
		{ 
			//This is a valid days
			document.getElementById('d'+d).innerHTML = d-day_of_week;
			document.getElementById('d'+d).onmouseover=mouse_over;
			document.getElementById('d'+d).onmouseout=mouse_out;
			document.getElementById('d'+d).onclick=mouse_click;
		}
		else
		{
			//This is a blank day
			document.getElementById('d'+d).innerHTML='&nbsp;';
			document.getElementById('d'+d).onmouseover=null;
			document.getElementById('d'+d).onmouseout=null;
			document.getElementById('d'+d).onclick=null;
			document.getElementById('d'+d).style.cursor='default';
		}
	}
}

function next_month()
{
	current_month=current_month+1;
	if(current_month==12)
	{
		current_month=0;
		current_year=current_year+1;
	}
	prepcalendar(current_day, current_month, current_year);
}

function prev_month()
{		
	if(current_month==0)
	{
		current_month=11;
		current_year=current_year-1;
	}
	else
	{
		current_month=current_month-1;
	}
	prepcalendar(current_day, current_month, current_year);
}

function setH(obj)
{
	current_hour=obj.value;
	update_time();
}

function setM(obj)
{
	current_mins=obj.value;
	update_time();
}

function setAP(obj)
{
	current_ampm=obj.value;
	update_time();
}

function update_time()
{
	document.getElementById('time').value = current_hour+':'+current_mins+' '+current_ampm;
}	

//style Setting
function set_style(obj)
{
	obj.style.background='#C4D3EA';
	obj.style.font='10px Arial';
	obj.style.color='#333333';
	obj.style.textAlign='center';
	obj.style.textDecoration='none';
	obj.style.border='1px solid #6487AE';
	obj.style.cursor='pointer';
}

//Mouse Handleing for the month
function mouse_over(obj) {
	obj.target.style.background='#FFCC66';
}

function mouse_out(obj) {
	obj.target.style.background='#C4D3EA';
}

function mouse_click(obj) {
	current_day = obj.target.innerHTML;
	if(current_day>9)
		day=current_day;
	else
		day = '0'+current_day;
	
	if(current_month>8)
	{
		month=current_month+1;
	}else{
		month='0'+(current_month+1);
	}
		
	document.getElementById('date').value=month+'-'+day+'-'+current_year;
	date_close();
}

