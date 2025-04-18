## Addon Overview

The Developer addon is a powerful tool within the local Dash Moodle plugin ecosystem, catering specifically to developers seeking to extend the functionality of Moodle's Block Dash feature. With the Developer addon, developers can create custom data sources and design unique custom layouts, offering unparalleled flexibility and customization options.

## Key Features:

  1. **Custom Data Sources**: Developers can harness the power of SQL queries to create bespoke data sources, granting precise access to Moodle's expansive database.
  2. **Custom Layouts**: Leveraging Mustache templates, developers can craft dynamic layouts tailored to specific requirements, enhancing the presentation of data within Moodle.
  3. **Integration with Block Dash**: Custom data sources and layouts seamlessly integrate with Moodle's Block Dash feature, enriching the user experience with personalized content.

## Installation Dependencies:

  Before using the Developer widget, ensure that the local Dash Moodle plugin is installed and activated within your Moodle environment. This plugin serves as the foundation for integrating the Developer widget's functionality into your Moodle instance.

## Developer Datasource Plugin Interface:

  To access the configuration of the Developer addon for creating data sources, follow these steps:

  * Navigate to Site administration / Plugins / Local Plugins / Manage data sources.

  Here, you'll find a list of datasources created by the Developer addon. You can update existing data sources by clicking the edit button next to the respective data source.

  * To create a new custom data source, click on the "Create custom data source" button.

### General Section:

  In the initial step of data source creation, you'll find the following configurations in the General section:

  1. **Data Source Name**: Specify a name for the data source, which will be displayed in the dash block datasource list.
  2. **ID Number**: Assign a unique identifier to the data source for identification purposes.
  3. **Select Main Table**: Choose a table from the list of available tables. This table will serve as the primary data source for the addon. The Developer addon will use the keyword "(mnt)" as an alias for the main table, facilitating the setup of joins and where conditions.

After setting up the main table, ensure to click the "**Generate fields**" button. This action populates fields, joins, and where conditions for further configuration.

Note: If you change the main table, remember to regenerate the fields by clicking the "**Generate fields**" button.


### Join Tables Section:

In the Join Tables section, you can establish relationships between multiple tables to enrich your data source.
Here's a detailed explanation of the configuration options:

  1. **Enable Joins**: Toggle this checkbox to activate or deactivate the setup of joins. Enabling joins allows you to define relationships between tables while disabling it ensures no effect on configured joins.

  2. **Select Join Table**: Choose the table you wish to join with the main table to create a comprehensive data source.

  3. **Table Alias**: Assign a unique alias keyword for the selected table. For instance, you can use "c" for the Course table or "ue" for the user_enrolments table. Simply specify the keyword (e.g., "c") without mentioning the table name (e.g., "Course AS c").

  4. **Enter Condition Query to Join**: This field facilitates the specification of conditions to join the selected table with other tables. Utilize the table alias keywords within the condition query for accurate linkage.

  5. **Delete Icon**: Click this icon to remove the configured join, providing flexibility in managing table relationships.

  6. **Add Additional Join Table**: Use the "Add 1 join table to form" button to incorporate another join table into the configuration. Each click generates a new group of join-related configuration options, enabling the addition of multiple join tables as needed.

By effectively utilizing these options, developers can establish intricate relationships between tables, enriching the data source with diverse datasets to meet specific project requirements.

### Fields Setup Section:

In the Fields Setup section, you can configure the select fields for your data source, ensuring the inclusion of relevant information. Here's a detailed breakdown of the available options:

  1. **Placeholder Fields**: This select box displays fields from the selected main table (mnt) and the list of selected join tables. Fields chosen here act as placeholders, facilitating their use in transformed field values (explained below).

#### Field Configuration:

