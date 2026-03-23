@local @local_dash @dash_slick_layout @javascript
Feature: Enable the slider layout in dash block on the dashboard page and view it's contents
  In order to enable the slider layout in dash block on the dashboard
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
    # And I add the "Dash" block
    And I create dash "Courses" datasource
    And I configure the "New Dash" block
    And I set the field "Block title" to "Course datasource"
    # And I click on "#id_config_data_source_idnumber_local_dashlocalblock_dashcourses_data_source" "css_element"
    And I press "Save changes"
    # And I click on "Preferences" "button" in the "Course datasource" "block"
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    Then I click on "General" "link"
    And I set the field "Layout" to "Grid layout"
    Then I click on "Fields" "link"
    Then I should see "Layout mode"
    And I set the field "Layout mode" to "Slider"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I press "Continue"
    And I log out

  Scenario: Check the slider layouts.
    Given I log in as "admin"
    Then ".dash-block-content .card_layout_slider" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-list" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-prev" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-next" "css_element" should exist
    Then I log out
    And I log in as "student1"
    Then ".dash-block-content .card_layout_slider" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-list" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-prev" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-next" "css_element" should exist
    Then I log out

  Scenario: Check the slider settings
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    Then ".dash-block-content .card_layout_slider .slick-next" "css_element" should exist
    Then ".dash-block-content .card_layout_slider .slick-prev" "css_element" should exist
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    And I set the field "Layout mode" to "Slider"
    And I set the field "Heading field" to "course: Full name"
    Then I should see "Autoplay"
    Then I should see "Autoplay speed (ms)"
    Then I should see "Show arrows"
    Then I should see "Center mode"
    Then I should see "Center padding (px)"
    Then I set the field "Show arrows" to "0"
    And I press "Save changes"
    Then ".dash-block-content .card_layout_slider .slick-next" "css_element" should not exist
    Then ".dash-block-content .card_layout_slider .slick-prev" "css_element" should not exist
    And I wait until the page is ready
    And I open the "Course datasource" block preference
    Then I click on "Fields" "link"
    And I set the field "Slides to show" to "2"
    And I press "Save changes"
    Then ".slick-slide.slick-current.slick-active" "css_element" should be visible
    Then ".slick-slide.slick-active + .slick-slide.slick-active" "css_element" should be visible
