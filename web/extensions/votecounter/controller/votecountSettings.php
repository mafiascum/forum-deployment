<?php

namespace mafiascum\votecounter\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class votecountSettings
{
    protected $helper;
    protected $request;
    protected $user_loader;
    protected $db;
    protected $user;
    protected $template;
    protected $table_prefix;

    public function __construct(
        \phpbb\controller\helper $helper,
        \phpbb\template\template $template,
        \phpbb\request\request $request,
        \phpbb\user_loader $user_loader,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\user $user,
        $table_prefix,
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->user_loader = $user_loader;
        $this->db = $db;
        $this->user = $user;
        $this->table_prefix = $table_prefix;
        $this->template = $template;
    }

    public function handle()
    {
        return new JsonResponse([], 200);
    }
}
