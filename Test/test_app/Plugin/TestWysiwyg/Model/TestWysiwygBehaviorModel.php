<?php
/**
 * WysiwygBehaviorテスト用Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppModel', 'Model');

/**
 * WysiwygBehaviorテスト用Model
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Wysiwyg\Test\test_app\Plugin\TestWysiwyg\Model
 */
class TestWysiwygBehaviorModel extends AppModel {

/**
 * テーブル名
 *
 * @var mixed
 */
	public $useTable = false;

/**
 * 使用ビヘイビア
 *
 * @var array
 */
	public $actsAs = array(
		'Wysiwyg.Wysiwyg'
	);

}
