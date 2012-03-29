<?php
use Nette\Application\UI\Form;

/**
 * Description of Kontakty
 *
 * @author mist
 */
class KontaktyPresenter extends BasePresenter {

    private $kontaktyModel_var = NULL;
    /** @persistent */
    public $filtr_kontakty = '';
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    /**
     * Form for adding new contact
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatKontakt($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('jmeno', 'Jméno a příjmení:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno kontaktu.');
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email:')->setAttribute('autoComplete', "off")
                ->addCondition(Form::MIN_LENGTH, 1) 
                ->addRule(Form::EMAIL, 'Zadejte platný email.');

        $form->addText('poznamka', 'Poznámka:')->setAttribute('autoComplete', "off");
        
        $form->addSubmit('novyKontakt', 'Přidat');
        $form->onSuccess[] = array($this, 'novyKontakt_submit');
        return $form;
    }
    
    /**
     * Button for submitting new contact
     * @param type $form name of form
     */
    public function novyKontakt_submit($form)
    {
        $kontakt = new Kontakt();
        $kontakt->jmeno = $form['jmeno']->getValue();
        $kontakt->telefon = $form['telefon']->getValue();
        $kontakt->email = $form['email']->getValue();
        $kontakt->poznamka = $form['poznamka']->getValue();
        
        $this->model->addKontakt($kontakt);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('kontakty');
            $this->invalidateControl('form');
        }
    }
    
    /**
     * Form for editting contact
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitKontakt($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('jmeno', 'Jméno a příjmení:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno kontaktu.');
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email:')->setAttribute('autoComplete', "off")
                ->addCondition(Form::MIN_LENGTH, 1) 
                ->addRule(Form::EMAIL, 'Zadejte platný email.');

        $form->addText('poznamka', 'Poznámka:')->setAttribute('autoComplete', "off");
        $form->addHidden('id');
        $form->addSubmit('editKontakt', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editKontakt_submit');
        return $form;
    }
    
    /**
     * Back button
     * @param type $form name of form
     */
    public function goBack_submit($form)
    {
        $this->redirect('default');
    }
    
    /**
     * Button for editting contact
     * @param type $form name of form
     */
    public function editKontakt_submit($form)
    {
        $kontakt = new Kontakt();
        $kontakt->id_kontakt = $form['id']->getValue();
        $kontakt->jmeno = $form['jmeno']->getValue();
        $kontakt->telefon = $form['telefon']->getValue();
        $kontakt->email = $form['email']->getValue();
        $kontakt->poznamka = $form['poznamka']->getValue();
        
        $kontakt->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting contact
     * @param type $id id of deleted contact
     */
    public function handleDelete($id)
    {
        $kontakt = new Kontakt();
        $kontakt->id_kontakt = urldecode($id);
        try
        {
            $kontakt->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('Kontakt je pravděpodobně přiřazen k nějakému automatu. Musíte nejdřív zrušit kontakt u automatu','error');
        }
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('kontakty');
        }
    }
    
    /**
     * Form for filtering contacts
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrKontakty($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Filtr')->setAttribute('autoComplete', "off");
        $form->addSubmit('filtrKontakty', 'Filtrovat');
        $form->onSuccess[] = callback($this, 'filtrKontakty_submit');
        return $form;
    }
    
    /**
     * Button for filtering
     * @param type $form 
     */
    public function filtrKontakty_submit($form)
    {
        $this->filtr_kontakty = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Kontakty:default');
        else {
            $this->invalidateControl('kontakty');
        }
    }

    /**
     * action default
     */
    public function actionDefault() {
        
    }

    /**
     * render default
     */
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        if (isset($this->filtr_kontakty) == false)
                $this->filtr_kontakty = "";
        $paginator->itemCount = count($this -> model -> getKontakty(NULL, NULL,NULL,NULL, $this->filtr_kontakty));
        $items = $this -> model -> getKontakty(array("jmeno" => "ASC"), NULL,
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_kontakty);
        $this->template->items = $items;
        
        $hromadnymail = "mailto:witt@witt.cz?bcc=";
        $kontakty = $this -> model -> getEmaily();
        $i=0;
        foreach($kontakty as $kontakt)
            if ($kontakt->email != "")
            {
                if ($i == 0)
                {
                    $hromadnymail .= $kontakt->email;
                    $i++;
                }
                else
                    $hromadnymail .=  "," . $kontakt->email;
            }
        $this->template->hromadnymail = $hromadnymail;
        
        if ($this->isAjax())
            $this->invalidateControl('kontakty');

    }
    
    /**
     * action edit
     * @param type $id editted contact
     */
    public function actionEdit($id) {
        
    }

    /**
     * render edit
     * @param type $id id of editted contact
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $kontakt = new Kontakt();
        $kontakt->id_kontakt = $id;
        if ($kontakt->fetch())
         {
            $this["upravitKontakt"]["id"]->setValue($kontakt->id_kontakt);
            $this["upravitKontakt"]["jmeno"]->setValue($kontakt->jmeno);
            $this["upravitKontakt"]["telefon"]->setValue($kontakt->telefon);
            $this["upravitKontakt"]["email"]->setValue($kontakt->email);
            $this["upravitKontakt"]["poznamka"]->setValue($kontakt->poznamka);
         }
    }
    
    /**
     * Singleton for Kontakty
     * @return type 
     */
    public function getModel() {
        if(!isset($this->kontaktyModel_var))
            $this->kontaktyModel_var = new KontaktyModel();

        return $this->kontaktyModel_var;
    }

}