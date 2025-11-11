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
 * @package    local_ocbsbcoursecreation
 * @copyright   2025 Oncampus GmbH
 * @category   navigation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Extend the settings navigation (course administration) with a link to the template list.
 *
 * Shown to users with sufficient capability (trainer / course creator / plugin capability).
 *
 * @param settings_navigation $settingsnav Settings navigation node.
 * @param context $context Current context.
 * @return void
 */
function local_ocbsbcoursecreation_extend_settings_navigation(settings_navigation $settingsnav, context $context): void {
    global $PAGE;

    // Only on real course pages (not the frontpage).
    if (empty($PAGE->course) || (int)$PAGE->course->id === SITEID) {
        return;
    }

    $coursecontext = context_course::instance($PAGE->course->id);

    // Capability check: allow trainers, course creators, or holders of the plugin cap.
    if (
        !has_capability('moodle/course:update', $coursecontext) &&
        !has_capability('moodle/course:create', context_system::instance()) &&
        !has_capability('local/ocbsbcoursecreation:ocbsbcoursecreation_access_capability', $coursecontext)
    ) {
        return;
    }

    // Attach to course administration node.
    if ($coursenode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $label = get_string('headline_table_view', 'local_ocbsbcoursecreation');
        $url = new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php', [
            'targetcourseid' => $PAGE->course->id,
        ]);

        $node = navigation_node::create(
            $label,
            $url,
            navigation_node::NODETYPE_LEAF,
            'local_ocbsbcoursecreation',
            'local_ocbsbcoursecreation',
            new pix_icon('i/backup', $label)
        );

        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $node->make_active();
        }

        $coursenode->add_node($node);
    }
}

/**
 * Extend the secondary course navigation with a link to the template list.
 *
 * Appears as a tab next to items like "Participants".
 *
 * @param navigation_node $navigation The course secondary navigation node.
 * @param stdClass $course The current course record.
 * @param context_course $context The course context.
 * @return void
 */
function local_ocbsbcoursecreation_extend_navigation_course(
    navigation_node $navigation,
    stdClass $course,
    context_course $context
): void {

    // Capability check: allow trainers, course creators, or holders of the plugin cap.
    if (
        !has_capability('moodle/course:update', $context) &&
        !has_capability('moodle/course:create', context_system::instance()) &&
        !has_capability('local/ocbsbcoursecreation:ocbsbcoursecreation_access_capability', $context)
    ) {
        return;
    }

    $label = get_string('headline_table_view', 'local_ocbsbcoursecreation');
    $url = new moodle_url('/local/ocbsbcoursecreation/list_courses_to_copy.php', [
        'targetcourseid' => $course->id,
    ]);

    $navigation->add(
        $label,
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_ocbsbcoursecreation',
        new pix_icon('i/backup', $label)
    );
}

/**
 * Check if course is in school of user only by getting the Schoolnumbers in profile
 *
 * @param $course
 * @return bool
 * @throws dml_exception
 */
function local_ocbsbcoursecreation_is_course_in_school($courseid): bool {
    global $USER;

    $coursecategory = local_ocbsbcoursecreation_return_categoryitems($courseid);

    while (!empty($coursecategory->parent) && $coursecategory->parent !== 0) {
        $coursecategory = local_ocbsbcoursecreation_return_categoryitems_from_parent($coursecategory->parent);
    }

    $schoolnumbers = $USER->profile_field_schoolno ?? ($USER->profile['schoolno'] ?? '');

    $profileschools = array_map('trim', explode(',', $schoolnumbers));

    foreach ($profileschools as $profileschool) {
        if (!empty($coursecategory->idnumber) && strpos($coursecategory->idnumber, $profileschool) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Return Category items
 *
 * @param $courseid
 * @return false|mixed
 * @throws dml_exception
 */
function local_ocbsbcoursecreation_return_categoryitems($courseid): mixed {
    global $DB;

    $sql = "SELECT cc.idnumber, cc.parent
              FROM {course_categories} cc
              JOIN {course} c ON c.category = cc.id
             WHERE c.id = :courseid";
    $params = ['courseid' => $courseid];

    return $DB->get_record_sql($sql, $params);
}

/**
 * Return category items from parent category
 *
 * @param $categoryid
 * @return false|mixed|stdClass
 * @throws dml_exception
 */
function local_ocbsbcoursecreation_return_categoryitems_from_parent($categoryid): mixed {
    global $DB;

    return $DB->get_record("course_categories", ['id' => $categoryid]);
}

/** Get information if school is from iam
 *
 * @param $targetcourseid
 * @return stdClass|bool
 * @throws dml_exception
 */
function local_ocbsbcoursecreation_get_iamschool($targetcourseid): stdClass|bool {
    global $DB;

    if ($DB->record_exists('local_oc_iamexp', ['type' => "COURSE", "lms_id" => $targetcourseid])) {
        return $DB->get_record("local_oc_iamexp", ['type' => "COURSE", "lms_id" => $targetcourseid]);
    }

    return false;
}

/**
 * Update Record in iamexp table if record exists there
 *
 * @param $iamschool
 * @param $newcourseid
 * @return void
 * @throws dml_exception
 */
function local_ocbsbcoursecreation_update_iamschool($iamschool, $newcourseid): void {
    global $DB;

    if ($iamschool) {
        $iamschool->lms_id = $newcourseid;
        $DB->update_record('local_oc_iamexp', $iamschool);
    }
}
