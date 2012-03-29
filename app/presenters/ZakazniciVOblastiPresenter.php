<?php
use Nette\Application\UI\Form;

/**
 * Description of ZakazniciVOblastiPresenter
 *
 * @author mist
 */
class ZakazniciVOblastiPresenter extends BasePresenter {

    private $zakazniciModel_var = NULL;
    /** @persistent */
    public $filtr_zakaz_oblast = '';
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Action for showing customers in specific area
     * @param type $id_oblast Id of area
     */
    public function actionDefault($id_oblast) {
        
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
        $this->filtr_zakaz_oblast = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Zakaznici:default');
        else {
            $this->invalidateControl('stranky');
        }
    }

    /**
     * Render for showing customers in specific area
     * @param type $id_oblast Id of area
     */
    public function renderDefault($id_oblast) {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $vp = new VisualPaginator($this, 'vp');
        $this->template->id_oblast = $id_oblast;
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this -> model -> getZakaznikyVOblasti($id_oblast, NULL, NULL, NULL, $this->filtr_zakaz_oblast ));
        $items = $this -> model -> getZakaznikyVOblasti($id_oblast, $order = array(
                'nazev' => 'ASC'), $paginator->offset, $paginator->itemsPerPage, $this->filtr_zakaz_oblast);
        
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