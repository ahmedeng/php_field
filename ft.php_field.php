<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Php_field_ft extends EE_Fieldtype {

    var $info = array(
        'name'      => 'PHP field',
        'version'   => '1.0'
    );

    
    function install()
    {
	// Somewhere in Oregon ...
	return array(
	  'php_code' => '$value="";\r\n'  
	);
    }
    
    
    function display_settings($data)
    {
    
      $val=isset($data['php_code']) ? $data['php_code'] : '$value="";';
      
      $php_code=(set_value('php_code')!='') ? set_value('php_code') : $val;
      
      $vars=$this->_get_fields();
      unset($vars['$'.$data['field_name']]);
      $note_vars= '<div id="vars"><font color="green"><p>$'.implode(array_keys($vars),' , $').'<p></font></div>';
      ee()->table->add_row("<b>PHP Variables</b><br>Predefined php variables of current displayed channel entry field that you can use.",$note_vars);
     
      $ex1='<div><b>Ex1:</b>'.'<br>'.'&nbsp;&nbsp;&nbsp;&nbsp;$value = $title.$status;</div>';
      $ex2='<div><b>Ex1:</b>'.'<br>'.'&nbsp;&nbsp;&nbsp;&nbsp;$value = "Any string";</div>';
      $ex3='<div><b>Ex1:</b>'.'<br>'.'&nbsp;&nbsp;&nbsp;&nbsp;$x=1;$y=2;<br>&nbsp;&nbsp;&nbsp;&nbsp;$value=$x+$y;</div>';
      $examples='<div>'.$ex1.'<br>'.$ex2.'<br>'.$ex3.'</div>';
      
      ee()->table->add_row('<b>PHP code</b><div>Use the variable <font color="red">$value</font> to put the final result that will be the field displayed value.<br>Don\'t use php tags (< ?php,?>).</div>'
			   .$examples
			   ,'<h3><font color="red">'.form_error('php_code').'</font></h3>'.	
			   form_textarea(array('name' => 'php_code','id' => 'php_code','value' => $php_code)).NBS.NBS.NBS.' ');
	
    }
    
    
    function validate_settings($data)
    {
      ee()->form_validation->set_rules('php_code', 'php_code', 'required|callback__check_php_code');      
    }
    
    
    function _check_php_code($php_code)
    {   
      error_reporting(!E_ALL);
      if(eval($php_code)===FALSE)
      {	
	$error=error_get_last();
	ee()->form_validation->set_message('_check_php_code', 'You have php error. please check your code.</font></h1>line'.$error['line'].':'.$error['message']);
	return False;
      }
      return true;
    }
    // --------------------------------------------------------------------
	
    /**
      * Save Settings
      *
      * @access	public
      * @return settings
      *
      */
    function save_settings($data)
    {
       return array_merge($this->settings, $_POST);      
    }
    
    
    function _get_fields()
    {
	$group_id = ee()->input->get_post('group_id');
	$group_id=urldecode($group_id);
      
        $this->EE->db->select( 'field_name, field_label' );
        $this->EE->db->where( 'group_id', $group_id);
        $this->EE->db->order_by( 'field_order' );
        $query = $this->EE->db->get( 'exp_channel_fields' );
        
        $data["unique_fields"] = array();
        $data["unique_fields"][ 'title' ] = "Title";
        $data["unique_fields"][ 'status' ] = "Status";

        if( $query->num_rows() > 0 ) {
            foreach( $query->result_array() as $row ) {
	      
                $data["unique_fields"][ $row["field_name"] ] = $row["field_label"];
            }
        }
        return $data["unique_fields"];
    }

    
    // --------------------------------------------------------------------

    function display_field($data)
    {
 	$val=isset($this->settings['php_code']) ? $this->settings['php_code'] : 'return "hi";';
 	$php_code	= isset($data['php_code']) ? $data['php_code'] : $val;
	return $php_code;
    }
    
    
    
    
    
}
// END Php_field_ft class

/* End of file ft.php_field.php */
/* Location: ./system/expressionengine/third_party/php_field/ft.php_field.php */