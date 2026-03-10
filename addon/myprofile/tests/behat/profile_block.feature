@local @local_dash @dash_my_profile_stats @javascript
Feature: User profile with stats
  In order to use the features
  As admin
  I need to be able to configure the dash plugin

  Show the user profile and stats visually in dash block on the dashboard page
  In order to show the user profile & stats in dash block on the dashboard
  As an admin
  I can add the dash block to the dashboard

  Background:
    Given the following "categories" exist:
        | name  | category | idnumber |
        | Cat 1 | 0        | CAT1     |
    And the following "course" exist:
        | fullname | shortname | category | enablecompletion |
        | Course 1 | C1        | 0        | 1                |
        | Course 2 | C2        | 0        | 1                |
        | Course 3 | C3        | 0        | 1                |
    And the following "activities" exist:
        | activity | name         | course | idnumber | intro                 | section | completion | completionview | completionexpected |
        | page     | Test page1   | C1     | page1    | Page description      | 1       | 1          | 0              | 0                  |
        | page     | Test page2   | C2     | page1    | Page description      | 2       | 1          | 0              | 0                  |
        | page     | Test page3   | C1     | page1    | Page description      | 3       | 1          | 0              | ##today##          |
        | assign   | Test assign1 | C1     | assign1  | Assign due today      | 2       | 1          | 0              | ##yesterday##      |
    And the following "users" exist:
        | username | firstname | lastname | email                |
        | student1 | Student   | First    | student1@example.com |
        | student2 | Student   | Two      | student2@example.com |
        | student3 | Student   | Three    | student3@example.com |
    And the following "course enrolments" exist:
        | user     | course | role    | timestart | timeend |
        | student1 | C1     | student | 0         | 0       |
        | student1 | C2     | student | 0         | 0       |
        | student1 | C3     | student | 0         | 0       |
        | student2 | C2     | student | 0         | 0       |
        | student2 | C3     | student | 0         | 0       |
        | admin    | C1     | manager | 0         | 0       |
        | admin    | C2     | manager | 0         | 0       |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Test page1" to "1"
    And I press "Save changes"
    And I create skill with the following fields to these values:
        | Skill name       | Beginner |
        | Key              | beginner |
        | Number of levels | 2        |
        | Base level name    | beginner |
        | Base level point   | 10       |
        | Level #1 name    | Level 1  |
        | Level #1 point   | 20       |
        | Level #2 name    | Level 2  |
        | Level #2 point   | 30       |
    And I create skill with the following fields to these values:
        | Skill name       | Competence |
        | Key              | competence |
        | Number of levels | 2          |
        | Base level name    | beginner   |
        | Base level point   | 10         |
        | Level #1 name    | Level 1    |
        | Level #1 point   | 20         |
        | Level #2 name    | Level 2    |
        | Level #2 point   | 30         |
    And I log out

  @javascript
  Scenario: Display user profile block and stats
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I create dash "My profile" datasource
    And I configure the "New Dash" block
    And I set the following fields to these values:
      | Block title | My profile |
      | Region      | content    |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student1"
    And I follow "Dashboard"
    And I log out
    And I log in as "admin"
    And I navigate to "Course 1" course skills
    And I click on ".skill-course-actions .action-edit" "css_element"
    And I set the following fields to these values:
        | Status                 | Enabled |
        | Upon course completion | Points  |
        | Points                 | 45      |
    And I press "Save changes"
    Then I should see "Points - 45" in the "beginner" "table_row"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I click on "#action-menu-toggle-0" "css_element"
    And I open the "My profile" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the following fields to these values:
        | Show user profile image         | 1                       |
        | Show user's full name           | 1                       |
        | id_config_preferences_userinfo1 | user: User              |
        | id_config_preferences_userinfo2 | user: Email address     |
        | id_config_preferences_userinfo3 | user: City/town         |
        | id_config_preferences_kpi1      | Completed courses       |
        | id_config_preferences_kpi2      | Completed/total courses |
        | id_config_preferences_kpi3      | Courses in progress     |
        | id_config_preferences_kpi4      | Current courses         |
        | id_config_preferences_kpi5      | Future courses          |
        | id_config_preferences_kpi6      | Past courses            |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I am on "Course 2" course homepage
    And I click on "Settings" "link" in the ".secondary-navigation" "css_element"
    And I expand all fieldsets
    And I set the following fields to these values:
        | Course start date | ##1 Mar 2026 08:00## |
    And I press "Save and display"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I am on the "student1" "user > profile" page
    And I should see "Earned: 0"
    And I follow "Dashboard"
    And I am on "Course 1" course homepage
    And I press "Mark as done"
    And I wait until "Done" "button" exists
    And I am on the "student1" "user > profile" page
    Then I should see "Earned: 45"
    And I follow "Dashboard"
    And I should see "1" in the ".dashboard-stats-block .dashboard-completedcourses-stats p b" "css_element"
    And I should see "Completed courses" in the ".dashboard-stats-block .dashboard-completedcourses-stats p span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-enrolledprogress-stats p b" "css_element"
    And I should see "/3" in the ".dashboard-stats-block .dashboard-enrolledprogress-stats p b span" "css_element"
    And I should see "Completed courses" in the ".dashboard-stats-block .dashboard-enrolledprogress-stats p > span" "css_element"
    And I should see "2" in the ".dashboard-stats-block .dashboard-coursesinprogress-stats p b" "css_element"
    And I should see "Courses in progress" in the ".dashboard-stats-block .dashboard-coursesinprogress-stats p span" "css_element"
    And I should see "2" in the ".dashboard-stats-block .dashboard-currentcoursescount-stats p b" "css_element"
    And I should see "Current courses" in the ".dashboard-stats-block .dashboard-currentcoursescount-stats p span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-futurecoursescount-stats p b" "css_element"
    And I should see "Future courses" in the ".dashboard-stats-block .dashboard-futurecoursescount-stats p span" "css_element"
    And I wait "10" seconds
    And I should see "0" in the ".dashboard-stats-block .dashboard-pastcoursescount-stats p b" "css_element"
    And I should see "Past courses" in the ".dashboard-stats-block .dashboard-pastcoursescount-stats p span" "css_element"
    Then I log out

  @javascript
  Scenario: Display user profile block and due activities stats
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "My profile" datasource
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student1"
    And I follow "Dashboard"
    And I log out
    And I log in as "admin"
    And I navigate to "Course 1" course skills
    And I click on ".skill-course-actions .action-edit" "css_element"
    And I set the following fields to these values:
        | Status                 | Enabled |
        | Upon course completion | Points  |
        | Points                 | 45      |
    And I press "Save changes"
    Then I should see "Points - 45" in the "beginner" "table_row"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "New Dash" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the following fields to these values:
        | Show user profile image         | 1                                   |
        | Show user's full name           | 0                                   |
        | id_config_preferences_userinfo1 | user: Fullname linked               |
        | id_config_preferences_userinfo2 | user: User profile URL              |
        | id_config_preferences_userinfo3 | user: User profile link             |
        | id_config_preferences_kpi1      | Earned skill points                 |
        | id_config_preferences_kpi2      | Earned/total skill points           |
        | id_config_preferences_kpi3      | Completed courses this week         |
        | id_config_preferences_kpi4      | Completed activities this week      |
        | id_config_preferences_kpi5      | Number of due activities            |
        | id_config_preferences_kpi6      | Number of overdue activities        |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I am on the "student1" "user > profile" page
    And I should see "Earned: 0"
    And I follow "Dashboard"
    And I am on "Course 1" course homepage
    And I press "Mark as done"
    And I wait until "Done" "button" exists
    And I am on the "student1" "user > profile" page
    Then I should see "Earned: 45"
    And I follow "Dashboard"
    And I reload the page
    And I should see "45" in the ".dashboard-stats-block .dashboard-earnedskillpoints-stats p b" "css_element"
    And I should see "Points collected" in the ".dashboard-stats-block .dashboard-earnedskillpoints-stats p span" "css_element"
    And I should see "45" in the ".dashboard-stats-block .dashboard-earnedandtotalpoints-stats p b" "css_element"
    And I should see "/30" in the ".dashboard-stats-block .dashboard-earnedandtotalpoints-stats p b span" "css_element"
    And I should see "Earned/total points" in the ".dashboard-stats-block .dashboard-earnedandtotalpoints-stats p > span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-completedcoursesinweek-stats p b" "css_element"
    And I should see "Courses completed this week" in the ".dashboard-stats-block .dashboard-completedcoursesinweek-stats p span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-completedactivitiesinweek-stats p b" "css_element"
    And I should see "Activities completed this week" in the ".dashboard-stats-block .dashboard-completedactivitiesinweek-stats p span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-numberofdueactivities-stats p b" "css_element"
    And I should see "Due activities" in the ".dashboard-stats-block .dashboard-numberofdueactivities-stats p span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-numberofoverdueactivities-stats p b" "css_element"
    And I should see "Overdue activities" in the ".dashboard-stats-block .dashboard-numberofoverdueactivities-stats p span" "css_element"
    Then I log out

  @javascript
  Scenario: Display user profile block, login streak, Message and Team members stats
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "My profile" datasource
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student1"
    And I follow "Dashboard"
    And I log out
    And I log in as "admin"
    And I navigate to "Course 1" course skills
    And I click on ".skill-course-actions .action-edit" "css_element"
    And I set the following fields to these values:
        | Status                 | Enabled |
        | Upon course completion | Points  |
        | Points                 | 45      |
    And I press "Save changes"
    Then I should see "Points - 45" in the "beginner" "table_row"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "New Dash" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the following fields to these values:
        | Show user profile image         | 1                                |
        | Show user's full name           | 1                                |
        | id_config_preferences_userinfo1 | user: First name                 |
        | id_config_preferences_userinfo2 | user: Last name                  |
        | id_config_preferences_userinfo3 | user: Last login                 |
        | id_config_preferences_kpi1      | Login streak                     |
        | id_config_preferences_kpi2      | Logins this week                 |
        | id_config_preferences_kpi3      | Number of unread messages        |
        | id_config_preferences_kpi4      | Number of days since last login  |
        | id_config_preferences_kpi5      | Number of team members           |
        | id_config_preferences_kpi6      | Number of users currently online |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow "Dashboard"
    And I open messaging
    And I select "Student First" user in messaging
    And I set the field with xpath "//textarea[@data-region='send-message-txt']" to "Hi!"
    And I press the enter key
    Then I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
    And I select "Student Two" user in messaging
    And I set the field with xpath "//textarea[@data-region='send-message-txt']" to "Hi!"
    And I press the enter key
    Then I should see "Hi!" in the "//*[@data-region='message-drawer']//div[@data-region='content-message-container']" "xpath_element"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I wait until the page is ready
    And I click on "Continue" "button"
    And I wait until the page is ready
    And I set the following fields to these values:
        | Short name                           | Parent |
        | Custom full name                     | Parent |
        | contextlevel30                       | 1      |
        | moodle/user:viewdetails              | 1      |
        | moodle/user:viewalldetails           | 1      |
        | moodle/user:readuserblogs            | 1      |
        | moodle/user:readuserposts            | 1      |
        | moodle/user:viewuseractivitiesreport | 1      |
        | moodle/user:editprofile              | 1      |
        | tool/policy:acceptbehalf             | 1      |
    And I click on "Create this role" "button"
    And I follow "Dashboard"
    And the following "blocks" exist:
        | blockname | contextlevel | reference | pagetypepattern | defaultregion |
        | mentees   | System       | 1         | site-index      | side-pre      |
    And I am on the "student1" "user > profile" page
    And I click on "Preferences" "link" in the ".profile_tree" "css_element"
    And I follow "Assign roles relative to this user"
    And I follow "Parent"
    Then "optgroup[label*='Potential users (4)']" "css_element" should exist
    And I set the field "addselect" to "Student First (student1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I set the field "addselect" to "Student Two (student2@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I set the field "addselect" to "Student Three (student3@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I wait until the page is ready
    And I should see "Student First" in the "#removeselect" "css_element"
    And I should see "Student Two" in the "#removeselect" "css_element"
    And I should see "Student Three" in the "#removeselect" "css_element"
    And I should see "Parent (3)" in the "Assign another role" "select"
    And I am on "Course 2" course homepage
    And I click on "Settings" "link" in the ".secondary-navigation" "css_element"
    And I expand all fieldsets
    And I set the following fields to these values:
        | Course start date | ##1 Mar 2024 08:00## |
    And I press "Save and display"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I am on the "student1" "user > profile" page
    And I should see "Earned: 0"
    And I follow "Dashboard"
    And I am on "Course 1" course homepage
    And I press "Mark as done"
    And I wait until "Done" "button" exists
    And I am on the "student1" "user > profile" page
    Then I should see "Earned: 45"
    And I follow "Dashboard"
    And I log out
    And I log in as "student2"
    And I follow "Dashboard"
    And I should see "1" in the ".dashboard-stats-block .dashboard-numberofunreadmsg-stats p b" "css_element"
    And I should see "Unread messages" in the ".dashboard-stats-block .dashboard-numberofunreadmsg-stats p span" "css_element"
    And I should see "1" in the ".dashboard-stats-block .dashboard-teammemberscount-stats p b" "css_element"
    And I should see "Team members" in the ".dashboard-stats-block .dashboard-teammemberscount-stats p span" "css_element"
    And I should see "3" in the ".dashboard-stats-block .dashboard-onlineuserscount-stats p b" "css_element"
    And I should see "Users currently online" in the ".dashboard-stats-block .dashboard-onlineuserscount-stats p span" "css_element"
    Then I log out
