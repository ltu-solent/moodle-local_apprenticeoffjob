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
 * External functions and service declaration for Apprentice off the job hours log
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    local_apprenticeoffjob
 * @category   webservice
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_apprenticeoffjob_search_courses' => [
        'classname' => 'local_apprenticeoffjob\external\search_courses',
        'description' => 'Search courses',
        'type' => 'read',
        'ajax'  => true,
    ],
    'local_apprenticeoffjob_delete_activities' => [
        'classname' => 'local_apprenticeoffjob\external\delete_activities',
        'description' => 'Delete selected activities',
        'type' => 'write',
        'capabilities' => 'local/apprenticeoffjob:manageuserdata',
        'ajax' => true,
    ],
];

$services = [
];
