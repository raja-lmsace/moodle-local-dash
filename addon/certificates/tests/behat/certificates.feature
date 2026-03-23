@local @local_dash @dash_certificate @javascript @_file_upload
Feature: Dash program to show the list of course data certificate
    In order to use the features
    As admin
    I need to be able to configure the certificate settings & dash plugins

    Show the list of course data source in dash block on the dashboard page
    In order to show the list of course data source in dash block on the dashboard
    As an admin
    I can add the dash block to the dashboard

  Background:
    Given the following "categories" exist:
        | name        | category | idnumber |
        | Category 01 | 0        | CAT01    |
        | Category 02 | 0        | CAT02    |
        | Category 03 | CAT02    | CAT03    |
    And the following "course" exist:
        | fullname | shortname | category | enablecompletion |
        | Course 1 | C1        | CAT01    |  1               |
        | Course 2 | C2        | CAT02    |  1               |
        | Course 3 | C3        | CAT03    |  1               |
    And the following certificate templates exist:
        | name          | Course category |
        | Certificate02 |                 |
        | Certificate3  | CAT02           |
        | Certificate4  | CAT03           |
    And the following "activities" exist:
        | activity          | name               | course | idnumber |  intro           | section | completion |
        | coursecertificate | Test certificate1  | C1     | cert1    | Page certificate | 1       | 1          |
        | coursecertificate | Test certificate01 | C1     | cert01   | Page certificate | 1       | 1          |
        | coursecertificate | Test certificate2  | C2     | cert2    | Page certificate | 2       | 1          |
        | coursecertificate | Test certificate3  | C3     | cert3    | Page certificate | 1       | 1          |
    And the following "users" exist:
        | username | firstname | lastname | email                |
        | student1 | Student   | First    | student1@example.com |
        | student2 | Student   | Two      | student2@example.com |
        | student3 | Student   | Three    | student3@example.com |
    And the following "course enrolments" exist:
        | user     | course | role    | timestart | timeend |
        | student1 | C1     | student | 0         |     0   |
        | student1 | C2     | student | 0         |     0   |
        | student1 | C3     | student | 0         |     0   |
        | student2 | C2     | student | 0         |     0   |
        | student2 | C3     | student | 0         |     0   |
        | student3 | C1     | student | 0         |     0   |
        | student3 | C2     | student | 0         |     0   |
        | admin    | C1     | manager | 0         |     0   |
        | admin    | C2     | manager | 0         |     0   |
        | admin    | C3     | manager | 0         |     0   |
    And the following "custom field categories" exist:
        | name          | component   | area   | itemid |
        | Dash Category | core_course | course | 0      |
    #---Certificate template---#
    And I log in as "admin"
    And I navigate to "Certificates > Manage certificate templates" in site administration
    And I click on "New certificate template" "button"
    And I set the field "Name" to "Certificate1"
    And I press "Save"
    And I should see "Certificate1" in the ".page-header-headings" "css_element"
    And I click on "Add element" "button"
    And I click on "Image" "link" in the ".dropdown-menu.show" "css_element"
    And I upload "local/dash/addon/certificates/tests/assets/background.jpg" file to "Upload image" filemanager
    And I press "Save"
    And I click on "Manage certificate templates" "link" in the ".breadcrumb" "css_element"
    And I should see "Certificate1"
    And I should see "Certificate02"
    And I should see "Certificate3"
    And I should see "Certificate4"
    #---Expiry date---#
    And I am on "Course 1" course homepage
    And I turn dash block editing mode on
    And I open "Test certificate1" actions menu
    And I click on "Edit settings" "link" in the "Test certificate1" activity
    And I set the following fields to these values:
        | Expiry date type | Select date     |
        | expirydateabsolute[day]    | 14    |
        | expirydateabsolute[month]  | April |
        | expirydateabsolute[year]   | 2024  |
        | expirydateabsolute[hour]   | 09    |
        | expirydateabsolute[minute] | 00    |
        | template        | Certificate 1    |
    And I press "Save and return to course"
    And I open "Test certificate01" actions menu
    And I click on "Edit settings" "link" in the "Test certificate01" activity
    And I set the following fields to these values:
        | Expiry date type | After |
        | id_expirydaterelative_number   | 5     |
        | id_expirydaterelative_timeunit | days  |
        | template        | Certificate02    |
    And I wait "5" seconds
    And I press "Save and display"
    And I am on "Course 2" course homepage
    And I open "Test certificate2" actions menu
    And I click on "Edit settings" "link" in the "Test certificate2" activity
    And I set the field "template" to "Certificate3"
    And I wait "5" seconds
    And I press "Save and display"
    And I am on "Course 3" course homepage
    And I open "Test certificate3" actions menu
    And I click on "Edit settings" "link" in the "Test certificate3" activity
    And I set the field "template" to "Certificate4"
    And I wait "5" seconds
    And I press "Save and display"

    #---Course Certificate dash block---#
    And I am on the "block_dash > Default Dashboard" page
    And I add the "Dash" block
    And I click on "Course certificates" "radio"
    And I configure the "New Dash" block
    And I set the following fields to these values:
        | Block title | Course certificates |
        | Region      | content             |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out

  Scenario: Display the Course certificate data source
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I am on the "Test certificate1" "coursecertificate activity" page
    And I log out
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And I am on the "Test certificate2" "coursecertificate activity" page
    And I log out
    #---Admin login---#
    And I log in as "admin"
    And I am on homepage
    And I should see "Category 01" in the "Certificate 1" "table_row"
    And I should see "Category 01" in the "Student First" "table_row"
    And I should not see "Category 03" in the "Student First" "table_row"
    And I should see "Certificate3" in the "Student Two" "table_row"
    And I should see "Never" in the "Certificate3" "table_row"
    And I should not see "Certificate1" in the "Student Two" "table_row"
    And I wait "5" seconds

  Scenario: Certificate block in current course page
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I turn dash block editing mode on
    #---Adding course certificate dash block---#
    And I add the "Dash" block
    And I click on "Course certificates" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Course certificates"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I follow "Conditions"
    And I click on "Current course" "checkbox"
    And I press "Save changes"
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I am on the "Test certificate1" "coursecertificate activity" page
    And I log out
    And I log in as "student3"
    And I am on "Course 1" course homepage
    And I am on the "Test certificate01" "coursecertificate activity" page
    And I log out
    #---Admin login---#
    And I log in as "admin"
    And I am on homepage
    And I am on "Course 1" course homepage
    And I should see "Student First" in the "Certificate 1" "table_row"
    And I should see "Sunday, 14 April 2024, 9:00 AM" in the "Certificate 1" "table_row"
    And I should see "Certificate02" in the "Student Three" "table_row"
    And I wait "5" seconds

  Scenario: Display certificate of category and subcatgories
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Adding course certificate dash block---#
    And I add the "Dash" block
    And I click on "Course certificates" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Course certificates"
    And I set the following fields to these values:
    | Region | content |
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I follow "Conditions"
    And I click on "Course categories" "checkbox"
    And I press "Save changes"
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I am on the "Test certificate1" "coursecertificate activity" page
    And I follow dashboard
    And I should see "Certificate 1" in the "Course 1" "table_row"
    And I should see "student1@example.com" in the "Category 01" "table_row"
    And I log out
    #-------Sub categories---------#
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    And I follow "Conditions"
    And I click on "Include subcategories" "checkbox"
    And I press "Save changes"
    And I log out
    And I log in as "student2"
    And I am on "Course 2" course homepage
    And I am on the "Test certificate2" "coursecertificate activity" page
    And I follow dashboard
    And I should see "Certificate3" in the "Course 2" "table_row"
    And I should see "Category 02" in the "Course 2" "table_row"
    And I should not see "Category 02" in the "Course 1" "table_row"
    And I wait "5" seconds

  Scenario: Display the certificate of the currently logged-in user
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    #---Adding course certificate dash block---#
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I follow "Conditions"
    And I click on "Logged in user" "checkbox"
    And I press "Save changes"
    And I log out
    And I log in as "student 1"
    And I am on "Course 1" course homepage
    And I am on the "Test certificate1" "coursecertificate activity" page
    And I follow dashboard
    And I should see "Certificate 1" in the "Course 1" "table_row"
    And I should see "Course 1" in the "C1" "table_row"
    And I should see "Test course 1" in the "Certificate 1" "table_row"
    And I should see "Category 01" in the "Certificate 1" "table_row"
    And I should see "student1" in the "Certificate 1" "table_row"
    And I should see "student1@example.com" in the "Certificate 1" "table_row"
    And I log out
    And I log in as "student 2"
    And I follow dashboard
    And "Certificate 1" "table_row" should not exist
    And I am on "Course 2" course homepage
    And I am on the "Test certificate2" "coursecertificate activity" page
    And I follow dashboard
    And I should see "Certificate3" in the "Course 2" "table_row"
    And I should see "Course 2" in the "C2" "table_row"
    And I should see "Test course 2" in the "Certificate3" "table_row"
    And I should see "Category 02" in the "Certificate3" "table_row"
    And I should see "student2" in the "Certificate3" "table_row"
    And I should see "student2@example.com" in the "Certificate3" "table_row"

  Scenario: Display the certificate for the user by the parent user
    Given I log in as "admin"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I press "Continue"
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
    And I set the field "addselect" to "Student Two (student2@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I set the field "addselect" to "Student Three (student3@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"
    And I wait until the page is ready
    And I should see "Student Two" in the "#removeselect" "css_element"
    And I should see "Student Three" in the "#removeselect" "css_element"
    And I should see "Parent (2)" in the "Assign another role" "select"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I follow "Conditions"
    And I click on "Relations" "checkbox"
    And I press "Save changes"
    And I log out
    #---Student login---#
    And I log in as "student 1"
    And I am on "Course 1" course homepage
    And I am on the "Test certificate1" "coursecertificate activity" page
    And I am on "Course 2" course homepage
    And I am on the "Test certificate2" "coursecertificate activity" page
    And I log out
    And I log in as "student 2"
    And I am on "Course 2" course homepage
    And I am on the "Test certificate2" "coursecertificate activity" page
    And I follow dashboard
    And I should see "Certificate 1" in the "Course 1" "table_row"
    And I should see "Course 1" in the "C1" "table_row"
    And I should see "Test course 1" in the "Certificate 1" "table_row"
    And I should see "Category 01" in the "Certificate 1" "table_row"
    And I should see "student1" in the "Certificate 1" "table_row"
    And I should see "student1@example.com" in the "Certificate 1" "table_row"
    And I should see "Certificate3" in the "Course 2" "table_row"
    And I should see "Course 2" in the "C2" "table_row"
    And I should see "Test course 2" in the "Course 2" "table_row"
    And I should see "Category 02" in the "Course 2" "table_row"
    And I should see "student1" in the "Course 2" "table_row"
    And I should see "student1@example.com" in the "Course 2" "table_row"
    And I log out

  Scenario: Display the certificate for the course custom fields
    Given I log in as "admin"
    #---Course Custom field---#
    And I navigate to "Courses > Course custom fields" in site administration
    And I click on "Add a new custom field" "link"
    And I click on "Dropdown menu" "link"
    And I set the following fields to these values:
        | Name       | Dash fields |
        | Short name | dashfield   |
    And I set the field "Menu options (one per line)" to multiline:
    """
    Wide
    Tall
    Square
    """
    And I click on "Save changes" "button" in the "Adding a new Dropdown menu" "dialogue"
    Then I should see "Dash field"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "Dash fields" to "Wide"
    And I press "Save and display"
    And I am on "Course 2" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "Dash fields" to "Wide"
    And I press "Save and display"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course certificates" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I follow "Conditions"
    And I click on "Dash fields" "checkbox"
    And I set the field "Dash fields" to "Wide"
    And I press "Save changes"
    And I log out
