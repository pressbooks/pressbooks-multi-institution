/**
 * @license
 * Copyright 2019 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const T=globalThis,D=T.ShadowRoot&&(T.ShadyCSS===void 0||T.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,j=Symbol(),B=new WeakMap;let tt=class{constructor(t,e,s){if(this._$cssResult$=!0,s!==j)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=t,this.t=e}get styleSheet(){let t=this.o;const e=this.t;if(D&&t===void 0){const s=e!==void 0&&e.length===1;s&&(t=B.get(e)),t===void 0&&((this.o=t=new CSSStyleSheet).replaceSync(this.cssText),s&&B.set(e,t))}return t}toString(){return this.cssText}};const at=o=>new tt(typeof o=="string"?o:o+"",void 0,j),lt=(o,...t)=>{const e=o.length===1?o[0]:t.reduce((s,i,r)=>s+(n=>{if(n._$cssResult$===!0)return n.cssText;if(typeof n=="number")return n;throw Error("Value passed to 'css' function must be a 'css' function result: "+n+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(i)+o[r+1],o[0]);return new tt(e,o,j)},ht=(o,t)=>{if(D)o.adoptedStyleSheets=t.map(e=>e instanceof CSSStyleSheet?e:e.styleSheet);else for(const e of t){const s=document.createElement("style"),i=T.litNonce;i!==void 0&&s.setAttribute("nonce",i),s.textContent=e.cssText,o.appendChild(s)}},q=D?o=>o:o=>o instanceof CSSStyleSheet?(t=>{let e="";for(const s of t.cssRules)e+=s.cssText;return at(e)})(o):o;/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const{is:ct,defineProperty:dt,getOwnPropertyDescriptor:pt,getOwnPropertyNames:ut,getOwnPropertySymbols:bt,getPrototypeOf:ft}=Object,$=globalThis,F=$.trustedTypes,mt=F?F.emptyScript:"",R=$.reactiveElementPolyfillSupport,S=(o,t)=>o,K={toAttribute(o,t){switch(t){case Boolean:o=o?mt:null;break;case Object:case Array:o=o==null?o:JSON.stringify(o)}return o},fromAttribute(o,t){let e=o;switch(t){case Boolean:e=o!==null;break;case Number:e=o===null?null:Number(o);break;case Object:case Array:try{e=JSON.parse(o)}catch{e=null}}return e}},et=(o,t)=>!ct(o,t),W={attribute:!0,type:String,converter:K,reflect:!1,hasChanged:et};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),$.litPropertyMetadata??($.litPropertyMetadata=new WeakMap);class _ extends HTMLElement{static addInitializer(t){this._$Ei(),(this.l??(this.l=[])).push(t)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(t,e=W){if(e.state&&(e.attribute=!1),this._$Ei(),this.elementProperties.set(t,e),!e.noAccessor){const s=Symbol(),i=this.getPropertyDescriptor(t,s,e);i!==void 0&&dt(this.prototype,t,i)}}static getPropertyDescriptor(t,e,s){const{get:i,set:r}=pt(this.prototype,t)??{get(){return this[e]},set(n){this[e]=n}};return{get(){return i==null?void 0:i.call(this)},set(n){const h=i==null?void 0:i.call(this);r.call(this,n),this.requestUpdate(t,h,s)},configurable:!0,enumerable:!0}}static getPropertyOptions(t){return this.elementProperties.get(t)??W}static _$Ei(){if(this.hasOwnProperty(S("elementProperties")))return;const t=ft(this);t.finalize(),t.l!==void 0&&(this.l=[...t.l]),this.elementProperties=new Map(t.elementProperties)}static finalize(){if(this.hasOwnProperty(S("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(S("properties"))){const e=this.properties,s=[...ut(e),...bt(e)];for(const i of s)this.createProperty(i,e[i])}const t=this[Symbol.metadata];if(t!==null){const e=litPropertyMetadata.get(t);if(e!==void 0)for(const[s,i]of e)this.elementProperties.set(s,i)}this._$Eh=new Map;for(const[e,s]of this.elementProperties){const i=this._$Eu(e,s);i!==void 0&&this._$Eh.set(i,e)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(t){const e=[];if(Array.isArray(t)){const s=new Set(t.flat(1/0).reverse());for(const i of s)e.unshift(q(i))}else t!==void 0&&e.push(q(t));return e}static _$Eu(t,e){const s=e.attribute;return s===!1?void 0:typeof s=="string"?s:typeof t=="string"?t.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var t;this._$ES=new Promise(e=>this.enableUpdating=e),this._$AL=new Map,this._$E_(),this.requestUpdate(),(t=this.constructor.l)==null||t.forEach(e=>e(this))}addController(t){var e;(this._$EO??(this._$EO=new Set)).add(t),this.renderRoot!==void 0&&this.isConnected&&((e=t.hostConnected)==null||e.call(t))}removeController(t){var e;(e=this._$EO)==null||e.delete(t)}_$E_(){const t=new Map,e=this.constructor.elementProperties;for(const s of e.keys())this.hasOwnProperty(s)&&(t.set(s,this[s]),delete this[s]);t.size>0&&(this._$Ep=t)}createRenderRoot(){const t=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return ht(t,this.constructor.elementStyles),t}connectedCallback(){var t;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),(t=this._$EO)==null||t.forEach(e=>{var s;return(s=e.hostConnected)==null?void 0:s.call(e)})}enableUpdating(t){}disconnectedCallback(){var t;(t=this._$EO)==null||t.forEach(e=>{var s;return(s=e.hostDisconnected)==null?void 0:s.call(e)})}attributeChangedCallback(t,e,s){this._$AK(t,s)}_$EC(t,e){var r;const s=this.constructor.elementProperties.get(t),i=this.constructor._$Eu(t,s);if(i!==void 0&&s.reflect===!0){const n=(((r=s.converter)==null?void 0:r.toAttribute)!==void 0?s.converter:K).toAttribute(e,s.type);this._$Em=t,n==null?this.removeAttribute(i):this.setAttribute(i,n),this._$Em=null}}_$AK(t,e){var r;const s=this.constructor,i=s._$Eh.get(t);if(i!==void 0&&this._$Em!==i){const n=s.getPropertyOptions(i),h=typeof n.converter=="function"?{fromAttribute:n.converter}:((r=n.converter)==null?void 0:r.fromAttribute)!==void 0?n.converter:K;this._$Em=i,this[i]=h.fromAttribute(e,n.type),this._$Em=null}}requestUpdate(t,e,s){if(t!==void 0){if(s??(s=this.constructor.getPropertyOptions(t)),!(s.hasChanged??et)(this[t],e))return;this.P(t,e,s)}this.isUpdatePending===!1&&(this._$ES=this._$ET())}P(t,e,s){this._$AL.has(t)||this._$AL.set(t,e),s.reflect===!0&&this._$Em!==t&&(this._$Ej??(this._$Ej=new Set)).add(t)}async _$ET(){this.isUpdatePending=!0;try{await this._$ES}catch(e){Promise.reject(e)}const t=this.scheduleUpdate();return t!=null&&await t,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var s;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[r,n]of this._$Ep)this[r]=n;this._$Ep=void 0}const i=this.constructor.elementProperties;if(i.size>0)for(const[r,n]of i)n.wrapped!==!0||this._$AL.has(r)||this[r]===void 0||this.P(r,this[r],n)}let t=!1;const e=this._$AL;try{t=this.shouldUpdate(e),t?(this.willUpdate(e),(s=this._$EO)==null||s.forEach(i=>{var r;return(r=i.hostUpdate)==null?void 0:r.call(i)}),this.update(e)):this._$EU()}catch(i){throw t=!1,this._$EU(),i}t&&this._$AE(e)}willUpdate(t){}_$AE(t){var e;(e=this._$EO)==null||e.forEach(s=>{var i;return(i=s.hostUpdated)==null?void 0:i.call(s)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(t)),this.updated(t)}_$EU(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(t){return!0}update(t){this._$Ej&&(this._$Ej=this._$Ej.forEach(e=>this._$EC(e,this[e]))),this._$EU()}updated(t){}firstUpdated(t){}}_.elementStyles=[],_.shadowRootOptions={mode:"open"},_[S("elementProperties")]=new Map,_[S("finalized")]=new Map,R==null||R({ReactiveElement:_}),($.reactiveElementVersions??($.reactiveElementVersions=[])).push("2.0.4");/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */const E=globalThis,I=E.trustedTypes,V=I?I.createPolicy("lit-html",{createHTML:o=>o}):void 0,st="$lit$",m=`lit$${(Math.random()+"").slice(9)}$`,it="?"+m,$t=`<${it}>`,y=document,C=()=>y.createComment(""),k=o=>o===null||typeof o!="object"&&typeof o!="function",ot=Array.isArray,gt=o=>ot(o)||typeof(o==null?void 0:o[Symbol.iterator])=="function",H=`[ 	
\f\r]`,w=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,J=/-->/g,G=/>/g,g=RegExp(`>|${H}(?:([^\\s"'>=/]+)(${H}*=${H}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),Z=/'/g,Q=/"/g,nt=/^(?:script|style|textarea|title)$/i,vt=o=>(t,...e)=>({_$litType$:o,strings:t,values:e}),b=vt(1),A=Symbol.for("lit-noChange"),c=Symbol.for("lit-nothing"),X=new WeakMap,v=y.createTreeWalker(y,129);function rt(o,t){if(!Array.isArray(o)||!o.hasOwnProperty("raw"))throw Error("invalid template strings array");return V!==void 0?V.createHTML(t):t}const yt=(o,t)=>{const e=o.length-1,s=[];let i,r=t===2?"<svg>":"",n=w;for(let h=0;h<e;h++){const a=o[h];let d,p,l=-1,u=0;for(;u<a.length&&(n.lastIndex=u,p=n.exec(a),p!==null);)u=n.lastIndex,n===w?p[1]==="!--"?n=J:p[1]!==void 0?n=G:p[2]!==void 0?(nt.test(p[2])&&(i=RegExp("</"+p[2],"g")),n=g):p[3]!==void 0&&(n=g):n===g?p[0]===">"?(n=i??w,l=-1):p[1]===void 0?l=-2:(l=n.lastIndex-p[2].length,d=p[1],n=p[3]===void 0?g:p[3]==='"'?Q:Z):n===Q||n===Z?n=g:n===J||n===G?n=w:(n=g,i=void 0);const f=n===g&&o[h+1].startsWith("/>")?" ":"";r+=n===w?a+$t:l>=0?(s.push(d),a.slice(0,l)+st+a.slice(l)+m+f):a+m+(l===-2?h:f)}return[rt(o,r+(o[e]||"<?>")+(t===2?"</svg>":"")),s]};class P{constructor({strings:t,_$litType$:e},s){let i;this.parts=[];let r=0,n=0;const h=t.length-1,a=this.parts,[d,p]=yt(t,e);if(this.el=P.createElement(d,s),v.currentNode=this.el.content,e===2){const l=this.el.content.firstChild;l.replaceWith(...l.childNodes)}for(;(i=v.nextNode())!==null&&a.length<h;){if(i.nodeType===1){if(i.hasAttributes())for(const l of i.getAttributeNames())if(l.endsWith(st)){const u=p[n++],f=i.getAttribute(l).split(m),M=/([.?@])?(.*)/.exec(u);a.push({type:1,index:r,name:M[2],strings:f,ctor:M[1]==="."?At:M[1]==="?"?xt:M[1]==="@"?wt:N}),i.removeAttribute(l)}else l.startsWith(m)&&(a.push({type:6,index:r}),i.removeAttribute(l));if(nt.test(i.tagName)){const l=i.textContent.split(m),u=l.length-1;if(u>0){i.textContent=I?I.emptyScript:"";for(let f=0;f<u;f++)i.append(l[f],C()),v.nextNode(),a.push({type:2,index:++r});i.append(l[u],C())}}}else if(i.nodeType===8)if(i.data===it)a.push({type:2,index:r});else{let l=-1;for(;(l=i.data.indexOf(m,l+1))!==-1;)a.push({type:7,index:r}),l+=m.length-1}r++}}static createElement(t,e){const s=y.createElement("template");return s.innerHTML=t,s}}function x(o,t,e=o,s){var n,h;if(t===A)return t;let i=s!==void 0?(n=e._$Co)==null?void 0:n[s]:e._$Cl;const r=k(t)?void 0:t._$litDirective$;return(i==null?void 0:i.constructor)!==r&&((h=i==null?void 0:i._$AO)==null||h.call(i,!1),r===void 0?i=void 0:(i=new r(o),i._$AT(o,e,s)),s!==void 0?(e._$Co??(e._$Co=[]))[s]=i:e._$Cl=i),i!==void 0&&(t=x(o,i._$AS(o,t.values),i,s)),t}class _t{constructor(t,e){this._$AV=[],this._$AN=void 0,this._$AD=t,this._$AM=e}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(t){const{el:{content:e},parts:s}=this._$AD,i=((t==null?void 0:t.creationScope)??y).importNode(e,!0);v.currentNode=i;let r=v.nextNode(),n=0,h=0,a=s[0];for(;a!==void 0;){if(n===a.index){let d;a.type===2?d=new U(r,r.nextSibling,this,t):a.type===1?d=new a.ctor(r,a.name,a.strings,this,t):a.type===6&&(d=new St(r,this,t)),this._$AV.push(d),a=s[++h]}n!==(a==null?void 0:a.index)&&(r=v.nextNode(),n++)}return v.currentNode=y,i}p(t){let e=0;for(const s of this._$AV)s!==void 0&&(s.strings!==void 0?(s._$AI(t,s,e),e+=s.strings.length-2):s._$AI(t[e])),e++}}class U{get _$AU(){var t;return((t=this._$AM)==null?void 0:t._$AU)??this._$Cv}constructor(t,e,s,i){this.type=2,this._$AH=c,this._$AN=void 0,this._$AA=t,this._$AB=e,this._$AM=s,this.options=i,this._$Cv=(i==null?void 0:i.isConnected)??!0}get parentNode(){let t=this._$AA.parentNode;const e=this._$AM;return e!==void 0&&(t==null?void 0:t.nodeType)===11&&(t=e.parentNode),t}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(t,e=this){t=x(this,t,e),k(t)?t===c||t==null||t===""?(this._$AH!==c&&this._$AR(),this._$AH=c):t!==this._$AH&&t!==A&&this._(t):t._$litType$!==void 0?this.$(t):t.nodeType!==void 0?this.T(t):gt(t)?this.k(t):this._(t)}S(t){return this._$AA.parentNode.insertBefore(t,this._$AB)}T(t){this._$AH!==t&&(this._$AR(),this._$AH=this.S(t))}_(t){this._$AH!==c&&k(this._$AH)?this._$AA.nextSibling.data=t:this.T(y.createTextNode(t)),this._$AH=t}$(t){var r;const{values:e,_$litType$:s}=t,i=typeof s=="number"?this._$AC(t):(s.el===void 0&&(s.el=P.createElement(rt(s.h,s.h[0]),this.options)),s);if(((r=this._$AH)==null?void 0:r._$AD)===i)this._$AH.p(e);else{const n=new _t(i,this),h=n.u(this.options);n.p(e),this.T(h),this._$AH=n}}_$AC(t){let e=X.get(t.strings);return e===void 0&&X.set(t.strings,e=new P(t)),e}k(t){ot(this._$AH)||(this._$AH=[],this._$AR());const e=this._$AH;let s,i=0;for(const r of t)i===e.length?e.push(s=new U(this.S(C()),this.S(C()),this,this.options)):s=e[i],s._$AI(r),i++;i<e.length&&(this._$AR(s&&s._$AB.nextSibling,i),e.length=i)}_$AR(t=this._$AA.nextSibling,e){var s;for((s=this._$AP)==null?void 0:s.call(this,!1,!0,e);t&&t!==this._$AB;){const i=t.nextSibling;t.remove(),t=i}}setConnected(t){var e;this._$AM===void 0&&(this._$Cv=t,(e=this._$AP)==null||e.call(this,t))}}class N{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(t,e,s,i,r){this.type=1,this._$AH=c,this._$AN=void 0,this.element=t,this.name=e,this._$AM=i,this.options=r,s.length>2||s[0]!==""||s[1]!==""?(this._$AH=Array(s.length-1).fill(new String),this.strings=s):this._$AH=c}_$AI(t,e=this,s,i){const r=this.strings;let n=!1;if(r===void 0)t=x(this,t,e,0),n=!k(t)||t!==this._$AH&&t!==A,n&&(this._$AH=t);else{const h=t;let a,d;for(t=r[0],a=0;a<r.length-1;a++)d=x(this,h[s+a],e,a),d===A&&(d=this._$AH[a]),n||(n=!k(d)||d!==this._$AH[a]),d===c?t=c:t!==c&&(t+=(d??"")+r[a+1]),this._$AH[a]=d}n&&!i&&this.j(t)}j(t){t===c?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,t??"")}}class At extends N{constructor(){super(...arguments),this.type=3}j(t){this.element[this.name]=t===c?void 0:t}}class xt extends N{constructor(){super(...arguments),this.type=4}j(t){this.element.toggleAttribute(this.name,!!t&&t!==c)}}class wt extends N{constructor(t,e,s,i,r){super(t,e,s,i,r),this.type=5}_$AI(t,e=this){if((t=x(this,t,e,0)??c)===A)return;const s=this._$AH,i=t===c&&s!==c||t.capture!==s.capture||t.once!==s.once||t.passive!==s.passive,r=t!==c&&(s===c||i);i&&this.element.removeEventListener(this.name,this,s),r&&this.element.addEventListener(this.name,this,t),this._$AH=t}handleEvent(t){var e;typeof this._$AH=="function"?this._$AH.call(((e=this.options)==null?void 0:e.host)??this.element,t):this._$AH.handleEvent(t)}}class St{constructor(t,e,s){this.element=t,this.type=6,this._$AN=void 0,this._$AM=e,this.options=s}get _$AU(){return this._$AM._$AU}_$AI(t){x(this,t)}}const L=E.litHtmlPolyfillSupport;L==null||L(P,U),(E.litHtmlVersions??(E.litHtmlVersions=[])).push("3.1.2");const Et=(o,t,e)=>{const s=(e==null?void 0:e.renderBefore)??t;let i=s._$litPart$;if(i===void 0){const r=(e==null?void 0:e.renderBefore)??null;s._$litPart$=i=new U(t.insertBefore(C(),r),r,void 0,e??{})}return i._$AI(o),i};/**
 * @license
 * Copyright 2017 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */class O extends _{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var e;const t=super.createRenderRoot();return(e=this.renderOptions).renderBefore??(e.renderBefore=t.firstChild),t}update(t){const e=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(t),this._$Do=Et(e,this.renderRoot,this.renderOptions)}connectedCallback(){var t;super.connectedCallback(),(t=this._$Do)==null||t.setConnected(!0)}disconnectedCallback(){var t;super.disconnectedCallback(),(t=this._$Do)==null||t.setConnected(!1)}render(){return A}}var Y;O._$litElement$=!0,O.finalized=!0,(Y=globalThis.litElementHydrateSupport)==null||Y.call(globalThis,{LitElement:O});const z=globalThis.litElementPolyfillSupport;z==null||z({LitElement:O});(globalThis.litElementVersions??(globalThis.litElementVersions=[])).push("4.0.4");/**
 * @license
 * Copyright 2021 Google LLC
 * SPDX-License-Identifier: BSD-3-Clause
 */function*Ot(o,t){if(o!==void 0){let e=0;for(const s of o)yield t(s,e++)}}class Ct extends O{static get styles(){return lt`
      :host {
        font-size: var(--pb-multiselect-font-size, 1rem);
      }

      * {
        box-sizing: border-box;
      }

      .hidden {
        display: none;
      }

      .selected-options {
        display: flex;
        flex-flow: var(--pb-selected-options-flex-direction, row) wrap;
        gap: 0.5rem;
        list-style-type: none;
        max-width: var(--pb-selected-options-max-width, 100%);
        padding-inline-start: 0;
        width: var(--pb-selected-options-width, 100%);
      }

      .selected-options button {
        align-items: center;
        appearance: none;
        background: var(--pb-button-secondary-background, #f6f7f7);
        border: var(--pb-button-secondary-border, 1px #d4002d solid);
        border-radius: var(--pb-button-border-radius, 3px);
        color: var(--pb-button-secondary-color, #d4002d);
        cursor: pointer;
        display: inline-flex;
        font-family: var(
          --pb-button-font-family,
          -apple-system,
          BlinkMacSystemFont,
          'Segoe UI',
          Roboto,
          Oxygen-Sans,
          Ubuntu,
          Cantarell,
          'Helvetica Neue',
          sans-serif
        );
        font-size: var(--pb-button-font-size, 13px);
        gap: var(--pb-button-gap, 0.125em);
        line-height: var(--pb-button-line-height, 2.15384615);
        margin: 0;
        min-height: var(--pb-button-min-height, 30px);
        padding: var(--pb-button-padding, 0 10px);
        text-decoration: none;
        white-space: nowrap;
      }

      .selected-options button:hover {
        background: var(--pb-button-secondary-background-hover, #f0f0f1);
        border-color: var(--pb-button-secondary-border-color-hover, #a10022);
        color: var(--pb-button-secondary-color-hover, #a10022);
      }

      .selected-options button:focus {
        border-color: var(--pb-button-secondary-border-color-focus, #ff083c);
        box-shadow: var(
          --pb-button-secondary-box-shadow-focus,
          0 0 0 1px #ff083c
        );
        color: var(--pb-button-secondary-color-focus, #6e0017);
        outline: var(
          --pb-button-secondary-outline-focus,
          2px solid transparent
        );
        outline-offset: 0;
      }

      .selected-options button:active {
        background: var(--pb-button-secondary-background-active, #f6f7f7);
        border-color: var(--pb-button-secondary-border-color-active, #7e8993);
        box-shadow: none;
        color: var(--pb-button-secondary-color-active, #262a2e);
      }

      .selected-options button svg {
        height: var(--pb-button-icon-size, 1.25em);
        width: var(--pb-button-icon-size, 1.25em);
      }

      .selected-options button[disabled] {
        background: var(--pb-button-background-disabled, #f6f7f7) !important;
        border-color: var(
          --pb-button-border-color-disabled,
          #dcdcde
        ) !important;
        box-shadow: var(--pb-button-box-shadow-disabled, none) !important;
        color: var(--pb-button-color-disabled, #a7aaad) !important;
        cursor: default;
        transform: none !important;
      }

      .combo-container {
        max-width: var(--pb-combo-container-max-width, 100%);
        position: relative;
        width: var(--pb-combo-container-width, 100%);
      }

      input {
        background-color: var(--pb-input-background, #fff);
        border: var(--pb-input-border, 1px solid #8c8f94);
        border-radius: var(--pb-input-border-radius, 4px);
        box-shadow: var(--pb-input-box-shadow, 0 0 0 transparent);
        color: var(--pb-input-color, #2c3338);
        font-family: var(
          --pb-input-font-family,
          -apple-system,
          BlinkMacSystemFont,
          'Segoe UI',
          Roboto,
          Oxygen-Sans,
          Ubuntu,
          Cantarell,
          'Helvetica Neue',
          sans-serif
        );
        font-size: var(--pb-input-font-size, 14px);
        line-height: var(--pb-input-line-height, 2);
        max-width: 100%;
        min-height: var(--pb-input-min-height, 30px);
        padding: var(--pb-input-padding, 0 8px);
        width: var(--pb-input-width, 100%);
      }

      input:focus {
        border-color: var(--pb-input-border-color-focus, #d4002d);
        box-shadow: var(--pb-input-box-shadow-focus, 0 0 0 1px #d4002d);
        outline: var(--pb-input-outline-focus, 2px solid transparent);
      }

      input:disabled {
        background: var(
          --pb-input-background-disabled,
          rgba(255 255 255 / 50%)
        );
        border-color: var(
          --pb-input-border-color-disabled,
          rgba(220, 220, 222, 0.75)
        );
        box-shadow: var(
          --pb-input-box-shadow-disabled,
          inset 0 1px 2px rgba(0, 0, 0, 0.04)
        );
        color: var(--pb-input-color-disabled, rgba(44, 51, 56, 0.5));
      }

      input.combo-open {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
      }

      .combo-menu {
        background-color: var(--pb-combo-menu-background, #fff);
        border-bottom: var(--pb-combo-menu-border, 1px solid #8c8f94);
        border-bottom-left-radius: var(--pb-combo-menu-border-radius, 4px);
        border-bottom-right-radius: var(--pb-combo-menu-border-radius, 4px);
        border-left: var(--pb-combo-menu-border, 1px solid #8c8f94);
        border-right: var(--pb-combo-menu-border, 1px solid #8c8f94);
        box-shadow: 0;
        box-sizing: border-box;
        height: auto;
        margin: 0;
        max-height: 20rem;
        overflow-y: scroll;
        padding-inline-start: 0;
        position: absolute;
        width: 100%;
        z-index: var(--pb-combo-menu-z-index, 1);
      }

      .combo-group {
        margin: 0;
        padding-inline-start: 0;
      }

      input:focus + .combo-menu {
        border-color: var(--pb-input-border-color-focus, #d4002d);
        box-shadow: var(--pb-input-box-shadow-focus, 0 0 0 1px #d4002d);
      }

      .combo-option {
        background: var(--pb-combo-option-background, #fff);
      }

      .combo-group-label {
        background: var(--pb-combo-group-label-background, #f0f0f1);
        font-weight: 600;
      }

      .combo-option,
      .combo-group-label {
        cursor: default;
        font-family: var(
          --pb-combo-option-font-family,
          -apple-system,
          BlinkMacSystemFont,
          'Segoe UI',
          Roboto,
          Oxygen-Sans,
          Ubuntu,
          Cantarell,
          'Helvetica Neue',
          sans-serif
        );
        list-style: none;
        padding: var(--pb-combo-option-padding, 0.25rem 0.5rem);
      }

      .combo-group .combo-option {
        padding-inline-start: 1.25rem;
      }

      .combo-option:hover,
      .combo-option.option-current {
        background: var(--pb-combo-option-background-hover, #dedede);
        color: var(--pb-combo-option-color-hover, #000);
      }

      .combo-option:active,
      .combo-option:active:hover {
        background: var(--pb-combo-option-background-active, #333);
        color: var(--pb-combo-option-color-active, #fff);
      }

      .combo-option[aria-selected='true'] {
        background: var(--pb-combo-option-background-selected, #d4002d);
        color: var(--pb-combo-option-color-selected, #fff);
      }

      .combo-option:last-of-type {
        border-bottom-left-radius: var(--pb-combo-menu-border-radius, 3px);
        border-bottom-right-radius: var(--pb-combo-menu-border-radius, 3px);
      }
    `}static get properties(){return{htmlId:{type:String},disabled:{type:Boolean},label:{type:String},hint:{type:String},activeIndex:{type:Number},value:{type:String},open:{type:Boolean},groups:{type:Array},options:{type:Object},selectedOptions:{type:Array},filteredOptions:{type:Object},MenuActions:{type:Object},Keys:{type:Object}}}constructor(){super(),this.htmlId="",this.activeIndex=0,this.value="",this.open=!1,this.groups=[],this.options={},this.selectedOptions=[],this.filteredOptions={},this.MenuActions={Close:"Close",CloseSelect:"CloseSelect",First:"First",Last:"Last",Next:"Next",Open:"Open",PageDown:"PageDown",PageUp:"PageUp",Previous:"Previous",Select:"Select",Space:"Space",Type:"Type"},this.Keys={Backspace:"Backspace",Clear:"Clear",Down:"ArrowDown",End:"End",Enter:"Enter",Escape:"Escape",Home:"Home",Left:"ArrowLeft",PageDown:"PageDown",PageUp:"PageUp",Right:"ArrowRight",Space:" ",Tab:"Tab",Up:"ArrowUp"}}get _label(){return this.shadowRoot.querySelector("slot").assignedElements().filter(e=>e.matches("label"))[0]}get _select(){return this.shadowRoot.querySelector("slot").assignedElements().filter(e=>e.matches("select[multiple]"))[0]}get _hint(){const t=this.shadowRoot.querySelector("slot");if(this._select.getAttribute("aria-describedby")){const e=this._select.getAttribute("aria-describedby");return t.assignedElements().filter(s=>s.matches(`#${e}`))[0]}return!1}selectionsTemplate(){return b` <span id="${this.htmlId}-remove" hidden>remove</span>
      <ul class="selected-options">
        ${this.selectedOptions.map(t=>b`<li>
              <button
                class="remove-option"
                type="button"
                ?disabled="${this.disabled}"
                aria-describedby="${this.htmlId}-remove"
                data-option="${t}"
                @click="${this._handleRemove}"
              >
                <span>${this.options[t].label}</span
                ><svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  aria-hidden="true"
                  role="presentation"
                  fill="currentColor"
                >
                  <path
                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                  />
                </svg>
              </button>
            </li>`)}
      </ul>`}hintTemplate(){return b`<span id="${this.htmlId}-hint" hidden>${this.hint}</span>`}comboBoxTemplate(){const t={};for(const e of this.groups)t[e]=[];return Object.keys(this.filteredOptions).forEach((e,s)=>{const{group:i}=this.options[e];t[i??"null"].push(b`<li
          class="combo-option ${this.activeIndex===s?"option-current":""}"
          id="${this.htmlId}-${s}"
          aria-selected="${this.selectedOptions.indexOf(e)>-1}"
          role="option"
          data-option="${e}"
          @click="${this._handleOptionClick}"
        >
          ${this.options[e].label}
        </li>`)}),b`<div class="combo-container">
      ${this.hint?this.hintTemplate():c}
      <input
        aria-controls="${this.htmlId}-listbox"
        aria-activedescendant="${this.htmlId}-${this.activeIndex}"
        aria-autocomplete="list"
        aria-expanded="${this.open}"
        aria-haspopup="listbox"
        aria-label="${this.label}"
        aria-describedby="${this.htmlId}-hint"
        class="combo-input${this.open?" combo-open":""}"
        ?disabled="${this.disabled}"
        role="combobox"
        type="text"
        @input="${this._handleInput}"
        @focus="${this._handleInputFocus}"
        @keydown="${this._handleInputKeydown}"
      />
      <ul
        class="combo-menu ${this.open?"":"hidden"}"
        role="listbox"
        aria-label="${this.label}"
        aria-multiselectable="true"
        id="${this.htmlId}-listbox"
      >
        ${Ot(this.groups,(e,s)=>b`${e?b`<ul
                  class="combo-group"
                  role="group"
                  aria-labelledby="group-${s}"
                >
                  <li
                    class="combo-group-label"
                    role="presentation"
                    id="group-${s}"
                  >
                    ${e}
                  </li>
                  ${t[e]}
                </ul>`:b`${t.null}`}`)}
      </ul>
    </div>`}render(){return b`
      <div class="pressbooks-multiselect">
        <slot></slot>
        ${this.htmlId!==""&&this.label!==""?this.selectionsTemplate():c}
        ${this.htmlId!==""&&this.label!==""?this.comboBoxTemplate():c}
        <slot name="after"></slot>
      </div>
    `}connectedCallback(){super.connectedCallback(),window.addEventListener("click",this._handleWindowClick.bind(this))}disconnectedCallback(){window.removeEventListener("click",this._handleWindowClick.bind(this)),super.disconnectedCallback()}firstUpdated(){this._select&&(this._select.hidden=!0,this.htmlId=this._select.id,this._select.disabled&&(this.disabled=this._select.disabled),this.label=this._label.innerText,this.hint=this._hint?this._hint.innerText:"",this.options=Object.fromEntries(Array.from(this._select.querySelectorAll("option")).map(t=>[t.value,{label:t.textContent,group:t.parentNode.tagName==="OPTGROUP"?t.parentNode.getAttribute("label"):null}])),this.selectedOptions=Array.from(this._select.querySelectorAll("option[selected]")).map(t=>t.value),this.filteredOptions=this.options,this.groups=[...new Set(Object.values(this.filteredOptions).map(t=>t.group))])}_handleWindowClick(t){!this.shadowRoot.contains(t.target)&&!this.contains(t.target)&&(this.open=!1,this.update())}addOption(t){this._select.querySelector(`option[value="${t}"]`).setAttribute("selected",!0),this.selectedOptions.push(t)}removeOption(t){this._select.querySelector(`option[value="${t}"]`).removeAttribute("selected"),this.selectedOptions.splice(this.selectedOptions.indexOf(t),1)}updateMenuState(t){this.open=t}getUpdatedIndex(t,e,s){switch(s){case this.MenuActions.First:return 0;case this.MenuActions.Last:return e;case this.MenuActions.Previous:return Math.max(0,t-1);case this.MenuActions.Next:return Math.min(e,t+1);default:return t}}updateIndex(t){this.activeIndex=t,this.requestUpdate()}_handleRemove(t){const{option:e}=t.target.closest("button").dataset;this.removeOption(e),this.updateMenuState(!1),this.requestUpdate()}_handleInputFocus(){this.updateMenuState(!0)}_handleInputKeydown(t){const e=Object.keys(this.filteredOptions).length-1,s=this.getActionFromKey(t,this.open);switch(s){case this.MenuActions.Next:case this.MenuActions.Last:case this.MenuActions.First:case this.MenuActions.Previous:return t.preventDefault(),this.updateIndex(this.getUpdatedIndex(this.activeIndex,e,s));case this.MenuActions.CloseSelect:return t.preventDefault(),this.updateOption(this.activeIndex);case this.MenuActions.Close:return t.preventDefault(),this.updateMenuState(!1);case this.MenuActions.Open:return this.updateMenuState(!0);default:return!1}}_handleInput(t){this.open||(this.open=!0);const e=t.target.value.toLowerCase().trim();this.filteredOptions={};for(const[s,i]of Object.entries(this.options))i.label.toLowerCase().includes(e)&&(this.filteredOptions[s]=i);this.groups=[...new Set(Object.values(this.filteredOptions).map(s=>s.group))]}_handleOptionClick(t){const{option:e}=t.target.closest(".combo-option").dataset;this.selectedOptions.indexOf(e)>-1?this.removeOption(e):this.addOption(e),this.requestUpdate()}getActionFromKey(t,e){const{key:s,altKey:i,ctrlKey:r,metaKey:n}=t;if(!e&&["ArrowDown","ArrowUp","Enter"," ","Home","End"].includes(s))return this.MenuActions.Open;if(s===this.Keys.Backspace||s===this.Keys.Clear||s.length===1&&s!==" "&&!i&&!r&&!n)return this.MenuActions.Type;if(e){if(s===this.Keys.Down&&!i||s===this.Keys.Right)return this.MenuActions.Next;if(s===this.Keys.Up&&i)return this.MenuActions.CloseSelect;if(s===this.Keys.Up||s===this.Keys.Left)return this.MenuActions.Previous;if(s===this.Keys.Home)return this.MenuActions.First;if(s===this.Keys.End)return this.MenuActions.Last;if(s===this.Keys.PageUp)return this.MenuActions.PageUp;if(s===this.Keys.PageDown)return this.MenuActions.PageDown;if(s===this.Keys.Escape)return this.MenuActions.Close;if(s===this.Keys.Enter)return this.MenuActions.CloseSelect;if(s===this.Keys.Space)return this.MenuActions.Space}return!1}updateOption(t){const e=Object.keys(this.filteredOptions)[t];e&&(this.selectedOptions.indexOf(e)>-1?(this.removeOption(e),this.value=""):(this.addOption(e),this.value="",this.filteredOptions=this.options,this.activeIndex=Object.keys(this.filteredOptions).indexOf(e)),this.requestUpdate())}}window.customElements.define("pressbooks-multiselect",Ct);
//# sourceMappingURL=multiselect-be356870.js.map
