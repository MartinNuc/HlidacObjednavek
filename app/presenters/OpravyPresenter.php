<?php
use Nette\Forms\Container;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;
/**
 * Description of OpravyPresenter
 *
 * @author mist
 */
class OpravyPresenter extends BasePresenter {

    private $skupinyModel_var = NULL;
    
    protected function startup() {
        parent::startup();
    }

    protected function createComponentOpravaPolozkaForm()
    {
        return new Multiplier(function ($itemId) {
            $form = new Nette\Application\UI\Form;
            $form->getElementPrototype()->class('ajax');
            $renderer = $form->getRenderer();

            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['label']['container'] = NULL;
            $renderer->wrappers['control']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div')->class('oprava_polozka');

            $form->addText('popis', '')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte popis.');
            $form->addText('cena', '')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte cenu.');
            $form->addHidden('itemId', $itemId);
            $form->addSubmit('novaPolozka', 'Přidat')->setAttribute('class', 'btnPolozka');
            $form->onSuccess[] = callback($this, 'novaPolozka_submit');
            return $form;
        });
    }
    
    public function novaPolozka_submit($form)
    {
        // pridat do session polozku
        
        $pol = new PolozkaOpravy();
        $pol->popis = $form['popis']->getValue();
        $pol->cena = $form['cena']->getValue();
        $pol->id_skupina = $form['itemId']->getValue();

        $this->context->polozkyOpravy->add($pol);

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form->setValues(array(), TRUE);
            $this->invalidateControl('oprava');
        }
    }
    
    public function handleDelete($id)
    {
        $this->context->polozkyOpravy->remove($id);
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('oprava');
        }
    }	
    
    public function actionDefault() {
        
    }
    
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        // zjistit skupiny z DB
        $this->template->skupiny = $this -> model -> getSkupiny();
        
        //
        // trojrozmerne pole [idskupiny][poradi polozky][sloupec]
        //
        $this->template->polozky = array();
        foreach ($this->context->polozkyOpravy->getItems() as $polozka)
        {
            $p = new PolozkaOpravy();
            $p->cena = $polozka["cena"];
            $p->popis = $polozka["popis"];
            $p->id = $polozka["id"];
            $p->id_skupina = $polozka["id_skupina"];
            $this->template->polozky[] = $p;
        }
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