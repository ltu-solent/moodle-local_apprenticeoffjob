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
 * Display a user grade report for all courses
 *
 * @package    local
 * @subpackage apprenticeoffjob
 * @copyright  2020 onwards Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class activity extends moodleform {
	public function definition() {
		global $DB, $CFG, $OUTPUT;

		$mform = $this->_form;

		$courses = get_apprentice_courses();

		$courseoptions = array();
		foreach($courses as $course => $c){
			$courseoptions[$c->courseid] = $c->fullname;
		}

		$activitytypes = get_activities();
		$activityoptions =array();
		foreach ($activitytypes as $type => $value) {
			$activityoptions[$value->id] = $value->activityname;
		}

		$mform->addElement('select', 'course', get_string('course',  'local_apprenticeoffjob'), $courseoptions);
    $mform->setType('course', PARAM_INT);
		$mform->addElement('select', 'activitytype', get_string('activitytype',  'local_apprenticeoffjob'), $activityoptions);
    $mform->setType('activitytype', PARAM_INT);
		$mform->addElement('date_selector', 'activitydate', get_string('activitydate',  'local_apprenticeoffjob'));
    $mform->setType('activitydate', PARAM_INT);
		$mform->addElement('text', 'activitydetails', get_string('activitydetails',  'local_apprenticeoffjob'));
    $mform->setType('activitydetails', PARAM_TEXT );
		$mform->addElement('text', 'activityhours', get_string('activityhours',  'local_apprenticeoffjob'));
    $mform->setType('activityhours', PARAM_RAW);
		$mform->addRule('activityhours', get_string('errnumeric', 'local_apprenticeoffjob'), 'numeric', null, 'server', 1, 0);
		$mform->addElement('hidden', 'id', '');
		$mform->setType('id', PARAM_INT);
    $mform->addElement('hidden', 'activityupdate', '');
		$mform->setType('activityupdate', PARAM_INT);

    $this->add_action_buttons();
	}

	public function validation($data, $files) {
			$errors = parent::validation($data, $files);
      return $errors;
  }
}

class deleteform extends moodleform {
	public function definition() {
		global $DB, $CFG, $OUTPUT;

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
