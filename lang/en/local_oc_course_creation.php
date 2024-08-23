<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_oc_course_creation
 * @category    string
 * @copyright   2021 Laurenz Schindler <Laurenz.Schindler@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course duplication';
$string['plugin_categoryname'] = 'Oncampus course creation category';
$string['settings:choose_course'] = 'Select course area';
$string['settings:create_course'] = 'Create course from template';
$string['settings:template_course_desc'] =
    'Set the course area in which the course templates are stored.';
$string['creation_page_title'] = 'Course Templates';
$string['headline_table_view'] = 'Course Templates';
$string['table_head_coursename'] = 'Course name';
$string['table_head_edit'] = 'Use this course as a template';
$string['table_head_preset_value'] = 'Preset value';
$string['course_format'] = 'Course format';
$string['info_no_courses'] = 'No course was found in the chosen course category';
$string['table_head_courseimg'] = 'Preview image';
$string['form:string:form_select'] = 'Edit/Create';
$string['modal_delete_title'] = 'Delete entry';
$string['modal_delete_message_failed'] = 'Delete entry failed';
$string['modal_delete_message'] = 'Do you want to delete this entry?';
$string['modal_delete_button'] = 'Delete';
$string['modal_create_title'] = 'Create entry';
$string['modal_create_message_failed'] = 'Creation of entry failed';
$string['modal_create_message'] = 'Do you want to create this entry?';
$string['modal_create_button'] = 'Create';
$string['table_btn_submit'] = 'Create';
$string['settings:edit_presets'] = 'Edit presets';
$string['edit_preset_value_title'] = 'Edit preset value';
$string['record_type1_value1'] = 'SuSe';
$string['record_type1_value2'] = 'WiSe';
$string['table_head_new_type'] = 'Descriptive name';
$string['table_head_coursetext'] = 'Description';
$string['settings:template_prefix_checkbox_text_desc'] = 'How should the use of the prefix be titled?';
$string['settings:template_prefix_desc'] = 'Set an example course name prefix';
$string['settings:template_prefix_default'] = 'Module 1';
$string['settings:edit_template_prefix'] = 'Course name prefix';
$string['settings:edit_template_checkbox_desc'] = 'Checkbox description';
$string['settings:edit_template_checkbox_default'] = 'Add an prefix to course name';
$string['oc_course_creation:handle_presets'] = 'Edit course copy presets';
$string['oc_course_creation:oc_course_creation_access_capability'] = 'Access to course creation from template';
$string['oc_course_creation:course_cat_copy_cap'] = 'Course creation by Template';
$string['form:copy:image_desc'] =
    'Please select an image from our collection (left gray button) or upload your own image for your course.';
$string['form:copy:image_header'] = 'Select course image';
$string['form:copy:course_name_header'] = 'Enter course data';
$string['form:copy:teacher_course_type_placeholder'] = 'Course name';
$string['form:copy:teacher_name_placeholder'] = 'Surname lecturer';
$string['form:copy:course_details'] = 'Enter course details';
$string['form:copy:description'] =
    'This course will be created and added to the specified course category.';
$string['form:copy:select_default'] = 'Select course category';
$string['form:copy:prefix_desc'] = 'Add module name';
$string['settings:use_default_course_naming'] = 'Create course name with preselections';
$string['settings:use_default_course_naming_desc'] =
    'This field disables all course name template options in the course creation form.';
$string['settings:template_textfield_values'] = 'Textfield input masks';
$string['settings:template_textfield_values_desc'] =
    'Entering with comma-separated names creates free text input fields in the course input form. As default "last name, course type" two input fields with the name last name and course name are entered.';
$string['settings:template_textfield_values_default'] = 'last name, course designation';
$string['settings:template_textfield_seperator'] = 'Separators';
$string['settings:template_textfield_seperator_desc'] =
    'The separators are displayed between each element of the input mask as a drop-down and offer the possibility to make the course name readable.';
$string['settings:template_course_name_readonly'] = 'Read full course name only';
$string['settings:template_course_name_readonly_desc'] =
    'Revokes the user\'s editing rights of the form input of the full course name.';
$string['settings:template_course_shortname_readonly'] = 'Short course name read only';
$string['settings:template_course_shortname_readonly_desc'] =
    'Revokes the user\'s editing rights of the form input of the short course name.';
$string['settings:template_prefix_checkbox_toggle'] = 'Toggle prefix';
$string['settings:template_prefix_checkbox_toggle_desc'] = 'The course name prefix is (not) displayed in the form.';
$string['settings:template_display_seperator'] = 'Toggle separator elements';
$string['settings:template_display_seperator_desc'] =
    'The separator elements are (not) displayed in the form and replaced by a single space.';
$string['settings:use_after_creation_name'] = 'Show Templates after course creation';
$string['settings:use_after_creation_desc'] =
    'Activation displays templates for selection after course creation.';
$string['settings:cshortname_charnumber'] = 'Short course name adopted characters';
$string['settings:cshortname_charnumber_desc'] =
    'How many characters from the course details default fields should be included in the course short name.';
