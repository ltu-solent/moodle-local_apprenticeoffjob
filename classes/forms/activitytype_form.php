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

use context_system;
use core\form\persistent as persistent_form;
use lang_string;

/**
 * Class activitytype
 *
 * @package    local_apprenticeoffjob
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activitytype_form extends persistent_form {
    /**
     * Cross reference for the object this form is working from.
     *
     * @var string
     */
    protected static $persistentclass = 'local_apprenticeoffjob\\activitytype';

    /**
     * Activity type object editing form
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $required = new lang_string('required');
        $mform->addElement('text', 'activityname', new lang_string('activityname', 'local_apprenticeoffjob'));
        $mform->addRule('activityname', $required, 'required', null, 'client');

        $mform->addElement('textarea', 'description', new lang_string('description'), 'wrap="virtual" rows="20" cols="50"');
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('advcheckbox', 'status', new lang_string('enabled', 'local_apprenticeoffjob'));
        $mform->addElement('hidden', 'id');

        $this->add_action_buttons();
    }
}
