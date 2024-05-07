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
      #| Course 3 | C3        | CAT2     | 1                | 1           |      1           |
      #| Course 4 | C4        | CAT3     | 1                | 1           |      1           |
      #| Course 5 | C5        | CAT3     | 1                | 1           |      1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      #| student3 | Student   | Three    | student3@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | teacher2 | Teacher   | Two      | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | admin    | C1     | manager |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | teacher1 | C1     | teacher |
      | student2 | C2     | student |
      #| teacher1 | C3     | teacher |
      #| teacher2 | C4     | teacher |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Dashboards" "radio"
    #--Create extra dashboard in block from manage dashboard--#
    And I navigate to "Plugins > Manage dashboards" in site administration
    And I click on "Create dashboard" "button"
    And I set the field "Name" to "Test dashboard"
    And I set the field "Short name" to "TestidTest"
    And I press "Save changes"
    #And I am on "Course 1" course homepage
    #And I should see "Core dashboard"
    #And I click on "Core dashboard" "link"
    #And "Dashboard" "text" should exist in the "#page-header" "css_element"
    #And I am on "Course 1" course homepage
    #And I should see "Test dashboard"
    #And I click on "Test dashboard" "link"
    #Then "#page-dashaddon-dashboard-TestidTest" "css_element" should exist
    And I log out

  Scenario:Navigate to dashboard page
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Core dashboard"
    And I click on "Core dashboard" "link"
    And "Dashboard" "text" should exist in the "#page-header" "css_element"
    And I am on "Course 1" course homepage
    And I should see "Test dashboard"
    And I click on "Test dashboard" "link"
    Then "#page-dashaddon-dashboard-TestidTest" "css_element" should exist
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Core dashboard" "link"
    And "Dashboard" "text" should exist in the "#page-header" "css_element"
    And I am on "Course 1" course homepage
    And I should see "Test dashboard"
    And I click on "Test dashboard" "link"
    Then "#page-dashaddon-dashboard-TestidTest" "css_element" should exist
    And I log out

  Scenario:Dashboard datasource
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
    And I should see "Edit" in the "Test dashboard" "table_row"
    And I click on "Edit" "button"
    And I set the field "Description" to "A learning management system or virtual learning environment is a software application."
    And I click on ".fontawesome-picker-container .fontawesome-autocomplete" "css_element"
    And I click on ".fontawesome-icon-suggestions li .fa-globe" "css_element"
    And I upload "local/dash/addon/dashboard/tests/fixtures/unnamed.jpg" file to "Thumbnail image" filemanager
    And I press "Save changes"

    And I should see "Edit" in the "Test dashboard" "table_row"
    And I click on "Edit" "button"
    And I upload "local/dash/addon/dashboard/tests/fixtures/lms.jpg" file to "Background image" filemanager
    And I press "Save changes"

    And I am on homepage
    And I should see "A learning management system or virtual learning environment is a software application." in the "Test dashboard" "table_row"
    And ".fa-globe" "css_element" should exist in the ".table.dash-table" "css_element"

    #And the "href" attribute of "section.block_dash table.dash-table tbody tr:nth-child(2) td:nth-child(5) > a" "css_element" should contain "local/dash/addon/dashboard/dashboard.php?id=217000"
    #And the "src" attribute of "section.block_dash table.dash-table tbody tr:nth-child(2) td:nth-child(5) > img" "css_element" should contain "pluginfile.php/1/dashaddon_dashboard/dashthumbnailimage/unnamed.jpg"
    #And the "src" attribute of "section.block_dash table.dash-table tbody tr:nth-child(2) td:nth-child(6) > img" "css_element" should contain "pluginfile.php/1/dashaddon_dashboard/dashbgimage/lms.jpg"

    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/dashaddon_dashboard/dashthumbnailimage/')][contains(@src, 'unnamed.jpg')]" "xpath_element" should exist    
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/dashaddon_dashboard/dashbgimage')][contains(@src, 'lms.jpg')]" "xpath_element" should exist    

    And I click on ".img-responsive" "css_element"
    And I am on homepage
    And I click on "Test dashboard" "link"

    Then "//body[@id='page-dashaddon-dashboard-TestidTest']" "xpath_element" should exist
    #And "//body[@id='page-dashaddon-dashboard-TestidTest']/*[1][contains(@style, 'pluginfile.php/1/dashaddon_dashboard/dashbgimage/216000/lms.jpg')]" "xpath_element" should exist
    #Then "//body[contains(@id, 'page-dashaddon-dashboard-TestidTest')][contains(@src, 'pluginfile.php/1/dashaddon_dashboard/dashbgimage')][contains(@src, 'lms.jpg')]" "xpath_element" should exist
    #//img[contains(@class, 'img-responsive')]
