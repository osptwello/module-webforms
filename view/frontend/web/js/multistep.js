define(['VladimirPopov_WebForms/js/form'], function (VarienForm) {

    function JsWebFormsMultistep() {}

    JsWebFormsMultistep.prototype.stepNext = function (el, formId) {
        if (!el) return;
        var form = new VarienForm('webform_' + formId);
        var current_fs = $(el).up().up().up();
        current_fs.scrollIntoView();

        var next_fs = current_fs.next();

        // get the fieldset
        var fieldset = next_fs.down();
        // if fieldset has no displayed items it has a style attribute
        if (fieldset.getAttribute("style")) {
            if (fieldset.getAttribute("style").match(/display: *none/i) !== null) {
                if (form.validator && form.validator.validate()) {
                    var nextEl = next_fs.select('.action-next')[0];
                    this.stepNext(nextEl, formId);
                }
            }
        }

        if (form.validator && form.validator.validate()) {
            Effect.Appear(next_fs, {duration: 0.5});
            current_fs.setStyle({'position': 'absolute', 'visibility': 'hidden'});
        }
    };

    JsWebFormsMultistep.prototype.stepPrevious = function (el) {
        var current_fs = $(el).up().up();
        current_fs.scrollIntoView();

        if (current_fs.className !== 'form-step') current_fs = current_fs.up();
        var previous_fs = current_fs.previous();

        // get the fieldset
        var fieldset = previous_fs.down();

        // if fieldset has no displayed items it has a style attribute
        if (fieldset.getAttribute("style")) {
            if (fieldset.getAttribute("style").match(/display: *none/i) !== null) {
                var prevEl = previous_fs.select('.action-previous')[0];
                this.stepPrevious(prevEl);
            }
        }
        previous_fs.setStyle({'position': 'inherit', 'visibility': 'visible'});
        current_fs.hide();
    };

    return JsWebFormsMultistep;
});
