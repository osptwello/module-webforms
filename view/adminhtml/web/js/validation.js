(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery-ui-modules/widget',
            'jquery/validate',
            'mage/translate',
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    return function(validation){
        $.extend($.validator.prototype,{
            check: function (element) {
                element = this.validationTargetFor(this.clean(element));

                if (element.offsetWidth == 0 || element.offsetWidth == undefined)
                    return true;

                var rules = $(element).rules();
                var dependencyMismatch = false;
                var val = this.elementValue(element);
                var result;

                for (var method in rules) {
                    var rule = { method: method, parameters: rules[method] };
                    try {

                        result = $.validator.methods[method].call(this, val, element, rule.parameters);

                        // if a method indicates that the field is optional and therefore valid,
                        // don't mark it as valid when there are no other rules
                        if (result === "dependency-mismatch") {
                            dependencyMismatch = true;
                            continue;
                        }
                        dependencyMismatch = false;

                        if (result === "pending") {
                            this.toHide = this.toHide.not(this.errorsFor(element));
                            return;
                        }

                        if (!result) {
                            this.formatAndAdd(element, rule);
                            return false;
                        }
                    } catch (e) {
                        if (this.settings.debug && window.console) {
                            console.log("exception occurred when checking element " + element.id + ", check the '" + rule.method + "' method", e);
                        }
                        throw e;
                    }
                }
                if (dependencyMismatch) {
                    return;
                }
                if (this.objectLength(rules)) {
                    this.successList.push(element);
                }
                return true;
            }
        });
        return validation;
    };
}));
