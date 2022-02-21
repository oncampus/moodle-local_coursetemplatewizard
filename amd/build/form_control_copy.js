/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */

define([],
    function () {
        var trigger = document.getElementsByClassName("modifying_type");

        for (let i = 0; i < trigger.length; i++) {
            console.log(i);
            console.log(trigger[i]);
            if (trigger[i].classList.contains('input_type')) {
                trigger[i].addEventListener('keyup', e => change_course_name(trigger));
            } else if (trigger[i].classList.contains('select_type')) {
                trigger[i].getElementsByTagName('select')[0].addEventListener('change', e => change_course_name(trigger));
            } else {
                console.log(trigger[i].classList);
            }
        }
    }
)

var change_course_name = function (input_fields) {
    let input_fullname = '';
    let input_shortname = '';
    var prefix_set = document.getElementById("id_add_prefix").checked;
    var field_len = input_fields.length;
    for (let i = 0; field_len > i; i++) {
        let spacing = field_len - 1 > i ? " " : "";
        if (input_fields[i].classList.contains('input_type')) {
            var is_prefix = input_fields[i].classList.contains('prefix');
            var select = input_fields[i].getElementsByTagName('input')[0];
            if (!is_prefix) {
                input_fullname += select.value;
                input_shortname += select.value.substr(0, 3) + spacing;
                input_fullname += i < 2 ? ":" : " -" ;
                input_fullname += spacing;
            } else if (is_prefix && prefix_set) {
                input_fullname += select.value;
                input_fullname += " - ";
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