<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class activity extends moodleform {
	public function definition() {
		global $USER, $DB, $CFG, $OUTPUT;

		$mform = $this->_form;

		$activitytypes = get_activities();
		$options =array();
		foreach ($activitytypes as $type => $value) {
			$options[$value->id] = $value->activityname;
		}

		$mform->addElement('select', 'activitytype', get_string('activitytype',  'local_apprenticeoffjob'), $options);
    $mform->setType('activitytype', PARAM_INT);
		$mform->addElement('date_selector', 'activitydate', get_string('activitydate',  'local_apprenticeoffjob'));
    $mform->setType('activitydate', PARAM_INT);
		$mform->addElement('text', 'activitydetails', get_string('activitydetails',  'local_apprenticeoffjob'));
    $mform->setType('activitydetails', PARAM_TEXT );
		$mform->addElement('text', 'activityhours', get_string('activityhours',  'local_apprenticeoffjob'));
    $mform->setType('activityhours', PARAM_RAW);
		$mform->addRule('activityhours', get_string('err_numeric', 'report_apprenticeoffjob'), 'numeric', null, 'server', 1, 0);
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
		global $USER, $DB, $CFG, $OUTPUT;

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
