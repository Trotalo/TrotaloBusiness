trotalobusiness.page.Manage = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [
            {
                xtype: 'trotalobusiness-panel-manage',
                renderTo: 'trotalobusiness-panel-manage-div'
            }
        ]
    });
    trotalobusiness.page.Manage.superclass.constructor.call(this, config);
};
Ext.extend(trotalobusiness.page.Manage, MODx.Component);
Ext.reg('trotalobusiness-page-manage', trotalobusiness.page.Manage);
