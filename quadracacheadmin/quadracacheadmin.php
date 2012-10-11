<?php
/**
 * ---------------------------------------------------------------------------------
 * 
 * 1997-2012 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ecommerce@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <ecommerce@quadra-informatique.fr>
 * @copyright 1997-2012 Quadra Informatique
 * @version Release: $Revision: 1.0 $
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * ---------------------------------------------------------------------------------
*/

class QuadraCacheAdmin extends Module{

    public function __construct(){
        $this->name = 'quadracacheadmin';
        $this->author = 'Quadra Informatique';
        $this->version = '1.0';
		//$this->module_key = "";

        parent::__construct();

        $this->displayName = $this->l('Quadra Cache Admin');
        $this->description = $this->l('manage cache on front');;
    }
    
	function install(){
        
        if(!parent::install()){
            return false;
    	}
    	$this->installModuleTab('AdminCache',array(
    		Language::getIdByIso('fr') => 'Quadra Gestion Cache',
    		Language::getIdByIso('en') => 'Quadra Cache Admin'), '8');
    		
    	Configuration::updateValue('PS_QUADRA_CACHE_ACTIVE', 0);	
	}	
    
	public function uninstall(){
        $this->uninstallModuleTab('AdminCache');
        Configuration::deleteByName('PS_QUADRA_CACHE_ACTIVE');
        return parent::uninstall();
	}
	    
	private function installModuleTab($tabClass, $tabName, $idTabParent)
    {
        $tab = new Tab();
        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = (int)($idTabParent);
        if (!$tab->save())
            return false;
        return true;
    }

    private function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0)
        {
        	$tab = new Tab($idTab);
            $tab->delete();
            return true;
        }
        return false;
    }
}    