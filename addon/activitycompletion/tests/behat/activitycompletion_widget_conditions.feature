@local @local_dash @dashaddon @dashaddon_activitycompletion_widget_conditions @mod_assign @javascript

Feature: Add activity completion widget conditions in dash block
    In order to enable the activity completion widget conditions in dash block on the dashboard
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
      | student2 | G1    |
      | student2 | G3    |
    And the following "cohorts" exist:
      | name    | idnumber  |
      | Cohort1 | CH1       |
      | Cohort2 | CH2       |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | CH1    |
      | teacher1 | CH1    |
      | student2 | CH2    |
      | student3 | CH2    |
    And I log out

  Scenario: Activity completion widget progress
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
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I am on "Course 1" course homepage
    And I click on "Assignment 2" "link" in the "#page-content" "css_element"
    And I click on assignment submissions link
    And I click on "#action-menu-1" "css_element" in the "Student 1" "table_row"
    And I choose "Grade" in the open action menu
    And I set the field "Grade out of 100" to "30"
    And I press "Save and show next"
    And I set the field "Grade out of 100" to "30"
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I click on "Assignment 4" "link" in the "#page-content" "css_element"
    And I click on assignment submissions link
    And I click on "#action-menu-1" "css_element" in the "Student 1" "table_row"
    And I choose "Grade" in the open action menu
    And I set the field "Grade out of 100" to "70"
    And I press "Save and show next"
    And I set the field "Grade out of 100" to "70"
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I follow "Dashboard"
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "Activity completion progress of students"
    And I should see "100%" in the "Assignment 1" "table_row"
    And I should see "100%" in the "Assignment 2" "table_row"
    And I should see "100%" in the "Assignment 3" "table_row"
    And I should see "100%" in the "Assignment 4" "table_row"

  Scenario: Activity completion widget courses condition check
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
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I set the field "config_preferences[filters][c_course][enabled]" to "1"
    And I set the field "config_preferences[filters][c_course][courseids][]" to "C2"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log in as "student1"
    And I follow "Dashboard"
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "67%" in the "Assignment 5" "table_row"
    And I should see "100%" in the "Assignment 6" "table_row"

  Scenario: Activity completion widget current course condition check
    Given I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion"
    And I press "Save changes"
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I press "Save changes"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "33%" in the "Assignment 5" "table_row"
    And I should see "100%" in the "Assignment 6" "table_row"

  Scenario: Activity completion widget my groups condition check
    Given I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion"
    And I press "Save changes"
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "33%" in the "Assignment 5" "table_row"
    And I should see "100%" in the "Assignment 6" "table_row"
    And I log out
    And I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I set the field "config_preferences[filters][group][enabled]" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 5" "table_row"
    And I should not see "50%" in the "Assignment 5" "table_row"

  Scenario: Activity completion widget members of my cohorts only
    Given I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion"
    And I press "Save changes"
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I press "Save changes"
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "33%" in the "Assignment 5" "table_row"
    And I should see "100%" in the "Assignment 6" "table_row"
    And I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I set the field "config_preferences[filters][users_mycohort][enabled]" to "1"
    And I press "Save changes"
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "50%" in the "Assignment 5" "table_row"
    And I should not see "66.7%" in the "Assignment 5" "table_row"

  Scenario: Activity completion widget selected cohort memebers only
    Given I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion"
    And I press "Save changes"
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I set the field "config_preferences[filters][cohort][enabled]" to "1"
    And I set the field "config_preferences[filters][cohort][cohorts][]" to "Cohort2"
    And I press "Save changes"
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "50%" in the "Assignment 5" "table_row"
    And I should see "100%" in the "Assignment 6" "table_row"
    And I log in as "admin"
    And I am on "Course 2" course homepage
    And I turn dash block editing mode on
    And I open the "Activity completion" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I set the field "config_preferences[filters][cohort][enabled]" to "0"
    And I press "Save changes"
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "33%" in the "Assignment 5" "table_row"
    And I should not see "50%" in the "Assignment 5" "table_row"
    And I should see "100%" in the "Assignment 6" "table_row"

  Scenario: Activity completion widget relation condition check
    Given I log in as "admin"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
    And I wait until the page is ready
    #---Create new parent role---#
    And I set the following fields to these values:
      | Short name                              | Parent |
      | Custom full name                        | Parent |
      | contextlevel30                          | 1      |
      | moodle/user:viewdetails                 | 1      |
      | moodle/user:viewalldetails              | 1      |
      | moodle/user:readuserblogs               | 1      |
      | moodle/user:readuserposts               | 1      |
      | moodle/user:viewuseractivitiesreport    | 1      |
      | moodle/user:editprofile                 | 1      |
      | tool/policy:acceptbehalf                | 1      |
    And I click on "Create this role" "button"
    And I follow "Dashboard"
    #---Assign parent to child---#
    And I am on the "student2" "user > profile" page
    And I click on "Preferences" "link" in the ".profile_tree" "css_element"
    And I follow "Assign roles relative to this user"
    And I follow "Parent"
    And I set the field "addselect" to "parent 1 (student1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I am on the "student3" "user > profile" page
    And I click on "Preferences" "link" in the ".profile_tree" "css_element"
    And I follow "Assign roles relative to this user"
    And I follow "Parent"
    And I set the field "addselect" to "parent 1 (student1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    #---Condition setting---#
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set User i manage in conditions---#
    And I add the "Dash" block
    And I click on "#id_config_data_source_idnumber_dashaddon_activitycompletionwidgetactivitycompletion_widget" "css_element"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Activity completion"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "0"
    And I set the field "config_preferences[filters][parentrole][enabled]" to "1"
    And I set the field "config_preferences[filters][parentrole][roleids][]" to "Parent"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I log in as "parent"
    And I follow "Dashboard"
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 1" "table_row"
    And I should see "50%" in the "Assignment 5" "table_row"
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][parentrole][enabled]" to "0"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I log in as "parent"
    And ".activity-completion-widget" "css_element" should exist
    And I click on "Show chart data" "link"
    And I should see "100%" in the "Assignment 1" "table_row"
    And I should see "67%" in the "Assignment 5" "table_row"
