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
 * @copyright  2019 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

function get_activities(){
  global $DB, $USER;
  $activities = $DB->get_records('local_apprenticeactivities');
  return $activities;
}

function get_user_activities(){
  global $DB, $USER;
  if(get_expected_hours() == null){
    $activities = $DB->get_records_sql('SELECT a.*, aa.activityname
                                        FROM {local_apprentice} a
                                        JOIN {local_apprenticeactivities} aa ON a.activitytype = aa.id
                                        WHERE a.userid = ?
                                        ORDER BY ?', array($USER->id,'activitytype'));
  }else{
    $activities = $DB->get_records_sql('SELECT (FLOOR( 1 + RAND( ) *5000 )) id, a.activitydate, a.activitydetails, a.activityhours, r.id activityid, aa.activityname
                                          FROM {report_apprentice} r
                                          JOIN {local_apprenticeactivities} aa ON aa.id = r.activityid
                                          LEFT JOIN {local_apprentice} a ON a.activitytype = r.activityid
                                          WHERE r.studentid = ?
                                          ORDER BY ?', array($USER->id,'r.id, a.activitytype'));
  }
  return $activities;
}

function get_expected_hours(){
  global $DB,$USER;
  if(report_exists() != null){
    $expectedhours = array();
    $totalhours = 0;
    $hours = $DB->get_records_sql('SELECT r.id, r.activityid, r.hours, l.activityname
                                    FROM {report_apprentice} r
                                    JOIN {local_apprenticeactivities} l ON l.id = r.activityid
                                    WHERE r.studentid = ?',
                                    array($USER->id));

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
  $activity->activitytype = intval($formdata->activitytype);
  $activity->activitydate = $formdata->activitydate;
  $activity->activitydetails = $formdata->activitydetails;
  $activity->activityhours = $formdata->activityhours;
  $date = new DateTime("now", core_date::get_user_timezone_object());
  $date->setTime(0, 0, 0);
  $activity->timecreated = $date->getTimestamp();

  if($formdata->activityupdate == 1){
    $activity->id = $formdata->id;
    $activityid = $DB->update_record('local_apprentice', $activity, true);
  }else{
    $activityid = $DB->insert_record('local_apprentice', $activity, true);
  }
  return $activityid;
}

function activities_table($activities){
  $completedhours = 0.00;
  $activitytypes = array();
  $activityhours = array();

  foreach ($activities as $k => $v) {
    $activityhours[$v->activitytype]->activityhours += sprintf("%02.2f", $v->activityhours);
    $activitytypes[] = $v->activityname;
  }

  $activitytypes = array_unique($activitytypes);

  // Main header row
  $table = new html_table();
  $table->attributes['class'] = 'generaltable boxaligncenter';
  $table->cellpadding = 5;
  $table->id = 'gradetable';
  $table->head = array('Date', 'Details', 'Hours', '');

  // Activity header rows
  foreach($activitytypes as $type => $v){
    $row = new html_table_row();
    $row->attributes['class'] = 'activityheader';
    $cell1 = new html_table_cell($v);
    $cell1->colspan = 2;
    //$cell2 = new html_table_cell($activityhours[$v]->activityhours);
    $cell2 = new html_table_cell('1/2');
    $cell2->attributes['class'] = 'cell-align-right';
    $cell3 = new html_table_cell();
    $row->cells = array($cell1, $cell2, $cell3);
    $table->data[] = $row;

    foreach ($activities as $activity) {
      if($activity->activitydate != null){
        $table->data[] = activity_row($activity, $v, $completedhours);
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
  global $DB;
  $filename = $DB->get_record('files', ['contextid'=>$contextid, 'filearea'=>'apprenticeoffjob'], 'filename');

  return $filename->filename;
}

function report_exists(){
  global $DB;
  $dbman = $DB->get_manager();
  if($dbman->table_exists('report_apprentice')){
    return true;
  }
     return null;
}

function activity_row($activity, $v, $completedhours){
    if($activity->activityname == $v){
      $completedhours = $completedhours + $activity->activityhours;
      $row = new html_table_row();
      $time = new DateTime('now', core_date::get_user_timezone_object());
      $time = DateTime::createFromFormat('U', $activity->activitydate);
      $timezone = core_date::get_user_timezone($time);
      $dst = dst_offset_on($activity->activitydate, $timezone);
      $activitydate = $time - $dst;

      $cell1 = new html_table_cell(userdate($activity->activitydate, get_string('strftimedaydate', 'langconfig')));
      $cell2 = new html_table_cell($activity->activitydetails);
      $cell3 = new html_table_cell($activity->activityhours);
      $cell3->attributes['class'] = 'cell-align-right';
      $params = ['id'=> $activity->id];
      $editurl = new moodle_url('/local/apprenticeoffjob/edit.php', $params);
      $editbutton = html_writer::start_tag('a', array('href'=>$editurl, 'class' => 'btn btn-secondary'));
      $editbutton .= get_string('edit', 'local_apprenticeoffjob');
      $editbutton .= html_writer::end_tag('a');
      $deleteurl = new moodle_url('/local/apprenticeoffjob/delete.php', $params);
      $deletebutton = html_writer::start_tag('a', array('href'=>$deleteurl, 'class' => 'btn btn-secondary'));
      $deletebutton .= get_string('delete', 'local_apprenticeoffjob');
      $deletebutton .= html_writer::end_tag('a');
      $cell4 = new html_table_cell($editbutton . ' ' . $deletebutton);
      $cell4->attributes['class'] = 'cell-align-right';

      $row->cells = array($cell1, $cell2, $cell3, $cell4);

      return $row;
  }
}
