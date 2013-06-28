<?php	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Data Mapper Configuration
 *
 * Global configuration settings that apply to all DataMapped models.
 */

$config['prefix'] = '';
$config['join_prefix'] = '';
$config['error_prefix'] = '';
$config['error_suffix'] = "\n";
$config['created_field'] = 'created';
$config['updated_field'] = 'updated';
/* Setting 'local_time' to TRUE should let us use BST correctly */
$config['local_time'] = TRUE;
$config['unix_timestamp'] = FALSE;
/*
	NOTE: if 'timestamp_format' is left as an empty string (as per the DataMapper config file) then created timestamps aren't saved!
	Either set it to 'Y-m-d H:i:s O', or omit this line. This might always be required under IIS, though:
*/
// $config['timestamp_format'] = '';
$config['lang_file_format'] = 'model_${model}';
$config['field_label_lang_format'] = '${model}_${field}';
$config['auto_transaction'] = FALSE;
$config['auto_populate_has_many'] = FALSE;
$config['auto_populate_has_one'] = FALSE;
$config['all_array_uses_ids'] = FALSE;

// set to FALSE to use the same DB instance across the board (breaks subqueries)
// Set to any acceptable parameters to $CI->database() to override the default.
$config['db_params'] = '';

// Uncomment to enable the production cache
if (ENVIRONMENT == 'production') {
	$config['production_cache'] = 'cache/datamapper';	
}

$config['extensions_path'] = '../sparks/Datamapper-ORM/1.8.3/extensions';
$config['extensions'] = array();

/* End of file datamapper.php */
/* Location: ./sparks/Datamapper-ORM/config/datamapper.php */
