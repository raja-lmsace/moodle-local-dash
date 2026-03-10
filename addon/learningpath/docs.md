# Learning Path Widget Documentation

## Introduction

The Learning Path Widget is a Dash block addon that enables educators to create visual, interactive learning paths in Moodle. Unlike standard course lists, this widget displays courses along customizable SVG paths, providing students with a clear visual representation of their progression through a learning sequence.

The widget addresses the limitation in standard Moodle where courses cannot be combined into visual learning paths, a feature available in platforms like Moodle Workplace or Totara.
## Key Features

The Learning Path Widget provides:

- **Visual Course Progression** - Display courses along custom SVG paths with clear progress indicators
- **Responsive Design** - Separate paths for desktop, tablet, and mobile devices
- **Dual Positioning Modes** - Choose between automatic path-based positioning or manual zone-based placement
- **Rich Progress Tracking** - Six distinct course statuses with customizable colors
- **Info Dashboard** - Configurable sidebar or top panel showing KPIs, course lists, faculty, and badges
- **Flexible Course Selection** - Multiple filters including categories, tags, and prerequisites
- **Custom Field Integration** - Map course custom fields to shapes and icons

## Installation

1. Extract the plugin files to `local/dash/addon/learningpath/`
2. Log in as administrator and navigate to Site administration → Notifications
3. Follow the installation prompts to complete the setup
4. The widget will be available in the Dash block widget list

**Optional Plugins:**
- tool_timetable - Enables assignment scheduling and status tracking features

## Widget Configuration

### Basic Settings

#### 1. **Info Area**

The Info Area displays summary information about the learning path, including progress metrics, KPIs, and additional course details.

<img width="549" height="509" alt="Infoarea-block-options" src="https://github.com/user-attachments/assets/7e658c6a-41f8-461b-9239-bf662ee02b36" />

**Info Area Position** - This setting becomes available when Info Area is enabled. Choose between:

- **Top** (default) - The info area appears as a horizontal bar above the learning path, similar to the current implementation
- **Sidebar** - The info area appears as a vertical panel on the left side of the learning path

When Position is set to **Sidebar**, additional configuration options become available:

**KPI Settings** - Select up to 4 Key Performance Indicators from 5 available metrics:

   - **Courses** - Displays completed courses versus total courses in "X / Y" format (e.g., "3 / 10"). This metric provides students with a clear understanding of how much of the learning path they have completed and how much remains.
   
   - **Courses (%)** - Shows overall completion as a percentage (e.g., "30%"). This displays completed courses out of total courses as a percentage. This format is particularly effective for motivating students as they can see incremental progress even within partially completed courses.
   
   - **Badges** - Displays earned badges versus possible badges in "X / Y" format (e.g., "2 / 5"). This shows earned badges out of possible badges that can be collected in the courses contained in the learning path. This includes both course badges directly associated with learning path courses and site badges whose criteria reference these courses. Badge tracking adds gamification elements to encourage completion.
   
   - **Period** - Shows the enrollment period as a date range (e.g., "Jan 1 - Dec 31, 2025"). This displays the earliest enrollment start date of a course in the learning path until the latest enrollment end date of a course in the learning path. This helps students understand the expected timeline for completing the path.
   
   - **Status** - Indicates whether the user has completed all courses that have a due date in the past. Displays "On Track" if all past-due courses are completed, or "Not on Track" if any courses with past due dates remain incomplete. This requires the tool_timetable plugin to be installed and configured. This metric helps identify students who may need intervention or support.


**Display Path Index** - When enabled, displays an expandable progress bar for each course in the path, color-coded by status (default: disabled)

**Display Faculty** - Multi-select field populated with roles available in course context. All users with the selected roles in any of the learning path courses will be displayed (e.g., Teacher, Non-editing teacher)

**Display Badges** - When enabled, displays badges that can be earned in the learning path courses. Unearned badges appear grayed out (default: disabled)

The Info Area provides students with at-a-glance understanding of their progress and helps them identify what courses remain in their learning journey. The Sidebar position offers richer information display with KPIs, course lists, faculty, and badges.

**Top**

<img width="824" height="224" alt="top-info-area" src="https://github.com/user-attachments/assets/64b966ec-5b34-470a-9434-70998f88d809" />

