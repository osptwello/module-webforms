define(['jquery', 'VladimirPopov_WebForms/js/jquery.magnific-popup'], function ($) {
    function popup(options, node) {
        var o = {
            container: '',
            modalClass: ''
        };
        for (var k in options) {
            if (options.hasOwnProperty(k)) o[k] = options[k];
        }

        $(node).magnificPopup({
            items: {
                src: o.container
            },
            mainClass: o.modalClass,
            closeOnContentClick: false,
            closeOnBgClick: false,
            type: 'inline'
        });
    }

    return popup;
});