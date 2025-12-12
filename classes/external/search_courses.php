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

namespace local_apprenticeoffjob\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * Class search_courses
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_courses extends external_api {
    /**
     * Search courses parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'Search string'),
        ]);
    }

    /**
     * Search courses
     *
     * @param string $query
     * @return array
     */
    public static function execute($query): array {
        global $DB;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'query' => $query,
            ]
        );
        $concat = $DB->sql_concat('c.shortname', "': '", 'c.fullname');
        $select = "SELECT c.id courseid, " . $concat . " AS label FROM {course} c
            JOIN {local_apprentice} a ON a.course = c.id
        ";
        $wheres = [];
        $qparams = [];
        if ($params['query']) {
            $likeshortname = $DB->sql_like("shortname", ':shortname', false, false);
            $likefullname = $DB->sql_like("fullname", ':fullname', false, false);
            $qparams['shortname'] = '%' . $DB->sql_like_escape($params['query']) . '%';
            $qparams['fullname'] = '%' . $DB->sql_like_escape($params['query']) . '%';
            $wheres[] = " ($likeshortname OR $likefullname) ";
        }

        $where = " WHERE 1=1 ";
        if (!empty($wheres)) {
            $where = " WHERE " . join(' AND ', $wheres);
        }

        $courses = $DB->get_records_sql($select . $where, $qparams, 0, 100);
        return $courses;
    }

    /**
     * Defines the returned structure of the array.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'courseid' => new external_value(PARAM_INT, 'courseid'),
                'label' => new external_value(PARAM_RAW, 'User friendly label - Shortname'),
            ])
        );
    }
}
