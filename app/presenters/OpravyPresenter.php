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
    private $akceModel_var = NULL;
    private $opravyModel_var = NULL;
    
    /** @persistent */
    public $id_automat = "";
    
    protected function startup() {
        parent::startup();
    }
    
    protected function createComponentZadatOpravuForm($name)
    {
        $form = new Form($this, $name);
        $form->addDatePicker('datum', "Datum: ")
            ->addRule(Form::VALID, 'Zadané datum není platné.')
            ->setDefaultValue(date("d. m. Y"));
        $form->addSubmit('novaOprava', 'Zadat');
        $form->onSuccess[] = callback($this, 'zadatOpravu');
        return $form;
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
    
    public function handleClean()
    {
        $this->context->polozkyOpravy->clean();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('oprava');
        }
    }	
     
    public function zadatOpravu($form)
    {
        $polozky = $this->context->polozkyOpravy->getItems();
        if (count($polozky) > 0)
        {
            // ulozit novou opravu
            $oprava = new Oprava();
            $oprava->id_automat = $this->id_automat;
            $oprava->datum = $form['datum']->getValue();  // datum objednavky
            $oprava->id=$this->opravyModel->addOprava($oprava);

            foreach ($polozky as $polozka)
            {
                $p = new Akce();
                $p->cena = $polozka["cena"];
                $p->popis = $polozka["popis"];
                $p->id_oprava = $oprava->id;
                $p->id_skupina = $polozka["id_skupina"];
                // ulozit
                $this->akceModel->addAkce($p);
            }
        }
        
        $polozky = $this->context->polozkyOpravy->clean();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('oprava');
        }
    }	
    
    
    public function actionDefault($id_automat = 1) {
        $this->id_automat = $id_automat;        
    }
    
    public function renderDefault($id_automat = 1) {
        $this->id_automat = $id_automat;
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
    
    
    public function getOpravyModel() {
        if(!isset($this->opravyModel_var))
            $this->opravyModel_var = new OpravyModel();

        return $this->opravyModel_var;
    }   
    
    public function getAkceModel() {
        if(!isset($this->akceModel_var))
            $this->akceModel_var = new AkceModel();

        return $this->akceModel_var;
    }

}