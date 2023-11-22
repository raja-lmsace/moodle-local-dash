@local @local_dash @dash_conditions @javascript
Feature: Add conditions to the datasource in dash block
  In order to enable the course completion widgets in dash block on the dashboard
  As an admin

  Background:
    Given the following "custom field categories" exist:
      | name  | component   | area   | itemid |
      | Other | core_course | course | 0      |
    And the following "custom fields" exist:
      | name    | category      | type     | shortname     |
      | Field 1 | Other         | text     | text    |
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 1  | 0        | CAT1     |
      | Category 2  | 0        | CAT2     |
      | Category 3  | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion| numsections| startdate | enddate |
      | Course 1 | C1        | 0        |     1           |         3  |           |         |
      | Course 2 | C2        | CAT1     |     0           |         2  |           |         |
      | Course 3 | C3        | CAT2     |     1           |         1  |           |         |
      | Course 4 | C4        | CAT3     |     1           |         2  |##1 year ago##| ##1 month ago##  |
      | Course 5 | C5        | CAT3     |     1           |         2  |           |         |
      | Course 6 | C6        | CAT3     |     1           |         2  |           |         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two   | student2@example.com    |
      | student3 | Student   | Three   | student3@example.com    |
    And the following "course enrolments" exist:
      | user | course | role             |   timestart | timeend   |
      | student1 | C1 | student          | ## today ## |     0     |
      | student1 | C2 | student          |   0         |     0       |
      | student1 | C3 | student          |   0         |     0       |
      | student1 | C4 | student          |   0         |     0       |
      | student2 | C2 | student          |   0         |     0       |
      | student2 | C3 | student          |   0         |     0       |
      | student3 | C4 | student          |   0         |     0       |
      | admin | C1    | student          |   0         |     0       |
      | admin | C2    | student          |   0         |     0       |
      | admin | C3    | student          |   0         |     0       |
      | admin | C4    | student          |   0         |     0       |
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn block editing mode on
    And I add the "Dash" block
    And I configure the "New Dash" block
    And I click on "#id_config_data_source_idnumber_local_dashlocalblock_dashcourses_data_source" "css_element"
    And I set the following fields to these values:
      | Region  | content |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out

  Scenario: Self enrolment options.
    Given I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn block editing mode on
    And I click on "Preferences" "button" in the "Dash" "block"
    Then I click on "Fields" "link"
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
    And I should see "Course 1" in the "Dash" "block"

  Scenario: Custom field conditions check.
    Given I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn block editing mode on
    And I click on "Preferences" "button" in the "Dash" "block"
    Then I click on "Fields" "link"
    And I set the field "Course: Full name" to "1"
    Then I click on "Conditions" "link"
    And I should see "Field 1"
    And I set the field "Field 1" to "1"
    And I set the field with xpath "//input[@name='config_preferences[filters][text][value]']" to "test"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should not see "Course 4" in the "Dash" "block"
    And I am on "Course 4" course homepage
    And I navigate to settings in current page administration
    And I set the following fields to these values:
      | Field 1 | test |
    And I press "Save and display"
    And I follow dashboard
    And I should see "Course 4" in the "Dash" "block"
