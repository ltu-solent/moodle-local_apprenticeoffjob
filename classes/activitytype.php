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

namespace local_apprenticeoffjob;

use core\persistent;

/**
 * Class activitytype
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activitytype extends persistent {
    /**
     * Table name for activity types
     */
    const TABLE = 'local_apprenticeactivities';

    /**
     * Define properties for model
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'activityname' => [
                'type' => PARAM_TEXT,
            ],
            'description' => [
                'type' => PARAM_RAW,
            ],
            'status' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
        ];
    }
}
