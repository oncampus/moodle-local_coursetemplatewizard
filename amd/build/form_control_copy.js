/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */

define(['jquery', 'core/modal_factory', 'core/str', "core/modal_events", 'core/ajax', 'core/notification'],
    function ($, ModalFactory, String, ModalEvents, Ajax, Notification) {

        var trigger = document.getElementsByClassName("modifying_type");

        for (let i = 0; i < trigger.length; i++) {
            if (trigger[i].tagName == 'INPUT') {
                trigger[i].addEventListener('keyup', e => change_course_name());
            } else if (trigger[i].classList.contains('select_type')) {
                trigger[i].getElementsByTagName('select')[0].addEventListener('change', e => change_course_name());
            } else {
                console.log(input_fields[i].classList);
            }
        }
    }
)

var change_course_name = function () {
    var input_fullname = '';
    var input_shortname = '';
    var input_fields = document.getElementsByClassName("modifying_type");
    var first = true;
    var prefix_set = document.getElementById("id_add_prefix").checked;
    for (let i = 0; input_fields.length > i; i++) {
        let spacing = input_fields.length - 1 > i ? " " : "";
        if (input_fields[i].tagName === 'INPUT') {
            var is_prefix = input_fields[i].classList.contains('prefix');
            if (!is_prefix) {
                input_fullname += input_fields[i].value + spacing;
                input_shortname +=  input_fields[i].value.substr(0, 3) + spacing;
                if (first) {
                    input_fullname += ": ";
                    first = false
                }
            }  else if (is_prefix && prefix_set) {
                input_fullname += input_fields[i].value;
                input_fullname += "- ";
            }

        } else if (input_fields[i].classList.contains('select_type')) {
            var select = input_fields[i].getElementsByTagName('select')[0];
            input_fullname += select.options[select.selectedIndex].text + spacing;
            input_shortname += select.options[select.selectedIndex].text + spacing;
        }
    }
    document.getElementById('id_fullname').value = input_fullname;
    document.getElementById('id_shortname').value = input_shortname;
}