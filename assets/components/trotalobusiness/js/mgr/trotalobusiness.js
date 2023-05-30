var trotalobusiness = function (config) {
    config = config || {};
    trotalobusiness.superclass.constructor.call(this, config);
};
Ext.extend(trotalobusiness, Ext.Component, {

    page: {},
    window: {},
    grid: {},
    tree: {},
    panel: {},
    combo: {},
    field: {},
    config: {},

});
Ext.reg('trotalobusiness', trotalobusiness);
trotalobusiness = new trotalobusiness();
