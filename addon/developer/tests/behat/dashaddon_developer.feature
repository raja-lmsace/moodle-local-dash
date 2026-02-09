@local @local_dash @dashaddon @dashaddon_developer_support @javascript @_file_upload
Feature: Add a developer data source support in dash block
  In order to enable the developer data source in dash block on the course page
  As an admin

  Background:
    Given the following "categories" exist:
      | name        | category | idnumber |
      | Category 01 | 0        | CAT1     |
      | Category 02 | 0        | CAT2     |
      | Category 03 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections |
      | Course 1 | C1        | CAT1     | 1                | 3           |
      | Course 2 | C2        | CAT2     | 0                | 2           |
      | Course 3 | C3        | CAT3     | 1                | 2           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
      | teacher2 | Teacher   | Two      | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | admin    | C1     | manager |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | teacher1 | C1     | teacher |
      | student2 | C2     | student |

  Scenario: Check course image url field attribute in custom data source
    Given the following "dashaddon_developer > custom data sources" exist:
      | name         | idnumber           | maintable | enablejoins | joinrepeats | tablejoins            | tablejoinsalias | tablejoinon            | placeholderfields                        | fieldrepeats | selectfield               | fieldattribute                                      | attributevalue                                    |
      | Courses list | COURSEDATASOURCE01 | course    | 1           | 1           | ["course_categories"] | ["cc"]          | ["cc.id=mnt.category"] | ["mnt.id","mnt.category","mnt.fullname"] | 2            | ["mnt.fullname","mnt.id"] | [[null],["course_image_url","image","linked_data"]] | [[""],[null,null,"/course/view.php?id={mnt.id}"]] |
    And I log in as "admin"
    # Course 1 course image
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/developer/tests/fixtures/course1.jpg" file to "Course image" filemanager
    And I press "Save and display"
    # Course 2 course image
    And I am on "Course 2" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/developer/tests/fixtures/course2.jpg" file to "Course image" filemanager
    And I press "Save and display"
    # Course 3 course image
    And I am on "Course 3" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I upload "local/dash/addon/developer/tests/fixtures/course3.jpg" file to "Course image" filemanager
    And I press "Save and display"
    # Check custom data source in dash
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Courses list" "radio"
    And I configure the "New Dash" block
    And I set the following fields to these values:
      | Block title  | Courses list |
      | Region       | content      |
    And I press "Save changes"
    And I open the "Courses list" block preference
    Then I click on "Fields" "link"
    And I click on "Select all" "button"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    # Course image check on the dash data source fields
    And I log in as "student1"
    And I follow "Dashboard"
    And "//table[contains(@class,'dash-table')]//tbody//img[contains(@src,'/course/overviewfiles/course1.jpg')]" "xpath_element" should exist
    And "//table[contains(@class,'dash-table')]//tbody//img[contains(@src,'/course/overviewfiles/course2.jpg')]" "xpath_element" should exist
    And "//table[contains(@class,'dash-table')]//tbody//img[contains(@src,'/course/overviewfiles/course3.jpg')]" "xpath_element" should exist

  Scenario: Check user image url field attribute in custom data source
    Given the following "dashaddon_developer > custom data sources" exist:
      | name       | idnumber         | maintable | fieldrepeats | selectfield                            | placeholderfields                      | fieldattribute                            | attributevalue          |
      | Users list | USERDATASOURCE01 | user      | 3            | ["mnt.firstname","mnt.id","mnt.email"] | ["mnt.id","mnt.firstname","mnt.email"] | [[null],["user_image_url","image"],[null]] | [[""],[null,null],[""]] |
    And I log in as "admin"
    # Upload user profile images
    And I navigate to "Users > Accounts > Browse list of users" in site administration
    And I click on "Student First" "link"
    And I click on "Edit profile" "link"
    And I expand all fieldsets
    And I upload "local/dash/addon/developer/tests/fixtures/student1.jpg" file to "New picture" filemanager
    And I press "Update profile"
    And I navigate to "Users > Accounts > Browse list of users" in site administration
    And I click on "Student Two" "link"
    And I click on "Edit profile" "link"
    And I expand all fieldsets
    And I upload "local/dash/addon/developer/tests/fixtures/student2.jpg" file to "New picture" filemanager
    And I press "Update profile"
    And I navigate to "Users > Accounts > Browse list of users" in site administration
    And I click on "Teacher First" "link"
    And I click on "Edit profile" "link"
    And I expand all fieldsets
    And I upload "local/dash/addon/developer/tests/fixtures/teacher1.jpg" file to "New picture" filemanager
    And I press "Update profile"
    # Check custom data source in dash
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Users list" "radio"
    And I configure the "New Dash" block
    And I set the following fields to these values:
      | Block title  | Users list |
      | Region       | content    |
    And I press "Save changes"
    And I open the "Users list" block preference
    Then I click on "Fields" "link"
    And I click on "Select all" "button"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    # User image check on the dash data source fields
    And I log in as "student1"
    And I follow "Dashboard"
    And "//table[contains(@class,'dash-table')]//tbody//img[contains(@src,'/user/icon/')]" "xpath_element" should exist

  Scenario: Check logged in user placeholder condition in custom data source
    Given the following "dashaddon_developer > custom data sources" exist:
      | name             | idnumber | maintable             | enablejoins | joinrepeats | tablejoins | tablejoinsalias | tablejoinon         | placeholderfields                                                        | fieldrepeats | selectfield                                                                          | fieldattribute                        | attributevalue                                              | customcondition          | enableconditions | conditionrepeats | conditionfield       | operator | operatorcondition | conditionvalue         |
      | Logged User Logs | LUL01    | logstore_standard_log | 1           | 1           | ["user"]   | ["u"]           | ["u.id=mnt.userid"] | ["mnt.eventname","mnt.userid","user.id","user.firstname","mnt.courseid"] | 5            | ["mnt.eventname","mnt.component","mnt.contextlevel","mnt.courseid","user.firstname"] | [[null],[null],[null],["link"],[null]] | [[""],[""],[""],["/course/view.php?id={mnt.courseid}"],[""]] | mnt.userid = [LOGINUSER] | 1                | 1                | ["user.firstname"]   | ["="]    | ["AND"]           | ["[LOGINUSER:firstname]"] |
    # Generate log entries for different users
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I log out
    # Set up dash block on default dashboard
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Logged User Logs" "radio"
    And I configure the "New Dash" block
    And I set the following fields to these values:
      | Block title  | Logged User Logs |
      | Region       | content          |
    And I press "Save changes"
    And I open the "Logged User Logs" block preference
    Then I click on "Fields" "link"
    And I click on "Select all" "button"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I log out
    # Student1 should only see their own log entries
    And I log in as "student1"
    And I follow "Dashboard"
    Then I should see "Student" in the "Logged User Logs" "block"
    And I should not see "Teacher" in the "Logged User Logs" "block"
