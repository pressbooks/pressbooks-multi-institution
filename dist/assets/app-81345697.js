document.addEventListener("DOMContentLoaded",function(){const e=document.querySelector(`${context.formSelector}  #doaction`);e&&e.addEventListener("click",function(t){t.preventDefault();const o=document.querySelector(`${context.formSelector} #bulk-action-selector-top`).value,c=document.querySelectorAll(`${context.formSelector} .check-column input:checked`);o!=="-1"&&c.length>0&&confirm(context.confirmationMessage)&&document.querySelector(context.formSelector).submit()})});
//# sourceMappingURL=app-81345697.js.map
