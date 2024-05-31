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

namespace local_apprenticeoffjob\tables;

use core_user;
use html_writer;
use lang_string;
use moodle_url;
use table_sql;

/**
 * Class activitytypes_table
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activitytypes_table extends table_sql {
    /**
     * Constructor to set up table
     *
     * @param string $uniqueid
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->useridfield = 'modifiedby';
        $columns = [
            'id',
            'activityname',
            'description',
            'enabled',
            'usermodified',
            'timemodified',
            'actions',
        ];

        $columnheadings = [
            'id',
            new lang_string('activityname', 'local_apprenticeoffjob'),
            new lang_string('description'),
            new lang_string('enabled', 'local_apprenticeoffjob'),
            new lang_string('modifiedby', 'local_apprenticeoffjob'),
            new lang_string('lastmodified', 'local_apprenticeoffjob'),
            new lang_string('actions', 'local_apprenticeoffjob'),
        ];

        $this->define_columns($columns);
        $this->define_headers($columnheadings);
        $this->no_sorting('actions');
        $this->sortable(true, 'activityname', SORT_ASC);
        $this->collapsible(false);

        $this->define_baseurl(new moodle_url("/local/apprenticeoffjob/manageactivitytypes.php"));
        $where = '1=1';
        $this->set_sql('*', "{local_apprenticeactivities}", $where);
    }

    /**
     * Output actions column
     *
     * @param stdClass $row
     * @return string HTML for row's column value
     */
    public function col_actions($row) {
        $params = ['action' => 'edit', 'id' => $row->id];
        $edit = new moodle_url('/local/apprenticeoffjob/editactivitytype.php', $params);
        $html = html_writer::link($edit, get_string('edit'));

        return $html;
    }

    /**
     * Output enabled column
     *
     * @param stdClass $row
     * @return string HTML for row's column value
     */
    public function col_enabled($row) {
        return ($row->status) ? new lang_string('enabled', 'local_apprenticeoffjob')
            : new lang_string('notenabled', 'local_apprenticeoffjob');
    }

    /**
     * Output usermodified column
     *
     * @param stdClass $row
     * @return string HTML for row's column value
     */
    public function col_usermodified($row) {
        if ($row->usermodified == 0) {
            return '';
        }
        $modifiedby = core_user::get_user($row->usermodified);
        if (!$modifiedby || $modifiedby->deleted) {
            return get_string('deleteduser', 'local_apprenticeoffjob');
        }
        return fullname($modifiedby);
    }

    /**
     * Output timemodified column
     *
     * @param stdClass $row
     * @return string HTML for row's column value
     */
    public function col_timemodified($row) {
        if ($row->timemodified == 0) {
            return '';
        }
        return userdate($row->timemodified, get_string('strftimedatetimeshort', 'core_langconfig'));
    }
}
