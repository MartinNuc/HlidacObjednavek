<?php
use Nette\Application\UI\Form;
/**
 * Description of AutomatyPresenter
 *
 * @author mist
 */
class AutomatyPresenter extends BasePresenter {

    private $zakazniciModel_var = NULL;
    private $automatyModel_var = NULL;
    private $kontaktyModel_var = NULL;
    private $oblastiModel_var = NULL;
    private $presunyModel_var = NULL;
    
    /** @persistent */
    public $filtr_automaty = '';

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    /**
     * Form for new automat
     * @param type $name form name
     * @return Form form for FW
     */
    public function createComponentPridatAutomat($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');

        $form->addText('nazev', 'Název automatu:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název automatu.');
        $form->addText('bmb', 'BMB:')->setAttribute('autoComplete', "off");
        $form->addText('vyrobni_cislo', 'Výrobní číslo:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte výrobní číslo automatu.');
        $form->addText('adresa', 'Adresa automatu:')->setAttribute('autoComplete', "off");
        $form->addText('umisteni', 'Umístění (pro hledání):')->setAttribute('autoComplete', "off");
        $form->addText('layout', 'Layout:')->addRule(Form::FILLED, 'Zadejte layout automatu.');
        
        $pole = $this->oblastiModel->getOblasti()->fetchPairs('id_oblast', 'nazev');
        $form->addSelect('oblast', 'Oblast:', $pole);
        
        $pole = $this->zakazniciModel->getZakazniky("nazev")->fetchPairs('id_zakaznik','nazev');
        $form->addSelect('zakaznik', 'Přidat rovnou pod zákazníka:', $pole);
        
        $form->addSubmit('novyAutomat', 'Přidat');
        $form->onSuccess[] = array($this, 'novyAutomat_submit');
        return $form;
    }
    
    /**
     * Submit button of new automat form
     * @param type $form new automat form
     */
    public function novyAutomat_submit($form)
    {
        $automat = new Automat();
        $automat->nazev = $form['nazev']->getValue();
        $automat->bmb = $form['bmb']->getValue();
        $automat->vyrobni_cislo = $form['vyrobni_cislo']->getValue();
        $automat->adresa = $form['adresa']->getValue();
        $automat->umisteni = $form['umisteni']->getValue();
        $automat->layout = $form['layout']->getValue();
        
        $automat->id_zakaznik = $form['zakaznik']->getValue();
        $automat->id_oblast = $form['oblast']->getValue();
        $this->model->addAutomat($automat);

        $form->setValues(array(), TRUE);
        if (!$this->isAjax())
            $this->redirect('this');
        else
        {
            $this->invalidateControl('stranky');
            $this->invalidateControl('form');
        }
    }
    
    /**
     * AJAX delete automat request
     * @param type $id Id of deleted automat
     */
    public function handleDelete($id)
    {
        $automat = new Automat();
        $automat->id_automat = urldecode($id);
        try
        {
            $automat->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('Při zpracování nastala chyba. Automat není možné odebrat.','error');
        }
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }
    
