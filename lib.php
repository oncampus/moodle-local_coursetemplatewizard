<?php
// This file is part of Moodle - http://moodle.org/
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
 * Local plugin library callbacks.
 *
 * @package     local_coursetemplatewizard
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @category    navigation
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Extend the settings navigation (course administration) with a link to the template list.
 *
 * Shown to users with sufficient capability (trainer / course creator / plugin capability).
 *
 * @param settings_navigation $settingsnav Settings navigation node.
 * @param context $context Current context.
 * @return void
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function local_coursetemplatewizard_extend_settings_navigation(settings_navigation $settingsnav, context $context): void {
    global $PAGE;
    if ($context->contextlevel != CONTEXT_COURSE) {
        return;
    }
    $course = get_course($context->instanceid);
    if ($course->id == SITEID) {
        return;
    }
    $templatetargetcourseexceptionsconfig = get_config('local_coursetemplatewizard', 'templatetargetcourseexceptions');
    $templatetargetcourseexceptions = explode(',', $templatetargetcourseexceptionsconfig);
    if (in_array($course->id, $templatetargetcourseexceptions, true)) {
        return;
    }
    // Capability check: only users, which are allowed to use the plugin in the current course context, see the navigation item.
    if (!has_capability('local/coursetemplatewizard:use', $context)) {
        return;
    }

    // Attach to course administration node.
    if ($coursenode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $label = get_string('headline_table_view', 'local_coursetemplatewizard');
        $url = new moodle_url('/local/coursetemplatewizard/list_courses_to_copy.php', [
            'targetcourseid' => $course->id,
        ]);

        $node = navigation_node::create(
            $label,
            $url,
            navigation_node::NODETYPE_LEAF,
            'local_coursetemplatewizard',
            'local_coursetemplatewizard',
            new pix_icon('i/backup', $label)
        );

        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $node->make_active();
        }

        $coursenode->add_node($node);
    }
}
