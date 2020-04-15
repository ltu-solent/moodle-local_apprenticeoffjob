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

global $PAGE, $USER;
// Trigger an grade report viewed event.
// $event = \report_feedbackdashboard\event\feedbackdashboard_report_viewed::create(array(
//             'context' => context_user::instance($USER->id),
//             'relateduserid' => $USER->id,
//             'other' => array(
//                   'userid' => $USER->id
//               )
//           ));
// $event->trigger();

if (isloggedin() && $USER->id != 1) {
$PAGE->set_heading($USER->firstname . ' ' . $USER->lastname . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));
} else {
  $PAGE->set_heading(get_string('pluginname', 'local_apprenticeoffjob'));
}

echo $OUTPUT->header();

$url = new moodle_url('activity.php');
echo html_writer::link($url, get_string('newactivity', 'local_apprenticeoffjob'), ["class"=>"btn btn-secondary"]);

$activities = get_user_activities();
$totalhours = 0;
$expectedhours = get_expected_hours();
foreach($activities as $activity=>$value) {
  $totalhours = $totalhours + $value->activityhours;
}
$hoursleft = $expectedhours - $totalhours;
echo get_string('totalhours', 'local_apprenticeoffjob');
echo get_string('expectedhourstotal', 'local_apprenticeoffjob', ['expectedhours' => $expectedhours]);
echo get_string('completedhours', 'local_apprenticeoffjob', ['completedhours' => $totalhours]);
echo get_string('hoursleft', 'local_apprenticeoffjob', ['hoursleft' => $hoursleft]);

echo get_string('completedhoursbreakdown', 'local_apprenticeoffjob');
echo activities_table($activities);

echo $OUTPUT->footer();
