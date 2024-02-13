/* global context */
import "../styles/pressbooks-multi-institutions.css";

document.addEventListener('DOMContentLoaded', function () {
	const doAction = document.querySelector(`${context.formSelector}  #doaction`);

	doAction && doAction.addEventListener('click', function (e) {
		e.preventDefault();

		const action = document.querySelector(`${context.formSelector} #bulk-action-selector-top`).value;
		const items = document.querySelectorAll(`${context.formSelector} .check-column input:checked`);

		if (action !== '-1' && items.length > 0 && confirm(context.confirmationMessage)) {
			document.querySelector(context.formSelector).submit();
		}
	});
});
