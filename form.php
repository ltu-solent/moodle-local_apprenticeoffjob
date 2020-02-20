<?php
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class activity extends moodleform {
	public function definition() {
		global $USER, $DB, $CFG, $OUTPUT;

		$mform = $this->_form;

    $activitytypes = array(
    'activity1' => get_string('activity1', 'local_apprenticeoffjob'),
    'activity2' => get_string('activity2', 'local_apprenticeoffjob'),
    'activity3' => get_string('activity3', 'local_apprenticeoffjob'),
    'activity4' => get_string('activity4', 'local_apprenticeoffjob'),
    'activity5' => get_string('activity5', 'local_apprenticeoffjob'),
    'activity6' => get_string('activity6', 'local_apprenticeoffjob'),
    'activity7' => get_string('activity7', 'local_apprenticeoffjob'),
    'activity8' => get_string('activity8', 'local_apprenticeoffjob')
		);
		$mform->addElement('select', 'activitytype', get_string('activitytype',  'local_apprenticeoffjob'), $activitytypes);
    $mform->setType('activitytype', PARAM_TEXT);
		$mform->addElement('date_time_selector', 'activitydate', get_string('activitydate',  'local_apprenticeoffjob'));
    $mform->setType('activitydate', PARAM_INT);
		$mform->addElement('text', 'activitydetails', get_string('activitydetails',  'local_apprenticeoffjob'));
    $mform->setType('activitydetails', PARAM_TEXT );
		$mform->addElement('text', 'activityhours', get_string('activityhours',  'local_apprenticeoffjob'));
    $mform->setType('activityhours', PARAM_FLOAT);
    $mform->addElement('hidden', 'id', '');
		$mform->setType('id', PARAM_INT);
    $mform->addElement('hidden', 'activityupdate', '');
		$mform->setType('activityupdate', PARAM_INT);

    $this->add_action_buttons();
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
?>
