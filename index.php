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
require_once($CFG->dirroot . '/local/apprenticeoffjob/locallib.php');

// Require proper login or redirect.
if (!isloggedin() || isguestuser()) {
  if (empty($SESSION->wantsurl)) {
      $SESSION->wantsurl = $CFG->wwwroot . '/local/apprenticeoffjob/index.php';
  }
  redirect(get_login_url());
}
$context = context_user::instance($USER->id);
// Optional parameters if coming from course report.
$params = [];
$studentid = optional_param('id', 0, PARAM_INT);
if ($studentid > 0) {
    $courseid = required_param('course', PARAM_INT);
    $context = context_course::instance($courseid);
    $params = ['id' => $studentid, 'course' => $courseid];
} else {
  $courseid = 0;
  $studentid = $USER->id;
}

$PAGE->set_context($context);
$PAGE->set_url('/local/apprenticeoffjob/index.php', $params);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));

// Check if we're the student viewing or someone with capability from a report.
$student = $DB->get_record('user', array('id' => $studentid));

// A student should be able to see their own report even if they have a courseid in the params.
// So only require this capability of the given studentid is not the same as the logged in user.
$reportviewer = false;
if ($courseid > 0 && $USER->id != $student->id) {
    require_capability('report/apprenticeoffjob:view', $context);
    $reportviewer = true;
    // Trigger a course context event when the log for a user in a course is being viewed.
    $event = \local_apprenticeoffjob\event\log_viewed::create(array(
      'context' =>  $context,
      'relateduserid' => $student->id,
      'userid' => $USER->id
    ));
  $event->trigger();
} else {
  // Trigger a log viewed event when user's viewing their own report.
  $usercontext = context_user::instance($USER->id);
  $event = \local_apprenticeoffjob\event\log_viewed::create(array(
    'context' =>  $usercontext,
    'userid' => $USER->id
  ));
  $event->trigger();  
}

$PAGE->set_heading(fullname($student) . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));

echo $OUTPUT->header();

[$expectedhours, $totalexpectedhours] = get_expected_hours($student->id);
$activities = get_user_activities($student->id, $expectedhours);
[$actualhours, $totalactualhours] = get_actual_hours($student->id);
 echo $html;
echo get_hours_summary($student, $totalexpectedhours, $totalactualhours);
echo activities_table($activities, $reportviewer, $student, $expectedhours, $actualhours);

echo $OUTPUT->footer();
