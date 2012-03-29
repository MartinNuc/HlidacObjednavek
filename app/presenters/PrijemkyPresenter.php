<?php
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
/**
 * Description of PrijemkyPresenter
 *
 * @author mist
 */
class PrijemkyPresenter extends BasePresenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
        Kdyby\Forms\Containers\Replicator::register();
    }
    
    protected function createComponentMyForm()
    {
        $presenter = $this;
        $form = new Nette\Application\UI\Form;
        //$form->getElementPrototype()->class('ajax');

        // jméno, továrnička, výchozí počet
        $replicator = $form->addDynamic('users', function (Container $container) use ($presenter) {
                $container->currentGroup = $container->form->addGroup('Zboží', FALSE);
                $container->addText('zkratka', 'Zkratka')->setRequired();
                $container->addText('pocet', 'Počet')->setRequired();
                $container->addHidden('id');

                $container->addSubmit('remove', 'Smazat')
                        ->setValidationScope(FALSE)
                        ->onClick[] = callback($presenter, 'MyFormRemoveElementClicked');
        }, 1);

        $replicator->addSubmit('add', 'Přidat další zboží')
                ->setValidationScope(FALSE)
                ->onClick[] = callback($this, 'MyFormAddElementClicked');

        $form->addSubmit('send', 'Uložit')
                ->onClick[] = callback($this, 'MyFormSubmitted');

        return $form;
    }

    /**
     * @param SubmitButton $button
     */
    public function MyFormAddElementClicked(SubmitButton $button)
    {
            $users = $button->parent;

            // spočítat, jestli byly vyplněny políčka
            // ignorovat hodnotu tlačítka
            if ($users->countFilledWithout(array('add')) == count($users->containers)) {
                    // přidá jeden řádek do containeru
                    $button->parent->createOne();
            }
    }

    /**
     * @param SubmitButton $button
     */
    public function MyFormRemoveElementClicked(SubmitButton $button)
    {
            $users = $button->parent->parent;

            // je možné využít hidden prvek, pro uložení ID existujícího záznamu
            // a smazat ho i z databáze
            $id = $button->parent['id']->value;

            // second parameter means cleanup groups
            $users->remove($button->parent, TRUE);
    }
    
    /**
     * @param SubmitButton $button
     */
    public function MyFormSubmitted(SubmitButton $button)
    {
            $users = array();
            foreach ($button->form['users']->values as $user) {
                    if (!array_filter((array)$user)) {
                            continue;
                    }

                    $users[] = (array)$user;
            }

            // jenom naplnění šablony, bez přesměrování
            $this->template->users = $users;
    }
        
    public function actionDefault() {
        
    }

    public function renderDefault() {
        
    }

    public function actionAdd() {
        
    }

    public function renderAdd() {
        
    }

}