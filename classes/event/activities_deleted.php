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

namespace local_apprenticeoffjob\event;

use moodle_url;

/**
 * Event activities_deleted
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activities_deleted extends \core\event\base {

    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'local_apprentice';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return description of what happened
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' deleted {$this->other['count']} activities for user '$this->relateduserid'";
    }

    /**
     * Return name of event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('activitiesdeleted', 'local_apprenticeoffjob');
    }

    /**
     * Get URL related to the action.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url('/local/apprenticeoffjob/user.php', ['userid' => $this->relateduserid]);
    }
}
