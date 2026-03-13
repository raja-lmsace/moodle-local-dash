@local @local_dash @dash_multicategory @javascript
Feature: Check multicategory field in the datasource in dash block
  In order to enable the course completion widgets in dash block on the dashboard
  As an admin

  Background:
    Given the following "custom field categories" exist:
      | name  | component   | area   | itemid |
      | Other | core_course | course | 0      |
    And the following "custom fields" exist:
      | name                  | category | type          | shortname  |
      | Field 1               | Other    | text          | text       |
      | Associated categories | Other    | multicategory | categories |
    Given the following "categories" exist:
      | name       			| category | idnumber |
      | Category A 			| 0        | CAT1     |
			| Sub Category A1 | CAT1     | SUBCATA1 |
			| Sub Category A2 | CAT1     | SUBCATA2 |
			| Sub Category A3 | CAT1     | SUBCATA3 |
			| Sub Sub Category A1 | SUBCATA1 | SUBSUBCATA1 |
      | Category 2 			| 0        | CAT2     |
      | Category 3 			| CAT2     | CAT3     |
      | Category 4 			| 0        | CAT4     |
      | Category B 			| 0        | CAT5     |
			| Sub Category B1 | CAT5     | SUBCATB1 |
			| Sub Category B2 | CAT5     | SUBCATB2 |
			| Sub Category B3 | CAT5     | SUBCATB3 |
    And the following "courses" exist:
      | fullname 			| shortname | category 		| enablecompletion | numsections | startdate      | enddate         |
      | Course 1 			| C1        | CAT1     		| 1                | 3           |                |                 |
      | Course 2 			| C2        | CAT1     		| 0                | 2           |                |                 |
      | Course 3 			| C3        | CAT2     		| 1                | 1           |                |                 |
      | Course 4 			| C4        | CAT3     		| 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 5 			| C5        | CAT3     		| 1                | 2           |                |                 |
      | Course 6 			| C6        | CAT3     		| 1                | 2           |                |                 |
			| Sub Course A1 | SUBCA1    | SUBCATA1    | 1                | 3           |                |                 |
			| Sub Course A2 | SUBCA2    | SUBCATA2    | 1                | 3           |                |                 |
			| Sub Course B1 | SUBCB1    | SUBCATB1    | 1                | 3           |                |                 |
			| Sub Course B2 | SUBCB2    | SUBCATB1    | 1                | 3           |                |                 |
			| Sub Course B3 | SUBCB3    | SUBCATB3    | 1                | 3           |                |                 |
			| Deep Course A1 | DEEPCA1   | SUBSUBCATA1 | 1                | 3           |                |                 |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend |
      | student1 | C1     | student | ## today ## | 0       |
      | student1 | C2     | student | 0           | 0       |
      | student1 | C3     | student | 0           | 0       |
      | student1 | C4     | student | 0           | 0       |
      | student2 | C2     | student | 0           | 0       |
      | student2 | C3     | student | 0           | 0       |
      | student3 | C4     | student | 0           | 0       |
      | admin    | C1     | student | 0           | 0       |
      | admin    | C2     | student | 0           | 0       |
      | admin    | C3     | student | 0           | 0       |
      | admin    | C4     | student | 0           | 0       |
    And I log in as "admin"
    And I log out

