@local @local_dash @dash_conditions @javascript
Feature: Add conditions to the datasource in dash block
  In order to enable the course completion widgets in dash block on the dashboard
  As an admin

  Background:
    Given the following "custom field categories" exist:
      | name  | component   | area   | itemid |
      | Other | core_course | course | 0      |
    And the following "custom fields" exist:
      | name    | category | type | shortname |
      | Field 1 | Other    | text | text      |
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category 1 | 0        | CAT1     |
      | Category 2 | 0        | CAT2     |
      | Category 3 | CAT2     | CAT3     |
      | Category 4 | 0        | CAT4     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1 | C1        | 0        | 1                | 3           |                |                 |
      | Course 2 | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3 | C3        | CAT2     | 1                | 1           |                |                 |
      | Course 4 | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 5 | C5        | CAT3     | 1                | 2           |                |                 |
      | Course 6 | C6        | CAT3     | 1                | 2           |                |                 |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend |
      | student1 | C1     | student | ## today ## | 0       |
      | student1 | C2     | student | 0           | 0       |
      | student1 | C3     | student | 0           | 0       |
      | student1 | C4     | student | 0           | 0       |
      | student2 | C2     | student | 0           | 0       |
      | student2 | C3     | student | 0           | 0       |
      | student3 | C4     | student | 0           | 0       |
      | admin    | C1     | student | 0           | 0       |
      | admin    | C2     | student | 0           | 0       |
      | admin    | C3     | student | 0           | 0       |
      | admin    | C4     | student | 0           | 0       |
    And I log in as "admin"
    And I log out

  Scenario: Self enrolment options.
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "New Dash" "block"
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Conditions" "link"
    And I set the field "Enrollment options" to "1"
    And I set the field "Enrolment methods" to "Self enrolment"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    When I add "Self enrolment" enrolment method in "Course 1" dashwith:
      | Custom instance name | Test student enrolment |
    And I log out
    And I log in as "student3"
    And I should see "Course 1" in the "New Dash" "block"

  Scenario: Custom field conditions check.
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "New Dash" "block"
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Conditions" "link"
    And I should see "Field 1"
    And I set the field "Field 1" to "1"
    And I set the field with xpath "//input[@name='config_preferences[filters][text][value]']" to "test"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should not see "Course 4" in the "New Dash" "block"
    And I am on "Course 4" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | Field 1 | test |
    And I press "Save and display"
    And I follow dashboard
    And I should see "Course 4" in the "New Dash" "block"

  Scenario Outline: Condition the courses list based on course start and end dates
    Given the following "courses" exist:
      | fullname       | shortname | category | startdate          | enddate              |
      | Course Past    | CD1       | CAT4     | ##1 year ago##     | ##1 month ago##      |
      | Course Future  | CD2       | CAT4     | ##tomorrow##       |                      |
      | Course Present | CD3       | CAT4     | ##1 year ago##     | ##tomorrow##         |
      | Course 4       | CD4       | CAT4     | ##1 year ago##     |                      |
      | Course 5       | CD5       | CAT4     | ##1 year ago##     | ##yesterday##        |
      | Course 6       | CD6       | CAT4     | ##tomorrow +1day## | ##tomorrow +15days## |
    And I log in as "admin"
    And I create dash "Courses" datasource
    And I wait until the page is ready
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "New Dash" "block"
    And I wait "3" seconds
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "#id_config_preferences_available_fields_c_shortname_visible" "css_element"
    And I click on "#id_config_preferences_available_fields_c_fullname_visible" "css_element"
    And I click on "#id_config_preferences_available_fields_c_startdate_visible" "css_element"
    And I click on "#id_config_preferences_available_fields_c_status_visible" "css_element"
    And I set the field "Sort direction" to "DESC"
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_coursedates][enabled]" to "1"
    And I set the field "id_config_preferences_filters_c_coursedates_coursedates" to "<Conditionvalue>"
    And I click on "Save changes" "button"
    Then "CD2" "table_row" <Futurecoursestatus> exist
    And "CD6" "table_row" <Futurecoursestatus> exist
    And "CD3" "table_row" <Presentcoursestatus> exist
    And "CD4" "table_row" <Presentcoursestatus> exist
    And "CD1" "table_row" <Pastcoursestatus> exist
    And "CD5" "table_row" <Pastcoursestatus> exist

    Examples:
      | Futurecoursestatus | Presentcoursestatus | Pastcoursestatus | Conditionvalue        |
      | should             | should              | should           | Past, Present, Future |
      | should             | should not          | should not       | Future                |
      | should not         | should              | should not       | Present               |
      | should not         | should not          | should           | Past                  |

  Scenario: Filter the courses list based on course start and end dates

    Given the following "courses" exist:
      | fullname       | shortname | category | startdate          | enddate              |
      | Course Past    | CD1       | CAT4     | ##1 year ago##     | ##1 month ago##      |
      | Course Future  | CD2       | CAT4     | ##tomorrow##       |                      |
      | Course Present | CD3       | CAT4     | ##1 year ago##     | ##tomorrow##         |
      | Course 4       | CD4       | CAT4     | ##1 year ago##     |                      |
      | Course 5       | CD5       | CAT4     | ##1 year ago##     | ##yesterday##        |
      | Course 6       | CD6       | CAT4     | ##tomorrow +1day## | ##tomorrow +15days## |
    And I log in as "admin"
    And I create dash "Courses" datasource
    And I wait until the page is ready
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "New Dash" "block"
    And I click on "Fields" "link"
    And I click on "#id_config_preferences_available_fields_c_shortname_visible" "css_element"
    And I click on "#id_config_preferences_available_fields_c_fullname_visible" "css_element"
    And I click on "#id_config_preferences_available_fields_c_startdate_visible" "css_element"
    And I click on "#id_config_preferences_available_fields_c_status_visible" "css_element"
    And I set the field "Sort direction" to "DESC"
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course_categories_condition][enabled]" to "1"
    And I set the field "id_config_preferences_filters_c_course_categories_condition_coursecategories" to "Category 4"
    Then I click on "Filter" "link"
    And I set the field "id_config_preferences_filters_f_coursedates_enabled" to "1"
    # And I set the field "id_config_preferences_filters_c_coursedates_coursedates" to "<Conditionvalue>"
    And I click on "Save changes" "button"
    And "CD2" "table_row" should exist
    And "CD6" "table_row" should exist
    And "CD3" "table_row" should exist
    And "CD4" "table_row" should exist
    And "CD1" "table_row" should exist
    And "CD5" "table_row" should exist
    When I set the field "f_coursedates" to "Past"
    Then I should not see "CD2" in the "New Dash" "block"
    And I should not see "CD6" in the "New Dash" "block"
    And I should not see "CD3" in the "New Dash" "block"
    And I should not see "CD4" in the "New Dash" "block"
    And I should see "CD1" in the "New Dash" "block"
    And I should see "CD5" in the "New Dash" "block"
    When I set the field "f_coursedates" to "Present"
    Then I should not see "CD2" in the "New Dash" "block"
    And I should not see "CD6" in the "New Dash" "block"
    And I should not see "CD1" in the "New Dash" "block"
    And I should not see "CD5" in the "New Dash" "block"
    And I should see "CD3" in the "New Dash" "block"
    And I should see "CD4" in the "New Dash" "block"
    When I set the field "f_coursedates" to "Future"
    Then I should see "CD2" in the "New Dash" "block"
    And I should see "CD6" in the "New Dash" "block"
    And I should not see "CD1" in the "New Dash" "block"
    And I should not see "CD5" in the "New Dash" "block"
    And I should not see "CD3" in the "New Dash" "block"
    And I should not see "CD4" in the "New Dash" "block"