    /**
     * Edit form
     * @param type $name edit form
     * @return Form for the FW
     */
    public function createComponentUpravitAutomat($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('nazev', 'Název automatu:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název automatu.');
        $form->addText('bmb', 'BMB:');
        $form->addText('vyrobni_cislo', 'Výrobní číslo:')->addRule(Form::FILLED, 'Zadejte výrobní číslo.');
        $form->addText('adresa', 'Adresa automatu:')->setAttribute('autoComplete', "off");
        $form->addText('umisteni', 'Umístění (pro hledání):')->setAttribute('autoComplete', "off");
        $form->addText('layout', 'Layout:')->addRule(Form::FILLED, 'Zadejte layout automatu.');
        
        $pole = $this->oblastiModel->getOblasti()->fetchPairs('id_oblast', 'nazev');
        $form->addSelect('oblast', 'Oblast:', $pole);
        
        $pole = $this->zakazniciModel->getZakazniky("nazev")->fetchPairs('id_zakaznik','nazev');
        $form->addSelect('zakaznik', 'U zákazníka:', $pole);
        
        $form->addHidden('id');
        $form->addSubmit('editAutomat', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editAutomat_submit');
        return $form;
    }
    
    /**
     * Filtering of automats
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrKontakty($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Filtr')->setAttribute('autoComplete', "off");
        $form->addHidden('id');
        $form->addSubmit('filtrKontakty', 'Filtrovat');
        $form->onSuccess[] = callback($this, 'filtrKontakty_submit');
        return $form;
    }
        
    /**
     * button to filter contacts
     * @param type $form name of filter form
     */
    public function filtrKontakty_submit($form)
    {
        $this->filtr_automaty = $form['filtr']->getValue();
        $id_zakaznik = $form['id']->getValue();

        if (!$this->isAjax())
            $this->redirect('Automaty:edit', $id_zakaznik);
        else {
            $this->invalidateControl('kontaktyVdb');
        }
    }

    /**
     * Button in edit form of editting automat
     * @param type $form name of automat
     */
    public function editAutomat_submit($form)
    {
        $automat = new Automat();
        $automat->id_automat = $form['id']->getValue();
        $automat->nazev = $form['nazev']->getValue();
        $automat->bmb = $form['bmb']->getValue();
        $automat->vyrobni_cislo = $form['vyrobni_cislo']->getValue();
        $automat->layout = $form['layout']->getValue();
        $automat->umisteni = $form['umisteni']->getValue();
        $automat->adresa = $form['adresa']->getValue();
        $automat->id_zakaznik = $form['zakaznik']->getValue();
        $automat->id_oblast = $form['oblast']->getValue();
        
        $automat->save();
                
        $this->redirect('default');
    }
    
    /**
     * Go back button
     * @param type $form name of form
     */
    public function goBack_submit($form)
    {
        $this->redirect('default');
    }
    
    /**
     * AJAX request for removing contact from automat
     * @param int $id_automat_kontakt id of kontakt
     * @param int $id_automat id of automat
     */
    public function handleKontaktOdpriradit($id_automat_kontakt, $id_automat)
    {
        Automat::odpriraditKontakt(urldecode($id_automat_kontakt));
        
        if (!$this->isAjax())
            $this->redirect('Automaty:edit', $id_automat);
        else {
            $this->invalidateControl('listKontakty');
        }
    }

    /**
     * AJAX request for adding contact to the automat
     * @param type $id_kontakt id of contact
     * @param type $id_automat id of automat
     */
    public function handlePriradKontakt($id_kontakt, $id_automat)
    {
        $automat = new Automat();
        $automat->id_automat = urldecode($id_automat);
        $automat->priraditKontakt($id_kontakt);
        
        if (!$this->isAjax())
            $this->redirect('Automaty:edit', $id_automat);
        else {
            $this->invalidateControl('listKontakty');
            $this->invalidateControl('kontaktyVdb');
        }
    }

    /**
     * Form for adding new contact
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentNovyKontakt($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');
        $form->addText('jmeno', 'Jméno:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno kontaktu.');
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email:')->setAttribute('autoComplete', "off")
                ->addCondition(Form::FILLED) 
                ->addRule(Form::EMAIL, 'Zadejte platný email.');
        $form->addText('poznamka', 'Poznámka:')->setAttribute('autoComplete', "off");
        
        $form->addHidden('id');
        
        $form->addSubmit('novyKontakt', 'Přidat');
        $form->onSuccess[] = array($this, 'novyKontakt_submit');
        return $form;
    }
    
    /**
     * Button for creation of contact
     * @param type $form name of form ... for FW
     */
    public function novyKontakt_submit($form)
    {
        $kontakt = new Kontakt();
        $kontakt->jmeno = $form['jmeno']->getValue();
        $kontakt->telefon = $form['telefon']->getValue();
        $kontakt->email = $form['email']->getValue();
        $kontakt->poznamka = $form['poznamka']->getValue();
        
        $newid = $this->kontaktyModel->addKontakt($kontakt);
        
        if ($newid != false)
        {
            $automat = new Automat();
            $automat->id_automat = $form['id']->getValue();
            $automat->priraditKontakt($newid);
        }
        $form->setValues(array(), TRUE);
        if (!$this->isAjax())
            $this->redirect('this');
        else
        {
            $this->invalidateControl('listKontakty');
            $this->invalidateControl('kontaktyVdb');
            $this->invalidateControl('form');
        }
    }
    
    /**
     * Filtering of automats
     * @param type $name name of filtering form
     * @return Form name of form for FW
     */
    public function createComponentFiltrAutomaty($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Filtr')->setAttribute('autoComplete', "off");
        $form->addSubmit('filtrAutomaty', 'Filtrovat');
        $form->onSuccess[] = callback($this, 'filtrAutomaty_submit');
        return $form;
    }
    
    /**
     * Button for filtering automats
     * @param type $form name of form for FW
     */
    public function filtrAutomaty_submit($form)
    {
        $this->filtr_automaty = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Automaty:default');
        else {
            $this->invalidateControl('stranky');
        }
    }
    
    /**
     * Action of presenter
     */
    public function actionDefault() {
        
    }

    /**
     * Render of presenter
     */
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = count($this -> model -> getAutomaty(NULL, NULL, NULL, NULL, $this->filtr_automaty));
        $items = $this -> model -> getAutomaty(array("id_oblast" => "DSC", "zakaznik_nazev" => "ASC"), NULL,
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_automaty);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }
    
