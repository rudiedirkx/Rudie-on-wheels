<?php

namespace app\mixins;

use row\core\Mixin;

class ReusableFormRenderers extends Mixin {

	// Element specific rendering
	public function renderCSVList( $name, $element, $form ) {
		// $this->object === $form
		$html = $form->renderTextElement($name, $element, false);
		$html = str_replace('<input ', '<input disabled ', $html);

		$html = '<div><div style="display: none;">'.$html.'</div><div><a href="javascript:void(0);" onclick="this.parentNode.style.display=\'none\';this.parentNode.previousSibling.style.display=\'block\';this.parentNode.previousSibling.getElementsByTagName(\'input\')[0].removeAttribute(\'disabled\');">Click here to edit</a></div></div>';

		return $form->renderElementWrapperWithTitle($html, $element);
	}

}


