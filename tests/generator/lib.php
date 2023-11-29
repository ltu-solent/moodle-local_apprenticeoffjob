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

defined('MOODLE_INTERNAL') || die();
global $CFG;
/**
 * Data generator class
 *
 * @package    local_apprenticeoffjob
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright  2023 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_apprenticeoffjob_generator extends component_generator_base {
    /**
     * Activity type counter
     *
     * @var integer
     */
    public $activitytypecount = 0;

    /**
     * Reset counters
     *
     * @return void
     */
    public function reset() {
        $this->activitytypecount = 0;
    }

    /**
     * Create an activity type
     *
     * @param array $record
     * @return stdClass
     */
    public function create_activity_type(array $record): stdClass {
        global $DB;
        $this->activitytypecount++;
        $i = $this->activitytypecount;

        $record = (object)array_merge([
            'activityname' => "Activity {$i}",
            'status' => 1,
        ], (array)$record);
        $id = $DB->insert_record('local_apprenticeactivities', $record);
        return $DB->get_record('local_apprenticeactivities', ['id' => $id]);
    }

    /**
     * Create a student activity log
     *
     * @param array $record
     * @return stdClass
     */
    public function create_student_activity(array $record): stdClass {
        global $DB;
        // Requires user concerned is logged in.
        if (!isset($record['course'])) {
            throw new moodle_exception('Course id not set');
        }
        if (!isset($record['activitytype'])) {
            throw new moodle_exception('Activity type not set');
        }
        if (defined('BEHAT_TEST') && BEHAT_TEST) {
            $generator = behat_util::get_data_generator();
        } else {
            $generator = phpunit_util::get_data_generator();
        }
        $record = (object)array_merge([
            'activitydate' => time(),
            'activitydetails' => $generator->loremipsum,
            'activityhours' => 1,
        ], (array)$record);
        $id = local_apprenticeoffjob\api::save_activity($record);
        return $DB->get_record('local_apprentice', ['id' => $id]);
    }
}
