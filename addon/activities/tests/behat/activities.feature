@local @local_dash @dashaddon @dashaddon_activities @javascript
Feature: Add activities widget in dash block
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
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | enablecompletion |
      | Course 1 | C1        | 0        | 1                | 3           |      1           |
      | Course 2 | C2        | CAT1     | 0                | 2           |      1           |
      | Course 3 | C3        | CAT2     | 1                | 1           |      1           |
			| Course 4 | C4        | CAT3     | 1                | 1           |      1           |
			| Course 5 | C5        | CAT3     | 1                | 1           |      1           |
    And the following "activities" exist:
      | activity | course | idnumber  | name                      | intro                        | section | completion | completionview | completionexpected  |
      | choice   | C2     | choice1   | Test choice 1             | Test choice 01 description   |    0    |  1          | 0              | ##+2days##         |
      | choice   | C1     | choice2   | Test choice 2             | Test choice 02 description   |    0    |  0          | 0              | 0                  |
      | choice   | C3     | choice3   | Test choice 3             | Test choice 03 description   |    0    |  0          | 0              | 0                  |
      | choice   | C2     | choice4   | Test choice 4             | Test choice 04 description   |    0    |  0          | 0              | 0                  |
      | page     | C2     | page1     | Test page 1               | Test page 01 description     |    1    |  1          | 0              | ##+5days##         |
      | page     | C1     | page2     | Test page 2               | Test page 02 description     |    0    |  0          | 0              | 0                  |
      | page     | C3     | page3     | Test page 3               | Test page 03 description     |    0    |  0          | 0              | 0                  |
      | assign   | C5     | assign1   | Test assignment 1         | Test assign 01 description   |    2    |  0          | 0              | ##+7days##         |
			| assign   | C5     | assign2   | Test assignment 2         | Test assign 02 description   |    0    |  0          | 0              | 0                  |
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
    And the following "tags" exist:
      | name               | isstandard  |
      | Tag of Choice      | 1           |
      | Tag of Page        | 1           |
      | Tag of Assignment  | 1           |
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I create dash "Activities" datasource
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Activities"
    And I set the following fields to these values:
        | Region | content |
    And I press "Save changes"
    Then I open the "Activities" block preference
    And I reload the page
    Then I open the "Activities" block preference
    Then I click on "Fields" "link"
    And I click on "Select all" "button"
    Then I click on "Filters" "link"
    Then I set the following fields to these values:
        | Category        | 1 |
        | Course          | 1 |
        | Module Name     | 1 |
        | Activities tags | 1 |
        | Type            | 1 |
        | Module purpose  | 1 |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    Then I am on the "Test choice 1" "choice activity" page
    And I follow "Settings"
    And I expand all fieldsets
    And I set the following fields to these values:
        | Tags    | Tag of Choice |
    And I press "Save and display"
    Then I am on the "Test page 1" "page activity" page
    And I follow "Settings"
    And I expand all fieldsets
    And I set the following fields to these values:
        | Tags    | Tag of Page |
    And I press "Save and display"
    Then I am on the "Test assignment 1" "assign activity" page
    And I follow "Settings"
    And I expand all fieldsets
    And I set the following fields to these values:
        | Tags    | Tag of Assignment |
    And I press "Save and display"
    And I log out

  Scenario: activities datasource list
    Given I log in as "student1"
    And ".dash-block-content .dash-table" "css_element" should exist
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test choice 2" in the "Activities" "block"
    And I should see "Test page 1" in the "Activities" "block"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should see "Test assignment 2" in the "Activities" "block"