Scenario: Course category condition includes courses from multicategory field
    Given I log in as "admin"
    And I am on "Course 3" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    And I click on "Category A" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I press the escape key
    And I press "Save and display"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "New Dash" "block"
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course_categories_condition][enabled]" to "1"
    And I set the field "id_config_preferences_filters_c_course_categories_condition_coursecategories" to "Category A"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    Then I should see "Course 2" in the "New Dash" "block"
    And I should see "Course 3" in the "New Dash" "block"
    And I should see "Course 1" in the "New Dash" "block"
    And I should not see "Course 4" in the "New Dash" "block"
    And I log out

  Scenario: Course category filter includes courses from multicategory field
    Given I log in as "admin"
    And I am on "Course 6" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    And I click on "Category A" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I press the escape key
    And I press "Save and display"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Datasource: Courses"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Datasource: Courses" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Filter" "link"
    And I set the field "Category" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    Then I log in as "student1"
    And I follow dashboard
    And I click on "Category" "button" in the ".dash-cat-dropdown" "css_element"
    Then I should not see "Category 4" in the ".dash-mega-cols" "css_element"
    When I click on ".dash-mega-col[data-level='1'] ul li:nth-child(1)" "css_element"
    Then I should see "Course 1" in the "Datasource: Courses" "block"
    And I should see "Course 2" in the "Datasource: Courses" "block"
    And I should see "Course 6" in the "Datasource: Courses" "block"
    And I should not see "Course 3" in the "Datasource: Courses" "block"
    And I should not see "Course 4" in the "Datasource: Courses" "block"
    And I click on "Category" "button" in the ".dash-cat-dropdown" "css_element"
    When I click on ".dash-mega-col[data-level='1'] ul li:nth-child(2)" "css_element"
    Then I should see "Course 3" in the "Datasource: Courses" "block"
    And I should not see "Course 1" in the "Datasource: Courses" "block"
    And I should not see "Course 2" in the "Datasource: Courses" "block"
    And I log out

  Scenario: Associated categories multicategory field displays comma-separated names in Dash
    Given I log in as "admin"
    And I am on "Course 2" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    And I click on "Category 2" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I click on "Category 3" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I press the escape key
    And I press "Save and display"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    And I click on "#action-menu-toggle-0" "css_element"
    And I click on "Preferences" "link" in the "New Dash" "block"
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    And I set the field "Associated categories" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    Then I should see "Category 2, Category 3" in the "Course 2" "table_row"
    And I should not see "Category 2, Category 3" in the "Course 1" "table_row"
    And I log out

	Scenario: Display only categories with results in the category filter
		Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Datasource: Courses"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Datasource: Courses" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Filter" "link"
    And I set the field "Category" to "1"
		Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course_categories_condition][enabled]" to "1"
    And I set the field "id_config_preferences_filters_c_course_categories_condition_coursecategories" to "Category A"
    And I set the field "config_preferences[filters][c_course_categories_condition][includesubcategories]" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student1"
    And I follow dashboard
    Then I should see "Datasource: Courses"
    And I click on "Category" "button" in the ".dash-cat-dropdown" "css_element"
    Then I should see "Category A" in the ".dash-mega-cols" "css_element"
    Then I should not see "Category 2" in the ".dash-mega-cols" "css_element"
    And I hover ".dash-mega-col[data-level='1'] ul li:nth-child(1)" "css_element"
    Then I should see "Sub Category A1" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    Then I should see "Sub Category A2" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    Then I should not see "Sub Category A3" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    And I log out

  Scenario: Category filter shows only categories containing courses matching self-enrolment condition
    Given I log in as "admin"
    When I add "Self enrolment" enrolment method in "Sub Course B1" dashwith:
      | Custom instance name | Self enrolment |
    And I add "Self enrolment" enrolment method in "Sub Course B2" dashwith:
      | Custom instance name | Self enrolment |
    And I add "Self enrolment" enrolment method in "Course 5" dashwith:
      | Custom instance name | Self enrolment |
    And I add "Self enrolment" enrolment method in "Course 6" dashwith:
      | Custom instance name | Self enrolment |
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Datasource: Courses"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Datasource: Courses" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Filter" "link"
    And I set the field "Category" to "1"
    Then I click on "Conditions" "link"
    And I set the field "Enrollment options" to "1"
    And I set the field "Enrolment methods" to "Self enrolment"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "student1"
    And I follow dashboard
    Then I should see "Datasource: Courses"
    And I click on "Category" "button" in the ".dash-cat-dropdown" "css_element"
    Then I should see "Category 2" in the ".dash-mega-cols" "css_element"
    Then I should see "Category B" in the ".dash-mega-cols" "css_element"
    Then I should not see "Category A" in the ".dash-mega-cols" "css_element"
    Then I should not see "Category 4" in the ".dash-mega-cols" "css_element"
    And I hover ".dash-mega-col[data-level='1'] ul li:nth-child(1)" "css_element"
    Then I should see "Category 3" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    And I hover ".dash-mega-col[data-level='1'] ul li:nth-child(2)" "css_element"
    Then I should see "Sub Category B1" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    Then I should not see "Sub Category B2" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    Then I should not see "Sub Category B3" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    And I log out

  Scenario: Category filter supports hierarchy navigation
    Given I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I create dash "Courses" datasource
    And I wait until the page is ready
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Datasource: Courses"
    And I set the following fields to these values:
      | Region | content |
    And I press "Save changes"
    And I open the "Datasource: Courses" block preference
    Then I click on "Fields" "link" in the "Edit preferences" "dialogue"
    And I set the field "Course: Full name" to "1"
    Then I click on "Filter" "link"
    And I set the field "Category" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    And I log in as "admin"
    And I follow dashboard
    Then I should see "Datasource: Courses"
    And I click on "Category" "button" in the ".dash-cat-dropdown" "css_element"
    Then I should see "Category A" in the ".dash-mega-cols" "css_element"
    And I hover ".dash-mega-col[data-level='1'] ul li:nth-child(1)" "css_element"
    Then I should see "Sub Category A1" in the ".dash-mega-col[data-level='2'] ul" "css_element"
    And I hover ".dash-mega-col[data-level='2'] ul li:nth-child(1)" "css_element"
    Then I should see "Sub Sub Category A1" in the ".dash-mega-col[data-level='3'] ul" "css_element"
    When I click on ".dash-mega-col[data-level='3'] ul li:nth-child(1)" "css_element"
    Then I should see "Deep Course A1" in the "Datasource: Courses" "block"
    And I should not see "Sub Course A1" in the "Datasource: Courses" "block"
    And I should not see "Course 1" in the "Datasource: Courses" "block"
    And I should not see "Course 2" in the "Datasource: Courses" "block"
    And I log out
