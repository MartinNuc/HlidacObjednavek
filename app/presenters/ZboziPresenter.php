<?php
use Nette\Diagnostics\Debugger;
use Nette\Application\UI\Form;
/**
 * Description of ZboziPresenter
 *
 * @author mist
 */
class ZboziPresenter extends BasePresenter {

    private $zboziModel_var = NULL;
    private $dphModel_var = NULL;
    private $kategorieModel_var = NULL;
    
    /** @persistent */
    public $filtr_zbozi = '';

    /**
     * Form for adding new item
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatZbozi($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('zkratka', 'Zkratka:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte zkratku pro zboží.');
        $form->addText('nazev', 'Název zboží:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název zboží.');
        $form->addText('prodejni_cena', 'Prodejní cena:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte projdení cenu zboží.')->addRule(Form::FLOAT, 'Prodejní cena není číselná');
        $form->addText('nakupni_cena', 'Nákupní cena:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte nákupní cenu zboží.')->addRule(Form::FLOAT, 'Nákupní cena není číselná');
        $form->addText('body', 'Body:')->setAttribute('autoComplete', "off")->setDefaultValue("0")
                ->addCondition($form::FILLED)
                ->addRule(Form::FLOAT, 'Zadejte body jako číslo.');

        $pole = array();
        foreach ($this->dphModel->getDph() as $key => $value)
            $pole[]=$value->dph . " %";
        $form->addSelect('dph', 'DPH:', $pole);
        
        $pole = $this->kategorieModel->getKategorie()->fetchPairs( 'id_kategorie', 'nazev' );
        foreach ($pole as $key => $value)
            $pole[$key]=$value;
        $form->addSelect('kategorie', 'Kategorie:', $pole);
        $form->addText('skladem', 'Kusy na skladě: ')->setAttribute('autoComplete', "off")->setDefaultValue("0");
        $form->addCheckbox('nestle', 'Spadá pod Nestle');
        $form->addText('sapcode', 'Kód Nestlé: ')->setAttribute('autoComplete', "off");
        
        $form->addSubmit('noveZbozi', 'Přidat');
        $form->onSuccess[] = array($this, 'noveZbozi_submit');
        return $form;
    }
    
    /**
     * Button adding new item
     * @param type $form name of form
     */
    public function noveZbozi_submit($form)
    {
        $zbozi = new Zbozi();
        $zbozi->zkratka = $form['zkratka']->getValue();
        $zbozi->nazev = $form['nazev']->getValue();
        $zbozi->nestle = $form['nestle']->getValue();
        $zbozi->nakupni_cena = $form['nakupni_cena']->getValue();
        $zbozi->prodejni_cena = $form['prodejni_cena']->getValue();
        $zbozi->body = $form['body']->getValue();
        $zbozi->sapcode = $form['sapcode']->getValue();
        $zbozi->skladem = $form['skladem']->getValue();
        $zbozi->id_kategorie = $form['kategorie']->getValue();
        
        $pct = $form['dph']->getSelectedItem();
        $zbozi->dph_cislo = trim(str_replace('%', '', $pct));
        $this->model->addZbozi($zbozi);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('stranky');
            $form['body']->setValue("0");
            $form['skladem']->setValue("0");
            $this->invalidateControl('form');
        }
    }

    /**
     * JQuery dialog for increasing amount of items in warehouse. This just turns on form.
     */
    public function handlegetSimpleForm() {
            $this->template->showSimpleForm = true;
            if ($this->isAjax()) {
                    $this->invalidateControl('simpleForm');
            }
    }

    /**
     * Form for increasing amount of items in warehouse
     * @return Form for FW
     */
    protected function createComponentSimpleForm() {
            $form = new Form;
            $form->getElementPrototype()->class('ajax');
            $form->addText('pocet', 'Počet:')->setAttribute('autoComplete', "off")
                    ->addRule(Form::FILLED, 'Zadejte počet přijatého zboží')
                    ->addRule(Form::FLOAT,'Zadejte číslo.');
            $form->addHidden('id');
            $form->addSubmit('pridat', 'Přidat na sklad')
                    ;//->onClick[] = callback($this, 'simpleFormSubmitted');
            $form->onSuccess[] = callback($this, 'simpleFormSubmitted');
            return $form;
    }

    /**
     * Submitted form for increasing amount of items in warehouse
     * @param type $btn button pressed
     * @return type for FW
     */
    public function simpleFormSubmitted($btn) {
            $form = $btn->form;
            $id = $form['id']->getValue();
            $pocet = $form['pocet']->getValue();
            if ($id == "")
                return;
            
            $zbozi = new Zbozi();
            $zbozi->id_zbozi = $id;
            $zbozi->pridejNaSklad($pocet);
            
            if ($this->isAjax()) {
                    $this->invalidateControl('simpleForm');
                    $this->invalidateControl('stranky');
            } else {
                    $this->redirect('this');
            }
    }
    
    /**
     * Form for editting item
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitZbozi($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('zkratka', 'Zkratka:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte zkratku pro zboží.');
        $form->addText('nazev', 'Název zboží:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název zboží.');
        $form->addText('prodejni_cena', 'Prodejní cena:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte projdení cenu zboží.')->addRule(Form::FLOAT, 'Prodejní cena není číselná');
        $form->addText('nakupni_cena', 'Nákupní cena:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte nákupní cenu zboží.')->addRule(Form::FLOAT, 'Nákupní cena není číselná');
        $form->addText('body', 'Body:')->setAttribute('autoComplete', "off")->setDefaultValue("0")
                ->addCondition(Form::FILLED)
                ->addRule(Form::FLOAT, 'Zadejte body jako číslo.');

        
        $pole = $this->dphModel->getDph()->fetchPairs( 'id_dph', 'dph' );
        foreach ($pole as $key => $value)
            $pole[$key]=$value . " %";
        $form->addSelect('dph', 'DPH:', $pole);
        
        $pole = $this->kategorieModel->getKategorie()->fetchPairs( 'id_kategorie', 'nazev' );
        $form->addSelect('kategorie', 'Kategorie:', $pole);
        $form->addText('skladem', 'Kusy na skladě: ')->setAttribute('autoComplete', "off")->setDefaultValue("0");
        
        $form->addCheckbox('nestle', 'Spadá pod Nestle');
        $form->addText('sapcode', 'Kód Nestlé: ')->setAttribute('autoComplete', "off");
       
        $form->addHidden('id');
        $form->addSubmit('editZbozi', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editZbozi_submit');
        return $form;
    }
    
    /**
     * Button submitting changes in item
     * @param type $form name of form
     */
    public function editZbozi_submit($form)
    {
        $zbozi = new Zbozi();
        $zbozi->id_zbozi = $form['id']->getValue();

        $zbozi->zkratka = $form['zkratka']->getValue();
        $zbozi->nazev = $form['nazev']->getValue();
        $zbozi->nestle = $form['nestle']->getValue();
        $zbozi->skladem = $form['skladem']->getValue();
        $zbozi->nakupni_cena = $form['nakupni_cena']->getValue();
        $zbozi->prodejni_cena = $form['prodejni_cena']->getValue();
        $zbozi->body = $form['body']->getValue();
        $zbozi->sapcode = $form['sapcode']->getValue();
        $zbozi->id_kategorie = $form['kategorie']->getValue();
        
        $pct = $form['dph']->getSelectedItem();
        $zbozi->dph_cislo = trim(str_replace('%', '', $pct));
        
        $zbozi->save();
                
        $this->redirect('default');
    }

    /**
     * AJAX request handler for deleting item
     * @param type $id id of deleted item
     */
    public function handleDelete($id)
    {
        $zbozi = new Zbozi();
        $zbozi->id_zbozi = urldecode($id);
        $zbozi->delete();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }

    /**
     * Back button
     * @param type $form 
     */
    public function goBack_submit($form)
    {     
        $this->redirect('default');
    }
    
    /**
     * Form for filtering items
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrZbozi($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Filtr')->setAttribute('autoComplete', "off");
        $form->addSubmit('filtrZbozi', 'Filtrovat');
        $form->onSuccess[] = callback($this, 'filtrZbozi_submit');
        return $form;
    }
    
    /**
     * Button for filtering items
     * @param type $form name of form
     */
    public function filtrZbozi_submit($form)
    {
        $this->filtr_zbozi = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Zbozi:default');
        else {
            $this->invalidateControl('stranky');
        }
    }
    
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
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this -> model -> getZbozi(NULL, NULL, NULL, NULL, $this->filtr_zbozi));
        $items = $this -> model -> getZbozi($order = array(
                'nazev' => 'ASC',), NULL,
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_zbozi);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }

    /**
     * Action edit
     * @param type $id Id of editted item
     */
    public function actionEdit($id) {
        
    }

    /**
     * Render edit
     * @param type $id Id of editted item
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $zbozi = new Zbozi();
        $zbozi->id_zbozi = $id;
        if ($zbozi->fetch())
         {
            $this["upravitZbozi"]["id"]->setValue($zbozi->id_zbozi);
            $this["upravitZbozi"]["nazev"]->setValue($zbozi->nazev);
            $this["upravitZbozi"]["zkratka"]->setValue($zbozi->zkratka);
            $this["upravitZbozi"]["nestle"]->setValue($zbozi->nestle);
            $this["upravitZbozi"]["skladem"]->setValue($zbozi->skladem);
            $this["upravitZbozi"]["prodejni_cena"]->setValue($zbozi->prodejni_cena);
            $this["upravitZbozi"]["nakupni_cena"]->setValue($zbozi->nakupni_cena);
            $this["upravitZbozi"]["body"]->setValue($zbozi->body);
            $this["upravitZbozi"]["sapcode"]->setValue($zbozi->sapcode);
            $this["upravitZbozi"]["kategorie"]->setDefaultValue($zbozi->id_kategorie);
            $this["upravitZbozi"]["dph"]->setDefaultValue($zbozi->id_dph);
         }
    }
    
    /**
     * Singleton for ZboziModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->zboziModel_var))
            $this->zboziModel_var = new ZboziModel();

        return $this->zboziModel_var;
    }
    
    /**
     * Singleton for DphModel
     * @return type 
     */
    public function getDphModel() {
        if(!isset($this->dphModel_var))
            $this->dphModel_var = new DphModel();

        return $this->dphModel_var;
    }
    
    /**
     * Singleton for KategorieModel
     * @return type 
     */
    public function getKategorieModel() {
        if(!isset($this->kategorieModel_var))
            $this->kategorieModel_var = new KategorieModel();

        return $this->kategorieModel_var;
    }

}