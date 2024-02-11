<?php

namespace mafiascum\valentines\controller;

class quiz {
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\language\language */
    protected $language;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\db\driver\driver */
    protected $db;
    
    public function __construct(\phpbb\controller\helper $helper, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\user $user, \phpbb\db\driver\driver_interface $db) {
        $this->helper   = $helper;
        $this->language = $language;
        $this->template = $template;
        $this->request = $request;
        $this->user = $user;
        $this->db = $db;
    }

    public function handle() {
        $user_id = $this->user->data['user_id'];
        if ($user_id == ANONYMOUS) {
            trigger_error('You must be logged in.');
        }
        
        $result = $this->db->sql_query("SELECT count(*) cnt FROM phpbb_alts WHERE alt_user_id=$user_id");
        $alt_data = mysqli_fetch_array($result);
        if ((int) $alt_data['cnt'] > 0) {
            trigger_error('You may not take this quiz as an alt user.');
        }

        $query = "SELECT COUNT(*) number_of_questions FROM valentines_questions";
        $result = $this->db->sql_query($query);
        $number_of_questions = (int)$this->db->sql_fetchrow($result)["number_of_questions"];
        $this->db->sql_freeresult($result);

        if ($this->request->is_set_post('question_id')) {
            $question_id = $this->request->variable('question_id', -1);
	        if ($question_id < 0 || $question_id > $number_of_questions) {
                trigger_error('Invalid question id');
            }

            if ($this->request->is_set_post('submit')) {
                $youranswer = $this->request->variable('youranswer', 0);
                
                if ($youranswer > 5) {
                    $youranswer = 5;
                } else if ($youranswer < 1) {
                    $youranswer = 1;
                }

                $prefanswer = $this->request->variable('prefanswer', 0);
                if ($prefanswer > 5) {
                    $prefanswer = 5;
                }
                else if ($prefanswer < 1) {
                    $prefanswer = 1;
                }
                $weight = $this->request->variable('weight', -1);
                if ( $weight > 4) {
                    $weight = 4;
                }
                else if ($weight < 0) {
                    $weight = 0;
                }

                $query = "INSERT INTO valentines_answers VALUES ($question_id,$user_id,$youranswer,$prefanswer,$weight) ON DUPLICATE KEY UPDATE answer=values(answer), prefanswer=(prefanswer), weight=(weight)";
                $result = $this->db->sql_query($query);
                $query = "INSERT INTO valentines_users (user_id, question_id) values($user_id, $question_id) ON DUPLICATE KEY UPDATE question_id=values(question_id)";
                $result = $this->db->sql_query($query);
                $question_id++;
            } else if ($this->request->is_set_post('pass')) {
                $query = "INSERT INTO valentines_users (user_id, question_id) values($user_id, $question_id) ON DUPLICATE KEY UPDATE question_id=values(question_id)";
                $result = $this->db->sql_query($query);
                $question_id++;
            }
        } else {
            $query = "SELECT * FROM valentines_users WHERE user_id=$user_id";
            $result = $this->db->sql_query($query);
            if ($result) {
                $question_data = mysqli_fetch_array($result);
            }

            if ($question_data) {
                $question_id = $question_data['question_id']+1;
            } else {
                $question_id = 1;
            }
        }

        $query = "SELECT * FROM valentines_questions WHERE question_id=$question_id";
        $result = $this->db->sql_query($query);
        $question_data = mysqli_fetch_array($result);
        if (!$question_data && $question_id <= $number_of_questions ) {
            $question_id++;
            $query = "SELECT * FROM valentines_questions WHERE question_id=$question_id";
            $result = $this->db->sql_query($query);
            $question_data = mysqli_fetch_array($result);
        }

        $answers = array();
        for ($i = 1; $i < 6; $i++) {
            if (!empty($question_data["Answer$i"])) {
                $answers[] = $question_data["Answer$i"];
            }
        }

        $this->template->assign_vars(array(
            'QUESTION_ID' => $question_id,
            'QUESTION_HEADER' => $question_data ? '#' . $question_data['question_id'] . '/' . $number_of_questions . ': ' .$question_data['question'] : '',
            'QUESTION_DATA' => $question_data,
            'ANSWERS' => $answers,
            'LOGGED_IN_AS' => $this->language->lang('LOGGED_IN_AS', $this->user->data['username']),
        ));

        return $this->helper->render('quiz.html', $this->language->lang('VALENTINES_QUIZ'));
    }
}
?>