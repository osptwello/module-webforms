define([
    'Magento_Ui/js/grid/columns/column',
    'ko',
    'jquery',
    'mage/translate',
    'VladimirPopov_WebForms/js/jquery.morelines'
], function (Column, ko, $, __) {
    'use strict';

    var self;

    ko.bindingHandlers.applyReadmore= {
        init: function(element, valueAccessor, allBindingsAccessor, viewModel) {

            $( document ).ready(function() {
                $(element).moreLines({
                    linecount: 5,
                    baseclass: 'b-description',
                    basejsclass: 'js-description',
                    classspecific: '_readmore',
                    buttontxtmore: __('Read more'),
                    buttontxtless: __('Close'),
                    animationspeed: 250
                });
            });

        }
    };

    return Column.extend({
        defaults: {
            bodyTmpl: 'VladimirPopov_WebForms/grid/columns/textarea'
        },

        initialize: function () {
            self = this;
            this._super();


        },

        /**
         * Ment to preprocess data associated with a current columns' field.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLabel: function (record) {

            return this.nl2br(record[this.index]);
        },

        nl2br: function (str, is_xhtml) {
            if (typeof str === 'undefined' || str === null) {
                return '';
            }
            var breakTag = '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        }

    });
});
