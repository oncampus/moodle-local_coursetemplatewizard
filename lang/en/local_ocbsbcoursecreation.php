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
 * @package     local_ocbsbcoursecreation
 * @category    string
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course Templates & Copying';
$string['creation_page_title'] = 'Course Templates';
$string['headline_table_view'] = 'Course Template Overview';
$string['table_head_coursename'] = 'Course Name';
$string['table_head_courseimg'] = 'Preview Image';
$string['table_head_coursetext'] = 'Description';
$string['table_head_edit'] = 'Use this Template';
$string['course_format'] = 'Course Category';
$string['info_no_courses'] = 'No courses were found in the selected course template category.';

$string['settings_choose_course'] = 'Select template course category';
$string['settings_create_course'] = 'Create course from template';
$string['template_course_desc'] = 'Select the course category containing the course templates.';

$string['form_copy_image_desc'] = 'Choose a course image or upload your own image for the target course.';
$string['form_copy_image_header'] = 'Select Course Image';
$string['form_copy_course_name_header'] = 'Course Data';
$string['form_copy_description'] = 'Select the target course to overwrite with the template.';
$string['form_copy_select_default'] = 'Select Target Course';
$string['select_target_course'] = 'Target Course';

$string['fullnamecourse'] = 'Full Course Name';
$string['shortnamecourse'] = 'Short Course Name';
$string['coursesummary'] = 'Course Description';
$string['missingfullname'] = 'Please enter a full course name.';
$string['missingshortname'] = 'Please enter a short course name.';

$string['apply_template'] = 'Apply Template';
$string['template_applied'] = 'The template has been successfully applied to the target course.';

$string['confirm_overwrite_title'] = 'Overwrite Course';
$string['confirm_overwrite_message'] = 'Warning! The selected target course will be overwritten with the chosen template. Do you want to continue?';
$string['confirm_overwrite_yes'] = 'Yes, overwrite';
$string['confirm_overwrite_no'] = 'No, cancel';

$string['ocbsbcoursecreation:ocbsbcoursecreation_access_capability'] = 'Access to course templates and copy functionality';
