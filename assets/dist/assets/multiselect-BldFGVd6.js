const P=globalThis,H=P.ShadowRoot&&(P.ShadyCSS===void 0||P.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,L=Symbol(),j=new WeakMap;let X=class{constructor(t,e,s){if(this._$cssResult$=!0,s!==L)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=t,this.t=e}get styleSheet(){let t=this.o;const e=this.t;if(H&&t===void 0){const s=e!==void 0&&e.length===1;s&&(t=j.get(e)),t===void 0&&((this.o=t=new CSSStyleSheet).replaceSync(this.cssText),s&&j.set(e,t))}return t}toString(){return this.cssText}};const ot=o=>new X(typeof o=="string"?o:o+"",void 0,L),nt=(o,...t)=>{const e=o.length===1?o[0]:t.reduce(((s,i,n)=>s+(r=>{if(r._$cssResult$===!0)return r.cssText;if(typeof r=="number")return r;throw Error("Value passed to 'css' function must be a 'css' function result: "+r+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(i)+o[n+1]),o[0]);return new X(e,o,L)},rt=(o,t)=>{if(H)o.adoptedStyleSheets=t.map((e=>e instanceof CSSStyleSheet?e:e.styleSheet));else for(const e of t){const s=document.createElement("style"),i=P.litNonce;i!==void 0&&s.setAttribute("nonce",i),s.textContent=e.cssText,o.appendChild(s)}},K=H?o=>o:o=>o instanceof CSSStyleSheet?(t=>{let e="";for(const s of t.cssRules)e+=s.cssText;return ot(e)})(o):o;const{is:at,defineProperty:lt,getOwnPropertyDescriptor:ht,getOwnPropertyNames:ct,getOwnPropertySymbols:dt,getPrototypeOf:pt}=Object,T=globalThis,q=T.trustedTypes,ut=q?q.emptyScript:"",bt=T.reactiveElementPolyfillSupport,x=(o,t)=>o,N={toAttribute(o,t){switch(t){case Boolean:o=o?ut:null;break;case Object:case Array:o=o==null?o:JSON.stringify(o)}return o},fromAttribute(o,t){let e=o;switch(t){case Boolean:e=o!==null;break;case Number:e=o===null?null:Number(o);break;case Object:case Array:try{e=JSON.parse(o)}catch{e=null}}return e}},Y=(o,t)=>!at(o,t),F={attribute:!0,type:String,converter:N,reflect:!1,useDefault:!1,hasChanged:Y};Symbol.metadata??=Symbol("metadata"),T.litPropertyMetadata??=new WeakMap;let y=class extends HTMLElement{static addInitializer(t){this._$Ei(),(this.l??=[]).push(t)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(t,e=F){if(e.state&&(e.attribute=!1),this._$Ei(),this.prototype.hasOwnProperty(t)&&((e=Object.create(e)).wrapped=!0),this.elementProperties.set(t,e),!e.noAccessor){const s=Symbol(),i=this.getPropertyDescriptor(t,s,e);i!==void 0&&lt(this.prototype,t,i)}}static getPropertyDescriptor(t,e,s){const{get:i,set:n}=ht(this.prototype,t)??{get(){return this[e]},set(r){this[e]=r}};return{get:i,set(r){const h=i?.call(this);n?.call(this,r),this.requestUpdate(t,h,s)},configurable:!0,enumerable:!0}}static getPropertyOptions(t){return this.elementProperties.get(t)??F}static _$Ei(){if(this.hasOwnProperty(x("elementProperties")))return;const t=pt(this);t.finalize(),t.l!==void 0&&(this.l=[...t.l]),this.elementProperties=new Map(t.elementProperties)}static finalize(){if(this.hasOwnProperty(x("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(x("properties"))){const e=this.properties,s=[...ct(e),...dt(e)];for(const i of s)this.createProperty(i,e[i])}const t=this[Symbol.metadata];if(t!==null){const e=litPropertyMetadata.get(t);if(e!==void 0)for(const[s,i]of e)this.elementProperties.set(s,i)}this._$Eh=new Map;for(const[e,s]of this.elementProperties){const i=this._$Eu(e,s);i!==void 0&&this._$Eh.set(i,e)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(t){const e=[];if(Array.isArray(t)){const s=new Set(t.flat(1/0).reverse());for(const i of s)e.unshift(K(i))}else t!==void 0&&e.push(K(t));return e}static _$Eu(t,e){const s=e.attribute;return s===!1?void 0:typeof s=="string"?s:typeof t=="string"?t.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){this._$ES=new Promise((t=>this.enableUpdating=t)),this._$AL=new Map,this._$E_(),this.requestUpdate(),this.constructor.l?.forEach((t=>t(this)))}addController(t){(this._$EO??=new Set).add(t),this.renderRoot!==void 0&&this.isConnected&&t.hostConnected?.()}removeController(t){this._$EO?.delete(t)}_$E_(){const t=new Map,e=this.constructor.elementProperties;for(const s of e.keys())this.hasOwnProperty(s)&&(t.set(s,this[s]),delete this[s]);t.size>0&&(this._$Ep=t)}createRenderRoot(){const t=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return rt(t,this.constructor.elementStyles),t}connectedCallback(){this.renderRoot??=this.createRenderRoot(),this.enableUpdating(!0),this._$EO?.forEach((t=>t.hostConnected?.()))}enableUpdating(t){}disconnectedCallback(){this._$EO?.forEach((t=>t.hostDisconnected?.()))}attributeChangedCallback(t,e,s){this._$AK(t,s)}_$ET(t,e){const s=this.constructor.elementProperties.get(t),i=this.constructor._$Eu(t,s);if(i!==void 0&&s.reflect===!0){const n=(s.converter?.toAttribute!==void 0?s.converter:N).toAttribute(e,s.type);this._$Em=t,n==null?this.removeAttribute(i):this.setAttribute(i,n),this._$Em=null}}_$AK(t,e){const s=this.constructor,i=s._$Eh.get(t);if(i!==void 0&&this._$Em!==i){const n=s.getPropertyOptions(i),r=typeof n.converter=="function"?{fromAttribute:n.converter}:n.converter?.fromAttribute!==void 0?n.converter:N;this._$Em=i;const h=r.fromAttribute(e,n.type);this[i]=h??this._$Ej?.get(i)??h,this._$Em=null}}requestUpdate(t,e,s){if(t!==void 0){const i=this.constructor,n=this[t];if(s??=i.getPropertyOptions(t),!((s.hasChanged??Y)(n,e)||s.useDefault&&s.reflect&&n===this._$Ej?.get(t)&&!this.hasAttribute(i._$Eu(t,s))))return;this.C(t,e,s)}this.isUpdatePending===!1&&(this._$ES=this._$EP())}C(t,e,{useDefault:s,reflect:i,wrapped:n},r){s&&!(this._$Ej??=new Map).has(t)&&(this._$Ej.set(t,r??e??this[t]),n!==!0||r!==void 0)||(this._$AL.has(t)||(this.hasUpdated||s||(e=void 0),this._$AL.set(t,e)),i===!0&&this._$Em!==t&&(this._$Eq??=new Set).add(t))}async _$EP(){this.isUpdatePending=!0;try{await this._$ES}catch(e){Promise.reject(e)}const t=this.scheduleUpdate();return t!=null&&await t,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??=this.createRenderRoot(),this._$Ep){for(const[i,n]of this._$Ep)this[i]=n;this._$Ep=void 0}const s=this.constructor.elementProperties;if(s.size>0)for(const[i,n]of s){const{wrapped:r}=n,h=this[i];r!==!0||this._$AL.has(i)||h===void 0||this.C(i,void 0,n,h)}}let t=!1;const e=this._$AL;try{t=this.shouldUpdate(e),t?(this.willUpdate(e),this._$EO?.forEach((s=>s.hostUpdate?.())),this.update(e)):this._$EM()}catch(s){throw t=!1,this._$EM(),s}t&&this._$AE(e)}willUpdate(t){}_$AE(t){this._$EO?.forEach((e=>e.hostUpdated?.())),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(t)),this.updated(t)}_$EM(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(t){return!0}update(t){this._$Eq&&=this._$Eq.forEach((e=>this._$ET(e,this[e]))),this._$EM()}updated(t){}firstUpdated(t){}};y.elementStyles=[],y.shadowRootOptions={mode:"open"},y[x("elementProperties")]=new Map,y[x("finalized")]=new Map,bt?.({ReactiveElement:y}),(T.reactiveElementVersions??=[]).push("2.1.1");const B=globalThis,U=B.trustedTypes,W=U?U.createPolicy("lit-html",{createHTML:o=>o}):void 0,tt="$lit$",m=`lit$${Math.random().toFixed(9).slice(2)}$`,et="?"+m,ft=`<${et}>`,v=document,E=()=>v.createComment(""),O=o=>o===null||typeof o!="object"&&typeof o!="function",D=Array.isArray,mt=o=>D(o)||typeof o?.[Symbol.iterator]=="function",R=`[ 	
\f\r]`,w=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,V=/-->/g,Z=/>/g,g=RegExp(`>|${R}(?:([^\\s"'>=/]+)(${R}*=${R}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),J=/'/g,G=/"/g,st=/^(?:script|style|textarea|title)$/i,gt=o=>(t,...e)=>({_$litType$:o,strings:t,values:e}),b=gt(1),_=Symbol.for("lit-noChange"),c=Symbol.for("lit-nothing"),Q=new WeakMap,$=v.createTreeWalker(v,129);function it(o,t){if(!D(o)||!o.hasOwnProperty("raw"))throw Error("invalid template strings array");return W!==void 0?W.createHTML(t):t}const $t=(o,t)=>{const e=o.length-1,s=[];let i,n=t===2?"<svg>":t===3?"<math>":"",r=w;for(let h=0;h<e;h++){const a=o[h];let d,p,l=-1,u=0;for(;u<a.length&&(r.lastIndex=u,p=r.exec(a),p!==null);)u=r.lastIndex,r===w?p[1]==="!--"?r=V:p[1]!==void 0?r=Z:p[2]!==void 0?(st.test(p[2])&&(i=RegExp("</"+p[2],"g")),r=g):p[3]!==void 0&&(r=g):r===g?p[0]===">"?(r=i??w,l=-1):p[1]===void 0?l=-2:(l=r.lastIndex-p[2].length,d=p[1],r=p[3]===void 0?g:p[3]==='"'?G:J):r===G||r===J?r=g:r===V||r===Z?r=w:(r=g,i=void 0);const f=r===g&&o[h+1].startsWith("/>")?" ":"";n+=r===w?a+ft:l>=0?(s.push(d),a.slice(0,l)+tt+a.slice(l)+m+f):a+m+(l===-2?h:f)}return[it(o,n+(o[e]||"<?>")+(t===2?"</svg>":t===3?"</math>":"")),s]};class C{constructor({strings:t,_$litType$:e},s){let i;this.parts=[];let n=0,r=0;const h=t.length-1,a=this.parts,[d,p]=$t(t,e);if(this.el=C.createElement(d,s),$.currentNode=this.el.content,e===2||e===3){const l=this.el.content.firstChild;l.replaceWith(...l.childNodes)}for(;(i=$.nextNode())!==null&&a.length<h;){if(i.nodeType===1){if(i.hasAttributes())for(const l of i.getAttributeNames())if(l.endsWith(tt)){const u=p[r++],f=i.getAttribute(l).split(m),M=/([.?@])?(.*)/.exec(u);a.push({type:1,index:n,name:M[2],strings:f,ctor:M[1]==="."?yt:M[1]==="?"?_t:M[1]==="@"?At:I}),i.removeAttribute(l)}else l.startsWith(m)&&(a.push({type:6,index:n}),i.removeAttribute(l));if(st.test(i.tagName)){const l=i.textContent.split(m),u=l.length-1;if(u>0){i.textContent=U?U.emptyScript:"";for(let f=0;f<u;f++)i.append(l[f],E()),$.nextNode(),a.push({type:2,index:++n});i.append(l[u],E())}}}else if(i.nodeType===8)if(i.data===et)a.push({type:2,index:n});else{let l=-1;for(;(l=i.data.indexOf(m,l+1))!==-1;)a.push({type:7,index:n}),l+=m.length-1}n++}}static createElement(t,e){const s=v.createElement("template");return s.innerHTML=t,s}}function A(o,t,e=o,s){if(t===_)return t;let i=s!==void 0?e._$Co?.[s]:e._$Cl;const n=O(t)?void 0:t._$litDirective$;return i?.constructor!==n&&(i?._$AO?.(!1),n===void 0?i=void 0:(i=new n(o),i._$AT(o,e,s)),s!==void 0?(e._$Co??=[])[s]=i:e._$Cl=i),i!==void 0&&(t=A(o,i._$AS(o,t.values),i,s)),t}class vt{constructor(t,e){this._$AV=[],this._$AN=void 0,this._$AD=t,this._$AM=e}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(t){const{el:{content:e},parts:s}=this._$AD,i=(t?.creationScope??v).importNode(e,!0);$.currentNode=i;let n=$.nextNode(),r=0,h=0,a=s[0];for(;a!==void 0;){if(r===a.index){let d;a.type===2?d=new k(n,n.nextSibling,this,t):a.type===1?d=new a.ctor(n,a.name,a.strings,this,t):a.type===6&&(d=new wt(n,this,t)),this._$AV.push(d),a=s[++h]}r!==a?.index&&(n=$.nextNode(),r++)}return $.currentNode=v,i}p(t){let e=0;for(const s of this._$AV)s!==void 0&&(s.strings!==void 0?(s._$AI(t,s,e),e+=s.strings.length-2):s._$AI(t[e])),e++}}class k{get _$AU(){return this._$AM?._$AU??this._$Cv}constructor(t,e,s,i){this.type=2,this._$AH=c,this._$AN=void 0,this._$AA=t,this._$AB=e,this._$AM=s,this.options=i,this._$Cv=i?.isConnected??!0}get parentNode(){let t=this._$AA.parentNode;const e=this._$AM;return e!==void 0&&t?.nodeType===11&&(t=e.parentNode),t}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(t,e=this){t=A(this,t,e),O(t)?t===c||t==null||t===""?(this._$AH!==c&&this._$AR(),this._$AH=c):t!==this._$AH&&t!==_&&this._(t):t._$litType$!==void 0?this.$(t):t.nodeType!==void 0?this.T(t):mt(t)?this.k(t):this._(t)}O(t){return this._$AA.parentNode.insertBefore(t,this._$AB)}T(t){this._$AH!==t&&(this._$AR(),this._$AH=this.O(t))}_(t){this._$AH!==c&&O(this._$AH)?this._$AA.nextSibling.data=t:this.T(v.createTextNode(t)),this._$AH=t}$(t){const{values:e,_$litType$:s}=t,i=typeof s=="number"?this._$AC(t):(s.el===void 0&&(s.el=C.createElement(it(s.h,s.h[0]),this.options)),s);if(this._$AH?._$AD===i)this._$AH.p(e);else{const n=new vt(i,this),r=n.u(this.options);n.p(e),this.T(r),this._$AH=n}}_$AC(t){let e=Q.get(t.strings);return e===void 0&&Q.set(t.strings,e=new C(t)),e}k(t){D(this._$AH)||(this._$AH=[],this._$AR());const e=this._$AH;let s,i=0;for(const n of t)i===e.length?e.push(s=new k(this.O(E()),this.O(E()),this,this.options)):s=e[i],s._$AI(n),i++;i<e.length&&(this._$AR(s&&s._$AB.nextSibling,i),e.length=i)}_$AR(t=this._$AA.nextSibling,e){for(this._$AP?.(!1,!0,e);t!==this._$AB;){const s=t.nextSibling;t.remove(),t=s}}setConnected(t){this._$AM===void 0&&(this._$Cv=t,this._$AP?.(t))}}class I{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(t,e,s,i,n){this.type=1,this._$AH=c,this._$AN=void 0,this.element=t,this.name=e,this._$AM=i,this.options=n,s.length>2||s[0]!==""||s[1]!==""?(this._$AH=Array(s.length-1).fill(new String),this.strings=s):this._$AH=c}_$AI(t,e=this,s,i){const n=this.strings;let r=!1;if(n===void 0)t=A(this,t,e,0),r=!O(t)||t!==this._$AH&&t!==_,r&&(this._$AH=t);else{const h=t;let a,d;for(t=n[0],a=0;a<n.length-1;a++)d=A(this,h[s+a],e,a),d===_&&(d=this._$AH[a]),r||=!O(d)||d!==this._$AH[a],d===c?t=c:t!==c&&(t+=(d??"")+n[a+1]),this._$AH[a]=d}r&&!i&&this.j(t)}j(t){t===c?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,t??"")}}class yt extends I{constructor(){super(...arguments),this.type=3}j(t){this.element[this.name]=t===c?void 0:t}}class _t extends I{constructor(){super(...arguments),this.type=4}j(t){this.element.toggleAttribute(this.name,!!t&&t!==c)}}class At extends I{constructor(t,e,s,i,n){super(t,e,s,i,n),this.type=5}_$AI(t,e=this){if((t=A(this,t,e,0)??c)===_)return;const s=this._$AH,i=t===c&&s!==c||t.capture!==s.capture||t.once!==s.once||t.passive!==s.passive,n=t!==c&&(s===c||i);i&&this.element.removeEventListener(this.name,this,s),n&&this.element.addEventListener(this.name,this,t),this._$AH=t}handleEvent(t){typeof this._$AH=="function"?this._$AH.call(this.options?.host??this.element,t):this._$AH.handleEvent(t)}}class wt{constructor(t,e,s){this.element=t,this.type=6,this._$AN=void 0,this._$AM=e,this.options=s}get _$AU(){return this._$AM._$AU}_$AI(t){A(this,t)}}const xt=B.litHtmlPolyfillSupport;xt?.(C,k),(B.litHtmlVersions??=[]).push("3.3.1");const St=(o,t,e)=>{const s=e?.renderBefore??t;let i=s._$litPart$;if(i===void 0){const n=e?.renderBefore??null;s._$litPart$=i=new k(t.insertBefore(E(),n),n,void 0,e??{})}return i._$AI(o),i};const z=globalThis;class S extends y{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){const t=super.createRenderRoot();return this.renderOptions.renderBefore??=t.firstChild,t}update(t){const e=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(t),this._$Do=St(e,this.renderRoot,this.renderOptions)}connectedCallback(){super.connectedCallback(),this._$Do?.setConnected(!0)}disconnectedCallback(){super.disconnectedCallback(),this._$Do?.setConnected(!1)}render(){return _}}S._$litElement$=!0,S.finalized=!0,z.litElementHydrateSupport?.({LitElement:S});const Et=z.litElementPolyfillSupport;Et?.({LitElement:S});(z.litElementVersions??=[]).push("4.2.1");function*Ot(o,t){if(o!==void 0){let e=0;for(const s of o)yield t(s,e++)}}class Ct extends S{static get styles(){return nt`
      :host {
        font-size: var(--pb-select-font-size, 1rem);
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

      .selected-options:not(:has(li)) {
        margin-block: 0;
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
          "Segoe UI",
          Roboto,
          Oxygen-Sans,
          Ubuntu,
          Cantarell,
          "Helvetica Neue",
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
        margin-block-start: 1em;
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
          "Segoe UI",
          Roboto,
          Oxygen-Sans,
          Ubuntu,
          Cantarell,
          "Helvetica Neue",
          sans-serif
        );
        font-size: var(--pb-input-font-size, 14px);
        line-height: var(--pb-input-line-height, 2);
        max-width: 100%;
        min-height: var(--pb-input-min-height, 30px);
        padding: var(--pb-input-padding, 0 8px);
        width: var(--pb-input-width, 100%);
      }

      input[data-multiple="false"] {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%232c3338' class='size-5'%3E%3Cpath fill-rule='evenodd' d='M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z' clip-rule='evenodd' /%3E%3C/svg%3E%0A");
        background-repeat: no-repeat;
        background-position: center right;
        padding: var(--pb-select-input-padding, 0 32px 0 8px);
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
          "Segoe UI",
          Roboto,
          Oxygen-Sans,
          Ubuntu,
          Cantarell,
          "Helvetica Neue",
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

      .combo-option[aria-selected="true"] {
        background: var(--pb-combo-option-background-selected, #d4002d);
        color: var(--pb-combo-option-color-selected, #fff);
      }

      .combo-option:last-of-type {
        border-bottom-left-radius: var(--pb-combo-menu-border-radius, 3px);
        border-bottom-right-radius: var(--pb-combo-menu-border-radius, 3px);
      }
    `}static get properties(){return{htmlId:{type:String},callFocus:{type:Boolean},ignoreBlur:{type:Boolean},disabled:{type:Boolean},max:{type:Number},label:{type:String},hint:{type:String},activeIndex:{type:Number},value:{type:String},open:{type:Boolean},multiple:{type:Boolean},groups:{type:Array},options:{type:Object},selectedOptions:{type:Array},filteredOptions:{type:Object},MenuActions:{type:Object},Keys:{type:Object}}}constructor(){super(),this.max=0,this.htmlId="",this.activeIndex=0,this.value="",this.callFocus=!1,this.ignoreBlur=!1,this.open=!1,this.multiple=!1,this.groups=[],this.options={},this.selectedOptions=[],this.filteredOptions={},this.MenuActions={Close:"Close",CloseSelect:"CloseSelect",First:"First",Last:"Last",Next:"Next",Open:"Open",PageDown:"PageDown",PageUp:"PageUp",Previous:"Previous",Select:"Select",Space:"Space",Type:"Type"},this.Keys={Backspace:"Backspace",Clear:"Clear",Down:"ArrowDown",End:"End",Enter:"Enter",Escape:"Escape",Home:"Home",Left:"ArrowLeft",PageDown:"PageDown",PageUp:"PageUp",Right:"ArrowRight",Space:" ",Tab:"Tab",Up:"ArrowUp"}}get _label(){return this.shadowRoot.querySelector("slot").assignedElements().filter(e=>e.matches("label"))[0]}get _select(){return this.shadowRoot.querySelector("slot").assignedElements().filter(e=>e.matches("select"))[0]}get _hint(){const t=this.shadowRoot.querySelector("slot:not([name])"),e=this.shadowRoot.querySelector('slot[name="after"]');if(this._select.getAttribute("aria-describedby")){const s=this._select.getAttribute("aria-describedby"),n=t.assignedElements().filter(r=>r.matches(`#${s}`))[0];if(n)return n;if(e){const h=e.assignedElements().filter(a=>a.matches(`#${s}`))[0];if(h)return h}}return!1}get _input(){return this.shadowRoot.querySelector("input")}get _selectionLessThanMax(){return this.max>0?this.selectedOptions.length<this.max:!0}selectionsTemplate(){return b` <span id="${this.htmlId}-remove" hidden>remove</span>
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
          @mousedown="${this._handleOptionMousedown}"
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
        class="combo-input${this.open&&this._selectionLessThanMax?" combo-open":""}"
        data-multiple="${this.multiple}"
        ?disabled="${this.disabled||!this._selectionLessThanMax}"
        role="combobox"
        type="text"
        value="${this.value}"
        @input="${this._handleInput}"
        @focus="${this._handleInputFocus}"
        @blur="${this._handleInputBlur}"
        @keydown="${this._handleInputKeydown}"
      />
      <ul
        class="combo-menu ${this.open&&this._selectionLessThanMax?"":"hidden"}"
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
        ${this.htmlId!==""&&this.label!==""&&this.multiple?this.selectionsTemplate():c}
        ${this.htmlId!==""&&this.label!==""?this.comboBoxTemplate():c}
        <slot name="after"></slot>
      </div>
    `}connectedCallback(){super.connectedCallback(),window.addEventListener("click",this._handleWindowClick.bind(this)),window.addEventListener("focus",this._handleWindowFocus.bind(this))}disconnectedCallback(){window.removeEventListener("click",this._handleWindowClick.bind(this)),window.removeEventListener("focus",this._handleWindowFocus.bind(this)),super.disconnectedCallback()}firstUpdated(){this._select&&(this._select.hidden=!0,this.multiple=this._select.hasAttribute("multiple"),this.htmlId=this._select.id,this._select.disabled&&(this.disabled=this._select.disabled),this.label=this._label.innerText,this.hint=this._hint?this._hint.innerText:"",this.options=Object.fromEntries(Array.from(this._select.querySelectorAll("option")).map(t=>[t.value,{label:t.textContent,group:t.parentNode.tagName==="OPTGROUP"?t.parentNode.getAttribute("label"):null}])),this.selectedOptions=Array.from(this._select.querySelectorAll("option[selected]")).map(t=>t.value),this.filteredOptions=this.options,this.multiple||(this.value=this._select.querySelector("option[selected]")?.textContent||""),this.groups=[...new Set(Object.values(this.filteredOptions).map(t=>t.group))])}updated(){this.callFocus===!0&&(this._input.focus(),this.callFocus=!1)}_handleWindowClick(t){!this.shadowRoot.contains(t.target)&&!this.contains(t.target)&&(this.open=!1,this.update())}_handleWindowFocus(t){this.open=!1,this.update()}addOption(t){this._select.querySelector(`option[value="${t}"]`).setAttribute("selected",!0),this.multiple?this.selectedOptions.push(t):(this.selectedOptions=[t],this._input.blur(),this._input.value=this.options[t].label,this.open=!1,this.update())}removeOption(t){this._select.querySelector(`option[value="${t}"]`).removeAttribute("selected"),this.multiple?this.selectedOptions.splice(this.selectedOptions.indexOf(t),1):(this.selectedOptions=[],this._input.blur(),this._input.value="",this.open=!1,this.update())}updateMenuState(t,e=!0){this.open=t,this.callFocus=e}getUpdatedIndex(t,e,s){switch(s){case this.MenuActions.First:return 0;case this.MenuActions.Last:return e;case this.MenuActions.Previous:return Math.max(0,t-1);case this.MenuActions.Next:return Math.min(e,t+1);default:return t}}updateIndex(t){this.activeIndex=t,this.requestUpdate();const e=this.shadowRoot.querySelector(".combo-menu"),s=this.shadowRoot.querySelector(".option-current");s&&(e.scrollTop=s.offsetTop-e.offsetTop)}_handleRemove(t){const{option:e}=t.target.closest("button").dataset;this.removeOption(e),this.updateMenuState(!1),this.requestUpdate()}_handleInputFocus(){this.updateMenuState(!0)}_handleInputBlur(){if(this.ignoreBlur){this.ignoreBlur=!1;return}this.updateMenuState(!1,!1)}_handleInputKeydown(t){const e=Object.keys(this.filteredOptions).length-1,s=this.getActionFromKey(t,this.open);switch(s){case this.MenuActions.Next:case this.MenuActions.Last:case this.MenuActions.First:case this.MenuActions.Previous:return t.preventDefault(),this.updateIndex(this.getUpdatedIndex(this.activeIndex,e,s));case this.MenuActions.CloseSelect:return t.preventDefault(),this.updateOption(this.activeIndex);case this.MenuActions.Close:return t.preventDefault(),this.updateMenuState(!1);case this.MenuActions.Open:return this.updateMenuState(!0);default:return!1}}_handleInput(t){this.open||(this.open=!0);const e=t.target.value.toLowerCase().trim();this.filteredOptions={};for(const[s,i]of Object.entries(this.options))i.label.toLowerCase().includes(e)&&(this.filteredOptions[s]=i);this.groups=[...new Set(Object.values(this.filteredOptions).map(s=>s.group))]}_handleOptionClick(t){const{option:e}=t.target.closest(".combo-option").dataset;this.selectedOptions.indexOf(e)>-1?this.removeOption(e):this.addOption(e),this.requestUpdate()}_handleOptionMousedown(){this.ignoreBlur=!0,this.callFocus=!0}getActionFromKey(t,e){const{key:s,altKey:i,ctrlKey:n,metaKey:r}=t;if(!e&&["ArrowDown","ArrowUp","Enter"," ","Home","End"].includes(s))return this.MenuActions.Open;if(s===this.Keys.Backspace||s===this.Keys.Clear||s.length===1&&s!==" "&&!i&&!n&&!r)return this.MenuActions.Type;if(e){if(s===this.Keys.Down&&!i||s===this.Keys.Right)return this.MenuActions.Next;if(s===this.Keys.Up&&i)return this.MenuActions.CloseSelect;if(s===this.Keys.Up||s===this.Keys.Left)return this.MenuActions.Previous;if(s===this.Keys.Home)return this.MenuActions.First;if(s===this.Keys.End)return this.MenuActions.Last;if(s===this.Keys.PageUp)return this.MenuActions.PageUp;if(s===this.Keys.PageDown)return this.MenuActions.PageDown;if(s===this.Keys.Escape)return this.MenuActions.Close;if(s===this.Keys.Enter)return this.MenuActions.CloseSelect;if(s===this.Keys.Space)return this.MenuActions.Space}return!1}updateOption(t){const e=Object.keys(this.filteredOptions)[t];e&&(this.selectedOptions.indexOf(e)>-1?this.removeOption(e):(this.addOption(e),this.filteredOptions=this.options,this.activeIndex=Object.keys(this.filteredOptions).indexOf(e)),this.requestUpdate())}}window.customElements.get("pressbooks-select")||window.customElements.define("pressbooks-select",Ct);
//# sourceMappingURL=multiselect-BldFGVd6.js.map