    /**
     * Action edit of presenter
     * @param type $id editted automat
     */
    public function actionHistorie($id_automat) {
        
    }

    /**
     * Render edit of presenter
     * @param type $id id of eddited automat
     */
    public function renderHistorie($id_automat) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this -> presunyModel -> getPresuny(NULL, array("id_automat" => $id_automat), NULL, NULL));
        $items = $this -> presunyModel->getPresuny($order = array('datum' => 'DESC'), array("id_automat" => $id_automat),
                $paginator->offset, $paginator->itemsPerPage);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }

    /**
     * Action edit of presenter
     * @param type $id editted automat
     */
    public function actionEdit($id) {
        
    }

    /**
     * Render edit of presenter
     * @param type $id id of eddited automat
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $automat = new Automat();
        $automat->id_automat = $id;
        if ($automat->fetch())
        {
            $this->template->id_automat = $automat->id_automat;
            $this["upravitAutomat"]["id"]->setValue($automat->id_automat);
            $this["upravitAutomat"]["nazev"]->setValue($automat->nazev);
            $this["upravitAutomat"]["bmb"]->setValue($automat->bmb);
            $this["upravitAutomat"]["vyrobni_cislo"]->setValue($automat->vyrobni_cislo);
            $this["upravitAutomat"]["layout"]->setValue($automat->layout);
            $this["upravitAutomat"]["adresa"]->setValue($automat->adresa);
            $this["upravitAutomat"]["umisteni"]->setValue($automat->umisteni);
            $this["upravitAutomat"]["zakaznik"]->setValue($automat->id_zakaznik);
            $this["upravitAutomat"]["oblast"]->setValue($automat->id_oblast);
            
            // kontakty
            $this->template->kontakty = $this->kontaktyModel->getKontaktyInContext(array("jmeno" => "ASC"), array('id_automat' => $id));
            // pro novy kontakt
            $this["novyKontakt"]["id"]->setValue($automat->id_automat);
            // kontakty volne
            $vp = new VisualPaginator($this, 'vp1');
            $paginator = $vp->getPaginator();
            $paginator->itemsPerPage = 5;
            $paginator->itemCount = count($this->kontaktyModel->getKontakty(NULL, NULL, NULL, NULL, $this->filtr_automaty));
            $this->template->kontakty_v_db = $this -> kontaktyModel -> getKontakty(array("jmeno" => "ASC"), NULL , $paginator->offset, $paginator->itemsPerPage,  $this->filtr_automaty);
            if ($this->isAjax())
                $this->invalidateControl('kontaktyVdb');
        }
    }
    
    /**
     * Singleton for AutomatyModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->automatyModel_var))
            $this->automatyModel_var = new AutomatyModel();

        return $this->automatyModel_var;
    }

    /**
     * Singleton for AutomatyModel
     * @return type 
     */
    public function getOblastiModel() {
        if(!isset($this->oblastiModel_var))
            $this->oblastiModel_var = new OblastiModel();

        return $this->oblastiModel_var;
    }
    
     /**
     * Singleton for AutomatyModel
     * @return type 
     */
    public function getZakazniciModel() {
        if(!isset($this->zakazniciModel_var))
            $this->zakazniciModel_var = new ZakazniciModel();

        return $this->zakazniciModel_var;
    }
    
    /**
     * Singleton for AutomatyModel
     * @return type 
     */
    public function getKontaktyModel() {
        if(!isset($this->kontaktyModel_var))
            $this->kontaktyModel_var = new KontaktyModel();

        return $this->kontaktyModel_var;
    }
    
    public function getPresunyModel() {
        if(!isset($this->presunyModel_var))
            $this->presunyModel_var = new PresunyModel();

        return $this->presunyModel_var;
    }
}