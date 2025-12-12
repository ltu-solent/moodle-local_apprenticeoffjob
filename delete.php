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

require('../../config.php');
require_login();
if (!isloggedin() || isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->wwwroot . '/local/apprenticeoffjob/index.php';
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
$activity = $DB->get_record('local_apprentice', ['id' => $activityid, 'userid' => $studentid], '*', MUST_EXIST);


$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url('/local/apprenticeoffjob/delete.php', ['id' => $activityid]);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'local_apprenticeoffjob'), new moodle_url('/local/apprenticeoffjob/'));
$PAGE->navbar->add(get_string('deleteactivity', 'local_apprenticeoffjob'));

$PAGE->set_heading(fullname($USER) . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));

$activity->activitydate = \local_apprenticeoffjob\api::format_date($activity->activitydate);

$deleteform = new \local_apprenticeoffjob\forms\delete(null, ['activity' => $activity]);

$formdata = ['id' => $activity->id];
$deleteform->set_data($formdata);
if ($deleteform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/apprenticeoffjob/index.php', '', 0);
} else if ($formdata = $deleteform->get_data()) {
    $deleteactivity = \local_apprenticeoffjob\api::delete_activity($activity->id);
    if ($deleteactivity == true) {
        redirect($CFG->wwwroot . '/local/apprenticeoffjob/index.php', get_string('activitydeleted', 'local_apprenticeoffjob'), 15);
    } else {
        redirect(
            $CFG->wwwroot . '/local/apprenticeoffjob/index.php',
            get_string('activitynotdeleted', 'local_apprenticeoffjob'),
            15
        );
    }
}

echo $OUTPUT->header();
$deleteform->display();
echo $OUTPUT->footer();