Each group of configurations corresponds to a select field. Within each group, you can set the following:

  1. **Field**: Choose a field from the selected main table and other join tables. The selected field will be listed in the data source preference field sections, as documented in the Block Dash documentation.

  2. **Transform Format**: This option allows you to transform the format of the displayed data. The "Transform Format" option empowers developers to modify the appearance of data displayed in the layout to suit specific formatting requirements. This feature is particularly useful for tasks such as converting timestamps into user-readable date time formats or adding links with field values.

  * Available transformations include:

    * **Bool**: Represent the field value as true or false. Useful for displaying Moodle's enable/disable values, often stored as 0 and 1.

    * **Date**: Convert Unix timestamp values into user-readable date formats.

    * **Image URL**: Display the field value as an image URL.

    * **Link**: Instead of displaying the value, present it as a link. Additional fields can be used to set up the link, utilizing placeholders.

    * **Moodle URL**: Display the field value as a Moodle URL.

    * **Percent**: Calculate and display the percentage from the field values. Additional data fields can set the base value for percentage calculation.

    * **Time**: Present Unix timestamp fields as user-readable time.

    * **Timeago**: Display the time difference between the field Unix timestamp and the current time.


  3. **Additional format value**: The "Additional Format Value" option provides developers with the capability to include custom static content within the field transform format.
  > For example: 1. For the link attribute, enter the link and use the field values as parameters, 2. For the percent attribute, Use the out of value. For the grades use 10 otherwise uses 100

  * **Delete Button**: Click the delete icon button to remove the field from the selection. This action removes the field from the preference field section.
  * **Adding Additional Fields**: To include more fields in the data source, utilize the "Add 3 field(s) to form" button. This allows for the addition of multiple fields, ensuring comprehensive data coverage within the data source.

By leveraging these options effectively, developers can tailor the selection and presentation of fields within their data source, ensuring that the displayed information meets specific formatting requirements and user preferences.

### Conditions Setup:

The Conditions Setup section allows developers to define conditions for the data source, enabling the extraction of specific data subsets based on predefined criteria. Here's a detailed explanation of the configuration options:

  * **Custom Condition Query**: Developers can specify raw query conditions for the data source, utilizing alias keywords for accurate referencing within the query. This feature provides advanced flexibility for tailoring data extraction criteria according to specific requirements.

  * **Enable Conditions**: This option enables or disables the conditions configured using the provided query. Disabling conditions allows developers to temporarily exclude them from affecting data retrieval while retaining the custom condition query for future use.

  #### Condition Groups:

  1. **Field Selection**: Choose a field from the list of join tables and the main table to create a condition based on this field. This selection determines the attribute upon which the condition will be applied.

  2. **Operator**: Specify the operator to be used in the condition, such as "equal," "not equal," "between," "like," and more. The chosen operator dictates the relationship between the field value and the specified condition value.

  3. **Conjunctive Condition**: This operator combines multiple conditions within a condition group. Developers can choose from a variety of logical operators to connect individual conditions, enhancing the complexity and precision of condition definitions.

  4. **Value**: Define the value against which the field's value will be evaluated based on the specified operator. This value serves as the criterion for filtering data, ensuring that only records meeting the specified condition are included in the data source.

  * **Delete Button**: Clicking the delete button removes the condition from the configuration, allowing developers to refine or modify condition definitions as needed.


Once datasources are created using the Developer addon, they will be listed in the datasources list of the Block Dash configuration section. Setting up a datasource within the Dash block involves configuring the block to fetch data from the created datasource.

For specific instructions on setting up datasources within the Dash block and configuring its display, refer to the documentation provided with the Block Dash feature in your Moodle instance. This documentation should provide detailed guidance tailored to your Moodle version and configuration.

## Developer - Layouts Interface:

To access the configuration of the Developer addon for creating data layouts, follow these steps:

Navigate to Site administration / Pulse / Local Plugins / Manage layouts.
Here, you'll find a list of layouts created by the Developer addon. You can update the existing layouts by clicking the edit button next to the respective data source.

