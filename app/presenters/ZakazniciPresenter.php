<?php
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
/**
 * Description of ZakazniciPresenter
 *
 * @author mist
 */
class ZakazniciPresenter extends BasePresenter {

    private $zakazniciModel_var = NULL;
    private $smlouvyModel_var = NULL;
    private $zboziModel_var = NULL;
    private $automatyModel_var = NULL;
    
    /** @persistent */
    public $filtr_sprava_zakazniku = "";
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Form for adding new customer
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatZakaznika($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');
        $form->addText('nazev', 'Jméno zákazníka:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno zákazníka.');
        $form->addText('adresa', 'Adresa zákazníka:')->setAttribute('autoComplete', "off");
        $form->addText('ico', 'IČ:')->setAttribute('autoComplete', "off");
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email zákazníka:')->setAttribute('autoComplete', "off")
                ->addCondition(Form::FILLED) 
                ->addRule(Form::EMAIL, 'Zadejte platný email.');
        $form->addCheckbox('osobni_zakaznik', 'Osobní zákazník:');
        $form->addSubmit('novyZakaznik', 'Přidat');
        $form->onSuccess[] = array($this, 'novyZakaznik_submit');
        return $form;
    }
    
    /**
     * Button for adding new customer
     * @param type $form name of form
     */
    public function novyZakaznik_submit($form)
    {
        $zakaznik = new Zakaznik();
        $zakaznik->nazev = $form['nazev']->getValue();
        $zakaznik->adresa = $form['adresa']->getValue();
        $zakaznik->telefon = $form['telefon']->getValue();
        $zakaznik->ico = $form['ico']->getValue();
        $zakaznik->osobni_zakaznik = $form['osobni_zakaznik']->getValue();
        $zakaznik->email = $form['email']->getValue();
        
        $this->model->addZakaznik($zakaznik);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('stranky');
            $this->invalidateControl('form');
        }
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
        $this->filtr_sprava_zakazniku = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Zakaznici:default');
        else {
            $this->invalidateControl('stranky');
        }
    }
    
    /**
     * Form for editting customer
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitZakaznika($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('nazev', 'Jméno zákazníka:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno zákazníka.');
        $form->addText('adresa', 'Adresa zákazníka:')->setAttribute('autoComplete', "off");
        $form->addText('ico', 'IČ:')->setAttribute('autoComplete', "off");
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email zákazníka:')
                ->setAttribute('autoComplete', "off")
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, 'Zadejte email.');
        $form->addText('poznamka', 'Poznámka:')->setAttribute('autoComplete', "off");
        $form->addCheckbox('osobni_zakaznik', 'Osobní zákazník:');
        $form->addHidden('id');
        $form->addSubmit('editZakaznik', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editZakaznik_submit');
        return $form;
    }
    
    /**
     * Button for saving changes in editted customer
     * @param type $form name of form
     */
    public function editZakaznik_submit($form)
    {
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $form['id']->getValue();
        $zakaznik->nazev = $form['nazev']->getValue();
        $zakaznik->adresa = $form['adresa']->getValue();
        $zakaznik->ico = $form['ico']->getValue();
        $zakaznik->telefon = $form['telefon']->getValue();
        $zakaznik->poznamka = $form['poznamka']->getValue();
        $zakaznik->osobni_zakaznik = $form['osobni_zakaznik']->getValue();
        $zakaznik->email = $form['email']->getValue();
        
        $zakaznik->save();
                
        $this->redirect('default');
    }
    
    /**
     * Form for filtering automats
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrAutomaty($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Filtr')->setAttribute('autoComplete', "off");
        $form->addHidden('id');
        $form->addSubmit('filtrAutomaty', 'Filtrovat');
        $form->onSuccess[] = callback($this, 'filtrAutomaty_submit');
        return $form;
    }
    
    /**
     * Button for filtering automats
     * @param type $form name of form
     */
    public function filtrAutomaty_submit($form)
    {
        $this->filtr_sprava_zakazniku = $form['filtr']->getValue();
        $id_zakaznik = $form['id']->getValue();

        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', $id_zakaznik);
        else {
            $this->invalidateControl('automatySkladem');
        }
    }
    
    /**
     * AJAX request for deleting customer
     * @param type $id id of deleted customer
     */
    public function handleDelete($id)
    {
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = urldecode($id);
        try
        {
            $zakaznik->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('V průběhu zpracování nastala chyba. Zákazníka není možné odebrat.','error');
        }
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('list');
        }
    }
    
    /**
     * AJAX request for deleting agreement
     * @param type $id id of deleted agreement
     */
    public function handleDeleteSmlouva($id_smlouva, $id_zakaznik)
    {
        $smlouva = new Smlouva();
        $smlouva->id_smlouva = urldecode($id_smlouva);
        try
        {
            $smlouva->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('V průběhu zpracování nastala chyba. Smlouvu není možné odebrat.','error');
        }
        
        $this->redirect('Zakaznici:edit', $id_zakaznik);
    }
    
    /**
     * AJAX request for deleting items
     * @param type $id id of deleted items
     */
    public function handleDeleteZbozi($id_zbozi, $id_zakaznik)
    {
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = urldecode($id_zakaznik);
        try
        {
            $zakaznik->deleteZbozi(urldecode($id_zbozi));
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('V průběhu zpracování nastala chyba. Zboží není možné odebrat.','error');
        }
        
        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', $id_zakaznik);
        else {
            $this->invalidateControl('listZbozi');
        }
    }
    
    /**
     * AJAX request to move automat to the warehouse
     * @param type $id_automat id of moved automat
     * @param type $id_zakaznik id of customer owning the automat
     */
    public function handleAutomatDoSkladu($id_automat, $id_zakaznik)
    {
        $automat = new Automat();
        $automat->id_automat = urldecode($id_automat);
        $automat->doSkladu();
        
        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', $id_zakaznik);
        else {
            $this->invalidateControl('listAutomaty');
            $this->invalidateControl('automatySkladem');
        }
    }
    
    /**
     * Moves automat from warehouse to customer
     * @param type $id_automat Id of moved automat
     * @param type $id_zakaznik Id of customer automat moves to
     */
    public function handlePriradAutomat($id_automat, $id_zakaznik)
    {
        $automat = new Automat();
        $automat->id_automat = urldecode($id_automat);
        $automat->id_zakaznik = $id_zakaznik;
        $automat->save();
        
        $this->flashMessage('Automat přiřazen. Aktualizujte jeho umisteni, adresu a oblast!!');
        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', $id_zakaznik);
        else {
            $this->invalidateControl('listAutomaty');
            $this->invalidateControl('automatySkladem');
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
        $paginator->itemCount = count($this -> model -> getZakazniky(NULL, NULL, NULL, NULL, $this->filtr_sprava_zakazniku ));
        $items = $this -> model -> getZakazniky($order = array(
                'nazev' => 'ASC',), array(array("id_zakaznik!=%i","0")),
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_sprava_zakazniku);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }

    /**
     * Action edit
     * @param type $id Id of editted customer
     */
    public function actionEdit($id) {
        
    }

    /**
     * Render edit
     * @param type $id Id of editted customer
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $this["pridatSmlouvu"]["id_zakaznik"]->setValue($id);
        $this["pridatZbozi"]["id_zakaznik"]->setValue($id);
        
        $this->template->id_zakaznik = $id;
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $id;
        if ($zakaznik->fetch())
         {                
            $this["upravitZakaznika"]["id"]->setValue($zakaznik->id_zakaznik);
            $this["upravitZakaznika"]["nazev"]->setValue($zakaznik->nazev);
            $this["upravitZakaznika"]["osobni_zakaznik"]->setValue($zakaznik->osobni_zakaznik);
            $this["upravitZakaznika"]["adresa"]->setValue($zakaznik->adresa);
            $this["upravitZakaznika"]["ico"]->setValue($zakaznik->ico);
            $this["upravitZakaznika"]["telefon"]->setValue($zakaznik->telefon);
            $this["upravitZakaznika"]["poznamka"]->setValue($zakaznik->poznamka);
            $this["upravitZakaznika"]["email"]->setValue($zakaznik->email);
            // zbozi
            $this->template->zbozi = $zakaznik->getZboziZakaznika();
            
            // automaty
            $vp = new VisualPaginator($this, 'vp1');
            $paginator = $vp->getPaginator();
            $paginator->itemsPerPage = 5;
            $paginator->itemCount = count($this -> automatyModel -> getAutomaty(NULL,array("automaty.id_zakaznik" => "0"), NULL,NULL, $this->filtr_sprava_zakazniku));
            $items = $this -> automatyModel -> getAutomaty(array("automaty.nazev" => "ASC"),array("automaty.id_zakaznik" => "0"), 
                    $paginator->offset, $paginator->itemsPerPage,  $this->filtr_sprava_zakazniku);

            $this->template->automaty_skladem = $items;
            if ($this->isAjax())
                $this->invalidateControl('automatySkladem');
            //$this->template->automaty_skladem = $this->automatyModel->getAutomaty(NULL,array("automaty.id_zakaznik" => "0"), NULL,NULL, $this->filtr_sprava_zakazniku);
            //Debugger::log(dibi::$sql);
            
            $vp = new VisualPaginator($this, 'vp2');
            $paginator = $vp->getPaginator();
            $paginator->itemsPerPage = 5;
            $paginator->itemCount = count($this -> automatyModel -> getAutomaty(NULL,array("automaty.id_zakaznik" => $id)));
            $items = $this -> automatyModel -> getAutomaty(array("automaty.nazev" => "ASC"),array("automaty.id_zakaznik" => $id), 
                    $paginator->offset, $paginator->itemsPerPage);

            $this->template->automaty_zakaznika = $items;
            if ($this->isAjax())
                $this->invalidateControl('listAutomaty');            
            //$this->template->automaty_zakaznika = $this->automatyModel->getAutomaty(NULL, array("automaty.id_zakaznik" => $id));
            
            // smlouvy
            $smlouvy = $zakaznik->getSmlouvyZakaznika();
            $predelane = array();
            foreach ($smlouvy as $smlouva)
            {
                $temp = new DibiRow(array());
                
                $datum = new DateTime($smlouva->od);
                $temp->od = $datum->format("j.n.Y");
                $datum = new DateTime($smlouva->do);
                $temp->do = $datum->format("j.n.Y");  
                $temp->cislo_smlouvy = $smlouva->cislo_smlouvy;
                $temp->minimalni_odber = $smlouva->minimalni_odber;
                $temp->zpusob_platby = $smlouva->zpusob_platby;
                $temp->id_smlouva = $smlouva->id_smlouva;
                $temp->poc = $smlouva->poc;
                $temp->preferovany_poc = $smlouva->preferovany_poc;
                $predelane[] = $temp;
            }
            $this->template->smlouvy = $predelane;
         }
         else
         {
             $this->template->zbozi = array();
             $this->template->smlouvy = array();
             // zakaznika se nepodarilo z databaze ziskat
             $this->flashMessage("Tento zákazník je z databáze již vymazán.");
             $this->redirect('Hlidac:default');
             return;
         }
    }
    
    /*************** SMLOUVY ******************/
    /**
     * Form for adding new agreement
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatSmlouvu($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('cislo_smlouvy', 'Číslo smlouvy:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte číslo smlouvy.');
        $form->addTextArea('minimalni_odber', 'Minimální odběr:',45,2)->addRule(Form::FILLED, 'Zadejte povinný odběr zákazníka.');
        $form->addDatePicker('od', "Platnost od")
                ->addCondition($form::FILLED)
                ->addRule(Form::VALID, 'Zadané datum není platné.');
        $form->addDatePicker('do', "Platnost do")
            ->addRule(Form::FILLED, 'Zadejte do kdy smlouva platí')
            ->addRule(Form::VALID, 'Zadané datum není platné.');
        $form->addText('zpusob_platby', 'Způsob platby:');
        $form->addText('poc', 'POC:');
        $form->addCheckbox('preferovany_poc', 'Odpovědný POC');
        $form->addHidden('id_zakaznik');
        $form->addSubmit('editSmlouva', 'Zapsat smlouvu');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'addSmlouva_submit');
        return $form;
    }
    
    /**
     * Button for adding new agreement
     * @param type $form name of form
     */
    public function addSmlouva_submit($form)
    {
        $smlouva = new Smlouva();
        
        $smlouva->minimalni_odber = $form['minimalni_odber']->getValue();
        $smlouva->id_zakaznik = $form['id_zakaznik']->getValue();
        $smlouva->cislo_smlouvy = $form['cislo_smlouvy']->getValue();
        $smlouva->od = $form['od']->getValue();
        $smlouva->do = $form['do']->getValue();
        $smlouva->zpusob_platby = $form['zpusob_platby']->getValue();
        $smlouva->poc = $form['poc']->getValue();
        $smlouva->preferovany_poc = $form['preferovany_poc']->getValue();
        
        $this->smlouvyModel->addSmlouva($smlouva);

        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', array($form['id_zakaznik']->getValue()));
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('smlouvy');
            $this->invalidateControl('formSmlouvy');
        }
       
    }
    
    /**
     * Form for editting of agreement
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitSmlouvu($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('cislo_smlouvy', 'Číslo smlouvy:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte číslo smlouvy.');
        $form->addTextArea('minimalni_odber', 'Minimální odběr:',45,2)->addRule(Form::FILLED, 'Zadejte povinny odběr zákazníka zákazníka.');
        $form->addDatePicker('od', "Platnost od")
                ->addCondition($form::FILLED)
                ->addRule(Form::VALID, 'Zadané datum není platné.');
        $form->addDatePicker('do', "Platnost do")
            ->addRule(Form::FILLED, 'Zadejte do kdy smlouva platí')
            ->addRule(Form::VALID, 'Zadané datum není platné.');
        $form->addText('zpusob_platby', 'Způsob platby:');
        $form->addText('poc', 'POC:');
        $form->addCheckbox('preferovany_poc', 'Odpovědný POC');
        $form->addHidden('id_smlouva');
        $form->addHidden('id_zakaznik');
        $form->addSubmit('editSmlouva', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editSmlouva_submit');
        return $form;
    }
    
    /**
     * Back button from editting agreement to editting of customer
     * @param type $btn button pressed
     */
    public function goBackToEditZakaznik_submit($btn)
    {
        $this->redirect('Zakaznici:edit', $btn->form->values['id_zakaznik']);
    }
    
    /**
     * Button submitting changes in agreement
     * @param type $form name of form
     */
    public function editSmlouva_submit($form)
    {
        $smlouva = new Smlouva();
        $smlouva->id_smlouva = $form['id_smlouva']->getValue();
        $smlouva->cislo_smlouvy = $form['cislo_smlouvy']->getValue();
        $smlouva->minimalni_odber = $form['minimalni_odber']->getValue();
        $smlouva->od = $form['od']->getValue();
        $smlouva->do = $form['do']->getValue();
        $smlouva->zpusob_platby = $form['zpusob_platby']->getValue();
        $smlouva->poc = $form['poc']->getValue();
        $smlouva->preferovany_poc = $form['preferovany_poc']->getValue();
        
        $smlouva->save();
        $this->redirect('Zakaznici:edit', array($form['id_zakaznik']->getValue()));
    }
    
    /**
     * Action edit agreement
     * @param type $id_smlouva Id of editted agreement
     * @param type $id_zakaznik Id of customer the agreement belongs to
     */
    public function actionEditSmlouvy($id_smlouva, $id_zakaznik) {
        
    }

    /**
     * Render edit agreement
     * @param type $id_smlouva Id of editted agreement
     * @param type $id_zakaznik Id of customer the agreement belongs to
     */
    public function renderEditSmlouvy($id_smlouva, $id_zakaznik) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $this->template->id_smlouva = $id_smlouva;
        $this->template->id_zakaznik = $id_zakaznik;
        
        $smlouva = new Smlouva();
        $smlouva->id_smlouva = $id_smlouva;
        if ($smlouva->fetch())
        {
            $this["upravitSmlouvu"]["id_smlouva"]->setValue($smlouva->id_smlouva);
            $this["upravitSmlouvu"]["id_zakaznik"]->setValue($id_zakaznik);
            $this["upravitSmlouvu"]["cislo_smlouvy"]->setValue($smlouva->cislo_smlouvy);
            $this["upravitSmlouvu"]["minimalni_odber"]->setValue($smlouva->minimalni_odber);
            $datum = new DateTime($smlouva->od);
            $this["upravitSmlouvu"]["od"]->setValue($datum->format("j.n.Y"));
            $datum = new DateTime($smlouva->do);
            $this["upravitSmlouvu"]["do"]->setValue($datum->format("j.n.Y"));
            $this["upravitSmlouvu"]["zpusob_platby"]->setValue($smlouva->zpusob_platby);
            $this["upravitSmlouvu"]["poc"]->setValue($smlouva->poc);
            $this["upravitSmlouvu"]["preferovany_poc"]->setValue($smlouva->preferovany_poc);
        }
    }
    
    /***************  Zbozi  *********************/
    /**
     * Form for adding items to customer
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatZbozi($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');
        $form->addText('zbozi', "Přidat zboží")->setAttribute('autoComplete', "off")->getControlPrototype()->class('query pridavacZbozi');
        $form->addHidden('id_zakaznik');
        //$form->addSubmit('addZbozi', 'Přidat zboží')->onClick[] = callback($this, 'addZbozi_submit');
        $form->onSuccess[] = callback($this, 'addZbozi_submit');
        return $form;
    }
    
    /**
     * Button adding items to customer
     * @param type $form name of form
     */
    public function addZbozi_submit($form)
    {
        $zakaznik = new Zakaznik();
        //$form = $btn->form;
        $zakaznik->id_zakaznik = $form['id_zakaznik']->getValue();
        $zbozi = new Zbozi();
        $zbozi->nazev = $form['zbozi']->getValue();
        if ($zbozi->fetch())
            $zakaznik->pridatZboziZakaznikovi($zbozi->id_zbozi);
        else
        {
            $zbozi->zkratka = $form['zbozi']->getValue();
            if ($zbozi->fetch())
                $zakaznik->pridatZboziZakaznikovi($zbozi->id_zbozi);
        }

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('listZbozi');
            //$this->invalidateControl('formZbozi');
            $form->setValues(array(), TRUE);
        }
        //$this->sendPayload();
        //$this->terminate();
    }
    
    /**
     * AJAX request changing if ordered item is mandatory according to agreement or not
     * @param type $id_zakaznici_zbozi Id in connection table
     * @param type $id_zakaznik Id of customer (used for redirecting back)
     * @param type $new_value New state of mandatorness
     */
    public function handleSwitchVeSmlouve($id_zakaznici_zbozi, $id_zakaznik, $new_value)
    {
        $new_value = !$new_value;
        Zakaznik::setVeSmlouve($id_zakaznici_zbozi, $new_value);
        
        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', $id_zakaznik);
        else {
            $this->invalidateControl('listZbozi');
        }
    }
    
    public function handleSwitchPOC($id_smlouva, $id_zakaznik, $new_value)
    {
        $new_value = !$new_value;
        Smlouva::setPOC($id_smlouva, $new_value);
        
        if (!$this->isAjax())
            $this->redirect('Zakaznici:edit', $id_zakaznik);
        else {
            $this->invalidateControl('smlouvy');
        }
    }
    
    /**
     * Adds items to customers list
     * @param Zbozi $zbozi Id of item which customer usually orders
     * @param type $id_zakaznik Id of customer
     */
    public function handlePridatZboziSubmitted($zbozi, $id_zakaznik)
    {
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $id_zakaznik;
        $zbozi = new Zbozi();
        $zbozi->nazev = $zbozi;
        if ($zbozi->fetch())
            $zakaznik->pridatZboziZakaznikovi($zbozi->id_zbozi);
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('listZbozi');
            //$this->invalidateControl('form');
            $form->setValues(array(), TRUE);
        }
        $this->terminate();
    }
        
    /**
     * AJAX autocomplete for adding items for customer
     * @param type $query string written in input box
     */
    public function handleAutoComplete($query)
    {
            $this->payload->suggestions = array();
            $this->payload->query = $query;
            $text = trim($query);
            if ($text !== '') {
                $list = $this->zboziModel->getZbozi();

                foreach ($list as $item) {
                    $item = trim($item->nazev);
                    if (strncasecmp($item, $text, strlen($text)) === 0) {
                            $this->payload->suggestions[] = $item;
                    }
                }
            }
            $this->sendPayload();
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

    /**
     * Singleton for SmlouvyModel
     * @return type 
     */
    public function getSmlouvyModel() {
        if(!isset($this->smlouvyModel_var))
            $this->smlouvyModel_var = new SmlouvyModel();

        return $this->smlouvyModel_var;
    }
    
    /**
     * Singleton for ZboziModel
     * @return type 
     */
    public function getZboziModel() {
        if(!isset($this->zboziModel_var))
            $this->zboziModel_var = new ZboziModel();

        return $this->zboziModel_var;
    }
    
    /**
     * Singleton for AutomatyModel
     * @return type 
     */
    public function getAutomatyModel() {
        if(!isset($this->automatyModel_var))
            $this->automatyModel_var = new AutomatyModel();

        return $this->automatyModel_var;
    }
}