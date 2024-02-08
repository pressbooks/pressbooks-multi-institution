/* global Msg */
import "../styles/pressbooks-multi-institutions.css";

document.addEventListener('DOMContentLoaded', function() {
	document.querySelector('#pressbooks-multi-institution-admin #doaction')
		.addEventListener('click', function(e) {
		e.preventDefault();
		const action = document.querySelector('#pressbooks-multi-institution-admin #bulk-action-selector-top').value;
		const items = document.querySelectorAll('.check-column input:checked');
		// we want to translate it. We could send the variable through the wp_localize_script function
		if (action !== '-1' && items.length > 0 && confirm(Msg.text)) {
			document.querySelector('#pressbooks-multi-institution-admin').submit();
		}
	});
});
