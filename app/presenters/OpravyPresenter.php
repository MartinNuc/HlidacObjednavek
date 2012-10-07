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
    private $kontaktyModel_var = NULL;
    private $automatyModel_var = NULL;
    
    /** @persistent */
    public $id_automat = "";
    
    /** @persistent */
    public $filtr_od = NULL;
    /** @persistent */
    public $filtr_do = NULL;

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
        $that = $this;
        return new Multiplier(function ($itemId) use ($that) {
            $form = new Nette\Application\UI\Form;
            $form->getElementPrototype()->class('ajax');
            $renderer = $form->getRenderer();

            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['label']['container'] = NULL;
            $renderer->wrappers['control']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div')->class('oprava_polozka');

            $form->addText('pocet', '')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte počet.')->addRule(Form::INTEGER, 'Zadejte číslo.');
            $form->addText('popis', '')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte popis.');
            $form->addText('cena', '')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte cenu.')->addRule(Form::FLOAT, 'Zadejte číslo.');
            $form->addHidden('itemId', $itemId);
            $form->addSubmit('novaPolozka', 'Přidat')->setAttribute('class', 'btnPolozka');
            $form->onSuccess[] = callback($that, 'novaPolozka_submit');
            return $form;
        });
    }
    
    public function createComponentPridatPolozku($name)
    {
        $form = new Form($this, $name);
        $form->addText('pocet', 'Počet')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte počet.')->addRule(Form::INTEGER, 'Zadejte číslo.');
        $form->addText('popis', 'Popis')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte popis.');
        $form->addText('cena', 'Cena')->setAttribute('autoComplete', "off")->addRule(Form::FILLED, 'Zadejte cenu.')->addRule(Form::FLOAT, 'Zadejte číslo.');
        
        foreach ($this->model->getSkupiny() as $key => $value)
            $pole[$value->id_skupina]=$value->nazev;
        
        $form->addSelect('skupina', 'Skupina', $pole);

        $form->addHidden('id');
        $form->addSubmit('novaPolozka', 'Přidat');

        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        $form->onSuccess[] = callback($this, 'pridatPolozku_submit');
        return $form;
    }
    
    /**
     * Button for editting area
     * @param type $form name of form
     */
    public function pridatPolozku_submit($form)
    {
        $p = new Akce();
        $p->cena = $form['cena']->getValue();
        $p->popis = $form['popis']->getValue();
        $p->pocet = $form['pocet']->getValue();
        $p->id_oprava = $form['id']->getValue();
        $p->id_skupina = $form['skupina']->getValue();
        // ulozit
        $this->akceModel->addAkce($p);

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form['pocet']->setValue("");
            $form['popis']->setValue("");
            $form['cena']->setValue("");
            $this->invalidateControl('stranky');
        }
    }
    
    public function novaPolozka_submit($form)
    {
        // pridat do session polozku
        if ($form['itemId']->getValue() != "")
        {
            $pol = new PolozkaOpravy();
            $pol->pocet = $form['pocet']->getValue();
            $pol->popis = $form['popis']->getValue();
            $pol->cena = $form['cena']->getValue();
            $pol->id_skupina = $form['itemId']->getValue();
            $this->context->polozkyOpravy->add($pol);
        }

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $form['pocet']->setValue("");
            $form['popis']->setValue("");
            $form['cena']->setValue("");
            $this->invalidateControl('oprava');
        }
    }
    
   public function handleDelete($id_polozka)
    {
        $this->context->polozkyOpravy->remove($id_polozka);
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('oprava');
        }
    }	
    
    public function handleDeleteAkce($id_akce)
    {
        $akce = new Akce();
        $akce->id_akce = $id_akce;
        $akce->delete();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }	
    
    public function handleDeleteOprava($id_oprava)
    {
        $akce = new Oprava();
        $akce->id_oprava = $id_oprava;
        $akce->delete();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
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
            if ($oprava->datum == null)
                $oprava->datum = date('Y-m-d');
            $oprava->id=$this->opravyModel->addOprava($oprava);

            foreach ($polozky as $polozka)
            {
                $p = new Akce();
                $p->cena = $polozka["cena"];
                $p->popis = $polozka["popis"];
                $p->pocet = $polozka["pocet"];
                $p->id_oprava = $oprava->id;
                $p->id_skupina = $polozka["id_skupina"];
                // ulozit
                $this->akceModel->addAkce($p);
            }
        }
        
        $polozky = $this->context->polozkyOpravy->clean();
        
        if (!$this->isAjax())
            $this->redirect('opravy:seznam', $this->id_automat);
        else {
            $this->invalidateControl('oprava');
        }
    }	
    
    public function createComponentFiltrOpravy($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div')->class('obj_filtr');
        
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y',strtotime($this->filtr_od)));
        $form->addDatePicker('do', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y'));

        $form->addSubmit('filtrOpravy', 'Zobrazit opravy');
        $form->onSuccess[] = callback($this, 'filtrOpravy_submit');
        return $form;
    }
    
    public function filtrOpravy_submit($form)
    {
        $datum = $form['od']->getValue();
        if ($datum == "")
            $this->filtr_od = NULL;
        else
            $this->filtr_od = $datum->format("Y-n-j");
        
        $datum = $form['do']->getValue();
        if ($datum == "")
            $this->filtr_do = NULL;
        else
            $this->filtr_do = $datum->format("Y-n-j");

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('historie');
        }
    }    
    
    public function actionDefault($id_automat) {
        if ($id_automat == null)
            $this->redirect('hlidac:default');
        
        $this->id_automat = $id_automat;        
    }
    
    public function renderDetail($id_oprava)
    {
        if ($id_oprava == null)
            $this->redirect('hlidac:default');
        
        $this["pridatPolozku"]["id"]->setValue($id_oprava);
        
        //SELECT sum(cena*pocet) FROM opravy o left join akce using (id_oprava) group by id_oprava
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this->akceModel->getAkce(array("datum" => "DESC"), array("id_oprava" => $id_oprava), $paginator->offset, $paginator->itemsPerPage));
        $this->template->items = $this->akceModel->getAkce(array("datum" => "DESC"), array("id_oprava" => $id_oprava), $paginator->offset, $paginator->itemsPerPage);
        $this->template->id_automat = $this->id_automat;
        
        $automat = $this -> automatyModel -> getAutomaty(NULL, array(
                'id_automat' => $this->id_automat))->fetch();
        $this->template->automat = $automat;
    }
    
    public function renderDefault($id_automat) {
        if ($id_automat == null)
            $this->redirect('hlidac:default');
                
        $this->id_automat = $id_automat;

        if ((isset($this->filtr_od) && isset($this->filtr_od)) == false)
        {
            $this->filtr_od = Date('Y-n-j',mktime(0,0,0,date('m')-5,date('d'),date('y')));
            $this->filtr_do = Date('Y-n-j');
        }
        
        // zjistit skupiny z DB
        $this->template->skupiny = $this -> model -> getSkupiny();
        
        //
        // trojrozmerne pole [idskupiny][poradi polozky][sloupec]
        //
        $this->template->polozky = array();
        $cena = 0;
        foreach ($this->context->polozkyOpravy->getItems() as $polozka)
        {
            $p = new PolozkaOpravy();
            $p->cena = $polozka["cena"];
            $p->popis = $polozka["popis"];
            $p->pocet = $polozka["pocet"];
            
            $cena += $p->cena * $p->pocet;
            
            $p->id = $polozka["id"];
            $p->id_skupina = $polozka["id_skupina"];
            $this->template->polozky[] = $p;
            //Debugger::log($p->id . " -> " . $p->popis);
        }
        $this->template->cena = $cena;
        
        $automat = new Automat();
        $automat->id_automat = $id_automat;
        //$automat->fetch();
        $automat = $this -> automatyModel -> getAutomaty(NULL, array(
                'id_automat' => $id_automat))->fetch();
        $this->template->automat = $automat;
        $this->template->kontakty = $this -> kontaktyModel -> getKontaktyInContext(NULL, array(
            'id_automat' => $id_automat));
        
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this->opravyModel->getOpravy(array("datum" => "DESC"), 
                array("id_automat" => $id_automat, array("datum <= %d", $this->filtr_do), array("datum >= %d", $this->filtr_od)),
                $paginator->offset, $paginator->itemsPerPage));
        //$this->template->items = $this->opravyModel->getOpravy(array("datum" => "DESC"),
        //        array("id_automat" => $id_automat, array("datum <= %d", $this->filtr_do), array("datum >= %d", $this->filtr_od)),
        //        $paginator->offset, $paginator->itemsPerPage);
        $this->template->historie = $this->opravyModel->getOpravy(array("datum" => "DESC"),
                array("id_automat" => $id_automat, array("datum <= %d", $this->filtr_do), array("datum >= %d", $this->filtr_od)),
                $paginator->offset, $paginator->itemsPerPage);
        
        $this->template->detail=array();
        foreach ($this->template->historie as $oprava)
        {
            // skupiny, ktere nejsou smazane
            $skupiny = $this->model->getSkupiny();
            $output = "";
            foreach ($skupiny as $skupina)
            {
                $akce = $this->akceModel->getAkce(NULL, array("id_skupina" => $skupina->id_skupina, "id_oprava" => $oprava->id_oprava));
                $output .= '<div class="hist_skupina"><div class="hist_skupina_nazev">' . $skupina->nazev . '</div>';
                foreach ($akce as $a)
                {
                    $output .= '<div class="hist_akce">';
                    $output .= '<div class="hist_pocet">' . $a->pocet . "</div>";
                    $output .= '<div class="hist_popis">' . $a->popis . "</div>";
                    $output .= "</div>";
                }
                $output .= "</div>";
            }

            // pokud ma nejake smazane skupiny, tak vypisem
            $skupiny = $this->akceModel->getDeletedSkupinyOfAkce(NULL, $oprava->id_oprava, NULL, NULL);

            foreach ($skupiny as $skupina)
            {
                $akce = $this->akceModel->getAkce(NULL, array("id_skupina" => $skupina->id_skupina, "id_oprava" => $oprava->id_oprava));
                $output .= '<div class="hist_skupina"><div class="hist_skupina_nazev">' . $skupina->nazev . "</div>";
                foreach ($akce as $a)
                {
                    $output .= '<div class="hist_akce">';
                    $output .= '<div class="hist_pocet">' . $a->pocet . "</div>";
                    $output .= '<div class="hist_popis">' . $a->popis . "</div>";
                    $output .= "</div>";
                }
                $output .= "</div>";
            }

            $this->template->detail[$oprava->id_oprava] = $output;
        }
    } 

    public function actionEdit() {
        
    }

    public function renderEdit() {
        
    }

    public function actionSeznam($id_automat) {
        if ($id_automat == null)
            $this->redirect('hlidac:default');
    }
    
    public function renderSeznam($id_automat) {
        if ($id_automat == null)
            $this->redirect('hlidac:default');
        
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this->opravyModel->getOpravy(array("datum" => "DESC"), array("id_automat" => $id_automat), $paginator->offset, $paginator->itemsPerPage));
        $this->template->items = $this->opravyModel->getOpravy(array("datum" => "DESC"), array("id_automat" => $id_automat), $paginator->offset, $paginator->itemsPerPage);
        $this->template->id_automat = $id_automat;
        $automat = new Automat();
        $automat->id_automat = $id_automat;
        $automat->fetch();
        $this->template->automat = $automat;

        $automat = $this -> automatyModel -> getAutomaty(NULL, array(
                'id_automat' => $this->id_automat))->fetch();
        $this->template->automat = $automat;
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

    public function getKontaktyModel() {
        if(!isset($this->kontaktyModel_var))
            $this->kontaktyModel_var = new KontaktyModel();

        return $this->kontaktyModel_var;
    }
    
   public function getAutomatyModel() {
        if(!isset($this->automatyModel_var))
            $this->automatyModel_var = new AutomatyModel();

        return $this->automatyModel_var;
    }
}