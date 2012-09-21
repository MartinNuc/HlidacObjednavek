<?php
use Nette\Forms\Container;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
/**
 * Description of OpravyPresenter
 *
 * @author mist
 */
class OpravyPresenter extends BasePresenter {

    private $skupinyModel_var = NULL;
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
        Kdyby\Extension\Forms\Replicator\Replicator::register();
    }

    protected function createComponentMyForm($name)
    {
        $form = new Form;
        
        $skupiny = $this->model->getSkupiny();
        foreach ($skupiny as $skupina)
        {
            $form->addGroup($skupina->nazev, TRUE);
            // jméno, továrnička, výchozí počet
            $replicator = $form->addDynamic('polozka' . $skupina->id_skupina, function (Container $container) use ($skupina) {
            $container->addText('popis' . $skupina->id_skupina, 'Popis');
            $container->addText('cena' . $skupina->id_skupina, 'Cena')->setRequired();

            $container->addSubmit('remove' . $skupina->id_skupina, 'Smazat')
            ->addRemoveOnClick();
            }, 1);
            
            $replicator->addSubmit('add' . $skupina->id_skupina, 'Přidat')
            ->addCreateOnClick(TRUE);

        }
        $form->addSubmit('send', 'Zpracovat')
        ->onClick[] = callback($this, 'MyFormSubmitted');
        $this[$name] = $form;
        return $form;
    }



    /**
    * @param SubmitButton $button
    */
    public function MyFormSubmitted(SubmitButton $button)
    {
        // jenom naplnění šablony, bez přesměrování
        $this->getSession('values')->users = $button->form->values;
        $this->redirect('this');
    }
    
	
    public function actionDefault() {
        
    }
    
    public function renderDefault() {
    } 

    public function actionEdit() {
        
    }

    public function renderEdit() {
        
    }

    public function renderSeznam() {
        
    }
    
    
    public function getModel() {
        if(!isset($this->skupinyModel_var))
            $this->skupinyModel_var = new SkupinyModel();

        return $this->skupinyModel_var;
    }

}