@local @local_dash @dashaddon @dashaddon_learning_path @javascript

Feature: Add learning path widget in dash block
     In order to enable the leaning path widgets in dash block on the dashboard
     As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
      | Category 04 | 0        | CAT4     |
      | Category 05 | 0        | CAT5     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1 | C1        | CAT1     | 1                | 3           |                |                 |
      | Course 2 | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3 | C3        | CAT2     | 1                | 1           |                |                 |
      | Course 4 | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 5 | C5        | CAT4     | 1                | 3           |                |                 |
      | Course 6 | C6        | CAT4     | 0                | 2           |                |                 |
      | Course 7 | C7        | CAT4     | 1                | 1           |                |                 |
      | Course 8 | C8        | CAT4     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 9 | C9        | CAT4     | 1                | 3           |                |                 |
      | Course 10| C10       | cAT5     | 0                | 2           |                |                 |
      | Course 11| C11       | CAT5     | 1                | 1           |                |                 |
      | Course 12| C12       | CAT5     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 13| C13       | CAT5     | 1                | 3           |                |                 |
      | Course 14| C14       | CAT5     | 0                | 2           |                |                 |
      | Course 15| C15       | CAT5     | 1                | 1           |                |                 |
      | Course 16| C16       | cAT5     | 1                | 2           | ##1 year ago## | ##1 month ago## |
    And the following "activities" exist:
      | activity      | name               | course   | idnumber | intro                 | section    | completion |
      | assign        | Assignment 1       | C1       | page01   | Welcome to Assignment | 1          | 1          |
      | assign        | Assignment 2       | C3       | page02   | Welcome to Assignment | 1          | 1          |
      | choice        | My quiz B          | C4       | choice   | Welcome to Quiz       | 1          | 1          |
      | choice        | Quiz 1             | C4       | choice   | Welcome to Quiz       | 2          | 1          |
      | chat          | Students chat room | C1       | chat01   | Welcome to chat       | 3          | 1          |
      | book          | Book 1             | C2       | book     | Welcome to Book       | 1          | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | teacher   | 1        | teacher1@example.com |
      | teacher2 | teacher   | 2        | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend |
      | student1 | C1     | student | ## today ## | 0       |
      | student1 | C2     | student | 0           | 0       |
      | student1 | C3     | student | 0           | 0       |
      | student1 | C4     | student | 0           | 0       |
      | student2 | C5     | student | 0           | 0       |
      | teacher2 | C11    | teacher | 0           | 0       |
      | teacher1 | C1     | teacher | 0           | 0       |
      | teacher1 | C2     | teacher | 0           | 0       |
      | teacher1 | C3     | teacher | 0           | 0       |

  Scenario:Widgets: Learning path
    Given I log in as "admin"
#--Course page--
    And I am on "Course 5" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "checkbox"
    And I press "Save changes"
    And I am on "Course 11" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "checkbox"
    And I press "Save changes"
#--Category page--
    And I am on course index
    And I click on "Category 01" "link"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "checkbox"
    And I press "Save changes"
    #And I reload the page
    And I am on course index
    And I click on "Category 02" "link"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "checkbox"
    And I press "Save changes"
    #And I reload the page
    And I log out
#--Student login Course page--
    And I log in as "student2"
    And I am on "Course 5" course homepage
    And I should see "New Dash"
    And "li.grid-block[data-title='Course 5'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 6'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 7'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 8'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 9'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And I log out
#--Teacher login Course page--
    And I log in as "teacher2"
    And I am on "Course 11" course homepage
    And I should see "New Dash"
    And "li.grid-block[data-title='Course 10'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 11'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 12'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 13'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 14'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 15'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 16'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And I log out
#--Student login Category page--
    And I log in as "student1"
    And I am on course index
    And I click on "Category 01" "link"
    And I should see "New Dash"
    And "li.grid-block[data-title='Course 1'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 2'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 3'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 6'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 8'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 11'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 15'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 16'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And I log out
#--Teacher login Category page--
    And I log in as "teacher1"
    And I am on course index
    And I click on "Category 02" "link"
    And I should see "New Dash"
    And "li.grid-block[data-title='Course 1'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 2'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 3'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 10'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 15'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 16'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And I log out
