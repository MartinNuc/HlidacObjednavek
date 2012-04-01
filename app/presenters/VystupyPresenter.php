<?php
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;

/**
 * Description of VystupyPresenter
 *
 * @author mist
 */
class VystupyPresenter extends BasePresenter {

    private $zakazniciModel_var = NULL;
    private $kategorieModel_var = NULL;
    private $zboziModel_var = NULL;
    private $objednavkyModel_var = NULL;
    private $oblastiModel_var = NULL;
    private $automatyModel_var = NULL;
    
    private $filtr_od = NULL;
    private $filtr_do = NULL;
    
    private $osobni = 3;
    private $nekupujici = 1;
    private $kategorie = array();
    private $oblasti = array();
    private $skryt_automaty = true;

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }

    public function renderDefault() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

    }
    
    /********* ZBOZI ***********/
    
    public function createComponentFiltrZbozi($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        /*$renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div');*/

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_kategorie'));
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
            $form->addCheckbox('kategorie_' . $kat->id_kategorie, $kat->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('filtrZbozi', 'Nastavit kritéria');
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->onSuccess[] = callback($this, 'filtrZbozi_submit');
        return $form;
    }
    
    /**
     * Button for filtering customers
     * @param type $form name of form
     */
    public function filtrZbozi_submit($form)
    {
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
            if ($form['kategorie_' . $kat->id_kategorie]->getValue() == true)
                $this->kategorie[] = $kat->id_kategorie;

        if (!$this->isAjax())
            $this->redirect('Vystupy:zakaznici');
        else {
            $this->invalidateControl('stranky');
        }
    }

    public function actionZbozi() {
        
    }

    public function renderZbozi() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');
        
        $filtr_kategorii = "";
        foreach ($this->kategorie as $kat)
        {
            if ($this->kategorie[0] == $kat)
                $filtr_kategorii .= "id_kategorie IN (" . $kat;
            else
                $filtr_kategorii .= "," . $kat;
        }
        if ($filtr_kategorii != "")
            $filtr_kategorii .= ")";
        else
            $filtr_kategorii = "1=0";

        // pro kazdou kategorii vypiseme zbozi
        $zbozi = array();
        $soucty_nc_celkem = 0;
        $soucty_pc_celkem = 0;
        $soucty_nc = array();
        $soucty_pc = array();
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
        {
            $zbozi[$kat->id_kategorie] = array();
            $soucty[$kat->id_kategorie] = array();
        }
        $kategorie = $this->kategorieModel->getKategorie(NULL, NULL, NULL, NULL, $filtr_kategorii);
        foreach ($kategorie as $kat)
        {
            $zbozi[$kat->id_kategorie] = $this->zboziModel->getZbozi(array("zkratka" => "ASC"), array("id_kategorie" => $kat->id_kategorie));
            $soucty_nc[$kat->id_kategorie] = 0;
            $soucty_pc[$kat->id_kategorie] = 0;
            foreach ($zbozi[$kat->id_kategorie] as $zb)
            {
                $soucty_nc[$kat->id_kategorie] += $zb->nakupni_cena*$zb->skladem;
                
                $soucty_pc[$kat->id_kategorie] += $zb->prodejni_cena*$zb->skladem;                
            }
            $soucty_nc_celkem += $soucty_nc[$kat->id_kategorie];
            $soucty_pc_celkem += $soucty_pc[$kat->id_kategorie];
        }
        $this->template->zbozi = $zbozi;
        $this->template->soucty_nc = $soucty_nc;
        $this->template->soucty_pc = $soucty_pc ;
        $this->template->soucty_nc_celkem = $soucty_nc_celkem ;
        $this->template->soucty_pc_celkem = $soucty_pc_celkem ;
        $this->template->kategorie = $kategorie;
    }
    
    /********* Automaty ****************/
    
    /**
     * Form for filtering customers
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrAutomaty($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        /*$renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div');*/

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_oblasti'));
        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                $form->addCheckbox('oblast_' . $oblast->id_oblast, $oblast->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_parametry'));
        $form->addCheckbox('osobni', "Osobní zákaznící")->setDefaultValue(true);
        $form->addCheckbox('nestle', "Nestlé zákaznící")->setDefaultValue(true);
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('filtrAutomaty', 'Nastavit kritéria');
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->onSuccess[] = callback($this, 'filtrAutomaty_submit');
        return $form;
    }
    
    /**
     * Button for filtering customers
     * @param type $form name of form
     */
    public function filtrAutomaty_submit($form)
    {
        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                if ($form['oblast_' . $oblast->id_oblast]->getValue() == true)
                    $this->oblasti[] = $oblast->id_oblast;
        
        if ($form['osobni']->getValue() && $form['nestle']->getValue())
            $this->osobni = 3;  // vsechno
        elseif ($form['osobni']->getValue())
            $this->osobni = 1;  // jen osobni
        elseif ($form['nestle']->getValue())
            $this->osobni = 2;  // jen nestle
        else
            $this->osobni = 0;  // ani jeden
        
        if (!$this->isAjax())
            $this->redirect('Vystupy:zakaznici');
        else {
            $this->invalidateControl('stranky');
        }
    }
    
    public function actionAutomaty() {
        
    }

    public function renderAutomaty() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $filtr_oblasti = "";
        foreach ($this->oblasti as $oblast)
        {
            if ($this->oblasti[0] == $oblast)
                $filtr_oblasti .= "id_oblast IN (" . $oblast;
            else
                $filtr_oblasti .= "," . $oblast;
        }
        if ($filtr_oblasti != "")
            $filtr_oblasti .= ")";
        else
            $filtr_oblasti = "1=0";
        
        // zde se hledaji zakaznici
        $where = NULL;
        switch ($this->osobni)
        {
            case 0:
                $where = array(1 => 0);
                break;
            case 1:
                $where = array('osobni_zakaznik' => 1);
                break;
            case 2:
                $where = array('osobni_zakaznik' => 0);
                break;
            case 3:
                $where = NULL;
                break;
        }
        $this->template->kontakt_jmeno = $this->automatyModel->getFirstKontakt (NULL)->fetchPairs("id_automat", "jmeno");
        $this->template->kontakt_email = $this->automatyModel->getFirstKontakt (NULL)->fetchPairs("id_automat", "email");
        $this->template->kava = $this->zboziModel->getZboziPodleSmlouvy (NULL)->fetchPairs("id_zakaznik", "zkratka");
        $this->template->items = $this->automatyModel->getAutomatyVystup(array("zakaznik_nazev" => "ASC"), $where, $filtr_oblasti);
    }

    /********** ZAKAZNICI **************/
    /**
     * Form for filtering customers
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrZakaznici($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        /*$renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div');*/

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_kategorie'));
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
            $form->addCheckbox('kategorie_' . $kat->id_kategorie, $kat->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_oblasti'));
        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                $form->addCheckbox('oblast_' . $oblast->id_oblast, $oblast->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_datum'));
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y',mktime(0,0,0,date('m')-1,date('d'),date('y'))));
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m')-1,date('d'),date('y')));
        $form->addDatePicker('doo', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y'));
        $this->filtr_do = Date('j-n-Y');
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_parametry'));
        $form->addCheckbox('osobni', "Osobní zákaznící")->setDefaultValue(true);
        $form->addCheckbox('nestle', "Nestlé zákaznící")->setDefaultValue(true);
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_parametry'));
        $form->addCheckbox('objednavali', "Objednávající zákaznící")->setDefaultValue(true);
        $form->addCheckbox('neobjednavali', "Neobjednávající zákaznící")->setDefaultValue(false);
        
        $form->addGroup("Volitelné");
        $form->addCheckbox('skryt_automaty', "Skrýt automaty")->setDefaultValue(true);
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('filtrZakaznici', 'Nastavit kritéria');
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->onSuccess[] = callback($this, 'filtrZakazniky_submit');
        return $form;
    }
    
    public function createComponentFiltrTrasy($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        /*$renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div');*/

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_oblasti'));
        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                $form->addCheckbox('oblast_' . $oblast->id_oblast, $oblast->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_datum'));
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y',mktime(0,0,0,date('m'),date('d'),date('y'))));
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
        $form->addDatePicker('doo', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y'));
        $this->filtr_do = Date('j-n-Y');
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('filtrZakaznici', 'Nastavit kritéria');
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->onSuccess[] = callback($this, 'filtrTrasy_submit');
        return $form;
    }

    
    /**
     * Button for filtering customers
     * @param type $form name of form
     */
    public function filtrZakazniky_submit($form)
    {
        $datum = $form['od']->getValue();
        if ($datum == "")
            $this->filtr_od = NULL;
        else
            $this->filtr_od = $datum->format("Y-n-j");
        
        $datum = $form['doo']->getValue();
        if ($datum == "")
            $this->filtr_do = NULL;
        else
            $this->filtr_do = $datum->format("Y-n-j");

        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                if ($form['oblast_' . $oblast->id_oblast]->getValue() == true)
                    $this->oblasti[] = $oblast->id_oblast;
        
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
            if ($form['kategorie_' . $kat->id_kategorie]->getValue() == true)
                $this->kategorie[] = $kat->id_kategorie;
        
        if ($form['osobni']->getValue() && $form['nestle']->getValue())
            $this->osobni = 3;  // vsechno
        elseif ($form['osobni']->getValue())
            $this->osobni = 1;  // jen osobni
        elseif ($form['nestle']->getValue())
            $this->osobni = 2;  // jen nestle
        else
            $this->osobni = 0;  // ani jeden
            
        if ($form['neobjednavali']->getValue() && $form['objednavali']->getValue())
            $this->nekupujici = 3;  // vsechno
        elseif ($form['objednavali']->getValue())
            $this->nekupujici = 1;  // jen kupujici
        elseif ($form['neobjednavali']->getValue())
            $this->nekupujici = 2;  // jen jen nekupujici
        else
            $this->nekupujici = 0;  // ani jeden

        $this->skryt_automaty = $form['skryt_automaty']->getValue();
        
        if (!$this->isAjax())
            $this->redirect('Vystupy:zakaznici');
        else {
            $this->invalidateControl('strankyLong');
        }
    }
    
    public function filtrTrasy_submit($form)
    {
        $datum = $form['od']->getValue();
        if ($datum == "")
            $this->filtr_od = NULL;
        else
            $this->filtr_od = $datum->format("Y-n-j");
        
        $datum = $form['doo']->getValue();
        if ($datum == "")
            $this->filtr_do = NULL;
        else
            $this->filtr_do = $datum->format("Y-n-j");

        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                if ($form['oblast_' . $oblast->id_oblast]->getValue() == true)
                    $this->oblasti[] = $oblast->id_oblast;

        if (!$this->isAjax())
            $this->redirect('Vystupy:trasy');
        else {
            $this->invalidateControl('strankyLong');
        }
    }
    
    public function actionTrasy() {
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
        $this->filtr_do = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
    }

    public function renderTrasy() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $filtr_oblasti = "";
        foreach ($this->oblasti as $oblast)
        {
            if ($this->oblasti[0] == $oblast)
                $filtr_oblasti .= "id_oblast IN (" . $oblast;
            else
                $filtr_oblasti .= "," . $oblast;
        }
        if ($filtr_oblasti != "")
            $filtr_oblasti .= ")";
        else
            $filtr_oblasti = "id_oblast=-1";

        $objednavky = array();
        $objednavky = $this->objednavkyModel->getObjednavkyTrasy(array("id_kategorie" => "ASC","zkratka" => "ASC"), NULL, NULL, NULL, $this->filtr_od, $this->filtr_do, $filtr_oblasti);
        
        $zbozi = array();
        $zbozi = $this->zboziModel->getZbozi();
        $zbozi_cnt = array();
        foreach ($zbozi as $zboz)
            $zbozi_cnt[$zboz->id_zbozi] = 0;
        $zbozi_cnt2 = $zbozi_cnt;
        $zbozi_obj = array();
        foreach ($objednavky as $objednavka)
        {
            // najdeme zbozi z objednavky
            $temp = $this -> zboziModel -> getZboziOdDo($order = array(
                'zkratka' => 'ASC'), array("id_objednavka" => $objednavka->id_objednavka), $this->filtr_od, $this->filtr_do);
            $zbozi_obj[$objednavka->id_objednavka] = $zbozi_cnt;
            foreach ($temp as $zboz)
            {
                $zbozi_obj[$objednavka->id_objednavka][$zboz->id_zbozi] += $zboz->pocet;
                $zbozi_cnt2[$zboz->id_zbozi]++;
            }
        }
        $zbozi_output = array();
        foreach ($zbozi as $zboz)
        {
            if ($zbozi_cnt2[$zboz->id_zbozi] > 0)
                $zbozi_output[] = $zboz;
        }
        
        $this->template->zbozi = $zbozi_output;
        $this->template->objednavky = $objednavky;
        $this->template->zbozi_obj = $zbozi_obj;

        if ($this->isAjax())
            $this->invalidateControl('strankyLong');
    }
    
    public function actionZakaznici() {
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m')-1,date('d'),date('y')));
        $this->filtr_do = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
    }

    public function renderZakaznici() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $filtr_kategorii = "";
        foreach ($this->kategorie as $kat)
        {
            if ($this->kategorie[0] == $kat)
                $filtr_kategorii .= "id_kategorie IN (" . $kat;
            else
                $filtr_kategorii .= "," . $kat;
        }
        if ($filtr_kategorii != "")
            $filtr_kategorii .= ")";
        else
            $filtr_kategorii = "1=0";
        
        $filtr_oblasti = "";
        foreach ($this->oblasti as $oblast)
        {
            if ($this->oblasti[0] == $oblast)
                $filtr_oblasti .= "id_oblast IN (" . $oblast;
            else
                $filtr_oblasti .= "," . $oblast;
        }
        if ($filtr_oblasti != "")
            $filtr_oblasti .= ")";
        else
            $filtr_oblasti = "1=0";
        
        // zde se hledaji zakaznici
        $where = NULL;
        switch ($this->osobni)
        {
            case 0:
                $where = array(1 => 0);
                break;
            case 1:
                $where = array('osobni_zakaznik' => 1);
                break;
            case 2:
                $where = array('osobni_zakaznik' => 0);
                break;
            case 3:
                $where = NULL;
                break;
        }
        
        // zde se hledaji zakaznici, kteri kupuji nebo i ti co nekupuji nebo jen ti co kupuji?
        $zakaznici=array(); 
        switch ($this->nekupujici)
        {
            case 0:  // ani jedni
                $zakaznici = $this -> zakazniciModel -> getZakazniky($order = array(
                    'nazev' => 'ASC'), array(1 => 0));   // prazdny vysledek
                break;
            case 1:  // chceme jenom platici
                $zakaznici = $this -> zakazniciModel -> getZakaznikyVystup($where,$this->filtr_od, $this->filtr_do, true, $filtr_kategorii, $filtr_oblasti);
                break;
            case 2:
                // chceme jenom neplaciti
                $zakaznici = $this -> zakazniciModel -> getZakaznikyVystup($where, $this->filtr_od, $this->filtr_do, false, $filtr_kategorii, $filtr_oblasti);
                break;
            case 3:
                // chceme platici i neplatici
                $zakaznici = $this -> zakazniciModel -> getZakaznikyVystupVse($where, $this->filtr_od, $this->filtr_do, $filtr_kategorii, $filtr_oblasti);
                break;
        }

        $automaty_soucty = array();
        $automaty = array();
        $soucty = array();
        $zbozi = array();
        $zbozi = $this->zboziModel->getZbozi(array("id_kategorie" => "ASC","zkratka" => "ASC"), NULL, NULL, NULL, NULL, $filtr_kategorii);
        
        // pripravim si soucty
        $soucty_celkem = array();
        foreach ($zbozi as $t)
            $soucty_celkem[$t->id_zbozi] = 0;
        
        // pro kazdyho zakaznika udelame soucet toho co koupil
        foreach ($zakaznici as $zakaznik)
        {
            // zde se k zakaznikovi najdou vsechna zbozi, ktera bral
            $temp = $this -> zboziModel -> getZboziOdDo($order = array(
                'zkratka' => 'ASC'), array("id_zakaznik" => $zakaznik->id_zakaznik), $this->filtr_od, $this->filtr_do, $filtr_kategorii, $filtr_oblasti);
            $soucty[$zakaznik->id_zakaznik] = array();
            // zde se nalezena zbozi sectou
            foreach ($zbozi as $t)
                $soucty[$zakaznik->id_zakaznik][$t->id_zbozi] = "";
            foreach ($temp as $t)
            {
                $soucty[$zakaznik->id_zakaznik][$t->id_zbozi] = $t->pocet;
                $soucty_celkem[$t->id_zbozi] += $t->pocet;
            }
            
            //if ($this->skryt_automaty == true)   // TODO: optimalizace, aby se nemuselo zbytecne pocitat ... ale pak chybi v sablone. Nevim co s tim.
            {
                // zde se najdou automaty, do kterych zakaznik bral zbozi (mozna by se mely hledat vsechny automaty? Co kdyz se automat odmontuje?)
                $automaty[$zakaznik->id_zakaznik] = $this->objednavkyModel->getObjednavkyOdDoVystup(NULL,
                        array("id_zakaznik" => $zakaznik->id_zakaznik),
                        NULL, NULL, $this->filtr_od, $this->filtr_do);

                // zde se udelaji soucty pro jednotlive automaty - jede se podle vyrobniho cisla
                foreach ($automaty[$zakaznik->id_zakaznik] as $automat)
                {
                    $temp = $this -> zboziModel -> getZboziOdDo($order = array(
                        'zkratka' => 'ASC'), array("id_zakaznik" => $zakaznik->id_zakaznik,"hledani_vyrobni_cislo" => $automat->vyrobni_cislo, "hledani_bmb" => $automat->bmb), $this->filtr_od, $this->filtr_do, $filtr_kategorii,$filtr_oblasti);
                    foreach ($zbozi as $t)
                        $automaty_soucty[$zakaznik->id_zakaznik . "_" . $automat->vyrobni_cislo . $automat->bmb][$t->id_zbozi] = "";
                    foreach ($temp as $t)
                        $automaty_soucty[$zakaznik->id_zakaznik . "_" . $automat->vyrobni_cislo . $automat->bmb][$t->id_zbozi] = $t->pocet;
                }
            }

        }
        $this->template->zakaznici = $zakaznici;
        $this->template->soucty = $soucty;
        $this->template->soucty_celkem = $soucty_celkem;
        $this->template->zbozi = $zbozi;
        if ($this->skryt_automaty != true)
            $this->template->skryt_automaty = false;
        else
            $this->template->skryt_automaty = true;
        
            $this->template->automaty_soucty = $automaty_soucty;
            $this->template->automaty = $automaty;
       
        if ($this->isAjax())
            $this->invalidateControl('strankyLong');
    }

    /**
     * Singleton for ZakazniciModel
     * @return type 
     */
    public function getZakazniciModel() {
        if(!isset($this->zakazniciModel_var))
            $this->zakazniciModel_var = new ZakazniciModel();

        return $this->zakazniciModel_var;
    }
    
    /**
     * Singleton ZboziModel
     * @return type 
     */
    public function getZboziModel() {
        if(!isset($this->zboziModel_var))
            $this->zboziModel_var = new ZboziModel();

        return $this->zboziModel_var;
    }
    
    /**
     * Singleton KategorieModel
     * @return type 
     */
    public function getKategorieModel() {
        if(!isset($this->kategorieModel_var))
            $this->kategorieModel_var = new KategorieModel();

        return $this->kategorieModel_var;
    }
    
    /**
     * Singleton ObjednavkyModel
     * @return type 
     */
    public function getObjednavkyModel() {
        if(!isset($this->objednavkyModel_var))
            $this->objednavkyModel_var = new ObjednavkyModel();

        return $this->objednavkyModel_var;
    }
      
    /**
     * Singleton OblastiModel
     * @return type 
     */
    public function getOblastiModel() {
        if(!isset($this->oblastiModel_var))
            $this->oblastiModel_var = new OblastiModel();

        return $this->oblastiModel_var;
    }
    
    /**
     * Singleton ObjednavkyModel
     * @return type 
     */
    public function getAutomatyModel() {
        if(!isset($this->automatyModel_var))
            $this->automatyModel_var = new AutomatyModel();

        return $this->automatyModel_var;
    }
}