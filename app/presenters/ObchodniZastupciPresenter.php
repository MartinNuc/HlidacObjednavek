<?php
use Nette\Application\UI\Form;

/**
 * Description of ObchodniZastupciPresenter
 *
 * @author mist
 */
class ObchodniZastupciPresenter extends BasePresenter {

    private $obchodniZastupciModel_var = NULL;
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Form for adding ObchodniZastupce
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatOZ($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->addText('jmeno', 'Jméno a příjmení:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno obchodního zástupce.');
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email:')->setAttribute('autoComplete', "off")
                ->addCondition(Form::MIN_LENGTH, 1) 
                ->addRule(Form::EMAIL, 'Zadejte platný email.');
        
        $form->addSubmit('novyObchodniZastupce', 'Přidat');
        $form->onSuccess[] = array($this, 'novyObchodniZastupce_submit');
        return $form;
    }
    
    /**
     * Button for creating new ObchodniZastupce
     * @param type $form for FW
     */
    public function novyObchodniZastupce_submit($form)
    {
        $oz = new ObchodniZastupce();
        $oz->jmeno = $form['jmeno']->getValue();
        $oz->telefon = $form['telefon']->getValue();
        $oz->email = $form['email']->getValue();
        
        $this->model->addObchodniZastupce($oz);
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('form');
            $this->invalidateControl('list');
        }
    }

    /**
     * Form for editting ObchodniZastupce
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitObchodnihoZastupce($name)
    {
        $form = new Form($this, $name);
                $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->addText('jmeno', 'Jméno a příjmení:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte jméno obchodního zástupce.');
        $form->addText('telefon', 'Telefon:')->setAttribute('autoComplete', "off");
        $form->addText('email', 'Email:')->setAttribute('autoComplete', "off")
                ->addCondition(Form::MIN_LENGTH, 1) 
                ->addRule(Form::EMAIL, 'Zadejte platný email.');
        
        $form->addHidden('id');
        $form->addSubmit('editObchodniZastupce', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editObchodniZastupce_submit');
        return $form;
    }
    
    /**
     * Button for edit obchodni zastupce
     * @param type $form name of form
     */
    public function editObchodniZastupce_submit($form)
    {
        $oz = new ObchodniZastupce();
        $oz->id_obchodni_zastupce = $form['id']->getValue();
        $oz->jmeno = $form['jmeno']->getValue();
        $oz->telefon = $form['telefon']->getValue();
        $oz->email = $form['email']->getValue();
        
        $oz->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting ObchodniZastupce
     * @param type $id id of ObchodniZastupce
     */
    public function handleDelete($id)
    {
        $oz = new ObchodniZastupce();
        $oz->id_obchodni_zastupce = urldecode($id);
        try
        {
            $oz->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('Obchodního zástupce není možné smazat, protože má pravděpodobně na starost nějakou oblast.','error');
        }
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('list');
        }
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
     * action default
     */
    public function actionDefault() {
        
    }

    /**
     * action render
     */
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $this->template->items = $this -> model -> getObchodniZastupce("jmeno", array(array("id_obchodni_zastupce != %i", "0")));
    }
    
    /**
     * Action edit
     * @param type $id id of editted ObchodniZastupce
     */
    public function actionEdit($id) {
        
    }

    /**
     * Render edit
     * @param type $id id of editted ObchodniZastupce
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $oz = new ObchodniZastupce();
        $oz->id_obchodni_zastupce = $id;
        if ($oz->fetch())
         {
            $this["upravitObchodnihoZastupce"]["id"]->setValue($oz->id_obchodni_zastupce);
            $this["upravitObchodnihoZastupce"]["jmeno"]->setValue($oz->jmeno);
            $this["upravitObchodnihoZastupce"]["telefon"]->setValue($oz->telefon);
            $this["upravitObchodnihoZastupce"]["email"]->setValue($oz->email);
         }
    }
    
    /**
     * Singleton for ObchodniZastupce
     * @return type 
     */
    public function getModel() {
        if(!isset($this->obchodniZastupciModel_var))
            $this->obchodniZastupciModel_var = new ObchodniZastupciModel();

        return $this->obchodniZastupciModel_var;
    }
}