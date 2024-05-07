@local @local_dash @dash_program @javascript @_file_upload
Feature: Dash program to show the list of course data source
  In order to use the features
  As admin
  I need to be able to configure the program setting & dash plugin

  Show the list of course data source in dash block on the dashboard page
  In order to show the list of course data source in dash block on the dashboard
  As an admin
  I can add the dash block to the dashboard

  Background:
    Given the following "categories" exist:
        | name  | category | idnumber |
        | Cat 1 | 0        | CAT1     |
    And the following "course" exist:
        | fullname    | shortname | category | enablecompletion |
        | Course 1    | C1        | 0        |  1         |
        | Course 2    | C2        | 0        |  1         |
        | Course 3    | C3        | 0        |  1         |
    And the following "activities" exist:
        | activity | name       | course | idnumber |  intro           | section  |completion|
        | page     | Test page1 | C1     | page1    | Page description | 1        | 1        |
        | page     | Test page2 | C2     | page1    | Page description | 2        | 1        |
        | page     | Test page3 | C3     | page1    | Page description | 3        | 1        |
    And the following "users" exist:
        | username | firstname | lastname | email                   |
        | student1 | Student   | First    | student1@example.com    |
        | student2 | Student   | Two      | student2@example.com    |
        | student3 | Student   | Three    | student3@example.com    |
    And the following "course enrolments" exist:
        | user | course | role             |   timestart | timeend   |
        | student1 | C1 | student          |   0         |     0     |
        | student1 | C2 | student          |   0         |     0     |
        | student1 | C3 | student          |   0         |     0     |
        | student2 | C2 | student          |   0         |     0     |
        | student2 | C3 | student          |   0         |     0     |
        | admin    | C1 | manager          |   0         |     0     |
        | admin    | C2 | manager          |   0         |     0     |
    And the following "cohorts" exist:
        | name     | idnumber |
        | Cohort 1 | CH1      |
    And the following "cohort members" exist:
        | user     | cohort |
        | student1 | CH1    |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Test page1" to "1"
    And I press "Save changes"
    And I navigate to "Programs > Program management" in site administration
    And I click on "Add program" "button"
    And "Add program" "dialogue" should be visible
    And I set the following fields to these values:
        | Program name   | Demo program |
        | ID number      | CH123        |
        | Description    | Demo text    |
        | Course groups  | Yes          |
    And I set the field "Tags" to "program"
    And I press enter
    And I upload "local/dash/addon/programs/tests/assets/background.jpg" file to "Program image" filemanager
    And I click on "Add program" "button" in the ".modal-body" "css_element"
    And "Demo program" "text" should exist in the "dl.row" "css_element"
    And I click on "Visibility settings" "link" in the ".nav-tabs" "css_element"
    And I should see "No" in the "dl.row" "css_element"
    And I click on "Edit" "button"
    And "Edit" "dialogue" should be visible
    And I set the following fields to these values:
        | Public               | Yes       |
        | Visible to cohorts   | Cohort 1  |
    And I click on "Update program" "button" in the ".modal-body" "css_element"
    And I should see "Yes" in the "dl.row" "css_element"
    And I should see "Cohort 1" in the "dl.row" "css_element"
    And I click on "Allocation settings" "link" in the ".nav-tabs" "css_element"
    And I should see "Allocations"
    And I click on ".fa-cog" "css_element" in the "#page.drawers div[role=\"main\"] h3:nth-of-type(1)" "css_element"
    And I set the following fields to these values:
        | timeallocationstart[enabled]  | 1  |
        | Allocation start              | ##15 Mar 2024 08:00## |
        | Allocation end                | ##15 Apr 2025 08:00## |
    And I click on "Update allocation" "button" in the ".modal-body" "css_element"
    And I should see "Friday, 15 March 2024, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(1) dd:nth-of-type(1)" "css_element"
    And I should see "Tuesday, 15 April 2025, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(1) dd:nth-of-type(2)" "css_element"
    And I should see "Scheduling"
    And I click on ".fa-cog" "css_element" in the "#page.drawers div[role=\"main\"] h3:nth-of-type(2)" "css_element"
    And I set the following fields to these values:
        | Program start                 | At a fixed date       |
        | Program start date            | ##15 Mar 2024 18:30## |
        | Program due                   | At a fixed date       |
        | Due date                      | ##15 Apr 2025 08:00## |
        | Program end                   | At a fixed date       |
        | Program end date              | ##15 Apr 2025 08:00## |
    And I click on "Update scheduling" "button" in the ".modal-body" "css_element"
    And I should see "Friday, 15 March 2024, 6:30 PM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(2) dd:nth-of-type(1)" "css_element"
    And I should see "Tuesday, 15 April 2025, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(2) dd:nth-of-type(2)" "css_element"
    And I should see "Tuesday, 15 April 2025, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(2) dd:nth-of-type(3)" "css_element"
    And I should see "Allocation sources"
    And I click on ".fa-cog" "css_element" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(3) dd:nth-of-type(2) a" "css_element"
    And I set the following fields to these values:
        | Active                 | Yes   |
        | Allow new sign ups     | Yes   |
        | Sign up key            | 1234  |
        | Max users              | 3     |
    And I click on "Update" "button" in the ".modal-body" "css_element"
    And I should see "Active; Sign up key is required; Users 0/3; Sign ups are allowed" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(3) dd:nth-of-type(2)" "css_element"
    And I click on "Program management" "link" in the ".breadcrumb" "css_element"
    And I should see "Demo program" in the "Demo text" "table_row"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I configure the "New Dash" block
    And I set the field "Block title" to "Enrol Programs"
    And I click on "Enrol Programs" "radio" in the "Configure New Dash block" "dialogue"
    And I press "Save changes"
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "Enrol Programs" "block"
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out

  @javascript
  Scenario: Visually display the program content & visiblity
    Given I log in as "student1"
    And I follow "Dashboard"
    And I should see "Enrol Programs" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "Demo program" in the "Demo program" "table_row"
    And I click on "View programs" "button"
    And I should see "Program catalogue" in the ".h2" "css_element"
    And I follow "Dashboard"
    And I click on "Sigup (Key required)" "button"
    And "Sigup (Key required)" "dialogue" should exist
    And I set the following fields to these values:
        | Sign up key   | 1234   |
    And I click on "Sign up" "button" in the ".modal-body" "css_element"
    And I should see "View program" in the ".dash-table > tbody > tr > td:last-child" "css_element"
    And I log out
    And I log in as "student2"
    And I follow "Dashboard"
    And I should see "Enrol Programs" in the ".block_dash-local-layout-grid_layout" "css_element"
    And "Demo program" "link" should not exist

  @javascript
  Scenario: Visually display the program with tags
    Given I log in as "admin"
    And I navigate to "Programs > Program management" in site administration
    And I click on "Add program" "button"
    And "Add program" "dialogue" should be visible
    And I set the following fields to these values:
        | Program name   | Basic program |
        | ID number      | BP321         |
        | Description    | Basic content |
        | Course groups  | Yes           |
    And I set the field "Tags" to "basic"
    And I press enter
    And I upload "local/dash/addon/programs/tests/assets/background.jpg" file to "Program image" filemanager
    And I click on "Add program" "button" in the ".modal-body" "css_element"
    And "Basic program" "text" should exist in the "dl.row" "css_element"
    And I click on "Visibility settings" "link" in the ".nav-tabs" "css_element"
    And I should see "No" in the "dl.row" "css_element"
    And I click on "Edit" "button"
    And "Edit" "dialogue" should be visible
    And I set the following fields to these values:
        | Public               | Yes       |
        | Visible to cohorts   | Cohort 1  |
    And I click on "Update program" "button" in the ".modal-body" "css_element"
    And I should see "Yes" in the "dl.row" "css_element"
    And I should see "Cohort 1" in the "dl.row" "css_element"
    And I click on "Allocation settings" "link" in the ".nav-tabs" "css_element"
    And I should see "Allocations"
    And I click on ".fa-cog" "css_element" in the "#page.drawers div[role=\"main\"] h3:nth-of-type(1)" "css_element"
    And I set the following fields to these values:
        | timeallocationstart[enabled]  | 1                     |
        | Allocation start              | ##16 Mar 2024 08:00## |
        | Allocation end                | ##16 Apr 2025 08:00## |
    And I click on "Update allocation" "button" in the ".modal-body" "css_element"
    And I should see "Saturday, 16 March 2024, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(1) dd:nth-of-type(1)" "css_element"
    And I should see "Wednesday, 16 April 2025, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(1) dd:nth-of-type(2)" "css_element"
    And I should see "Scheduling"
    And I click on ".fa-cog" "css_element" in the "#page.drawers div[role=\"main\"] h3:nth-of-type(2)" "css_element"
    And I set the following fields to these values:
        | Program start                 | At a fixed date       |
        | Program start date            | ##16 Mar 2024 18:30## |
        | Program due                   | At a fixed date       |
        | Due date                      | ##16 Apr 2025 08:00## |
        | Program end                   | At a fixed date       |
        | Program end date              | ##16 Apr 2025 08:00## |
    And I click on "Update scheduling" "button" in the ".modal-body" "css_element"
    And I should see "Saturday, 16 March 2024, 6:30 PM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(2) dd:nth-of-type(1)" "css_element"
    And I should see "Wednesday, 16 April 2025, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(2) dd:nth-of-type(2)" "css_element"
    And I should see "Wednesday, 16 April 2025, 8:00 AM" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(2) dd:nth-of-type(3)" "css_element"
    And I should see "Allocation sources"
    And I click on ".fa-cog" "css_element" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(3) dd:nth-of-type(2) a" "css_element"
    And I set the following fields to these values:
        | Active                 | Yes   |
        | Allow new sign ups     | Yes   |
        | Sign up key            | 1234  |
        | Max users              | 3     |
    And I click on "Update" "button" in the ".modal-body" "css_element"
    And I should see "Active; Sign up key is required; Users 0/3; Sign ups are allowed" in the "#page.drawers div[role=\"main\"] dl.row:nth-of-type(3) dd:nth-of-type(2)" "css_element"
    And I click on "Program management" "link" in the ".breadcrumb" "css_element"
    And I should see "Basic program" in the "Basic content" "table_row"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I configure the "New Dash" block
    And I set the field "Block title" to "Enrol Programs"
    And I click on "Enrol Programs" "radio" in the "Configure New Dash block" "dialogue"
    And I press "Save changes"
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "Enrol Programs" "block"
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I click on "Select all" "button"
    And I click on "Filters" "link" in the "Edit preferences" "dialogue"
    And I set the following fields to these values:
        | id_config_preferences_filters_epp_tags_enabled    | 1   |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student1"
    And I should see "Enrol Programs" in the ".block_dash-local-layout-grid_layout" "css_element"
    And I should see "Demo program" in the "Demo program" "table_row"
    And I should see "Basic program" in the "Basic program" "table_row"
    And I click on ".dash-container .select2-search__field" "css_element"
    And I click on "basic" "text" in the ".select2-results__options" "css_element"
    And "Basic program" "link" should be visible
    And "Demo program" "link" should not exist
