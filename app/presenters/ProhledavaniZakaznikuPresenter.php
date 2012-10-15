<?php
use Nette\Application\UI\Form;

/**
 * Description of ProhledavaniZakaznikuPresenter
 *
 * @author mist
 */
class ProhledavaniZakaznikuPresenter extends BasePresenter {

    /** @persistent */
    public $filtr_zakaznici = "";
    private $zakazniciModel_var = NULL;
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    /**
     * Form for filtering customers
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrZakaznici($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Filtr')->setAttribute('autoComplete', "off");
        $form->addSubmit('filtrZakaznici', 'Filtrovat');
        $form->onSuccess[] = callback($this, 'filtrZakazniky_submit');
        return $form;
    }
    
    /**
     * Button for filtering customers
     * @param type $form name of form
     */
    public function filtrZakazniky_submit($form)
    {
        $this->filtr_zakaznici = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Zakaznici:default');
        else {
            $this->invalidateControl('stranky');
        }
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
        $this->redirect('hledaniZakazniku:default');
        
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = count($this -> model -> getZakazniky(NULL, NULL, NULL, NULL, $this->filtr_zakaznici ));
        $items = $this -> model -> getZakazniky($order = array(
                'nazev' => 'ASC',), NULL,
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_zakaznici);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }
    
    /**
     * Singleton for ZakazniciModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->zakazniciModel_var))
            $this->zakazniciModel_var = new ZakazniciModel();

        return $this->zakazniciModel_var;
    }
    
}