/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */

define(['jquery'],
    function ($) {

        var change_course_name = function (input_fields, enclosing, seperator_on) {
            var input_fullname = '';
            let input_shortname = '';
            let prefix_set = document.getElementById("id_add_prefix") ? document.getElementById("id_add_prefix").checked : false;
            let field_len = input_fields.length;
            let post_fix = seperator_on ? '' : ' ';
            for (let i = 0; field_len > i; i++) {
                let is_prefix = input_fields[i].classList.contains('prefix');
                if (input_fields[i].classList.contains('input_type')) {
                    var select = input_fields[i].getElementsByTagName('input')[0];
                    if (!is_prefix) {
                        input_fullname += select.value + post_fix;
                        input_shortname += select.value.substr(0, 3) + post_fix;
                    } else if (is_prefix && prefix_set) {
                        input_fullname += select.value + post_fix;
                    }
                } else if (input_fields[i].classList.contains('select_type')) {
                    let select = input_fields[i].getElementsByTagName('select')[0];
                    let selected_option = select.options[select.selectedIndex].text;
                    selected_option_cut = selected_option.substr(1, selected_option.length - 2);
                    if (prefix_set && is_prefix) {
                        input_fullname += selected_option_cut + post_fix;
                    } else if (selected_option[0] === enclosing && selected_option[selected_option.length - 1] === enclosing
                        && !is_prefix) {
                        input_fullname += selected_option_cut + post_fix;
                        input_shortname += selected_option_cut + post_fix;
                    } else if (!is_prefix) {
                        input_shortname += selected_option + post_fix;
                    }
                }
            }
            document.getElementById('id_fullname').value = input_fullname;
            document.getElementById('id_shortname').value = input_shortname;
        }

        return {
            init: function (args) {
                var trigger = document.getElementsByClassName("modifying_type");
                for (let i = 0; i < trigger.length; i++) {
                    if (trigger[i].classList.contains('input_type')) {
                        trigger[i].addEventListener('keyup',e =>
                            change_course_name(trigger, args.enclosing, args.seperator));
                    } else if (trigger[i].classList.contains('select_type')) {
                        trigger[i].getElementsByTagName('select')[0]
                            .addEventListener('click',e =>
                                change_course_name(trigger, args.enclosing, args.seperator));
                    }
                }
            }
        }
    });