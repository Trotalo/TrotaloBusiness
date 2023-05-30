<?php
abstract class TrotaloBusinessBaseManagerController extends modExtraManagerController {
    /** @var \trotalobusiness\TrotaloBusiness $trotalobusiness */
    public $trotalobusiness;

    public function initialize(): void
    {
        $this->trotalobusiness = $this->modx->services->get('trotalobusiness');

        $this->addCss($this->trotalobusiness->getOption('cssUrl') . 'mgr.css');
        $this->addJavascript($this->trotalobusiness->getOption('jsUrl') . 'mgr/trotalobusiness.js');

        $this->addHtml('
            <script type="text/javascript">
                Ext.onReady(function() {
                    trotalobusiness.config = '.$this->modx->toJSON($this->trotalobusiness->config).';
                });
            </script>
        ');

        parent::initialize();
    }

    public function getLanguageTopics(): array
    {
        return array('trotalobusiness:default');
    }

    public function checkPermissions(): bool
    {
        return true;
    }
}
