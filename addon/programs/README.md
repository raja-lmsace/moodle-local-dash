# Dash
# Enrol Programs - Addon

## Overview

The "Programs" addon functions as a data source, displaying program details and presenting them in a user-friendly table format. By utilizing this catalog of programs, users can effortlessly discover and enroll in their preferred courses, thereby improving the organization and accessibility of learning content within Moodle.

## Installation
You can install the Dash plugin using the Moodle plugin installer. Here are the steps to follow:

1. Download the "<a href="https://bdecent.de/product/dash-pro/">Dash</a>" plugin from the bdecent website.
2. Log in to your Moodle site as an administrator.
3. Go to "Site administration > Plugins > Install plugins".
4. Upload the downloaded plugin ZIP file.
5. Follow the prompts to install the plugin.
6. Once the programs local plugin is installed, you can manage it by going to Site Administration > Programs > Program management. From there, you can set up the programs.

Alternatively, you can also install the Dash plugin manually. Here are the steps to follow:

1. Download the "<a href="https://bdecent.de/product/dash-pro/">Dash</a>" plugin from the  bdecent website.
2. Unzip the downloaded file.
3. Upload the Dash folder to the Moodle/local directory on your Moodle server.
4. Log in to your Moodle site as an administrator.
5. Go to "Site administration > Notifications".
6. Follow the prompts to install the plugin.
7. Once the programs local plugin is installed, you can manage it by going to Site Administration > Programs > Program management. From there, you can set up the programs.

# Configuration:

## Programs - Configuration:

Manage programs to create a new program and edit existing programs.

After creating the program, you can use the programs in the Dash "Enrol programs" data source block <a href="https://github.com/open-lms-open-source/moodle-enrol_programs/blob/MOODLE_401_STABLE/docs/en/management.md">Program Management Overview</a>.

