<?php

/**
 * Description of SeznamOblastiPresenter
 *
 * @author mist
 */
class SeznamOblastiPresenter extends BasePresenter {

    private $oblastiModel_var = NULL;
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Action default
     */
    public function actionDefault() {
        
    }

    /**
     * Render default
     */
    public function renderDefault() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $this->template->items = $this -> model -> getOblasti();
    }

    /**
     * Singleton for OblastiModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->oblastiModel_var))
            $this->oblastiModel_var = new OblastiModel();

        return $this->oblastiModel_var;
    }
}