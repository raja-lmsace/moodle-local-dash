@local @local_dash @widget_course_sections @javascript @_file_upload
Feature: Dash program to course sections
    In order to show the course section in dash block on the dashboard
    As an admin
    I can add the dash block to the dashboard

  Background:
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category 2 | 0        | CAT2     |
      | Category 3 | 0        | CAT3     |
      | Category 4 | CAT3     | CAT4     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
      | Course 2 | C2        | CAT2     | 0                |
      | Course 3 | C3        | CAT3     | 1                |
      | Course 4 | C4        | CAT4     | 1                |
    And the following "activities" exist:
      | activity  | name               | course   | idnumber | intro                  | section    | completion |
      | assign    | Assignment 2       | C1       | page03   | Welcome to Assignment2 | 0          | 1          |
      | assign    | Assignment 3       | C1       | page04   | Welcome to Assignment3 | 0          | 1          |
      | assign    | Assignment 4       | C1       | page05   | Welcome to Assignment4 | 0          | 1          |
      | assign    | Assignment 7       | C1       | page07   | Welcome to Assignment7 | 2          | 1          |
      | assign    | Assignment 8       | C1       | page08   | Welcome to Assignment8 | 2          | 1          |
      | assign    | Assignment 5       | C1       | page05   | Welcome to Assignment5 | 4          | 1          |
      | assign    | Assignment 6       | C1       | page06   | Welcome to Assignment6 | 4          | 1          |
      | assign    | Assignment 5       | C4       | page06   | Welcome to Assignment5 | 0          | 1          |
      | assign    | Assignment 6       | C4       | page07   | Welcome to Assignment6 | 0          | 1          |
      | assign    | Assignment 7       | C4       | page08   | Welcome to Assignment7 | 0          | 1          |
      | assign    | Assignment 1       | C1       | page01   | Welcome to Assignment1 | 1          | 1          |
      | assign    | Assignment 2       | C3       | page02   | Welcome to Assignment2 | 1          | 1          |
      | choice    | My quiz B          | C3       | choice   | Welcome to Quiz        | 1          | 1          |
      | choice    | Quiz 1             | C3       | choice01 | Welcome to Quiz        | 2          | 1          |
      | choice    | Quiz 2             | C1       | choice02 | Welcome to Quiz 2      | 5          | 1          |
      | book      | Book 1             | C1       | book     | Welcome to Book        | 5          | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Second   | student2@example.com |
      | student3 | Student   | Third    | student3@example.com |
      | student4 | Student   | Fourth   | student4@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | teacher2 | Teacher   | Second   | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | admin    | C1     | manager |
      | admin    | C2     | manager |
      | admin    | C4     | manager |
      | student1 | C1     | student |
      | student1 | C2     | student |
      | student1 | C4     | student |
      | student2 | C1     | student |
      | student2 | C2     | student |
      | student3 | C1     | student |
      | student4 | C1     | student |
      | student4 | C2     | student |
      | teacher1 | C1     | teacher |
      | teacher1 | C2     | teacher |
      | teacher2 | C1     | teacher |

    Given I log in as "admin"
    #--Enable course section--#
    And I navigate to "Plugins > Local plugins > Manage addons" in site administration
    And I click on ".action-icon" "css_element" in the "Widget: Course sections" "table_row"
    #--create dashboard--#
    And I follow "Dashboard"
    And I turn dash block editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    And I set the following fields to these values:
      | Name                     | Dash-dashboard             |
      | Short name               | Dash                       |
      | Description              | Welcome to Dash-dashboard  |
      | Context type             | course                     |
      | Add to course navigation | 1                          |
    And I press "Save changes"
    And I should see "Manage dashboards"
    And I should see "Dash-dashboard"
    And I click on "Dash-dashboard" "link"
    And I add the "Dash" block to the "Dash" region
    And I click on "Course Sections" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "course sections"
    And I press "Save changes"
    #--Highlight course section--#
    And I am on "Course 1" course homepage
    And I turn section "3" highlighting on
    And section "3" should be highlighted
    And I hide section "1"
    And I hide section "4"
    And I hide section "5"

  Scenario: New widget course section details
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "0"
    And I expand all fieldsets
    Then I set the following fields to these values:
      | Description | This is a description |
    And I press "Save changes"
    Then I am on "Course 1" course homepage with editing mode on
    #--select course designer format settings--#
    And I edit the section "1"
    And I expand all fieldsets
    #--section 1 estimate time added--#
    #--section 1 restriction added--#
    And I click on "Add restriction..." "button"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Direction | from   |
      | x[day]    | 1      |
      | x[month]  | 12     |
      | x[year]   | 2024   |
    And I press "Save changes"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Direction | from   |
      | x[day]    | 1      |
      | x[month]  | 5      |
      | x[year]   | 2025   |
    #And I set the field "id_sectionestimatetime_day" to "##02-April-2025##"
    And I press "Save changes"
    And I am on "Course 1" course homepage with editing mode on
    #--section 5 hide and restriction added--#
    And I edit the section "5"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the following fields to these values:
      | Direction | from   |
      | x[day]    | 15     |
      | x[month]  | 5      |
      | x[year]   | 2025   |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "Dash-dashboard" in current page administration
    Then I should see "course sections"
    Then I should see "Course 1"
    And I should see "Course 3"
    And I click on "General" "button"
    #--section hide--#
    And "Designer section 1" "button" should not exist
    And "Designer section 4" "button" should not exist
    #--section hide and restrict--#
    And "Designer section 5" "button" should not exist
    #--section restrict--#
    And ".dash-section-highlighted" "css_element" should exist in the ".widget-course_sections .course-sections:nth-child(1) .course-sections-list .course-section-accordion-block .card:nth-child(3)" "css_element"
    #--text--#
    And "Assignment 2" "text" should exist in the ".widget-course_sections .course-sections:nth-child(1) .course-section-accordion-block .card:nth-child(1)" "css_element"
    #--summary--#
    And "This is a description" "text" should exist in the ".widget-course_sections .course-sections:nth-child(1) .course-section-accordion-block .card:nth-child(1)" "css_element"

  Scenario: Expand general section
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Course Sections" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "course sections"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"

    And I am on "Course 4" course homepage
    And I delete section "4"
    And I delete section "3"
    And I delete section "2"
    And I delete section "1"
    And I delete section "1"
    And I log out
    And I log in as "student1"
    And I follow "Dashboard"
    #--Enrolled course activity linked and checkmark--#
    And I click on "Assignment 5" "link"
    And I click on "Mark as done" "button"
    And I follow "Dashboard"
    And ".fa.fa-circle-check" "css_element" should exist in the ".widget-course_sections .course-sections:nth-child(4) .course-sections-list .course-section-accordion-block .card.section-0 .collapse .card-body .course-section-block:nth-child(1)" "css_element"
    #--hide general section when no activity--#
    And ".card.section-0" "css_element" should not exist in the ".widget-course_sections .course-sections:nth-child(2) .course-sections-list .course-section-accordion-block" "css_element"

  Scenario: New widget course section condition
    And I log in as "admin"
    And I turn dash block editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Dash-dashboard" "link"
    And I wait until the page is ready
    And I open the "course sections" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course][enabled]" to "1"
    And I set the field "config_preferences[filters][c_course][courseids][]" to "C2"
    And I press "Save changes"
    And I should see "Course 2"

    And I log out

    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "Dash-dashboard" in current page administration
    And I should see "Course 2"
    And I should not see "Course 1"
    And I log out

    And I log in as "admin"
    And I turn dash block editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Dash-dashboard" "link"
    And I wait until the page is ready
    And I open the "course sections" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course][enabled]" to "0"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I press "Save changes"
    And I should see "Course 1"
    And I should not see "Course 2"
    And I log out

    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "Dash-dashboard" in current page administration
    And I should see "Course 1"
    And I should not see "Course 2"
