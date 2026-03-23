@local @local_dash @dashaddon @dashaddon_course_completions @javascript
Feature: Add course completion widget in dash block
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
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1 | C1        | 0        | 1                | 3           |                |                 |
      | Course 2 | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3 | C3        | CAT2     | 1                | 1           |                |                 |
      | Course 4 | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 5 | C5        | CAT3     | 1                | 2           |                |                 |
      | Course 6 | C6        | CAT3     | 1                | 2           |                |                 |

    And the following "activities" exist:
      | activity | name      | course | idnumber | intro            | section | completion |
      | page     | testpage1 | C1     | page1    | Page description | 1       | 1          |
      | page     | testpage2 | C1     | page1    | Page description | 2       | 1          |
      | page     | testpage3 | C1     | page1    | Page description | 3       | 1          |
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
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I add the "Dash" block
    # And I create dash "Course completions" datasource
    And I click on "#id_config_data_source_idnumber_dashaddon_course_completionswidgetcompletion_widget" "css_element"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Course completions"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | testpage1 | 1 |
      | testpage2 | 1 |
      | testpage3 | 1 |
    And I press "Save changes"
    And I log out

  Scenario: Completion courses list
    Given I log in as "student1"
    And ".datatable" "css_element" should exist
    And I should see "Course 1" in the "Course completions" "block"
    And I should see "2" in the "[data-title='Course 1'] .notyetstarted" "css_element"
    And I should see "0" in the "[data-title='Course 1'] .completed" "css_element"
    And I should see "0" in the "[data-title='Course 1'] .inprogress" "css_element"
    And I am on "Course 1" course homepage
    And I should see "testpage1"
    And I click on "Mark as done" "button"
    And I trigger cron
    And I am on homepage
    And I should see "1" in the "[data-title='Course 1'] .notyetstarted" "css_element"
    And I should see "1" in the "[data-title='Course 1'] .inprogress" "css_element"

  Scenario: Widget completion conditions
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course completions" block preference
    Then I click on "Conditions" "link"
    And I set the field "My enrolled courses" to "1"
    And I set the field "With role(s)" to "Student"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student2"
    And I should see "Course 3" in the "Course completions" "block"
    And I should see "3" in the "[data-title='Course 3'] .notyetstarted" "css_element"
    And I should see "0" in the "[data-title='Course 3'] .completed" "css_element"
    And I should see "0" in the "[data-title='Course 3'] .inprogress" "css_element"

  Scenario: Course dates condition test
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course completions" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][coursedates][enabled]" to "1"
    And I set the field "Course dates" to "Past"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student3"
    And I should see "Course 4" in the "Course completions" "block"
    And I should see "3" in the "[data-title='Course 4'] .notyetstarted" "css_element"

  Scenario: Custom field conditions for completion
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course completions" block preference
    Then I click on "Conditions" "link"
    And I should see "Field 1"
    And I set the field "Field 1" to "1"
    And I set the field with xpath "//input[@name='config_preferences[filters][text][value]']" to "test"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And ".datatable" "css_element" should not exist in the "Course completions" "block"
    And I am on "Course 4" course homepage
    And I navigate to settings in current page administration
    And I set the following fields to these values:
      | Field 1 | test |
    And I press "Save and display"
    And I follow dashboard
    And I should see "Course 4" in the "Course completions" "block"
