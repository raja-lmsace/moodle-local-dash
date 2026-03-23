@local @local_dash @dashaddon @dashaddon_role_assignment @javascript

Feature: Add role assignment datasource in dash block
  In order to enable the role assignment datasource in dash block on the dashboard
  As an admin

  Background:
    Given the following "categories" exist:
      | name       | category | idnumber |
      | Category 1 | 0        | CAT1     |
      | Category 2 | 0        | CAT2     |
      | Category 3 | CAT2     | CAT3     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | numsections | startdate      | enddate         |
      | Course 1 | C1        | 0        | 1                | 3           |                |                 |
      | Course 2 | C2        | CAT1     | 0                | 2           |                |                 |
      | Course 3 | C3        | CAT2     | 1                | 1           |                |                 |
      | Course 4 | C4        | CAT3     | 1                | 2           | ##1 year ago## | ##1 month ago## |
    And the following "activities" exist:
      | activity | name      | course | idnumber | intro             | section | completion |
      | page     | testpage1 | C1     | page1    | Page description  | 1       | 1          |
      | page     | testpage2 | C1     | page1    | Page description  | 2       | 1          |
      | page     | testpage3 | C1     | page1    | Page description  | 3       | 1          |
      | choice   | My quiz 	 | C3     | choice   | Welcome to Quiz   | 1       | 1          |
      | choice   | Quiz 1  	 | C3     | choice01 | Welcome to Quiz   | 2       | 1          |
      | choice   | Quiz 2  	 | C1     | choice02 | Welcome to Quiz 2 | 5       | 1          |
      | book     | Book 1  	 | C1     | book     | Welcome to Book   | 5       | 1          |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
	  	| teacher2 | Teacher   | Second   | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student1 | C2     | student |
      | student1 | C3     | student |
      | student1 | C4     | student |
      | student2 | C2     | student |
      | student2 | C3     | student |
      | student3 | C4     | student |
      | admin    | C1     | student |
      | admin    | C2     | student |
      | admin    | C3     | student |
      | admin    | C4     | student |
      | teacher1 | C1     | teacher |
			| teacher2 | C1     | teacher |
    And I log in as "admin"
		#---Enable course section---#
    And I navigate to "Plugins > Local plugins > Manage addons" in site administration
    And I click on ".action-icon" "css_element" in the "Data source: Role Assignments" "table_row"
    #---Enable roles---#
    # And I navigate to "Users > Permissions > Define roles" in site administration
    # And I click on "Add a new role" "button"
    # And I click on "Continue" "button"
    #---Create new system context role---#
    And the following "role" exists:
      | shortname 			 					 | adminrole 			 |
      | name             					 | system admin role |
      | context_system             | 1      					 |
      | moodle/user:viewdetails    | allow						 |
			| moodle/user:viewalldetails | allow						 |
    # And I click on "Create this role" "button"
    # And I click on "List all roles" "button"
    # And I click on "Add a new role" "button"
    # And I click on "Continue" "button"
    #---Create new category context role---#
    And the following "role" exists:
      | shortname 			 					 | categoryrole |
      | name             					 | Category role |
      | context_coursecat          | 1       			 |
      | moodle/user:viewdetails    | allow  			 |
			| moodle/user:viewalldetails | allow  			 |
    # And I click on "Create this role" "button"
    # And I click on "List all roles" "button"
    # And I click on "Add a new role" "button"
    # And I click on "Continue" "button"
    #---Create new user context role---#
    And the following "role" exists:
      | shortname 			 					 | userrole |
      | name             					 | User role |
      | context_user               | 1      	 |
      | moodle/user:viewdetails    | allow		 |
			| moodle/user:viewalldetails | allow		 |
    # And I click on "Create this role" "button"
		# #---Create new course context role---#
    # And I click on "List all roles" "button"
    # And I click on "Add a new role" "button"
    # And I click on "Continue" "button"
    And the following "role" exists:
      | shortname 			 					 | courserole |
      | name 					             | Course role |
      | context_course             | 1      		 |
      | moodle/user:viewdetails    | allow			 |
			| moodle/user:viewalldetails | allow			 |
    # And I click on "Create this role" "button"

		#---Assign system context role---#
    And I navigate to "Users > Permissions > Assign system roles" in site administration
    And I follow "system admin role"
    And I set the field "addselect" to "Admin User (moodle@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"

		#---Assign category context role---#
    And I go to the courses management page
    And I click on "permissions" action for "Category 1" in management category listing
    And I set the field "Participants tertiary navigation" to "Assign roles"
    And I follow "Category role"
    And I set the field "addselect" to "Teacher First (teacher1@example.com)"
    And I set the field "addselect" to "Student First (student1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"

    #---Assign course context role---#
    And I am on the "Course 1" "enrolled users" page
    And I click on ".quickediticon .icon" "css_element" in the "teacher1@example.com" "table_row"
    And I open the autocomplete suggestions list
    And I click on "Course role" item in the autocomplete list
    And I click on ".inplaceeditable .icon" "css_element" in the "teacher1@example.com" "table_row"

    And I click on ".quickediticon .icon" "css_element" in the "student1@example.com" "table_row"
    And I open the autocomplete suggestions list
    And I click on "Course role" item in the autocomplete list
    And I click on ".inplaceeditable .icon" "css_element" in the "student1@example.com" "table_row"

    #---Assign user context role---#
    And I am on the "student2" "user > profile" page
    And I click on "Preferences" "link" in the ".profile_tree" "css_element"
    And I follow "Assign roles relative to this user"
    And I follow "User role"
    And I set the field "addselect" to "Teacher First (teacher1@example.com)"
    And I click on "Add" "button" in the "#page-content" "css_element"

    #---Dashboard adding user block---#
    And the following "block_dash > dash blocks default" exist:
      | type       | name              | title            | perpage  | fields                                                                                                                         | filters                                  |
      | datasource | roleassignments   | Role Assignments | 20       |Role name, Short name, Original name, r_description,ra_timemodified,ctx_contextname,ctx_contexturl,ctx_contextlevel,ctx_parent | user_id,context_level,context_id,role_id |
    # And I follow "Dashboard"
    # And I turn dash block editing mode on
    # And I create dash "Role Assignments" datasource
    # And I configure the "New Dash" block
    # And I set the following fields to these values:
    #   | Block title  | Role Assignments |
    #   | Region       | content 					|
    # And I press "Save changes"
    # And I wait until the page is ready
    # And I wait until the page is ready
    # And I open the "Role Assignments" block preference
    # #---Fields---#
    # And I click on "Fields" "link"
    # And I wait "5" seconds
    # And I set the following fields to these values:
    #   | Role: Role name                 | 1 |
    #   | Role: Short name                | 1 |
    #   | Role: Original name             | 1 |
    #   | Role: Description               | 1 |
    #   | Role assignment: Time modified  | 1 |
    #   | Context: Context name           | 1 |
    #   | Context: Context URL            | 1 |
    #   | Context: Context level          | 1 |
    #   | Context: Parent                 | 1 |
    # #---Filters---#
    # And I click on "Filters" "link"
    # And I set the following fields to these values:
    # | config_preferences[filters][user_id][enabled] | 1 |
    # | config_preferences[filters][context_level][enabled] | 1 |
    # | config_preferences[filters][context_id][enabled] | 1 |
    # | config_preferences[filters][role_id][enabled] | 1 |
    # And I press "Save changes"
    # And I press "Reset Dashboard for all users"
    # And I log out

  Scenario: role assignments fields
    And I log in as "admin"
    #---All fields title---#
    # And I should see "Role name" in the ".dash-table thead tr:nth-child(1) th:nth-child(1)" "css_element"
    # And I should see "Short name" in the ".dash-table thead tr:nth-child(1) th:nth-child(2)" "css_element"
    # And I should see "Context name" in the ".dash-table thead tr:nth-child(1) th:nth-child(3)" "css_element"
    # And I should see "Context URL" in the ".dash-table thead tr:nth-child(1) th:nth-child(4)" "css_element"
    # And I should see "Context level" in the ".dash-table thead tr:nth-child(1) th:nth-child(5)" "css_element"
    # And I should see "Original name" in the ".dash-table thead tr:nth-child(1) th:nth-child(6)" "css_element"
    # And I should see "Description" in the ".dash-table thead tr:nth-child(1) th:nth-child(7)" "css_element"
    # And I should see "Time modified" in the ".dash-table thead tr:nth-child(1) th:nth-child(8)" "css_element"
    # And I should see "Parent" in the ".dash-table thead tr:nth-child(1) th:nth-child(9)" "css_element"
    And the following should exist in the "dash-table" table:
      | Role name     | Short name    | Context name  | Context URL   | Context level | Original name | Description   | Time modified | Parent        |

    And the following should exist in the "dash-table" table:
      | Role name           | Short name | Context name     | Context level | Original name       | Time modified       | Description                                                                                 |
      | Non-editing teacher | teacher    | Course: Course 1 | Course        | Non-editing teacher | ##today##%d/%m/%y## | Non-editing teachers can teach in courses and grade students, but may not alter activities. |
    #---Check user details in fields---#
    # And I should see "Non-editing teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Non-editing teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "" in the ".dash-table tbody tr:nth-child(1) td:nth-child(7)" "css_element"
    # And I should see "" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    #---check for conditions:student---#
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable system context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][rolecondition][enabled]" to "1"
    And I set the field "config_preferences[filters][rolecondition][roleids][]" to "student"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    #---Enable conditions---#
    And the following should exist in the "dash-table" table:
      | Role name           | Short name | Context name     | Context level | Original name       | Time modified       | Description                                               |
      | Student             | student    | Course: Course 1 | Course        | Student             | ##today##%d/%m/%y## | Students generally have fewer privileges within a course. |
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "Students generally have fewer privileges within a course." in the ".dash-table tbody tr:nth-child(1) td:nth-child(7)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"

  Scenario: role assignments filters
    And I log in as "admin"
    #---All user filter---#
    And I set the field "user_id" to "Teacher First"
    And the following should exist in the "dash-table" table:
      | Role name           | Short name | Context name     | Context level | Original name       | Time modified       |
      | Non-editing teacher | teacher    | Course: Course 1 | Course        | Non-editing teacher | ##today##%d/%m/%y## |
    # And I should see "Non-editing teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Non-editing teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    And I click on ".select2-selection__choice__remove" "css_element"
    #---All context level filter---#
    Then I set the field "context_level" to "System"
    And the following should exist in the "dash-table" table:
      | Role name          | Short name | Context name | Context level | Original name       | Time modified       |
      | system admin role  | adminrole  | System       | System        | system admin role   | ##today##%d/%m/%y## |

    # And I should see "system admin role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "adminrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "System" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "System" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "system admin role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    And I click on ".select2-selection__choice__remove" "css_element"
    #---All context name filter---#
    Then I set the field "context_id" to "Course: Course 4"
    And the following should exist in the "dash-table" table:
      | Role name | Short name | Context name     | Context level | Original name | Time modified       | Description                                               |
      | Student   | student    | Course: Course 4 | Course        | Student       | ##today##%d/%m/%y## | Students generally have fewer privileges within a course. |
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 4" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "Students generally have fewer privileges within a course." in the ".dash-table tbody tr:nth-child(1) td:nth-child(7)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    And I click on ".select2-selection__choice__remove" "css_element"
    #---All role name filter---#
    Then I set the field "role_id" to "Category role"
    And the following should exist in the "dash-table" table:
      | Role name     | Short name   | Context name         | Context level   | Original name | Time modified       |
      | Category role | categoryrole | Category: Category 1 | Course Category | Category role | ##today##%d/%m/%y## |
    # And I should see "Category role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "categoryrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Category: Category 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course Category" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Category role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"

  Scenario: role assignments role conditions
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
  	#---Enable system context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][rolecondition][enabled]" to "1"
    And I set the field "config_preferences[filters][rolecondition][roleids][]" to "adminrole"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name          | Short name | Context name | Context level | Original name       | Time modified       |
      | system admin role  | adminrole  | System       | System        | system admin role   | ##today##%d/%m/%y## |
    # And I should see "system admin role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "adminrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "System" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "System" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "system admin role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
		#---category context---#
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
  	#---Enable system context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][rolecondition][enabled]" to "1"
    And I set the field "config_preferences[filters][rolecondition][roleids][]" to "categoryrole"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name     | Short name   | Context name         | Context level   | Original name | Time modified       |
      | Category role | categoryrole | Category: Category 1 | Course Category | Category role | ##today##%d/%m/%y## |
    # And I should see "Category role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "categoryrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Category: Category 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course Category" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Category role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
		#---course context---#
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
  	#---Enable system context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][rolecondition][enabled]" to "1"
    And I set the field "config_preferences[filters][rolecondition][roleids][]" to "courserole"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name   | Short name | Context name     | Context level | Original name | Time modified       |
      | Course role | courserole | Course: Course 1 | Course        | Course role   | ##today##%d/%m/%y## |
    # And I should see "Course role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "courserole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Course role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
		#---user context---#
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
  	#---Enable system context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][rolecondition][enabled]" to "1"
    And I set the field "config_preferences[filters][rolecondition][roleids][]" to "userrole"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name  | Short name | Context name       | Context level | Original name | Time modified       | Parent          |
      | User role  | userrole   | User: Student Two  | User          | User role     | ##today##%d/%m/%y## | Teacher First   |
    # And I should see "User role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "userrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "User: Student Two" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "User" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "User role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    # And I should see "Teacher First" in the ".dash-table tbody tr:nth-child(1) td:nth-child(9)" "css_element"

  Scenario: role assignments context level conditions
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable system context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][context_level_condition][enabled]" to "1"
    And I set the field "config_preferences[filters][context_level_condition][contextlevels][]" to "System"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name          | Short name | Context name | Context level | Original name       | Time modified       |
      | system admin role  | adminrole  | System       | System        | system admin role   | ##today##%d/%m/%y## |
    # And I should see "system admin role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "adminrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "System" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "System" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "system admin role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    #---user context---#
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable user context role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][context_level_condition][enabled]" to "1"
    And I set the field "config_preferences[filters][context_level_condition][contextlevels][]" to "User"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name  | Short name | Context name       | Context level | Original name | Time modified       | Parent          |
      | User role  | userrole   | User: Student Two  | User          | User role     | ##today##%d/%m/%y## | Teacher First   |
    # And I should see "User role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "userrole" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "User: Student Two" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "User" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "User role" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"
    # And I should see "Teacher First" in the ".dash-table tbody tr:nth-child(1) td:nth-child(9)" "css_element"

  Scenario: role assignments course category conditions
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable course category role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course_categories_condition][enabled]" to "1"
    And I set the field "config_preferences[filters][c_course_categories_condition][includesubcategories]" to "1"
    And I set the field "config_preferences[filters][c_course_categories_condition][coursecategories][]" to "Category 1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name | Short name | Context name     | Context level | Original name | Time modified       | Description                                               |
      | Student   | student    | Course: Course 1 | Course        | Student       | ##today##%d/%m/%y## | Students generally have fewer privileges within a course. |
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(3) td:nth-child(1)" "css_element"
    # And I should see "student" in the ".dash-table tbody tr:nth-child(3) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(3) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(3) td:nth-child(5)" "css_element"
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(3) td:nth-child(6)" "css_element"
    # And I should see "Students generally have fewer privileges within a course." in the ".dash-table tbody tr:nth-child(3) td:nth-child(7)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(3) td:nth-child(8)" "css_element"

  Scenario: role assignments course conditions
    And I log in as "admin"
    And I am on the "block_dash > Default Dashboard" page
    And I turn dash block editing mode on
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable course role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][c_course][enabled]" to "1"
    And I set the field "config_preferences[filters][c_course][courseids][]" to "C1"
    And I press "Save changes"
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And the following should exist in the "dash-table" table:
      | Role name           | Short name | Context name     | Context level | Original name       | Time modified       | Description                                                                                 |
      | Non-editing teacher | teacher    | Course: Course 1 | Course        | Non-editing teacher | ##today##%d/%m/%y## | Non-editing teachers can teach in courses and grade students, but may not alter activities. |
    # And I should see "Non-editing teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    # And I should see "Non-editing teacher" in the ".dash-table tbody tr:nth-child(1) td:nth-child(6)" "css_element"
    # And I should see "Non-editing teachers can teach in courses and grade students, but may not alter activities." in the ".dash-table tbody tr:nth-child(1) td:nth-child(7)" "css_element"
    # And I should see "##today##%d/%m/%y##" in the ".dash-table tbody tr:nth-child(1) td:nth-child(8)" "css_element"

  Scenario: role assignments current course conditions
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Role Assignments" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Role Assignments"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable current course role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_course][enabled]" to "1"
    And I press "Save changes"
    And the following should exist in the "dash-table" table:
      | Role name | Short name | Context name     | Context level |
      | Student   | student    | Course: Course 1 | Course        |
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Course: Course 2" in the "Student" "table_row"
    # And I should not see "Course: Course 2" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"

  Scenario: role assignments current category conditions
    And I log in as "admin"
    And I am on course index
    And I follow "Category 1"
    And I turn dash block editing mode on
    And I add the "Dash" block
    And I click on "Role Assignments" "radio"
    And I configure the "New Dash" block
    And I set the field "Block title" to "Role Assignments"
    And I press "Save changes"
    And I wait until the page is ready
    And I open the "Role Assignments" block preference
    #---Enable current category role---#
    And I click on "Conditions" "link"
    And I set the field "config_preferences[filters][current_category][enabled]" to "1"
    And I press "Save changes"
    And the following should exist in the "dash-table" table:
      | Role name | Short name | Context name     | Context level |
      | Student   | student    | Course: Course 1 | Course        |
    # And I should see "Student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(1)" "css_element"
    # And I should see "student" in the ".dash-table tbody tr:nth-child(1) td:nth-child(2)" "css_element"
    # And I should see "Course: Course 1" in the ".dash-table tbody tr:nth-child(1) td:nth-child(3)" "css_element"
    # And I should see "Course" in the ".dash-table tbody tr:nth-child(1) td:nth-child(5)" "css_element"
    And I log out
    #---Student login---#
    And I log in as "student1"
    And I am on course index
    And I follow "Category 1"
    And the following should exist in the "dash-table" table:
      | Role name | Context name |
      | Student   | Course: Course 1 |
    # And I should see "Course: Course 1" in the "Student" "table_row"
    And I should not see "Course: Course 2" in the "Student" "table_row"
