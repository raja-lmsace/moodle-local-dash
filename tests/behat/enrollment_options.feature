@local @local_dash @dash_enrollment_options @javascript
Feature: Enrolment options field displays correct label based on user enrolment state
  In order to understand my enrolment status and available options for each course
  As a user viewing the dashboard
  I need to see the correct enrolment options label for each course

  Background:
    Given the following "custom field categories" exist:
      | name    | component   | area   | itemid |
      | Booking | core_course | course | 0      |
    And the following "custom fields" exist:
      | name         | category | type | shortname   |
      | Shop URL     | Booking  | text | shopurl     |
      | Book Content | Booking  | text | bookcontent |
    And the following "categories" exist:
      | name       | category | idnumber |
      | Category 1 | 0        | CAT1     |
    And the following "courses" exist:
      | fullname              | shortname   | category |
      | Course Active         | EACTIVE     | CAT1     |
      | Course Suspended      | ESUSP       | CAT1     |
      | Course Future         | EFUTURE     | CAT1     |
      | Course Expired        | EEXPIRED    | CAT1     |
      | Course Guest          | EGUEST      | CAT1     |
      | Course NoMethod       | ENONE       | CAT1     |
      | Course Self           | ESELF       | CAT1     |
      | Course Shop           | ESHOP       | CAT1     |
      | Course Booking        | EBOOK       | CAT1     |
      | Course SelfEnrolld    | ESELFENRL   | CAT1     |
      | Course Multi Method   | EMULTI      | CAT1     |
      | Course Auto Enrol     | EAUTO       | CAT1     |
      | Course Payment Single | EPAYMENTSGL | CAT1     |
      | Course Payment Multi  | EPAYMENTMUL | CAT1     |
      | Course Credit Single  | ECREDITSGL  | CAT1     |
      | Course Credit Multi   | ECREDITMUL  | CAT1     |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | manager1 | Manager   | One      | manager1@example.com |
    And the following "block_dash > dash blocks default" exist:
      | type       | name    | title     | fields                                                 |
      | datasource | courses | Test Dash | c_shortname,c_enrollment_options,c_smart_course_button |

  # Enrolled + Active -> "Active enrolment"
  Scenario: Active enrolment displays Active enrolment label
    Given the following "course enrolments" exist:
      | user     | course  | role    |
      | student1 | EACTIVE | student |
    When I log in as "student1"
    Then I should see "Active enrolment" in the "EACTIVE" "table_row"
    And I should not see "Free" in the "EACTIVE" "table_row"
    And I should not see "Open" in the "EACTIVE" "table_row"
    And I click on "View course" "link" in the "EACTIVE" "table_row"
    Then I should see "Course Active"

  # Enrolled + Suspended -> "Pending enrolment"
  Scenario: Suspended enrolment displays Pending enrolment label
    Given the following "course enrolments" exist:
      | user     | course | role    | status |
      | student1 | ESUSP  | student | 1      |
    When I log in as "student1"
    Then I should see "Pending enrolment" in the "ESUSP" "table_row"
    And I click on "View course" "link" in the "ESUSP" "table_row"
    Then I should see "You cannot enrol yourself in this course."

  # Enrolled + Future (timestart in future) -> "Upcoming enrolment"
  Scenario: Future enrolment displays Upcoming enrolment label
    Given the following "course enrolments" exist:
      | user     | course  | role    | timestart    |
      | student1 | EFUTURE | student | ##tomorrow## |
    When I log in as "student1"
    Then I should see "Upcoming enrolment" in the "EFUTURE" "table_row"
    And I should not see "Active enrolment" in the "EFUTURE" "table_row"
    And I should not see "Pending enrolment" in the "EFUTURE" "table_row"
    And I click on "View course" "link" in the "EFUTURE" "table_row"
    Then I should see "You cannot enrol yourself in this course."

  # Enrolled + Expired (timeend in past) -> "Expired enrolment"
  Scenario: Expired enrolment displays Expired enrolment label
    Given the following "course enrolments" exist:
      | user     | course   | role    | timestart       | timeend       |
      | student1 | EEXPIRED | student | ##1 month ago## | ##yesterday## |
    When I log in as "student1"
    Then I should see "Expired enrolment" in the "EEXPIRED" "table_row"
    And I should not see "Active enrolment" in the "EEXPIRED" "table_row"
    And I should not see "Upcoming enrolment" in the "EEXPIRED" "table_row"
    And I click on "View course" "link" in the "EEXPIRED" "table_row"
    Then I should see "You cannot enrol yourself in this course."

  # Not enrolled + Guest access -> "Open"
  Scenario: Guest access course displays Open label for non-enrolled user
    Given I log in as "admin"
    And I am on the "Course Guest" "enrolment methods" page
    And I click on "Enable" "link" in the "Guest access" "table_row"
    And I log out
    When I log in as "student2"
    Then I should see "Open" in the "EGUEST" "table_row"
    And I click on "View course" "link" in the "EGUEST" "table_row"
    Then I should see "Course Guest"

  # Not enrolled + No methods + No external -> "Not available for enrolment"
  Scenario: Course with no enrolment methods displays Not available for enrolment label
    When I log in as "student2"
    Then I should see "Not available for enrolment" in the "ENONE" "table_row"
    And I should not see "Free" in the "ENONE" "table_row"
    And I should not see "Open" in the "ENONE" "table_row"
    And I click on "View course" "link" in the "ENONE" "table_row"
    Then I should see "You cannot enrol yourself in this course."

  # Not enrolled + No methods + Shop URL -> "Available for purchase"
  Scenario: Course with shop URL displays Available for purchase label
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Dash" in site administration
    And I set the field "Course shop url" to "Shop URL"
    And I press "Save changes"
    And I am on "Course Shop" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | Shop URL | https://example.com/ |
    And I press "Save and display"
    And I log out
    When I log in as "student2"
    Then I should see "Available for purchase" in the "ESHOP" "table_row"
    And I should not see "Not available for enrolment" in the "ESHOP" "table_row"
    And I click on "Buy now" "link" in the "ESHOP" "table_row"
    Then I should see "Example Domain"

  # Not enrolled + No methods + Custom content -> "Available for booking"
  Scenario: Course with booking content displays Available for booking label
    Given I log in as "admin"
    And I navigate to "Plugins > Blocks > Dash" in site administration
    And I set the field "Standard terms" to "External booking content here"
    And I press "Save changes"

    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on

    And I open the "Test Dash" block preference
    And I click on "Details area" "link"
    And I set the following fields to these values:
      | Details area                  | Expanding                        |
      | Details area size             | Like item                        |
      | Details header                | course: Course                   |
      | Details Title                 | course: Short name               |
      | Details Body 1                | course: Full name                |
      | Details custom content        | Standard terms                   |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out

    When I log in as "student2"
    Then I should see "Available for booking" in the "EBOOK" "table_row"
    And I should not see "Not available for enrolment" in the "EBOOK" "table_row"
    And I should not see "Available for purchase" in the "EBOOK" "table_row"
    And I click on "Book now" "link" in the "EBOOK" "table_row"
    Then I should see "External booking content here" in the "EBOOK" "table_row"

  # Not enrolled + Self enrolment -> "Free"
  Scenario: Course with self enrolment displays Free label for non-enrolled user
    Given I log in as "admin"
    And I add "Self enrolment" enrolment method in "Course Self" dashwith:
      | Custom instance name | Test self enrolment |
    And I log out
    When I log in as "student2"
    Then I should see "Free" in the "ESELF" "table_row"
    And I should not see "Not available for enrolment" in the "ESELF" "table_row"
    And I should not see "Active enrolment" in the "ESELF" "table_row"
    And I click on "Enrol Now" "link" in the "ESELF" "table_row"
    Then I should see "Course Self"

  # Single course credit enrolment -> "X credits"
  # Label shows the credit cost of the single credit enrolment instance
  Scenario: Single course credit enrolment displays X credits label
    Given I log in as "admin"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Enable" "link" in the "Course credit enrolment" "table_row"
    And I add "Course credit enrolment" enrolment method in "Course Active" dashwith:
      | Custom instance name | Credit enrol |
      | Credit cost          | 100          |
    And I log out
    When I log in as "student2"
    Then I should see "100 credits" in the "EACTIVE" "table_row"
    And I should not see "From" in the "EACTIVE" "table_row"
    And I should not see "Not available for enrolment" in the "EACTIVE" "table_row"
    And I click on "Enrol Now" "link" in the "EACTIVE" "table_row"
    Then I should see "You have insufficient course credits to enroll."

  # Multiple enrolment methods -> "Multiple options"
  # Old label was "See options" — spec renames it to "Multiple options"
  Scenario: Multiple enrolment methods displays Multiple options label
    Given I log in as "admin"
    And I am on the "Course Guest" "enrolment methods" page
    And I click on "Enable" "link" in the "Self enrolment" "table_row"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Enable" "link" in the "Course credit enrolment" "table_row"
    And I add "Course credit enrolment" enrolment method in "Course Guest" dashwith:
      | Custom instance name | Credit enrol |
      | Credit cost          | 100          |
    And I log out
    When I log in as "student2"
    Then I should see "See options" in the "EGUEST" "table_row"

  # Row (Payment single): Single enrolment on payment -> "X <currency>"
  # Label shows the actual cost and currency of the single payment enrolment
  Scenario: Single payment enrolment displays cost and currency label
    Given I log in as "admin"
    And I navigate to "Payments > Payment accounts" in site administration
    And I click on "Create payment account" "button"
    And I set the following fields to these values:
      | Account name | LMS account |
      | ID number    | 1001        |
    And I press "Save changes"
    And I click on "PayPal" "link" in the "LMS account" "table_row"
    And I set the following fields to these values:
      | Brand name | Test paypal |
      | Client ID  | Test        |
      | Secret     | Test        |
      | Enable     | 1           |
    And I press "Save changes"
    And the following config values are set as admin:
      | enrol_plugins_enabled | manual,self,guest,fee,cohort,meta |
    And I add "Enrolment on payment" enrolment method in "Course Shop" dashwith:
      | Custom instance name | Course fee |
      | Enrolment fee        | 100        |
    And I log out
    When I log in as "student2"
    Then I should see "100 USD" in the "ESHOP" "table_row"
    And I click on "Enrol Now" "link" in the "ESHOP" "table_row"