**Sidebar**

<img width="1919" height="1464" alt="infoarea-coursecompletion-collapsed (1)" src="https://github.com/user-attachments/assets/03c78d33-15dc-490a-89a2-fd1a2c9e6bcc" />


#### 2. **Course Size**

This setting controls the visual size of course elements displayed on the learning path. Larger sizes make courses more prominent and easier to identify, while smaller sizes allow more courses to fit on screen.

Available sizes:
- **Dot** (default) - 20×20px minimal dots, displayed only with status color. When Dot is selected, Visual and Shape settings have no effect
- **Tiny** - 35×35px
- **Small** - 50×50px
- **Medium** - 75×75px
- **Large** - 100×100px
- **Extra Large** - 150×150px, best for featured learning paths

When any size other than Dot is selected, you can customize both the Visual and Shape settings to control course appearance.

#### 3. **Start Element**

When enabled, this adds a "Start" marker at the beginning of the learning path, clearly indicating where students should begin their journey.

#### 4. **Finish Element**

When enabled, this adds a "Finish" marker at the end of the learning path, providing students with a clear goal and sense of completion.

<img width="777" height="253" alt="info-settings" src="https://github.com/user-attachments/assets/ba7702cc-8d5d-4030-8f6a-2b8987a70436" />


### Layout and Positioning

#### 1. **Positioning Mode**

The widget offers two positioning modes:

**Path Mode** - Courses are automatically spread along an SVG path at equal intervals. This mode is ideal when you want consistent spacing and don't need precise control over individual course positions.

**Zones Mode** - Courses are manually assigned to specific zones within your SVG. This mode provides complete control over course placement, allowing you to create custom layouts like semester-based grids or departmental structures.

#### 2. **SVG Path Files**

Select SVG files for different viewport sizes to ensure optimal display across devices. SVG files can be sourced from:
- Global SVG paths uploaded by administrators
- Block-specific files uploaded in the Resources tab

**Path for Desktop** - Select the SVG file for screens 1024px and wider. Options include:
- Global desktop paths (uploaded at site level)
- Files from the block's Resources file picker

**Path for Tablet** - Select the SVG file for screens between 768px and 1023px. Options include:
- "Inherit from desktop" (uses the desktop SVG)
- Global tablet paths (uploaded at site level)
- Files from the block's Resources file picker

**Path for Mobile** - Select the SVG file for screens under 768px. Options include:
- "Inherit from tablet" (uses the tablet SVG, which may inherit from desktop)
- Global mobile paths (uploaded at site level)
- Files from the block's Resources file picker

The widget automatically selects the appropriate SVG based on the user's device. If no SVG is selected for a particular viewport and inheritance is not configured, the widget displays courses in a grid layout instead.

SVG files should contain either a path element (for Path mode) or zone elements like circles, rectangles, or polygons (for Zones mode).

**Supported Zone Elements:**

By default, the widget recognizes the following SVG element types as zones:
- `<circle>` - Circular zones
- `<rect>` - Rectangular zones
- `<polygon>` - Polygon zones

Additionally, the following element types are supported and can be enabled through global configuration:
- `<ellipse>` - Elliptical zones
- `<g>` - Group elements containing multiple shapes

The list of supported element types can be configured globally at Site administration → Plugins → Local plugins → Dash addon: Learning path. This allows administrators to extend or limit the types of SVG elements used as zones based on institutional needs.

#### 3. **Configure Zones**

This button becomes available when Positioning is set to "In zones". Clicking it opens a new window with a tabbed interface for managing zone-based course placement across all viewports (desktop, tablet, mobile).

The zone configuration window displays:

**SVG Tabs** - Each uploaded SVG (desktop, tablet, mobile) appears as a separate tab, allowing you to configure zones independently for each viewport.

**Zone List** - For each SVG, the system automatically detects all supported zone elements and displays them in a list with the following information:

- **Type** - The element type (e.g., "Rectangle" for `<rect>`, "Circle" for `<circle>`, "Polygon" for `<polygon>`)
- **ID** - The SVG element's ID attribute if available (design tools like Figma can export elements with IDs)
- **Status Toggle** - Enable or disable individual zones. Disabled zones are not used for course positioning
- **Set Course** - An autocomplete selector to manually assign a specific course to this zone

