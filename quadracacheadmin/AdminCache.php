<?php
/*
* 1997-2012 Quadra Informatique
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0) that is available
* through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
* If you are unable to obtain it through the world-wide-web, please send an email
* to ecommerce@quadra-informatique.fr so we can send you a copy immediately.
*
*  @author Quadra Informatique SARL <ecommerce@quadra-informatique.fr>
*  @copyright 1997-2012 Quadra Informatique
*  @version Release: $Revision: 1.0 $
*  @license http://www.opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
*/

include_once(PS_ADMIN_DIR . '/tabs/AdminPreferences.php');
include_once(dirname(__FILE__) . '/quadracacheadmin.php');

class AdminCache extends AdminTab {
	    
	private $module = 'quadracacheadmin';
	
	public function __construct()
    {
    	parent::__construct();
	}
	
	public function display()
	{
		global $currentIndex;
        
        echo '
		<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post" enctype="multipart/form-data">
		<fieldset>';
		
		echo '
		<label>'.$this->l('Activate on front:').'</label>
		<div class="margin-form">
			<input type="radio" name="active_cache"';if(Configuration::get('PS_QUADRA_CACHE_ACTIVE') == 1){echo 'checked=checked';}
		echo '
		value="1" />&nbsp;'.$this->l('Yes').'&nbsp;&nbsp;
		
			<input type="radio" name="active_cache"';if(Configuration::get('PS_QUADRA_CACHE_ACTIVE') == 0){echo 'checked=checked';}
		echo '
		value="0" />&nbsp;'.$this->l('No').'&nbsp;&nbsp;
		</div>
		
		<div class="margin-form">
			<input type="submit" value="'.$this->l('save').'" name="saveConfigCache" class="button"/>
		</div>
		<label>'.$this->l('Remove cache :').'</label>
		<div class="margin-form">
			<input type="submit" value="'.$this->l('remove').'" name="removeCache" class="button"/>
		</div>
		</fieldset>
		</form>';
	}

    public function postProcess($token = NULL) {
    	$cache_active = (int) Tools::getValue('active_cache');	
    	if (Tools::isSubmit('saveConfigCache')) {
    		Configuration::updateValue('PS_QUADRA_CACHE_ACTIVE',$cache_active);
    	}elseif (Tools::isSubmit('removeCache')) {
    		
    	}else{
    		
    	}
    	
    }
}