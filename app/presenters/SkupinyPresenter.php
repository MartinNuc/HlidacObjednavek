<?php
use Nette\Application\UI\Form;

/**
 * Description of SkupinyPresenter
 *
 * @author mist
 */
class SkupinyPresenter extends BasePresenter {

    private $skupinyModel_var = NULL;
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    public function createComponentPridatSkupinu($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->addText('nazev', 'Název skupiny:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název skupiny.');
        
        $form->addSubmit('novaSkupina', 'Přidat');
        $form->onSuccess[] = array($this, 'novaSkupina_submit');
        return $form;
    }
    
    public function novaSkupina_submit($form)
    {
        $skupina = new Skupina();
        $skupina->nazev = $form['nazev']->getValue();
        
        $this->model->addSkupina($skupina);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('form');
            $this->invalidateControl('skupiny');
        }
    }
    
    public function createComponentUpravitSkupinu($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('nazev', 'Název:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název skupiny.');
        
        $form->addHidden('id');
        $form->addSubmit('editSkupinu', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editSkupinu_submit');
        return $form;
    }
    
    public function editSkupinu_submit($form)
    {
        $skupina = new Skupina();
        $skupina->id_skupina = $form['id']->getValue();
        $skupina->nazev = $form['nazev']->getValue();
        
        $skupina->save();
                
        $this->redirect('default');
    }
    
    public function handleDelete($id)
    {
        $skupina = new Skupina();
        $skupina->id_skupina = urldecode($id);
        try
        {
            $skupina->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('Nastala chyba.','error');
        }
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('skupiny');
        }
    }
    
    public function goBack_submit($form)
    {     
        $this->redirect('default');
    }

    public function actionDefault() {
        
    }

    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $this->template->items = $this -> model -> getSkupiny("nazev");
    }

    public function actionEdit($id) {
        
    }

    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $skupina = new Skupina();
        $skupina->id_skupina = $id;
        if ($skupina->fetch())
         {
            $this["upravitSkupinu"]["id"]->setValue($skupina->id_skupina);
            $this["upravitSkupinu"]["nazev"]->setValue($skupina->nazev);
         }
    }
    
    public function getModel() {
        if(!isset($this->skupinyModel_var))
            $this->skupinyModel_var = new SkupinyModel();

        return $this->skupinyModel_var;
    }
    

}