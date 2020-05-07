<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display a user grade report for all courses
 *
 * @package    local
 * @subpackage apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

function get_activities(){
  global $DB;
  $activities = $DB->get_records('local_apprenticeactivities');
  return $activities;
}

function get_user_activities($studentid){
  global $DB;
  if(get_expected_hours($studentid) == null){

    $activities = $DB->get_records_sql('SELECT (FLOOR( 1 + RAND( ) *5000 )) id,
                                        a.id activityid, a.activitydate, a.activitytype, a.activitydetails, a.activityhours, aa.activityname, c.fullname
                                        FROM {local_apprentice} a
                                        JOIN {local_apprenticeactivities} aa ON a.activitytype = aa.id
                                        JOIN {course} c ON c.id = a.course
                                        WHERE a.userid = ?
                                        ORDER BY ?', array($studentid,'activitytype'));
  }else{
    $activities = $DB->get_records_sql('SELECT (FLOOR( 1 + RAND( ) *5000 )) id,
                                          a.id activityid, a.activitydate, aa.id activitytype, a.activitydetails, a.activityhours, aa.activityname, c.fullname
                                          FROM {report_apprentice} r
                                          JOIN {local_apprenticeactivities} aa ON aa.id = r.activityid
                                          LEFT OUTER JOIN {local_apprentice} a ON a.activitytype = r.activityid AND a.userid = ?
                                          LEFT JOIN {course} c ON c.id = a.course
                                          WHERE r.studentid = ?
                                          ORDER BY ?', array($studentid, $studentid,'r.id, a.activitytype'));
  }
  return $activities;
}

function get_expected_hours($studentid){
  global $DB;
  if(report_exists() != null){
    $expectedhours = array();
    $totalhours = 0;
    $hours = $DB->get_records_sql('SELECT r.id, r.activityid, r.hours, l.activityname
                                    FROM {report_apprentice} r
                                    JOIN {local_apprenticeactivities} l ON l.id = r.activityid
                                    WHERE r.studentid = ?',
                                    array($studentid));

    if(count($hours)!= 0){
      foreach($hours as $hour=>$h){
        $totalhours = $totalhours + $h->hours;
        $expectedhours[$h->activityid] = $h->hours;
      }
      $expectedhours['totalhours'] = $totalhours;
      return $expectedhours;
    }
  }
    return null;
}

function save_activity($formdata){
  global $DB, $USER;
  $activity = new stdClass();
  $activity->userid = $USER->id;
  $activity->course = $formdata->course;
  $activity->activitytype = intval($formdata->activitytype);
  $activity->activitydate = $formdata->activitydate;
  $activity->activitydetails = $formdata->activitydetails;
  $activity->activityhours = $formdata->activityhours;
  $date = new DateTime("now", core_date::get_user_timezone_object());
  $date->setTime(0, 0, 0);


  if($formdata->activityupdate == 1){
    $activity->id = $formdata->id;
    $activity->timemodified = $date->getTimestamp();
    $activityid = $DB->update_record('local_apprentice', $activity, true);
  }else{
    $activity->timecreated = $date->getTimestamp();
    $activityid = $DB->insert_record('local_apprentice', $activity, true);
  }
  return $activityid;
}

function activities_table($activities, $studentid){
  global $USER;
  $completedhours = 0.00;
  $activitytypes = array();
  $expectedhours = get_expected_hours($studentid);

  foreach ($activities as $k => $v) {
    $activitytypes[$v->activitytype] = $v->activityname;
  }

  $activitytypes = array_unique($activitytypes);

  // Main header row
  $table = new html_table();
  $table->attributes['class'] = 'generaltable boxaligncenter';
  $table->cellpadding = 5;
  $table->id = 'apprenticeoffjob';
  if($studentid == $USER->id){
    $ownerstudent = 1;
    $table->head = array('Date', 'Course/Module', 'Details', 'Hours', '');
    $table->colclasses = array('', '', '', '','editcol');
  }else{
    $ownerstudent = 0;
    $table->head = array('Date', 'Course/Module', 'Details', 'Hours');
  }
  // Activity header rows
  foreach($activitytypes as $type => $v){
    $row = new html_table_row();
    $row->attributes['class'] = 'activityheader';
    $cell1 = new html_table_cell($v);
    $cell1->colspan = 3;

    $activitycompletedhours = activity_completed_hours($activities, $type);
    if($expectedhours){
      $cell2 = new html_table_cell($activitycompletedhours. '/' . $expectedhours[$type]);
    }else{
      $cell2 = new html_table_cell();
    }
    $cell2->attributes['class'] = 'cell-align-right';
    if($ownerstudent == 1){
      $cell3 = new html_table_cell();
      $cell3->attributes['class'] = 'editcol';
      $row->cells = array($cell1, $cell2, $cell3);
    }else{
      $row->cells = array($cell1, $cell2);
    }
    $table->data[] = $row;

    foreach ($activities as $activity) {

      if($activity->activitydate != null){
        if($activity->activityname == $v){
          $table->data[] = activity_row($activity, $v, $completedhours, $ownerstudent, $studentid);
        }
      }
    }
  }
  return html_writer::table($table);
}

function delete_activity($formdata){
  global $DB, $USER;
  $deleted = $DB->delete_records('local_apprentice', array('id'=>$formdata->id));
  return $deleted;
}

function format_date($activitydate){

  $date = new DateTime();
  $date = DateTime::createFromFormat('U', $activitydate);
  $timezone = core_date::get_user_timezone($date);
  date_default_timezone_set($timezone);
  $date = userdate($activitydate, get_string('strftimedaydate', 'langconfig'));

  return $date;
}

function get_filename($contextid){
  if(report_exists()){
    global $DB;

    $filename = $DB->get_record_sql("SELECT filename FROM {files}
                                      WHERE contextid = ?
                                      AND (filearea = ? AND filesize != ?)", array($contextid, "apprenticeoffjob", "filename", 0 ));
    if($filename){
      return $filename->filename;
    }else{
      return null;
    }
  }
}

function report_exists(){
  global $DB;
  $dbman = $DB->get_manager();
  if($dbman->table_exists('report_apprentice')){
    return true;
  }
     return null;
}

function activity_row($activity, $v, $completedhours, $ownerstudent, $studentid){
      $completedhours = $completedhours + $activity->activityhours;
      $row = new html_table_row();
      $time = new DateTime('now', core_date::get_user_timezone_object());
      $time = DateTime::createFromFormat('U', $activity->activitydate);
      $timezone = core_date::get_user_timezone($time);
      $activitydate = $time->getOffset();

      $cell1 = new html_table_cell(userdate($activity->activitydate, get_string('strftimedaydate', 'langconfig')));
      $cell2 = new html_table_cell($activity->fullname);
      $cell3 = new html_table_cell($activity->activitydetails);
      $cell4 = new html_table_cell($activity->activityhours);
      $cell4->attributes['class'] = 'cell-align-right';
      if($ownerstudent == 1){
        $params = ['id'=> $activity->activityid, 'student'=>$studentid];
        $editurl = new moodle_url('/local/apprenticeoffjob/edit.php', $params);
        $editbutton = html_writer::start_tag('a', array('href'=>$editurl, 'class' => 'btn btn-secondary'));
        $editbutton .= get_string('edit', 'local_apprenticeoffjob');
        $editbutton .= html_writer::end_tag('a');
        $deleteurl = new moodle_url('/local/apprenticeoffjob/delete.php', $params);
        $deletebutton = html_writer::start_tag('a', array('href'=>$deleteurl, 'class' => 'btn btn-secondary'));
        $deletebutton .= get_string('delete', 'local_apprenticeoffjob');
        $deletebutton .= html_writer::end_tag('a');
        $cell5 = new html_table_cell($editbutton . ' ' . $deletebutton);
        $cell5->attributes['class'] = 'cell-align-right';
        $row->cells = array($cell1, $cell2, $cell3, $cell4, $cell5);
      }else{
        $row->cells = array($cell1, $cell2, $cell3, $cell4);
      }

      return $row;
}

function activity_completed_hours($activities, $type){
  //var_dump($activities);
  $activitycompletedhours = 0;
  foreach ($activities as $activity) {
    if($activity->activitytype == $type){
      $activitycompletedhours = $activitycompletedhours + $activity->activityhours;
    }
  }
  return $activitycompletedhours;
}

function get_apprentice_courses(){
  global $DB, $USER;

  $params = [];
  $unitpages = $DB->sql_like('cc.name', ':unitname', false, false);
  $params['unitname'] = "%unit pages%";
  $coursepages = $DB->sql_like('cc.name', ':coursename', false, false);
  $params['coursename'] = "%course pages%";

  $sql = "SELECT DISTINCT e.courseid, c.shortname, c.fullname, c.startdate, c.enddate, cc.name categoryname
                                  FROM {enrol} e
                                  JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = $USER->id
                                  JOIN {course} c ON c.id = e.courseid
                                  JOIN {course_categories} cc ON cc.id = c.category
                                  WHERE ue.status = 0 AND e.status = 0 AND ue.timestart < UNIX_TIMESTAMP()
                                  AND (ue.timeend = 0 OR ue.timeend > UNIX_TIMESTAMP())
                                  AND ue.userid = $USER->id
                                  AND cc.name LIKE :unitname OR cc.name LIKE :coursename";

  $courses = $DB->get_records_sql($sql, $params);

  return $courses;
}

function get_hours_summary($student, $activities, $expectedhours){
  global $USER, $DB, $OUTPUT;
  $summary = '';
  $notify = new \core\output\notification((get_string('statement1', 'local_apprenticeoffjob')),
                  \core\output\notification::NOTIFY_WARNING);
  $summary .= html_writer::span($OUTPUT->render($notify));
  if($USER->id == $student->id){
    $url = new moodle_url('activity.php');
    $summary .= html_writer::link($url, get_string('newactivity', 'local_apprenticeoffjob'), ["class"=>"btn btn-secondary", "id"=>"activitybutton"]);
  }
  $printbutton = html_writer::start_tag('button', array('id'=>'printbutton', 'onClick'=>'window.print()', 'class' => 'btn btn-secondary btn-apprentice-print'));
  $printbutton .= get_string('print', 'local_apprenticeoffjob');
  $printbutton .= html_writer::end_tag('button');
  $summary .= $printbutton;

  $totalhours = 0;

  foreach($activities as $activity=>$value) {
    $totalhours = $totalhours + $value->activityhours;
  }

  $hoursleft = $expectedhours['totalhours'] - $totalhours;
  $summary .= get_string('totalhours', 'local_apprenticeoffjob');
  $summary .= get_string('completedhours', 'local_apprenticeoffjob', ['completedhours' => $totalhours]);

  if($expectedhours != null){
    $summary .= get_string('expectedhourstotal', 'local_apprenticeoffjob', ['expectedhours' => $expectedhours['totalhours']]);
    $summary .= get_string('hoursleft', 'local_apprenticeoffjob', ['hoursleft' => $hoursleft]);
  }

  $usercontext = context_user::instance($student->id);
  $filename = get_filename($usercontext->id);
  if($filename){
    $url= moodle_url::make_pluginfile_url($usercontext->id,'report_apprenticeoffjob','apprenticeoffjob', 0,'/',$filename, true);
    $summary .= '<a href="'.$url.'">'. get_string('commitmentstatement', 'local_apprenticeoffjob') . '</a>';
  }elseif($filename == null){
    if(report_exists() == true){
      $summary.= get_string('commitmentnotavailable', 'local_apprenticeoffjob');
    }
  }

  $summary .= get_string('completedhoursbreakdown', 'local_apprenticeoffjob');

  return $summary;
}
