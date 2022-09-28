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
 * Activities table
 *
 * @package   local_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_apprenticeoffjob;

use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use moodle_url;

/**
 * Activities table displays all student's recorded activities.
 */
class activities_table {

    /**
     * List of activity objects
     *
     * @var array
     */
    private $activities;
    /**
     * Is this a viewer only
     *
     * @var bool
     */
    private $reportviewer;
    /**
     * User object for selected studetn
     *
     * @var stdClass
     */
    private $student;
    /**
     * Expected hours grouped by activity type
     *
     * @var array
     */
    private $expectedhours;
    /**
     * Actual hours grouped by activity type
     *
     * @var array
     */
    private $actualhours;
    /**
     * Activity type names
     *
     * @var array
     */
    private $activitytypes = [];
    /**
     * Table object
     *
     * @var html_table
     */
    private $table;
    /**
     * Display the action column?
     *
     * @var boolean
     */
    private $hasactioncol = false;

    /**
     * Constructor
     *
     * @param array $activities List of activity objects
     * @param bool $reportviewer Is this a viewer only
     * @param object $student User object for student
     * @param array $expectedhours Expected hours grouped by activitytype
     * @param array $actualhours Actual hours grouped by activitytype
     */
    public function __construct($activities, $reportviewer, $student, $expectedhours, $actualhours) {
        global $USER;
        $this->activities = $activities;
        $this->reportviewer = $reportviewer;
        $this->student = $student;
        $this->expectedhours = $expectedhours;
        $this->actualhours = $actualhours;
        // TODO: Allow others to see the action menu with capabilities.
        $this->hasactioncol = ($this->student->id == $USER->id);
        foreach ($activities as $activity) {
            if (!isset($this->activitytypes[$activity->activitytype])) {
                $this->activitytypes[$activity->activitytype] = $activity->activityname;
            }
        }
        $this->table = new html_table();
        $this->table->attributes['class'] = 'generaltable boxaligncenter';
        $this->table->id = 'apprenticeoffjob-table';
        $headers = [
            get_string('date', 'local_apprenticeoffjob'),
            get_string('course', 'local_apprenticeoffjob'),
            get_string('details', 'local_apprenticeoffjob'),
            get_string('hours', 'local_apprenticeoffjob')
        ];
        $colclasses = ['', '', '', ''];
        if ($this->hasactioncol) {
            $headers[] = get_string('actions', 'local_apprenticeoffjob');
            $colclasses[] = 'editcol';
        }
        $this->table->head = $headers;
        $this->table->colclasses = $colclasses;
        $this->assemble();
    }

    /**
     * Assembles the table from the parameters set
     *
     * @return void
     */
    private function assemble() {
        foreach ($this->activitytypes as $actkey => $actname) {
            $activities = array_filter($this->activities, function($activity) use ($actkey) {
                return $activity->activitytype == $actkey;
            });
            $this->table->data[] = $this->activitytyperow($actkey);
            $activitycount = 0;
            foreach ($activities as $activity) {
                if ($activity->activitydate > 0) {
                    $this->table->data[] = $this->activityrow($activity);
                    $activitycount++;
                }
            }
            if ($activitycount == 0) {
                $this->table->data[] = $this->nonerecordedrow();
            }
        }
    }

    /**
     * A sort of header row to display activitytype information
     *
     * @param string $activitytype
     * @return html_table_row
     */
    private function activitytyperow($activitytype): html_table_row {
        $row = new html_table_row();
        $row->attributes['class'] = 'activityheader';
        $cell1 = $this->cell($this->activitytypes[$activitytype]);
        $cell1->colspan = 3;
        if ($this->expectedhours) {
            if (isset($this->actualhours)) {
                $actual = array_key_exists($activitytype, $this->actualhours) ? $this->actualhours[$activitytype] : 0;
            } else {
                $actual = 0;
            }
            $cell2 = $this->cell($actual . '/' . $this->expectedhours[$activitytype], 'cell-align-right');
        } else {
            $cell2 = $this->cell('', 'cell-align-right');
        }
        $row->cells = [$cell1, $cell2];
        if ($this->hasactioncol) {
            $cell3 = $this->cell('', 'editcol');
            $row->cells[] = $cell3;
        }
        return $row;
    }

    /**
     * Row for activity
     *
     * @param object $activity
     * @return html_table_row
     */
    private function activityrow($activity): html_table_row {
        $row = new html_table_row();
        $cell1 = $this->cell(userdate($activity->activitydate, get_string('strftimedaydate', 'langconfig')));
        $cell2 = $this->cell(s($activity->fullname));
        $cell3 = $this->cell(text_to_html($activity->activitydetails, null, false));
        $cell4 = $this->cell($activity->activityhours, 'cell-align-right');
        $row->cells = [$cell1, $cell2, $cell3, $cell4];
        if ($this->hasactioncol) {
            $params = ['id' => $activity->activityid, 'student' => $activity->userid];
            $editbutton = html_writer::link(
                new moodle_url('/local/apprenticeoffjob/edit.php', $params),
                get_string('edit', 'local_apprenticeoffjob'),
                [
                    'class' => 'btn btn-secondary'
                ]
            );
            $deletebutton = html_writer::link(
                new moodle_url('/local/apprenticeoffjob/delete.php', $params),
                get_string('delete', 'local_apprenticeoffjob'),
                [
                    'class' => 'btn btn-secondary'
                ]
            );
            $cell5 = $this->cell($editbutton . ' ' . $deletebutton, 'cell-align-right');
            $row->cells[] = $cell5;
        }
        return $row;
    }

    /**
     * Helper to format a cell with a class
     *
     * @param string $contents The text content
     * @param string $class
     * @return html_table_cell
     */
    private function cell($contents = '', $class = ''): html_table_cell {
        $cell = new html_table_cell($contents);
        if ($class != '') {
            $cell->attributes['class'] = $class;
        }
        return $cell;
    }

    /**
     * Returns a row with "None recorded" message to highlight no entries for given type.
     *
     * @return html_table_row
     */
    private function nonerecordedrow(): html_table_row {
        $span = 4;
        if ($this->hasactioncol) {
            $span = 5;
        }
        $cell = $this->cell(get_string('nonerecorded', 'local_apprenticeoffjob'));
        $cell->colspan = $span;
        $row = new html_table_row([$cell]);
        return $row;
    }

    /**
     * Outputs the table either directly or by returning string
     *
     * @param boolean $echo
     * @return string|void
     */
    public function print_table($echo = true) {
        $table = html_writer::table($this->table);
        if (!$echo) {
            return $table;
        }
        echo $table;
    }
}
