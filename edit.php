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

// This is basically the same as activity.php.
require('../../config.php');
require_login();

if (!isloggedin() || isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->wwwroot.'/local/apprenticeoffjob/index.php';
    }
    redirect(get_login_url());
}

$activityid = required_param('id', PARAM_INT);
$studentid = optional_param('studentid', 0, PARAM_INT);
if ($studentid > 0 && $studentid != $USER->id) {
    throw new moodle_exception('noeditpermissions', 'local_apprenticeoffjob');
} else {
    $studentid = $USER->id;
}

$activity = $DB->get_record('local_apprentice', [
        'id' => $activityid,
        'userid' => $studentid,
    ], '*', MUST_EXIST);

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url('/local/apprenticeoffjob/edit.php', ['id' => $activityid]);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname',  'local_apprenticeoffjob'), new moodle_url('/local/apprenticeoffjob/'));
$PAGE->navbar->add(get_string('editactivity',  'local_apprenticeoffjob'));
$PAGE->set_heading(fullname($USER) . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));

$editform = new \local_apprenticeoffjob\forms\activity();
$formdata = [
    'id' => $activity->id,
    'course' => $activity->course,
    'activitytype' => $activity->activitytype,
    'activitydate' => $activity->activitydate,
    'activitydetails' => $activity->activitydetails,
    'activityhours' => $activity->activityhours,
    'activityupdate' => 1,
];
$editform->set_data($formdata);
if ($editform->is_cancelled()) {
    redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php');
} else if ($formdata = $editform->get_data()) {
    $saveactivity = \local_apprenticeoffjob\api::save_activity($formdata);
    if ($saveactivity == true) {
        // Trigger a log viewed event.
        $usercontext = context_user::instance($USER->id);
        $event = \local_apprenticeoffjob\event\activity_edited::create([
                    'context' => $usercontext,
                    'userid' => $USER->id,
                    'other' => [
                        'activityid' => $formdata->id,
                    ],
                ]);
        $event->trigger();

        redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', get_string('activitysaved', 'local_apprenticeoffjob'), 15);
    } else {
        redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', get_string('activitynotsaved', 'local_apprenticeoffjob'), 15);
    }
}

echo $OUTPUT->header();
$notify = new \core\output\notification((get_string('confirm', 'local_apprenticeoffjob')),
                  \core\output\notification::NOTIFY_WARNING);
echo $OUTPUT->render($notify);

$editform->display();
$activitytypes = $DB->get_records_menu('local_apprenticeactivities', [], '', 'id,description');
$PAGE->requires->js_call_amd('local_apprenticeoffjob/activitytypes', 'init', [$activitytypes]);

echo $OUTPUT->footer();
