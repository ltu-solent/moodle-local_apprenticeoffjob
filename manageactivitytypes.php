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
 * TODO describe file activitytype
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_apprenticeoffjob\tables\activitytypes_table;

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_apprenticeoffjob/manageactivitytypes', '', null, '/local/apprenticeoffjob/manageactivitytypes.php');

$url = new moodle_url('/local/apprenticeoffjob/manageactivitytypes.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading(get_string('manageactivitytypes', 'local_apprenticeoffjob'));
echo $OUTPUT->header();

$new = new action_link(
    new moodle_url('/local/apprenticeoffjob/editactivitytype.php', ['action' => 'new']),
    get_string('newactivitytype', 'local_apprenticeoffjob'),
    null,
    ['class' => 'btn btn-primary']
);

echo $OUTPUT->render($new);

$table = new activitytypes_table('apprenticeactivitytypes');
$table->out(100, false);

echo $OUTPUT->footer();
