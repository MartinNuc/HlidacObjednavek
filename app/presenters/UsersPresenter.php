<?php
use Nette\Application\UI\Form;
/**
 * Description of SpravaOblasti
 *
 * @author mist
 */
class UsersPresenter extends BasePresenter {

    private $usersModel_var = NULL;
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * Form for adding new user
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentPridatUser($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $form->addText('username', 'Přihlašovací jméno:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte přihlašovací jméno.');
        $form->addPassword('password', 'Heslo:');

        $pole = array("Uživatel", "Administrátor");
        $form->addSelect('role', 'Role:', $pole);
        
        $form->addSubmit('novyUser', 'Přidat');
        $form->onSuccess[] = array($this, 'novyUser_submit');
        return $form;
    }
    
    /**
     * Button for creating new user
     * @param type $form name of form
     */
    public function novyUser_submit($form)
    {
        $user = new User();
        $user->username = $form['username']->getValue();
        $user->password = $form['password']->getValue();
        $user->role = $form['role']->getSelectedItem();
        
        $this->model->addUser($user);
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('list');
            $this->invalidateControl('form');
        }
    }
    
    /**
     * Form for editting user
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentUpravitUser($name)
    {
        $form = new Form($this, $name);
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        
        $form->addText('username', 'Přihlašovací jméno:')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte přihlašovací jméno.');
        $form->addPassword('password', 'Heslo:');

        $pole = array("Uživatel", "Administrátor");
        $form->addSelect('role', 'Role:', $pole);
        
        $form->addHidden('id');
        $form->addSubmit('editUser', 'Uložit');
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'editUser_submit');
        return $form;
    }
    
    /**
     * Button for saving changes in User
     * @param type $form name of form
     */
    public function editUser_submit($form)
    {
        $user = new User();
        $user->id_user = $form['id']->getValue();
        $user->username = $form['username']->getValue();
        $user->password = $form['password']->getValue();
        $user->role = $form['role']->getSelectedItem();
        
        $user->save();
                
        $this->redirect('default');
    }
    
    /**
     * AJAX request for deleting User
     * @param type $id Id of deleted user
     */
    public function handleDelete($id)
    {
        $user = new User();
        $user->id_user = urldecode($id);
        try
        {
            $user->delete();
        }
        catch (DibiDriverException $e)
        {
            $this->flashMessage('Uživatele se nepodařilo smazat.','error');
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

        $this->template->items = $this -> model -> getUsers();
    }

    /**
     * Action edit user
     * @param type $id Id of editted User
     */
    public function actionEdit($id) {
        
    }

    /**
     * Render edit user
     * @param type $id Id of editted User
     */
    public function renderEdit($id) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        $user = new User();
        $user->id_user = $id;
        if ($user->fetch())
         {
            $this["upravitUser"]["id"]->setValue($user->id_user);
            $this["upravitUser"]["username"]->setValue($user->username);
            if ($user->role == "Administrátor")
                $this["upravitUser"]["role"]->setValue(1);
            else
                $this["upravitUser"]["role"]->setValue(0);
         }
    }
    
    /**
     * Singleton for Users Model
     * @return type 
     */
    public function getModel() {
        if(!isset($this->usersModel_var))
            $this->usersModel_var = new UsersModel();

        return $this->usersModel_var;
    }

}