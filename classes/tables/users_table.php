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

use coding_exception;
use context_system;
use core_user\fields;
use html_writer;
use lang_string;
use moodle_url;
use stdClass;
use table_sql;

/**
 * Class useractivities_table
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users_table extends table_sql {

    /**
     * Constructor for table
     *
     * @param string $uniqueid
     * @param array $filters
     * @param string $downloadformat
     */
    public function __construct($uniqueid, array $filters = [], string $downloadformat = '') {
        parent::__construct($uniqueid);
        $this->useridfield = 'userid';
        $this->set_attribute('id', $uniqueid . '_table');
        $columns = [
            'userid' => 'id',
            'fullname' => new lang_string('participant', 'local_apprenticeoffjob'),
            'activitycount' => new lang_string('activitycount', 'local_apprenticeoffjob'),
            'totalhours' => new lang_string('sumtotalhours', 'local_apprenticeoffjob'),
            'lastactivity' => new lang_string('lastactivity', 'local_apprenticeoffjob'),
        ];

        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->collapsible(false);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->pageable(true);
        $this->is_downloadable(true);
        $sheetfilename = clean_filename('otjh-userlist-' . date('Ymd-His'));
        $this->is_downloading($downloadformat, $sheetfilename, 'otjh-users');
        $this->sql = new stdClass();
        $this->sql->params = $filters;
        // Apply relevant filters.
        $this->define_base_filter_sql();
        $this->apply_filters($filters);

        // Define the basic SQL data and object format.
        $this->define_base_sql();
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        // Define url.
        $urlparams = [];
        $baseurl = new moodle_url("/local/apprenticeoffjob/users.php", $urlparams);
        // Need to treat array param differently.
        $sc = [];
        if (isset($filters['selectedcourses'])) {
            foreach ($filters['selectedcourses'] as $key => $selectedcourse) {
                $sc['selectedcourses[' . $key . ']'] = $selectedcourse;
            }
        }
        $baseurl->params($sc);
        $this->define_baseurl($baseurl);
    }

    /**
     * Fullname is treated as a special columname in tablelib and should always
     * be treated the same as the fullname of a user.
     * @uses $this->useridfield if the userid field is not expected to be id
     * then you need to override $this->useridfield to point at the correct
     * field for the user id.
     *
     * @param object $row the data from the db containing all fields from the
     *                    users table necessary to construct the full name of the user in
     *                    current language.
     * @return string contents of cell in column 'fullname', for this row.
     */
    public function col_fullname($row) {
        $html = parent::col_fullname($row);
        if ($this->is_downloading()) {
            return $html;
        }
        $name = fullname($row, has_capability('moodle/site:viewfullnames', $this->get_context()));
        return html_writer::link(
            new moodle_url('/local/apprenticeoffjob/user.php', ['userid' => $row->userid]),
            $name
        );
    }

    /**
     * Last activity date
     *
     * @param object $row
     * @return string HTML for cell.
     */
    public function col_lastactivity($row): string {
        return userdate($row->lastactivity, get_string('strftimedate', 'core_langconfig'));
    }

    /**
     * Download
     *
     * @return void
     */
    public function download() {
        \core\session\manager::write_close();
        $this->out(0, false);
        exit;
    }

    /**
     * Query the db. Store results in the table object for use by build_table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Overridden but unused.
     * @return void
     */
    public function query_db($pagesize, $useinitialsbar = false): void {
        global $DB;

        // Set up pagination if not downloading the whole report.
        if (!$this->is_downloading()) {
            $totalsql = $this->get_full_sql(false);

            // Set up pagination.
            $totalrows = $DB->count_records_sql($totalsql, $this->sql->params);
            if ($useinitialsbar && !$this->is_downloading()) {
                $this->initialbars(true);
            }
            $this->pagesize($pagesize, $totalrows);
        }

        // Fetch the data.
        $sql = $this->get_full_sql();

        // Only paginate when not downloading.
        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    /**
     * Prepares a complete SQL statement from the base query and any filters defined.
     *
     * @param bool $fullselect Whether to select all relevant columns.
     *              False selects a count only (used to calculate pagination).
     * @return string The complete SQL statement.
     */
    protected function get_full_sql(bool $fullselect = true): string {
        $groupby = '';
        $orderby = '';

        if ($fullselect) {
            $selectfields = "{$this->sql->basefields}
                             {$this->sql->filterfields}";

            $groupby = ' GROUP BY ' . $this->sql->basegroupby . $this->sql->filtergroupby;

            if ($sort = $this->get_sql_sort()) {
                $orderby = " ORDER BY {$sort}";
            }
        } else {
            $selectfields = 'COUNT(u.id)';
        }

        $sql = "SELECT {$selectfields}
                  FROM {$this->sql->basefromjoins}
                       {$this->sql->filterfromjoins}
                 WHERE {$this->sql->basewhere}
                       {$this->sql->filterwhere}
                       {$groupby}
                       {$orderby}";

        return $sql;
    }

    /**
     * Convenience method to call a number of methods for you to display the table.
     * Overrides the parent so SQL for filters is handled.
     *
     * @param int $pagesize Number of rows to fetch.
     * @param bool $useinitialsbar Whether to include the initials bar with the table.
     * @param string $downloadhelpbutton Unused.
     *
     * @return void.
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = ''): void {
        global $DB;

        if (!$this->columns) {
            $sql = $this->get_full_sql();

            $onerow = $DB->get_record_sql($sql, $this->sql->params, IGNORE_MULTIPLE);

            // If columns is not set, define columns as the keys of the rows returned from the db.
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }

        $this->setup();
        [$wsql, $wparams] = $this->get_sql_where();
        if ($wsql) {
            $this->sql->filterwhere .= ' AND ' . $wsql;
            $this->sql->params += $wparams;
        }
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->close_recordset();
        $this->finish_output();
    }

    /**
     * SQL used for all queries
     *
     * @return void
     */
    protected function define_base_sql(): void {
        $userfieldsapi = \core_user\fields::for_identity(context_system::instance(), false)->with_userpic();
        $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $this->sql->basefields = "a.userid, {$userfieldssql}, COUNT(a.userid) activitycount, SUM(a.activityhours) totalhours,
            MAX(a.activitydate) lastactivity";
        $this->sql->basefromjoins = " {local_apprentice} a
            JOIN {user} u ON u.id = a.userid";
        $this->sql->basewhere = "1 = 1";
        $this->sql->basegroupby = "a.userid";
    }

    /**
     * Apply filters, if set.
     *
     * @param array $filters
     * @return void
     */
    protected function apply_filters(array $filters): void {
        global $DB;
        foreach ($filters as $key => $filter) {
            if ($key == 'selectedcourses' && !empty($filter)) {
                [$insql, $inparams] = $DB->get_in_or_equal($filter, SQL_PARAMS_NAMED);
                $this->sql->filterfromjoins .= " JOIN {course} c ON c.id = a.course ";
                $this->sql->filterwhere .= " AND c.id $insql";
                $this->sql->params += $inparams;
            }
        }
    }

    /**
     * Instantiate the properties to store filter values.
     *
     * @return void.
     */
    protected function define_base_filter_sql(): void {
        // Filter values will be populated separately where required.
        $this->sql->filterfields = '';
        $this->sql->filterfromjoins = '';
        $this->sql->filterwhere = '';
        $this->sql->filtergroupby = '';
    }

    /**
     * Overriding the parent method because it should not be used here.
     * Filters are applied, so the structure of $this->sql is now different to the way this is set up in the parent.
     *
     * @param string $fields Unused.
     * @param string $from Unused.
     * @param string $where Unused.
     * @param array $params Unused.
     * @return void.
     *
     * @throws coding_exception
     */
    public function set_sql($fields, $from, $where, array $params = []) {
        throw new coding_exception('The set_sql method should not be used by the summary_table class.');
    }
}
