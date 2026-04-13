<?php
/**
 * Element of MathJax include
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Satoru Majima <neo.otokomae@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

// skipStartupTypeset: true によって MathJaxの自動読み込みを無効にする
echo $this->Html->scriptStart(array('inline' => false));
?>
MathJax = {
	tex: {
		inlineMath: [['$$', '$$'], ['\\\\(', '\\\\)']],
		displayMath: [['\\\\[', '\\\\]']]
	}
};
$(document).ready(function(){
	MathJax.typesetPromise();
});
<?php
echo $this->Html->scriptEnd();

// wysiwyg呼び出し
echo $this->NetCommonsHtml->script(
	array(
		'/components/MathJax/es5/tex-chtml.js',
	)
);
