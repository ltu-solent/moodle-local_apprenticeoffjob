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

require('../../config.php');
require_login();
require_once($CFG->dirroot . '/local/apprenticeoffjob/form.php');
require_once($CFG->dirroot . '/local/apprenticeoffjob/locallib.php');

if (!isloggedin() or isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->wwwroot.'/local/apprenticeoffjob/activity.php';
    }
    redirect(get_login_url());
}

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url('/local/apprenticeoffjob/activity.php');
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname',  'local_apprenticeoffjob'), new moodle_url('/local/apprenticeoffjob/'));
$PAGE->navbar->add(get_string('newactivity',  'local_apprenticeoffjob'));
$PAGE->set_heading(fullname($USER) . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));

$activityform = new \local_apprenticeoffjob\forms\activity(null, array());
if ($activityform->is_cancelled()) {
    redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php');
} else if ($formdata = $activityform->get_data()) {
    $saveactivity = \local_apprenticeoffjob\api::save_activity($formdata);
    if (is_int($saveactivity)) {
        // Trigger an activity added event.
        $usercontext = context_user::instance($USER->id);
        $event = \local_apprenticeoffjob\event\activity_added::create(array(
                    'context' => $usercontext,
                    'userid' => $USER->id
                ));
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

$activityform->display();
echo $OUTPUT->footer();
