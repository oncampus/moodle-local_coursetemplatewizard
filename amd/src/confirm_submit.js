define(['jquery'], function($) {
    return {
        init: function() {
            const form = $('form.mform');
            const submitBtn = $('.js-confirm-submit');

            submitBtn.on('click', function(e) {
                e.preventDefault();
                $('#confirmModal').modal('show');
            });

            $('#confirmOverwrite').on('click', function() {
                form.submit();
            });
        }
    };
});
