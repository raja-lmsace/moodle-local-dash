@local @local_dash @dash_details_area @javascript
Feature: Enable the layout in dash block on the dashboard page and view it's contents
  In order to enable the layout in dash block on the dashboard
  As an admin
  I can add the dash block to the dashboard

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
      | Course 3 | C3        |
      | Course 4 | C4        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
      | student4 | Student   | Four     | student3@example.com |
      | student5 | Student   | Five     | student3@example.com |
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I configure the "New Dash" block
    And I set the field "Block title" to "Course datasource"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    Then I click on "General" "link"
    And I set the field "Layout" to "Grid layout"
    Then I click on "Fields" "link"
    Then I should see "Details area"
    And I set the field "Background image" to "course: Course image URL"
    And I set the field "Subheading field" to "course: Short name"
    And I set the field "Details area" to "Expanding"
    And I set the field "Details Title" to "course: Full name"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I press "Continue"
    And I log out

  Scenario: Check expand area detials in default layouts.
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then ".table.dash-table" "css_element" should not exist
    Then ".card-layout-default.card-layout-row" "css_element" should exist
    Then ".card-layout-default .card-block:nth-child(1) .card .card-body" "css_element" should exist
    Then I should see "C1" in the ".card-layout-default .card-block:nth-child(1) .card-title-sub" "css_element"
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    And I set the field "Details area" to "Expanding"
    Then I set the field "config_preferences[details_bg_color]" to "#030096"
    Then I set the field "config_preferences[details_bg_color]" to "#FFFF"
    Then I set the field "Details area size" to "Like item"
    And I press "Save changes"
    Then ".card-layout-default .card-block:nth-child(1) .details-area-block" "css_element" should exist
    Then I click on ".card-layout-default .card-block:nth-child(1)" "css_element"
    Then I should see "Course 1" in the ".card-layout-default .card-block:nth-child(1) .details-area-block .title-block" "css_element"
    Then ".dash-block-content .card-layout-default" "css_element" should exist
    And ".card-layout-default .card-block:nth-child(1).expand-details" "css_element" should exist
    And ".card-layout-default .card-block:nth-child(1) .details-area-block.show-detail" "css_element" should exist

  Scenario: Check floating area detials in default layouts.
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    Then I should see "Details area"
    And I set the field "Details area" to "Floating"
    And I press "Save changes"
    Then ".card-layout-default .card-block:nth-child(1) .details-area-block .title-block" "css_element" should exist
    Then I hover ".card-layout-default .card-block:nth-child(1)" "css_element"
    Then I should see "Course 1" in the ".card-layout-default .card-block:nth-child(1) .details-area-block .title-block" "css_element"
    And ".card-layout-default .card-block:nth-child(1).floating-details" "css_element" should exist
    And ".card-layout-default .card-block:nth-child(1) .details-area-block.show-detail" "css_element" should exist

  Scenario: Check modal area detials in default layouts.
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    Then I should see "Details area"
    And I set the field "Details area" to "Modal"
    And I press "Save changes"
    Then ".card-layout-default .card-block:nth-child(1) .details-area-block .title-block" "css_element" should exist
    Then I click on ".card-layout-default .card-block:nth-child(1)" "css_element"
