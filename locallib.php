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
 * @package    local_apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_apprenticeoffjob\activities_table;
use local_apprenticeoffjob\api;

/**
 * Undocumented function
 * @deprecated version
 * @return void
 */
function get_activities() {
    return api::get_activitytypes();
}

/**
 * Get user activities.
 * @deprecated version
 * @param int $studentid
 * @param array $expectedhours Expected activities grouped by activity type.
 * @return array
 */
function get_user_activities($studentid, $expectedhours) {
    return api::get_user_activities($studentid, $expectedhours);
}

/**
 * Get expected hours for a student.
 * @deprecated version
 * @param int $studentid
 * @return array [$hoursbyactivity, $totalhours]
 */
function get_expected_hours($studentid) {
    return api::get_expected_hours($studentid);
}

/**
 * Get actual hours recorded by student
 * @deprecated version
 * @param int $studentid
 * @return array [$hoursbyactivity, $totalhours]
 */
function get_actual_hours($studentid) {
    return api::get_actual_hours($studentid);
}

/**
 * Save activity data
 * @deprecated version
 * @param object $formdata
 * @return int ActivityID
 */
function save_activity($formdata) {
    return api::save_activity($formdata);
}

/**
 * Create table
 * @deprecated version
 * @param array $activities
 * @param bool $reportviewer
 * @param object $student
 * @param array $expectedhours
 * @param array $actualhours
 * @return string HTML
 */
function activities_table($activities, $reportviewer, $student, $expectedhours, $actualhours) {
    $table = new activities_table($activities, $reportviewer, $student, $expectedhours, $actualhours);
    return $table->print_table(false);
}

/**
 * Delete activity
 * @deprecated version
 * @param object $formdata
 * @return void
 */
function delete_activity($formdata) {
    return api::delete_activity($formdata);
}

/**
 * Format data
 * @deprecated version
 * @param string $activitydate Date/Time
 * @return string
 */
function format_date($activitydate) {
    return api::format_date($activitydate);
}

/**
 * Gets the filename for the given context.
 * @deprecated version
 * @param int $contextid
 * @return string The filename
 */
function get_filename($contextid) {
    return api::get_filename($contextid);
}

/**
 * Report tables exists.
 *
 * @deprecated 2022062100 report_apprenticeoffjob has a dependency on this, so this function is superfluous.
 * @return bool
 */
function report_exists() {
    return api::report_exists();
}

/**
 * Generate activity row
 *
 * @deprecated version
 * @param object $activity
 * @param object $student
 * @param bool $reportviewer
 * @param int $studentid
 * @return html_table_row
 */
function activity_row($activity, $student, $reportviewer, $studentid) {
    $row = new html_table_row();
    $time = new DateTime('now', core_date::get_user_timezone_object());
    $time = DateTime::createFromFormat('U', $activity->activitydate);

    $cell1 = new html_table_cell(userdate($activity->activitydate, get_string('strftimedaydate', 'langconfig')));
    $cell2 = new html_table_cell($activity->fullname);
    $cell3 = new html_table_cell($activity->activitydetails);
    $cell4 = new html_table_cell($activity->activityhours);
    $cell4->attributes['class'] = 'cell-align-right';
    if ($studentid == $student->id) {
        $params = ['id' => $activity->activityid, 'student' => $studentid];
        $editurl = new moodle_url('/local/apprenticeoffjob/edit.php', $params);
        $editbutton = html_writer::start_tag('a', array('href' => $editurl, 'class' => 'btn btn-secondary'));
        $editbutton .= get_string('edit', 'local_apprenticeoffjob');
        $editbutton .= html_writer::end_tag('a');
        $deleteurl = new moodle_url('/local/apprenticeoffjob/delete.php', $params);
        $deletebutton = html_writer::start_tag('a', array('href' => $deleteurl, 'class' => 'btn btn-secondary'));
        $deletebutton .= get_string('delete', 'local_apprenticeoffjob');
        $deletebutton .= html_writer::end_tag('a');
        $cell5 = new html_table_cell($editbutton . ' ' . $deletebutton);
        $cell5->attributes['class'] = 'cell-align-right';
        $row->cells = array($cell1, $cell2, $cell3, $cell4, $cell5);
    } else {
        if ($reportviewer == true) {
            $row->cells = array($cell1, $cell2, $cell3, $cell4);
        }
    }

    return $row;
}

/**
 * Gets valid courses for the logged in user for logging apprentice hours
 * @deprecated version
 * @return array
 */
function get_apprentice_courses() {
    return api::get_apprentice_courses();
}

/**
 * Create a summary section
 * @deprecated version
 * @param object $student user object
 * @param float $totalexpectedhours
 * @param float $totalactualhours
 * @return string HTML summary
 */
function get_hours_summary($student, float $totalexpectedhours, float $totalactualhours) {
    global $OUTPUT;
    $summary = new \local_apprenticeoffjob\output\summary($student, $totalexpectedhours, $totalactualhours);
    return $summary;
}
