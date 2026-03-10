@local @local_dash @dashaddon @dashaddon_course_enrols @javascript
Feature: Add course enrol widget in dash block
  In order to enable the course enrol widgets in dash block on the dashboard
  As an admin

  Background:
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category 1 | 0        | CAT1     |
      | Category 2 | 0        | CAT2     |
      | Category 3 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections |
      | Course 1 | C1        | 0        | 1                | 3           |
      | Course 2 | C2        | CAT1     | 0                | 2           |
      | Course 3 | C3        | CAT2     | 1                | 1           |
      | Course 4 | C4        | CAT3     | 1                | 2           |
      | Course 5 | C5        | CAT3     | 1                | 2           |
      | Course 6 | C6        | CAT3     | 1                | 2           |
    And the following "activities" exist:
      | activity | name      | course | idnumber | intro            | section |
      | page     | testpage1 | C1     | page1    | Page description | 1       |
      | page     | testpage2 | C1     | page1    | Page description | 2       |
      | page     | testpage3 | C1     | page1    | Page description | 3       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend |
      | student1 | C1     | student | ## today ## | 0       |
      | student1 | C2     | student | 0           | 0       |
      | student1 | C3     | student | 0           | 0       |
      | student1 | C4     | student | 0           | 0       |
      | student2 | C1     | student | 0           | 0       |
      | student2 | C2     | student | 0           | 0       |
      | student2 | C3     | student | 0           | 0       |
      | admin    | C1     | student | 0           | 0       |
      | admin    | C2     | student | 0           | 0       |
      | admin    | C3     | student | 0           | 0       |
      | admin    | C4     | student | 0           | 0       |
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Course Enrolments" datasource
    And I configure the "New Dash" block
    And I set the field "Block title" to "New Dash"
    # # And I click on "Course Enrolments" "text" in the "New Dash" "block"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "New Dash" block preference
    Then I click on "Filters" "link"
    And I set the field "User" to "1"
    And I set the field "Status" to "1"
    And I set the field "Sort" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

  Scenario: Display course for specific course categories.
    Given I log in as "student1"
    And I should see "Course 2" in the "New Dash" "block"
    And I should see "Course 3" in the "New Dash" "block"
    And I should see "Course 4" in the "New Dash" "block"
    Then I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I set the field "Course categories" to "1"
    And I set the field "config_preferences[filters][c_course_categories_condition][coursecategories][]" to "Category 2"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    Then I log in as "student1"
    And I should not see "Course 2" in the "New Dash" "block"
    And I should see "Course 3" in the "New Dash" "block"
    And I should not see "Course 4" in the "New Dash" "block"

  Scenario: Check the course enrol for fields.
    Given I log in as "admin"
    And ".dash-block-content .pagination" "css_element" should not exist
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Per page" to "3"
    And I press "Save changes"
    Then ".dash-block-content .pagination" "css_element" should exist
    And I should not see "Course 4" in the "New Dash" "block"
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Display progress" to "0"
    And I press "Save changes"
    And ".course-enrol-block .course-section-block .section-element" "css_element" should not exist
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Expandable" to "0"
    And I press "Save changes"
    And ".course-enrol-block .course-section-block .mb-0 button.btn" "css_element" should not exist
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Display add course form" to "Above the courses"
    And I press "Save changes"
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Display add course form" to "Above the courses"
    And I press "Save changes"
    Then ".course-enrols-courses-list .add-course-block + #accordion" "css_element" should exist
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Display add course form" to "Below the courses"
    And I press "Save changes"
    Then ".course-enrols-courses-list #accordion + .add-course-block" "css_element" should exist
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Display add course form" to "Disable"
    And I press "Save changes"
    Then ".course-enrols-courses-list .add-course-block" "css_element" should not exist

  Scenario: Check the course enrol to enrol course.
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I set the field "Display add course form" to "Above the courses"
    And I press "Save changes"
    Then I log out
    And I log in as "student1"
    And I should not see "Course 5" in the ".course-enrols-courses-list .courses-list" "css_element"
    And I set the field "courses[]" to "Course 5"
    Then I click on "Enrol Now" "button" in the "New Dash" "block"
    Then I reload the page
    And I should see "Course 5" in the ".course-enrols-courses-list .courses-list" "css_element"

  Scenario: Check the course enrol to view options.
    Given I log in as "student1"
    And I set the field "c_sort" to "Sort alphabetically A-Z"
    And I should see "Course 1" in the ".courses-list .card:nth-child(1) .course-section-block" "css_element"
    And I click on ".courses-list .card:nth-child(1) .edit-option-block .dropdown-toggle" "css_element"
    Then I click on "View details" "link" in the ".courses-list .card:nth-child(1) .edit-option-block" "css_element"
    Then I should see "Enrolment details"
    And I should see "Student First" in the "Full name" "table_row"
    And I should see "##today##%A, %d %B %Y, %I:%M %p##" in the "Enrolment starts" "table_row"
    And I click on "Cancel" "button" in the "Enrolment details" "dialogue"
    And I click on ".courses-list .card:nth-child(1) .edit-option-block .dropdown-toggle" "css_element"
    Then I click on "Edit enrolment" "link" in the ".courses-list .card:nth-child(1) .edit-option-block" "css_element"
    And I should see "Edit Student First's enrolment"
    And I set the field "Status" to "Suspended"
    And I click on "Save changes" "button"
    Then I should see "Suspended" in the ".courses-list .card:nth-child(1) .edit-option-block .badge" "css_element"
    And I click on ".courses-list .card:nth-child(1) .edit-option-block .dropdown-toggle" "css_element"
    Then I click on "Unenrol" "link" in the ".courses-list .card:nth-child(1) .edit-option-block" "css_element"
    And I click on "Save changes" "button" in the "Unenrol" "dialogue"
    And I should not see "Course 1" in the "New Dash" "block"
