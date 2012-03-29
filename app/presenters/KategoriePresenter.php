<?php
use Nette\Application\UI\Form;
/**
 * Description of KategoriePresenter
 *
 * @author mist
 */
class KategoriePresenter extends BasePresenter {

    private $kategorieModel_var = NULL;
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    /**
     * Form for adding new category
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatKategorii($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->getElementPrototype()->class('ajax');
        $form->addText('nazev', 'Název kategorie:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název kategorie.');
        
        $form->addSubmit('novaKategorie', 'Přidat');
        $form->onSuccess[] = array($this, 'novaKategorie_submit');
        return $form;
    }
    
    /**
     * Button for adding new category
     * @param type $form name of form
     */
    public function novaKategorie_submit($form)
    {
        $kategorie = new Kategorie();
        $kategorie->nazev = $form['nazev']->getValue();
        
        $this->model->addKategorie($kategorie);

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('form');
            $this->invalidateControl('kategorie');
        }

    }
    
    /**
     * Form for editting category
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitKategorii($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('nazev', 'Název kategorie:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte název kategorie.');
        $form->addHidden('id');
        $form->addSubmit('editKategorii', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editKategorie_submit');
        return $form;
    }
    
    /**
     * Button for saving changes in category
     * @param type $form name of form
     */
    public function editKategorie_submit($form)
    {
        $kategorie = new Kategorie();
        $kategorie->id_kategorie = $form['id']->getValue();
        $kategorie->nazev = $form['nazev']->getValue();
        
        $kategorie->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting category
     * @param type $id id of category
     */
    public function handleDelete($id)
    {
        $kategorie = new Kategorie();
        $kategorie ->id_kategorie = urldecode($id);
        try {
            $kategorie ->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('V kategorie se pravděpodobně nachází nějaké zboží nebo je v ní nějaká objednávka. Není možné ji odebrat. Odstraňte nejdříve všechna zboží a objednávky společná s touto oblastí.','error');
        }
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('kategorie');
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
     * render default
     */
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $this->template->items = $this -> model -> getKategorie();
    }

    /**
     * action edit
     * @param type $id id of category to edit
     */
    public function actionEdit($id) {
        
    }

    /**
     * render edit
     * @param type $id id of category to edit
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $kategorie = new Kategorie();
        $kategorie->id_kategorie = $id;
        if ($kategorie->fetch())
         {
            $this["upravitKategorii"]["id"]->setValue($kategorie->id_kategorie);
            $this["upravitKategorii"]["nazev"]->setValue($kategorie->nazev);
         }        
    }
    
    /**
     * Singleton for category
     * @return type 
     */
    public function getModel() {
        if(!isset($this->kategorieModel_var))
            $this->kategorieModel_var = new KategorieModel();

        return $this->kategorieModel_var;
    }
}