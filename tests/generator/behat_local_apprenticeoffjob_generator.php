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
 * Behat plugin generator
 *
 * @package    local_apprenticeoffjob
 * @category   test
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_apprenticeoffjob_generator extends behat_generator_base {
    /**
     * Get creatable entities for apprentice off job
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'activities' => [
                'singular' => 'activity',
                'datagenerator' => 'student_activity',
                'required' => [
                    'activity',
                    'course',
                    'user',
                ],
                'switchids' => [
                    'activity' => 'activitytype',
                    'user' => 'userid',
                    'course' => 'course',
                ],
            ],
        ];
    }

    /**
     * Switch activity name to activityid
     *
     * @param string $name The activity name
     * @return int The activity id
     */
    protected function get_activity_id(string $name): int {
        global $DB;
        $activityname = $DB->sql_compare_text('activityname');
        $placeholder = $DB->sql_compare_text(':activityname');
        $activityid = (int)$DB->get_field_sql(
            "SELECT id FROM {local_apprenticeactivities} WHERE {$activityname} = {$placeholder}",
            [
                'activityname' => $name,
            ],
            MUST_EXIST
        );
        return $activityid;
    }
}
