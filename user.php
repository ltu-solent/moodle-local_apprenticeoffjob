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
 * TODO describe file user
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_apprenticeoffjob\tables\useractivities_table;

require('../../config.php');
$userid = required_param('userid', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('local/apprenticeoffjob:manageuserdata', $context);

$apprentice = core_user::get_user($userid, '*', MUST_EXIST);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(
    get_string('pluginname',  'local_apprenticeoffjob'),
    new moodle_url('/local/apprenticeoffjob/')
);
$PAGE->navbar->add(
    get_string('apprenticeactivitiessummary',  'local_apprenticeoffjob'),
    new moodle_url('/local/apprenticeoffjob/users.php')
);
$url = new moodle_url('/local/apprenticeoffjob/user.php', ['userid' => $userid]);
$PAGE->navbar->add(
    get_string('otjhfor', 'local_apprenticeoffjob', fullname($apprentice)),
    $url
);
$PAGE->set_url($url);

$table = new useractivities_table('apprenticeoffjob_useractivities', ['userid' => $userid], $download);
if ($table->is_downloading()) {
    $table->download();
}

$PAGE->set_heading(get_string('otjhfor', 'local_apprenticeoffjob', fullname($apprentice)));
echo $OUTPUT->header();
$strings = [
    'apprentice' => fullname($apprentice),
];
echo html_writer::div(
    get_string('apprenticesummary', 'local_apprenticeoffjob', $strings)
);
echo html_writer::tag('br', '');
$table->out(100, false);

echo $OUTPUT->footer();
