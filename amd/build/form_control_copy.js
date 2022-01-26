/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */

define(['jquery', 'core/modal_factory', 'core/str', "core/modal_events", 'core/ajax', 'core/notification'],
    function ($, ModalFactory, String, ModalEvents, Ajax, Notification) {

        var trigger = document.querySelectorAll("[name='type']");
        for (let i = 0; i < trigger.length; i++) {
            if (trigger[i].tagName == 'SELECT') {
                trigger[i].addEventListener('change', e => change_course_fullname());
            } else {
                trigger[i].addEventListener('keyup', e => change_course_fullname());
            }
        }
    }
)

var change_course_fullname = function () {
    var input = '';
    var input_fields = document.querySelectorAll("[name='type']");
    for (let i = input_fields.length; i > 0; i--) {
        if (input_fields[i].tagName !== 'SELECT') {
            input += input_fields[i].value + ": ";
        } else {
            input += input_fields[i].options[input_fields[i].selectedIndex].text+" ";
        }
    }
    document.getElementById('id_fullname').value = input;
};

