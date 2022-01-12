/* WebForms 2.9.3 */
define(['prototype'], function () {

    var Targets = [];

    function JsWebFormsLogicRuleCheck(logic, prefix) {
        var FLAG = false;
        var field = $$('[name="' + prefix + '[field][' + logic['field_id'] + ']"]');
        var field_type = 'select';
        var selected = 'selected';
        if (typeof (field[0]) != 'object') {
            input = $$('[name="' + prefix + '[field][' + logic['field_id'] + '][]"]');
            field_type = 'checkbox';
            selected = 'checked';
            if (input[0] && input[0].type && input[0].type == 'select-multiple') {
                input = input[0];
                selected = 'selected';
            }
        } else {
            if (field[0].type == 'radio') {
                field_type = 'radio';
                input = field;
                selected = 'checked';
            }
        }
        var value;
        if (field_type == 'select') {
            var input = {'option': {'value': field[0].getValue(), selected: true}};
        }
        if (logic['aggregation'] == 'any' || (logic['aggregation'] == 'all' && logic['logic_condition'] == 'notequal')) {
            if (logic['logic_condition'] == 'equal') {
                for (var k in input) {
                    if (typeof (input[k]) == 'object' && input[k]) {
                        if (input[k][selected]) {
                            for (var j in logic['value']) {
                                FieldIsVisible(logic["field_id"]) ? value = input[k].value : value = false;
                                if (value == logic['value'][j]) FLAG = true;
                            }
                        }
                    }
                }
            } else {
                FLAG = true;
                var checked = false;
                for (var k in logic['value']) {
                    for (var j in input) {
                        if (typeof (input[j]) == 'object' && input[j])
                            if (input[j][selected]) {
                                checked = true;
                                FieldIsVisible(logic["field_id"]) ? value = input[j].value : value = false;
                                if (value == logic['value'][k])
                                    FLAG = false;
                            }
                    }
                }
                if (!checked) FLAG = false;
            }
        } else {
            FLAG = true;
            for (var k in logic['value']) {
                for (var j in input) {
                    if (typeof (input[j]) == 'object' && input[j])
                        FieldIsVisible(logic["field_id"]) ? value = input[j].value : value = false;
                    if (!input[j][selected] && value == logic['value'][k])
                        FLAG = false;
                }
            }
        }
        return FLAG;
    }

    function JsWebFormsLogicTargetCheck(target, logicRules, fieldMap, prefix) {
        if (typeof (target) != 'object') return false;
        for (var i in logicRules) {
            if (typeof (logicRules[i]) == 'object') {
                for (var j in logicRules[i]['target']) {
                    if (typeof (target) == 'object') {
                        if (target["id"] === logicRules[i]['target'][j]) {
                            var FLAG = JsWebFormsLogicRuleCheck(logicRules[i], prefix);
                            var currentRule = logicRules[i];
                            var display = 'block';
                            var targetId = target["id"];

                            if (FLAG) {
                                if (currentRule['action'] === 'hide') {
                                    display = 'none';
                                }
                                Targets[target["id"]] = {
                                    display: display,
                                    flag: true
                                };
                            } else {
                                if (currentRule['action'] === 'show') {
                                    display = 'none';
                                }
                                if (Targets[target["id"]]) {
                                    if (!Targets[target["id"]].flag) {
                                        Targets[target["id"]] = {
                                            display: display,
                                            flag: false
                                        };
                                    }
                                } else {
                                    Targets[target["id"]] = {
                                        display: display,
                                        flag: false
                                    };
                                }
                            }

                            if ($(target["id"] + '_container') !== null && typeof($(target["id"] + '_container')) == 'object' && $(target["id"] + '_container').style) {
                                $(target["id"] + '_container').style.display =Targets[target["id"]].display;
                                if (Targets[target["id"]].display === 'none') {
                                    $(target["id"] + '_container').getElementsBySelector('.required-entry').each(function (s, i) {
                                        s.disable();
                                    });
                                } else {
                                    $(target["id"] + '_container').getElementsBySelector('.required-entry').each(function (s, i) {
                                        s.enable();
                                    });
                                }
                            }

                            if (FLAG) {
                                for (var k in logicRules) {
                                    if (typeof (logicRules[k]) == 'object' && logicRules[k] !== currentRule) {
                                        var nextRule = logicRules[k];
                                        if (typeof (target) == 'object') {
                                            var fieldsetId = target["id"];
                                            var nextFieldId = 'field_' + nextRule['field_id'];
                                            if (target["id"] === 'field_' + nextRule['field_id'] || FieldInFieldset(nextRule['field_id'], fieldsetId, fieldMap)) {
                                                for (var n in nextRule['target']) {
                                                    var visibility;
                                                    if (nextRule['action'] === 'show') visibility = 'hidden';
                                                    if (nextRule['action'] === 'hide') visibility = 'visible';
                                                    if (typeof (nextRule['target'][n]) == 'string') {
                                                        var newTarget = {
                                                            'id': nextRule['target'][n],
                                                            'logic_visibility': visibility
                                                        };
                                                        JsWebFormsLogicTargetCheck(newTarget, logicRules, fieldMap, prefix);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function Admin_JSWebFormsLogic(targets, logicRules, prefix, fieldMap) {
        for (var n in logicRules) {
            var config = logicRules[n];
            if (typeof (config) == 'object') {
                var input = $$('[name="' + prefix + '[field][' + config['field_id'] + ']"]');
                var trigger_function = 'onchange';
                if (typeof (input[0]) != 'object') {
                    input = $$('[name="' + prefix + '[field][' + config['field_id'] + '][]"]');
                    trigger_function = 'onclick';
                    if (input[0] !== undefined && input[0].type === 'select-multiple')
                        trigger_function = 'onchange';
                } else {
                    if (input[0] !== undefined && input[0].type === 'radio') {
                        trigger_function = 'onclick';
                    }
                }
                for (var i in input) {
                    if (trigger_function === 'onchange') {
                        input[i].onchange = function () {
                            Targets = [];
                            for (var k in targets)
                                JsWebFormsLogicTargetCheck(targets[k], logicRules, fieldMap, prefix);
                        }
                        if (input[i].value) {
                            input[i].onchange();
                        }
                    } else {
                        input[i].onclick = function () {
                            Targets = [];
                            for (var k in targets)
                                JsWebFormsLogicTargetCheck(targets[k], logicRules, fieldMap, prefix);
                        }
                        if (input[i].value) {
                            input[i].onclick();
                        }
                    }
                }
            }
        }
    }

    function FieldIsVisible(fieldId) {
        var el = $('field_' + fieldId + '_container');
        if (el !== null) {
            if (el.offsetWidth == 0 || el.offsetWidth === undefined) return false;
        } else {
            return false;
        }
        return true;
    }

    function FieldInFieldset(fieldId, fieldsetId) {
        if (typeof fieldsetId != 'string') return false;
        var el = $$('#fieldset_' + fieldsetId.replace('fieldset_', '') + '_container #field_' + fieldId);
        if (el.length > 0) return true;
        return false;
    }

    return Admin_JSWebFormsLogic;
});