**Interactive Highlighting** - The zone configuration window provides visual feedback:
- Rolling over a zone in the list highlights it in the SVG preview
- Rolling over a zone in the SVG highlights it in the list
- The SVG displays all courses that are currently positioned, making it easy to visualize the learning path

<img width="756" height="652" alt="zone_settings" src="https://github.com/user-attachments/assets/b6df30ce-553e-43ce-8f7b-c82084cd318e" />


### Course Selection and Ordering

#### 1. **Order By**

This setting determines the sequence in which courses appear on the learning path:

- **Menu order** - Sort by course ID (the order courses appear in Moodle's course management)
- **Course short name** - Alphabetical order by short name
- **Course full name** - Alphabetical order by full name
- **Course ID number** - Sort by the course's ID number field
- **Course start date** - Chronological order by start date
- **Custom** - Manually specify the exact order using comma-separated course IDs

The Custom option is particularly useful for learning paths that follow a specific pedagogical sequence that doesn't align with standard sorting methods.

#### 2. **Order Direction**

Controls whether courses are sorted in ascending or descending order:

- **Ascending** - A to Z, 1 to 10, oldest to newest
- **Descending** - Z to A, 10 to 1, newest to oldest

#### 3. **Custom Order**

When Order By is set to Custom, this field becomes available. Enter course IDs separated by commas to specify the exact sequence. For example: "15,23,8,42" will display course 15 first, followed by course 23, then course 8, and finally course 42.

#### 4. **Limit**

Set the maximum number of courses to display in the learning path. This is useful for focusing on a subset of courses or preventing overcrowding on the path. For example, setting the limit to 10 will show only the first 10 courses after filters and ordering are applied.

### Course Filters

Filters control which courses are included in the learning path. Multiple filters can be combined to create precise course selections.

#### 1. **Course Category**

Select one or more categories to include courses from. Only courses within the selected categories will appear in the learning path. Hidden courses are automatically excluded regardless of category selection.

This filter is essential for creating department-specific or program-specific learning paths.

#### 2. **Current Category**

When enabled, the widget automatically displays only courses from the current page's category context. This is useful when placing the widget on category pages where you want the learning path to adapt to the viewing context.

#### 3. **Course Tags**

Filter courses based on their assigned tags. Only courses with the selected tags will be included in the learning path. This allows you to create thematic learning paths that span multiple categories, such as "Leadership Skills" or "Data Analytics."

#### 4. **Course Prerequisites**

Show only courses that have specific prerequisite courses configured. This filter helps create advanced learning paths that build upon foundational courses.

#### 5. **Current Course Prerequisites**

When enabled, displays courses that list the current course (the course page where the widget is placed) as a prerequisite. This helps students understand what courses they can access next after completing the current course.

#### 6. **Assignment Tags**

Available when the tool_timetable plugin is installed. This filter allows you to include courses based on assignment tags defined in the Timetable tool, enabling integration with scheduled learning programs.

<img width="779" height="951" alt="conditions" src="https://github.com/user-attachments/assets/b8e47758-ae25-4e4b-85ac-e7d2a92de6de" />

### Visual Customization

#### 1. **Course Shape**

This setting determines the geometric shape used for course elements on the path. The Shape setting is only available when Course Size is set to anything other than Dot.

Available shape options:

- **Set: Circle** (default) - Standard circular shape, universally recognizable
- **Set: Triangle** - Three-sided shape, useful for differentiating course types
- **Set: Hexagon** - Six-sided shape, modern and distinctive
- **Set: Diamond** - Rotated square, creates visual interest
- **Set: Star** - Five-pointed star, ideal for highlighting important courses
- **Shape set for [custom field name]** - Shape varies based on a course custom field value. This option appears when a custom course field is configured in global settings for shape mapping
- **Shape of the vector element (experimental)** - The course uses the actual shape of the SVG element. When positioning is set to "On the path", this falls back to circle

The custom field option enables you to automatically assign different shapes to courses based on their type, level, or any other custom field attribute. For example, if you have a custom field called "Type" with options like "E-Learning", "Webinar", "Seminar", "Blended", "Exam", and "Library", you can map each option to a specific shape:
- E-Learning → Circle
- Webinar → Triangle
- Seminar → Triangle
- Blended → Hexagon
- Exam → Diamond
- Library → Star

**Example: triangle-shape**

<img width="914" height="739" alt="triangle-shape" src="https://github.com/user-attachments/assets/9afc950e-7a5f-47b6-933e-3b756fd4cb87" />


#### 2. **Course Visual**

This setting determines what appears inside each course shape. The Visual setting is only available when Course Size is set to anything other than Dot.

Available visual options:

- **None** (default) - The course is displayed without additional visual elements, showing only the background color determined by the status
- **Number** - The course displays its sequential number (determined by the order setting) on top of the background color determined by the status
- **Course Image** - The course uses its summary image with an outline/border in the color determined by the status
- **Determined by [custom field name]** - The course uses the icon associated with the custom course field on top of the background color determined by the status. This option appears when a custom course field is configured in global settings for icon mapping

The Number option is particularly effective for linear learning paths where sequence is critical. Course Image provides immediate visual recognition of courses students are already familiar with. The custom field option allows you to display subject-specific or category-specific icons (such as Font Awesome icons) for each course.

<img width="1920" height="1080" alt="course-visual" src="https://github.com/user-attachments/assets/25ca4b44-3652-4bda-936b-c527130006d4" />


#### 3. **Status Colors**

Customize the color scheme for each of the six course completion statuses. Using distinct, high-contrast colors helps students quickly identify their progress at a glance. These colors can be configured both globally and at the widget level, with widget-level settings overriding global defaults.

Available status colors:

- **Not Available** - The user is not enrolled in the course and cannot enroll (suggested: light gray)
- **Available** - The user is not enrolled but can self-enroll (suggested: dark gray)
- **Enrolled (Not Started)** - The user is enrolled but has not started the course; no completion criteria have been completed yet (suggested: dark blue)
- **In Progress** - The user is enrolled and has started the course; at least one completion criterion has been completed (suggested: yellow)
- **Completed** - The user is enrolled and has completed all course requirements (suggested: green)
- **Failed** - The user is enrolled, completed the course, but did not achieve the required course grade (suggested: red)

Color selections should consider accessibility guidelines and ensure sufficient contrast for all users. Global color settings can be configured at Site administration → Plugins → Dash Pro, making these colors available for other widgets and data sources as well.

## Course Status Indicators

The widget uses six distinct statuses to represent course availability and progress:

**Not Available** (suggested: light gray) - The user is not enrolled in the course and cannot enroll. This may be due to enrollment restrictions, hidden course status, unmet prerequisites, or lack of enrollment methods available to the user.

**Available** (suggested: dark gray) - The user is not enrolled in the course but can enroll themselves. The course allows self-enrollment and the student can access it immediately by enrolling.

**Enrolled (Not Started)** (suggested: dark blue) - The user is enrolled in the course but has not started. No completion criteria have been completed yet, and the student has not accessed any course activities.

**In Progress** (suggested: yellow) - The user is enrolled and has started the course. At least one completion criterion has been completed, indicating the student is actively working on the course.

**Completed** (suggested: green) - The user is enrolled and has completed all course requirements. This includes completion of all required activities, achieving minimum grades if applicable, and meeting any other configured completion criteria.

**Failed** (suggested: red) - The user is enrolled, has completed the course activities, but did not achieve the required course grade. This status only appears when course completion includes grade criteria and the student has been graded in all course activities without achieving the minimum required grade.

## Advanced Features

### Custom Field Integration

The Learning Path widget integrates with Moodle's custom course fields to provide dynamic shape and icon mapping. Custom field configuration is managed in the global Dash Pro settings to ensure consistency across multiple widgets and data sources.

#### Custom Shapes

Course shapes can be automatically assigned based on custom field values:

1. **Create a course custom field** - Navigate to Site administration → Courses → Course custom fields and create a custom field of type "Dropdown menu" (e.g., "Course Type" with options like "E-Learning," "Webinar," "Seminar," "Blended," "Exam," "Library")

2. **Configure global settings** - Navigate to Site administration → Plugins → Dash Pro and set the "Select course field for shape" setting to point to your custom field

3. **Map field values to shapes** - In the global settings, use the mapping interface to associate each custom field option with a desired shape:
   - E-Learning → Circle
   - Webinar → Triangle
   - Seminar → Triangle
   - Blended → Hexagon
   - Exam → Diamond
   - Library → Star

4. **Select in widget settings** - When configuring a Learning Path widget, select "Shape set for [custom field name]" in the Course Shape setting

This enables automatic visual differentiation of courses based on their type, level, or any other custom field attribute. If you prefer to manually select shapes rather than using an intermediate type field, create a custom field with the shape names as the select options.

#### Custom Icons

Similar to shapes, icons can be assigned based on custom field values:

1. **Create a course custom field** - Create a custom field of type "Dropdown menu" (e.g., "Subject Area" with options like "Mathematics," "Science," "Language," "History")

2. **Configure global settings** - Navigate to Site administration → Plugins → Dash Pro and set the "Select course field for visual" setting to point to your custom field

3. **Map field values to icons** - In the global settings, use the icon picker (developed for Union theme) to associate each custom field option with a desired icon. Icons are specified using Font Awesome notation (e.g., "fa-calculator:fontawesome")
   - Mathematics → fa-calculator:fontawesome
   - Science → fa-flask:fontawesome
   - Language → fa-language:fontawesome
   - History → fa-landmark:fontawesome

4. **Select in widget settings** - When configuring a Learning Path widget, select "Determined by [custom field name]" in the Course Visual setting

This creates immediate visual recognition of course subject areas without requiring course images, and provides a consistent icon scheme across your learning paths.

<img width="826" height="826" alt="course-icon" src="https://github.com/user-attachments/assets/1971d63a-2497-4ddf-8da0-c9fc3eace81f" />


### Course Details Area

Clicking any course element on the path displays detailed course information. The display method depends on the Info Area Position setting:

**When Info Area Position is "Top" or disabled:**
A modal window opens displaying course details.

<img width="1905" height="947" alt="Details-area-modal" src="https://github.com/user-attachments/assets/05663108-e416-48ee-a57f-3c10da78c990" />

**When Info Area Position is "Sidebar":**
Course details appear in the available space within the details area section, allowing students to view course information while still seeing the learning path.

**Course Information Displayed:**

- **Course Name** - Complete course title
- **Course Image** - The course's featured image (if configured)
- **Course Summary** - The course description/summary (if available)
- **Completion Progress Bar** - Visual indicator of course completion
- **Completion Statistics** - Detailed breakdown of completion status

**Assignment Information** (when tool_timetable is installed and course has an associated assignment):
- **Assignment Start** - When the assignment becomes available
- **Assignment Due** - The assignment due date
- **Assignment End** - When the assignment closes
- **Assignment Type** - Whether the assignment is mandatory or optional
- **Assignment Priority** - Low, Normal, or High priority indicator
- **Assignment Tags** - Tags associated with the assignment

**Call to Action:**
The details area includes a context-appropriate action button:
- **"Start"** - For courses not yet begun
- **"Resume"** - For courses in progress
- **"Review"** - For completed courses

**Navigation** (when displayed as modal):
- Previous course button - Navigate to the preceding course in the path
- Next course button - Navigate to the following course in the path

This detailed view allows students to review course information and take immediate action without leaving the learning path interface.

<img width="1912" height="1408" alt="Details-area" src="https://github.com/user-attachments/assets/9756236d-b546-48eb-b928-88a296da8fb6" />

## Global Settings

Global settings for the Learning Path widget are configured in two locations:

### Learning Path Widget Settings

Navigate to: Site administration → Plugins → Local plugins → Dash addon: Learning path

#### Default Configuration

**Info Area Defaults:**
- **Info Area Enabled** - Default state for new widgets
- **Info Area Position** - Default position (top or sidebar)
- **Default KPI 1** - Default selection for first KPI slot
- **Default KPI 2** - Default selection for second KPI slot
- **Default KPI 3** - Default selection for third KPI slot
- **Default KPI 4** - Default selection for fourth KPI slot
- **Default Display Path Index** - Default state for path index display
- **Default Display Faculty** - Default roles selected for faculty display
- **Default Display Badges** - Default state for badge display

**Visual Defaults:**
- **Default Course Size** - Default size selection (Dot, Tiny, Small, Medium, Large, Extra Large)
- **Default Course Shape** - Default shape option
- **Default Course Visual** - Default visual mode

These defaults are applied when creating new Learning Path widgets but can be overridden at the widget level.

<img width="1208" height="723" alt="Infoarea-global-settings" src="https://github.com/user-attachments/assets/b327dfd7-8319-41e6-b697-da9f398b33a0" />


#### Resource Management

**Global SVG Paths** - Upload SVG files that are available to all Learning Path widgets across the site. These provide a starting point that individual widgets can override with their own SVG files.

**Supported Zone Elements** - Configure which SVG element types are recognized as zones when using zone-based positioning. The default supported elements are `<circle>`, `<rect>`, and `<polygon>`. Administrators can add `<ellipse>` and `<g>` (group) elements to the supported list based on their SVG design requirements. This setting accepts a comma-separated list of element type names.

<img width="1852" height="1494" alt="svg-global-settings" src="https://github.com/user-attachments/assets/4b67d5a7-852e-46d6-ab7b-ee3d76d211dd" />


### Dash Pro Global Settings

Navigate to: Site administration → Plugins → Dash Pro

These settings are shared across multiple widgets and data sources to ensure visual consistency:

#### Custom Field Configuration

**Select course field for shape** - Choose which custom course field is used to determine course shapes when the "Shape set for [field name]" option is selected. This should be a custom field of type "Dropdown menu" containing different course types or categories.

**Shape Mapping** - Map each option of the selected custom field to a specific shape (Circle, Triangle, Hexagon, Diamond, Star). This mapping determines which shape appears for courses with each custom field value.

<img width="1291" height="553" alt="shape-custom-field" src="https://github.com/user-attachments/assets/3e571bc0-3524-44c5-bbe5-ae29a0ab2548" />


**Select course field for visual** - Choose which custom course field is used to determine course icons when the "Determined by [field name]" option is selected. This should be a custom field of type "Dropdown menu" containing subject areas or course categories.

**Icon Mapping** - Map each option of the selected custom field to a specific icon using the icon picker. Icons are specified in Font Awesome notation and appear inside course shapes.

<img width="1146" height="643" alt="visual-custom-field" src="https://github.com/user-attachments/assets/e84eb34e-0b08-4c4e-b96d-43c8508230eb" />


#### Status Colors

Configure default colors for all six course statuses. These colors are used across the Learning Path widget and other Dash widgets:

- **Not Available Color** - For courses users cannot access
- **Available Color** - For courses available for self-enrollment
- **Enrolled (Not Started) Color** - For enrolled but not started courses
- **In Progress Color** - For courses with partial completion
- **Completed Color** - For fully completed courses
- **Failed Color** - For courses that did not meet grade requirements

Widget-level color settings will override these global defaults.

<img width="1233" height="1231" alt="color-status" src="https://github.com/user-attachments/assets/1311cb84-e943-4353-84ad-ee4e04e34aea" />


### Block-Specific Resources

Individual block instances can upload their own SVG files through the **Resources** tab in the Dash block configuration. This feature enables users without site administration access to create custom learning path designs.

**Accessing the Resources Tab:**
1. Navigate to the Dash block configuration
2. Click on the "Resources" tab
3. Use the file picker to upload SVG files

**Using Block-Specific SVG Files:**

Files uploaded in the Resources tab become available in the SVG path selection dropdowns alongside global paths. This allows for:
- Department-specific learning path designs
- Program-specific visual layouts
- Custom paths for different contexts without requiring administrator intervention

**Path Selection Priority:**

When configuring SVG paths in the widget preferences, you can choose from:
1. **Global paths** - Uploaded by administrators at the site level
2. **Block resources** - Uploaded via the Resources tab in the block configuration
3. **Inheritance options** - Tablet can inherit from desktop, mobile can inherit from tablet

This flexibility ensures that educators and course coordinators can customize learning paths to match their specific needs while administrators maintain a library of standard templates.

<img width="972" height="904" alt="course-path-resources" src="https://github.com/user-attachments/assets/88e632b2-c713-4dd8-89d7-73f93b75d785" />

## Version Information

**Plugin:** dashaddon_learningpath
**Version:** 1.0 (2025110100)
**Developer:** bdecent gmbh
**License:** GNU GPL v3 or later
**Required:** Moodle 3.3+, PHP 7.2+, Dash (local_dash) plugin
