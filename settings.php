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
 * TODO describe file settings
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$parent = new admin_category('local_apprenticeoffjobcat', new lang_string('pluginname', 'local_apprenticeoffjob'));
if ($hassiteconfig) {
    $ADMIN->add('localplugins', $parent);

    $name = 'local_apprenticeoffjob/manageactivitytypes';
    $title = new lang_string('manageactivitytypes', 'local_apprenticeoffjob');
    $url = new moodle_url('/local/apprenticeoffjob/manageactivitytypes.php');
    $externalpage = new admin_externalpage($name, $title, $url);

    $ADMIN->add('local_apprenticeoffjobcat', $externalpage);
}