To create a new custom layout, click on the "Create custom layout"

### General sections:

In the initial step of custom layout creation, you will find the following configurations.

1. "**Name**": Specify a name for the layout, which will be displayed in the layouts option in the preference modal.
2. "**Supports field visibility**": Enable this option to allow users to control the visibility of fields in the layout.
3. "**Supports filtering**": Enable this option to enable filtering capabilities for the layout.
4. "**Supports pagination**": Enable this option to implement pagination for handling large datasets.
5. "**Supports sorting**": Enable this option to allow users to sort data based on specified fields.

6. "**Mustache template**":

The Mustache template is used to define the layout structure for displaying data within the Dash block. Please review the examples of layouts below.

Let's break down the Mustache explanation:

- **{{< block_dash/layout }} and {{/ block_dash/layout }}**: These tags denote the beginning and end of the layout definition. Everything within these tags defines the layout structure.

- **{{$body}} and {{/body}}**: These tags encapsulate the body of the layout. The layout body contains the HTML structure for displaying the data.

- **{{^data.rows}} and {{/data.rows}}**: These tags check if any rows of data exist. If there are no rows, it displays a message indicating that there are no results.

- **{{#data.first_child.data}} and {{/data.first_child.data}}**: These tags loop through the fields of the first child data collection (usually the main dataset). Within this loop, it defines the headers of the table by accessing the field labels and names.

- **{{#data.rows}} and {{/data.rows}}**: These tags loop through all rows of data (child data collections). Within this loop, it defines the rows of the table.

- **{{#data}} and {{/data}}**: Within the row loop, these tags loop through all fields in each row of data. Within this loop, it accesses the values of each field to display them in the table cells.

- **{{{get_value}}}**: This Mustache tag retrieves the value of the current field being accessed.

- **{{get_name}} and {{get_label}}**: These Mustache tags retrieve the name and label of the current field being accessed, typically used for table headers.

- **{{#str}} and {{/str}}**: This Mustache tag is used to access Moodle's language string translation functionality. It translates the "noresults" string to display a message when no data is available.

- **{{{data.first_child.c_fullname}}}**: This Mustache tag demonstrates accessing a specific field within the first child data collection. In this example, it retrieves the value of the "c_fullname" field, which could represent the full name of a course.

Overall, the Mustache template provides a flexible and dynamic way to define the layout structure and access data values for display within the Dash block. It allows for looping through datasets, accessing field values, and conditional rendering based on the presence of data.

> Once the layouts are created, these layouts are listed in the preference modal in the layout config under the general tab.

## Architecture of Developer Addon:

- The Developer addon operates similarly to built-in datasources like courses and users, with custom layouts functioning akin to standard layouts such as tables and timelines.

- Datasources in the Developer addon are essentially a combination of queries to retrieve data, with values subsequently passed through custom transformation methods.

- Typically, datasources contain query templates and multiple tables defining fields along with their attributes (transformations). Each datasource may consist of multiple tables; for instance, a course datasource might include both course and category fields.

- Layouts are defined within a layout class, a subclass of abstract_layout. The layout file defines a Mustache template for displaying data in a specific format.

- Custom datasources and layouts created within the Developer addon are stored in the database. These records are retrieved and registered as separate datasources and layouts in developer/lib.php using the methods **dashaddon_developer_register_data_sources** and **dashaddon_developer_register_layouts**.

- In the Developer addon, the **dashaddon_developer/data_source/persistent_data_source** class serves as the datasource for all custom datasources. When Block Dash attempts to construct a query for any custom datasource, it calls this **persistent_data_source** class.

- Within this persistent source class, configured custom datasource queries are structured into tables to function with the Dash query builder method. The fields configured for the custom datasource are generated within the **dashaddon_developer/data_source/persistent_data_table** class.

- Regarding custom layouts, the **dashaddon_developer/layout/persistent_layout** class dynamically configures the layout within the layout, updating the Mustache contents of the configured layout in the custom template file on cache for rendering.


## Limitations:

While the Developer addon provides powerful capabilities for creating custom data sources and layouts within the local Dash Moodle plugin, there are limitations to consider in general reporting aspects:

  **Filters**: The Developer addon lacks native filtering options. Users may need to rely on pre-defined SQL queries to filter data before presenting it in reports.

  **Datasource Conditions**: While the Developer addon supports custom SQL queries, it may not offer built-in features for defining complex conditions within the addon's interface. Users may need to manually construct SQL queries with conditions or implement custom logic externally.

  1. **Complex SQL Queries**: Although the addon simplifies query construction, complex queries may still require advanced SQL knowledge. Developers must be proficient in SQL to effectively utilize the addon's capabilities.

  2. **Complex Aggregations**: Performing intricate aggregations or calculations within the addon's interface can be challenging. Developers might need to preprocess data or perform aggregations externally before presenting them in reports.

  3. **Advanced Aggregation Functions**: While the addon allows querying data from Moodle's database, it may not support complex aggregation functions across multiple datasets. Advanced calculations and aggregations may necessitate custom SQL queries and post-processing of data.

  4. **Limited Transformation Options**: Despite offering various transformation options for data formatting, some advanced requirements may not be directly supported. Developers may need to implement custom transformation logic beyond the addon's scope.

  5. **Interactive Visualizations**: Creating highly interactive visualizations within the addon's interface may be limited. Advanced visualization capabilities might require integration with external reporting tools or libraries.

  6. **User Interface Constraints**: The addon's interface may have limitations in customization and flexibility, particularly for developers with specific design preferences or workflow requirements.

  7. **Data Security and Access Control**: Fine-grained data security mechanisms, such as row-level security or role-based access control, may not be directly supported. Developers might need to implement custom security measures or integrate with Moodle's native access control features.

  8. **Data Source Limitations**: The addon's ability to retrieve and process data is constrained by the underlying database schema and Moodle's data model. Complex data relationships or non-standard structures may pose challenges for data retrieval and reporting.

## Examples Datasource

Here are some of the basic example datasources config setup:

### 1. Active courses list.

Configurations the datasource that lists all active courses with visibility, start date in the past, and end date in the future or empty,

you can follow these steps:

1. **Data Source Name:** "Active Courses"
2. **ID Number**: active-courses
3. **Select Main Table**: **course**

Click on the "**Generate fields**" button to populate the fields from the main table.

Join Tables: No additional join tables are required for this datasource.

**Fields Setup**:

  *Placeholder Fields*: Include fields like course:id, course_categories:id

  **Field #1**:

    Field: **course:fullname**
    Transform the format: **Link**
    Additional format value: **course/view.php?id={course.id}**

  **Field #2**:

    Field: course_categories:name
    Transform the format: Link
    Additional format value: course/index.php?category={course_categories.id}

  **Field #3**:

    Field: course:startdate
    Transform the format: None
    Additional format value: -

  **Field #4**:

    Field: course:enddate
    Transform the format: None
    Additional format value: -

Conditions Setup:

  **Custom condition query**: mnt.startdate <= NOW() AND ( mnt.enddate = NULL OR mnt.enddate > NOW() )
  **Enable Conditions**: checked

  **Condition #1:**

    Field: course:visible
    Operator: "="
    Conjunctive Condition: AND
    Value: 1

Save Configuration: Save the datasource configuration.

### 2. Enrolled users who have never accessed a given course:

Configurations the datasource that lists enrolled users who have never accessed a given course,

you can follow these steps:

1. **Data Source Name:** "Enrolled users never accessed a course"
2. **ID Number**: notaccessed-enroledusers
3. **Select Main Table**: **user_enrolments**

Click on the "**Generate fields**" button to populate the fields from the main table.

Join Tables:

  **Joint table #1**:

    Select Join table: enrol
    Table alias: en
    Enter condition query to join: mnt.enrolid = en.id

  **Joint table #2**:

    Select Join table: user
    Table alias: uu
    Enter condition query to join: uu.id = mnt.userid


Fields Setup:

  **Field #1**:

    Field: **user:id**
    Transform the format: None
    Additional format value: -

  **Field #2**:

    Field: user:firstname
    Transform the format: None
    Additional format value: -

  **Field #3**:

    Field: user:lastname
    Transform the format: None
    Additional format value: -

  **Field #4**:

    Field: user:email
    Transform the format: None
    Additional format value: -

Conditions Setup:

  **Custom condition query**:

      NOT EXISTS (
          SELECT * FROM {user_lastaccess} la
          WHERE la.userid = mnt.userid
          AND la.courseid = en.courseid
      )

  **Enable Conditions**: checked

  **Condition #1:**

    Field: enrol.courseid
    Operator: "="
    Conjunctive Condition: AND
    Value: 5 (Prefered course id)

Save the datasource configuration.


### 3. List of logged in users from 120 days:

Configurations the datasource that lists of logged in users from 120 days,

you can follow these steps:

1. **Data Source Name:** "List of logged in users from 120 days"
2. **ID Number**: loggedinusersin120days
3. **Select Main Table**: **user**

Click on the "**Generate fields**" button to populate the fields from the main table.

**Join Tables**: No additional join tables are required for this datasource.

**Fields Setup**:

  **Field #1**:

    Field: user:firstname
    Transform the format: None
    Additional format value: -

  **Field #2**:

    Field: user:lastname
    Transform the format: None
    Additional format value: -

  **Field #3**:

    Field: user:lastlogin
    Transform the format: None
    Additional format value: -

**Conditions Setup**:

  **Custom condition query**:

      DATEDIFF( NOW(),FROM_UNIXTIME(mnt.lastlogin) ) < 120

  **Enable Conditions**: checked

  **Condition #1:**

    Field: user.username
    Operator: "!="
    Conjunctive Condition: AND
    Value: admin

  **Condition #2:**

    Field: user.deleted
    Operator: "="
    Conjunctive Condition: AND
    Value: 0

Save the datasource configuration.


### 4. Students with enrollment and completion dates in current course:

Configurations the datasource that lists all the students with enrollment and completion dates in given course.

you can follow these steps:

1. **Data Source Name:** "Students with enrollment and completion dates in current course"
2. **ID Number**: enrolment-completion-date
3. **Select Main Table**: **role_assignments**

Click on the "**Generate fields**" button to populate the fields from the main table.

**Join Tables:**

  **Joint table #1**:

    Select Join table: context
    Table alias: ctx
    Enter condition query to join: ctx.id = mnt.contextid   AND ctx.contextlevel = 50

  **Joint table #2**:

    Select Join table: course
    Table alias: c
    Enter condition query to join: c.id  = ctx.instanceid AND c.id = 3 (Prefered course id)

  **Joint table #3**:

    Select Join table: user
    Table alias: u
    Enter condition query to join: u.id = mnt.userid

  **Joint table #4**:

    Select Join table: course_completions
    Table alias: cc
    Enter condition query to join: cc.course = c.id

**Fields Setup**:

  **Field #1**:

    Field: user:firstname
    Transform the format: None
    Additional format value: -

  **Field #2**:

    Field: user:lastname
    Transform the format: None
    Additional format value: -

  **Field #3**:

    Field: user:email
    Transform the format: None
    Additional format value: -

  **Field #4**:

    Field: course_completion:timeenroled
    Transform the format: Date
    Additional format value: -

  **Field #5**:

    Field: course_completion:timecompleted
    Transform the format: Date
    Additional format value: -

Save the datasource configuration.

### 5. List of badges awarded.

Configurations the datasource that lists the badges awarded.

you can follow these steps:

1. **Data Source Name:** "Badges awarded"
2. **ID Number**: badges-awarded
3. **Select Main Table**: **badge_issued**

Click on the "**Generate fields**" button to populate the fields from the main table.

**Join Tables:**

  **Joint table #1**:

    Select Join table: badge
    Table alias: b
    Enter condition query to join: b.id =  mnt.badgeid

  **Joint table #2**:

    Select Join table: user
    Table alias: u
    Enter condition query to join: u.id = mnt.userid


**Fields Setup**:

  *Placeholder Fields*:  user:id, badge:id

  **Field #1**:

    Field: badge_issued:id
    Transform the format: None
    Additional format value: -

  **Field #2**:

    Field: badge:name
    Transform the format: Link
    Additional format value: badges/overview.php?id={b.id}

  **Field #3**:

    Field: badge:id
    Transform the format: Image
    Additional format value: pluginfile.php/1/badges/badgeimage/{b.id}/f2

  **Field #4**:

    Field: user.firstname
    Transform the format: Link
    Additional format value: user/view.php?id={u.id}

  **Field #5**:

    Field: badge_issued:dateissued
    Transform the format: Date
    Additional format value: -

Save the datasource configuration.

## Examples Layouts

Here are some of the basic example of layouts:

### 1. Table layout:

This example layout provides a simple and straightforward way to present data in a tabular format within the Dash block.

  1. **Name**: Table layout
  2. **Supports field visibility**: checked
  3. **Supports pagination**: checked
  4. **Mustache**:
```mustache
        {{< block_dash/layout }}
        {{$body}}
            <h1>This is an example layout.</h1>
            <p>This example uses the Mustache syntax. Read more on it <a href="https://docs.moodle.org/dev/Templates">here</a></p>

            {{! This example loops through all rows (child data collections) and displays their fields. }}
            {{! 1. Check if any rows exist }}
            {{^data.rows}}
                <p class="text-muted">{{#str}}noresults{{/str}}</p>
            {{/data.rows}}
            <div class="table-responsive">
                <table class="table dash-table">
                    <thead>
                    <tr>
                        {{! 2. Display field headings by looping through first child data collection's fields }}
                        {{#data.first_child.data}}
                            <th class="dash-sort" data-sort="{{get_name}}">{{get_label}}</th>
                        {{/data.first_child.data}}
                    </tr>
                    </thead>
                    <tbody>
                    {{! 3. Loop through all rows (child data collections) }}
                    {{#data.rows}}
                        <tr>
                            {{! 4. Loop through all fields in data collection }}
                            {{#data}}
                                {{#is_visible}}
                                <td>{{{get_value}}}</td>
                                {{/is_visible}}
                            {{/data}}
                        </tr>
                    {{/data.rows}}
                    </tbody>
                </table>
            </div>

            {{! Here's another example: Access of specific data/fields. }}
            <p>Here's a course: {{{data.first_child.c_fullname}}}</p>
        {{/body}}
        {{/ block_dash/layout }}
```

### 2. Grid layout:

This layout presents the data in a grid format, allowing for a visually appealing presentation. Each item from the datasource is displayed as a grid item, with its content encapsulated within a <div> element. The grid items can be styled and arranged using CSS to create a visually pleasing layout.

> This layout works well for the badges data source.

  1. **Name**: Badges grid layout
  2. **Supports field visibility**: checked
  3. **Supports pagination**: checked
  4. **Mustache**:
```mustache
        {{< block_dash/layout }}
        {{$body}}
            {{^data.rows}}
                {{#noresult}}{{{noresult}}}{{/noresult}}
            {{/data.rows}}
            <ul class="table-responsive {{#preferences.hidetable}} d-none{{/preferences.hidetable}}" id="gridLayout">
                {{#data.rows}}
                <li class="grid-block grid-course-{{mnt_id}}"  data-title='{{{mnt_b_name}}}' data-toggle="tooltip"
              data-grid="{{mnt_id}}" data-html="true" >
                    {{#data}}
                    <div class="grid-item  {{completionstatus}}" >
                        <a class="grid-link" href="#">
                            {{{mnt_b_id}}}
                        </a>
                        <label>{{{mnt_b_name}}}</label>
                        <h4>{{{mnt_u_firstname}}}</h4>
                    </div>
                    {{/data}}
                </li>
                {{/data.rows}}
            </ul>
        {{/body}}
        {{/ block_dash/layout }}
```
The Mustache layout directly accesses the data source field values to populate rows.

### 3. User cards layout:

This Mustache layout presents data from the users datasource in a card-style format. Each user's information is displayed within an article element, including their profile image, username, handle, messaging link, and social media links if available.

  1. **Name**: User card layout
  2. **Supports field visibility**: checked
  3. **Supports pagination**: checked
  4. **Mustache**:
```mustache
        {{< block_dash/layout }}
        {{$body}}
            {{^data.rows}}
                {{#noresult}}{{{noresult}}}{{/noresult}}
            {{/data.rows}}
            <div class="user-profile-layout">
                {{#data.rows}}
                <article class="profile">
                    <div class="profile-image">
                        <img src="{{{u_picture_url}}}" />
                    </div>
                    <h2 class="profile-username">{{{u_fullname_linked}}}</h2>
                    <small class="profile-user-handle">@{{{u_username}}}</small>
                    <div class="profile-actions">
                        {{{u_message_link}}}
                    </div>
                    <div class="profile-links">
                        {{#u_pf_twitter}}
                        <a href="{{{u_pf_twitter}}}" class="link link--icon">
                            <i class="ph-twitter-logo"></i>
                        </a>
                        {{/u_pf_twitter}}
                        {{#u_pf_facebook}}
                        <a href="{{{u_pf_facebook}}}" class="link link--icon">
                            <i class="ph-facebook-logo"></i>
                        </a>
                        {{/u_pf_facebook}}
                        {{#u_pf_instagram}}
                        <a href="{{{u_pf_instagram}}}" class="link link--icon">
                            <i class="ph-instagram-logo"></i>
                        </a>
                        {{/u_pf_instagram}}
                    </div>
                </article>
                {{/data.rows}}
            </div>
        {{/body}}
        {{/ block_dash/layout }}
```
Add your css rule to style your layout in your current theme extra css options.
```css
.user-profile-layout{text-align:center}.user-profile-layout .profile{display:inline-flex;align-items:center;flex-direction:column;padding:3rem;width:90%;max-width:300px;background-color:#1b2028;border-radius:16px;position:relative;border:3px solid transparent;background-clip:padding-box;text-align:center;color:#f1f3f3;background-image:linear-gradient(135deg,rgba(117,46,124,.35),rgba(115,74,88,.1) 15%,#1b2028 20%,#1b2028 100%)}.user-profile-layout .profile:after{content:"";display:block;top:-3px;left:-3px;bottom:-3px;right:-3px;z-index:-1;position:absolute;border-radius:16px;background-image:linear-gradient(135deg,#752e7c,#734a58 20%,#1b2028 30%,#2c333e 100%)}.user-profile-layout .profile-image{border-radius:50%;overflow:hidden;width:175px;height:175px;position:relative}.user-profile-layout .profile-image img{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:100%}.user-profile-layout .profile-username{font-size:1.5rem;font-weight:600;margin-top:1.5rem}.user-profile-layout .profile-user-handle{color:#7d8396}.user-profile-layout .profile-actions{margin-top:1.5rem;display:flex;align-items:center;justify-content:center}.user-profile-layout .profile-actions>*{margin:0 .25rem}.user-profile-layout .profile-links{margin-top:3.5rem}.user-profile-layout .link{text-decoration:none;color:#7d8396;margin-left:.375rem;margin-right:.375rem}.user-profile-layout .link i{font-size:1.25em}
```

### 4. Course listing

This layout effectively presents data from the course data source in a listing format. Each course is displayed as a list item with its associated icon, title, and description.

  1. **Name**: Course listing layout
  2. **Supports field visibility**: checked
  3. **Supports pagination**: checked
  4. **Mustache**:
  ```mustache
        {{< block_dash/layout }}
        {{$body}}
            {{^data.rows}}
                {{#noresult}}{{{noresult}}}{{/noresult}}
            {{/data.rows}}
            <ol class="listing-dash-layout">
                {{#data.rows}}
                <li style="--accent-color:#0D6EFD">
                    <div class="icon">{{{c_image}}}</div>
                    <div class="title">{{{c_fullname}}}</div>
                    <div class="descr">{{{c_summary}}}</div>
                </li>
                {{/data.rows}}
            </ol>
        {{/body}}
        {{/ block_dash/layout }}
  ```

### 5. Features list

This layout displays a list of features using a card-style presentation. Each feature is represented as a card with a title, description, and list of visible fields.

> Disable the fields used outside the rows loop in the fields preferences of datasource.


  1. **Name**: Course listing layout
  2. **Supports field visibility**: checked
  3. **Supports pagination**: checked
  4. **Mustache**:

```mustache
    {{< block_dash/layout }}
    {{$body}}
        {{^data.rows}}
            {{#noresult}}{{{noresult}}}{{/noresult}}
        {{/data.rows}}
        <div class="dashaddon-developer-plans-layout">

          <div class="planItem__container">

            {{#data.rows}}
            <div class="planItem planItem--free">
              <!-- Hide the fields fullname, summary in the preference modal under field settings  -->
              <div class="card">
                <div class="card__header">
                  <div class="card__icon symbol symbol--rounded"></div>
                  <h2>{{{c_fullname}}}</h2>
                </div>
                <div class="card__desc">{{{c_summary}}}</div>
              </div>

              {{! 3. Loop through all rows (child data collections) }}
              <ul class="featureList">
                {{! 4. Loop through all visible fields in data collection }}
                {{#data}}
                {{#is_visible}}
                <li>{{{get_value}}}</li>
                {{/is_visible}}
                {{/data}}
              </ul>
              <!-- HIde this button field in preference -->
              {{{c_button}}}
            </div>
            {{/data.rows}}
          </div>
        </div>
    {{/body}}
    {{/ block_dash/layout }}
```
Add your css rule to style your layout in your current theme extra css options.

```css
.price,h2{font-weight:400}// plan item starts .planItem{--border:1px solid #e6e6e6;--bgColor:#fff;--boxShadow:none;display:grid;grid-auto-flow:var(--direction);grid-auto-columns:1fr;gap:1.5rem}&--pro{--border:0;--boxShadow:0px 14px 30px rgba(204, 204, 204, 0.32);--labelColor:#fff}.featureList{--color:#fff;--icon:var(--whiteTick)}&--white{--bgColor:#fff;--shadowColor:rgb(255 255 255 / 30%);--outline:#fff}&:hover{transform:translateY(-2px);box-shadow:0 6px 10px var(--shadowColor)}&:focus-visible{outline-offset:2px;outline:2px solid var(--outline)}&__icon{width:2.625rem;height:2.625rem}h2{color:var(--titleColor);font-size:1.5rem;line-height:1.2;margin:0;flex-grow:1}&__desc{margin:1.5rem 0 0;color:var(--descColor)}.price{--color:#000;--priceMargin:0;display:flex;color:var(--color);align-items:center;gap:.5625rem;font-size:2rem;margin:var(--priceMargin);color:var(--baseColor)}&:after{content:"";box-sizing:border-box;display:block;position:absolute;border:2px solid var(--pink);width:var(--small);height:var(--small);border-radius:var(--radius);top:50%;left:50%;transform:translate(-50%,-50%)}
```
