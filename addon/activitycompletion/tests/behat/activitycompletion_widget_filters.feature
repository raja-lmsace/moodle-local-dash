@local @local_dash @dashaddon @dashaddon_activitycompletion_widget_filters @mod_assign @javascript

Feature: Add activity completion widget filters in dash block
    In order to enable the activity completion widget filters in dash block on the dashboard
    As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | 0        | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate 			            | enddate  	                   |
      | Course 1 | C1        | CAT1     | 1                | 3           | ## 1 August 2024 08:00 ##  | ## 1 August 2025 08:00 ## 	 |
      | Course 2 | C2        | CAT2     | 1                | 2           | ## 1 July 2024 10.00 ##	  | ## 5 September 2025 08.00 ## |
    And the following "activities" exist:
      | activity | name         | course  | idnumber       | intro                   | section | submissiondrafts | assignsubmission_onlinetext_enabled | grade[modgrade_type] | modgrade_scale  | grade[modgrade_point] | gradepass | completion | completionsubmit | completionusegrade | completionpassgrade |
      | assign   | Assignment 1 | C1      | Assignment 1   | Welcome to Assignment 1 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 1          |                  |                    |                     |
      | assign   | Assignment 2 | C1      | Assignment 2   | Welcome to Assignment 2 | 2       | 0                | 1                                   |                      |                 | 100                   | 50        | 2          |                  | 1                  | 1                   |
      | assign   | Assignment 3 | C1      | Assignment 3   | Welcome to Assignment 3 | 3       | 0                | 1                                   |                      |                 | 100                   |           | 2          | 1                |                    |                     |
      | assign   | Assignment 4 | C1      | Assignment 4   | Welcome to Assignment 4 | 4       | 0                | 1                                   |                      |                 | 100                   | 50        | 2          |                  | 1                  | 1                   |
      | assign   | Assignment 5 | C2      | Assignment 5   | Welcome to Assignment 5 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 2          | 1                |                    |                     |
      | assign   | Assignment 6 | C2      | Assignment 6   | Welcome to Assignment 6 | 2       | 0                | 1                                   |                      |                 | 100                   | 50        | 2          |                  | 1                  | 1                   |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
      | teacher1 | teacher   | 1        | teacher1@example.com |
      | teacher2 | teacher   | 2        | teacher2@example.com |
      | parent   | parent    | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           | timestart                  | timeend |
      | student1 | C1     | student        | ## 1 August 2024 07:00 ##	| 0       |
      | student2 | C1     | student        | ## 1 July 2024 09.00 ##    | 0       |
      | student1 | C2     | student        | ## 1 August 2024 07:00 ##	| 0       |
      | student2 | C2     | student        | ## 1 July 2024 09.00 ##    | 0       |
      | student3 | C2     | student        | ## 18 August 2024 07.00 ## | 0       |
      | teacher1 | C1     | editingteacher | 0                          | 0       |
      | teacher2 | C1     | editingteacher | 0                          | 0       |
    And the following "mod_assign > submissions" exist:
      | assign       | user      | onlinetext                                                                           |
      | Assignment 1 | student1  | Birds are a group of warm-blooded vertebrates constituting the class Aves ...        |
      | Assignment 2 | student1  | Animals are multicellular, eukaryotic organisms in the biological kingdom Animalia.  |
      | Assignment 3 | student1  | A fish is an aquatic, anamniotic, gill-bearing vertebrate animal with swimming fins. |
      | Assignment 4 | student1  | Insects are hexapod invertebrates of the class Insecta.                              |
      | Assignment 1 | student2  | Birds are a group of warm-blooded vertebrates constituting the class Aves ...        |
      | Assignment 2 | student2  | Animals are multicellular, eukaryotic organisms in the biological kingdom Animalia.  |
      | Assignment 3 | student2  | A fish is an aquatic, anamniotic, gill-bearing vertebrate animal with swimming fins. |
      | Assignment 4 | student2  | Insects are hexapod invertebrates of the class Insecta.                              |
      | Assignment 5 | student1  | Birds are a group of warm-blooded vertebrates constituting the class Aves ...        |
      | Assignment 5 | student3  | Insects are hexapod invertebrates of the class Insecta.                              |
    And the following "groups" exist:
      | name       | course | idnumber | enablemessaging |
      | Group C1 1 | C1     | G1       | 1               |
      | Group C1 2 | C1     | G2       | 1               |
      | Group C2 1 | C2     | G3       | 1               |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
      | student3 | G1    |
      | student2 | G2    |
      | student1 | G3    |
      | student2 | G3    |
      | student3 | G3    |
    And I log out

  Scenario: Activity completion widget my group filter check
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Activity completion" block preference
    Then I click on "Filters" "link"
    And I set the field "Groups" to "1"
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log in as "student2"
    And I follow "Dashboard"
    And ".activity-completion-widget" "css_element" should exist
    And I click on "select[name='my_groups'] option:nth-child(2)" "css_element"
    And I click on "Show chart data" "link"
    And I should see "67%" in the "Assignment 5" "table_row"
    And I click on "select[name='my_groups'] option:nth-child(4)" "css_element"
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 5" "table_row"

  Scenario: Activity completion widget course sections filter check
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion with course sections filter"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Activity completion with course sections filter" block preference
    Then I click on "Filters" "link"
    And I set the field "Course sections" to "1"
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I set the field "config_preferences[filters][c_course][enabled]" to "1"
    And I set the field "config_preferences[filters][c_course][courseids][]" to "C1"
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I turn section "2" highlighting on
    And I log in as "student2"
    And I follow "Dashboard"
    And ".activity-completion-widget" "css_element" should exist
    #---Highlight section---#
    And I click on "select[name='c_sections'] option:nth-child(2)" "css_element"
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 2" "table_row"
    #---Section 2---#
    And I click on "select[name='c_sections'] option:nth-child(4)" "css_element"
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 1" "table_row"
    #---All course sections---#
    And I click on "select[name='c_sections'] option:nth-child(1)" "css_element"
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 1" "table_row"
    And I should see "100%" in the "Assignment 2" "table_row"
    And I should see "100%" in the "Assignment 3" "table_row"
    And I should see "100%" in the "Assignment 4" "table_row"
