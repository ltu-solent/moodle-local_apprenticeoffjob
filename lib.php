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
 * Lib file for Apprenticeoffjob
 *
 * @package   local_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Extend course navigation to give teachers a link they can share with students.
  *
  * @param navigation_node $navigation
  * @param stdClass $course
  * @param context $context
  * @return void
  */
function local_apprenticeoffjob_extend_navigation_course($navigation, $course, $context) {
    if (!get_capability_info('report/apprenticeoffjob:view')) {
        return;
    }
    if (has_capability('report/apprenticeoffjob:view', $context)) {
        $url = new moodle_url('/local/apprenticeoffjob/index.php');
        $navigation->add(
            get_string('navigationlink', 'local_apprenticeoffjob'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/report', '')
        );
    }
}
