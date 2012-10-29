<?php

class ShopDisplay
{
    public static function priceFormat($price, $currency)
    {
        return self::numberFormat($price) . ' ' . $currency;
    }

    public static function numberFormat($number)
    {
        return number_format($number, 2, '.', '');
    }

	/**
	 * Returns HTML code for a text input/textarea form field that supports multiple languages
	 *
	 * Returned markup contains a div with a label and an input/textarea, plus a hidden field
	 * for the final value, plus the "translate" link
	 *
	 * @param      $input_name
	 * @param null $input_id
	 * @param null $input_value
	 * @param      $input_style
	 * @param      $input_class
	 * @param      $label_value
	 * @param      $label_class
	 * @param      $container_class
	 * @return string
	 */
	private static function multiLanguageFormField($field_type
														, $input_name, $input_id = NULL, $input_value = NULL, $input_style, $input_class
														, $label_value, $label_class
														, $container_class
														, $rows = NULL, $cols = NULL
													)
	{
		global $lang;
		if($input_id) $input_id = $input_name;

		$label = '<label for="' . $input_id . '" ' . ($label_class ? ' class="' . $label_class . '"' : '') . '>' . $label_value .  '</label>';

		if($field_type == 'input')
		{
			$visible_input = '<input type="text" id="' . $input_id . '"';
			$visible_input .= ($input_class ? ' class="' . $input_class . '"' : '');
			$visible_input .= ($input_style ? ' style="' . $input_style . '"' : '');
			$visible_input .=  ($label_value ? ' title="' . $label_value . '"' : '');
			$visible_input .= ($input_value ? ' value="' . $input_value . '"' : '');
			$visible_input .= ' />';
		}
		else if($field_type == 'textarea')
		{
			$visible_input = '<textarea id="' . $input_id . '"';
			$visible_input .= ($rows ? ' rows="' . $rows . '"' : '');
			$visible_input .= ($cols ? ' cols="' . $cols . '"' : '');
			$visible_input .= ($input_class ? ' class="' . $input_class . '"' : '');
			$visible_input .= ($input_style ? ' style="' . $input_style . '"' : '');
			$visible_input .=  ($label_value ? ' title="' . $label_value . '"' : '');
			$visible_input .= '>';
			$visible_input .= $input_value;
			$visible_input .= '</textarea>';
		}

		$hidden_input = '<input type="hidden" name="' . $input_name . '"';
		$hidden_input .= ' value="' . ShopDisplay::getMultiLanguageValue($input_value) . '"';
		$hidden_input .= ' />';

		$add_language_link = '<a href="#langEdit" class="translate">' . $lang->cmd_set_multilingual . '</a>';

		return '<div class="multiLanguage' . ucfirst($field_type) . ' ' . $container_class .'">' . $label . $visible_input . $hidden_input . $add_language_link . '</div>';
	}

	public static function multiLanguageInput($input_name, $input_id = NULL, $input_value = NULL, $input_style, $input_class, $label_value, $label_class, $container_class)
	{
		return ShopDisplay::multiLanguageFormField('input'
			, $input_name, $input_id, $input_value, $input_style, $input_class
			, $label_value, $label_class
			, $container_class
		);
	}

	public static function multiLanguageTextarea($input_name, $input_id = NULL, $input_value = NULL, $input_style, $input_class, $label_value, $label_class, $container_class, $rows, $cols)
	{
		return ShopDisplay::multiLanguageFormField('textarea'
			, $input_name, $input_id, $input_value, $input_style, $input_class
			, $label_value, $label_class
			, $container_class
			, $rows, $cols
		);
	}

	public static function getMultiLanguageValue($text)
	{
		if(strpos($text, '$user_lang->') === FALSE)
		{
			return $text;
		}
		else
		{
			return htmlspecialchars($text);
		}
	}
}