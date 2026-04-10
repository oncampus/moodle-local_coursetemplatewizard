# Course Template Wizard

**Course Template Wizard** is a local plugin for Moodle that simplifies the use and management of course templates.
Course creators and teachers can use it to select course templates and copy them into their own course category,
allowing them to create standardized courses more quickly.

## Features

- **Course template management**: Managing of course templates in a configured course category
- **Apply course templates**: Using course templates to overwrite courses in any course category

## Installation

1. Copy the contents of this directory to `{your/moodle/dirroot}/local/coursetemplatewizard`.
2. Go to **Site Administration → Notifications** to initiate or execute the installation via `php admin/cli/upgrade.php`.

### Requirements
- Moodle version: `2024100700` or higher.

## Configuration

Once installed, the plugin can be configured via the following page:  
**Site Administration → Plugins → Local plugins → Course Template Wizard**

Before using the plugin, the following settings must be set:
- **Template course category**: The course category containing all course templates.
- **Template target exceptions**: Courses (Specified by comma-separated IDs) that can't be overwritten by course templates.

## Usage

- Navigate to a course, you want to apply a course template on
- Open the "Template Wizard overview" via the secondary menu
- Select a course template you want to use
- (Optional) Add a course image and a description to the target course
- Apply the template

## Rights

This plugin defines the following right:

| Name                             | Description                                                             | Default role                   |
|----------------------------------|-------------------------------------------------------------------------|--------------------------------|
| `local/coursetemplatewizard:use` | Allows the user to use course templates to overwrite existing courses   | Course creator, editingteacher |

## License

2026 oncampus GmbH support@oncampus.de

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program. If not, see https://www.gnu.org/licenses/.