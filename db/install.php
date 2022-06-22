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

/**
 * Install code for report_customsql.
 *
 * @return bool true on success.
 */
function xmldb_local_apprenticeoffjob_install() {
    global $CFG, $DB;
    $activities = [];
    $dataobject1 = new stdClass();
    $dataobject1->activityname = 'Teaching of Theory';
    $dataobject1->status = 1;
    $activities[] = $dataobject1;
    $dataobject2 = new stdClass();
    $dataobject2->activityname = 'Practical Training';
    $dataobject2->status = 1;
    $activities[] = $dataobject2;
    $dataobject3 = new stdClass();
    $dataobject3->activityname = 'Assignments, Projects & Portfolio (SDS)';
    $dataobject3->status = 1;
    $activities[] = $dataobject3;
    $dataobject4 = new stdClass();
    $dataobject4->activityname = 'Work Shadowing';
    $dataobject4->status = 1;
    $activities[] = $dataobject4;
    $dataobject5 = new stdClass();
    $dataobject5->activityname = 'Mentoring';
    $dataobject5->status = 1;
    $activities[] = $dataobject5;

    $DB->insert_records('local_apprenticeactivities', $activities);

    return true;
}
