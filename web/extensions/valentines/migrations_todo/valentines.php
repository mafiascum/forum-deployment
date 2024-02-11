<?php

namespace mafiascum\valentines\migrations;

class valentines extends \phpbb\db\migration\migration
{

    public function effectively_installed()
    {	
        return $this->db_tools->sql_table_exists('valentines_questions');
    }
    
    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }
	
    public function update_schema()
    {
        return array(
            'add_tables'    => array(
                'valentines_questions' => array(
                    'COLUMNS' => array(
                        'queston_id'             => array('UINT', 0, 'auto_increment'),
                        'question'              => array('TEXT'),
                        'Answer1' => array('TEXT', NULL),
                        'Answer2' => array('TEXT', NULL),
                        'Answer3' => array('TEXT', NULL),
                        'Answer4' => array('TEXT', NULL),
                        'Answer5' => array('TEXT', NULL),
                    ),
                ),
            ),
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_tables'    => array(
                'valentines_questions',
            ),
        );
    }
}
?>