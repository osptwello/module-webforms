define([
    'ko',
    'mageUtils',
    'Magento_Ui/js/grid/tree-massactions'
], function (ko, utils, Massactions) {
    'use strict';
    return Massactions.extend({
        defaults: {
            email: '',
            submenuTemplate: 'VladimirPopov_WebForms/grid/input-submenu'
        },

        /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];
            selections['input'] = action.input;

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            utils.submit({
                url: action.url,
                data: selections
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
