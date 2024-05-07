@local @local_dash @dash_cohort_course @javascript @_file_upload
Feature: Dash program to show the list of cohort course
  In order to show the course data source in dash block on the dashboard
  As an admin
  I can add the dash block to the dashboard

  Background:
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category 2 | 0        | CAT2     |
      | Category 3 | 0        | CAT3     |
      | Category 4 | CAT3     | CAT4     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
      | Course 2 | C2        | CAT2     | 0                |
      | Course 3 | C3        | CAT3     | 1                |
      | Course 4 | C4        | CAT4     | 1                |
    And the following "activities" exist:
      | activity      | name               | course   | idnumber | intro                 | section    | completion |
      | assign        | Assignment 1       | C1       | page01   | Welcome to Assignment | 1          | 1          |
      | assign        | Assignment 2       | C3       | page02   | Welcome to Assignment | 1          | 1          |
      | choice        | My quiz B          | C3       | choice   | Welcome to Quiz       | 1          | 1          |
      | choice        | Quiz 1             | C3       | choice   | Welcome to Quiz       | 2          | 1          |
      | chat          | Students chat room | C1       | chat01   | Welcome to chat       | 3          | 1          |
      | book          | Book 1             | C2       | book     | Welcome to Book       | 1          | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Second   | student2@example.com |
      | student3 | Student   | Third    | student3@example.com |
      | student4 | Student   | Fourth   | student4@example.com |
      | student5 | Student   | Fifth    | student5@example.com |
      | student6 | Student   | Sixth    | student6@example.com |
      | student7 | Student   | Seventh  | student7@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | teacher2 | Teacher   | Second   | teacher2@example.com |
      | teacher3 | Teacher   | Third    | teacher3@example.com |
      | teacher4 | Teacher   | Fourth   | teacher4@example.com |
      | teacher5 | Teacher   | Fifth    | teacher5@example.com |
      | teacher6 | Teacher   | Sixth    | teacher6@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student1 | C2     | student |
      | student2 | C2     | student |
      | student3 | C1     | student |
      | student4 | C1     | student |
      | student4 | C2     | student |
      | student5 | C3     | student |
      | teacher1 | C1     | teacher |
      | teacher1 | C2     | teacher |
      | teacher2 | C1     | teacher |
      | teacher3 | C1     | teacher |
    And the following "cohorts" exist:
      | name    | idnumber  |
      | Cohort1 | cohortid1 |
      | Cohort2 | cohortid2 |
      | Cohort3 | cohortid3 |
    And the following "cohort members" exist:
      | user     | cohort    |
      | student1 | cohortid1 |
      | teacher1 | cohortid1 |
      | student2 | cohortid1 |
      | teacher2 | cohortid1 |
      | student3 | cohortid1 |
      | teacher3 | cohortid1 |
      | student4 | cohortid1 |
      | student5 | cohortid1 |
      | student4 | cohortid2 |
      | student6 | cohortid2 |
      | student7 | cohortid2 |

    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add the "Course completion status" block
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I click on "Assignment - Assignment 1" "checkbox"
    And I click on "Chat - Students chat room" "checkbox"
    And I set the field "activity_aggregation" to "2"
    #And I click on "criteria_self" "checkbox" in the "#id_manualselfcompletioncontainer" "css_element"
    And I press "Save changes"
    And I am on "Course 3" course homepage
    And I add the "Course completion status" block
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I click on "Assignment - Assignment 2" "checkbox"
    And I click on "Choice - My quiz B" "checkbox"
    And I click on "Choice - Quiz 1" "checkbox"
    And I set the field "activity_aggregation" to "2"
    #And I click on "criteria_self" "checkbox" in the "#id_manualselfcompletioncontainer" "css_element"
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Assignment 1" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I am on "Course 1" course homepage
    And I click on "Students chat room" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I click on "Assignment 1" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I log out
    And I log in as "student4"
    And I am on "Course 1" course homepage
    And I click on "Assignment 1" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I log out
    And I log in as "student5"
    And I am on "Course 3" course homepage
    And I click on "Assignment 2" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I am on "Course 3" course homepage
    And I click on "My quiz B" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I am on "Course 3" course homepage
    And I click on "Quiz 1" "link" in the "#page-content" "css_element"
    And I click on "Mark as done" "button"
    And I log out

  Scenario:Show cohort course in dashboard
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I create dash "Course completions" datasource
    Then I configure the "New Dash" block
    And I set the field "Block title" to "course completion cohort"
    And I press "Save changes"
#--student login-- Enable condition 1
    Then I open the "course completion cohort" block preference
    And I click on "Fields" "link"
    And I set the field "Course completion: Progress bar (completed / total activities)" to "0"
    Then I click on "Conditions" "link"
    And I click on "Cohorts" "checkbox"
    And I set the field "config_preferences[filters][cohort][cohorts][]" to "Cohort2"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should not see "Student Two" in the "course completion cohort" "block"
    And I should not see "Teacher One" in the "course completion cohort" "block"
    And I should not see "Student Seven" in the "course completion cohort" "block"
    And I should not see "Student Six" in the "course completion cohort" "block"
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should not see "Student Two" in the "course completion cohort" "block"
    And I should not see "Teacher One" in the "course completion cohort" "block"
    And I log out
#--Condition 1 & 2 Enabled
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    Then I open the "course completion cohort" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][cohort][cohorts][]" to "Cohort2"
    And I click on "Users in one of my cohorts" "checkbox"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should not see "Student Two" in the "course completion cohort" "block"
    And I should not see "Teacher One" in the "course completion cohort" "block"
    And I log out
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    Then I open the "course completion cohort" block preference
    Then I click on "Conditions" "link"
    And I click on ".select2-selection__clear" "css_element"
    And I set the field "config_preferences[filters][cohort][cohorts][]" to "Cohort1"
    #And I click on "config_preferences[filters][users_mycohort][enabled]" "checkbox"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out
    And I log in as "student7"
    And I am on homepage
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should not see "Student First" in the "course completion cohort" "block"
    And I should not see "Student Third" in the "course completion cohort" "block"
    And I should not see "Student Seventh" in the "course completion cohort" "block"
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I should see "Student First" in the "course completion cohort" "block"
    And I should see "Student Third" in the "course completion cohort" "block"
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should see "Student Fifth" in the "course completion cohort" "block"
    And I should not see "Student Seventh" in the "course completion cohort" "block"
    And I log out

#--Condtion 2 only enabled
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    Then I open the "course completion cohort" block preference
    Then I click on "Conditions" "link"
    And I click on "Cohorts" "checkbox"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I should see "Student First" in the "course completion cohort" "block"
    And I should see "Student Third" in the "course completion cohort" "block"
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should see "Student Fifth" in the "course completion cohort" "block"
    And I should not see "Student Seventh" in the "course completion cohort" "block"
    And I log out
    And I log in as "student6"
    And I am on homepage
    And I should see "Student Fourth" in the "course completion cohort" "block"
    And I should not see "Student First" in the "course completion cohort" "block"
    And I should not see "Student Third" in the "course completion cohort" "block"
    And I should not see "Student Fifth" in the "course completion cohort" "block"
    And I should not see "Student Seventh" in the "course completion cohort" "block"
    And I log out
