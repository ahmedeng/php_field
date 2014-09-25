<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Php_field_ext {

    var $name       = 'PHP field';
    var $version        = '1.0';
    var $description    = 'Create a field that run php code';
    var $settings_exist = 'n';
    var $docs_url       = ''; // 'http://ellislab.com/expressionengine/user-guide/';

    var $settings       = array();

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    function __construct($settings = '')
    {
	$this->EE =& get_instance();     
        $this->settings = $settings;
    }
    
    /**
    * Activate Extension
    *
    * This function enters the extension into the exp_extensions table
    *
    * @see http://ellislab.com/codeigniter/user-guide/database/index.html for
    * more information on the db class.
    *
    * @return void
    */
    function activate_extension()
    {
	$this->settings = array(	    
	);


	$data = array(
	    'class'     => __CLASS__,
	    'method'    => 'run_php_code',
	    'hook'      => 'channel_entries_row',
	    'settings'  => serialize($this->settings),
	    'priority'  => 10,
	    'version'   => $this->version,
	    'enabled'   => 'y'
	);

	ee()->db->insert('extensions', $data);
    }
    
    /**
    * Update Extension
    *
    * This function performs any necessary db updates when the extension
    * page is visited
    *
    * @return  mixed   void on update / false if none
    */
    function update_extension($current = '')
    {
	if ($current == '' OR $current == $this->version)
	{
	    return FALSE;
	}

	if ($current < '1.0')
	{
	    // Update to version 1.0
	}

	ee()->db->where('class', __CLASS__);
	ee()->db->update(
		    'extensions',
		    array('version' => $this->version)
	);
    }
    
    /**
    * Disable Extension
    *
    * This method removes information from the exp_extensions table
    *
    * @return void
    */
    function disable_extension()
    {
	ee()->db->where('class', __CLASS__);
	ee()->db->delete('extensions');

    }
    
    
    
    function _get_fields($channel_id)
    {
    // Get custom key_fields for the selected channel
        
        $this->EE->db->select("field_group, cat_group,channel_title");
        $this->EE->db->where( 'channel_id', $channel_id );
        $query = $this->EE->db->get( 'exp_channels' );
        $row = $query->row_array();
        $field_group = $row["field_group"];
        $cat_group = $row["cat_group"];
        $channel_title = $row["channel_title"];
    
        $this->EE->db->select( 'field_id,field_name, field_type' );
        $this->EE->db->where( 'group_id', $field_group );
        $this->EE->db->order_by( 'field_order' );
        $query = $this->EE->db->get( 'exp_channel_fields' );
        
        
        $data["unique_fields"] = array();
        
        if( $query->num_rows() > 0 ) {
            foreach( $query->result_array() as $row ) {
		$data["unique_fields"][ $row["field_name"] ] = $row["field_id"];		
            }
        }

        return $data["unique_fields"];
    }

    
    function run_php_code($channel,$entry)
    {
      $php_field_settings=array();
      
      $fields=$this->_get_fields($entry['channel_id']);
      
      foreach($fields as $key=>$id){
	${$key}=$entry['field_id_'.$id];	
      }
      
      $title=$entry['title'];
      $status=$entry['status'];
      $value='empty';
      
      foreach($fields as $key=>$id){
	$php_field_settings=ee()->api_channel_fields->get_settings((string)$id);
	if($php_field_settings['field_type']=="php_field")
	{
	  $php_code=$php_field_settings['php_code'];
	  eval($php_code);
	  ${$key}=$entry['field_id_'.$id]=$value;
	}
	else
	{
	  $php_field_settings=array();
	}
      }
      return $entry;
    }

}
// END CLASS
