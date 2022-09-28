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
 * Summary output class
 *
 * @package   local_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_apprenticeoffjob\output;

use context_user;
use local_apprenticeoffjob\api;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
/**
 * Summary displayed on landing page.
 */
class summary implements renderable, templatable {
    /**
     * Constructor
     *
     * @param object $student User object
     * @param float $totalexpectedhours
     * @param float $totalactualhours
     */
    public function __construct($student, float $totalexpectedhours, float $totalactualhours) {
        $this->student = $student;
        $this->totalexpectedhours = $totalexpectedhours;
        $this->totalactualhours = $totalactualhours;
    }

    /**
     * {@inheritDoc}
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER;
        $summary = new stdClass();
        $notify1 = new \core\output\notification((get_string('statement1', 'local_apprenticeoffjob')),
                        \core\output\notification::NOTIFY_WARNING);
        $notify2 = new \core\output\notification((get_string('statement3', 'local_apprenticeoffjob')),
                        \core\output\notification::NOTIFY_WARNING);
        $summary->notify1 = $notify1->export_for_template($output);
        $summary->notify2 = $notify2->export_for_template($output);

        if ($USER->id == $this->student->id) {
            $url = new moodle_url('/local/apprenticeoffjob/activity.php');
            $summary->newactivity = new stdClass();
            $summary->newactivity->url = $url->out();
            $summary->newactivity->text = get_string('newactivity', 'local_apprenticeoffjob');
            $summary->newactivity->class = "btn btn-secondary";
            $summary->newactivity->id = "activitybutton";
        }

        $hoursleft = ($this->totalexpectedhours - $this->totalactualhours);
        $summary->completedhours = $this->totalactualhours;

        if ($this->totalexpectedhours > 0) {
            $summary->expectedhours = new stdClass();
            $summary->expectedhours->total = $this->totalexpectedhours;
            $summary->expectedhours->hoursleft = $hoursleft;
        }

        $usercontext = context_user::instance($this->student->id);
        $filename = api::get_filename($usercontext->id);
        if ($filename) {
            $url = moodle_url::make_pluginfile_url(
                $usercontext->id,
                'report_apprenticeoffjob',
                'apprenticeoffjob',
                0,
                '/',
                $filename,
                true
            );
            $summary->commitmentstatement = $url->out();
        }
        return $summary;
    }
}
