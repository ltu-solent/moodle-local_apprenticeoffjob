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
 * Test apprenticeoffjob locallib.php functions
 *
 * @package   local_apprenticeoffjob
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2021 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_apprenticeoffjob;

use advanced_testcase;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/apprenticeoffjob/locallib.php');

class locallib_testcase extends advanced_testcase {

    public function test_get_apprentice_courses() {
        $this->resetAfterTest();
        $cats = [];
        $courses = [];
        $currentstartdate = strtotime('-3 months');
        $currenenddate = strtotime('+3 months');
        $paststartdate = strtotime('2019-09-20');
        $pastenddate = strtotime('2020-01-20');
        $futurestartdate = strtotime('+3 months');
        $futureenddate = strtotime('+6 months');

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        // Current module category structure.
        $cats['courses'] = $this->getDataGenerator()->create_category([
            'name' => 'Courses',
            'parent' => 0
        ]);
        $cats['courses/FSHSS'] = $this->getDataGenerator()->create_category([
            'name' => 'Faculty of Sport, Health and Social Sciences',
            'parent' => $cats['courses']->id,
            'idnumber' => 'FSHSS'
        ]);
        $cats['courses/FSHSS/ModulePages'] = $this->getDataGenerator()->create_category([
            'name' => 'Module Pages',
            'parent' => $cats['courses/FSHSS']->id,
            'idnumber' => 'modules_current_FSHSS'
        ]);

        // Current modules.
        $courses['ABC101_123456789'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 1',
            'shortname' => 'ABC101_123456789',
            'idnumber' => 'ABC101_123456789',
            'category' => $cats['courses/FSHSS/ModulePages']->id,
            'startdate' => $currentstartdate,
            'enddate' => $currenenddate
        ]);

        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC101_123456789']->id,
            'student',
            'manual',
            $currentstartdate,
            $currenenddate);
        $this->getDataGenerator()->enrol_user(
            $student2->id,
            $courses['ABC101_123456789']->id,
            'student');

        $courses['ABC102_123456789'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 2',
            'shortname' => 'ABC102_123456789',
            'idnumber' => 'ABC102_123456789',
            'category' => $cats['courses/FSHSS/ModulePages']->id,
            'startdate' => $currentstartdate,
            'enddate' => $currenenddate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC102_123456789']->id,
            'student',
            'manual',
            $currentstartdate,
            $currenenddate);

        // Future modules.
        $courses['ABC102_456789123'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 2',
            'shortname' => 'ABC102_456789123',
            'idnumber' => 'ABC102_456789123',
            'category' => $cats['courses/FSHSS/ModulePages']->id,
            'startdate' => $futurestartdate,
            'enddate' => $futureenddate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC102_456789123']->id,
            'student');

        // Current courses.
        $cats['courses/FSHSS/CoursePages'] = $this->getDataGenerator()->create_category([
            'name' => 'Course Pages',
            'parent' => $cats['courses/FSHSS']->id,
            'idnumber' => 'courses_FSHSS'
        ]);

        $courses['BAABC'] = $this->getDataGenerator()->create_course([
            'fullname' => 'BA Adaption Binocular Conversation (BAABC)',
            'shortname' => 'BAABC',
            'idnumber' => 'BAABC',
            'category' => $cats['courses/FSHSS/CoursePages']->id,
            'startdate' => $currentstartdate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['BAABC']->id,
            'student',
            'manual',
            $paststartdate);
        
        $courses['BABCD'] = $this->getDataGenerator()->create_course([
            'fullname' => 'BA Binocular Conversation Darwin (BABCD)',
            'shortname' => 'BABCD',
            'idnumber' => 'BABCD',
            'category' => $cats['courses/FSHSS/CoursePages']->id,
            'startdate' => $currentstartdate,
            'visible' => 0
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['BABCD']->id,
            'student',
            'manual',
            $paststartdate);
        
        $courses['BACDE'] = $this->getDataGenerator()->create_course([
            'fullname' => 'BA Conversation Darwinian Environment (BACDE)',
            'shortname' => 'BACDE',
            'idnumber' => 'BACDE',
            'category' => $cats['courses/FSHSS/CoursePages']->id,
            'startdate' => $currentstartdate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student2->id,
            $courses['BACDE']->id,
            'student',
            'manual',
            $paststartdate);

        // Archived modules category structure.
        $cats['archive'] = $this->getDataGenerator()->create_category([
            'name' => 'Archive',
            'parent' => 0
        ]);
        $cats['archive/2019-20'] = $this->getDataGenerator()->create_category([
            'name' => '2019-20',
            'parent' => $cats['archive']->id
        ]);
        $cats['archive/2019-20/FSHSS'] = $this->getDataGenerator()->create_category([
            'name' => 'Faculty of Sport, Health and Social Sciences 2019',
            'parent' => $cats['archive/2019-20']->id,
            'idnumber' => 'modules_2019_FSHSS'
        ]);

        // Archived modules.
        $courses['ABC101_234567891'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 1',
            'shortname' => 'ABC101_234567891',
            'idnumber' => 'ABC101_234567891',
            'category' => $cats['archive/2019-20/FSHSS']->id,
            'startdate' => $paststartdate,
            'enddate' => $pastenddate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC101_234567891']->id,
            'student',
            'manual',
            $paststartdate,
            $pastenddate);

        $courses['ABC102_234567891'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 2',
            'shortname' => 'ABC102_234567891',
            'idnumber' => 'ABC102_234567891',
            'category' => $cats['archive/2019-20/FSHSS']->id,
            'startdate' => $paststartdate,
            'enddate' => $pastenddate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC102_234567891']->id,
            'student',
            'manual',
            $paststartdate,
            $pastenddate);


        // False archive. i.e. A Category idnumber like modules_current_2019 rather than modules_2019.
        $cats['courses/FSHSS/2019ModulePages'] = $this->getDataGenerator()->create_category([
            'name' => '2019 Module Pages',
            'parent' => $cats['archive/2019-20']->id,
            'idnumber' => 'modules_current_2019_FSHSS'
        ]);
        $courses['ABC101_345678912'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 1',
            'shortname' => 'ABC101_345678912',
            'idnumber' => 'ABC101_345678912',
            'category' => $cats['courses/FSHSS/2019ModulePages']->id,
            'startdate' => $paststartdate,
            'enddate' => $pastenddate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC101_345678912']->id,
            'student',
            'manual',
            $paststartdate,
            $pastenddate);

        // Suspended enrolment.
        $courses['ABC102_345678912'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 1',
            'shortname' => 'ABC102_345678912',
            'idnumber' => 'ABC102_345678912',
            'category' => $cats['courses/FSHSS/ModulePages']->id,
            'startdate' => $currentstartdate,
            'enddate' => $currenenddate
        ]);
        $this->getDataGenerator()->enrol_user(
            $student1->id,
            $courses['ABC102_345678912']->id,
            'student',
            'manual',
            $currentstartdate,
            $currenenddate,
            ENROL_USER_SUSPENDED);

        // Non-academic course page site pages.
        $cats['courses/services'] = $this->getDataGenerator()->create_category([
            'name' => 'Services',
            'parent' => $cats['courses']->id,
            'idnumber' => ''
        ]);
        $courses['accesssolent'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Access Solent',
            'shortname' => 'Access Solent',
            'category' => $cats['courses/services']->id,
            'startdate' => $currentstartdate
        ]);
        $this->getDataGenerator()->enrol_user($student1->id, $courses['accesssolent']->id, 'student');
        $this->getDataGenerator()->enrol_user($student2->id, $courses['accesssolent']->id, 'student');

        // Actual tests for Student1.
        $this->setUser($student1);
        $apprenticecourses = api::get_apprentice_courses();
        // The count should be 6.
        $this->assertCount(6, $apprenticecourses);
        // Should be listed.
        $this->assertArrayHasKey($courses['ABC101_123456789']->id, $apprenticecourses);
        $this->assertArrayHasKey($courses['ABC102_123456789']->id, $apprenticecourses);
        $this->assertArrayHasKey($courses['BAABC']->id, $apprenticecourses);

        // Archives. We do need to see these now.
        $this->assertArrayHasKey($courses['ABC101_234567891']->id, $apprenticecourses);
        $this->assertArrayHasKey($courses['ABC102_234567891']->id, $apprenticecourses);
        // False archive - will be visible.
        $this->assertArrayHasKey($courses['ABC101_345678912']->id, $apprenticecourses);

        // Suspended enrolment on an active module.
        $this->assertArrayNotHasKey($courses['ABC102_345678912']->id, $apprenticecourses);
        // This course is hidden, so should not be returned.
        $this->assertArrayNotHasKey($courses['BABCD']->id, $apprenticecourses);
        // Modules not started yet, should not be available.
        $this->assertArrayNotHasKey($courses['ABC102_456789123']->id, $apprenticecourses);

        // Site pages.
        $this->assertArrayNotHasKey($courses['accesssolent']->id, $apprenticecourses);

        // Student 2.
        $this->setUser($student2);
        $apprenticecourses = api::get_apprentice_courses();
        // One module, One Course.
        $this->assertCount(2, $apprenticecourses);
        // Should be listed.
        $this->assertArrayHasKey($courses['ABC101_123456789']->id, $apprenticecourses);
        $this->assertArrayHasKey($courses['BACDE']->id, $apprenticecourses);
    }
}
