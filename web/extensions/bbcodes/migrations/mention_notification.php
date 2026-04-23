<?php

namespace mafiascum\bbcodes\migrations;

class mention_notification extends \phpbb\db\migration\migration
{
    public function depends_on()
    {
        return ['\mafiascum\bbcodes\migrations\bbcodes'];
    }

    public function update_data()
    {
        return [
            ['custom', [[$this, 'add_notification_type']]],
        ];
    }

    public function add_notification_type()
    {
        $sql = 'SELECT notification_type_id FROM ' . $this->table_prefix . 'notification_types
                WHERE notification_type_name = \'notification.type.mention\'';
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$row) {
            $sql = 'INSERT INTO ' . $this->table_prefix . 'notification_types
                    (notification_type_name, notification_type_enabled)
                    VALUES (\'notification.type.mention\', 1)';
            $this->db->sql_query($sql);
        }
    }
}
