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

use context_system;
use context_user;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_value;
use local_apprenticeoffjob\event\activities_deleted;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * Class delete_activities
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_activities extends external_api {
    /**
     * Parameters for main function
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'activityids' => new external_multiple_structure(new external_value(PARAM_INT, 'Activity ID')),
        ]);
    }

    /**
     * Delete selected activities by activityids
     *
     * @param int $activityids
     * @return void
     */
    public static function execute($activityids) {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'activityids' => $activityids,
            ]
        );
        require_capability('local/apprenticeoffjob:manageuserdata', context_system::instance());
        [$insql, $inparams] = $DB->get_in_or_equal($params['activityids']);
        $affected = $DB->get_records_select('local_apprentice', "id $insql", $inparams);
        $userids = [];
        // Although we only expect deletion requests for one user, break this down so we are sure about the events we trigger.
        foreach ($affected as $row) {
            if (!isset($userids[$row->userid])) {
                $userids[$row->userid] = [];
            }
            $userids[$row->userid][$row->id] = $row;
        }
        foreach ($userids as $userid => $activities) {
            $activityids = array_keys($activities);
            $count = count($activityids);
            // Delete activities.
            [$insql, $inparams] = $DB->get_in_or_equal($activityids);
            $DB->delete_records_select('local_apprentice', "id $insql", $inparams);
            $usercontext = context_user::instance($userid);
            $eventparams = [
                'userid' => $USER->id,
                'context' => $usercontext,
                'relateduser' => $userid,
                'other' => [
                    'count' => $count,
                ],
            ];
            $event = activities_deleted::create($eventparams);
            $event->trigger();
        }
        return null;
    }

    /**
     * No need to return anything here.
     *
     * @return void
     */
    public static function execute_returns() {
        return null;
    }
}
