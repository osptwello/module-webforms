var config = {
    config: {
        mixins: {
            'mage/validation': {
                'VladimirPopov_WebForms/js/validation' : false
            }
        }
    },
    map: {
        '*': {
            logic: 'VladimirPopov_WebForms/js/logic',
            webformsRegion: 'VladimirPopov_WebForms/js/region',
        }
    }

};
