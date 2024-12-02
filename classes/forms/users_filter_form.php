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

namespace local_apprenticeoffjob\forms;

use lang_string;
use moodleform;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Class users_filter_form
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users_filter_form extends moodleform {
    /**
     * Filter users Form definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'filterusersshdr', new lang_string('filterusers', 'local_apprenticeoffjob'));
        $mform->setExpanded('filterusersshdr', true);
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('noselection', 'local_apprenticeoffjob'),
            'ajax' => 'local_apprenticeoffjob/form-course-selector',
            'valuehtmlcallback' => function($value) {
                global $DB;
                $course = $DB->get_record('course', ['id' => $value]);
                return $course->shortname . ': ' . $course->fullname;
            },
        ];
        $mform->addElement('autocomplete', 'selectedcourses',
            new lang_string('selectcourses', 'local_apprenticeoffjob'),
            [],
            $options);
        $mform->setDefault('selectedcourses', []);

        // Add in option to select by date range.

        $this->add_action_buttons(null, new lang_string('filterusers', 'local_apprenticeoffjob'));
    }
}
