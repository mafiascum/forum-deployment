<?php

namespace mafiascum\votecounter\controller;

use \Symfony\Component\HttpFoundation\JsonResponse;

class votecountSettings
{
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\user_loader */
    protected $user_loader;

    /** @var \phpbb\db\driver\driver */
    protected $db;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\template\template */
    protected $template;

    protected $table_prefix;

    public function __construct(
        \phpbb\controller\helper $helper,
        \phpbb\template\template $template,
        \phpbb\request\request $request,
        \phpbb\user_loader $user_loader,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\user $user,
        $table_prefix
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->user_loader = $user_loader;
        $this->db = $db;
        $this->user = $user;
        $this->table_prefix = $table_prefix;
        $this->template = $template;
    }

    public function is_moderator_of_topic($topic_id)
    {
        if (!$topic_id) {
            return false;
        }

        $current_user_id = (int) $this->user->data['user_id'];

        $sql = 'SELECT topic_poster FROM ' . $this->table_prefix . 'topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $topic_poster = $row ? (int) $row['topic_poster'] : 0;
        $this->db->sql_freeresult($result);

        $sql = 'SELECT user_id FROM ' . $this->table_prefix . 'topic_mod WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $topic_mods = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $topic_mods[] = (int) $row['user_id'];
        }
        $this->db->sql_freeresult($result);

        return $current_user_id === $topic_poster || in_array($current_user_id, $topic_mods);
    }

    public function add_user_as_slot()
    {
        $topic_id = (int) $this->request->variable('t', 0);
        $username = $this->request->variable('q', '');

        if ($topic_id === 0) {
            return new JsonResponse(
                ['error' => 'no topic context'],
                400
            );
        }

        if (!$this->is_moderator_of_topic($topic_id)) {
            return new JsonResponse(['error' => 'not permitted'], 403);
        }

        $user_id = $this->user_loader->load_user_by_username($username);

        if ($user_id == ANONYMOUS) {
            return new JsonResponse(array());
        } else {
            $query_array = [
                'topic_id' => (int) $topic_id,
                'user_id'  => (int) $user_id,
            ];

            $this->db->sql_query(
                'INSERT IGNORE INTO ' . $this->table_prefix . 'votecount_slots ' . $this->db->sql_build_array('INSERT', $query_array)
            );

            $username_formatted = $this->user_loader->get_username($user_id, 'username');
            $username_profile = $this->user_loader->get_username($user_id, 'profile');

            $this->template->assign_vars([
                'ROOT_PATH' => './',
                'TOPIC_ID' => $topic_id,
                'S_SLOT_LIST' => [[
                    'user_id' => $user_id,
                    'username' => $username_formatted,
                    'profile' => $username_profile
                ]]
            ]);

            try {
                $response = $this->helper->render('@mafiascum_votecounter/partials/vc_slot_list_item.html');
                $partial = $response->getContent();
            } catch (\Exception $err) {
                $partial = '<li>An error occurred</li>';
            }

            return new JsonResponse(array(
                'user_id'  => $user_id,
                'username' => $username_formatted,
                'profile'  => $username_profile,
                'partial' => $partial
            ));
        }
    }

    public function remove_slot()
    {
        $topic_id = (int) $this->request->variable('t', 0);
        $user_id = (int) $this->request->variable('q', 0);

        if ($topic_id === 0 || $user_id === 0) {
            return new JsonResponse(
                ['error' => 'no topic context'],
                400
            );
        }

        if (!$this->is_moderator_of_topic($topic_id)) {
            return new JsonResponse(['error' => 'not permitted'], 403);
        }

        $sql = 'DELETE FROM ' . $this->table_prefix . 'votecount_slots WHERE topic_id = ' . $topic_id . ' AND user_id = ' . $user_id;
        $this->db->sql_query($sql);

        return new JsonResponse([], 200);
    }
}
