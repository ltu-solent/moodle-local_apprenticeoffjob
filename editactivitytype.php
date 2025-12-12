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
 * TODO describe file editactivitytype
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_apprenticeoffjob\activitytype;
use local_apprenticeoffjob\forms\activitytype_form;

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'new', PARAM_ALPHA);

if (!in_array($action, ['edit', 'new'])) {
    $action = 'new';
}

$pageparams = [
    'action' => $action,
    'id' => $id,
];

admin_externalpage_setup(
    'local_apprenticeoffjob/manageactivitytypes',
    '',
    $pageparams,
    '/local/apprenticeoffjob/manageactivitytypes.php'
);
$context = context_system::instance();

$url = new moodle_url('/local/apprenticeoffjob/editactivitytype.php', []);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

if ($action == 'edit' && $id == 0) {
    throw new moodle_exception('invalidactivitytypeid', 'local_apprenticeoffjob');
}

$activitytype = new activitytype($id);
$customdata = [
    'persistent' => $activitytype,
    'userid' => $USER->id,
];

$form = new activitytype_form($PAGE->url->out(false), $customdata);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/apprenticeoffjob/manageactivitytypes.php'));
}
if ($formdata = $form->get_data()) {
    if (empty($formdata->id)) {
        $activitytype = new activitytype(0, $formdata);
        $activitytype->create();
        redirect(
            new moodle_url('/local/apprenticeoffjob/manageactivitytypes.php'),
            get_string('newsavedactivitytype', 'local_apprenticeoffjob'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    $activitytype = new activitytype($formdata->id);
    $activitytype->from_record($formdata);
    $activitytype->update();
    redirect(
        new moodle_url('/local/apprenticeoffjob/manageactivitytypes.php'),
        get_string('updatedactivitytype', 'local_apprenticeoffjob', $formdata->activityname),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$heading = get_string('editactivitytype', 'local_apprenticeoffjob');
if ($id == 0) {
    $heading = get_string('newactivitytype', 'local_apprenticeoffjob');
}

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
echo $OUTPUT->header();
echo html_writer::tag('h3', $heading);

$form->display();

echo $OUTPUT->footer();
