document.addEventListener("DOMContentLoaded",function(){document.querySelector("#pressbooks-multi-institution-admin #doaction").addEventListener("click",function(t){t.preventDefault();const e=document.querySelector("#pressbooks-multi-institution-admin #bulk-action-selector-top").value,n=document.querySelectorAll(".check-column input:checked");e!=="-1"&&n.length>0&&confirm("Are you sure you want to delete these institutions?")&&document.querySelector("#pressbooks-multi-institution-admin").submit()})});
//# sourceMappingURL=app-9216d47d.js.map