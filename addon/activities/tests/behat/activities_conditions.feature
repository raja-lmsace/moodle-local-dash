@local @local_dash @dashaddon @dashaddon_activities @dashaddon_activities_conditions @javascript

Feature: Add activity widget in dash block
  In order to enable the activity widgets in dash block on the dashboard
  As an admin

  Background:
    Given the following "custom field categories" exist:
      | name  | component   | area   | itemid |
      | Other | core_course | course | 0      |
    And the following "custom fields" exist:
      | name    | category | type | shortname |
      | Field 1 | Other    | text | text      |
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1 | C1        | CAT1     | 1                | 3           |                |                 |
      | Course 2 | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3 | C3        | CAT2     | 1                | 1           |                |                 |
      | Course 4 | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
    And the following "activities" exist:
      | activity      | name               | course   | idnumber | intro                 | section    | completion |
      | assign        | Assignment 1       | C1       | page01   | Welcome to Assignment | 1          | 1          |
      | assign        | Assignment 2       | C3       | page02   | Welcome to Assignment | 1          | 1          |
      | choice        | My quiz B          | C4       | choice   | Welcome to Quiz       | 1          | 1          |
      | choice        | Quiz 1             | C4       | choice   | Welcome to Quiz       | 2          | 1          |
      | book          | Book 1             | C2       | book     | Welcome to Book       | 1          | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | teacher1 | teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend |
      | student1 | C1     | student | ## today ## | 0       |
      | student1 | C2     | student | 0           | 0       |
      | student1 | C3     | student | 0           | 0       |
      | student1 | C4     | student | 0           | 0       |
      | teacher1 | C1     | teacher | 0           | 0       |
      | teacher1 | C2     | teacher | 0           | 0       |
      | teacher1 | C3     | teacher | 0           | 0       |
    And the following "block_dash > dash blocks default" exist:
      | type       | name       | title      |
      | datasource | activities | Activities |
    # And I log in as "admin"
    #And I am on the "block_dash > Default Dashboard" page
    # And I turn dash block editing mode on
    # And I create dash "Activities" datasource
    # Then I configure the "New Dash" block
    # And I set the field "Block title" to "Activities"
    # And I set the following fields to these values:
    #   | Region | content |
    # And I press "Save changes"
    # Then I open the "Activities" block preference
    # And I reload the page
    # Then I open the "Activities" block preference
    # Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    # And I click on "Select all" "button"
    # And I press "Save changes"
    # And I click on "Reset Dashboard for all users" "button"
    # Then I log out

  Scenario: Activities Datasource: Course Category Conditions.
    Given I log in as "admin"
    # Category : Category 01
    #And I am on the "block_dash > Default Dashboard" page
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I click on "Course categories" "checkbox"
    And I set the field "id_config_preferences_filters_c_course_categories_condition_coursecategories" to "Category 01"
    And I press "Save changes"
    And I should see "Assignment 1"
    And I should not see "Assignment 2"
    # Category : Category 01, Category 02
    #And I am on the "block_dash > Default Dashboard" page
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I set the field "id_config_preferences_filters_c_course_categories_condition_coursecategories" to "Category 01, Category 02"
    And I press "Save changes"
    And I should see "Assignment 1"
    And I should see "Assignment 2"
    And I should not see "Quiz 1"
    # Category : Category 01, Category 02 Include the subcategory.
    #And I am on the "block_dash > Default Dashboard" page
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I click on "Include subcategories" "checkbox"
    And I set the field "id_config_preferences_filters_c_course_categories_condition_coursecategories" to "Category 01, Category 02"
    And I press "Save changes"
    And I should see "Assignment 1"
    And I should see "Assignment 2"
    And I should see "Quiz 1"

  Scenario: Activities Datasource: Course Conditions.
    Given I log in as "admin"
    # Course : Course 01
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I click on "Courses" "checkbox"
    And I set the field "id_config_preferences_filters_c_course_courseids" to "C1"
    And I press "Save changes"
    And I should see "Assignment 1"
    Then I should not see "Book 1"
    And I should not see "Assignment 2"
    # Course : Course 02
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I set the field "id_config_preferences_filters_c_course_courseids" to "C1,C2"
    And I press "Save changes"
    And I should see "Assignment 1"
    Then I should see "Book 1"
    And I should not see "Assignment 2"
    # Course :  Course 01, Course 02.
    Then I open the "Activities" block preference
    And I follow "Conditions"
    And I set the field "id_config_preferences_filters_c_course_courseids" to "C1,C2,C3"
    And I press "Save changes"
    And I should see "Assignment 1"
    Then I should see "Book 1"
    And I should see "Assignment 2"

  Scenario:Activities Datasource: My enrolled courses condition
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    And I follow "Conditions"
    And I click on "My enrolled courses" "checkbox"
    And I set the field "id_config_preferences_filters_my_enrolled_courses_roleids" to "Student"
    And I press "Save changes"
    Then I press "Reset Dashboard for all users"
    Then I follow "Dashboard"
    And I should not see "My quiz B"
    And I should not see "Quiz 1"
    Then I log out
    And I log in as "student1"
    Then I follow "Dashboard"
    And I should see "Book 1"
    Then I log out
    And I log in as "teacher1"
    Then I follow "Dashboard"
    And I should not see "Assignment 1"
    Then I log out
    And I log in as "admin"
    Then I follow "Dashboard"
    And I should not see "Assignment 1"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    And I follow "Conditions"
    And I set the field "id_config_preferences_filters_my_enrolled_courses_roleids" to "Student, Non-editing teacher"
    And I press "Save changes"
    When I press "Reset Dashboard for all users"
    Then I log out
    And I log in as "student1"
    Then I follow "Dashboard"
    And I should see "Assignment 1"
    And I should see "Book 1"
    And I should see "My quiz B"
    Then I log out
    And I log in as "teacher1"
    Then I follow "Dashboard"
    And I should see "Book 1"
    And I should not see "My quiz B"
    Then I log out
