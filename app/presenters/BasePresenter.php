<?php
/**
 * Base class for all application presenters.
 *
 * @author     mist
 * @package    HlidacObjednavek
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    public $mena;
    public $language;
    /**
     * Flash messages
     */
    public function afterRender()
    {
        if ($this->isAjax() && $this->hasFlashSession())
            $this->invalidateControl('flashes');//*/
    }
    
    protected function beforeRender()
        {
            $this->language = $this->context->params['language'];
            $this->mena = $this->context->params['currency'];
            $this->template->mena = $this->mena;
            $this->template->language = $this->language;
        }
}
