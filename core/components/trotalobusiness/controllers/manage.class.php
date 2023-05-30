<?php
require_once dirname(dirname(__FILE__)) . '/index.class.php';

class TrotaloBusinessManageManagerController extends TrotaloBusinessBaseManagerController
{

    public function process(array $scriptProperties = []): void
    {
    }

    public function getPageTitle(): string
    {
        return $this->modx->lexicon('trotalobusiness');
    }

    public function loadCustomCssJs(): void
    {
        $this->addLastJavascript($this->trotalobusiness->getOption('jsUrl') . 'mgr/widgets/manage.panel.js');
        $this->addLastJavascript($this->trotalobusiness->getOption('jsUrl') . 'mgr/sections/manage.js');

        $this->addHtml(
            '
            <script type="text/javascript">
                Ext.onReady(function() {
                    MODx.load({ xtype: "trotalobusiness-page-manage"});
                });
            </script>
        '
        );
    }

    public function getTemplateFile(): string
    {
        return $this->trotalobusiness->getOption('templatesPath') . 'manage.tpl';
    }

}
