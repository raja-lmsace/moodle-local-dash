## Addon Overview
The Developer addon is a powerful tool within the local Dash Moodle plugin ecosystem, catering specifically to developers seeking to extend the functionality of Moodle's Block Dash feature. With the Developer addon, developers can create custom data sources and design unique layouts, offering unparalleled flexibility and customization options.

## Key Features:
  1. **Custom Data Sources**: Developers can harness the power of SQL queries to create bespoke data sources, granting precise access to Moodle's expansive database.
  2. **Layout Customization**: Leveraging Mustache templates, developers can craft dynamic layouts tailored to specific requirements, enhancing the presentation of data within Moodle.
  3. **Integration with Block Dash**: Custom data sources and layouts seamlessly integrate with Moodle's Block Dash feature, enriching the user experience with personalized content.

## Installation Dependencies:

  Before using the Developer widget, ensure that the local Dash Moodle plugin is installed and activated within your Moodle environment. This plugin serves as the foundation for integrating the Developer widget's functionality into your Moodle instance.

## Developer Plugin Interface:

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


## Examples

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
