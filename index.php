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
 * Student data entry point for apprentice off job hours
 *
 * @package    local
 * @subpackage apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('locallib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/apprenticeoffjob/index.php');
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));

// Optional parameters if coming from course report
$studentid = optional_param('id', '', PARAM_INT);
$reportuser = optional_param('user', '', PARAM_INT);
$course = optional_param('course', '', PARAM_INT);

global $USER, $DB, $OUTPUT;

// require proper login or redirect
if (!isloggedin() or isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->wwwroot.'/local/apprenticeoffjob/index.php';
    }
    redirect(get_login_url());
}

// Check if we're the student viewing or someone with capability from a report.
if(!empty($studentid)){
  $student = $DB->get_record('user', array('id'=>$studentid));

}else{
  $student = $DB->get_record('user', array('id'=>$USER->id));
}

// Trigger a log viewed event.
$usercontext = context_user::instance($USER->id);
$event = \local_apprenticeoffjob\event\log_viewed::create(array(
            'context' =>  $usercontext,
            'relateduserid' => $student->id,
            'userid' => $USER->id
          ));
$event->trigger();

$PAGE->set_heading($student->firstname . ' ' . $student->lastname . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));

echo $OUTPUT->header();

// Display table
if($USER->id == $student->id || !empty($course)){
  if(!empty($course)){
    $reportviewer = context_course::instance($course);
    if(has_capability('report/apprenticeoffjob:view', $reportviewer)){
      $activities = get_user_activities($student->id);
      $expectedhours = get_expected_hours($student->id);
      echo get_hours_summary($student, $activities, $expectedhours);
      echo activities_table($activities, $student->id);
    }else{
      echo get_string('nopermission', 'local_apprenticeoffjob');
    }
  }else{
    $activities = get_user_activities($student->id);
    $expectedhours = get_expected_hours($student->id);
    echo get_hours_summary($student, $activities, $expectedhours);
    echo activities_table($activities, $student->id);
  }
}

echo $OUTPUT->footer();
