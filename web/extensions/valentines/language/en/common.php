<?php
/**
*
* @package phpBB Extension - MafiaScum Valentine's Quiz
* @copyright (c) 2017 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'QUIZ_TITLE' => 'Mafiascum Valentines Day Match Making Test',
    'QUIZ_HEADER' => 'Mafiascum Valentines Day Match Making Test',
    'LOGGED_IN_AS' => 'You are logged in as %s',
    'YOUR_ANSWER' => 'Your Answer: ',
    'DESIRED_ANSWER' => 'How you\'d want a match to answer:',
    'RANK' => 'How highly do you value your matches response to this question?',
    'RANK_WEIGHT_1' => 'This question is very important to me',
    'RANK_WEIGHT_2' => 'This question is important to me',
    'RANK_WEIGHT_3' => 'This question is somewhat important to me',
    'RANK_WEIGHT_4' => 'This question isn\'t that important to me',
    'RANK_WEIGHT_5' => 'This question is of no importance to me',
    'ANSWER' => 'Answer',
    'SKIP' => 'Skip',
    'RESULTS_HEADER' => 'Mafiascum Valentines Day Match Making Results',
    'RESULTS_NORMAL' => 'Normal',
    'RESULTS_IMPORTANT' => 'Same Site Area (Overall)',
    'RESULTS_NORMAL_U2T' => 'Normal (You to Them)',
    'RESULTS_NORMAL_T2U' => 'Normal (Them to You)',
    'RESULTS_IMPORTANT_U2T' => 'Same Site Area (You to Them)',
    'RESULTS_IMPORTANT_T2U' => 'Same Site Area (Them to You)',
    'RESULTS_WORST' => 'Worst',
    'THANKS' => 'Thank you for taking the time to fill out the quiz. Results will be released on the 14th.',
));
