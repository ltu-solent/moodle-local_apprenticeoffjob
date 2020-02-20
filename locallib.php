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
  $activities = $DB->get_records('local_apprenticeoffjob', array('userid'=>$USER->id), 'activitytype');
  return $activities;
}

function save_activity($formdata){
  global $DB, $USER;

  $activity = new stdClass();
  $activity->userid = $USER->id;
  $activity->activitytype = $formdata->activitytype;
  $activity->activitydate = $formdata->activitydate;
  $activity->activitydetails = $formdata->activitydetails;
  $activity->activityhours = $formdata->activityhours;

  $date = new DateTime("now", core_date::get_user_timezone_object());
  $date->setTime(0, 0, 0);
  $activity->timecreated = $date->getTimestamp();

  if($formdata->activityupdate == 1){
    $activity->id = $formdata->id;
    $activityid = $DB->update_record('local_apprenticeoffjob', $activity, true);
  }else{
    $activityid = $DB->insert_record('local_apprenticeoffjob', $activity, true);
  }
  return $activityid;
}

function activities_table($activities){
  $completedhours = 0.00;
  $activitytypes = array();
  $activityhours = array();
  foreach ($activities as $k => $v) {
    $activityhours[$v->activitytype]->activityhours += sprintf("%02.2f", $v->activityhours);
    if(!in_array($v->activitytype, $activitytypes)){
      $activitytypes[] = $v->activitytype;
    }
  }

  $table = new html_table();
  $table->attributes['class'] = 'generaltable boxaligncenter';
  $table->cellpadding = 5;
  $table->id = 'gradetable';
  $table->head = array('Date', 'Details', 'Hours', '');

  foreach($activitytypes as $type => $v){
    $row = new html_table_row();
    $row->attributes['class'] = 'activityheader';
    $cell1 = new html_table_cell(get_string($v, 'local_apprenticeoffjob'));
    $cell1->colspan = 2;
    $cell2 = new html_table_cell($activityhours[$v]->activityhours);
    $cell3 = new html_table_cell();
    $row->cells = array($cell1, $cell2, $cell3);
    $table->data[] = $row;

    foreach ($activities as $activity) {
      if($activity->activitytype == $v){
        $completedhours = $completedhours + $activity->activityhours;
        $row = new html_table_row();
        $time = new DateTime('now', core_date::get_user_timezone_object());
        $time = DateTime::createFromFormat('U', $activity->activitydate);
        $timezone = core_date::get_user_timezone($time);
        $dst = dst_offset_on($activity->activitydate, $timezone);
        $activitydate = $time - $dst;

        $cell1 = new html_table_cell(userdate($activity->activitydate));
        $cell2 = new html_table_cell($activity->activitydetails);
        $cell3 = new html_table_cell($activity->activityhours);
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
        $table->data[] = $row;
      }
    }
  }
  return html_writer::table($table);
}

function delete_activity($formdata){
  global $DB, $USER;
  $deleted = $DB->delete_records('local_apprenticeoffjob', array('id'=>$formdata->id));
  return $deleted;
}
