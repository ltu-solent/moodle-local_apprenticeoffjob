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
 * Upgrade steps for Apprentice off the job hours log
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    local_apprenticeoffjob
 * @category   upgrade
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_apprenticeoffjob_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024051500) {
        $table = new xmldb_table('local_apprenticeactivities');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, false, null, null, 'activityname');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024051500, 'local', 'apprenticeoffjob');
    }
    if ($oldversion < 2024051502) {
        $table = new xmldb_table('local_apprenticeactivities');
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, "10", true, true, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, "10", true, true, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, "10", true, true, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024051502, 'local', 'apprenticeoffjob');
    }

    return true;
}
