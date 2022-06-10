/**
 *  Show a delete modal instead of doing it in a seperated page
 *  @module local_message
 */
import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import * as String from 'core/str';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
export const blubb = () => {
        var trigger = $('.action-delete');
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: String.get_string('modal_delete_title', 'local_oc_course_creation'),
            body: String.get_string('modal_delete_message', 'local_oc_course_creation'),
            large: true,
            // Get id before modal is displayed
            preShowCallback: function(triggerElement, modal) {
                triggerElement = $(triggerElement);
                let id = triggerElement[0].classList[0].substr(3);
                modal.params = {'id': id};
                modal.setSaveButtonText(String.get_string('modal_delete_button', 'local_oc_course_creation'));
            },
        }, trigger)
            .done(function(modal) {
                modal.getRoot().on(ModalEvents.save, function(e) {
                    let footer = Y.one('.modal-footer');
                    footer.setContent('Deleting');
                    let spinner = M.util.add_spinner(Y, footer);
                    spinner.show();
                    e.preventDefault();
                    Y.log(modal.params);
                    let request = {
                        methodname: 'local_oc_course_creation_custom_preset_delete',
                        args: modal.params,
                    };

                    Ajax.call([request])[0].done(function(data) {
                        if (data === true) {
                            window.location.reload();
                            Y.log('deleted message ' + modal.params);
                        } else {
                            Notification.addNotification({
                                message: String.get_string('modal_delete_preset_failed', 'local_oc_course_creation'),
                                type: 'error'
                            });
                        }
                    }).fail(Notification.exception);

                });
            });
    };
