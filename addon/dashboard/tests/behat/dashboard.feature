@local @local_dash @dashaddon @dashaddon_dashboard @javascript @_file_upload
Feature: Add a dashboard data source in dash block
  In order to enable the dashboard data source in dash block on the course page
  As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | enablecompletion |
      | Course 1 | C1        | 0        | 1                | 3           |      1           |
      | Course 2 | C2        | CAT1     | 0                | 2           |      1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | teacher2 | Teacher   | Two      | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | admin    | C1     | manager |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | teacher1 | C1     | teacher |
      | student2 | C2     | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Dashboards" "radio"
    And I navigate to "Plugins > Manage dashboards" in site administration
    And I click on "Create dashboard" "button"
    And I set the field "Name" to "Test dashboard"
    And I set the field "Short name" to "TestidTest"
    And I press "Save changes"
    And I log out

  Scenario: Navigate to dash dashboard page
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Main dashboard"
    And I click on "Main dashboard" "link"
    And "Dashboard" "text" should exist in the "#page-header" "css_element"
    And I am on "Course 1" course homepage
    And I should see "Test dashboard"
    And I click on "Test dashboard" "link"
    Then "#page-dashaddon-dashboard-TestidTest" "css_element" should exist
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Main dashboard" "link"
    And "Dashboard" "text" should exist in the "#page-header" "css_element"
    And I am on "Course 1" course homepage
    And I should see "Test dashboard"
    And I click on "Test dashboard" "link"
    Then "#page-dashaddon-dashboard-TestidTest" "css_element" should exist
    And I log out

  Scenario: Dash dashboard appearance
    Given I log in as "admin"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Dashboards" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Dashboards"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    Then I open the "Dashboards" block preference
    Then I click on "Fields" "link"
    And I click on "config_preferences[available_fields][dd_description][visible]" "checkbox"
    And I click on "config_preferences[available_fields][dd_dashicon][visible]" "checkbox"
    And I click on "config_preferences[available_fields][dd_dashthumbnailimg][visible]" "checkbox"
    And I click on "config_preferences[available_fields][dd_dashbgimage][visible]" "checkbox"
    And I press "Save changes"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Test dashboard" "table_row"
    And I expand all fieldsets
    And I set the field "Description" to "A learning management system or virtual learning environment is a software application."
    And I click on ".fontawesome-picker-container .fontawesome-autocomplete" "css_element"
    And I click on ".fontawesome-icon-suggestions li .fa-globe" "css_element"
    And I upload "local/dash/addon/dashboard/tests/fixtures/unnamed.jpg" file to "Thumbnail image" filemanager
    And I press "Save changes"
    And I click on ".action-edit" "css_element" in the "Test dashboard" "table_row"
    And I expand all fieldsets
    And I upload "local/dash/addon/dashboard/tests/fixtures/lms.jpg" file to "Background image" filemanager
    And I press "Save changes"
    And I follow "Dashboard"
    And I should see "A learning management system or virtual learning environment is a software application." in the "Test dashboard" "table_row"
    And ".fa-globe" "css_element" should exist in the ".table.dash-table" "css_element"
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/dashaddon_dashboard/dashthumbnailimage/')][contains(@src, 'unnamed.jpg')]" "xpath_element" should exist
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/dashaddon_dashboard/dashbgimage')][contains(@src, 'lms.jpg')]" "xpath_element" should exist
    And I click on ".img-responsive" "css_element"
    And I follow "Dashboard"
    And I click on "Test dashboard" "link"
    Then "//body[@id='page-dashaddon-dashboard-TestidTest']" "xpath_element" should exist

  Scenario: Dash dashboard on page navigation
    Given I log in as "admin"
    And I turn dash block editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    #---Creating dashboard---#
    And I set the field "Description" to "Welcome to Dash-dashboard"
    And I set the following fields to these values:
    | name        | Dash-dashboard |
    | shortname   | Dash           |
    | contexttype | system         |
    And I press "Save changes"
    #---creating blocks in dashboard---#
    And I should see "Manage dashboards"
    And I should see "Dash-dashboard"
    And I click on "Dash-dashboard" "link"
    And I add the "Dash" block to the "Dash" region
    And I click on "User" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "user"
    And I press "Save changes"
    And I add the "Dash" block to the "Dash" region
    And I click on "Course" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "course"
    And I press "Save changes"
    And I add the "Dash" block to the "Dash" region
    And I click on "Activities" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Activities"
    And I press "Save changes"
    # #---Enable & adding campaigns---#
    # And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    # And I should see "Available authentication plugins"
    # And I should see "Magic authentication" in the "Magic authentication" "table_row"
    # And I click on ".fa-eye-slash" "css_element" in the "Magic authentication" "table_row"
    # And I navigate to "Plugins > Authentication > Manage campaign" in site administration
    # And I click on "Create campaign" "button"
    # And I set the field "Title" to "Dash campaign"
    # And I set the field "Description" to "Welcome to Dash campaign"
    # And I press "Save changes"
    # And I click on "Create campaign" "button"
    # And I set the field "Title" to "Dash campaign 1"
    # And I set the field "Description" to "Welcome to Dash campaign"
    # And I press "Save changes"
    #---Choosing included blocks---#
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I should see "Edit dashboard"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Context type            | course                   |
    | Included blocks         | user, course, activities |
    | Display dashboard title | 1                        |
    | Display call to action  | 1                        |
    | Call to action link     | enrolment                |
    And I press "Save changes"
    And I should see "Manage dashboards"
    And I should see "Dash-dashboard"
    And I click on "Dash-dashboard" "link"
    #---Visit enroll users page when click on view enrollment page button---#
    And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    And I click on "View enrolment page" "link"
    And "Participants" "link" should exist
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Display dashboard title | 2 |
    | Display call to action  | 2 |
    And I press "Save changes"
    And I should see "Manage dashboards"
    And I should see "Dash-dashboard"
    And I click on "Dash-dashboard" "link"
    #---Visit enroll users page when click on view enrollment page button---#
    And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    #And I close block drawer if open
    And I scroll down page
    And I click on "View enrolment page" "link"
    And "Participants" "link" should exist
    # #---Choosing campaigns---#
    # And I follow "Dashboard"
    # And I click on "Manage dashboards" "button"
    # And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    # And I should see "Edit dashboard"
    # And I expand all fieldsets
    # And I set the following fields to these values:
    # | Display dashboard title | 1             |
    # | Display call to action  | 1             |
    # | Call to action link     | campaign      |
    # | Select campaign         | Dash campaign |
    # And I press "Save changes"
    # #---Visit enroll users page when click on view enrollment page button---#
    # And I click on "Dash-dashboard" "link"
    # And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    # And I click on "Registration" "link"
    # And I should see "Dash campaign"
    # And ".campaign-signup-block" "css_element" should exist in the ".campaign-block" "css_element"
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I should see "Edit dashboard"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Display dashboard title | 2 |
    | Display call to action  | 2 |
    And I press "Save changes"
    # #---Visit enroll users page when click on view enrollment page button---#
    # And I click on "Dash-dashboard" "link"
    # And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    And I scroll down page
    # And I click on "Registration" "link"
    # And I should see "Dash campaign"
    # And ".campaign-signup-block" "css_element" should exist in the ".campaign-block" "css_element"
    #---Choosing shop url---#
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I should see "Edit dashboard"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Display dashboard title | 1       |
    | Display call to action  | 1       |
    | Call to action link     | shopurl |
    And I press "Save changes"
    #---Visit View shop link and open course page---#
    And I click on "Dash-dashboard" "link"
    And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    And I click on "View shop" "link"
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I should see "Edit dashboard"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Display dashboard title | 2 |
    | Display call to action  | 2 |
    And I press "Save changes"
    #---Visit View shop link and open course page---#
    And I click on "Dash-dashboard" "link"
    And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    And I scroll down page
    And I click on "View shop" "link"
    #---Choosing custom url---#
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I should see "Edit dashboard"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Display dashboard title | 1                        |
    | Display call to action  | 1                        |
    | Call to action link     | custom                   |
    | Custom URL              | http://www.example.com   |
    | Custom URL Text         | Custom link              |
    And I press "Save changes"
    #---Visit cutom url---#
    And I click on "Dash-dashboard" "link"
    And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    And I click on "Custom link" "link"
    And I am on "Course 1" course homepage
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I should see "Edit dashboard"
    And I expand all fieldsets
    And I set the following fields to these values:
    | Display dashboard title | 2 |
    | Display call to action  | 2 |
    And I press "Save changes"
    #---Visit cutom url---#
    And I click on "Dash-dashboard" "link"
    And I wait "60" seconds
    And ".dash-register-block" "css_element" should exist in the ".onpage-navigation" "css_element"
    And I scroll down page
    And I click on "Custom link" "link"

  Scenario:Dash dashboard Duplicate feature
    Given I log in as "admin"
    And I turn dash block editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    #---Creating dashboard---#.
    And I set the field "Description" to "Welcome to Dash-dashboard"
    And I expand all fieldsets
    And I click on ".fontawesome-picker-container .fontawesome-autocomplete" "css_element"
    And I click on ".fontawesome-icon-suggestions li .fa-info" "css_element"
    And I set the following fields to these values:
    | Name                     | Dash-dashboard             |
    | Short name               | Dash                       |
    | Description              | Welcome to Dash-dashboard  |
    | Context type             | course                     |
    | Add to course navigation | 1                          |
    And I upload "local/dash/addon/dashboard/tests/fixtures/unnamed.jpg" file to "Thumbnail image" filemanager
    And I press "Save changes"
    And I click on ".action-edit .icon" "css_element" in the "Dash-dashboard" "table_row"
    And I expand all fieldsets
    And I upload "local/dash/addon/dashboard/tests/fixtures/lms.jpg" file to "Background image" filemanager
    And I press "Save changes"
    #---creating blocks in dashboard---#
    And I should see "Manage dashboards"
    And I should see "Dash-dashboard"
    And I click on "Dash-dashboard" "link"
    And I add the "Dash" block to the "Dash" region
    And I click on "User" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "user"
    And I press "Save changes"
    And I add the "Dash" block to the "Dash" region
    And I click on "Course" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "course"
    And I press "Save changes"
    #---Choosing locks & duplicate dashboard---#
    And I follow "Dashboard"
    And I click on "Manage dashboards" "button"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard" "table_row"
    And I set the following fields to these values:
    | Included blocks          | user, course |
    | Display dashboard title  | 1            |
    | Display call to action   | 1            |
    | Call to action link      | enrolment    |
    And I press "Save changes"
    And I click on ".action-copy .icon" "css_element" in the "Dash-dashboard" "table_row"
    And I click on ".action-edit" "css_element" in the "Dash-dashboard Copy" "table_row"
    #---check duplicate dashboard settings---#
    And I expand all fieldsets
    And the field "Name" matches value "Dash-dashboard Copy"
    And the field "Description" matches value "Welcome to Dash-dashboard"
    And the field "Context type" matches value "Course"
    #And "Course 1" "text" should exist in the "#fitem_id_courseid .form-autocomplete-selection .badge" "css_element"
    #And the field "Select course" matches value "Course 1"
    And the field "Restrict access to" matches value "Must be logged in"
    And the field "Add to course navigation" matches value "Yes"
    #And the field "Included blocks" matches value "user, course"
    #And "user, course" "text" should exist in the "#fitem_id_includedblocks .badge" "css_element"
    And the field "Display dashboard title" matches value "Always"
    And the field "Display call to action" matches value "Always"
    And the field "Call to action link" matches value "Enrolment options"

  Scenario: Redirect to course dashboard
    Given I log in as "admin"
    And I turn dash block editing mode on
    #---Create course restriction & redirect to course dashboard---#
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    And I set the following fields to these values:
    | Name                     | course redirect dashboard  |
    | Short name               | Dashboard                  |
    | Description              | Welcome to Dash-dashboard  |
    | Context type             | course                     |
    | Select course            | Course 1                   |
    | redirecttodashboard      | 1                          |
    | Restrict access to       | public                     |
    | Add to course navigation | 1                          |
    And I press "Save changes"
    And I click on "course redirect dashboard" "link"
    #And I add the "Dash" block
    #And I click on "Dashboard" "radio"
    #Then I configure the "New Dash" block
    #And I set the following fields to these values:
    #  | Block title  | course redirect dashboard |
    #  | Region       | Dashboard                 |
    #And I press "Save changes"
    And I log out
    #---unenrolled Student login---#
    And I log in as "student2"
    And I am on course index
    And I follow "Category 1"
    And I click on "Course 1" "link"
    And I should see "Course 1" in the ".page-header-headings" "css_element"
    And "course redirect dashboard" "link" should exist in the ".secondary-navigation" "css_element"
    #---enrolled Student login---#
    And I log in as "student1"
    And I am on course index
    And I follow "Category 1"
    And I click on "Course 1" "link"
    And I wait "5" seconds
    And I am on "Course 1" course homepage

  Scenario: Edit dashboard page
    And I log in as "admin"
    #---Create dashboard---#
    And I turn editing mode on
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    And I set the following fields to these values:
    | Name       | edit dashboard page |
    | Short name | Dashboard           |
    And I press "Save changes"
    And I click on "edit dashboard page" "link"
    #---Check edit dashboard button---#
    And I turn editing mode off
    And "Edit dashboard" "button" should not exist
    And I turn editing mode on
    And "Edit dashboard" "button" should exist
    And I click on "Edit dashboard" "button"
    #---Change dashboard title---#
    And I set the field "Name" to "Edit dashboard title"
    And I press "Save changes"
    And I click on "Edit dashboard title" "link"
    And I should see "Edit dashboard title"
    And I should not see "edit dashboard page"

  Scenario: Public dashboards are accessible without login
    Given I log in as "admin"
    And I turn dash block editing mode on
    #---Create course restriction & redirect to course dashboard---#
    And I click on "Manage dashboards" "button"
    And I click on "Create dashboard" "button"
    And I set the following fields to these values:
    | Name                     | Public dashboard           |
    | Short name               | Dash                       |
    | Description              | Welcome to Publicdashboard |
    | Context type             | course                     |
    | Select course            | Course 1                   |
    | redirecttodashboard      | 1                          |
    | Restrict access to       | public                     |
    | Add to course navigation | 1                          |
    And I press "Save changes"
    And I click on "Public dashboard" "link"
    And I add the "Dash" block to the "Dash" region
    And I click on "Learning Path" "radio"
    Then I configure the "New Dash" block
    And I set the following fields to these values:
    | Block title | Learning Path |
    And I press "Save changes"
    And I add the "Dash" block to the "Dash" region
    And I click on "Course Enrolments" "radio"
    Then I configure the "New Dash" block
    And I set the following fields to these values:
    | Block title | Course Enrolments |
    And I press "Save changes"
    And I add the "Calendar" block to the "Dash" region
    And I am on site homepage
    And I add the "Dash" block
    And I click on "Dashboard" "radio"
    And I log out
    When I click on "Public dashboard" "link"
    Then I should see "Public dashboard"
    And "Learning Path" "block" should exist
    And "Course Enrolments" "block" should exist
    And "Calendar" "block" should exist

