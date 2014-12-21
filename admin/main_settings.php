<?php

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'dynamictemplate');

$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path.'common.php');


/*+----------------------------------------------------------------------------
  | dynamictemplate_main_settings
  +--------------------------------------------------------------------------*/
class dynamictemplate_main_settings extends page_generic
{

  /**
   * Constructor
   */
  public function __construct()
  {
    // plugin installed?
    if (!$this->pm->check('dynamictemplate', PLUGIN_INSTALLED))
      message_die($this->user->lang('dynamictemplate_plugin_not_installed'));

    $handler = array(
      'save' => array('process' => 'save', 'csrf' => true, 'check' => 'a_dynamictemplate_main'),
    );
    parent::__construct('a_dynamictemplate_main', $handler, array('dynamictemplate', 'name'), null, 'field_ids[]');

    $this->process();
  }


  /**
   * Save
   * save the configuration
   */
  public function save()
  {
	if (count($this->in->getArray('field', 'raw')) > 0){
		//Truncate field table
		$this->pdh->put('dynamictemplate', 'truncate', array());
		
		$id = 0;
		foreach($this->in->getArray('field', 'raw') as $val){
			if ($val['name'] == '') continue;
			
			$intSortID = $id;

			$intActive = (isset($val['active']) && (int)$val['active']) ? 1 : 0;
			$strName = strtoupper($val['name']);
			$strValue = $val['value'];
			
			$this->pdh->put('dynamictemplate', 'add', array($val['id'], $intSortID, $intActive, $strName, $strValue));
			$id++;
		}
	}
	$this->pdh->process_hook_queue();
	//$this->pdc->del_prefix('hptt_dynamictemplate');
	$this->pdc->del('pdh_dynamictemplate_table');
	
    // Success message
	$this->core->message($this->user->lang('pk_succ_saved'), $this->user->lang('success'), 'green');
    $this->display($messages);
  }


  /**
   * Display
   * display the page
   *
   * @param    array  $messages   Array of Messages to output
   */
  public function display()
  {
	$this->tpl->add_js("
		$(\"#dynamictemplate_form_table tbody\").sortable({
			cancel: '.not-sortable, input, .input, select',
			cursor: 'pointer',
		});
	", "docready");
	
	$this->confirm_delete($this->user->lang('dynamictemplate_confirm_delete_field'));
	$this->jquery->selectall_checkbox('selall_fields', 'field_ids[]');
	
	$arrFields = $this->pdh->get('dynamictemplate', 'id_list', array());
	
	foreach($arrFields as $id){
		$row = $this->pdh->get('dynamictemplate', 'id', array($id));

		$this->tpl->assign_block_vars('field_row', array(
			'KEY'				=> $row['id'],
			'ACTIVE'			=> ($row['active']) ? 'checked="checked"' : '',
			'NAME'				=> $row['name'],
			'VALUE'				=> $row['value'],
		));
	}
		
	$this->tpl->assign_vars(array(
		'KEY'		=> max($arrFields)+1,
	));
		
    // -- EQDKP ---------------------------------------------------------------
    $this->core->set_vars(array(
      'page_title'    => $this->user->lang('dynamictemplate').' '.$this->user->lang('dynamictemplate_main_settings'),
      'template_path' => $this->pm->get_data('dynamictemplate', 'template_path'),
      'template_file' => 'admin/main_settings.html',
      'display'       => true
    ));
  }
  
}
registry::register('dynamictemplate_main_settings');

?>