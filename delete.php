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
$PAGE->set_url('/local/apprenticeoffjob/delete.php', array('id' => $_GET['id']));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'local_apprenticeoffjob'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname',  'local_apprenticeoffjob'), new moodle_url('/local/apprenticeoffjob/'));
$PAGE->navbar->add(get_string('deleteactivity',  'local_apprenticeoffjob'));

if (!isloggedin() or isguestuser()) {
    if (empty($SESSION->wantsurl)) {
        $SESSION->wantsurl = $CFG->wwwroot.'/local/apprenticeoffjob/index.php';
    }
    redirect(get_login_url());
}

$PAGE->set_heading($USER->firstname . ' ' . $USER->lastname . ' - ' . get_string('pluginname', 'local_apprenticeoffjob'));
echo $OUTPUT->header();

$activityid = optional_param('id', '', PARAM_INT);
$studentid = optional_param('student', '', PARAM_INT);

$activity = $DB->get_record('local_apprentice', array('id'=>$activityid));

if($USER->id == $activity->userid){
  $activity->activitydate = format_date($activity->activitydate);

  $deleteform = new deleteform(null, array('activity' => $activity));
  $formdata = array('id' => $activity->id);
  $deleteform->set_data($formdata);
  if ($deleteform->is_cancelled()) {
    redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', '', 0);
  } else if ($formdata = $deleteform->get_data()) {
    $deleteactivity = delete_activity($formdata);
    if($deleteactivity == true){
      // Trigger a log viewed event.
      $usercontext = context_user::instance($USER->id);
      $event = \local_apprenticeoffjob\event\activity_deleted::create(array(
                  'context' =>  $usercontext,
                  'userid' => $USER->id,
                  'other' => array(
                        'activityid' => $formdata->id
                    )
                ));
      $event->trigger();

      redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', get_string('activitydeleted', 'local_apprenticeoffjob'), 15);
    }else{
      redirect($CFG->wwwroot. '/local/apprenticeoffjob/index.php', get_string('activitynotdeleted', 'local_apprenticeoffjob'), 15);
    }
  }
  $deleteform->display();
}else{
  echo $OUTPUT->notification(get_string('nopermission', 'local_apprenticeoffjob'));
}

echo $OUTPUT->footer();
