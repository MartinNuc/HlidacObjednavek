<?php

use Nette\Application\UI\Form;
/**
 * Description of SpravaTechnikuPresenter
 *
 * @author mist
 */
class TechniciPresenter extends BasePresenter {

    private $techniciModel = NULL;
    private $oblastiModel_var = NULL;

    /**
     * For for adding new engeneer
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatTechnika($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');
        $form->addText('jmeno', 'Jméno:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte technikovo jméno.');
        $form->addText('prijmeni', 'Příjmení:')->setAttribute('autoComplete', "off");
        
        $pole = $this->oblastiModel->getOblasti()->fetchPairs('id_oblast', 'nazev');
        foreach ($pole as $key => $value)
            $pole[$key]=$value;

        $form->addSelect('oblast', 'Oblast:', $pole);
        
        $form->addSubmit('novyTechnik', 'Přidat');
        $form->onSuccess[] = array($this, 'novyTechnik_submit');
        return $form;
    }

    /**
     * Button for adding new engeneer
     * @param type $form name of form
     */
    public function novyTechnik_submit($form)
    {
        $technik = new Technik();
        $technik->jmeno = $form['jmeno']->getValue();
        $technik->prijmeni = $form['prijmeni']->getValue();
        $technik->id_oblast = $form['oblast']->getValue();
        $this->model->addTechnik($technik);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('list');
            $this->invalidateControl('form');
        }
    }
    
    /**
     * Form for editting engeneer
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitTechnika($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('jmeno', 'Jméno:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte technikovo jméno.');
        $form->addText('prijmeni', 'Příjmení:')->setAttribute('autoComplete', "off");
        
        foreach ($this->oblastiModel->getOblasti() as $key => $value)
            $pole[$value->id_oblast]=$value->nazev;
        
        $form->addSelect('oblast', 'Oblast:', $pole);
        $form->addHidden('id');
        $form->addSubmit('editTechnik', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editTechnik_submit');
        return $form;
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
     * Button for saving changes in editation
     * @param type $form name of form
     */
    public function editTechnik_submit($form)
    {
        $technik = new Technik();
        $technik->id_technik = $form['id']->getValue();
        $technik->jmeno = $form['jmeno']->getValue();
        $technik->prijmeni = $form['prijmeni']->getValue();

        $oblast = new Oblast();
        $oblast->nazev = $form['oblast']->getSelectedItem();
        $oblast->fetch();
        
        $technik->id_oblast = $oblast->id_oblast;
        $technik->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting engeneer
     * @param type $id id of deleted engeneer
     */
    public function handleDelete($id)
    {
        $technik = new Technik();
        $technik->id_technik = urldecode($id);
        try
        {
            $technik->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('V průběhu zpracování nastala chyba. Technika není možné odebrat.','error');
        }
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('list');
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

        $this->template->items = $this -> model -> getTechnici(array("jmeno" => "ASC"));
    }

    /**
     * Action edit
     * @param type $id Editted engeneer
     */
    public function actionEdit($id) {

    }

    /**
     * Render edit
     * @param type $id Editted engeneer
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $technik = new Technik();
        $technik->id_technik = $id;
        if ($technik->fetch())
         {
            $this["upravitTechnika"]["id"]->setValue($technik->id_technik);
            $this["upravitTechnika"]["jmeno"]->setValue($technik->jmeno);
            $this["upravitTechnika"]["prijmeni"]->setValue($technik->prijmeni);
            $oblast = new Oblast();
            $oblast->id_oblast = $technik->id_oblast;

            if ($oblast->fetch())
                $this["upravitTechnika"]["oblast"]->setValue($technik->id_oblast);
         }
    }
   
    /**
     * Singleton for TechniciModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->techniciModel))
            $this->techniciModel = new TechniciModel();

        return $this->techniciModel;
    }

    /**
     * Singleton for OblastiModel
     * @return type 
     */
    public function getOblastiModel() {
        if(!isset($this->oblastiModel_var))
            $this->oblastiModel_var = new OblastiModel();

        return $this->oblastiModel_var;
    }
}