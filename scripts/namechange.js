$(document).ready(function() {
	
	/*
	 * Function to select the containing p from a form element
	 */
	getContainer = function(x) {
		if (x.parent().is("p"))
			return x.parent();
		else if (x.parent().is("span"))
			return x.parent().parent();
		else
			return null;
	}
	
	/*
	 * Highlight rows when we focus a field
	 */
	$("fieldset p input, fieldset p select").focus(function() {
		if ((c = getContainer($(this))) !== null)
			c.addClass("field-focus");
	});
	
	$("fieldset p input, fieldset p select").blur(function() {
		if ((c = getContainer($(this))) !== null)
			c.removeClass("field-focus");
	});
	
	// Select / Hightlight firstname
	$("#p_fname").focus();
	
	/*
	 * Validating fields
	 */
	
	$("fieldset p input, fieldset p select").change(function() {
		if ((c = getContainer($(this))) !== null) {
			if (c.hasClass("field-error")) {
				if ($(this).is("input:text")) {
					if ($(this).val != "")
						c.removeClass("field-error");
				} else if ($(this).is("input:radio")) {
					if ($(this).attr('name').substr(3) == 'ticket_type') {
						if ($("input:radio[name='" + $(this).attr("name") + "']:checked").length != 0)
							c.removeClass("field-error");
					} else {
						if ($("#" + $(this).attr("name") + "_true:checked").length != 0)
							c.removeClass("field-error");
					}
				} else if ($(this).is("select")) {
					if ($(this).val() != "0")
						c.removeClass("field-error");
				}
			}
		}
	});
	
});