![Program-management-active](https://github.com/bdecentgmbh/moodle-local_dash/assets/44221518/46944f73-65a6-4d7d-a2b7-3e6050cc63a6)


## Dash - Programs:

A new data source called "Programs" has been implemented within the Dash plugin. To add the "Programs" data source to the Dash block, enable "Edit mode" on the page and click the "Add a block" link either in the Main page content or in the collapsible drawer on the right side. Then, select the "Dash" block from the "Add a block" dialogue box. Next, choose "Enrol Programs" from the "Dynamic content with data source" options.

Once added, a "New Dash" block will appear. Access the "Preferences" option on the Dash block by clicking the cog icon. In the "Edit Preferences" dialogue that appears, navigate to the "Fields" tab and enable the necessary lists to display the data content on the Dash block table.

![Dash-enrol-program](https://github.com/bdecentgmbh/moodle-local_dash/assets/44221518/a738ba50-2f0f-463e-8f13-f0dc94657204)


### Fields
1. <b>Full name</b>: This field represents the complete name of the program.
2. <b>Full Name (Linked)</b>: This field provides the full name of the program but hyperlinks it to its respective page. So, if a user clicks on the program's name, it will take them directly to the program catalogue page.
3. <b>Program Image</b>: It denotes the image associated with the program. This image serves as a visual representation of the program. If no specific image is assigned, a default image will be displayed.
4. <b>Program Image (Linked)</b>: Similar to the previous field, it represents the program's image but hyperlinks it to its page. Clicking on the image would lead the user to the program catalogue page.
5. <b>ID Number</b>: This field is a unique identifier assigned to the program. It helps in distinguishing one program from another, where multiple programs exist.
6. <b>Context</b>: Context refers to the category context to which the program belongs. For instance, if the program falls under the category, it would be specified here.
7. <b>Context (Linked)</b>: This field links the context/category of the program to its respective category page. Clicking on this linked context would provide more information about the category to which the program belongs.
8. <b>Tags</b>: Tags are keywords or labels associated with the program. They help in organizing and categorizing programs based on common attributes.
9.  <b>Course Groups</b>: Indicates whether the program includes course groups. Course groups are subsets or clusters of courses within a program, often used for organizing content or facilitating group activities.
10. <b>Description</b>: This field provides a brief description or overview of the program. It offers insight into what the program's content, its objectives, and what learners can expect to gain from it.
11. <b>Archived</b>: This field indicates the status of the program, whether it is currently active or archived. Archived programs may no longer be offered or accessible to learners but are retained for historical purposes or potential future use.
12. <b>Content</b>: This field refers to the table of contents displayed on the program page under the "Content" heading. It provides an overview of the topics, modules, or lessons included in the program, allowing learners to understand the structure and content covered.

    <b>Program Image - General setting</b>

    The Default image will be available from the **Site administration > Plugin > Local plugins > Dash > General Setting**. In Dash General settings, upload the image on the "Enrol Program image"

![Dash-general-setting-image](https://github.com/bdecentgmbh/moodle-local_dash/assets/44221518/08d8b913-d4ae-4f51-a33d-acf6bb2e91ac)


### Visibility settings

13. <b>Public</b>: This setting determines whether the program is accessible to all users (public) or to the specific users. If set to "Yes," the program is available to all users. If set to "No," access is limited to certain groups.
14. <b>Visible to Cohorts</b>: This field lists the cohorts (groups of users) with visibility to the program. It specifies which groups of users can view and access the program. Only users who belong to at least one of the specified cohort(s) can see and access the program. If a user doesn't belong to any of the specified cohorts, they won't see the program listed or be able to access it.

### Allocation Settings:

15. <b>Allocations</b>: "Allocations" refer to the process of assigning or enrolling users into a program. This section deals with the settings related to the allocation process.
16. <b>Allocation Start</b>: This setting specifies the date when the allocation process for the program begins. It marks the starting point from which users can enrol or be assigned to the program.
17. <b>Allocation End</b>: Conversely, the allocation end date signifies the deadline or endpoint for the allocation process. After this date, users cannot enrol or be assigned to the program. It limits the enrollment period, ensuring that the program's composition remains consistent and manageable for administrators.
18. <b>Scheduling</b>: "Scheduling" refers to the configuration of specific dates that define the timeline for the program. This includes setting the start date, due dates, and end date for the program. Each of these dates plays a crucial role in guiding learners through the program and ensuring timely completion of tasks and assessments.
19. <b>Scheduling Start Date</b>: The start date marks the point at which the program becomes accessible to learners. It indicates when learners can begin accessing the program's content, participating in activities, and interacting with course materials.
20. <b>Scheduling Due Date</b>: The Due date represents deadlines for completing specific tasks, assignments, or assessments within the program. These dates serve as milestones for learners, indicating when certain activities or assessments must be completed
21. <b>Scheduling End Date</b>: The End date, learners are expected to have completed all program requirements, including assignments and assessments. The end date helps enforce program completion deadlines and ensures that learners have sufficient time to fulfil all program requirements.
22. <b>Allocation Sources:</b>: Allocation sources refer to the various methods through which users are assigned or enrolled in the program. This section lists the active allocation sources available for administrators to utilize when enrolling users.
23. <b>Self-allocation:</b>:  Users have the ability to enrol themselves in the program without requiring approval from an administrator.
24. <b>Requests with approval:</b>: Users can request access to the program, and their enrollment must be approved by an administrator before they can join.
25. <b>Access program catalogue:</b>: The smart program button dynamically adjusts its appearance based on the allocation possibilities configured for the program. It analyzes factors such as the availability of self-allocation, the requirement for a sign-up key, or the need for administrator approval. Based on these factors, the smart program button displays the most appropriate action for users to take:

    **Sign up**:- The signup button is visible and allows the user to self-enroll without requiring a key.

    **Sign up (key required)**:- The signup button is visible, indicating that a key is required for enrollment.

    **Request access**:- The Request access button is visible, indicating that enrollment requires approval from an administrator.

    **Not available**:- The text "Not available" is visible, indicating that enrollment is not currently available.

26. <b>Requests with approval:</b>: Users can request access to the program, and their enrollment must be approved by an administrator before they can join.

![Programs-field-preferences](https://github.com/bdecentgmbh/moodle-local_dash/assets/44221518/07dbcde7-7d42-4701-ac56-75dd57d86397)


## Dash - Filters:

To access the "Preferences" option on the Dash block, click the cog icon. In the "Edit Preferences" window, go to the "Tags" tab and enable the Tags option under "Enable filters" for the Dash block table.

Once enabled, an auto-complete input box will appear above the Dash table content. You can use this box to search for or select tags associated with the program. The selected tags will then be listed in the program table content.

![Programs-filter-tags-preferences](https://github.com/bdecentgmbh/moodle-local_dash/assets/44221518/e2614ce8-33a2-400b-9f5d-4cbbc2c1a61e)


## Program Listings in Dash Block:

The program listings are displayed on the Dash block as a table, making it easy for you to see and understand the available programs.

![Dash-program-lists-table](https://github.com/bdecentgmbh/moodle-local_dash/assets/44221518/88d677bc-233e-4beb-8df1-d49288e9caf0)

