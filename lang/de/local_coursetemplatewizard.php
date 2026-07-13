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
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @category    string
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apply_template'] = 'Vorlage anwenden';
$string['confirm_overwrite_checkbox'] = 'Ich habe verstanden: Der ausgewählte Zielkurs wird vollständig mit der Vorlage überschrieben.';
$string['confirm_overwrite_message'] = 'Achtung! Der ausgewählte Zielkurs wird mit der gewählten Vorlage überschrieben. Möchten Sie fortfahren?';
$string['confirm_overwrite_no'] = 'Nein, abbrechen';
$string['confirm_overwrite_note'] = 'Achtung: Dieser Vorgang ist endgültig und kann <strong>nicht</strong> rückgängig gemacht werden.';
$string['confirm_overwrite_required'] = 'Bitte bestätigen Sie die Überschreibung, indem Sie die Checkbox aktivieren.';
$string['confirm_overwrite_title'] = 'Kurs überschreiben';
$string['confirm_overwrite_yes'] = 'Ja, überschreiben';
$string['course_format'] = 'Kursbereich';
$string['coursesummary'] = 'Kursbeschreibung';
$string['coursetemplateserviceuser_firstname'] = 'Vorlagenkurse';
$string['coursetemplateserviceuser_lastname'] = 'Service user';
$string['coursetemplateserviceuserrole_desc'] = 'Ein Service User für Vorlagenkurse ist ein interner User, der den Backup- und Restore-Prozess durchführt und auf den Zielkurs anwendet.';
$string['coursetemplateserviceuserrole_name'] = 'Vorlagenkurse Service User';
$string['coursetemplatewizard:use'] = 'Zugriff auf Kursvorlagen und Überschreiben von Kursen';
$string['creation_page_title'] = 'Vorlagen-Assistent';
$string['errornocourseoverwriterights'] = 'Sie haben nicht die Berechtigung, diesen Kurs mit einer Kursvorlage zu überschreiben.';
$string['errornotteacherincourse'] = 'Sie sind nicht in diesem Kurs eingeschrieben, deswegen können Sie den Vorlagen-Assistent nicht für diesen Kurs nutzen.';
$string['form_copy_course_name_header'] = 'Kursdaten';
$string['form_copy_description'] = 'Wählen Sie den Zielkurs, der mit der Vorlage überschrieben wird.';
$string['form_copy_header'] = 'Kursbild auswählen';
$string['form_copy_image_desc'] = 'Wählen Sie ein Kursbild aus oder laden Sie ein eigenes Bild für den Zielkurs hoch.';
$string['form_copy_select_default'] = 'Zielkurs auswählen';
$string['fullnamecourse'] = 'Vollständiger Kursname';
$string['headline_table_view'] = 'Vorlagen-Assistent';
$string['info_no_courses'] = 'Es wurde kein Kurs in der ausgewählten Kursvorlagen-Kategorie gefunden.';
$string['missingfullname'] = 'Bitte geben Sie einen vollständigen Kursnamen ein.';
$string['missingshortname'] = 'Bitte geben Sie einen kurzen Kursnamen ein.';
$string['pluginname'] = 'Vorlagenkurs-Assistent';
$string['privacy:metadata'] = 'Der Vorlagenkurs-Assistent ermöglicht lediglich einen einfachen Weg standartisierte Kurse zu erstellen und speichert keine personenbezogen Daten für diesen Prozess.';
$string['select_target_course'] = 'Zielkurs';
$string['settings:templatetargetcourseexceptions'] = 'Vorlagenziel-Ausnahmen';
$string['settings:templatetargetcourseexceptions_desc'] = 'Kommagetrennte Id\'s von Kursen, die nicht durch eine Kursvorlage überschrieben werden können.';
$string['settings_choose_course'] = 'Vorlagen-Kursbereich auswählen';
$string['settings_create_course'] = 'Kurs aus Vorlage erstellen';
$string['settings_serviceuserid'] = 'Service-Nutzer-ID';
$string['settings_serviceuserid_desc'] = 'Optionale numerische Nutzer-ID, die die Backup/Restore-Vorgänge ausführt (empfohlen ist ein eigenes Service-Konto). 0 bedeutet: es wird wie bisher ein Seiten-Admin verwendet.';
$string['shortnamecourse'] = 'Kurzer Kursname';
$string['table_head_courseimg'] = 'Vorschaubild';
$string['table_head_coursename'] = 'Kursname';
$string['table_head_coursetext'] = 'Beschreibung';
$string['table_head_edit'] = 'Diese Vorlage verwenden';
$string['template_applied'] = 'Die Vorlage wurde erfolgreich auf den Zielkurs angewendet.';
$string['template_course_desc'] = 'Wählen Sie den Kursbereich, in dem sich die Kursvorlagen befinden.';
$string['template_restore_page_no_data'] = 'Die Daten für den Überschreibungsvorgang konnten nicht gefunden werden. Dies kann passieren, wenn Sie eine Seite neu laden. Bitte senden Sie diese Daten erneut.';
$string['template_restore_page_title'] = 'Überschreibe Kurs mit Kursvorlage.';
