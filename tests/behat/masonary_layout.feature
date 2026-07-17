@local @local_dash @dash_masonry_layout @javascript
Feature: Enable the masonry layout in dash block on the dashboard page and view it's contents
  In order to enable the masonry layout in dash block on the dashboard
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
    And I wait until the page is ready
    # Layout is now selected directly on the Layout tab — "Layout mode" field removed
    And I click on "Layout" "link"
    And I set the field "Layout" to "Masonry"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I press "Continue"
    And I log out

  Scenario: Check the masonry layouts.
    Given I log in as "admin"
    Then ".dash-block-content .card_layout_masonry" "css_element" should exist
    Then ".dash-block-content .card_layout_masonry .masonry.filtr-item" "css_element" should exist
    Then I log out
    And I log in as "student1"
    Then ".dash-block-content .card_layout_masonry" "css_element" should exist
    Then ".dash-block-content .card_layout_masonry .masonry.filtr-item" "css_element" should exist
    Then I log out

  Scenario: Check the masonry settings
    Given the following "custom field categories" exist:
      | name          | component   | area   | itemid |
      | Other         | core_course | course | 0      |
      | Dash settings | core_course | course | 1      |
    And the following "custom fields" exist:
      | name      | category      | type     | shortname | configdata                             |
      | Field 1   | Other         | checkbox | checkbox  |                                        |
      | Field 2   | Other         | date     | date      |                                        |
      | Grid size | Dash settings | select   | gridsize  | {"options":"Wide\nTall\nSquare"}       |
      | Promotion | Dash settings | select   | promotion | {"options":"Featured\nNormal\nDimmed"} |
    And I log in as "admin"
    Then I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    And I wait until the page is ready
    # Layout, Search box and Styling options are now on the Layout tab
    And I click on "Layout" "link"
    And I set the field "Layout" to "Masonry"
    And I set the field "Search box" to "1"
    And I set the field "Styling options" to "c_f_gridsize, c_f_promotion"
    # Heading field remains on the Fields tab
    And I click on "Fields" "link"
    And I set the field "Heading field" to "course: Full name"
    Then I press "Save changes"
    And I am on the "C1" "course editing" page
    And I expand all fieldsets
    And I set the field "Grid size" to "Wide"
    And I set the field "Promotion" to "Featured"
    Then I press "Save and display"
    And I am on the "C2" "course editing" page
    And I expand all fieldsets
    And I set the field "Grid size" to "Tall"
    And I set the field "Promotion" to "Normal"
    Then I press "Save and display"
    And I am on the "C3" "course editing" page
    And I expand all fieldsets
    And I set the field "Grid size" to "Square"
    And I set the field "Promotion" to "Dimmed"
    Then I press "Save and display"
    And I am on the "block_dash > Default Dashboard" page
    Then ".card_layout_masonry .card-block:nth-child(1).Wide" "css_element" should exist
    Then ".card_layout_masonry .card-block:nth-child(1).Featured" "css_element" should exist
    Then ".card_layout_masonry .card-block:nth-child(1).Tall" "css_element" should not exist
    Then ".card_layout_masonry .card-block:nth-child(1).Normal" "css_element" should not exist
    Then ".card_layout_masonry .card-block:nth-child(2).Tall" "css_element" should exist
    Then ".card_layout_masonry .card-block:nth-child(2).Normal" "css_element" should exist
    Then ".card_layout_masonry .card-block:nth-child(3).Square" "css_element" should exist
    Then ".card_layout_masonry .card-block:nth-child(3).Dimmed" "css_element" should exist
    Then I should see "Course 1"
    Then I should see "Course 2"
    Then I should see "Course 3"
    And I set the field "masonry-searchbox" to "Course 1"
    Then I should see "Course 1"
