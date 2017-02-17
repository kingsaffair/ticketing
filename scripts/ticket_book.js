$(document).ready(function() {

	/*
	 * If Javascript is enabled then we can use that to toggle the guest ticket
	 */
	$("#js_enabled").val(true);
	
	if (guests_allowed > 0) {
		var h = '';
		h += "<label for=\"guest_select\">Guest tickets<span>Number of guests you would like.</span></label>\n";
		h += "<select name=\"guest_select\" id=\"guest_select\" class=\"field\">\n";
		h += "<option value=\"0\"" + (guests_enabled == 0 ? " selected=\"selected\"" : "") + ">None</option>\n";
		for (var i=1; i<=guests_allowed; i++) {
			h += "<option value=\"" + i + "\"" + (guests_enabled == i ? " selected=\"selected\"" : "") + ">" + i + "</option>\n";
			if (i <= guests_enabled)
				$("#g" + i + "_ticket").show();
			else
				$("#g" + i + "_ticket").hide();
		}
		h += "</select>\n";
		$("#guest_explanation").html(h);
		$("#guest_explanation").removeClass("note");
	
		$("#guest_select").change(function() {
			var v = $(this).val();
			for (var i=1; i<=guests_allowed; i++) {
				if (i <= v)
					$("#g" + i + "_ticket").show();
				else
					$("#g" + i + "_ticket").hide();
			}
		});
	}
	
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
