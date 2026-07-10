<?php

namespace mafiascum\miscellaneous\ucp;

class main_info
{
	public function module()
	{
		return array(
			'filename' => '\mafiascum\miscellaneous\ucp\main_module',
			'title' => 'MAF_UCP_HIDDEN_TOPICS',
			'modes' => array(
				'hidden_topics' => array(
					'title' => 'MAF_UCP_HIDDEN_TOPICS',
					'auth' => 'ext_mafiascum/miscellaneous',
					'cat' => array('UCP_PREFS'),
				),
			),
		);
	}
}
