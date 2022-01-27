/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */

define(['jquery', 'core/modal_factory', 'core/str', "core/modal_events", 'core/ajax', 'core/notification'],
    function ($, ModalFactory, String, ModalEvents, Ajax, Notification) {

        var trigger = document.getElementsByClassName("modifying_type");

        for (let i = 0; i < trigger.length; i++) {
            if (trigger[i].tagName == 'INPUT') {
                trigger[i].addEventListener('keyup', e => change_course_fullname());
            } else if(trigger[i].classList.contains('select_type')){
                trigger[i].getElementsByTagName('select')[0].addEventListener('change', e => change_course_fullname());
            } else{
                console.log(input_fields[i].classList);
            }
        }
    }
)

var change_course_fullname = function () {
    var input_fullname = '';
    var input_shortname = '';
    var input_fields = document.getElementsByClassName("modifying_type");
    var first = true;
    for (let i = 0; input_fields.length > i; i++) {
        if (input_fields[i].tagName === 'INPUT') {
            if (first) {
                input_fullname += input_fields[i].value + ": ";
                first = false;
            } else {
                input_fullname += input_fields[i].value + " ";
            }
            input_shortname += input_fields[i].value.substr(0, 3) + " ";
        } else if(input_fields[i].classList.contains('select_type')){
            var select = input_fields[i].getElementsByTagName('select')[0];
           input_fullname +=select.options[select.selectedIndex].text + " ";
           input_shortname +=  select.options[select.selectedIndex].text + " ";
        }
    }
    document.getElementById('id_fullname').value = input_fullname;
    document.getElementById('id_shortname').value = input_shortname;
}