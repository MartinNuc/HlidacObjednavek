<?php

use Nette\Application\UI;

/**
 * Sign in/out presenters.
 *
 * @author     Martin Nuc
 * @package    HlidacObjednavek
 */
class SignPresenter extends BasePresenter
{
	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('username', 'Uživatel:')->setAttribute('autoComplete', "off");

		$form->addPassword('password', 'Heslo:');

		$form->addCheckbox('remember', 'Pamatovat přihlášení na tomto počítači');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}


        /**
         * Submitted form when signing in
         * @param type $form name of form
         */
	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			/*if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 1 day', TRUE);
			}*/
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Hlidac:default');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
                        $this->redirect('Sign:in');
		}
	}


        /**
         * Action out - for signing out
         */
	public function actionOut()
        {
		$this->getUser()->logout();
		$this->flashMessage('Byl jste úspěšně odhlášen.');
		$this->redirect('Sign:in');
	}

        /**
         * Render for signing in
         */
        public function renderIn()
        {
            if ($this->getUser()->isLoggedIn())
                $this->redirect('Hlidac:default');
        }
}
