<?php
use Nette\Application\UI\Form;
/**
 * Description of SpravaOblasti
 *
 * @author mist
 */
class OblastiPresenter extends BasePresenter {

    private $obchodniZastupciModel_var = NULL;
    private $oblastiModel_var = NULL;
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Form for adding new Area
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatOblast($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->addText('nazev', 'Název oblasti:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název oblasti.');
        
        $pole = $this->obchodniZastupciModel->getObchodniZastupce()->fetchPairs('id_obchodni_zastupce','jmeno');
        $form->addSelect('obchodni_zastupce', 'Obchodní zástupce:', $pole);

        $form->addSubmit('novaOblast', 'Přidat');
        $form->onSuccess[] = array($this, 'novaOblast_submit');
        return $form;
    }
    
    /**
     * Button for creating new Area
     * @param type $form name of form
     */
    public function novaOblast_submit($form)
    {
        $oblast = new Oblast();
        $oblast->nazev = $form['nazev']->getValue();
        $oblast->id_obchodni_zastupce = $form['obchodni_zastupce']->getValue();
        
        $this->model->addOblast($oblast);
        if (!$this->isAjax())
            $this->redirect('this', $id_zakaznik);
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('form');
            $this->invalidateControl('oblasti');
        }
    }
    
    /**
     * Form for editting area
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitOblast($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('nazev', 'Název:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název oblasti.');
        $pole = $this->obchodniZastupciModel->getObchodniZastupce()->fetchPairs('id_obchodni_zastupce','jmeno');
        $form->addSelect('obchodni_zastupce', 'Obchodní zástupce:', $pole);
        
        $form->addHidden('id');
        $form->addSubmit('editOblast', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editOblast_submit');
        return $form;
    }
    
    /**
     * Button for editting area
     * @param type $form name of form
     */
    public function editOblast_submit($form)
    {
        $oblast = new Oblast();
        $oblast->id_oblast = $form['id']->getValue();
        $oblast->nazev = $form['nazev']->getValue();
        $oblast->id_obchodni_zastupce = $form['obchodni_zastupce']->getValue();
        
        $oblast->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting area
     * @param type $id id of deleted area
     */
    public function handleDelete($id)
    {
        $oblast = new Oblast();
        $oblast->id_oblast = urldecode($id);
        try
        {
            $oblast->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('V oblasti se nachází automaty nebo je spravují technici, proto není možné oblast smazat.','error');
        }
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('oblasti');
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

        $this->template->items = $this -> model -> getOblasti("nazev", array(array("id_oblast != %i", "0")));
    }

    /**
     * Action edit
     * @param type $id id of editted area
     */
    public function actionEdit($id) {
        
    }

    /**
     * Render edit
     * @param type $id id of editted area
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $oblast = new Oblast();
        $oblast->id_oblast = $id;
        if ($oblast->fetch())
         {
            $this["upravitOblast"]["id"]->setValue($oblast->id_oblast);
            $this["upravitOblast"]["obchodni_zastupce"]->setValue($oblast->id_obchodni_zastupce);
            $this["upravitOblast"]["nazev"]->setValue($oblast->nazev);
         }
    }
    
    /**
     * Singleton for OblastiModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->oblastiModel_var))
            $this->oblastiModel_var = new OblastiModel();

        return $this->oblastiModel_var;
    }
    
    /**
     * Singleton for ObchodniZastupciModel
     * @return type 
     */
    public function getObchodniZastupciModel() {
        if(!isset($this->obchodniZastupciModel_var))
            $this->obchodniZastupciModel_var = new ObchodniZastupciModel();

        return $this->obchodniZastupciModel_var;
    }

}