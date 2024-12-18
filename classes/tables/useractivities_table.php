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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/tablelib.php");

use context_system;
use html_writer;
use lang_string;
use moodle_exception;
use moodle_url;
use stdClass;
use table_sql;

/**
 * Class useractivities_table
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class useractivities_table extends table_sql {
    /**
     * Constructor for user activities table
     *
     * @param string $uniqueid
     * @param array $params Needs minimum userid
     * @param string $downloadformat
     */
    public function __construct($uniqueid, array $params, string $downloadformat = '') {
        global $OUTPUT;
        if (!isset($params['userid'])) {
            throw new moodle_exception('missingparam', 'error', '', 'userid');
        }
        parent::__construct($uniqueid);
        $this->set_attribute('id', 'apprentice_useractivities_table');
        $this->useridfield = 'userid';
        $columns = [];
        if ($downloadformat == '') {
            $mastercheckbox = new \core\output\checkbox_toggleall($uniqueid, true, [
                'id' => 'select-all-activities',
                'name' => 'select-all-activities',
                'label' => get_string('selectall'),
                'labelclasses' => 'sr-only',
                'classes' => 'm-1',
                'checked' => false,
            ]);
            $columns['select'] = $OUTPUT->render($mastercheckbox);
        }
        $columns += [
            'id' => 'id',
            'fullname' => new lang_string('participant', 'local_apprenticeoffjob'),
            'idnumber' => new lang_string('apprenticeidnumber', 'local_apprenticeoffjob'),
            'course' => new lang_string('course', 'local_apprenticeoffjob'),
            'course_shortname' => new lang_string('courseshortname', 'local_apprenticeoffjob'),
            'activitytype' => new lang_string('activitytype', 'local_apprenticeoffjob'),
            'activitydate' => new lang_string('activitydate', 'local_apprenticeoffjob'),
            'activitydetails' => new lang_string('activitydetails', 'local_apprenticeoffjob'),
            'activityhours' => new lang_string('activityhours', 'local_apprenticeoffjob'),
            'timecreated' => new lang_string('timecreated'),
            'timemodified' => new lang_string('lastmodified', 'local_apprenticeoffjob'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->define_baseurl(new moodle_url("/local/apprenticeoffjob/user.php", ['userid' => $params['userid']]));

        $userfieldsapi = \core_user\fields::for_name(context_system::instance(), false);
        $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $select = "a.id, a.userid, {$userfieldssql}, u.idnumber, a.course, c.shortname course_shortname, c.fullname course_fullname,
            aa.activityname activitytype, a.activitydate, a.activitydetails, a.activityhours, a.timecreated, a.timemodified";
        $from = "{local_apprentice} a
            JOIN {local_apprenticeactivities} aa ON aa.id = a.activitytype
            JOIN {user} u ON u.id = a.userid
            LEFT JOIN {course} c ON c.id = a.course";
        $where = "a.userid = :userid";
        $this->set_sql($select, $from, $where, ['userid' => $params['userid']]);

        $this->is_downloadable(true);
        $user = \core_user::get_user($params['userid']);
        $fullname = fullname($user);
        $sheetfilename = clean_filename('otjh-' . date('Ymd') . '-' . $fullname . '-' . s($user->idnumber));
        $this->is_downloading($downloadformat, $sheetfilename, 'activities');
        $this->show_download_buttons_at([TABLE_P_BOTTOM, TABLE_P_TOP]);

        $this->collapsible(false);
        $this->no_sorting('activitydetails');
        $this->no_sorting('fullname');
        $this->no_sorting('idnumber');
        $this->no_sorting('select');
        $this->sortable(true, 'activitydate', SORT_ASC);
    }

    /**
     * Activity date
     *
     * @param object $row
     * @return string HTML for cell
     */
    public function col_activitydate($row): string {
        if ($row->activitydate == 0) {
            return '';
        }
        return userdate($row->activitydate, get_string('strftimedate', 'core_langconfig'));
    }

    /**
     * Activity details
     *
     * @param object $row
     * @return string HTML for cell
     */
    public function col_activitydetails($row): string {
        return s($row->activitydetails);
    }

    /**
     * Course name and link
     *
     * @param object $row
     * @return string HTML for cell
     */
    public function col_course($row): string {
        if (is_null($row->course_fullname)) {
            return '';
        }
        if ($this->is_downloading()) {
            return $row->course_fullname;
        }
        return html_writer::link(
            new moodle_url('/course/view.php', ['id' => $row->course]),
            $row->course_fullname
        );
    }

    /**
     * Generate the select column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_select($row): string {
        global $OUTPUT;
        $row->activitydatestring = userdate($row->activitydate);
        $checkbox = new \core\output\checkbox_toggleall($this->uniqueid, false, [
            'classes' => 'activitycheckbox m-1',
            'id' => 'activity' . $row->id,
            'name' => 'activity' . $row->id,
            'checked' => false,
            'label' => get_string('selectitem' , 'local_apprenticeoffjob', $row),
            'labelclasses' => 'accesshide',
        ]);

        return $OUTPUT->render($checkbox);
    }

    /**
     * Time created date
     *
     * @param object $row
     * @return string HTML for cell
     */
    public function col_timecreated($row): string {
        if ($row->timecreated == 0) {
            return '';
        }
        return userdate($row->timecreated, get_string('strftimedate', 'core_langconfig'));
    }

    /**
     * Time modified date
     *
     * @param object $row
     * @return string HTML for cell
     */
    public function col_timemodified($row): string {
        if ($row->timemodified == 0) {
            return '';
        }
        return userdate($row->timemodified, get_string('strftimedate', 'core_langconfig'));
    }

    /**
     * Download
     *
     * @return void
     */
    public function download() {
        unset($this->columns['select']);
        \core\session\manager::write_close();
        $this->out(0, false);
        exit;
    }

    /**
     * Overriding method to render the bulk actions and items per page pagination options directly below the table.
     *
     * @return void
     */
    public function wrap_html_finish(): void {
        global $OUTPUT;

        $data = new stdClass();
        $data->showbulkactions = true;

        if ($data->showbulkactions) {
            $data->id = 'formactionid';
            $data->attributes = [
                [
                    'name' => 'data-action',
                    'value' => 'toggle',
                ],
                [
                    'name' => 'data-togglegroup',
                    'value' => $this->uniqueid,
                ],
                [
                    'name' => 'data-toggle',
                    'value' => 'action',
                ],
                [
                    'name' => 'disabled',
                    'value' => true,
                ],
            ];
            $data->actions = [
                [
                    'value' => '#deleteselect',
                    'name' => get_string('deleteselected'),
                ],
            ];
        }

        echo $OUTPUT->render_from_template('local_apprenticeoffjob/bulk_action_menu', $data);
    }
}
