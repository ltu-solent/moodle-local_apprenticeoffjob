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
 * Helper class for apprentice off job
 *
 * @package   local_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_apprenticeoffjob;

use context_user;
use core_date;
use DateTime;
use stdClass;

/**
 * Selection of helper functions
 */
class api {
    /**
     * Get activity types
     *
     * @return array
     */
    public static function get_activitytypes() {
        global $DB;
        return $DB->get_records('local_apprenticeactivities', ['status' => 1]);
    }

    /**
     * Gets valid courses for the logged in user for logging apprentice hours
     *
     * @return array
     */
    public static function get_apprentice_courses() {
        global $CFG, $DB, $USER;
        $graceperiod = $CFG->coursegraceperiodbefore;
        $now = time();
        $params = [];
        $unitpages = $DB->sql_like('cc.idnumber', ':unitcat', false, false);
        $params['unitcat'] = "modules_%";
        $coursepages = $DB->sql_like('cc.idnumber', ':coursecat', false, false);
        $params['coursecat'] = "courses_%";
        $params['userid'] = $USER->id;
        // Include courses that start within 2 weeks.
        $params['startdate'] = $now + (DAYSECS * $graceperiod);
        $params['timestart'] = $now + (DAYSECS * $graceperiod);
        $sql = "SELECT DISTINCT e.courseid, c.shortname, c.fullname, c.startdate, c.enddate, cc.name categoryname
                                    FROM {enrol} e
                                    JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = :userid
                                    JOIN {course} c ON c.id = e.courseid AND c.visible = 1 AND c.startdate < :startdate
                                    JOIN {course_categories} cc ON cc.id = c.category
                                    WHERE ue.status = 0 AND e.status = 0 AND ue.timestart < :timestart
                                    AND ({$unitpages} OR {$coursepages})";

        $courses = $DB->get_records_sql($sql, $params);
        return $courses;
    }

    /**
     * Get user activities.
     *
     * @param int $studentid
     * @param array $expectedhours Expected activities grouped by activity type.
     * @return array
     */
    public static function get_user_activities($studentid, $expectedhours) {
        global $DB;
        $random = self::db_random();
        // Expected hours are set by the teacher. If there are none, it's still possible the student has entered something.
        if (count($expectedhours) == 0) {
            $activities = $DB->get_records_sql("SELECT {$random} idx,
                   a.id activityid, a.activitydate, a.activitytype, a.activitydetails, a.activityhours, aa.activityname,
                   c.fullname, a.userid
                   FROM {local_apprentice} a
                   JOIN {local_apprenticeactivities} aa ON a.activitytype = aa.id
                   JOIN {course} c ON c.id = a.course
                   WHERE a.userid = :studentid
                   ORDER BY a.activitytype", ['studentid' => $studentid]);
        } else {
            $activities = $DB->get_records_sql("SELECT {$random} idx,
                   a.id activityid, a.activitydate, aa.id activitytype, a.activitydetails, a.activityhours, aa.activityname,
                   c.fullname, a.userid
                   FROM {report_apprentice} r
                   JOIN {local_apprenticeactivities} aa ON aa.id = r.activityid
                   LEFT OUTER JOIN {local_apprentice} a ON a.activitytype = r.activityid AND a.userid = :userid
                   LEFT JOIN {course} c ON c.id = a.course
                   WHERE r.studentid = :studentid
                   ORDER BY r.id, a.activitytype", ['userid' => $studentid, 'studentid' => $studentid]);
        }
        return $activities;
    }

    /**
     * Get expected hours for a student.
     *
     * @param int $studentid
     * @return array [$hoursbyactivity, $totalhours]
     */
    public static function get_expected_hours($studentid) {
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
    public static function get_actual_hours($studentid) {
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

    /**
     * Save activity data
     *
     * @param object $formdata
     * @return int ActivityID
     */
    public static function save_activity($formdata) {
        global $DB, $USER;
        $activity = new stdClass();
        $activity->userid = $formdata->userid ?? $USER->id;
        $activity->course = $formdata->course;
        $activity->activitytype = intval($formdata->activitytype);
        $activity->activitydate = $formdata->activitydate;
        $activity->activitydetails = $formdata->activitydetails;
        $activity->activityhours = $formdata->activityhours;
        $date = new DateTime("now", core_date::get_user_timezone_object());
        $date->setTime(0, 0, 0);

        if (isset($formdata->id) && $formdata->id > 0) {
            $activity->id = $formdata->id;
            $activity->timemodified = $date->getTimestamp();
            $activityid = $DB->update_record('local_apprentice', $activity, true);
        } else {
            $activity->timemodified = $date->getTimestamp();
            $activity->timecreated = $date->getTimestamp();
            $activityid = $DB->insert_record('local_apprentice', $activity, true);
        }
        return $activityid;
    }

    /**
     * Get activity by id
     *
     * @param int $id
     * @return stdClass
     */
    public static function get_activity($id): ?stdClass {
        global $DB;
        $sql = "SELECT a.id, a.userid, a.course, a.activitytype, atype.activityname, a.activitydate, a.activitydetails,
            a.activityhours, a.timecreated, a.timemodified
            FROM {local_apprentice} a
            JOIN {local_apprenticeactivities} atype ON atype.id = a.activitytype
            WHERE a.id = :id";
        $activity = $DB->get_record_sql($sql, ['id' => $id]);
        return $activity;
    }

    /**
     * Delete activity
     *
     * @param int $id
     * @return bool
     */
    public static function delete_activity($id) {
        global $DB, $USER;
        $activity = self::get_activity($id);
        if (!$activity) {
            return false;
        }
        $deleted = $DB->delete_records('local_apprentice', ['id' => $id]);
        if ($deleted) {
            // Trigger deleted activity event.
            $usercontext = context_user::instance($USER->id);
            $eventdata = [
                'context' => $usercontext,
                'userid' => $USER->id,
                'objectid' => $activity->id,
                'relateduserid' => $activity->userid,
                'other' => [
                    'activityid' => $id,
                    'activitytype' => $activity->activitytype,
                    'activitydate' => $activity->activitydate,
                    'activityhours' => $activity->activityhours,
                ],
            ];
            $event = \local_apprenticeoffjob\event\activity_deleted::create($eventdata);
            $event->trigger();
        }
        return $deleted;
    }

    /**
     * Format data
     *
     * @param string $activitydate Date/Time
     * @return string
     */
    public static function format_date($activitydate) {

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
    public static function get_filename($contextid) {
        global $DB;

        $filename = $DB->get_field_select('files', 'filename',
            "contextid = :contextid
                AND (filearea = :filearea AND filesize != :filesize)",
            [
                'contextid' => $contextid,
                'filearea' => 'apprenticeoffjob',
                'filesize' => 0,
            ]);
        return $filename ?? '';
    }

    /**
     * Report tables exists.
     *
     * @deprecated 2022062100 report_apprenticeoffjob has a dependency on this, so this function is superfluous.
     * @return bool
     */
    public static function report_exists() {
        global $DB;
        $dbman = $DB->get_manager();
        return $dbman->table_exists('report_apprentice');
    }

    /**
     * Return database specific random function
     *
     * @return string
     */
    private static function db_random() {
        global $DB;
        switch ($DB->get_dbfamily()) {
            case 'oracle':
                return ' dbms_random.value ';
            case 'postgres':
                return ' RANDOM() ';
            case 'mssql':
                return ' NEWID() ';
            default:
                return ' RAND() ';
        }
    }
}
