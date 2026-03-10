@local @local_dash @dashaddon @dashaddon_activity_completion_condition @mod_assign @javascript

Feature: Add activity completion condition datasource in dash block
    In order to enable the activity completion condition datasource in dash block on the dashboard
    As an admin
  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | 0        | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate                                   | enddate                                            |
      | Course 1 | C1        | CAT1     | 1                | 3           | ## 1 August 2024 08:00 ##  | ## 1 August 2025 08:00 ##      |
      | Course 2 | C2        | CAT2     | 1                | 2           | ## 1 July 2024 10.00 ##      | ## 5 September 2024 08.00 ## |
      | Course 3 | C3        | CAT3     | 1                | 3           | ## 1 July 2024 10.00 ##    | ## 7 August 2024 08.00 ##      |
      | Course 4 | C4        | CAT1     | 1                | 4           | ## 8 August 2024 08.00 ##  | ## 8 August 2024 12.00 ##      |
      | Course 5 | C5        | CAT2     | 1                | 5           | ## 12 August 2024 08.00 ## | ## 12 August 2024 12.00 ##   |
      | Course 6 | C6        | CAT3     | 1                | 4           | ## 18 August 2024 08.00 ## | ## 30 August 2024 08.00 ##     |
    And the following "activities" exist:
      | activity | name         | course  | idnumber       | intro                   | section | submissiondrafts | assignsubmission_onlinetext_enabled | grade[modgrade_type] | modgrade_scale  | grade[modgrade_point] | gradepass | completion | completionsubmit | completionusegrade | completionpassgrade |
      | assign   | Assignment 1 | C1      | Assignment 1   | Welcome to Assignment 1 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 0          |                  |                    |                     |
      | assign   | Assignment 2 | C1      | Assignment 2   | Welcome to Assignment 2 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 1          |                  |                    |                     |
      | assign   | Assignment 3 | C1      | Assignment 3   | Welcome to Assignment 3 | 1       | 0                | 1                                   |                      |                 | 100                   |           | 2          | 1                |                    |                     |
      | assign   | Assignment 4 | C1      | Assignment 4   | Welcome to Assignment 4 | 1       | 0                | 1                                   |                      |                 | 100                   | 50        | 2          |                  | 1                  | 1                   |
      | assign   | Assignment 5 | C4      | Assignment 5   | Welcome to Assignment 5 | 1       | 0                | 1                                   | scale                | 2               |                       |           |            |                  |                    |                     |
      | assign   | Assignment 6 | C6      | Assignment 6   | Welcome to Assignment 6 | 1       | 0                | 1                                   | point                |                 | 80                    |           |            |                  |                    |                     |
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
      | student1 | C1     | student        | ## 1 August 2024 07:00 ##  | 0       |
      | student2 | C1     | student        | ## 1 July 2024 09.00 ##    | 0       |
      | student2 | C4     | student        | ## 1 July 2024 09.00 ##    | 0       |
      | student3 | C1     | student        | ## 18 August 2024 07.00 ## | 0       |
      | student3 | C6     | student        | ## 18 August 2024 07.00 ## | 0       |
      | teacher1 | C1     | editingteacher | 0                          | 0       |
      | teacher2 | C1     | editingteacher | 0                          | 0       |
    And the following "mod_assign > submissions" exist:
      | assign       | user      | onlinetext                                                                           |
      | Assignment 1 | student1  | Birds are a group of warm-blooded vertebrates constituting the class Aves ...        |
      | Assignment 2 | student1  | Animals are multicellular, eukaryotic organisms in the biological kingdom Animalia.  |
      | Assignment 3 | student1  | A fish is an aquatic, anamniotic, gill-bearing vertebrate animal with swimming fins. |
      | Assignment 4 | student1  | Insects are hexapod invertebrates of the class Insecta.                              |
      | Assignment 5 | student2  | Assignment 5 grading - scale                                                         |
      | Assignment 6 | student3  | Assignment 6 grading - point                                                         |
    And the following "block_dash > dash blocks default" exist:
      | type       | name                  | title               | fields                                               |
      | datasource | activity_completion   | Activity completion | Module icon, Activity name, Section, Fullname linked |

  Scenario: Activity completion datasource: Course
    #---Admin login---#
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I wait until the page is ready
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I click on "Courses" "checkbox"
    And I set the field "config_preferences[filters][c_course][courseids][]" to "C1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Check Activities in course for enrolled users---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And the following should exist in the "dash-table" table:
    | Activity name | Fullname linked |
    | Assignment 1  | Student 1       |
    | Assignment 2  | Student 2       |
    | Assignment 2  | Student 3       |
    #---Admin log out---#
    And I log out

  Scenario Outline: Activity completion: Course date
    #---Admin login---#
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I click on "Course dates" "checkbox"
    And I set the field "config_preferences[filters][c_coursedates][coursedates][]" to "<coursedate>"
    And I click on "Fields" "link"
    And I set the field "Course: Short name" to "1"
    And I set the field "Per page" to "30"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Check Activities course dates---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "<name>" in the ".dash-table tbody tr:nth-child(<Nrow>) td:nth-child(4)" "css_element"
    And I should see "<activity>" in the ".dash-table tbody tr:nth-child(<Arow>) td:nth-child(2)" "css_element"
    And I should see "<course>" in the ".dash-table tbody tr:nth-child(<Crow>) td:nth-child(5)" "css_element"
    Examples:
      | coursedate | name      | Nrow | activity      | Arow | course | Crow |
      | past       | Student 2 | 21   | Assignment 5  | 21   | C4     | 21   |

  Scenario Outline: Activity completion: Course future date
    #---Admin login---#
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I click on "Course dates" "checkbox"
    And I set the field "config_preferences[filters][c_coursedates][coursedates][]" to "<coursedate>"
    And I click on "Fields" "link"
    And I set the field "Course: Short name" to "1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Check Activities course dates---#
    And I am on "Course 1" course homepage
    And I click on "Settings" "link" in the ".secondary-navigation" "css_element"
    And I expand all fieldsets
    And I set the following fields to these values:
      | startdate[year]  | ## +1 year ## %Y ## |
    And I set the field "enddate[enabled]" to "0"
    And I press "Save and display"
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "<name>" in the ".dash-table tbody tr:nth-child(<Nrow>) td:nth-child(4)" "css_element"
    And I should see "<activity>" in the ".dash-table tbody tr:nth-child(<Arow>) td:nth-child(2)" "css_element"
    And I should see "<course>" in the ".dash-table tbody tr:nth-child(<Crow>) td:nth-child(5)" "css_element"
    Examples:
      | coursedate | name      | Nrow | activity     | Arow | course | Crow |
      | future     | Student 3 | 8    | Assignment 2 | 8    | C1     | 8    |

  Scenario Outline: Activity completion datasource: activity completion status
    #---Admin login---#
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][activitycompletion_status][enabled]" to "1"
    And I set the field "config_preferences[filters][activitycompletion_status][cmcompletionstatus][]" to "<status>"
    And I click on "Fields" "link"
    And I set the field "Course: Short name" to "1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Check Activities completion status---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And the following should exist in the "dash-table" table:
    | Fullname linked | Activity name | Short name |
    | <name>          | <activity>    | <course>   |

    Examples:
      | status       | name      | Nrow | activity     | Arow | course | Crow |
      | completed    | Student 1 | 1    | Assignment 3 | 1    | C1     | 1    |
      | notcompleted | Student 1 | 1    | Assignment 1 | 1    | C1     | 1    |
      | notcompleted | Student 2 | 2    | Assignment 1 | 2    | C1     | 2    |
      | notcompleted | Student 3 | 3    | Assignment 1 | 3    | C1     | 3    |
      | notcompleted | Student 1 | 6    | Assignment 2 | 6    | C1     | 6    |
      | notcompleted | Student 2 | 7    | Assignment 2 | 7    | C1     | 7    |
      | notcompleted | Student 3 | 8    | Assignment 2 | 8    | C1     | 8    |

  Scenario: Activity completion datasource:User I manage
    #---Admin login---#
    Given I log in as "admin"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
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
    #---Condition setting---#
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set User i manage in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][parentrole][enabled]" to "1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Check parent and child users---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    #---Admin log out---#
    And I log out
    #---Parent login---#
    And I log in as "parent"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "Student 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(4)" "css_element"

  Scenario: Activity completion datasource:Current user
      #---Admin login---#
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_user][enabled]" to "1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Check Current users in each activites---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should not see "Student 1" in the ".dash-table" "css_element"
    #---Admin log out---#
    And I log out
    #---student log in---#
    And I log in as "student1"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "Student 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(4)" "css_element"

  Scenario Outline: Activity completion datasource:Module name
    Given the following "activities" exist:
      | activity | name    | course  | idnumber | intro             | section |
      | book     | Book    | C1      | book     | Welcome to book   | 1       |
      | choice   | Choice  | C1      | choice   | Welcome to choice | 1       |
      | forum    | Forum   | C1      | forum    | Welcome to forum  | 1       |
    #---Admin login---#
    And I log in as "<user>"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][modulename][enabled]" to "1"
    And I set the field "config_preferences[filters][modulename][modules][]" to "<id>"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Admin log out---#
    And I log out
    And I log in as "<user1>"
    #---Check Activities name list---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "<activity1>" in the "<username>" "table_row"
    And I log out
    Examples:
      | user  | user1    | activity1       | id      | username  |
      | admin | student1 | Assignment 1    | assign  | Student 1 |
      | admin | student1 | Book            | book    | Student 1 |
      | admin | student2 | Forum           | forum   | Student 2 |

  Scenario Outline: Activity completion datasource:Cohorts condition
    And the following "cohorts" exist:
      | name    | idnumber  |
      | Cohort1 | cohortid1 |
      | Cohort2 | cohortid2 |
    And the following "cohort members" exist:
      | user     | cohort    |
      | student1 | cohortid1 |
      | teacher1 | cohortid1 |
      | student2 | cohortid2 |
      | teacher2 | cohortid2 |
    #---Admin login---#
    And I log in as "<user>"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][cohort][enabled]" to "1"
    And I set the field "config_preferences[filters][cohort][cohorts][]" to "<cohorts>"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Admin log out---#
    And I log out
    And I log in as "<loguser>"
    #---Check Cohort members---#
    And I follow "Dashboard"
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And the following should exist in the "dash-table" table:
    | Activity name | Fullname linked |
    | Assignment 1  | <user1>         |
    | Assignment 1  | <user2>         |

    Examples:
      | user  | cohorts | loguser  | user1     | user2     |
      | admin | Cohort1 | student1 | Student 1 | teacher 1 |
      | admin | Cohort2 | student2 | Student 2 | teacher 2 |

  Scenario Outline: Activity completion datasource:Members of my cohort(s) only
    And the following "cohorts" exist:
      | name    | idnumber  |
      | Cohort1 | cohortid1 |
      | Cohort2 | cohortid2 |
    And the following "cohort members" exist:
      | user     | cohort    |
      | student1 | cohortid1 |
      | teacher1 | cohortid1 |
      | student2 | cohortid2 |
      | teacher2 | cohortid2 |
    #---Admin login---#
    And I log in as "<user>"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Set course in conditions---#
    And I open the "Activity completion" block preference
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][users_mycohort][enabled]" to "1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    #---Admin log out---#
    And I log out
    And I log in as "<loguser>"
    #---Check Cohort members---#
    And I follow "Dashboard"
    And I wait "20" seconds
    And I should see "Activity completion" in the ".block_dash-local-layout-grid_layout" "css_element"
    And the following should exist in the "dash-table" table:
    | Activity name | Fullname linked |
    | Assignment 1  | <user1>         |
    | Assignment 1  | <user2>         |
    Examples:
      | user  | cohorts | loguser  | user1     | user2     | tr1 | tr2 |
      | admin | Cohort1 | student1 | Student 1 | teacher 1 | 1   | 4   |
      | admin | Cohort2 | student2 | Student 2 | teacher 2 | 1   | 6   |
      | admin | Cohort2 | teacher1 | Student 1 | teacher 1 | 1   | 4   |
