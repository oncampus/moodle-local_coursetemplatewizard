/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */

define(['jquery'],
    function ($) {
        var change_course_name = function (input_fields, seperator_on) {
            var input_fullname = '';
            let input_shortname = '';
            let prefix_set = document.getElementById("id_add_prefix") ? document.getElementById("id_add_prefix").checked : false;
            let field_len = input_fields.length;
            let seperator = seperator_on === '1' ? '' : ' ';
            for (let i = 0; field_len > i; i++) {
                let is_prefix = input_fields[i].classList.contains('prefix');
                let select = null;
                let shortselect = null;
                if (input_fields[i].classList.contains('input_type')) {
                    select = input_fields[i].getElementsByTagName('input')[0].value;
                    shortselect = select.substring(0, 3);
                } else if (input_fields[i].classList.contains('select_type')) {
                    select = input_fields[i].getElementsByTagName('select')[0].value;
                }
                if (!is_prefix) {
                    input_fullname += select;
                    input_shortname += shortselect ? shortselect : select;
                } else if (is_prefix && prefix_set) {
                    input_fullname += select;
                }

                // special separation with no infix settings
                if (seperator_on === '0') {
                    if (prefix_set) {
                        input_fullname += i === 0 || i === 2 ? ' -' : i === 1 ? ':' : '';
                    } else {
                        input_fullname += i === 2 ? ' -' : i === 1 ? ':' : '';
                    }
                }
                input_fullname += (prefix_set && is_prefix) || !is_prefix ? seperator : '';
                input_shortname += !is_prefix ? seperator : '';
            }
            document.getElementById('id_fullname').value = input_fullname;
            document.getElementById('id_shortname').value = input_shortname;
        };

        return {
            init: function (args) {
                var trigger = document.getElementsByClassName("modifying_type");
                change_course_name(trigger, args.seperator);
                for (let i = 0; i < trigger.length; i++) {
                    if (trigger[i].classList.contains('input_type')) {
                        trigger[i].addEventListener('keyup', e =>
                            change_course_name(trigger, args.seperator));
                    } else if (trigger[i].classList.contains('select_type')) {
                        trigger[i].getElementsByTagName('select')[0]
                            .addEventListener('click', e =>
                                change_course_name(trigger, args.seperator));
                    }
                }
                if (document.getElementById("id_add_prefix")) {
                    document.getElementById("id_add_prefix").onclick = function () {
                        change_course_name(trigger, args.seperator);
                    };
                }
            }
        };
    })
;