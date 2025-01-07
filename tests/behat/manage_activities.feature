@local @local_apprenticeoffjob @javascript
Feature: Manage activities
  As an administrator
  In order to archive recorded hours
  I need to be able to search, download and delete any recorded hours

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
      | username | firstname  | lastname | email          | department |
      | student1 | Student    | One      | s1@example.com | student    |
      | student2 | Student    | Two      | s2@example.com | student    |
      | student3 | Student    | Three    | s3@example.com | student    |
      | teacher1 | Teacher    | One      | t1@example.com | academic   |
      | aadmin   | Apprentice | Admin    | aa@example.com | support    |
    And the following "roles" exist:
      | shortname  | name              |
      | aadmin     | Apprentice office |
    And I set the following system permissions of "Apprentice office" role:
      | capability                            | permission |
      | local/apprenticeoffjob:manageuserdata | Allow      |
    And the following "role assigns" exist:
      | user   | role    | contextlevel | reference |
      | aadmin | aadmin  | System       |           |
    And the following "course enrolments" exist:
      | user     | course  | role           |
      | student1 | COURSE1 | student        |
      | student1 | COURSE2 | student        |
      | student1 | MODULE1 | student        |
      | student2 | COURSE1 | student        |
      | student2 | COURSE2 | student        |
      | student2 | MODULE1 | student        |
      | student3 | COURSE1 | student        |
      | student3 | COURSE2 | student        |
      | student3 | MODULE1 | student        |
      | teacher1 | COURSE1 | editingteacher |
      | teacher1 | COURSE2 | editingteacher |
      | teacher1 | MODULE1 | editingteacher |
    And the following "local_apprenticeoffjob > activities" exist:
      | course  | user     | activity                                |
      | COURSE1 | student1 | Teaching of Theory                      |
      | COURSE1 | student1 | Practical Training                      |
      | COURSE1 | student1 | Work Shadowing                          |
      | MODULE1 | student1 | Work Shadowing                          |
      | MODULE1 | student1 | Assignments, Projects & Portfolio (SDS) |
      | MODULE1 | student1 | Mentoring                               |
      | COURSE1 | student2 | Teaching of Theory                      |
      | COURSE1 | student2 | Practical Training                      |
      | COURSE1 | student2 | Work Shadowing                          |
      | MODULE1 | student2 | Work Shadowing                          |
      | MODULE1 | student2 | Assignments, Projects & Portfolio (SDS) |
      | MODULE1 | student2 | Mentoring                               |
      | COURSE1 | student3 | Teaching of Theory                      |
      | COURSE1 | student3 | Practical Training                      |
      | COURSE1 | student3 | Work Shadowing                          |


  Scenario: Access to the records page is restricted by permission
    Given I am logged in as "aadmin"
    When I visit "/local/apprenticeoffjob/index.php"
    Then I should see "Manage recorded hours" 
    And I should see "New activity"
    And I should see "Print this page"
    When I follow "Manage recorded hours"
    Then I should see "Apprentice activities summary"
    When I am logged in as "student1"
    And I visit "/local/apprenticeoffjob/index.php"
    Then I should not see "Manage recorded hours" 
    And I should see "New activity"
    And I should see "Print this page"
    # I would like to visit users.php here, but it would throw an exception which would fail the test.

  Scenario: Adminstrator can view all users with apprentice entries
    Given I am logged in as "aadmin"
    And I visit "/local/apprenticeoffjob/users.php"
    Then the following should exist in the "apprenticeoffjob_users_table" table:
    | -2-           | -3- | -4-     |
    | Student One   | 6   | 6.00    |
    | Student Two   | 6   | 6.00    |
    | Student Three | 3   | 3.00    |
    When I follow "Student One"
    Then the following should exist in the "apprentice_useractivities_table" table:
    | -3-           | -5-      | -6-     | -7-                                     | -10- |
    | Student One   | Course 1 | COURSE1 | Teaching of Theory                      | 1.00 |
    | Student One   | Course 1 | COURSE1 | Practical Training                      | 1.00 |
    | Student One   | Course 1 | COURSE1 | Work Shadowing                          | 1.00 |
    | Student One   | Module 1 | MODULE1 | Work Shadowing                          | 1.00 |
    | Student One   | Module 1 | MODULE1 | Assignments, Projects & Portfolio (SDS) | 1.00 |
    | Student One   | Module 1 | MODULE1 | Mentoring                               | 1.00 |

  Scenario: Administrator can filter users by course
    Given I am logged in as "aadmin"
    And I visit "/local/apprenticeoffjob/users.php"
    When I expand the "Select Courses/Modules" autocomplete
    Then "Course 1" "autocomplete_suggestions" should exist
    And "Course 2" "autocomplete_suggestions" should not exist
    And "Module 1" "autocomplete_suggestions" should exist
    And I click on "MODULE1: Module 1" item in the autocomplete list
    # Need to press escape to close the autocomplete list.
    And I press the escape key
    And I press "Filter users"
    Then the following should exist in the "apprenticeoffjob_users_table" table:
    | -2-           | -3- | -4-     |
    | Student One   | 3   | 3.00    |
    | Student Two   | 3   | 3.00    |
    Then the following should not exist in the "apprenticeoffjob_users_table" table:
    | -2-           |
    | Student Three |
    And I should see "Because you are filtering by courses/modules, the summary only counts the activities and hours for the selected courses/modules"

  Scenario: Administrator can delete one or more entries for a given user
    Given I am logged in as "aadmin"
    And I visit "/local/apprenticeoffjob/users.php"
    And I follow "Student One"
    Then the "With selected activities..." "field" should be disabled
    When I click on ".activitycheckbox" "css_element" in the "Teaching of Theory" "table_row"
    Then the "With selected activities..." "field" should be enabled
    When I set the field "With selected activities..." to "Delete selected"
    Then I should see "Confirm delete"
    And I click on "Delete" "button" in the "Confirm delete" "dialogue"
    Then the following should exist in the "apprentice_useractivities_table" table:
    | -3-           | -5-      | -6-     | -7-                                     | -10- |
    | Student One   | Course 1 | COURSE1 | Practical Training                      | 1.00 |
    | Student One   | Course 1 | COURSE1 | Work Shadowing                          | 1.00 |
    | Student One   | Module 1 | MODULE1 | Work Shadowing                          | 1.00 |
    | Student One   | Module 1 | MODULE1 | Assignments, Projects & Portfolio (SDS) | 1.00 |
    | Student One   | Module 1 | MODULE1 | Mentoring                               | 1.00 |
    When I click on "#select-all-activities" "css_element"
    And I set the field "With selected activities..." to "Delete selected"
    Then I should see "You are about to delete 5 activities for Student One"
    When I click on "Delete" "button" in the "Confirm delete" "dialogue"
    And I wait until the page is ready
    Then I should see "Off the job hours for Student One"
    And I should see "Nothing to display"


    


        
