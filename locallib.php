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

function get_activities() {
    global $DB;
    $activities = $DB->get_records('local_apprenticeactivities');
    return $activities;
}

/**
 * Get user activities.
 *
 * @param int $studentid
 * @param array $expectedhours Expected activities grouped by activity type.
 * @return array
 */
function get_user_activities($studentid, $expectedhours) {
    global $DB;
    // Expected hours are set by the teacher. If there are none, it's still possible the student has entered something.
    if (count($expectedhours) == 0) {

        $activities = $DB->get_records_sql("SELECT (FLOOR( 1 + RAND( ) *5000 )) id,
                a.id activityid, a.activitydate, a.activitytype, a.activitydetails, a.activityhours, aa.activityname, c.fullname
                FROM {local_apprentice} a
                JOIN {local_apprenticeactivities} aa ON a.activitytype = aa.id
                JOIN {course} c ON c.id = a.course
                WHERE a.userid = :studentid
                ORDER BY a.activitype", ['studentid' => $studentid]);
    } else {
        $activities = $DB->get_records_sql('SELECT (FLOOR( 1 + RAND( ) *5000 )) id,
                a.id activityid, a.activitydate, aa.id activitytype, a.activitydetails, a.activityhours, aa.activityname, c.fullname
                FROM {report_apprentice} r
                JOIN {local_apprenticeactivities} aa ON aa.id = r.activityid
                LEFT OUTER JOIN {local_apprentice} a ON a.activitytype = r.activityid AND a.userid = :userid
                LEFT JOIN {course} c ON c.id = a.course
                WHERE r.studentid = :studentid
                ORDER BY r.id, a.activitytype', ['userid' => $studentid, 'studentid' => $studentid]);
    }
    return $activities;
}

/**
 * Get expected hours for a student.
 *
 * @param int $studentid
 * @return array [$hoursbyactivity, $totalhours]
 */
function get_expected_hours($studentid) {
    global $DB;
    $hoursbyactivity = [];
    $totalhours = 0;
    $hours = $DB->get_records_sql('SELECT r.id, r.activityid, r.hours, l.activityname
                                    FROM {report_apprentice} r
                                    JOIN {local_apprenticeactivities} l ON l.id = r.activityid
                                    WHERE r.studentid = :studentid',
                                    ['studentid' => $studentid]);

    if (count($hours) > 0) {
        foreach ($hours as $h) {
            $totalhours = $totalhours + $h->hours;
            $hoursbyactivity[$h->activityid] = $h->hours;
        }
    }
    return [$hoursbyactivity, (float)$totalhours];
}

/**
 * Get actual hours recorded by student
 *
 * @param int $studentid
 * @return array [$hoursbyactivity, $totalhours]
 */
function get_actual_hours($studentid) {
    global $DB;
    $actualhours = [];
    $totalhours = 0;

    $hours = $DB->get_records_sql('SELECT activitytype, SUM(activityhours) hours
                            FROM {local_apprentice}
                            where userid = :userid
                            GROUP BY activitytype', ['userid' => $studentid]);
    if (count($hours) > 0) {
        foreach ($hours as $h) {
            $totalhours = $totalhours + $h->hours;
            $actualhours[$h->activitytype] = $h->hours;
        }
    }
    return [$actualhours, (float)$totalhours];
}

function save_activity($formdata) {
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

    if ($formdata->activityupdate == 1) {
        $activity->id = $formdata->id;
        $activity->timemodified = $date->getTimestamp();
        $activityid = $DB->update_record('local_apprentice', $activity, true);
    } else {
        $activity->timecreated = $date->getTimestamp();
        $activityid = $DB->insert_record('local_apprentice', $activity, true);
    }
    return $activityid;
}

function activities_table($activities, $reportviewer, $student, $expectedhours, $actualhours) {
    global $USER;
    $activitytypes = array();

    foreach ($activities as $k => $v) {
        $activitytypes[$v->activitytype] = $v->activityname;
    }

    $activitytypes = array_unique($activitytypes);

    // Main header row.
    $table = new html_table();
    $table->attributes['class'] = 'generaltable boxaligncenter';
    $table->cellpadding = 5;
    $table->id = 'apprenticeoffjob';
    if ($student->id == $USER->id) {
        $table->head = array('Date', 'Course/Module', 'Details', 'Hours', '');
        $table->colclasses = array('', '', '', '', 'editcol');
    } else {
        if ($reportviewer == true) {
            $table->head = array('Date', 'Course/Module', 'Details', 'Hours');
        }
    }
    // Activity header rows.
    foreach ($activitytypes as $type => $v) {
        $row = new html_table_row();
        $row->attributes['class'] = 'activityheader';
        $cell1 = new html_table_cell($v);
        $cell1->colspan = 3;

        if ($expectedhours) {
            if (isset($actualhours)) {
                $actual = array_key_exists($type, $actualhours) ? $actualhours[$type] : 0;
            } else {
                $actual = 0;
            }
            $cell2 = new html_table_cell($actual . '/' . $expectedhours[$type]);
        } else {
            $cell2 = new html_table_cell();
        }
        $cell2->attributes['class'] = 'cell-align-right';
        if ($student->id == $USER->id) {
            $cell3 = new html_table_cell();
            $cell3->attributes['class'] = 'editcol';
            $row->cells = array($cell1, $cell2, $cell3);
        } else {
            $row->cells = array($cell1, $cell2);
        }
        $table->data[] = $row;

        foreach ($activities as $activity) {
            if ($activity->activitydate != null) {
                if ($activity->activityname == $v) {
                    $table->data[] = activity_row($activity, $USER, $reportviewer, $student->id);
                }
            }
        }
    }
    return html_writer::table($table);
}

function delete_activity($formdata) {
    global $DB;
    $deleted = $DB->delete_records('local_apprentice', array('id' => $formdata->id));
    return $deleted;
}

function format_date($activitydate) {

    $date = new DateTime();
    $date = DateTime::createFromFormat('U', $activitydate);
    $timezone = core_date::get_user_timezone($date);
    date_default_timezone_set($timezone);
    $date = userdate($activitydate, get_string('strftimedaydate', 'langconfig'));

    return $date;
}

/**
 * Gets the filename for the given context.
 *
 * @param int $contextid
 * @return string The filename
 */
function get_filename($contextid) {
    global $DB;

    $filename = $DB->get_field_select('files', 'filename',
        "contextid = :contextid
            AND (filearea = :filearea AND filesize != :filesize)",
        [
            'contextid' => $contextid,
            'filearea' => 'apprenticeoffjob',
            'filesize' => 0
        ]);
    return $filename ?? '';
}

/**
 * Report tables exists.
 *
 * @deprecated 2022062100 report_apprenticeoffjob has a dependency on this, so this function is superfluous.
 * @return bool
 */
function report_exists() {
    global $DB;
    $dbman = $DB->get_manager();
    return $dbman->table_exists('report_apprentice');
}

/**
 * Generate activity row
 *
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
    // $timezone = core_date::get_user_timezone($time);
    // $activitydate = $time->getOffset();
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
 *
 * @return array
 */
function get_apprentice_courses() {
    global $DB, $USER;

    $params = [];
    $unitpages = $DB->sql_like('cc.idnumber', ':unitcat', false, false);
    $params['unitcat'] = "modules_%";
    $coursepages = $DB->sql_like('cc.idnumber', ':coursecat', false, false);
    $params['coursecat'] = "courses_%";
    $params['userid'] = $USER->id;
    $sql = "SELECT DISTINCT e.courseid, c.shortname, c.fullname, c.startdate, c.enddate, cc.name categoryname
                                  FROM {enrol} e
                                  JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = :userid
                                  JOIN {course} c ON c.id = e.courseid AND c.visible = 1 AND c.startdate < UNIX_TIMESTAMP()
                                  JOIN {course_categories} cc ON cc.id = c.category
                                  WHERE ue.status = 0 AND e.status = 0 AND ue.timestart < UNIX_TIMESTAMP()
                                  AND ({$unitpages} OR {$coursepages})";

    $courses = $DB->get_records_sql($sql, $params);
    return $courses;
}

/**
 * Create a summary section
 *
 * @param object $student user object
 * @param float $totalexpectedhours
 * @param float $totalactualhours
 * @return string HTML summary
 */
function get_hours_summary($student, float $totalexpectedhours, float $totalactualhours) {
    global $OUTPUT, $USER;
    $summary = '';
    $notify1 = new \core\output\notification((get_string('statement1', 'local_apprenticeoffjob')),
                    \core\output\notification::NOTIFY_WARNING);
    $notify2 = new \core\output\notification((get_string('statement3', 'local_apprenticeoffjob')),
                    \core\output\notification::NOTIFY_WARNING);
    $summary .= html_writer::start_span('notify1') . $OUTPUT->render($notify1) . html_writer::end_span();
    $summary .= html_writer::start_span('notify2') . $OUTPUT->render($notify2) . html_writer::end_span();

    if ($USER->id == $student->id) {
        $url = new moodle_url('activity.php');
        $summary .= html_writer::link($url,
            get_string('newactivity', 'local_apprenticeoffjob'),
            ["class" => "btn btn-secondary", "id" => "activitybutton"]);
    }
    $printbutton = html_writer::start_tag('button',
        array('id' => 'printbutton', 'onClick' => 'window.print()', 'class' => 'btn btn-secondary btn-apprentice-print'));
    $printbutton .= get_string('print', 'local_apprenticeoffjob');
    $printbutton .= html_writer::end_tag('button');
    $summary .= $printbutton;

    $hoursleft = ($totalexpectedhours - $totalactualhours);
    $summary .= get_string('totalhours', 'local_apprenticeoffjob');
    $summary .= get_string('completedhours', 'local_apprenticeoffjob', ['completedhours' => $totalactualhours]);

    if ($totalexpectedhours > 0) {
        $summary .= get_string('expectedhourstotal', 'local_apprenticeoffjob', ['expectedhours' => $totalexpectedhours]);
        $summary .= get_string('hoursleft', 'local_apprenticeoffjob', ['hoursleft' => $hoursleft]);
    }

    $usercontext = context_user::instance($student->id);
    $filename = get_filename($usercontext->id);
    if ($filename) {
        $url = moodle_url::make_pluginfile_url(
            $usercontext->id,
            'report_apprenticeoffjob',
            'apprenticeoffjob',
            0,
            '/',
            $filename,
            true
        );
        $summary .= '<a href="'.$url.'" class="commitment">'. get_string('commitmentstatement', 'local_apprenticeoffjob') . '</a>';
    } else if ($filename == null) {
        if (report_exists() == true) {
            $summary .= '<span class="commitment">' . get_string('commitmentnotavailable', 'local_apprenticeoffjob').'</span>';
        }
    }

    $summary .= get_string('completedhoursbreakdown', 'local_apprenticeoffjob');

    return $summary;
}
