@local @local_dash @dashaddon @dashaddon_calendar_events @javascript

Feature: Add calendar events datasource in dash block
    In order to display calendar event fields in the dash block on the dashboard
    As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
    And the following "courses" exist:
      | fullname  | shortname | category | enablecompletion | numsections |
      | Course 1  | C1        | CAT1     | 1                | 3           |
      | Course 2  | C2        | CAT1     | 1                | 2           |
      | Course 3  | C3        | CAT1     | 1                | 2           |
      | Main      | MAIN      | CAT1     | 1                | 3           |
      | Reference | REF       | CAT1     | 1                | 3           |
      | Unrelated | UNREL     | CAT1     | 1                | 3           |
    And the following "activities" exist:
      | activity | name         | course | idnumber     | intro                   | section | duedate                    |
      | assign   | Assignment 1 | C1     | Assignment 1 | Welcome to Assignment 1 | 1       | ## 1 August 2026 09:00 ##  |
      | assign   | Assignment 2 | C2     | Assignment 2 | Welcome to Assignment 2 | 1       | ## 15 August 2026 10:00 ## |
      | assign   | Assignment 3 | C3     | Assignment 3 | Welcome to Assignment 3 | 1       | ## 30 August 2026 11:00 ## |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student1 | C2     | student        |
      | student1 | C3     | student        |
      | teacher1 | MAIN   | editingteacher |
      | teacher1 | REF    | editingteacher |
      | teacher1 | UNREL  | editingteacher |
      | student1 | MAIN   | student        |
      | student1 | REF    | student        |
      | student1 | UNREL  | student        |
    And the following "events" exist:
      | name              | eventtype | course |
      | Main course event | course    | MAIN   |
      | Ref course event  | course    | REF    |
      | Unrelated event   | course    | UNREL  |

  Scenario: Calendar events datasource: Event dates, Event times and Event date/times fields display
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Calendar events" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Calendar Events"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    # Enable the three new fields in block preferences
    And I open the "Calendar Events" block preference
    And I click on "Fields" "link"
    And I set the field "Event dates" to "1"
    And I set the field "Event times" to "1"
    And I set the field "Event date/times" to "1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I follow "Dashboard"
    # Verify the three field headers appear as columns
    And I should see "Event dates" in the ".dash-table thead" "css_element"
    And I should see "Event times" in the ".dash-table thead" "css_element"
    And I should see "Event date/times" in the ".dash-table thead" "css_element"
    And I log out
    # Verify field headers and values are visible for enrolled student
    And I log in as "student1"
    And I follow "Dashboard"
    And I should see "Calendar Events" in the ".block_dash" "css_element"
    # Verify event rows are present
    And I should see "Assignment 1" in the ".dash-table" "css_element"
    And I should see "Assignment 2" in the ".dash-table" "css_element"
    And I should see "Assignment 3" in the ".dash-table" "css_element"
    # Verify the three field headers are visible
    And I should see "Event dates" in the ".dash-table thead" "css_element"
    And I should see "Event times" in the ".dash-table thead" "css_element"
    And I should see "Event date/times" in the ".dash-table thead" "css_element"
    # Verify Event dates, Event times and Event date/times values are populated
    And the following should exist in the "dash-table" table:
      | Event dates               | Event times | Event date/times                    |
      | Saturday, 1 August 2026   | 9:00 AM     | Saturday, 1 August 2026, 9:00 AM    |
      | Saturday, 15 August 2026  | 10:00 AM    | Saturday, 15 August 2026, 10:00 AM  |
      | Sunday, 30 August 2026    | 11:00 AM    | Sunday, 30 August 2026, 11:00 AM    |
    And I log out

  @mod_subcourse
  Scenario: Scope picker appears only when mod_subcourse is installed and defaults to both scopes
    Given I log in as "teacher1"
    And I am on "Main" course homepage with editing mode on
    And I add a "subcourse" activity to course "Main" section "1" and I fill the form with:
      | Subcourse name                    | Linked reference course |
      | Fetch grades from                 | Reference (REF)         |
      | Redirect to the referenced course | 0                       |
    And I log out
    And I log in as "admin"
    And I am on "Main" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Calendar events" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Calendar events"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Calendar events" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    # The mod_subcourse-only scope picker must be visible once the condition is enabled.
    Then "config_preferences[filters][current_course][scopes][]" "field" should exist
    And the "config_preferences[filters][current_course][scopes][]" select box should contain "Current course"
    And the "config_preferences[filters][current_course][scopes][]" select box should contain "Subcourses of current course"
    # First-time default seeds both scopes; saving must show events from MAIN and REF but never UNREL.
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Main course event" in the "Calendar events" "block"
    And I should see "Ref course event" in the "Calendar events" "block"
    And I should not see "Unrelated event" in the "Calendar events" "block"

  @mod_subcourse
  Scenario: Limiting the scope to Current course hides subcourse-referenced events
    Given I log in as "teacher1"
    And I am on "Main" course homepage with editing mode on
    And I add a "subcourse" activity to course "Main" section "1" and I fill the form with:
      | Subcourse name                    | Linked reference course |
      | Fetch grades from                 | Reference (REF)         |
      | Redirect to the referenced course | 0                       |
    And I log out
    And I log in as "admin"
    And I am on "Main" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Calendar events" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Calendar events"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Calendar events" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    # Keep only "Current course"; explicitly deselect the subcourses option.
    And I set the field "config_preferences[filters][current_course][scopes][]" to "Current course"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Main course event" in the "Calendar events" "block"
    And I should not see "Ref course event" in the "Calendar events" "block"
    And I should not see "Unrelated event" in the "Calendar events" "block"
    # Re-open preferences to verify the deselection persisted.
    And I open the "Calendar events" block preference
    And I click on "Conditions" "link"
    And the field "Scope" matches value "Current course"

  @mod_subcourse
  Scenario: Limiting the scope to Subcourses of current course shows only the linked course's events
    Given I log in as "teacher1"
    And I am on "Main" course homepage with editing mode on
    And I add a "subcourse" activity to course "Main" section "1" and I fill the form with:
      | Subcourse name                    | Linked reference course |
      | Fetch grades from                 | Reference (REF)         |
      | Redirect to the referenced course | 0                       |
    And I log out
    And I log in as "admin"
    And I am on "Main" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Calendar events" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Calendar events"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Calendar events" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    # Keep only "Subcourses of current course".
    And I set the field "config_preferences[filters][current_course][scopes][]" to "Subcourses of current course"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Ref course event" in the "Calendar events" "block"
    And I should not see "Main course event" in the "Calendar events" "block"
    And I should not see "Unrelated event" in the "Calendar events" "block"

  @mod_subcourse
  Scenario: Hidden subcourse activities do not contribute their referenced course
    Given I log in as "teacher1"
    And I am on "Main" course homepage with editing mode on
    And I add a "subcourse" activity to course "Main" section "1" and I fill the form with:
      | Subcourse name                    | Linked reference course |
      | Fetch grades from                 | Reference (REF)         |
      | Redirect to the referenced course | 0                       |
    And I log out
    And I log in as "admin"
    And I am on "Main" course homepage
    And I turn editing mode on
    # Hide the subcourse activity; the condition's SQL excludes cm.visible = 0
    # so events from REF must drop out of the "Subcourses" scope.
    And I open "Linked reference course" actions menu
    And I choose "Hide" in the open action menu
    And I turn editing mode off
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Calendar events" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Calendar events"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Calendar events" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I set the field "config_preferences[filters][current_course][scopes][]" to "Subcourses of current course"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should not see "Ref course event" in the "Calendar events" "block"
    And I should not see "Main course event" in the "Calendar events" "block"

  @mod_subcourse
  Scenario: Student on the course page only sees events from the configured scopes
    Given I log in as "teacher1"
    And I am on "Main" course homepage with editing mode on
    And I add a "subcourse" activity to course "Main" section "1" and I fill the form with:
      | Subcourse name                    | Linked reference course |
      | Fetch grades from                 | Reference (REF)         |
      | Redirect to the referenced course | 0                       |
    And I log out
    # Admin configures the block with both scopes (the default), then a student
    # visiting the same course page must see the OR-combined result.
    And I log in as "admin"
    And I am on "Main" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Calendar events" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Calendar events"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Calendar events" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I press "Save changes"
    And I log out
    When I log in as "student1"
    And I am on "Main" course homepage
    Then I should see "Main course event" in the "Calendar events" "block"
    And I should see "Ref course event" in the "Calendar events" "block"
    And I should not see "Unrelated event" in the "Calendar events" "block"
