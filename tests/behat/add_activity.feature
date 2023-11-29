@local @local_apprenticeoffjob @sol
Feature: Record offjob activities
  As an apprentice student
  In order to record the hours I put in
  I need to be able to record those hours against modules or courses I am enrolled on

  Background:
    Given the following "categories" exist:
    | name                                         | category      | idnumber      |
    | courses                                      | 0             | SOL_Courses   |
    | Faculty of Sport, Health and Social Sciences | SOL_Courses   | FSHSS         |
    | Modules                                      | FSHSS         | modules_FSHSS |
    | Courses                                      | FSHSS         | courses_FSHSS |
    And the following "courses" exist:
    | fullname | shortname | category      | startdate        |
    | Module 1 | MODULE1   | modules_FSHSS | ## 2023-09-01 ## |
    | Course 1 | COURSE1   | courses_FSHSS | ## 2023-09-01 ## |
    | Course 2 | COURSE2   | 0             | ## 2023-09-01 ## |
    And the following "users" exist:
    | username | firstname | lastname | email          | department |
    | student1 | Student   | One      | s1@example.com | student    |
    | teacher1 | Teacher   | One      | t1@example.com | academic   |
    And the following "course enrolments" exist:
    | user     | course  | role           |
    | student1 | COURSE1 | student        |
    | student1 | COURSE2 | student        |
    | student1 | MODULE1 | student        |
    | teacher1 | COURSE1 | editingteacher |
    | teacher1 | COURSE2 | editingteacher |
    | teacher1 | MODULE1 | editingteacher |

  @javascript
  Scenario: No hours set or completed
    Given I log in as "student1"
    When I visit "/local/apprenticeoffjob/index.php"
    Then I should see "Apprentice off the job hours log"
    And I should see "Completed hours: 0"
    And I should not see "Total expected hours:"
    And I should not see "Hours left to complete:"
    And I should see "Commitment statement not available"
    And "Commitment statement" "link" should not exist

  @javascript @_file_upload
  Scenario: Module leader has set target hours and included a Commitment statement
    Given I am on the "Course 1" "Course" page logged in as "teacher1"
    And I navigate to "Reports > Apprentice off the job hours report" in current page administration
    And I click on "Edit" "link" in the "Student One" "table_row"
    And I set the following fields to these values:
    | Teaching of Theory                      | 10 |
    | Practical Training                      | 10 |
    | Assignments, Projects & Portfolio (SDS) | 10 |
    | Work Shadowing                          | 10 |
    | Mentoring                               | 0  |
    And I upload "lib/tests/fixtures/empty.txt" file to "Commitment statement" filemanager
    And I press "Save changes"
    And I am logged in as "student1"
    When I visit "/local/apprenticeoffjob/index.php"
    Then I should see "Apprentice off the job hours log"
    And I should see "Completed hours: 0"
    And I should see "Total expected hours: 40"
    And I should see "Hours left to complete: 40"
    And I should not see "Commitment statement not available"
    And "Commitment statement" "link" should exist
    And I should see "None recorded" in the ".noactivity-teaching-of-theory" "css_element"
    And I should see "None recorded" in the ".noactivity-practical-training" "css_element"
    And I should see "None recorded" in the ".noactivity-assignments-projects-portfolio-sds" "css_element"
    And I should see "None recorded" in the ".noactivity-work-shadowing" "css_element"
    And I should see "None recorded" in the ".noactivity-mentoring" "css_element"
    And I should see "0/10" in the ".activity-teaching-of-theory" "css_element"
    And I should see "0/10" in the ".activity-practical-training" "css_element"
    And I should see "0/10" in the ".activity-assignments-projects-portfolio-sds" "css_element"
    And I should see "0/10" in the ".activity-work-shadowing" "css_element"
    And I should see "0/0" in the ".activity-mentoring" "css_element"

  @javascript
  Scenario: Student adds hours to a course page
    Given I am on the "Course 1" "Course" page logged in as "teacher1"
    And I navigate to "Reports > Apprentice off the job hours report" in current page administration
    And I click on "Edit" "link" in the "Student One" "table_row"
    And I set the following fields to these values:
    | Teaching of Theory                      | 10 |
    | Practical Training                      | 10 |
    | Assignments, Projects & Portfolio (SDS) | 10 |
    | Work Shadowing                          | 10 |
    | Mentoring                               | 0  |
    And I press "Save changes"
    And I am logged in as "student1"
    And I visit "/local/apprenticeoffjob/index.php"
    When I follow "New activity"
    Then I should see "I can confirm that the following activity was completed during my normal working hours"
    And the "Course/module" select box should contain "Module 1"
    And the "Course/module" select box should contain "Course 1"
    And the "Course/module" select box should not contain "Course 2"
    And I set the following fields to these values:
    | id_course             | Module 1                               |
    | id_activitytype       | Teaching of Theory                     |
    | id_activitydate_day   | 1                                      |
    | id_activitydate_month | March                                  |
    | id_activitydate_year  | 2023                                   |
    | id_activitydetails    | module-1-teaching-of-theory-2023-03-01 |
    | id_activityhours      | 1                                      |
    And I press "Save changes"
    And I should see "Completed hours: 1"
    And I should see "Hours left to complete: 39"
    And I should see "1.00/10" in the ".activity-teaching-of-theory" "css_element"
    And I should see "1.00" in the "module-1-teaching-of-theory-2023-03-01" "table_row"
    And ".noactivity-teaching-of-theory" "css_element" should not exist
    And I follow "New activity"
    And I set the following fields to these values:
    | id_course             | Course 1                               |
    | id_activitytype       | Practical Training                     |
    | id_activitydate_day   | 1                                      |
    | id_activitydate_month | March                                  |
    | id_activitydate_year  | 2023                                   |
    | id_activitydetails    | course-1-practical-training-2023-03-01 |
    | id_activityhours      | .5                                     |
    And I press "Save changes"
    And I follow "New activity"
    And I set the following fields to these values:
    | id_course             | Module 1                               |
    | id_activitytype       | Practical Training                     |
    | id_activitydate_day   | 2                                      |
    | id_activitydate_month | March                                  |
    | id_activitydate_year  | 2023                                   |
    | id_activitydetails    | module-1-practical-training-2023-03-02 |
    | id_activityhours      | .5                                     |
    And I press "Save changes"
    And I follow "New activity"
    And I set the following fields to these values:
    | id_course             | Module 1                                  |
    | id_activitytype       | Assignments, Projects & Portfolio (SDS)   |
    | id_activitydate_day   | 3                                         |
    | id_activitydate_month | March                                     |
    | id_activitydate_year  | 2023                                      |
    | id_activitydetails    | module-1-assignments-projects-2023-03-03  |
    | id_activityhours      | 1.5                                       |
    And I press "Save changes"
    And I follow "New activity"
    And I set the following fields to these values:
    | id_course             | Module 1                                  |
    | id_activitytype       | Work Shadowing                            |
    | id_activitydate_day   | 4                                         |
    | id_activitydate_month | March                                     |
    | id_activitydate_year  | 2023                                      |
    | id_activitydetails    | module-1-work-shadowing-2023-03-04        |
    | id_activityhours      | 7.5                                       |
    And I press "Save changes"
    And I should see "Completed hours: 11"
    And I should see "Hours left to complete: 29"

    And I should see "1.00/10" in the ".activity-teaching-of-theory" "css_element"
    And I should see "1.00" in the "module-1-teaching-of-theory-2023-03-01" "table_row"
    And ".noactivity-teaching-of-theory" "css_element" should not exist

    And I should see "1.00/10" in the ".activity-practical-training" "css_element"
    And I should see "0.50" in the "course-1-practical-training-2023-03-01" "table_row"
    And I should see "0.50" in the "module-1-practical-training-2023-03-02" "table_row"
    And ".noactivity-practical-training" "css_element" should not exist

    And I should see "1.50/10" in the ".activity-assignments-projects-portfolio-sds" "css_element"
    And I should see "1.50" in the "module-1-assignments-projects-2023-03-03" "table_row"
    And ".noactivity-assignments-projects-portfolio-sds" "css_element" should not exist

    And I should see "7.50/10" in the ".activity-work-shadowing" "css_element"
    And I should see "7.50" in the "module-1-work-shadowing-2023-03-04" "table_row"
    And ".noactivity-work-shadowing" "css_element" should not exist

    When I click on "Edit" "link" in the "module-1-practical-training-2023-03-02" "table_row"
    Then the following fields match these values:
    | id_course             | Module 1                               |
    | id_activitytype       | Practical Training                     |
    | id_activitydate_day   | 2                                      |
    | id_activitydate_month | March                                  |
    | id_activitydate_year  | 2023                                   |
    | id_activitydetails    | module-1-practical-training-2023-03-02 |
    | id_activityhours      | .5                                     |
    And I set the following fields to these values:
    | id_course             | Module 1                               |
    | id_activitytype       | Teaching of Theory                     |
    | id_activitydate_day   | 2                                      |
    | id_activitydate_month | March                                  |
    | id_activitydate_year  | 2023                                   |
    | id_activitydetails    | module-1-teaching-of-theory-2023-03-02 |
    | id_activityhours      | 1.5                                    |
    When I press "Save changes"
    Then I should see "Completed hours: 12"
    And I should see "Hours left to complete: 28"
    And I should see "0.50/10" in the ".activity-practical-training" "css_element"
    And "module-1-practical-training-2023-03-02" "table_row" should not exist
    And I should see "2.50/10" in the ".activity-teaching-of-theory" "css_element"
    And I should see "1.50" in the "module-1-teaching-of-theory-2023-03-02" "table_row"

    When I click on "Delete" "link" in the "module-1-teaching-of-theory-2023-03-02" "table_row"
    Then I should see "Are you sure you wish to delete this activity?"
    And I should see "module-1-teaching-of-theory-2023-03-02"
    And I should see "Hours: 1.50"
    When I press "Yes"
    Then I should see "Completed hours: 10.5"
    And I should see "Hours left to complete: 29.5"
    And I should see "1.00/10" in the ".activity-teaching-of-theory" "css_element"
    And "module-1-teaching-of-theory-2023-03-02" "table_row" should not exist
    Then I should see "wawa"
