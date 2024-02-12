<?php

namespace mafiascum\valentines\controller;

class results
{
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
        global $phpEx, $phpbb_root_path;
        include_once($phpbb_root_path . 'common.' . $phpEx);
        include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        
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
        $user_array = array();

        $query = "SELECT user_id FROM valentines_users";
        $result = $this->db->sql_query($query);
        while ($row = $this->db->sql_fetchrow($result)){
            $temp_id = $row['user_id'];
            if ($temp_id != $user_id){
                $user_array[] = $temp_id;
            }
        }
        user_get_id_name($user_array, $user_array_names);
        $query = "SELECT * FROM valentines_answers WHERE user_id=" . ((int)$user_id);
        $result = $this->db->sql_query($query);
        $my_question_id_set = array();
        $my_pref_answer = array();
        $my_answer = array();
        $my_weight = array();

        $my_total_weight = array();
        $my_score = array();

        $their_important_score = array();
        $my_important_score = array();

        $their_score = array();
        $their_total_weight = array();

        $weighted_question_id = 1;
        $final_results = array();

        for ($i=0; $i<sizeOf($user_array); $i++) {
            $their_id = $user_array[$i];
            $query = "SELECT * FROM valentines_answers WHERE user_id=" . $their_id;
            $result = $this->db->sql_query($query);
            $their_question_id_set = array();
            $their_pref_answer = array();
            $their_answer = array();
            $their_weight = array();
            $my_total_weight[$their_id] = 0;
            $my_score[$their_id] = 0;
            $their_total_weight[$their_id] = 0;
            $their_score[$their_id] = 0;
            $question_match = 0;
            while ($row = $this->db->sql_fetchrow($result)){
                $question_id=$row['question_id'];
                $their_pref_answer[$question_id] = $row['prefanswer'];
                $their_answer[$question_id] = $row['answer'];
                $their_weight[$question_id] = $row['weight'];
            }
            for ($k=0;$k<sizeOf($my_question_id_set);$k++){
                $question_id = $my_question_id_set[$k];
                if ($question_id != $weighted_question_id){
                    if ($their_pref_answer[$question_id]){
                        $question_match++;
                        $temp_weight = $their_weight[$question_id];
                        $their_total_weight[$their_id] += $weight[$temp_weight];
                        if ($their_pref_answer[$question_id] == $my_answer[$question_id]){
                            $their_score[$their_id] += $weight[$temp_weight];
                        }
                        $temp_weight = $my_weight[$question_id];
                        $my_total_weight[$their_id] += $weight[$temp_weight];
                        if ($their_answer[$question_id] == $my_pref_answer[$question_id]){
                            $my_score[$their_id] += $weight[$temp_weight];
                            
                        }
                    }
                } else {
                    if ($their_pref_answer[$weighted_question_id]){
                        $their_important_score[$their_id] = 0;
                        $my_important_score[$their_id] = 0;
                        if ($their_pref_answer[$weighted_question_id] == $my_answer[$weighted_question_id] || $their_weight[$question_id] == 0){
                            $their_important_score[$their_id] = 1;
                        }
                        if ($their_answer[$weighted_question_id] == $my_pref_answer[$weighted_question_id] || $my_weight[$question_id] == 0){
                            $my_important_score[$their_id] = 1;
                        }
                    }
                    
                    
                }
            }
            if ($their_total_weight[$their_id] > 0 && $my_total_weight[$their_id] > 0 && $question_match >= 5){
                $my_percent = $my_score[$their_id] / ($my_total_weight[$their_id]);
                $their_percent = $their_score[$their_id]/($their_total_weight[$their_id]);
                $finalscore = pow(($my_percent*$their_percent),1/2);
                $final_results[$their_id] = $finalscore;
            }
        }
            
        $unweighted_results = array_merge($final_results);
        arsort($unweighted_results);
        $worst = array();
        
        if (sizeof($unweighted_results)) {
            $worst_id = array_key_last($unweighted_results);
            $worst_percent = $unweighted_results[$worst_id];

            $worst[$worst_id] = $worst_percent;
        }

        $important_results = array();
        $unweighted_you_to_them = array();
        $unweighted_them_to_you = array();
        $important_you_to_them = array();
        $important_them_to_you = array();
        foreach ($unweighted_results as $user => $percent) {
            $important_results[$user] = $percent * $my_gender_score[$user] * $their_gender_score[$user];
            $unweighted_you_to_them[$user] = ($my_score[$user]/$my_total_weight[$user]);
            $unweighted_them_to_you[$user] = ($their_score[$user]/$their_total_weight[$user]);
            $important_you_to_them[$user] = ($my_score[$user]/$my_total_weight[$user]) * $my_gender_score[$user];
            $important_them_to_you[$user] = ($their_score[$user]/$their_total_weight[$user]) * $their_gender_score[$user];
        }
        arsort($important_results);
        arsort($unweighted_you_to_them);
        arsort($unweighted_them_to_you);
        arsort($important_you_to_them);
        arsort($important_them_to_you);

        $this->template->assign_vars(array(
            'FINAL_RESULTS_TOP_FIVE' => array_slice($unweighted_results, 0, 5),
            'FINAL_RESULTS_TOP_FIVE_IMPORTANT_OVERALL' => array_slice($important_results, 0, 5),
            'FINAL_RESULTS_TOP_FIVE_YOU_TO_THEM' => array_slice($unweighted_you_to_them, 0, 5),
            'FINAL_RESULTS_TOP_FIVE_THEM_TO_YOU' => array_slice($unweighted_them_to_you, 0, 5),
            'FINAL_RESULTS_TOP_FIVE_IMPORTANT_YOU_TO_THEM' => array_slice($important_you_to_them, 0, 5),
            'FINAL_RESULTS_TOP_FIVE_IMPORTANT_THEM_TO_YOU' => array_slice($important_them_to_you, 0, 5),
            'FINAL_RESULTS_TOP_ONE_WORST' => array_slice($worst, 0, 1),
            'USER_ARRAY_NAMES' => $user_array_names,
        ));

        return $this->helper->render('results.html', $this->language->lang('VALENTINES_RESULTS'));
    }
}
?>