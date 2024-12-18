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
 * TODO describe file users
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_apprenticeoffjob\forms\users_filter_form;
use local_apprenticeoffjob\tables\users_table;

require('../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('local/apprenticeoffjob:manageuserdata', $context);
$download = optional_param('download', '', PARAM_ALPHA);
$filters = [
    'selectedcourses' => [],
];
$filterform = new users_filter_form();
if ($filterdata = $filterform->get_data()) {
    $filters['selectedcourses'] = $filterdata->selectedcourses;
} else {
    $filters['selectedcourses'] = optional_param_array('selectedcourses', [], PARAM_INT);
    $filterform->set_data($filters);
}
$urlparams = [];
$baseurl = new moodle_url("/local/apprenticeoffjob/users.php", $urlparams);
// Need to treat array param differently.
$sc = [];
if (isset($filters['selectedcourses'])) {
    foreach ($filters['selectedcourses'] as $key => $selectedcourse) {
        $sc['selectedcourses[' . $key . ']'] = $selectedcourse;
    }
}
$baseurl->params($sc);
$PAGE->set_url($baseurl);
$table = new users_table('apprenticeoffjob_users', $filters, $download);
if ($table->is_downloading()) {
    $table->download();
}

$PAGE->set_heading(get_string('apprenticeactivitiessummary', 'local_apprenticeoffjob'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname',  'local_apprenticeoffjob'), new moodle_url('/local/apprenticeoffjob/'));
$PAGE->navbar->add(get_string('apprenticeactivitiessummary',  'local_apprenticeoffjob'), $baseurl);
echo $OUTPUT->header();

$filterform->display();

$table->out(100, true);
echo $OUTPUT->footer();
