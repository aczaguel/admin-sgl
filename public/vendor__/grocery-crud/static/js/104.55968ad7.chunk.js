(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[104],{1205:function(e,t,a){"use strict";a.r(t);var n=a(31),l=a(4);var c={"@keyframes fadeIn":{"0%":{opacity:"0.1"},"66%":{opacity:" 0.5"},"100%":{opacity:"1"}},"columns-modal":{composes:"modal",display:e=>{let{columnsModalOpen:t}=e;return t?"block":"none"},opacity:"0","z-index":"10",animation:e=>{let{columnsModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"form-fields":{"overflow-x":"hidden","overflow-y":"auto",position:"relative","max-height":"calc(100vh - 240px)"}},o=a(1);var s=e=>{let{onChange:t,checked:a}=e;return Object(o.jsxs)(o.Fragment,{children:[Object(o.jsx)("input",{type:"checkbox",checked:a,onChange:t}),Object(o.jsx)("span",{})]})};const r=Object(n.b)(c);var i=e=>{let{columns:t,columnsModalOpen:a,onColumnsModalClose:n,selectColumnsAllOrNone:c,toggleVisibleColumn:i,visibleColumns:d}=e;const b=r({columnsModalOpen:a});return Object(o.jsx)("div",{className:b["columns-modal"],tabIndex:"-1",role:"dialog",children:Object(o.jsxs)("div",{className:"modal-dialog modal-l",role:"document",children:[Object(o.jsxs)("div",{className:"modal-content",children:[Object(o.jsx)("h4",{className:"modal-title",children:l.a.t("columns")}),Object(o.jsx)("div",{className:"modal-body",children:a&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsxs)("div",{children:[Object(o.jsx)("input",{type:"checkbox",onChange:()=>c(t),checked:d.length===t.length}),Object(o.jsx)("span",{})]}),t.map((e=>Object(o.jsx)("div",{children:Object(o.jsxs)("label",{children:[Object(o.jsx)(s,{checked:d.includes(e.name),onChange:()=>i(e.name,t)})," ",e.displayAs]})},e.name)))]})})]}),Object(o.jsx)("div",{className:"modal-footer",children:Object(o.jsx)("button",{type:"button",className:"modal-close waves-effect waves-green btn-flat",onClick:n,children:l.a.t("close_modal")})})]})})};var d={"column-text":{overflow:"hidden","max-width":"350px","text-overflow":"ellipsis","white-space":"nowrap","vertical-align":"middle"},"column-action":{"white-space":"nowrap"}},b=a(261);const m=Object(n.b)(d);var j=e=>{const{rows:t,visibleColumns:a,hasActions:n}=e,l=m(e);return Object(o.jsx)("tbody",{children:t.map(((t,c)=>Object(o.jsxs)("tr",{children:[n&&Object(o.jsx)("td",{className:l["column-action"],children:Object(o.jsx)(b.a,{...e,primaryKeyValue:t.grocery_crud_extras.primaryKeyValue})},"column__action"),a.map((e=>Object(o.jsx)("td",{children:t[e.name]?t[e.name]:Object(o.jsx)(o.Fragment,{children:"\xa0"})},e.name)))]},c)))})};var u={checkbox:{"margin-right":"10px"}};const h=Object(n.b)(u);var p=e=>{let{onChange:t,checked:a}=e;const n=h();return Object(o.jsxs)("label",{children:[Object(o.jsx)("input",{type:"checkbox",className:n.checkbox,onChange:t,checked:a}),Object(o.jsx)("span",{})]})},x=a(214),O=a(239),f=a.n(O),g=a(266);var v={footer:{display:"flex",padding:"5px",justifyContent:"space-between",alignItems:"center"},"footer-child":{display:"flex",alignItems:"center","& > div":{paddingRight:"5px"}},pagination:{composes:"pagination",margin:"0"},"pagination-item":{composes:"waves-effect","& input.gc-page-input[type=number]":{width:"100px",height:"32px",padding:"6px 12px",margin:"0","box-sizing":"border-box"}},"pagination-button":{"& i.fas":{"font-size":"18px"}},"@keyframes spin":{"0%":{transform:"rotate(0deg)"},"100%":{transform:"rotate(360deg)"}},loader:{border:"4px solid #f3f3f3","border-radius":"50%","border-top":"4px solid #3498db",width:"16px",height:"16px","-webkit-animation":"$spin 2s linear infinite",animation:"$spin 2s linear infinite","margin-right":"10px"}};const y=Object(n.b)(v);var N=e=>{const t=y(e),{filteredTotalEntries:a,goToFirstPage:n,goToLastPage:l,goToNextPage:c,goToPreviousPage:s,lastPage:r,page:i,pageChange:d,totalEntries:b,perPage:m,perPageChange:j,forceSearch:u,pagingLoading:h,pagingOptions:p}=e;return Object(o.jsxs)("div",{className:t.footer,children:[Object(o.jsxs)("div",{className:t["footer-child"],children:[Object(o.jsx)("div",{children:"Show"}),Object(o.jsx)("div",{className:"floatL r5 l5 t3 per-page-container",children:Object(o.jsx)("select",{className:"form-control form-select",onChange:j,value:m,style:{display:"block"},children:p.map((e=>Object(o.jsx)("option",{value:e,children:e},e)))})}),Object(o.jsx)("div",{children:"entries"})]}),Object(o.jsxs)("div",{className:t["footer-child"],children:[h&&Object(o.jsx)("div",{className:t.loader}),a&&!h?Object(o.jsx)("div",{children:Object(o.jsx)(g.a,{currentPage:i,totalEntries:b,perPage:m,filteredTotalEntries:a})}):null,Object(o.jsx)("div",{children:Object(o.jsxs)("ul",{className:t.pagination,children:[Object(o.jsx)("li",{className:f()("waves-effect",{disabled:1===i}),children:Object(o.jsx)("a",{href:"#!",onClick:e=>{e.preventDefault(),n(i,r)},className:t["pagination-button"],children:Object(o.jsx)(x.a,{icon:"step-backward"})})}),Object(o.jsx)("li",{className:f()("waves-effect",{disabled:1===i}),children:Object(o.jsx)("a",{href:"#!",onClick:e=>{e.preventDefault(),s(i,r)},className:t["pagination-button"],children:Object(o.jsx)(x.a,{icon:"chevron-left"})})}),Object(o.jsx)("li",{className:t["pagination-item"],children:Object(o.jsx)("input",{type:"number",className:"gc-page-input",value:i,onChange:e=>d(e,i,r),disabled:0===a,onKeyUp:e=>{"Enter"===e.key&&u()}})}),Object(o.jsx)("li",{className:f()("waves-effect",{disabled:i===r}),children:Object(o.jsx)("a",{href:"#!",onClick:e=>{e.preventDefault(),c(i,r)},className:t["pagination-button"],children:Object(o.jsx)(x.a,{icon:"chevron-right"})})}),Object(o.jsx)("li",{className:f()("waves-effect",{disabled:i===r}),children:Object(o.jsx)("a",{href:"#!",onClick:e=>{e.preventDefault(),l(i,r)},className:t["pagination-button"],children:Object(o.jsx)(x.a,{icon:"step-forward"})})})]})})]})]})};var k={"search-column":{"min-width":"160px"},"table-th-with-ordering":{cursor:"pointer","&:hover":{background:"var(--gc-table-hover-background)"}},"actions-column-header":{"align-items":"center",display:"flex",height:"39px"},"with-ordering":{display:"flex","justify-content":"space-between","align-items":"center"},"input-text":{composes:"form-control"},"input-select":{composes:"form-control form-select"},"input-select-search":{}},C=a(235),w=a(238),_=a(2);const S=Object(n.b)(k),M=e=>{const{DatagridCheckbox:t,columnOrdering:a,columnSearch:n,columnSearchValues:c,extendedSearchData:s,forceSearch:r,hasActions:i,onMultipleDeleteModalOpen:d,onSelectRowAllOrNone:b,options:{actionButtonHasText:m},selectRowsAllOrNoneChecked:j,selectedIds:u,sorting:h,sortingFor:p,visibleColumns:O,loadCssThirdParty:f}=e,g=S(e),v=0===s.length;return Object(o.jsxs)("thead",{children:[Object(o.jsxs)("tr",{children:[i&&O.length>0&&Object(o.jsx)("th",{children:l.a.t("actions")}),O.map((e=>Object(o.jsx)("th",{className:g["table-th-with-ordering"],onClick:()=>a({columnName:e.name,sorting:""===h||"desc"===h?"asc":"desc"}),children:Object(o.jsxs)("div",{className:g["with-ordering"],children:[Object(o.jsx)("span",{children:e.displayAs}),p===e.name?Object(o.jsx)(x.a,{icon:"asc"===h?"sort-amount-down-alt":"sort-amount-down"}):Object(o.jsx)(x.a,{icon:"sort"})]})},e.name)))]}),Object(o.jsxs)("tr",{children:[i&&O.length>0&&Object(o.jsx)("td",{children:Object(o.jsxs)("div",{className:g["actions-column-header"],children:[Object(o.jsx)(t,{onChange:b,checked:j}),u.length>0&&Object(o.jsxs)("button",{type:"button",className:"btn btn-default btn-outline-dark",onClick:d,children:[Object(o.jsx)(x.a,{icon:"trash"}),"\xa0\xa0",m&&Object(o.jsx)("span",{children:l.a.t("action_delete")})]})]})}),O.map((e=>{const t=Object(C.l)(e.dataType);return Object(o.jsx)("td",{className:g["search-column"],children:v&&Object(o.jsx)(t,{className:g[Object(w.b)(e.dataType)],placeholder:l.a.t("quick_search"),permittedValues:e.permittedValues,loadCssThirdParty:f,onChange:t=>{n({columnName:e.name,searchValue:t.target.value,searchValueDisplayAs:t.target.displayAs}),!0===Object(C.n)(e.dataType)&&r()},onKeyUp:e=>{"Enter"===e.key&&r()},value:c[e.name]?c[e.name]:""})},e.name)}))]})]})};M.defaultProps={hasActions:!1,visibleColumns:[],options:{actionButtonHasText:!0}};var F=M;var D=e=>{let{title:t}=e;return Object(o.jsx)("div",{className:"gc-datagrid-title",children:t})};var V={"datagrid-tools":{position:"relative",padding:"10px","border-left":"1px solid var(--gc-border-separator-color)","border-right":"1px solid var(--gc-border-separator-color)",display:"flex","justify-content":"space-between"},"tools-container":{display:"flex"},"simple-button":{composes:"btn","margin-right":"5px"}},I=a(231),T=a(0),A=a(241);var E={"dropdown-menu":{composes:"dropdown-content",left:"auto",top:"auto",display:e=>{let{opened:t}=e;return t?"block":"none"},opacity:e=>{let{opened:t}=e;return t?"1":"0"},right:e=>{let{direction:t}=e;return t===A.a.RIGHT?"0":"auto"}},"dropdown-menu-container":{display:"inline-flex","margin-left":e=>{let{leftSpace:t}=e;return t?"5px":"0"}},"dropdown-menu-button":{composes:"dropdown-trigger btn",display:"inline-flex","align-items":"center"}};const P=Object(n.b)(E),L=e=>{let{buttons:t,buttonIcon:a,buttonLabel:n,direction:l,leftSpace:c}=e;const s=Object(_.useRef)(null),[r,i]=Object(_.useState)(!1);const d=P({opened:r,direction:l,leftSpace:c});return 0===t.length?null:Object(o.jsxs)("div",{className:d["dropdown-menu-container"],ref:s,children:[Object(o.jsxs)("button",{className:d["dropdown-menu-button"],onClick:function(){i(!r)},onBlur:function(e){setTimeout((()=>{i(!1)}),200)},children:[a&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsx)(x.a,{icon:a}),"\xa0"]}),n,"\xa0\xa0",Object(o.jsx)(x.a,{icon:"caret-down"})]}),Object(o.jsx)("ul",{className:d["dropdown-menu"],children:t.map((e=>Object(o.jsx)("li",{children:Object(o.jsx)("a",{href:e.url?e.url:"",target:e.newTab?"_blank":void 0,className:"dropdown-item",rel:"noreferrer",onClick:e.onClick?t=>{t.preventDefault(),e.onClick&&e.onClick({primaryKeyValue:e.primaryKeyValue})}:void 0,children:Object(o.jsxs)("span",{children:[e.icon&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsx)(x.a,{icon:e.icon}),"\xa0\xa0"]}),e.text]})})},e.key)))})]})};L.defaultProps={buttons:[],buttonIcon:"",buttonLabel:"",direction:"left",leftSpace:!1};var B=L,K=a(24);var R={"simple-button":{composes:"btn","margin-right":"5px","margin-left":e=>{let{leftSpace:t}=e;return t?"5px":void 0},".modal .modal-footer .btn&":{"margin-right":"5px","margin-left":e=>{let{leftSpace:t}=e;return t?"5px":void 0}}}};const z=Object(n.b)(R);var $=e=>{let{onClick:t,icon:a,label:n,link:l=!1,href:c,leftSpace:s}=e;const r=z({leftSpace:s});return l?Object(o.jsxs)("a",{className:r["simple-button"],onClick:t,href:c,children:[a&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsx)(x.a,{icon:a}),"\xa0"]}),n]}):Object(o.jsxs)("button",{className:r["simple-button"],onClick:t,children:[a&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsx)(x.a,{icon:a}),"\xa0"]}),n]})},H=a(9),U=a(10),G=a(26);const q=Object(n.b)(V);var J=e=>{const{apiUrl:t,columnSearchValues:a,extendedSearchData:n,hasAdd:c,onAdd:s,onClearCache:r,onClearFiltering:i,onFilteringModalOpen:d,onOrderingReset:b,onRefresh:m,sorting:j,sortingFor:u,subject:h,visibleColumnsAsShortString:p}=e,O=q(e),f=Object(H.c)(),g=Object(H.d)((e=>e.configuration.hasSettings)),v=Object(H.d)((e=>e.configuration.hasPrint)),y=Object(H.d)((e=>e.configuration.hasFilters)),N=Object(H.d)((e=>e.configuration.hasColumnsButton)),k=Object(H.d)((e=>e.configuration.hasExportData)),C=Object(H.d)((e=>e.configuration.hasExportPdf)),w=Object(H.d)((e=>e.configuration.hasExportExcel)),_=c,S={apiUrl:t,columnSearchValues:a,sorting:j,sortingFor:u,visibleColumnsAsShortString:p,extendedSearchData:n};try{return _?Object(o.jsxs)("div",{className:O["datagrid-tools"],children:[c&&Object(o.jsx)($,{link:!0,href:"/add",icon:"plus",label:Object(I.c)(T.a.ADD,h),onClick:e=>{e.preventDefault(),s()}}),Object(o.jsxs)("div",{children:[y&&Object(o.jsxs)("button",{className:n.length>0?O["success-button"]:O["simple-button"],onClick:d,children:[Object(o.jsx)(x.a,{icon:"filter"}),"\xa0",Object(I.b)(n.length)]}),y&&n.length>0&&Object(o.jsxs)("button",{className:O["danger-button"],onClick:i,children:[Object(o.jsx)(x.a,{icon:"eraser"}),"\xa0",l.a.t("filtering_remove_filters")]}),N&&Object(o.jsx)($,{onClick:()=>(e=>e({type:U.a.MODAL_OPEN}))(f),label:l.a.t("columns"),icon:"list-alt"}),v&&Object(o.jsxs)("a",{className:O["simple-button"],href:Object(K.g)(S),rel:"noreferrer",target:"_blank",children:[Object(o.jsx)(x.a,{icon:"print"}),"\xa0",l.a.t("print")]}),k&&Object(o.jsx)(B,{buttons:[w&&{icon:"file-excel",text:"Excel",url:Object(K.a)(S),newTab:!0,key:"excel"},C&&{icon:"file-pdf",text:"PDF",url:Object(K.f)(S),newTab:!0,key:"pdf"}],buttonLabel:l.a.t("export_to_file"),buttonIcon:"download",leftSpace:!0}),g&&Object(o.jsx)(B,{leftSpace:!0,direction:A.a.RIGHT,buttons:[{icon:"broom",text:l.a.t("clear_cache"),onClick:r,key:"clear_cache"},{icon:"eraser",text:l.a.t("clear_filtering"),onClick:i,key:"clear_filtering"},{icon:"unlink",text:l.a.t("reset_ordering"),onClick:b,key:"reset_ordering"},{icon:"sync-alt",text:l.a.t("refresh"),onClick:m,key:"refresh"}],buttonLabel:l.a.t("settings"),buttonIcon:"cog"})]})]}):null}catch(M){return console.log(M),Object(o.jsx)(G.a,{})}};var W=e=>{let{children:t}=e;return Object(o.jsx)("table",{className:"highlight",children:t})};const Q={composes:"modal modal-fixed-footer",opacity:"0","z-index":"10"};var X={"@keyframes fadeIn":{"0%":{opacity:"0.1"},"66%":{opacity:" 0.5"},"100%":{opacity:"1"}},"@keyframes shine":{to:{"background-position":"100% 0"}},"form-dialog":{...Q,display:e=>{let{formModalOpen:t}=e;return t?"block":"none"},animation:e=>{let{formModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"modal-delete-one":{...Q,display:e=>{let{deleteOneModalOpen:t}=e;return t?"block":"none"},animation:e=>{let{deleteOneModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"modal-delete-multiple":{...Q,display:e=>{let{deleteMultipleModalOpen:t}=e;return t?"block":"none"},animation:e=>{let{deleteMultipleModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"form-fields":{position:"relative","max-height":"calc(100vh - 240px)"},"skeleton-loader":{width:"100%",height:"15px",border:"1px solid rgb(239,239,239)","border-radius":"4px",display:"block","background-repeat":"repeat-y","background-size":"50px 500px","background-position":"0 0",background:"linear-gradient(to right, rgba(239, 239, 239, 0), rgba(239, 239, 239, 0.5) 50%, rgba(239, 239, 239, 0) 80%),rgb(255,255,255)",animation:"$shine 2s infinite","animation-delay":"0.3s"}};var Y={"mini-grid":{composes:"table table-bordered table-hover","margin-bottom":"0px"},"scrolling-wrapper":{width:"100%",position:"relative","overflow-x":"auto"},"column-text":{"&>span":{"text-overflow":"ellipsis","white-space":"nowrap",overflow:"hidden"},"align-items":"center","max-width":"350px","min-width":"0","vertical-align":"middle",display:"flex",height:"38px"},"mini-grid-body":{".table &":{"border-top":"none"}}},Z=a(7);const ee=Object(n.b)(Y),te=e=>e.configuration.locale,ae=e=>e.configuration.dateFormat;var ne=e=>{let{visibleColumns:t,rows:a}=e;const n=ee(),l=Object(H.d)(te),c=Object(H.d)(ae);return Object(o.jsx)("div",{className:n["scrolling-wrapper"],children:Object(o.jsxs)("table",{className:n["mini-grid"],children:[Object(o.jsx)("thead",{children:Object(o.jsx)("tr",{children:t.map((e=>e.dataType===Z.a.INVISIBLE?null:Object(o.jsx)("th",{children:e.displayAs},e.name)))})}),Object(o.jsx)("tbody",{className:n["mini-grid-body"],children:a.map(((e,a)=>Object(o.jsx)("tr",{children:t.map((t=>t.dataType===Z.a.INVISIBLE?null:Object(o.jsx)("td",{children:Object(o.jsx)("div",{className:n["column-text"],children:Object(w.a)(e[t.name],t.dataType,{permittedValues:t.permittedValues,fieldName:t.name,locale:l,dateFormat:c,primaryKeyValue:e.grocery_crud_extras.primaryKeyValue})})},t.name)))},a)))})]})})};const le=Object(n.b)(X);var ce=e=>{const{deleteMultipleModalOpen:t,deleteMultipleModalClose:a,deleteMultiple:n,selectedIds:c,visibleColumnsWithDetails:s,rows:r}=e,i=le(e);return Object(o.jsx)("div",{className:i["modal-delete-multiple"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:Object(o.jsx)("div",{className:"modal-dialog modal-xl",role:"document",children:Object(o.jsxs)("div",{className:"modal-content",children:[Object(o.jsxs)("div",{className:"modal-header",children:[Object(o.jsx)("h5",{className:"modal-title",children:l.a.t("action_delete")}),Object(o.jsx)("button",{type:"button",className:"btn-close","data-dismiss":"modal","aria-label":"Close",onClick:a})]}),Object(o.jsxs)("div",{className:"modal-body",children:[Object(o.jsx)("p",{children:Object(I.a)(c.length)}),t&&Object(o.jsx)(ne,{visibleColumns:s,rows:r.filter((e=>c.includes(e.grocery_crud_extras.primaryKeyValue)))})]}),t&&Object(o.jsxs)("div",{className:"modal-footer",children:[Object(o.jsx)("button",{type:"button",className:"btn btn-default btn-outline-dark","data-dismiss":"modal",onClick:a,children:l.a.t("cancel")}),c.length>0&&Object(o.jsx)("button",{type:"button",className:"btn btn-danger delete-single-confirmation-button",onClick:n,children:l.a.t("action_delete")})]})]})})})};const oe=Object(n.b)(X);var se=e=>{const{deleteOneModalOpen:t,deleteOneModalClose:a,deleteOne:n}=e,c=oe(e);return Object(o.jsxs)("div",{className:c["modal-delete-one"],tabIndex:"-1",children:[Object(o.jsxs)("div",{className:"modal-content",children:[Object(o.jsx)("h4",{children:l.a.t("action_delete")}),Object(o.jsx)("p",{children:l.a.t("confirm_delete")})]}),t&&Object(o.jsxs)("div",{className:"modal-footer",children:[Object(o.jsx)($,{onClick:a,label:l.a.t("cancel")}),Object(o.jsx)("button",{type:"button",className:"btn red",onClick:n,children:l.a.t("action_delete")})]})]})};var re={"error-dialog":{composes:"modal","z-index":"1100",display:e=>{let{showError:t}=e;return t?"block":"none"}},"error-details":{width:"100%",height:"200px"}};const ie=Object(n.b)(re),de=e=>{let{closeModal:t,showError:a,details:n,message:c}=e;const s=ie({showError:a});return Object(o.jsx)("div",{className:s["error-dialog"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:Object(o.jsx)("div",{className:"modal-dialog modal-xl",role:"document",children:Object(o.jsxs)("div",{className:"modal-content",children:[Object(o.jsxs)("div",{className:"modal-header",children:[Object(o.jsx)("h5",{className:"modal-title",children:l.a.t("error_generic_title")}),Object(o.jsx)("button",{type:"button",className:"btn-close","data-dismiss":"modal","aria-label":"Close",onClick:t})]}),Object(o.jsxs)("div",{className:"modal-body",children:[Object(o.jsxs)("div",{children:["Message: ",c]}),Object(o.jsx)("div",{children:"Error:"}),Object(o.jsx)("div",{children:Object(o.jsx)("textarea",{defaultValue:n||"",className:s["error-details"]})})]}),Object(o.jsx)("div",{className:"modal-footer",children:Object(o.jsx)("button",{type:"button",className:"btn btn-default btn-outline-dark","data-dismiss":"modal",onClick:t,children:l.a.t("close_modal")})})]})})})};de.defaultProps={formFields:[]};var be=de;var me={"@keyframes fadeIn":{"0%":{opacity:"0.1"},"66%":{opacity:" 0.5"},"100%":{opacity:"1"}},"filtering-modal":{composes:"modal","z-index":"10",display:e=>{let{filteringModalOpen:t}=e;return t?"block":"none"},opacity:"0",animation:e=>{let{filteringModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"form-fields":{"overflow-x":"hidden","overflow-y":"auto",position:"relative","max-height":"calc(100vh - 240px)"}},je=a(230);var ue={"filtering-row":{composes:"row","padding-top":"10px","padding-bottom":"10px"}},he=a(61),pe=a(60),xe=a(267);const Oe=Object(n.b)(ue);var fe=e=>{const{onFilteringModalClose:t,columns:a,onSubmit:n,extendedSearchOperator:c,extendedSearchData:s}=e,{control:r,handleSubmit:i,getValues:d}=Object(je.d)({defaultValues:{basic_operator:c||"AND",extended_search:s.length>0?s:[{name:a[0].name,filter:"contains",value:""}]}}),{fields:b,append:m,remove:j,insert:u}=Object(je.c)({control:r,name:"extended_search"}),h=Oe(e),p=b.length;return Object(o.jsxs)("form",{className:"form-horizontal",onSubmit:i((e=>{if(n){let t=[];e.extended_search.forEach((e=>{null!==e&&t.push(e)})),n(Object(he.a)({...e,extended_search:t}))}}),((e,t)=>console.log(e,t))),children:[Object(o.jsxs)("div",{className:"modal-header",children:[Object(o.jsx)("h5",{className:"modal-title",children:l.a.t("filtering_filter_text")}),Object(o.jsx)("button",{type:"button",className:"btn-close","data-dismiss":"modal","aria-label":"Close",onClick:t})]}),Object(o.jsxs)("div",{className:"modal-body",children:[Object(o.jsxs)("div",{className:"row",children:[Object(o.jsxs)("label",{className:"col-md-3 control-label",children:[l.a.t("filtering_operator")," :"]}),Object(o.jsx)("div",{className:"col-md-3",children:Object(o.jsx)(je.a,{render:e=>{let{field:{onChange:t,onBlur:a,value:n}}=e;return Object(o.jsxs)("select",{name:"basic_operator",onChange:t,className:"form-control form-select",defaultValue:n,children:[Object(o.jsx)("option",{value:"AND",children:l.a.t("filtering_and_statement")}),Object(o.jsx)("option",{value:"OR",children:l.a.t("filtering_or_statement")})]})},name:"basic_operator",control:r,defaultValue:"AND"})})]}),Object(o.jsx)("div",{children:b.map(((e,t)=>{const n=d("extended_search[".concat(t,"]")).name,l=a.find((e=>e.name===n));return Object(o.jsxs)("div",{className:h["filtering-row"],children:[Object(o.jsx)("div",{className:"col-md-1",children:Object(o.jsx)("button",{className:"btn btn-outline-dark btn-block",type:"button",onClick:()=>j(t),disabled:1===p,children:Object(o.jsx)(x.a,{icon:"trash"})})}),Object(o.jsx)("div",{className:"col-md-3",children:Object(o.jsx)(je.a,{render:e=>{let{field:{onChange:n,onBlur:l,value:c}}=e;return Object(o.jsx)("select",{onChange:e=>{const l={...d("extended_search[".concat(t,"]"))},c=e.target.value,o=a.find((e=>e.name===l.name)),s=a.find((e=>e.name===c));Object(pe.a)(o.dataType,s.dataType)&&(j(t),u(t,{name:c,filter:Object(pe.d)(s.dataType),value:""})),n(e)},className:"form-control form-select",name:"extended_search[".concat(t,"].name"),value:c,children:a.map((e=>e.isSearchable&&Object(o.jsx)("option",{value:e.name,children:e.displayAs},e.name)))})},name:"extended_search[".concat(t,"].name"),control:r,defaultValue:e.firstName})}),Object(o.jsx)("div",{className:"col-md-3",children:Object(o.jsx)(je.a,{render:e=>{let{field:{onChange:a,value:n}}=e;return Object(o.jsx)(xe.a,{onChange:e=>{const n={...d("extended_search[".concat(t,"]"))},l=e.target.value;Object(pe.b)(n.filter,l)&&(j(t),u(t,{name:n.name,filter:l,value:pe.c[l]?null:""})),a(e)},className:"form-control form-select",name:"extended_search[".concat(t,"].filter"),value:n,dataType:l.dataType})},name:"extended_search[".concat(t,"].filter"),control:r,defaultValue:e.filter})}),Object(o.jsx)("div",{className:"col-md-5",children:Object(o.jsx)(je.a,{render:e=>{let{field:{onChange:a,onBlur:n,value:c}}=e;if(null===c)return null;const{dataType:s,permittedValues:r}=l,i=Object(C.l)(s);return Object(o.jsx)(i,{onChange:a,onBlur:n,className:h[Object(C.j)(s)],name:"extended_search[".concat(t,"].value"),value:c,required:!0,permittedValues:r})},name:"extended_search[".concat(t,"].value"),control:r,defaultValue:e.value})})]},e.id)}))}),Object(o.jsx)("div",{className:h["filtering-row"],children:Object(o.jsx)("div",{className:"col-md-12",children:Object(o.jsxs)("button",{type:"button",className:"btn btn-default btn-outline-dark",onClick:()=>{m({name:a[0].name,filter:"contains",value:""})},children:[Object(o.jsx)(x.a,{icon:"plus"}),"\xa0",l.a.t("filtering_add_more")]})})})]}),Object(o.jsxs)("div",{className:"modal-footer",children:[Object(o.jsx)($,{onClick:t,label:l.a.t("filtering_cancel")}),Object(o.jsx)("button",{type:"submit",className:"btn green",children:l.a.t("filtering_filter_text")})]})]})};const ge=Object(n.b)(me);var ve=e=>{const{filteringModalOpen:t,onFilteringSubmit:a,columns:n}=e,l=ge(e);return Object(o.jsx)("div",{className:l["filtering-modal"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:t&&Object(o.jsx)(fe,{...e,fields:n,onSubmit:a})})},ye=a(307),Ne=a(21);const ke=Object(n.b)(X);var Ce=e=>{let{modalLoading:t,formModalClose:a,formFields:n,formState:c,onFormSubmit:s,readOnly:r,publishEvents:i}=e;const{handleSubmit:d,control:b,setValue:m}=Object(je.d)();Object(_.useEffect)((()=>{const e=e=>{let{detail:t}=e;m(t.fieldName,t.fieldValue)};return i&&Object(Ne.c)(Ne.a,e),()=>{i&&Object(Ne.d)(Ne.a,e)}}),[m,i]);const j=ke({modalLoading:t,formModalClose:a,formFields:n});return Object(o.jsx)(o.Fragment,{children:Object(o.jsxs)("form",{onSubmit:d((e=>{s({formState:c,data:e})}),((e,t)=>console.log(e,t))),children:[Object(o.jsxs)("div",{className:"modal-content",children:[Object(o.jsx)("h4",{children:Object(I.c)(c)}),Object(o.jsx)("div",{className:j["form-fields"],children:t?"Loading...":n.map((e=>Object(o.jsxs)("div",{className:"row",children:[Object(o.jsx)("label",{htmlFor:"gc-form-".concat(e.fieldName),children:e.displayAs}),Object(o.jsx)("div",{children:Object(o.jsx)(ye.a,{className:"form-control",name:e.fieldName,value:e.fieldValue,control:b,id:"form-".concat(e.fieldName)})})]},e.fieldName)))})]}),Object(o.jsxs)("div",{className:"modal-footer",children:[!r&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsxs)("label",{children:[Object(o.jsx)("input",{type:"checkbox"}),Object(o.jsx)("span",{}),l.a.t("close_modal_on_save")]})," ","\xa0 \xa0"]}),Object(o.jsx)("button",{type:"button",className:"modal-close waves-effect waves-green btn-flat",onClick:a,children:l.a.t("close_modal")}),!r&&Object(o.jsx)("button",{type:"submit",className:"btn btn-primary",children:l.a.t("modal_save")})]})]})})};const we=Object(n.b)(X),_e=e=>{const{modalLoading:t,formModalClose:a,formFields:n,formState:c,onFormSubmit:s,formIsReadOnly:r}=e,i=we(e);return Object(o.jsx)("div",{className:i["form-dialog"],tabIndex:"-1",children:t?Object(o.jsxs)(o.Fragment,{children:[Object(o.jsxs)("div",{className:"modal-content",children:[Object(o.jsx)("h4",{children:Object(I.c)(c)}),Object(o.jsx)("div",{className:i["skeleton-loader"]})]}),Object(o.jsx)("div",{className:"modal-footer",children:Object(o.jsx)("button",{type:"button",className:"modal-close waves-effect waves-green btn-flat",onClick:a,children:l.a.t("close_modal")})})]}):Object(o.jsx)(Ce,{formFields:n,formModalClose:a,modalLoading:t,formState:c,onFormSubmit:s,readOnly:r})})};_e.defaultProps={formFields:[]};var Se=_e;const Me=Object(n.b)(R),Fe=e=>{const{buttons:t,maxButtons:a}=e,n=Me(e);return Object(o.jsxs)(o.Fragment,{children:[t.filter(((e,t)=>t<a-1)).map((e=>Object(o.jsxs)("a",{href:e.url?e.url:"",className:n["simple-button"],onClick:t=>{t.preventDefault(),e.onClick&&e.onClick({primaryKeyValue:e.primaryKeyValue})},children:[e.icon&&Object(o.jsxs)(o.Fragment,{children:[Object(o.jsx)(x.a,{icon:e.icon}),"\xa0"]}),e.text]},e.key))),Object(o.jsx)(B,{buttons:t.filter(((e,t)=>t>=a-1)),buttonLabel:1===a?l.a.t("actions"):l.a.t("more")})]})};Fe.defaultProps={buttons:[],maxButtons:2};const De={ColumnsModal:i,DatagridBody:j,DatagridCheckbox:p,DatagridFooter:N,DatagridHeader:F,DatagridTitle:D,DatagridTools:J,DatagridWrapper:W,DeleteMultipleModal:ce,DeleteSingleModal:se,ErrorDialog:be,FilteringModal:ve,FormDialog:Se,GroupButtons:Fe};t.default=De}}]);
//# sourceMappingURL=104.55968ad7.chunk.js.map