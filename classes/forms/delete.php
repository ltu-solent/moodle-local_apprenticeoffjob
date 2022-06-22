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
 * Delete confirmation form
 *
 * @package   local_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_apprenticeoffjob\forms;

use moodleform;

require_once("$CFG->libdir/formslib.php");

class delete extends moodleform {
    public function definition() {
        global $OUTPUT;
        $mform = $this->_form;
        $mform->addElement('html', $OUTPUT->notification(get_string('deleteconfirm', 'local_apprenticeoffjob')));
        $mform->addElement('html', '<p>Date: ' . $this->_customdata['activity']->activitydate. '</p>');
        $mform->addElement('html', '<p>Details: ' . $this->_customdata['activity']->activitydetails. '</p>');
        $mform->addElement('html', '<p>Hours: ' . $this->_customdata['activity']->activityhours. '</p>');
        $mform->addElement('hidden', 'id', $this->_customdata['activity']->id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('buttonyes', 'local_apprenticeoffjob'));
    }
}
