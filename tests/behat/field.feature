@customfield @customfield_multicategory @javascript
Feature: Managers can manage course custom fields multicategory
  In order to associate courses with multiple categories
  As a manager
  I need to create, edit and manage multicategory custom fields

  Background:
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category A | 0        | CAT_A    |
      | Category B | 0        | CAT_B    |
      | Child of A | CAT_A    | CAT_A1   |
      | Child of B | CAT_B    | CAT_B1   |
    And the following "custom field categories" exist:
      | name              | component   | area   | itemid |
      | Category for test | core_course | course | 0      |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | CAT_A    |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "admin"
    ##--Creating multicategory field in course custom field--##
    And I navigate to "Courses > Default settings > Course custom fields" in site administration
    And I click on "Add a new custom field" "link"
    And I click on "Multicategory" "link"
    And I set the following fields to these values:
      | Name       | Associated categories |
      | Short name | categories            |
    And I click on "Save changes" "button" in the "Adding a new Multicategory" "dialogue"

  Scenario: Manage multicategory custom field configuration
    Then I should see "Associated categories"
    When I press "Edit custom field: Associated categories"
    And I set the following fields to these values:
      | Name | Updated field name |
    And I click on "Save changes" "button" in the "Updating Associated categories" "dialogue"
    Then I should see "Updated field name"
    And I should not see "Associated categories"
    When I press "Edit custom field: Updated field name"
    And I set the following fields to these values:
      | Unique data | 1 |
    And I click on "Save changes" "button" in the "Updating Updated field name" "dialogue"
    Then I should see "The \"Unique values\" setting is not compatible with multi-select fields."
    When I set the following fields to these values:
      | Unique data | 0 |
    And I expand the "Parent categories" autocomplete
    And I click on "Category A" item in the autocomplete list
    And I click on "Save changes" "button" in the "Updating Updated field name" "dialogue"
    And I press "Edit custom field: Updated field name"
    Then I should see "Category A" in the "Parent categories" "form_row"
    When I set the following fields to these values:
      | Required | Yes |
    And I click on "Save changes" "button" in the "Updating Updated field name" "dialogue"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I press "Save and display"
    Then I should see "Required"
    When I navigate to "Courses > Default settings > Course custom fields" in site administration
    And I press "Delete custom field: Updated field name"
    And I click on "Yes" "button" in the "Confirm" "dialogue"
    Then I should not see "Updated field name"
    And I log out

  Scenario: Category selection and parent restriction on the course form
    When I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    Then "Category A" "autocomplete_suggestions" should exist
    And "Category B" "autocomplete_suggestions" should exist
    And "Category A / Child of A" "autocomplete_suggestions" should exist
    And "Category B / Child of B" "autocomplete_suggestions" should exist
    And I expand the "Associated categories" autocomplete
    And I click on "Category A" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I click on "Category B" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I press the escape key
    And I press "Save and display"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    Then I should see "Category A" in the "Associated categories" "form_row"
    And I should see "Category B" in the "Associated categories" "form_row"
    When I navigate to "Courses > Default settings > Course custom fields" in site administration
    And I press "Edit custom field: Associated categories"
    And I expand the "Parent categories" autocomplete
    And I click on "Category A" item in the autocomplete list
    And I click on "Save changes" "button" in the "Updating Associated categories" "dialogue"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    Then "Category A" "autocomplete_suggestions" should exist
    And "Child of A" "text" should exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And "Category B" "text" should not exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And "Child of B" "text" should not exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I log out

  Scenario: Selected categories are displayed as comma-separated names on the course listing, category page and report page
    And I press "Edit custom field: Associated categories"
    And I set the following fields to these values:
      | Visible to | Everyone |
    And I click on "Save changes" "button" in the "Updating Associated categories" "dialogue"
    When I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    And I click on "Category A" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I click on "Category B" "text" in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I press the escape key
    And I press "Save and display"
    # Course listing page
    And I am on site homepage
    Then I should see "Category A" in the ".customfields-container" "css_element"
    And I should see "Category B" in the ".customfields-container" "css_element"
    And I navigate to "Courses > Manage courses and categories" in site administration
    # Category Page
    And I click on "view" action for "Category A" in management category listing
    Then I should see "Category A" in the ".customfields-container" "css_element"
    And I should see "Category B" in the ".customfields-container" "css_element"
    # Report page
    And I navigate to "Reports > Report builder > Custom reports" in site administration
    And I click on "New report" "button"
    And I set the field "name" to "Report source course category"
    And I set the following fields in the "New report" "dialogue" to these values:
      | Report source         | Course  |
    And I click on "Save" "button" in the "New report" "dialogue"
    And I click on "Add column 'Associated categories'" "link"
    And I click on "Preview" "button" in the ".reportbuilder-report-container" "css_element"
    Then "Category A, Category B" row "Associated categories" column of "reportbuilder-table" table should contain "Category A"
    And I log out

  Scenario: User with course creator role can only select categories in their scope
    Given the following "role assigns" exist:
      | user     | role          | contextlevel | reference |
      | teacher1 | coursecreator | Category     | CAT_B     |
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    Then "Category B" "autocomplete_suggestions" should exist
    And "Category B / Child of B" "text" should exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And "Category A" "text" should not exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And "Category A / Child of A" "text" should not exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I log out

  Scenario: User with category manage capability can associate course without course creator role
    Given the following "roles" exist:
      | shortname | name                | archetype |
      | catassoc  | Category Associator |           |
    And the following "role capabilities" exist:
      | role     | customfield/multicategory:manage |
      | catassoc | allow                            |
    And the following "role assigns" exist:
      | user     | role     | contextlevel | reference |
      | teacher1 | catassoc | Category     | CAT_A     |
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    Then "Category A" "autocomplete_suggestions" should exist
    And "Category A / Child of A" "text" should exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And "Category B" "text" should not exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And "Category B / Child of B" "text" should not exist in the "#fitem_id_customfield_categories .form-autocomplete-suggestions" "css_element"
    And I log out

  Scenario: Hidden categories respect viewhiddencategories and are excluded without it
    Given the following "categories" exist:
      | name       | category | idnumber | visible |
      | Hidden Cat | CAT_B    | CAT_BH   | 0       |
    And the following "roles" exist:
      | shortname | name                | archetype |
      | catassoc  | Category Associator |           |
    And the following "role capabilities" exist:
      | role     | customfield/multicategory:manage |
      | catassoc | allow                            |
    And the following "role assigns" exist:
      | user     | role     | contextlevel | reference |
      | teacher1 | catassoc | Category     | CAT_B     |
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    Then "Category B" "autocomplete_suggestions" should exist
    And "Category B / Child of B" "autocomplete_suggestions" should exist
    And "Category B / Hidden Cat" "autocomplete_suggestions" should not exist
    And I log out
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I expand the "Associated categories" autocomplete
    Then "Category B / Hidden Cat" "autocomplete_suggestions" should exist
    And I log out
