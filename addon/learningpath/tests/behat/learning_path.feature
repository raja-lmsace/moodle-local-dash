@local @local_dash @dashaddon @dashaddon_learning_path @javascript @_file_upload

Feature: Add learning path widget in dash block
     In order to enable the leaning path widgets in dash block on the dashboard
     As an admin

  Background:
    Given the following config values are set as admin:
      | debug | 0 |
      | debugdisplay | 0 |
    And the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
      | Category 04 | 0        | CAT4     |
      | Category 05 | 0        | CAT5     |
    And the following "custom field categories" exist:
      | name   | component   | area   | itemid |
      | shape  | core_course | course | 0      |
      | visual | core_course | course | 1      |
    And the following "custom fields" exist:
      | name   | category    | type   | shortname | configdata                                 |
      | Shape  | shape       | select | shape1    | {"options":"E-Learning\nWebinar\nSeminar"} |
      | Visual | shape       | select | visual1   | {"options":"Heart\nTrophy\nCertificate"}   |
    And the following "courses" exist:
      | fullname  | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1  | C1        | CAT1     | 1                | 3           |                |                 |
      | Course 2  | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3  | C3        | CAT2     | 1                | 1           | ##2 days ago## | ##yesterday##   |
      | Course 4  | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 5  | C5        | CAT4     | 1                | 3           |                |                 |
      | Course 6  | C6        | CAT4     | 0                | 2           |                |                 |
      | Course 7  | C7        | CAT4     | 1                | 1           |                |                 |
      | Course 8  | C8        | CAT4     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 9  | C9        | CAT4     | 1                | 3           |                |                 |
      | Course 10 | C10       | CAT5     | 0                | 2           |                |                 |
      | Course 11 | C11       | CAT5     | 1                | 1           |                |                 |
      | Course 12 | C12       | CAT5     | 1                | 2           | ##1 year ago## | ##1 month ago## |
      | Course 13 | C13       | CAT5     | 1                | 3           |                |                 |
      | Course 14 | C14       | CAT5     | 0                | 2           |                |                 |
      | Course 15 | C15       | CAT5     | 1                | 1           |                |                 |
      | Course 16 | C16       | CAT5     | 1                | 2           | ##1 year ago## | ##1 month ago## |
    And the following "activities" exist:
      | activity      | name               | course   | idnumber | intro                 | section    | completion | gradepass |
      | assign        | Assignment 1       | C1       | page01   | Welcome to Assignment | 1          | 1          | 50.00     |
      | assign        | Assignment 2       | C3       | page02   | Welcome to Assignment | 1          | 1          | 50.00     |
      | choice        | My quiz B          | C4       | choice   | Welcome to Quiz       | 1          | 1          | 50.00     |
      | choice        | Quiz 1             | C4       | choice   | Welcome to Quiz       | 2          | 1          | 50.00     |
      | book          | Book 1             | C2       | book     | Welcome to Book       | 1          | 1          | 50.00     |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | teacher   | 1        | teacher1@example.com |
      | teacher2 | teacher   | 2        | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    | timestart   | timeend | enrol  |
      | student1 | C1     | student | ## today ## | 0       | manual |
      | student1 | C2     | student | 0           | 0       | manual |
      | student1 | C4     | student | 0           | 0       | manual |
      | student2 | C5     | student | 0           | 0       | manual |
      | teacher2 | C11    | teacher | 0           | 0       | manual |
      | teacher1 | C1     | teacher | 0           | 0       | manual |
      | teacher1 | C2     | teacher | 0           | 0       | manual |
      | teacher1 | C3     | teacher | 0           | 0       | manual |

    And I log in as "admin"
    # Add svg image to Path for different devices
    And I navigate to "Plugins > Local plugins > Dash Pro" in site administration
    And I set the following fields to these values:
      | s_local_dash_customselectfield  | Shape  |
      | s_local_dash_customvisualfield  | Visual |
    And I press "Save changes"
    And I select "Triangle" from the "Shape mapping: Webinar" singleselect
    And I select "Star" from the "Shape mapping: Seminar" singleselect
    And I open the autocomplete suggestions list in the ".adminsettings .form-item:last-child .form-setting" "css_element"
    And I click on "fa-certificate" item in the autocomplete list
    And I open the autocomplete suggestions list in the ".adminsettings .form-item:nth-last-child(3) .form-setting" "css_element"
    And I click on "fa-trophy" item in the autocomplete list
    And I open the autocomplete suggestions list
    And I click on "fa-heart" item in the autocomplete list
    And I press "Save changes"

    # Course 1 custom fields
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/image.png" file to "Course image" filemanager
    And I select "E-Learning" from the "Shape" singleselect
    And I select "Heart" from the "Visual" singleselect
    And I press "Save and display"

    # Course 2 custom fields
    And I am on "Course 2" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/image1.png" file to "Course image" filemanager
    And I select "Webinar" from the "Shape" singleselect
    And I select "Trophy" from the "Visual" singleselect
    And I press "Save and display"

    # Course 3 custom fields
    And I am on "Course 3" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/image.png" file to "Course image" filemanager
    And I select "Seminar" from the "Shape" singleselect
    And I select "Certificate" from the "Visual" singleselect
    And I press "Save and display"

    # Add Dash block
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    Then I configure the "New Dash" block
    And I set the field "Block title" to "Learning Path"
    And I set the following fields to these values:
        | Region | content |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out

  Scenario: Widgets: Learning path
    Given I log in as "admin"

    #--Course page--
    And I am on "Course 5" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    And I wait until the page is ready
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "text" in the ".modal .dash-preferences-form" "css_element"
    And I press "Save changes"

    And I am on "Course 11" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    And I wait until the page is ready
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "text" in the ".modal .dash-preferences-form" "css_element"
    And I press "Save changes"
    #--Category page--
    And I am on course index

    And I click on "Category 01" "link"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    And I wait until the page is ready
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "text" in the ".modal .dash-preferences-form" "css_element"
    And I press "Save changes"
    #And I reload the page
    And I am on course index

    And I click on "Category 02" "link"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Learning Path" "radio"
    And I wait until the page is ready
    Then I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on "Current Category" "text" in the ".modal .dash-preferences-form" "css_element"
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
    And "li.grid-block[data-title='Course 3'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
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
    And "li.grid-block[data-title='Course 3'] .grid-item.notstarted" "css_element" should exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 1'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 2'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 10'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 15'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And "li.grid-block[data-title='Course 16'] .grid-item.notstarted" "css_element" should not exist in the "#gridLayout" "css_element"
    And I log out

  Scenario: learning path block default info area
    Given I log in as "admin"

    # Learnign path block info area setup
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the field "config_preferences[infoarea]" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    # Check Learning path block modal navigation
    And I log in as "student1"
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".modal-dialog .grid-layout-block .card-title" "css_element"
    And I click on "Next" "link" in the ".modal-dialog" "css_element"
    Then I should see "Course 2" in the ".modal-dialog .grid-layout-block .card-title" "css_element"
    And I click on "Previous" "link" in the ".modal-dialog" "css_element"
    Then I should see "Course 1" in the ".modal-dialog .grid-layout-block .card-title" "css_element"
    And I click on "Close" "button" in the ".modal-dialog" "css_element"
    And I click on ".learning-path-grid .grid-block:last-child .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 16" in the ".modal-dialog .grid-layout-block .card-title" "css_element"
    And I click on "Close" "button" in the ".modal-dialog" "css_element"

    And ".learning-path-header" "css_element" should exist in the "Learning Path" "block"
    # Top info area postion and sidebar not exist
    And ".sidebar" "css_element" should not exist in the ".dash-block-content" "css_element"
    And I wait until the page is ready
    # And I should see "You have completed 0 out of 16 courses. The next course in this learning path is: Course 1" in the ".learning-path-header" "css_element"
    And "Resume learning path" "link" should exist in the ".learning-path-header .learningpath-info-btn" "css_element"
    And I click on "Resume learning path" "link" in the ".learning-path-header .learningpath-info-btn" "css_element"
    Then I should see "Course 1" in the ".page-header-headings" "css_element"
    And I log out

    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"

    # Info area position - Sidebar
    And I set the following fields to these values:
      | Info area position  | Sidebar      |
      | Info area KPI 1     | Courses (%)  |
      | Info area KPI 2     | Period       |
      | Info area KPI 3     | Status       |
      | Info area KPI 4     | Badges       |
      | config_preferences[displaypathindex] | 1 |
      | config_preferences[displaybadges]  | 1 |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    # Check Stats of student1
    And I log in as "student1"
    And I follow "Dashboard"
    And I click on ".sidebar-toggle" "css_element" in the ".dash-learning-path.sidebar" "css_element"
    And ".learningpath-sidebar.show" "css_element" should exist in the ".dash-learning-path.sidebar" "css_element"
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    # Back button - from course details to course status
    And I click on ".details-back" "css_element" in the ".learningpath-sidebar .learningpath-course-details" "css_element"
    # Sidebar exist
    And ".learningpath-sidebar.show" "css_element" should exist in the ".dash-learning-path.sidebar" "css_element"
    # Courses stats
    Then I should see "0%" in the ".learningpath-stats-block .stats-block" "css_element"
    # Period stats
    And I should see "Not available" in the ".learningpath-stats-block .stats-block:nth-child(2)" "css_element"
    # Badges stats
    And I should see "0 / 0" in the ".learningpath-stats-block .stats-block:last-child" "css_element"
    # Faculty display
    And ".faculty-block" "css_element" should not exist in the "Learning Path" "block"
    And I log out

    # Disable Faculty, Course completion and badges in info area
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"

    # Info area position - Sidebar
    And I set the following fields to these values:
      | config_preferences[displaypathindex] | 0 |
      | Display faculty     | Non-editing Teacher, Teacher |
      | config_preferences[displaybadges]  | 0 |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    And I log in as "student1"
    And I follow "Dashboard"
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And ".learningpath-stats-block" "css_element" should not exist in the ".learningpath-sidebar .learningpath-course-details" "css_element"
    And "//img[contains(@src, 'u/f1') and @alt='teacher 1']" "xpath_element" should exist
    And "//img[contains(@src, 'u/f1') and @alt='Student 1']" "xpath_element" should not exist
    And ".learningpath-course-completion" "css_element" should not exist in the "Learning Path" "block"
    And ".badges-block" "css_element" should not exist in the "Learning Path" "block"

  Scenario: sidebar info area display
    And the following "activities" exist:
      | activity      | name               | course   | idnumber | intro                 | section    | completion |
      | assign        | Assignment 3       | C1       | page03   | Welcome to Assignment | 1          | 1          |
    Given I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"

    # Info area position - Sidebar
    And I set the field "config_preferences[infoarea]" to "1"
    And I set the following fields to these values:
      | Info area position  | Sidebar  |
      | Info area KPI 1     | Courses  |
      | Info area KPI 2     | Period   |
      | Info area KPI 3     | Status   |
      | Info area KPI 4     | Badges   |
      | config_preferences[displaypathindex] | 1 |
      | Display faculty     | Non-editing Teacher, Teacher |
      | config_preferences[displaybadges]  | 1 |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    # Enrollement end date and badges setup
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Participants" in current page administration
    And I click on ".editenrollink" "css_element" in the "student1" "table_row"
    And I set the following fields to these values:
      | timeend[enabled] | 1             |
      | Enrolment ends   | ## tomorrow ##|
    And I press "Save changes"

    # Badge 1
    And I am on "Course 1" course homepage
    And I navigate to "Badges" in current page administration
    And I click on "Add a new badge" "button"
    And I set the following fields to these values:
      | Name          | Test Badge 1                   |
      | Description   | Test badge related description |
      | Image caption | Test caption image             |
    And I upload "local/dash/addon/learningpath/tests/fixtures/image.png" file to "Image" filemanager
    And I press "Create badge"
    And I set the field "type" to "Activity completion"
    And I wait until the page is ready
    And I wait "10" seconds
    And I click on "input[type='checkbox']" "css_element" in the "#id_first_headercontainer" "css_element"
    And I press "Save"
    And I click on "Enable access" "button"
    And I click on "Enable" "button" in the "Confirm" "dialogue"

    # Badge 2
    And I am on "Course 1" course homepage
    And I navigate to "Badges" in current page administration
    And I click on "Add a new badge" "button"
    And I set the following fields to these values:
      | Name          | Test Badge 2                   |
      | Description   | Test badge related description |
      | Image caption | Test caption image             |
    And I upload "local/dash/addon/learningpath/tests/fixtures/image1.png" file to "Image" filemanager
    And I press "Create badge"
    And I set the field "type" to "Activity completion"
    And I wait until the page is ready
    And I wait "10" seconds
    And I click on "input[type='checkbox']" "css_element" in the "#id_first_headercontainer" "css_element"
    And I press "Save"
    And I click on "Enable access" "button"
    And I click on "Enable" "button" in the "Confirm" "dialogue"

    # Badge 3
    And I am on "Course 3" course homepage
    And I navigate to "Badges" in current page administration
    And I click on "Add a new badge" "button"
    And I set the following fields to these values:
      | Name          | Test Badge 3                   |
      | Description   | Test badge related description |
      | Image caption | Test caption image             |
    And I upload "local/dash/addon/learningpath/tests/fixtures/image.png" file to "Image" filemanager
    And I press "Create badge"
    And I set the field "type" to "Activity completion"
    And I wait until the page is ready
    And I wait "10" seconds
    And I click on "input[type='checkbox']" "css_element" in the "#id_first_headercontainer" "css_element"
    And I press "Save"
    And I click on "Enable access" "button"
    And I click on "Enable" "button" in the "Confirm" "dialogue"

    # Badge 4
    And I am on "Course 3" course homepage
    And I navigate to "Badges" in current page administration
    And I click on "Add a new badge" "button"
    And I set the following fields to these values:
      | Name          | Test Badge 4                   |
      | Description   | Test badge related description |
      | Image caption | Test caption image             |
    And I upload "local/dash/addon/learningpath/tests/fixtures/image1.png" file to "Image" filemanager
    And I press "Create badge"
    And I set the field "type" to "Activity completion"
    And I wait until the page is ready
    And I click on "input[type='checkbox']" "css_element" in the ".fcontainer" "css_element"
    And I press "Save"
    And I click on "Enable access" "button"
    And I click on "Enable" "button" in the "Confirm" "dialogue"

    # Check Stats of student1 after enrollement end date and badges setup
    And I log in as "student1"

    # Course 1 stats
    And I follow "Dashboard"
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And I click on ".details-back" "css_element" in the ".learningpath-sidebar .learningpath-course-details" "css_element"

    # Courses stats
    Then I should see "0 / 16" in the ".learningpath-stats-block .stats-block" "css_element"
    # Period stats
    And I should see "## today ##%d %B##" in the ".learningpath-stats-block .stats-block:nth-child(2)" "css_element"
    # Status stats
    And I should see "## tomorrow ##%d %B##" in the ".learningpath-stats-block .stats-block:nth-child(2)" "css_element"

    # Course completion status min progress bar - not strated
    And ".course-unavailable" "css_element" should exist in the ".learningpath-sidebar .learningpath-course-completion .progress-mini" "css_element"

    # Course completion status collapse and expand - not strated
    And I click on ".completion-header" "css_element" in the ".learningpath-sidebar .learningpath-course-completion" "css_element"
    And ".course-unavailable" "css_element" should exist in the ".learningpath-sidebar .completion-details > .course-progress-item" "css_element"

    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    # Button status - Start
    And I click on "Start course" "link" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"

    And I am on "Course 1" course homepage
    And I click on "Mark as done" "button"
    And "Done" "button" should exist in the ".activity .activity-completion " "css_element"
    And I follow "Dashboard"
    # Course 1 stats
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And I click on ".details-back" "css_element" in the ".learningpath-sidebar .learningpath-course-details" "css_element"

    # Course completion status min progress bar - inprogress
    And ".course-inprogress" "css_element" should exist in the ".learningpath-sidebar .learningpath-course-completion .progress-mini" "css_element"

    # Course completion status collapse and expand - inprogress
    And I click on ".completion-header" "css_element" in the ".learningpath-sidebar .learningpath-course-completion" "css_element"
    And ".course-inprogress" "css_element" should exist in the ".learningpath-sidebar .completion-details > .course-progress-item" "css_element"

    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    # Button status - Resume
    And I click on "Resume course" "link" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"
    And I click on "Mark as done" "button" in the ".activity:nth-child(2) .activity-completion" "css_element"
    And I follow "Dashboard"
    # Course 1 stats
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    # Button status
    And I should see "Completed course" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"
    And I click on ".details-back" "css_element" in the ".learningpath-sidebar .learningpath-course-details" "css_element"
    # Faculty display
    And ".learningpath-info-area .badges-block" "css_element" should exist in the "Learning Path" "block"

    # Badges stats
    # Badges earned
    And ".badge-earned" "css_element" should exist in the ".learningpath-sidebar .badges-block .badges-list .badge-item" "css_element"
    And ".badge-earned" "css_element" should exist in the ".learningpath-sidebar .badges-block .badges-list .badge-item:nth-child(2)" "css_element"
    # Badges not earned
    And ".badge-earned" "css_element" should not exist in the ".learningpath-sidebar .badges-block .badges-list .badge-item:nth-child(3)" "css_element"
    # Badges not earned confirm
    And ".badge-not-earned" "css_element" should exist in the ".learningpath-sidebar .badges-block .badges-list .badge-item:nth-child(4)" "css_element"
    And I should see "2 / 4" in the ".learningpath-stats-block .stats-block:last-child" "css_element"
    # Course completion status min progress bar - completed
    And ".course-completed" "css_element" should exist in the ".learningpath-sidebar .learningpath-course-completion .progress-mini" "css_element"
    # Course completion status collapse and expand - completed
    And I click on ".completion-header" "css_element" in the ".learningpath-sidebar .learningpath-course-completion" "css_element"
    And ".course-completed" "css_element" should exist in the ".learningpath-sidebar .completion-details > .course-progress-item" "css_element"
    And I log out

  Scenario: learning path block course appearance
    Given I log in as "admin"
    And I am on the "Course 3" "enrolment methods" page
    And I click on "Enable" "link" in the "Self enrolment (Student)" "table_row"

    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"

    # Info area - Sidebar
    And I set the field "config_preferences[infoarea]" to "1"
    And I set the following fields to these values:
      | Info area position  | Sidebar      |
      | Not available       | #021B87    |
      | Available           | #fcec83    |
      | Enrolled            | #9300A0    |
      | In progress         | #FF7C28    |
      | Completed           | #84AA00    |
      | Failed              | #DB3C00    |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    # Check Stats of student1
    And I log in as "student1"
    And I follow "Dashboard"
    And I click on ".learning-path-grid .grid-block .grid-link" "css_element" in the "Learning Path" "block"
    Then I should see "Course 1" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And I click on ".details-back" "css_element" in the ".learningpath-sidebar .learningpath-course-details" "css_element"

    # Course appearance
    # Not available color
    And ".unavailable" "css_element" should exist in the ".learning-path-widget.sidebar .grid-block:nth-child(5)" "css_element"
    And I check dash css "rgb(2, 27, 135)" ".grid-block:nth-child(5) .grid-item.unavailable .grid-link" "background"
    And I click on ".grid-link" "css_element" in the ".learning-path-widget.sidebar .grid-block:nth-child(5)" "css_element"
    And I should see "Course 5" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And I click on "Start course" "link" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"
    And I should see "You cannot enrol yourself in this course." in the "#notice" "css_element"

    # Available color
    And I follow "Dashboard"
    And I check dash css "rgb(252, 236, 131)" ".learning-path-widget.sidebar .grid-block:nth-child(3) .grid-link" "background"
    And I click on ".grid-link" "css_element" in the ".learning-path-widget.sidebar .grid-block:nth-child(3)" "css_element"
    Then I should see "Course 3" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And I click on "Start course" "link" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"
    # And I should see "Enrol me" "button" in the ".card .card-footer .singlebutton" "css_element"
    And I click on "Enrol me" "button"
    And I should see "You are enrolled in the course." in the ".alert-success" "css_element"

    # Enrolled Available color - Not started
    And I follow "Dashboard"
    And ".notstarted" "css_element" should exist in the ".learning-path-widget.sidebar .grid-block:nth-child(4)" "css_element"
    And I check dash css "rgb(147, 0, 160)" ".learning-path-widget.sidebar .grid-block:nth-child(4) .grid-link" "background"
    And I click on ".grid-link" "css_element" in the ".learning-path-widget.sidebar .grid-block:nth-child(4)" "css_element"
    Then I should see "Course 4" in the ".learningpath-sidebar .grid-layout-block .card-title" "css_element"
    And I should see "0%" in the ".learningpath-sidebar .learningpath-course-details .progress .progress-not-started" "css_element"
    And I click on "Start course" "link" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"
    And I click on "Mark as done" "button"
    And "Done" "button" should exist in the ".activity .activity-completion" "css_element"
    And I click on "Mark as done" "button"
    And "Done" "button" should exist in the ".activity .activity-completion" "css_element"

    # Completed color
    And I follow "Dashboard"
    And ".completed" "css_element" should exist in the ".learning-path-widget.sidebar .grid-block:nth-child(4)" "css_element"
    And I check dash css "rgb(132, 170, 0)" ".learning-path-widget.sidebar .grid-block:nth-child(4) .grid-link" "background"
    And I click on ".grid-link" "css_element" in the ".learning-path-widget.sidebar .grid-block:nth-child(4)" "css_element"
    And I should see "Completed course" in the ".learningpath-sidebar .grid-layout-block .btn-block" "css_element"
    And I log out

    And I log in as "admin"
    # Grade activity to failed status
    And I am on "Course 1" course homepage
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "criteria_grade" to "1"
    And I set the field "Required course grade" to "60"
    And I press "Save changes"
    And I log out

    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Mark as done" "button"
    And "Done" "button" should exist in the ".activity .activity-completion" "css_element"
    And I log out

    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I click on "Assignment 1" "link"
    And I click on "Grade" "link" in the ".tertiary-navigation" "css_element"

    And I set the field "grade" to "20"
    And I press "Save changes"
    And I log out

    And I log in as "student1"
    And I follow "Dashboard"
    And I click on ".learning-path-grid .grid-block:nth-child(3) .grid-link" "css_element" in the "Learning Path" "block"
    # Failed color
    And ".failed" "css_element" should exist in the ".learning-path-widget.sidebar .grid-block" "css_element"
    And I check dash css "rgb(219, 60, 0)" ".grid-item.failed .grid-link" "background"

  Scenario: learning path - course visual
    Given I log in as "admin"
    # Add svg image to Path for different devices
    And I navigate to "Plugins > Local plugins > Dash Pro" in site administration
    And I upload "local/dash/addon/learningpath/tests/fixtures/path2.svg" file to "Desktop resources" filemanager
    And I press "Save changes"
    And I upload "local/dash/addon/learningpath/tests/fixtures/path1.svg" file to "Tablet resources" filemanager
    And I press "Save changes"
    And I upload "local/dash/addon/learningpath/tests/fixtures/path3.svg" file to "Mobile resources" filemanager
    And I press "Save changes"

    # Upload SVG for Path for devices
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the field "config_preferences[infoarea]" to "1"
    # Path for devices - Default course size
    And I set the following fields to these values:
      | Info area position  | Sidebar  |
      | Path for Desktop    | Path2    |
      | Path for Tablet     | Path1    |
      | Path for Mobile     | Path3    |
      | Course size         | Small    |
      | Course visual       | Number   |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    # Shape display - Dot circle, Small size, Number
    And I log in as "student1"
    And I follow "Dashboard"
    And I should see "1" in the ".learning-path-block.svg-block .course-number-text" "css_element"
    And I click on ".course-shape-circle" "css_element" in the ".learning-path-block.svg-block" "css_element"
    Then I should see "Course 1" in the ".learningpath-course-details .grid-layout-block .card-title" "css_element"
    And I log out

    # Shape display - Tiny, Triangle, Course image
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the following fields to these values:
      | Course size   | Tiny         |
      | Course shape  | Triangle     |
      | Course visual | Course image |
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    And I log in as "student1"
    And I follow "Dashboard"
    And ".course-shape-triangle" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And I click on ".course-shape-triangle" "css_element" in the ".learning-path-block.svg-block" "css_element"
    Then I should see "Course 1" in the ".learningpath-course-details .grid-layout-block .card-title" "css_element"
    And I log out

    # Custom field of course shape
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"

    # Shape display - Triangle
    # Path for devices - Default course size
    And I set the following fields to these values:
      | Course size    | Medium                  |
      | Course shape   | Shape set for (Shape)   |
      | Course visual  | Visual set for (Visual) |
    And I press "Save changes"

    # Coruse 1 - Shape
    And ".course-shape-circle" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And ".fa-heart" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And I click on ".course-shape-circle" "css_element" in the ".learning-path-block.svg-block" "css_element"
    And I should see "Course 1" in the ".learningpath-course-details .grid-layout-block .card-title" "css_element"

    # Coruse 2 - Shape
    And ".course-shape-triangle" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And ".fa-trophy" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And I click on ".course-shape-triangle" "css_element" in the ".learning-path-block.svg-block" "css_element"
    And I should see "Course 2" in the ".learningpath-course-details .grid-layout-block .card-title" "css_element"

    # Coruse 3 - Shape
    And ".course-shape-star" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And ".fa-certificate" "css_element" should exist in the ".learning-path-block.svg-block" "css_element"
    And I click on ".course-shape-star" "css_element" in the ".learning-path-block.svg-block" "css_element"
    And I should see "Course 3" in the ".learningpath-course-details .grid-layout-block .card-title" "css_element"

  Scenario: learning path with zones
    Given I log in as "admin"
    # Add svg image to Path for different devices
    And I navigate to "Plugins > Local plugins > Dash Pro" in site administration
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones1.svg" file to "Desktop resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones2.svg" file to "Desktop resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones3.svg" file to "Desktop resources" filemanager
    And I press "Save changes"
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones1.svg" file to "Tablet resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones2.svg" file to "Tablet resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones3.svg" file to "Tablet resources" filemanager
    And I press "Save changes"
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones1.svg" file to "Mobile resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones2.svg" file to "Mobile resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/zones3.svg" file to "Mobile resources" filemanager
    And I press "Save changes"
    And I set the field "Course size" to "Tiny"
    And I press "Save changes"

    # Upload SVG for Path for devices
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the field "config_preferences[infoarea]" to "1"
    # Path for devices - Default course size
    And I set the following fields to these values:
      | Info area position  | Sidebar  |
      | Positioning         | In zones |
      | Path for Desktop    | Zones1   |
      | Path for Tablet     | Zones2   |
      | Path for Mobile     | Zones3   |
    # And I press "Save changes"
    # And I click on "Reset Dashboard for all users" "button"
    # And I click on "Continue" "button"
    # And I navigate to "Appearance > Default Dashboard page" in site administration
    # And I turn dash block editing mode on
    # And I open the "Learning Path" block preference
    # And I click on "Fields" "link"
    # And I wait until the page is ready
    And I click on "Configure zones" "button"
    And I set the following fields to these values:
      | zone_desktop_polygon_0_enabled | 1 |
      | zone_desktop_ellipse_7_enabled | 1 |
      | zone_desktop_ellipse_6_enabled | 1 |
      | zone_desktop_ellipse_5_enabled | 1 |
      | zone_desktop_ellipse_4_enabled | 1 |
      | zone_desktop_ellipse_3_enabled | 1 |
      | zone_desktop_ellipse_2_enabled | 1 |
      | zone_desktop_ellipse_1_enabled | 1 |
      | zone_desktop_rect_1_enabled    | 1 |
      | zone_desktop_ellipse_0_enabled | 1 |
      | zone_desktop_rect_2_enabled    | 1 |
      | zone_desktop_rect_0_enabled    | 1 |
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_rect_0_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 1" item in the autocomplete list
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_rect_1_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 10" "text" in the "#fitem_id_zone_desktop_rect_1_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_0_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 11" "text" in the "#fitem_id_zone_desktop_ellipse_0_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_rect_2_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 12" "text" in the "#fitem_id_zone_desktop_rect_2_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_1_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 13" "text" in the "#fitem_id_zone_desktop_ellipse_1_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_2_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 14" "text" in the "#fitem_id_zone_desktop_ellipse_2_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_3_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 15" "text" in the "#fitem_id_zone_desktop_ellipse_3_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_4_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 16" "text" in the "#fitem_id_zone_desktop_ellipse_4_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_5_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 2" "text" in the "#fitem_id_zone_desktop_ellipse_5_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_6_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 3" "text" in the "#fitem_id_zone_desktop_ellipse_6_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_ellipse_7_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 4" "text" in the "#fitem_id_zone_desktop_ellipse_7_courseid .form-autocomplete-suggestions" "css_element"
    And I open the autocomplete suggestions list in the "#fitem_id_zone_desktop_polygon_0_courseid .form-autocomplete-input" "css_element"
    And I click on "Course 5" "text" in the "#fitem_id_zone_desktop_polygon_0_courseid .form-autocomplete-suggestions" "css_element"
    And I click on "Save changes" "button" in the "Configure Zones" "dialogue"
    # Course shape
    And I set the field "Course shape" to "Shape of the vector element"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out

    And I log in as "student1"

    # Zone Shape
    And ".rect-zone" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='rect_0']" "css_element"
    And ".ellipse-zone" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='ellipse_0']" "css_element"
    And ".polygon-zone" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='polygon_0']" "css_element"

    # Zone status - Unavailable
    And ".course-circle.unavailable" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='rect_1']" "css_element"

    # Zone status - notstarted
    And ".rect-zone.notstarted" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='rect_0']" "css_element"

    # Zone status - inprogress
    And I am on "Course 4" course homepage
    And I click on "Mark as done" "button"
    And "Done" "button" should exist in the ".activity .activity-completion" "css_element"
    And I follow "Dashboard"
    And ".ellipse-zone.inprogress" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='ellipse_7']" "css_element"

    # Zone status - completed
    And I am on "Course 4" course homepage
    And I click on "Mark as done" "button"
    And "Done" "button" should exist in the ".section:nth-child(3) .activity-completion" "css_element"
    And I follow "Dashboard"
    And ".ellipse-zone.completed" "css_element" should exist in the ".learning-path-block.svg-block [data-original-zone-key='ellipse_7']" "css_element"

  Scenario: learning path - custom paths
    And I log in as "admin"

    # Add svg image to Path for different devices
    And I navigate to "Plugins > Local plugins > Dash Pro" in site administration
    And I upload "local/dash/addon/learningpath/tests/fixtures/path1.svg" file to "Desktop resources" filemanager
    And I upload "local/dash/addon/learningpath/tests/fixtures/path2.svg" file to "Desktop resources" filemanager
    And I press "Save changes"

    # Adding resources with the User roles to manage Custom path
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    # Block preference without the path image for Desktop, Tablet, Mobile
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the field "config_preferences[infoarea]" to "1"
    And I set the following fields to these values:
      | config_preferences[infoarea] | 1       |
      | Info area position           | Sidebar |
    Then "//select[@name='config_preferences[desktoppath]']/option[contains(., 'Path 3')]" "xpath_element" should not exist
    Then "//select[@name='config_preferences[tabletpath]']/option[contains(., 'Path 3')]" "xpath_element" should not exist
    Then "//select[@name='config_preferences[mobilepath]']/option[contains(., 'Path 3')]" "xpath_element" should not exist
    And I press "Save changes"

    # Block configuration
    And I click on ".dropdown-toggle" "css_element" in the ".block_dash .menubar" "css_element"
    And I click on "Configure Learning Path block" "link" in the ".block_dash .menubar .dropdown-menu" "css_element"
    And I expand all fieldsets
    And I upload "local/dash/addon/learningpath/tests/fixtures/path3.svg" file to "Desktop resources" filemanager
    And I wait until the page is ready
    And I click on "Save changes" "button" in the "Configure Learning Path block" "dialogue"

    # Preferences
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    And I set the following fields to these values:
      | Path for Desktop  | Path1  |
    And I click on "Save changes" "button" in the "Edit preferences" "dialogue"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"

    # Adding resources with the User roles to manage Custom path
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    # Block preference without the path image for Desktop, Tablet, Mobile
    And I open the "Learning Path" block preference
    And I click on "Fields" "link"
    # Path for devices - Default course size
    And I wait "20" seconds
    Then "//select[@name='config_preferences[desktoppath]']/option[contains(., 'Path3')]" "xpath_element" should exist
      | Path for Desktop    | Path3    |
      | Path for Tablet     | Path3    |
      | Path for Mobile     | Path3    |
    And I click on "Save changes" "button" in the "Edit preferences" "dialogue"
    And I click on "Reset Dashboard for all users" "button"
    And I click on "Continue" "button"
    And I log out
