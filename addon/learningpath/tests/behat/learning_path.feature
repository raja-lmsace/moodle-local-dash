@local @local_dash @dashaddon @dashaddon_learning_path @javascript @_file_upload

Feature: Add learning path widget in dash block
     In order to enable the leaning path widgets in dash block on the dashboard
     As an admin

  Background:
    Given the following config values are set as admin:
      | debug | 0 |
      | debugdisplay | 0 |
    And the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
      | Category 04 | 0        | CAT4     |
      | Category 05 | 0        | CAT5     |
    And the following "custom field categories" exist:
      | name   | component   | area   | itemid |
      | shape  | core_course | course | 0      |
      | visual | core_course | course | 1      |
    And the following "custom fields" exist:
      | name   | category    | type   | shortname | configdata                                 |
      | Shape  | shape       | select | shape1    | {"options":"E-Learning\nWebinar\nSeminar"} |
      | Visual | shape       | select | visual1   | {"options":"Heart\nTrophy\nCertificate"}   |
    And the following "courses" exist:
      | fullname  | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1  | C1        | CAT1     | 1                | 3           |                |                 |
      | Course 2  | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3  | C3        | CAT2     | 1                | 1           | ##2 days ago## | ##yesterday##   |
      | Course 4  | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 5  | C5        | CAT4     | 1                | 3           |                |                 |
      | Course 6  | C6        | CAT4     | 0                | 2           |                |                 |
      | Course 7  | C7        | CAT4     | 1                | 1           |                |                 |
      | Course 8  | C8        | CAT4     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 9  | C9        | CAT4     | 1                | 3           |                |                 |
      | Course 10 | C10       | CAT5     | 0                | 2           |                |                 |
      | Course 11 | C11       | CAT5     | 1                | 1           |                |                 |
      | Course 12 | C12       | CAT5     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 13 | C13       | CAT5     | 1                | 3           |                |                 |
      | Course 14 | C14       | CAT5     | 0                | 2           |                |                 |
      | Course 15 | C15       | CAT5     | 1                | 1           |                |                 |
      | Course 16 | C16       | CAT5     | 1                | 2           | ##1 year ago## | ##1 month ago## |
    And the following "activities" exist:
      | activity      | name               | course   | idnumber | intro                 | section    | completion | gradepass |
      | assign        | Assignment 1       | C1       | page01   | Welcome to Assignment | 1          | 1          | 50.00     |
      | assign        | Assignment 2       | C3       | page02   | Welcome to Assignment | 1          | 1          | 50.00     |
      | choice        | My quiz B          | C4       | choice   | Welcome to Quiz       | 1          | 1          | 50.00     |
      | choice        | Quiz 1             | C4       | choice   | Welcome to Quiz       | 2          | 1          | 50.00     |
      | book          | Book 1             | C2       | book     | Welcome to Book       | 1          | 1          | 50.00     |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | teacher   | 1        | teacher1@example.com |
      | teacher2 | teacher   | 2        | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend | enrol  |
      | student1 | C1     | student | ## today ## | 0       | manual |
      | student1 | C2     | student | 0           | 0       | manual |
      | student1 | C4     | student | 0           | 0       | manual |
      | student2 | C5     | student | 0           | 0       | manual |
      | teacher2 | C11    | teacher | 0           | 0       | manual |
      | teacher1 | C1     | teacher | 0           | 0       | manual |
      | teacher1 | C2     | teacher | 0           | 0       | manual |
      | teacher1 | C3     | teacher | 0           | 0       | manual |

    And I log in as "admin"
    # Add svg image to Path for different devices
    And I navigate to "Plugins > Local plugins > Dash Pro" in site administration
    And I set the following fields to these values:
      | s_local_dash_customselectfield  | Shape  |
      | s_local_dash_customvisualfield  | Visual |
    And I press "Save changes"
    And I select "Triangle" from the "Shape mapping: Webinar" singleselect
    And I select "Star" from the "Shape mapping: Seminar" singleselect
    And I open the autocomplete suggestions list in the ".adminsettings .form-item:last-child .form-setting" "css_element"
    And I click on "fa-certificate" item in the autocomplete list
    And I open the autocomplete suggestions list in the ".adminsettings .form-item:nth-last-child(3) .form-setting" "css_element"
    And I click on "fa-trophy" item in the autocomplete list
    And I open the autocomplete suggestions list
    And I click on "fa-heart" item in the autocomplete list
    And I press "Save changes"

    # Course 1 custom fields
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/image.png" file to "Course image" filemanager
    And I select "E-Learning" from the "Shape" singleselect
    And I select "Heart" from the "Visual" singleselect
    And I press "Save and display"

    # Course 2 custom fields
    And I am on "Course 2" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/image1.png" file to "Course image" filemanager
    And I select "Webinar" from the "Shape" singleselect
    And I select "Trophy" from the "Visual" singleselect
    And I press "Save and display"

    # Course 3 custom fields
    And I am on "Course 3" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/image.png" file to "Course image" filemanager
    And I select "Seminar" from the "Shape" singleselect
    And I select "Certificate" from the "Visual" singleselect
    And I press "Save and display"

    # Add Dash block
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Learning Path"
    And I set the following fields to these values:
        | Region | content |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out

  Scenario: learning path - custom paths
    And I log in as "admin"

    # Add svg image to Path for different devices
    And I navigate to "Plugins > Local plugins > Dash Pro" in site administration
    And I upload "local/dash/addon/learningpath/tests/fixtures/path1.svg" file to "Desktop resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/path2.svg" file to "Desktop resources" filemanager
    And I press "Save changes"

    # Adding resources with the User roles to manage Custom path
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    # Block preference without the path image for Desktop, Tablet, Mobile
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the field "config_preferences[infoarea]" to "1"
    And I set the following fields to these values:
      | config_preferences[infoarea] | 1       |
      | Info area position           | Sidebar |
    Then "//select[@name='config_preferences[desktoppath]']/option[contains(., 'Path 3')]" "xpath_element" should not exist
    Then "//select[@name='config_preferences[tabletpath]']/option[contains(., 'Path 3')]" "xpath_element" should not exist
    Then "//select[@name='config_preferences[mobilepath]']/option[contains(., 'Path 3')]" "xpath_element" should not exist
    And I press "Save changes"

    # Block configuration
    And I click on ".dropdown-toggle" "css_element" in the ".block_dash .menubar" "css_element"
    And I click on "Configure Learning Path block" "link" in the ".block_dash .menubar .dropdown-menu" "css_element"
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/path3.svg" file to "Desktop resources" filemanager
    And I wait until the page is ready
    And I click on "Save changes" "button" in the "Configure Learning Path block" "dialogue"

    # Preferences
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the following fields to these values:
      | Path for Desktop  | Path1  |
    And I click on "Save changes" "button" in the "Edit preferences" "dialogue"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"

    # Adding resources with the User roles to manage Custom path
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    # Block preference without the path image for Desktop, Tablet, Mobile
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    # Path for devices - Default course size
    And I set the following fields to these values:
      | Path for Desktop    | Path3    |
    And I click on "Save changes" "button" in the "Edit preferences" "dialogue"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out
