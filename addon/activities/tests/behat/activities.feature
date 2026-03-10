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
    And the following "block_dash > dash blocks default" exist:
      | type       | name       | title      | fields | disablefields                   | filters                                                              |
      | datasource | activities | Activities | all    | c_idnumber, cc_idnumber         | Category, Course, Module Name, Activities tags, Type, Module purpose |
    And I log in as "admin"
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

  Scenario: Activities datasource list
    Given I log in as "student1"
    And ".dash-block-content .dash-table" "css_element" should exist
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test choice 2" in the "Activities" "block"
    And I should see "Test page 1" in the "Activities" "block"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should see "Test assignment 2" in the "Activities" "block"

  Scenario: Check the activities datasource fields
    Given I log in as "student1"
    And ".dash-block-content .dash-table" "css_element" should exist
    And I wait "20" seconds
    And the following should exist in the "dash-table" table:
    | Activity name     | Description                | ID number | Type     | Module Name | Module purpose  | Due date            |
    | Test choice 1     | Test choice 01 description | choice1   | Activity | Choice      | Communication   | ##+2day##%d/%m/%y## |
    | Test choice 2     | Test choice 02 description | choice2   | Activity | Choice      | Communication   |                     |
    | Test page 1       | Test page 01 description   | page      | Resource | Page        | Content         | ##+5day##%d/%m/%y## |
    | Test assignment 1 | Test assign 01 description | assign1   | Activity | Assignment  | Assessment      |                     |
    Then I click on "//a[contains(text(), 'Test choice 1')]" "xpath" in the "Test choice 1" "table_row"
    And I should see "Test choice 1"
    And I press "Mark as done"
    Then I follow "Dashboard"
    # Then I click on ".dash-table tbody tr:nth-child(5) td:nth-child(5) a" "css_element"
    Then I click on "//a[contains(text(), 'Test page 1')]" "xpath" in the "Test page 1" "table_row"
    And I should see "Test page 1"
    # Then I wait "10" seconds
    And I press "Mark as done"
    Then I follow "Dashboard"
    # check the activity completion or not.
    And the following should exist in the "dash-table" table:
    | Activity name     | Completion status | Completion date     |
    | Test choice 1     | Completed         | ##today##%d/%m/%y## |
    | Test page 1       | Completed         | ##today##%d/%m/%y## |
    And I should see "Category 01 / Course 2 / General" in the "Test choice 1" "table_row"

  Scenario: check the activities datasource filters
    Given I log in as "student1"
    # Category filter
    And ".dash-block-content .dash-table" "css_element" should exist
    Then I click on "select[name='cc_id'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Category 01')]" "xpath_element"
    And I should not see "Test choice 2" in the "Activities" "block"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should not see "Test choice 3" in the "Activities" "block"
    Then I click on "select[name='cc_id'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Category 02')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test choice 3" in the "Activities" "block"
    And I should not see "Test assignment 1" in the "Activities" "block"
    Then I reload the page

    # Course filter
    Then I click on "select[name='c_id'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Course 1')]" "xpath_element"
    And I should see "Test choice 2" in the "Activities" "block"
    And I should see "Test page 2" in the "Activities" "block"
    And I should not see "Test choice 1" in the "Activities" "block"
    And I should not see "Test choice 3" in the "Activities" "block"
    Then I click on "select[name='c_id'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Course 2')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test choice 2" in the "Activities" "block"
    Then I reload the page
    # Module filter
    Then I click on "select[name='m_id'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Choice')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test choice 2" in the "Activities" "block"
    Then I click on "select[name='m_id'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Page')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test page 1" in the "Activities" "block"
    Then I should not see "Test assign 1" in the "Activities" "block"
    Then I reload the page
    # Activity type.
    Then I click on "select[name='cm_type'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Activity')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    Then I click on "select[name='cm_type'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Resource')]" "xpath_element"
    And I should see "Test page 1" in the "Activities" "block"
    And I should see "Test assignment 1" in the "Activities" "block"
    Then I reload the page

    # Activities tags.
    Then I click on "select[name='cm_tags'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Tag of Assignment')]" "xpath_element"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    And I should not see "Test choice 1" in the "Activities" "block"
    Then I reload the page

    Then I click on "select[name='cm_tags'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Tag of Choice')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should not see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    Then I reload the page

    Then I click on "select[name='cm_tags'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Tag of Page')]" "xpath_element"
    And I should see "Test page 1" in the "Activities" "block"
    And I should not see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test choice 1" in the "Activities" "block"
    Then I reload the page

    Then I click on "select[name='cm_tags'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Tag of Assignment')]" "xpath_element"
    Then I click on "select[name='cm_tags'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Tag of Choice')]" "xpath_element"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    Then I reload the page
    # Module purpose.
    Then I click on "select[name='cm_purpose'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Communication')]" "xpath_element"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test choice 2" in the "Activities" "block"
    And I should not see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    Then I reload the page
    Then I click on "select[name='cm_purpose'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Content')]" "xpath_element"
    And I should see "Test page 1" in the "Activities" "block"
    And I should not see "Test choice 1" in the "Activities" "block"
    And I should not see "Test assignment 1" in the "Activities" "block"
    Then I reload the page
    Then I click on "select[name='cm_purpose'] + span .selection" "css_element"
    Then I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Assessment')]" "xpath_element"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    And I should not see "Test choice 1" in the "Activities" "block"

  Scenario: Activities datasource : tags condition
    Given I log in as "student1"
    And ".dash-block-content .dash-table" "css_element" should exist
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should see "Test page 1" in the "Activities" "block"
    And I should see "Test choice 1" in the "Activities" "block"
    Then I log out
    # Set tag as Tag of choice
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I set the field "Activities tags" to "1"
    And I set the field "Tags" to "Tag of Choice"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    # Tag of choice
    Given I log in as "student1"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should not see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    Then I log out
    #Set tag as Tag of Assignment
    Then I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I set the field "Tags" to "Tag of Assignment"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    # Tag of Assignment
    Given I log in as "student1"
    And I should see "Test assignment 1" in the "Activities" "block"
    And I should not see "Test choice 1" in the "Activities" "block"
    And I should not see "Test page 1" in the "Activities" "block"
    Then I log out
    # Set tag as Tag of choice, Tag of Page
    Then I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then I open the "Activities" block preference
    Then I click on "Conditions" "link"
    And I set the field "Tags" to "Tag of Choice, Tag of Page"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    # Tag of choice
    Given I log in as "student1"
    And I should not see "Test assignment 1" in the "Activities" "block"
    And I should see "Test choice 1" in the "Activities" "block"
    And I should see "Test page 1" in the "Activities" "block"
    Then I log out
