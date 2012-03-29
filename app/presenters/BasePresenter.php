<?php
/**
 * Base class for all application presenters.
 *
 * @author     mist
 * @package    HlidacObjednavek
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /**
     * Flash messages
     */
    public function afterRender()
    {
        if ($this->isAjax() && $this->hasFlashSession())
            $this->invalidateControl('flashes');//*/
    }
}
