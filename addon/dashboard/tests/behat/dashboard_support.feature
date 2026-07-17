@local @local_dash @dashaddon @dashaddon_dashboard_support @javascript @_file_upload
Feature: Add a dashboard data source support in dash block
  In order to enable the dashboard data source in dash block on the course page
  As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | enablecompletion |
      | Course 1 | C1        | 0        | 1                | 3           |      1           |
      | Course 2 | C2        | CAT1     | 0                | 2           |      1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | teacher2 | Teacher   | Two      | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | admin    | C1     | manager |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | teacher1 | C1     | teacher |
      | student2 | C2     | student |
    And I log in as "admin"
    #---Enable roles---#
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
    #---Create new system context role---#
    And I set the following fields to these values:
      | Short name                 | admin role        |
      | Custom full name           | system admin role |
      | contextlevel10             | 1                 |
      | moodle/user:viewdetails    | 1                 |
      | moodle/user:viewalldetails | 1                 |
    And I click on "Create this role" "button"
    And I click on "List all roles" "button"
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
    #---Create new category context role---#
    And I set the following fields to these values:
      | Short name                  | category role |
      | Custom full name            | Category role |
      | contextlevel40              | 1             |
      | local/dash:managedashboards | 1             |
      | moodle/user:viewdetails     | 1             |
      | moodle/user:viewalldetails  | 1             |
    And I click on "Create this role" "button"
    #---Create new course context role---#
    And I click on "List all roles" "button"
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
    And I set the following fields to these values:
      | Short name                  | course role |
      | Custom full name            | Course role |
      | contextlevel50              | 1           |
      | local/dash:managedashboards | 1           |
      | moodle/user:viewdetails     | 1           |
      | moodle/user:viewalldetails  | 1           |
    And I click on "Create this role" "button"

    #---Assign category context role---#
    And I go to the courses management page
    And I click on "permissions" action for "Category 1" in management category listing
    And I set the field "Participants tertiary navigation" to "Assign roles"
    And I follow "Category role"
    And I set the field "addselect" to "Teacher First (teacher1@example.com)"
    And I set the field "addselect" to "Student First (student1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"

    #---Assign course context role---#
    And I am on the "Course 1" "enrolled users" page
    And I click on ".quickediticon .icon" "css_element" in the "teacher1@example.com" "table_row"
    And I open the autocomplete suggestions list
    And I click on "Course role" item in the autocomplete list
    And I click on ".inplaceeditable .icon" "css_element" in the "teacher1@example.com" "table_row"

    And I click on ".inplaceeditable .icon" "css_element" in the "student1@example.com" "table_row"
    And I open the autocomplete suggestions list
    And I click on "Course role" item in the autocomplete list
    And I click on ".inplaceeditable .icon" "css_element" in the "student1@example.com" "table_row"
    And I log out

  Scenario: Dashdoard support-category page
    And I log in as "admin"
    And I turn editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    #---Manage dashboard category context---#
    And I set the following fields to these values:
    | Name         | category |
    | Short name   | category |
    | Context type | category |
    And I press "Save changes"
    #---Manage dashboard category page---#
    And I click on "category" "link" in the "Category: Category 1" "table_row"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    And I configure the "New Dash" block
    And I set the following fields to these values:
      | Block title  | Courses  |
      | Region       | category |
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Courses" block preference
    #---Enable current course role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_category][enabled]" to "1"
    And I press "Save changes"
    #And I should see "Course 1" "text" in the ".learning-path-widget" "css_element"
    And "li.grid-block[data-title='Course 1'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"

  Scenario: Dashdoard support-course page
    And I log in as "admin"
    And I turn editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    #---Manage dashboard course context---#
    And I set the following fields to these values:
    | Name         | course |
    | Short name   | course |
    | Context type | course |
    And I press "Save changes"
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    #---Manage dashboard course page---#
    And I click on "course" "link" in the "Course: Course 1" "table_row"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Courses" "radio"
    And I configure the "New Dash" block
    And I set the following fields to these values:
      | Block title  | Courses |
      | Region       | course  |
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Courses" block preference
    #---Enable current course role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_current_course][enabled]" to "1"
    And I press "Save changes"
    And I should see "Course 1" in the "Category 1" "table_row"
    And I click on "Participants" "link"
    And I should see "Course 1" in the "#page-header" "css_element"
