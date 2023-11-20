<?php
/**
 * Wysiwyg inline image
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('WysiwygBehavior', 'Wysiwyg.Model/Behavior');

/**
 * WysiwygUtility
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Wysiwyg\Utility
 */
class WysiwygInlineImageConverter {

/**
 * アップロードデータ
 *
 * @var array
 */
	private $__uploads = [];

/**
 * Wysiwyg画像を抽出・変換するための条件
 *
 * @var string
 */
	const WYSIWYG_IMAGE_PARTTERN = WysiwygBehavior::REPLACE_BASE_URL .
			'\/wysiwyg\/image\/download\/([0-9]+)\/([0-9]+)\/?(biggest|big|medium|small|thumb)?';

/**
 * インライン画像に変換するかどうか
 *
 * @var bool
 */
	const DEFAULT_USE_INLINE_IMAGE = true;

/**
 * インライン画像に変換するかどうか
 *
 * @var array
 */
	private $__useInlineImage = self::DEFAULT_USE_INLINE_IMAGE;

/**
 * インライン画像に変換するかどうか
 *
 * @var array
 */
	private static $__isTopPage = false;

/**
 * 変換する最大件数
 *
 * インライン画像はリクエストを少なくする一方でメモリを消費する。そのため変換する件数を制限する
 *
 * @var int
 */
	const CONVERT_MAX_SIZE = 25;

/**
 * トップページを表示しているかどうか
 *
 * @param bool $isTopPage トップページを表示しているかどうか
 * @return array
 */
	public static function setIsTopPage($isTopPage) {
		self::$__isTopPage = $isTopPage;
	}

/**
 * Constructor
 */
	public function __construct() {
		//memberの場合、インライン画像は使用しない
		$memberUrl = Configure::read('App.memberUrl');
		if (!isset($memberUrl) || self::$__isTopPage || Router::fullBaseUrl() === $memberUrl) {
			$this->__useInlineImage = false;
		}
	}

/**
 * Wysiwygフィールド内からインライン画像に変換する対象データを抽出する
 *
 * @param string $content 変換するコンテンツデータ
 * @return array
 */
	private function __getTargets($content) {
		$targets = [];
		$matches = [];
		if (! preg_match_all('/src="(' . self::WYSIWYG_IMAGE_PARTTERN . ')"/iUus', $content, $matches)) {
			return $targets;
		}

		$uploadIds = [];
		$indexes = array_keys($matches[0]);
		foreach ($indexes as $idx) {
			if ($idx === 0) {
				continue;
			}
			$target = [
				'room_id' => $matches[2][$idx],
				'upload_id' => $matches[3][$idx],
				'size' => $matches[4][$idx],
				'pattern' => $matches[1][$idx],
			];

			if (! isset($this->__uploads[$target['upload_id']])) {
				$uploadIds[] = $target['upload_id'];
			}
			$targets[] = $target;
		}

		if (count($uploadIds) > 0) {
			$this->__setUploadFiles($uploadIds);
		}

		$targets = $this->__mergeUploadFilesToTargets($targets);

		return $targets;
	}

/**
 * UploadFilesデータ取得して変数にセットする
 *
 * @param array $uploadIds 取得するupload_idリスト
 * @return void
 */
	private function __setUploadFiles($uploadIds) {
		// ファイル情報取得 plugin_keyとコンテンツID、フィールドの情報が必要
		$UploadFile = ClassRegistry::init('Files.UploadFile');

		$result = $UploadFile->find('all', [
			'recursive' => -1,
			'fields' => ['id', 'path', 'real_file_name', 'mimetype'],
			'conditions' => [
				'id IN' => $uploadIds,
			],
			'callbacks' => false,
		]);

		foreach ($result as $upload) {
			$id = $upload[$UploadFile->alias]['id'];
			$this->__uploads[$id] = $upload[$UploadFile->alias];
		}
	}

/**
 * UploadFilesデータを対象データにセットする
 *
 * @param array $targets 取得するupload_idリスト
 * @return array
 */
	private function __mergeUploadFilesToTargets($targets) {
		foreach ($targets as $i => $target) {
			$id = $target['upload_id'];
			$upload = $this->__uploads[$id] ?? null;
			if (! isset($upload)) {
				$targets[$i]['path'] = false;
				$targets[$i]['file_exists'] = false;
				continue;
			}

			$filePrefix = $target['size'] ? $target['size'] . '_' : '';
			$target['path'] = UPLOADS_ROOT . $upload['path'] . $upload['id'] . '/' .
					$filePrefix . $upload['real_file_name'];
			$target['file_exists'] = file_exists($target['path']);
			$target['mimetype'] = $upload['mimetype'];

			$targets[$i] = $target;
		}

		return $targets;
	}

/**
 * 変換処理
 *
 * @param string $content 変換するコンテンツデータ
 * @return string 変換した結果
 */
	public function convert($content) {
		if (! $this->__useInlineImage || count($this->__uploads) > self::CONVERT_MAX_SIZE) {
			return $content;
		}

		$targets = $this->__getTargets($content);
		if (empty($targets)) {
			return $content;
		}

		foreach ($targets as $target) {
			if (! $target['file_exists']) {
				continue;
			}

			$pattern = '/src="' . preg_quote($target['pattern'], '/') . '"/iUus';
			$replace = 'src="' . $this->__getInlineImage($target) . '"';
			$content = preg_replace($pattern, $replace, $content);
		}

		return $content;
	}

/**
 * getInlineImage
 *
 * @param array $target 対象データ
 * @return string
 */
	private function __getInlineImage($target) {
		$filepath = $target['path'];
		$mimeType = $target['mimetype'];
		$encodeData = base64_encode(file_get_contents($filepath));

		return sprintf('data:%s;base64,%s', $mimeType, $encodeData);
	}

}
