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
 * @package     local_coursetemplatewizard
 * @copyright   2025 Oncampus GmbH
 * @category    string
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apply_template'] = 'Apply template';
$string['confirm_overwrite_checkbox'] = 'I understand: The selected target course will be completely overwritten by the template.';
$string['confirm_overwrite_message'] = 'Warning! The selected target course will be overwritten by the chosen template. Do you want to continue?';
$string['confirm_overwrite_no'] = 'No, cancel';
$string['confirm_overwrite_note'] = 'Warning: This action is final and <strong>cannot</strong> be undone.';
$string['confirm_overwrite_required'] = 'Please confirm the overwrite by ticking the checkbox.';
$string['confirm_overwrite_title'] = 'Overwrite course';
$string['confirm_overwrite_yes'] = 'Yes, overwrite';
$string['course_format'] = 'Course category';
$string['coursesummary'] = 'Course summary';
$string['coursetemplateserviceuser_firstname'] = 'Course template';
$string['coursetemplateserviceuser_lastname'] = 'Service user';
$string['coursetemplateserviceuserrole_desc'] = 'A Service user for course templates is an internal user, which executes backup and restore processes in the background, required for applying the template to the target course.';
$string['coursetemplateserviceuserrole_name'] = 'Course template service User';
$string['coursetemplatewizard:use'] = 'Access course templates and overwrite existing courses';
$string['creation_page_title'] = 'Template Wizard';
$string['errornocourseoverwriterights'] = 'You do not have permission to override this course with a template.';
$string['errornotteacherincourse'] = 'You are not enrolled in this course. Therefore you are not allowed to use the Template Wizard for this course.';
$string['form_copy_course_name_header'] = 'Course data';
$string['form_copy_description'] = 'Select the target course to be overwritten with the template.';
$string['form_copy_header'] = 'Select course image';
$string['form_copy_image_desc'] = 'Choose a course image or upload your own image for the target course.';
$string['form_copy_select_default'] = 'Select target course';
$string['fullnamecourse'] = 'Full course name';
$string['headline_table_view'] = 'Template Wizard overview';
$string['info_no_courses'] = 'No course found in the selected templates category.';
$string['missingfullname'] = 'Please enter a full course name.';
$string['missingshortname'] = 'Please enter a short course name.';
$string['pluginname'] = 'Course Template Wizard';
$string['select_target_course'] = 'Target course';
$string['settings:templatetargetcourseexceptions'] = 'Template target course exceptions';
$string['settings:templatetargetcourseexceptions_desc'] = 'Comma-separated id\'s from courses, which can\'t be overwritten by a course template.';
$string['settings_choose_course'] = 'Select templates course category';
$string['settings_create_course'] = 'Create course from template';
$string['settings_serviceuserid'] = 'Service user ID';
$string['settings_serviceuserid_desc'] = 'Optional numeric user ID that performs backup/restore operations (should be a dedicated service account). Leave 0 to fall back to a site admin.';
$string['shortnamecourse'] = 'Short course name';
$string['table_head_courseimg'] = 'Preview image';
$string['table_head_coursename'] = 'Course name';
$string['table_head_coursetext'] = 'Description';
$string['table_head_edit'] = 'Use this template';
$string['template_applied'] = 'The template was successfully applied to the target course.';
$string['template_course_desc'] = 'Select the course category where the templates are located.';
$string['template_restore_page_no_data'] = 'The data to apply the overwrite process could not be found. This can happen, if you reload a page. Please resubmit this data.';
$string['template_restore_page_title'] = 'Overwriting course with course template.';
