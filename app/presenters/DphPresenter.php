<?php
use Nette\Application\UI\Form;
/**
 * Description of DphPresenter
 *
 * @author mist
 */
class DphPresenter extends BasePresenter {

    private $dphModel_var = NULL;
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Form for adding new TAX value
     * @param type $name form name
     * @return Form for FW
     */
    public function createComponentPridatDph($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->addText('dph', 'Sazba DPH:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte sazbu DPH.')->addRule(Form::PATTERN, "Zadejte sazbu DPH jako číslo.", "^\d{1,2}\s%|^\d{1,2}");
        
        $form->addSubmit('noveDph', 'Přidat');
        $form->onSuccess[] = array($this, 'noveDph_submit');
        return $form;
    }
    
    /**
     * Button for submiting new TAX value
     * @param type $form for FW
     */
    public function noveDph_submit($form)
    {
        $dph = new Dph();
        $res = $form['dph']->getValue();
        $dph->dph = trim(str_replace('%', '', $res));
        
        $this->model->addDph($dph);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('list');
            $this->invalidateControl('form');
        }
    }
    
    /**
     * Form for editting TAX value
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitDph($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('dph', 'Sazba DPH:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte sazbu DPH.')->addRule(Form::PATTERN, "Zadejte sazbu DPH jako číslo.", "^\d{1,2}\s%|^\d{1,2}");;
        $form->addHidden('id');
        $form->addSubmit('editDph', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editDph_submit');
        return $form;
    }
    
    /**
     * Back button
     * @param type $form name of forms
     */
    public function goBack_submit($form)
    {     
        $this->redirect('default');
    }

    /**
     * Button for editting TAX form
     * @param type $form name of form
     */
    public function editDph_submit($form)
    {
        $dph = new Dph();
        $dph->id_dph = $form['id']->getValue();
        $res = $form['dph']->getValue();
        $dph->dph = trim(str_replace('%', '', $res));
        
        $dph->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting tax value
     * @param type $id id of deleted DPH entity
     */
    public function handleDelete($id)
    {
        $dph = new Dph();
        $dph->id_dph = urldecode($id);
        try
        {
            if ($dph->delete() == false)
                $this->flashMessage('DPH se pravděpodobně používá. Není možné ho odebrat.','error');
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('DPH se pravděpodobně používá. Není možné ho odebrat.','error');
        }
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('list');
        }
    }

    /**
     * Action default of presenter
     */
    public function actionDefault() {
        
    }
    
    /**
     * Render default of presenter
     */
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $this->template->items = $this -> model -> getDph();
    }
    
    /**
     * Action edit
     * @param int $id id of editted TAX value
     */
    public function actionEdit($id) {
        
    }
    
    /**
     * Render edit
     * @param int $id id of editted TAX value
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $dph = new Dph();
        $dph->id_dph = $id;
        if ($dph->fetch())
         {
            $this["upravitDph"]["id"]->setValue($dph->id_dph);
            $this["upravitDph"]["dph"]->setValue($dph->dph . " %");
         }
    }
    
    /**
     * Singleton for DPH model
     * @return type 
     */
    public function getModel() {
        if(!isset($this->dphModel_var))
            $this->dphModel_var = new DphModel();

        return $this->dphModel_var;
    }

}