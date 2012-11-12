<?php
/**
 * Base class for all application presenters.
 *
 * @author     mist
 * @package    HlidacObjednavek
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    
    public $mena = "€";
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
            //$config = $this->getConfig($this->defaults);
            $this->template->mena = $this->mena;
        }
}
