define([
    'Magento_Ui/js/grid/tree-massactions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate',
    'ko'
], function (Massactions, uiAlert, _, $, $t, ko) {
    'use strict';

    return Massactions.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                massaction: 'onAction'
            },
            email: '',
            submenuTemplate: 'VladimirPopov_WebForms/grid/input-submenu'
        },

        /**
         * Reload customer results listing
         *
         * @param {Object} data
         */
        onAction: function (data) {

            // Split subActions
            var action = data.action.split('.');
            switch (action[0]) {
                case 'delete':
                case 'status': {
                    this.source.reload({
                        refresh: true
                    });
                    break;
                }
            }
        },

        /**
         * Default action callback. Send selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType, selections;

            if (action.isAjax) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                selections = {};

                selections[itemsType] = data[itemsType];
                selections['input'] = action.input;

                if (!selections[itemsType].length) {
                    selections[itemsType] = false;
                }

                _.extend(selections, data.params || {});

                this.request(action.url, selections).done(function (response) {
                    if (!response.error) {
                        this.trigger('massaction', {
                            action: action.type,
                            data: selections
                        });
                    }
                }.bind(this));
            } else {
                this._super();
            }
        },

        /**
         * Send customer results listing mass action ajax request
         *
         * @param {String} href
         * @param {Object} data
         */
        request: function (href, data) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: data
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    }
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        },

        /**
         * Recursive initializes observable actions.
         *
         * @param {Array} actions - Action objects.
         * @param {String} [prefix] - An optional string that will be prepended
         *      to the "type" field of all child actions.
         * @returns {Massactions} Chainable.
         */
        recursiveObserveActions: function (actions, prefix) {
            _.each(actions, function (action) {
                if (prefix) {
                    action.type = prefix + '.' + action.type;
                }

                // custom code to show input
                action.showInput = action.type.substr(action.type.lastIndexOf('.'), action.type.lastIndexOf('.') + 5) === '.input';
                action.input = '';

                if (action.actions) {
                    action.visible = ko.observable(false);
                    action.parent = actions;
                    this.recursiveObserveActions(action.actions, action.type);
                }
            }, this);

            return this;
        }
    });
});
