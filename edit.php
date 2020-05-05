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
global $PAGE, $USER, $COURSE, $DB, $OUTPUT;

require('../../config.php');
require_once('form.php');
require_once('locallib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/apprenticeoffjob/edit.php', array('id' => $_GET['id']));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname',  'local_apprenticeoffjob'), new moodle_url('/local/apprenticeoffjob/'));
$PAGE->navbar->add(get_string('editactivity',  'local_apprenticeoffjob'));

if (!isloggedin() or isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->wwwroot.'/local/apprenticeoffjob/index.php';
    }
    redirect(get_login_url());
}

$PAGE->set_heading($USER->firstname . ' ' . $USER->lastname . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));
echo $OUTPUT->header();
$activityid = $_GET['id'];
$studentid = $_GET['student'];

if($USER->id == $studentid){
  $activity = $DB->get_record('local_apprentice', array('id'=>$activityid));

  $editform = new activity();
  $formdata = array('id' => $activity->id,
                    'course' => $activity->course,
                    'activitytype' => $activity->activitytype,
                    'activitydate' => $activity->activitydate,
                    'activitydetails' => $activity->activitydetails,
                    'activityhours' => $activity->activityhours,
                    'activityupdate' => 1
                    );
  $editform->set_data($formdata);
  if ($editform->is_cancelled()) {
    redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php');
  } else if ($formdata = $editform->get_data()) {
    $saveactivity = save_activity($formdata);
    if($saveactivity == true){
      redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', get_string('activitysaved', 'local_apprenticeoffjob'), 15);
    }else{
      redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', get_string('activitynotsaved', 'local_apprenticeoffjob'), 15);
    }
  }
  $editform->display();
}else{
  echo $OUTPUT->notification('No permission');
}

echo $OUTPUT->footer();
