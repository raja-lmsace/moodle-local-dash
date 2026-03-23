@local @local_dash @dashaddon @dashaddon_activity_completion @mod_assign @javascript

Feature: Add activity completion datasource in dash block
     In order to enable the activity completion datasource in dash block on the dashboard
     As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections |
      | Course 1 | C1        | CAT1     | 1                | 3           |
    And the following "activities" exist:
      | activity | name         | course  | idnumber       | intro                   | section | submissiondrafts | assignsubmission_onlinetext_enabled | grade[modgrade_type] | modgrade_scale  | grade[modgrade_point] | gradepass | completion | completionsubmit | completionusegrade | completionpassgrade |
      | assign   | Assignment 1 | C1      | Assignment 1   | Welcome to Assignment 1 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 0          |                  |                    |                     |
      | assign   | Assignment 2 | C1      | Assignment 2   | Welcome to Assignment 2 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 1          |                  |                    |                     |
      | assign   | Assignment 3 | C1      | Assignment 3   | Welcome to Assignment 3 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 2          | 1                |                    |                     |
      | assign   | Assignment 4 | C1      | Assignment 4   | Welcome to Assignment 4 | 1       | 0                | 1                                   |                      |                 | 100                   | 50        | 2          |                  | 1                  | 1                   |
      | assign   | Grade-scale  | C1      | Assignment 5   | Welcome to Assignment 5 | 1       | 0                | 1                                   | scale                | 2               |                       |           |            |                  |                    |                     |
      | assign   | Grade-point  | C1      | Assignment 6   | Welcome to Assignment 6 | 1       | 0                | 1                                   | point                |                 | 80                    |           |            |                  |                    |                     |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | student   | 3        | student3@example.com |
      | teacher1 | teacher   | 1        | teacher1@example.com |
      | teacher2 | teacher   | 2        | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           | timestart   | timeend |
      | student1 | C1     | student        | ## today ## | 0       |
      | student2 | C1     | student        | 0           | 0       |
      | student3 | C1     | student        | 0           | 0       |
      | teacher1 | C1     | editingteacher | 0           | 0       |
      | teacher2 | C1     | editingteacher | 0           | 0       |
    And the following "mod_assign > submissions" exist:
      | assign       | user      | onlinetext                   |
      | Assignment 1 | student1  | Birds are a group of warm-blooded vertebrates constituting the class Aves ...  |
      | Assignment 2 | student1  | Animals are multicellular, eukaryotic organisms in the biological kingdom Animalia. |
      | Assignment 3 | student1  | A fish is an aquatic, anamniotic, gill-bearing vertebrate animal with swimming fins. |
      | Assignment 4 | student1  | Insects are hexapod invertebrates of the class Insecta. |
      | Assignment 5 | student1  | Assignment 5 grading - scale |
      | Assignment 6 | student1  | Assignment 6 grading - point |
    And the following "block_dash > dash blocks default" exist:
      | type       | name                  | title               | fields                                                                                                                                                                                                                    | filters      |
      | datasource | activity_completion   | Activity completion | Module icon, Activity Name (linked), Fullname linked, Completion override by, Completion override date, Start date, Due date, Grade max, Grade to pass, Current grade, Activity button, Toggle completion, Grade activity | Course, User |
    #---Student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And the manual completion button of "Assignment 2" is displayed as "Mark as done"
    And I toggle the manual completion state of "Assignment 2"
    And the manual completion button of "Assignment 2" is displayed as "Done"
    And I log out

  Scenario: Override activity completion and check as student
    #---Teacher login---#
    And I log in as "teacher1"
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash h3.card-title" "css_element"
    And I click on "select[name='u_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'student1')]" "xpath_element"
    #---Check toggle completion enabled in completed activity---#
    And I should see "Grade-scale" in the "Grade-scale" "table_row"
    #---Override activity---#
    And the following should exist in the "dash-table" table:
    | Activity Name (linked) | Fullname linked |
    | Assignment 2           | Student 1       |
    # And I should see "Assignment 2" in the ".dash-table tbody tr:nth-child(2) td:nth-child(2)" "css_element"
    # And "//table[contains(@class, 'dash-table')]//tbody//tr[2]//td[3]//a[contains(text(), 'Student 1')]" "xpath_element" should exist
    And I click on ".activity-completion-override" "css_element" in the "//table[contains(@class, 'dash-table')]//tbody//tr[contains(., 'Assignment 2') and contains(., 'Student 1')]" "xpath_element"
    And I press "Save changes"
    #---Teacher log out---#
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Assignment 2" "link"
    #---Student check the overrided activity---#
    And "Assignment 2" should have the "Mark as done" completion condition
    And I should see "Not graded" in the "Grading status" "table_row"
    #---Student log out---#
    And I log out

  Scenario: Override activity completion and check override username
    #---Teacher login---#
    And I log in as "teacher1"
    And I should see "Activity completion" in the ".block_dash h3.card-title" "css_element"
    And I click on "select[name='u_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'student1')]" "xpath_element"
    #---Override activity---#
    And I click on ".activity-completion-override" "css_element" in the "//table[contains(@class, 'dash-table')]//tbody//tr[2]//td[12]" "xpath_element"
    And I press "Save changes"
    #---check override teacher name---#
    And "//table[contains(@class, 'dash-table')]//tbody//tr[2]//td[4]//a[contains(text(), 'teacher 1')]" "xpath_element" should exist
    #---Teacher log out---#
    And I log out

  Scenario: Check the activity grade fields
    #---Teacher login---#
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Assignment 3" "link"
    And I click on assignment submissions link
    And I click on "#action-menu-1" "css_element" in the "Student 1" "table_row"
    Then I choose "Grade" in the open action menu
    And I set the field "Grade out of 100" to "70"
    And I press "Save changes"
    When I click on "Course: Course 1" "link"
    And I click on "Assignment 4" "link"
    And I click on assignment submissions link
    And I click on "#action-menu-1" "css_element" in the "Student 1" "table_row"
    And I choose "Grade" in the open action menu
    And I set the field "Grade out of 100" to "30"
    And I press "Save changes"
    #---Teacher log out---#
    And I log out
    #---Student login---#
    Then I log in as "student1"
    And I should see "Activity completion" in the ".block_dash h3.card-title" "css_element"
    And I click on "select[name='u_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'student1')]" "xpath_element"
    And the following should exist in the "dash-table" table:
    | Activity Name (linked) | Grade max | Grade to pass | Current grade |
    | Assignment 4           | 100       | 50            | 30            |
    | Assignment 3           | 100       |               | 70            |
    #---View the pass and fail grades color difference in the activity completion table---#
    # And ".badge-success" "css_element" should exist in the ".dash-table tbody tr:nth-child(3) td:nth-child(10) p" "css_element"
    # And ".badge-danger" "css_element" should exist in the ".dash-table tbody tr:nth-child(4) td:nth-child(10) p" "css_element"
    And ".badge-success" "css_element" should exist in the "Assignment 3" "table_row"
    And ".badge-danger" "css_element" should exist in the "Assignment 4" "table_row"

  Scenario: Grade type
    #---Admin login---#
    And I log in as "admin"
    And I am on the "Grade-scale" "assign activity editing" page
    When I expand all fieldsets
    #---Set grade type to scale---#
    And I set the field "grade[modgrade_type]" to "Scale"
    And I set the field "grade[modgrade_scale]" to "Separate and Connected ways of knowing"
    And I press "Save and display"
    And I am on the "Grade-point" "assign activity editing" page
    When I expand all fieldsets
    #---Set grade type to scale---#
    And I set the field "grade[modgrade_type]" to "point"
    And I set the field "grade[modgrade_point]" to "90"
    And I press "Save and display"
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash h3.card-title" "css_element"
    #---Set grade scale in dashboard activity completion table---#
    And I click on "select[name='u_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'student1')]" "xpath_element"
    And I click on "Grade" "link" in the ".dash-table tbody tr:nth-child(5) td:nth-child(13)" "css_element"
    And I set the field "Grade" to "2"
    And I press "Save changes"
    #---Set grade point in dashboard activity completion table---#
    And I wait "5" seconds
    And I click on "Grade" "link" in the ".dash-table tbody tr:nth-child(6) td:nth-child(13)" "css_element"
    And I set the field "Grade" to "60"
    And I press "Save changes"
    #---Admin log out---#
    And I log out
    #---student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Grade-scale" "link"
    And I should see "Separate and connected"
    And I click on "Grade-point" "link" in the "courseindex-content" "region"
    And I should see "60.00"

  Scenario: Parent role grading child activity
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | parent   | parent    | 1        | student1@example.com |
    #---Admin login---#
    And I log in as "admin"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I wait until the page is ready
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
      | dashaddon/activity_completion:editgrade | 1      |
    And I click on "Create this role" "button"
    And I follow "Dashboard"
    #---Assign parent to child---#
    And I am on the "student1" "user > profile" page
    And I click on "Preferences" "link" in the ".profile_tree" "css_element"
    And I follow "Assign roles relative to this user"
    And I follow "Parent"
    And I set the field "addselect" to "parent 1 (student1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Activity completion" block preference
    #---Set the parent condition---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][parentrole][enabled]" to "1"
    And I set the field "config_preferences[filters][parentrole][roleids][]" to "Parent"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Admin log out---#
    And I log out

    #---Parent login---#
    And I log in as "parent"
    And I should see "Activity completion" in the ".block_dash h3.card-title" "css_element"
    And I should see "Grade-scale" in the ".dash-table tbody tr:nth-child(5) td:nth-child(2)" "css_element"
    And I should see "Student 1" in the ".dash-table tbody tr:nth-child(5) td:nth-child(3)" "css_element"
    And I should see "Grade" in the ".dash-table tbody tr:nth-child(5) td:nth-child(13)" "css_element"
    And I click on "Grade" "link" in the ".dash-table tbody tr:nth-child(5) td:nth-child(13)" "css_element"
    And I set the field "Grade" to "80"
    And I press "Save changes"
    And I should see "Student 1" in the ".dash-table tbody tr:nth-child(5) td:nth-child(3)" "css_element"
    And I should see "Current grade" in the ".dash-table thead tr:nth-child(1) th:nth-child(10)" "css_element"
    And I should see "80" in the ".dash-table tbody tr:nth-child(5) td:nth-child(10)" "css_element"
    #---Parent log out---#
    And I log out

    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Activity completion" block preference
    #---Set the parent condition---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][parentrole][enabled]" to "0"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Admin log out---#
    And I log out

    #---Student login---#
    And I log in as "student1"
    And I should see "Activity completion" in the ".block_dash h3.card-title" "css_element"
    And I click on "select[name='u_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'student1')]" "xpath_element"
    And I should see "Student 1" in the ".dash-table tbody tr:nth-child(5) td:nth-child(3)" "css_element"
    And I should see "Current grade" in the ".dash-table thead tr:nth-child(1) th:nth-child(10)" "css_element"
    And I should see "80" in the ".dash-table tbody tr:nth-child(5) td:nth-child(10)" "css_element"

    And I am on "Course 1" course homepage
    And I am on the "Grade-scale" "assign activity" page
    And I should see "Grade" in the ".feedbacktable .generaltable tbody tr th" "css_element"

  Scenario: Activity completion filters for user course activity
    #---Admin login---#
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Activity completion" block preference
    #---Set the fields and filters---#
    And I click on "Filters" "link"
    And I set the field "User" to "1"
    And I set the field "Course" to "1"
    And I set the field "Activity" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    #---Admin log out---#
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I should see "Activity completion"
    #---Course filter---#
    And I click on "select[name='c_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Course 1')]" "xpath_element"
    And I should see "Course 1" in the "Activity completion" "block"
    #---User filter---#
    And I click on "select[name='u_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'student1')]" "xpath_element"
    And I should see "Assignment 1" in the ".dash-table tbody tr:first-child td:nth-child(2)" "css_element"
    #---Activity filter---#
    And I click on "select[name='cm_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Assignment 1')]" "xpath_element"
    And I click on "select[name='cm_id'] + span .selection" "css_element"
    And I click on "//span[@class='select2-results']//li[contains(normalize-space(.),'Assignment 2')]" "xpath_element"
    And I should see "Assignment 1" in the "Activity completion" "block"
    And I should see "Assignment 2" in the "Activity completion" "block"
