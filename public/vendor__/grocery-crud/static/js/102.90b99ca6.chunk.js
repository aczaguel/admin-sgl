(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[102],{1203:function(e,t,a){"use strict";a.r(t);var r=a(31),l=a(4);var o={"@keyframes fadeIn":{"0%":{opacity:"0.1"},"66%":{opacity:" 0.5"},"100%":{opacity:"1"}},"columns-modal":{composes:"modal",display:e=>{let{columnsModalOpen:t}=e;return t?"block":"none"},opacity:"0","z-index":"10",animation:e=>{let{columnsModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"form-fields":{"overflow-x":"hidden","overflow-y":"auto",position:"relative","max-height":"calc(100vh - 240px)"}},n=a(1);const s=Object(r.b)(o);var c=e=>{let{columns:t,columnsModalOpen:a,onColumnsModalClose:r,selectColumnsAllOrNone:o,toggleVisibleColumn:c,visibleColumns:i}=e;const d=s({columnsModalOpen:a});return Object(n.jsx)("div",{className:d["columns-modal"],tabIndex:"-1",role:"dialog",children:Object(n.jsx)("div",{className:"modal-dialog modal-l",role:"document",children:Object(n.jsxs)("div",{className:"modal-content",children:[Object(n.jsxs)("div",{className:"modal-header",children:[Object(n.jsx)("h5",{className:"modal-title",children:l.a.t("columns")}),Object(n.jsx)("button",{type:"button",className:"btn-close","data-dismiss":"modal","aria-label":"Close",onClick:r})]}),Object(n.jsx)("div",{className:"modal-body",children:a&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsx)("div",{children:Object(n.jsx)("input",{type:"checkbox",onChange:()=>o(t),checked:i.length===t.length})}),t.map((e=>Object(n.jsx)("div",{children:Object(n.jsxs)("label",{children:[Object(n.jsx)("input",{type:"checkbox",checked:i.includes(e.name),onChange:()=>c(e.name,t)})," ",e.displayAs]})},e.name)))]})})]})})})},i=a(9);var d={"column-text":{overflow:"hidden","max-width":"350px","text-overflow":"ellipsis","white-space":"nowrap","vertical-align":"middle"},"column-action":{composes:"border px-4 py-2 font-normal flex","white-space":"nowrap","align-items":"center"}},b=a(261),m=a(238);const u=Object(r.b)(d),j=e=>e.configuration.locale,h=e=>e.configuration.dateFormat;var x=e=>{const t=Object(i.d)(j),a=Object(i.d)(h),{rows:r,visibleColumns:l,hasActions:o,lastPrimaryKeyValue:s}=e,c=u(e);return Object(n.jsx)("tbody",{className:c["table-body"],children:r.map(((r,i)=>Object(n.jsxs)("tr",{className:s&&s===r.grocery_crud_extras.primaryKeyValue?c["animation-flash"]:void 0,children:[o&&l.length>0&&Object(n.jsx)("td",{className:c["column-action"],children:Object(n.jsx)(b.a,{...e,primaryKeyValue:r.grocery_crud_extras.primaryKeyValue})},"column__action"),l.map((e=>Object(n.jsx)("td",{children:Object(n.jsx)("div",{className:c["column-text"],children:Object(m.a)(r[e.name],e.dataType,{dateFormat:a,fieldName:e.name,fieldOptions:e.options,locale:t,permittedValues:e.permittedValues,primaryKeyValue:r.grocery_crud_extras.primaryKeyValue})})},e.name)))]},i)))})};var p={checkbox:{"margin-right":"10px"}};const g=Object(r.b)(p);var f=e=>{let{onChange:t,checked:a}=e;const r=g();return Object(n.jsx)("input",{type:"checkbox",className:r.checkbox,onChange:t,checked:a})},O=a(214),y=a(239),v=a.n(y),w=a(266);var N={footer:{display:"flex",padding:"5px",justifyContent:"space-between",alignItems:"center"},"footer-child":{display:"flex",alignItems:"center","& > div":{paddingRight:"5px"}},pagination:{composes:"relative z-0 inline-flex rounded-md shadow-sm -space-x-px",margin:"0"},"page-number":{composes:"relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium",borderRadius:"0",width:"100px"},"@keyframes spin":{"0%":{transform:"rotate(0deg)"},"100%":{transform:"rotate(360deg)"}},loader:{border:"4px solid #f3f3f3","border-radius":"50%","border-top":"4px solid #3498db",width:"16px",height:"16px","-webkit-animation":"$spin 2s linear infinite",animation:"$spin 2s linear infinite","margin-right":"10px"}};const k=Object(r.b)(N);var C=e=>{const t=k(e),{filteredTotalEntries:a,goToFirstPage:r,goToLastPage:l,goToNextPage:o,goToPreviousPage:s,lastPage:c,page:i,pageChange:d,totalEntries:b,perPage:m,perPageChange:u,forceSearch:j,pagingLoading:h,pagingOptions:x}=e;return Object(n.jsxs)("div",{className:t.footer,children:[Object(n.jsxs)("div",{className:t["footer-child"],children:[Object(n.jsx)("div",{children:"Show"}),Object(n.jsx)("div",{className:"floatL r5 l5 t3 per-page-container",children:Object(n.jsx)("select",{className:"form-control form-select",onChange:u,value:m,children:x.map((e=>Object(n.jsx)("option",{value:e,children:e},e)))})}),Object(n.jsx)("div",{children:"entries"})]}),Object(n.jsxs)("div",{className:t["footer-child"],children:[h&&Object(n.jsx)("div",{className:t.loader}),a&&!h?Object(n.jsx)("div",{children:Object(n.jsx)(w.a,{currentPage:i,totalEntries:b,perPage:m,filteredTotalEntries:a})}):null,Object(n.jsx)("div",{children:Object(n.jsxs)("div",{className:t.pagination,children:[Object(n.jsx)("button",{className:v()("relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium",{"disabled text-gray-500":1===i,"cursor-pointer hover:bg-gray-50":i>1}),onClick:()=>r(i,c),children:Object(n.jsx)(O.a,{icon:"step-backward"})}),Object(n.jsx)("button",{className:v()("relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium",{"disabled text-gray-500":1===i,"cursor-pointer hover:bg-gray-50":i>1}),onClick:()=>s(i,c),children:Object(n.jsx)(O.a,{icon:"chevron-left"})}),Object(n.jsx)("input",{type:"number",className:t["page-number"],value:i,onChange:e=>d(e,i,c),disabled:0===a,onKeyUp:e=>{"Enter"===e.key&&j()}}),Object(n.jsx)("button",{className:v()("relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium",{"disabled text-gray-500":i===c,"cursor-pointer hover:bg-gray-50":i<c}),onClick:()=>o(i,c),children:Object(n.jsx)(O.a,{icon:"chevron-right"})}),Object(n.jsx)("button",{className:v()("relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium",{"disabled text-gray-500":i===c,"cursor-pointer hover:bg-gray-50":i<c}),onClick:()=>l(i,c),children:Object(n.jsx)(O.a,{icon:"step-forward"})})]})})]})]})};var _={"search-column":{"min-width":"160px"},"table-th-with-ordering":{cursor:"pointer","&:hover":{background:"var(--gc-table-hover-background)"}},"actions-column-header":{"align-items":"center",display:"flex",height:"39px"},"with-ordering":{display:"flex","justify-content":"space-between","align-items":"center"},"input-text":{composes:"px-4 py-3 border border-gray-200 w-full"},"input-select":{composes:"px-4 py-3 border border-gray-200 w-full"},"input-select-search":{}},V=a(235),M=a(2);var S={"simple-button":{composes:"bg-white hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded border border-gray-700 shadow-sm text-gray-700 hover:bg-gray-100 focus:ring-4 focus:ring-gray-300","margin-right":"5px"},"buttons-group":{display:"flex"}};const F=Object(r.b)(S);var I=e=>{let{onClick:t,icon:a,label:r,link:l=!1,href:o}=e;const s=F();return l?Object(n.jsxs)("a",{className:s["simple-button"],onClick:t,href:o,children:[a&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsx)(O.a,{icon:a}),"\xa0"]}),r]}):Object(n.jsxs)("button",{className:s["simple-button"],onClick:t,children:[a&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsx)(O.a,{icon:a}),"\xa0"]}),r]})};const L=Object(r.b)(_),T=e=>{const{DatagridCheckbox:t,columnOrdering:a,columnSearch:r,columnSearchValues:o,columnSearchValuesDisplayAs:s,extendedSearchData:c,forceSearch:i,hasActions:d,onMultipleDeleteModalOpen:b,onSelectRowAllOrNone:u,options:{actionButtonHasText:j},selectRowsAllOrNoneChecked:h,selectedIds:x,sorting:p,sortingFor:g,visibleColumns:f,loadCssThirdParty:y,isMobileDevice:v}=e,w=L(e),N=0===c.length;return Object(n.jsxs)("thead",{children:[Object(n.jsxs)("tr",{children:[d&&f.length>0&&Object(n.jsx)("th",{children:l.a.t("actions")}),f.map((e=>Object(n.jsx)("th",{className:w["table-th-with-ordering"],onClick:()=>a({columnName:e.name,sorting:""===p||"desc"===p?"asc":"desc"}),children:Object(n.jsxs)("div",{className:w["with-ordering"],children:[Object(n.jsx)("span",{children:e.displayAs}),g===e.name?Object(n.jsx)(O.a,{icon:"asc"===p?"sort-amount-down-alt":"sort-amount-down"}):Object(n.jsx)(O.a,{icon:"sort"})]})},e.name)))]}),Object(n.jsxs)("tr",{children:[d&&f.length>0&&Object(n.jsx)("td",{children:Object(n.jsx)("div",{className:w["actions-column-header"],children:!v&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsx)(t,{onChange:u,checked:h}),x.length>0&&Object(n.jsx)(I,{onClick:b,icon:"trash",label:j?l.a.t("action_delete"):""})]})})}),f.map((e=>{const t=Object(V.l)(e.dataType);return Object(n.jsx)("td",{className:w["search-column"],children:N&&Object(n.jsx)(t,{className:w[Object(m.b)(e.dataType)],placeholder:l.a.t("quick_search"),permittedValues:e.permittedValues,loadCssThirdParty:y,onChange:t=>{r({columnName:e.name,searchValue:"object"===typeof t.target.value?t.target.value.key:t.target.value,searchValueDisplayAs:"object"===typeof t.target.value?t.target.value.displayAs:""}),!0===Object(V.n)(e.dataType)&&i()},onKeyUp:e=>{"Enter"===e.key&&i()},value:o[e.name]?o[e.name]:"",displayAs:s[e.name]?s[e.name]:"",fieldName:e.name})},e.name)}))]})]})};T.defaultProps={hasActions:!1,visibleColumns:[],options:{actionButtonHasText:!0}};var D=T;var A=e=>{let{title:t}=e;return Object(n.jsx)("div",{className:"gc-datagrid-title",children:t})};var E={"datagrid-tools":{position:"relative",padding:"10px","border-left":"1px solid var(--gc-border-separator-color)","border-right":"1px solid var(--gc-border-separator-color)",display:"flex","justify-content":"space-between"},"simple-button":{composes:"bg-white hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded border border-gray-700 shadow-sm text-gray-700 hover:bg-gray-100","margin-right":"5px"},"tools-container":{display:"flex"}},P=a(231),B=a(0),K=a(241);var z={"dropdown-menu":{composes:"origin-top-right absolute right-0 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 text-gray-700 font-bold focus:outline-none",left:"auto",top:"auto",display:e=>{let{opened:t}=e;return t?"block":"none"},opacity:e=>{let{opened:t}=e;return t?"1":"0"},right:e=>{let{direction:t}=e;return t===K.a.RIGHT?"0":"auto"},"margin-top":"38px","z-index":"100"},"dropdown-menu-container":{composes:"inline-flex","margin-left":e=>{let{leftSpace:t}=e;return t?"5px":"0"}},"dropdown-menu-button":{composes:"inline-flex justify-center bg-white hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded border border-gray-700 shadow-sm hover:bg-gray-100","align-items":"center"}};var R=e=>{let{buttons:t}=e;return Object(n.jsx)("ul",{className:"py-1 text-sm text-gray-700 dark:text-gray-20",children:t.map((e=>Object(n.jsx)("li",{children:Object(n.jsxs)("a",{href:e.url?e.url:"",target:e.newTab?"_blank":void 0,className:"text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100",rel:"noreferrer",onClick:e.onClick?t=>{t.preventDefault(),e.onClick&&e.onClick({primaryKeyValue:e.primaryKeyValue})}:void 0,children:[e.icon&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsx)(O.a,{icon:e.icon}),"\xa0\xa0"]}),e.text]},e.key)})))})};const W=Object(r.b)(z),$=e=>{let{buttons:t,buttonIcon:a,buttonLabel:r,direction:l,leftSpace:o}=e;const s=Object(M.useRef)(null),[c,i]=Object(M.useState)(!1);const d=W({opened:c,direction:l,leftSpace:o});return 0===t.length?null:Object(n.jsxs)("div",{className:d["dropdown-menu-container"],ref:s,children:[Object(n.jsxs)("button",{className:d["dropdown-menu-button"],onClick:function(){i(!c)},onBlur:function(e){setTimeout((()=>{i(!1)}),200)},children:[a&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsx)(O.a,{icon:a}),"\xa0"]}),r,"\xa0",Object(n.jsx)(O.a,{icon:"caret-down"})]}),Object(n.jsx)("div",{className:d["dropdown-menu"],role:"menu","aria-orientation":"vertical","aria-labelledby":"menu-button",tabindex:"-1",children:Object(n.jsx)("div",{className:"py-1",role:"none",children:Object(n.jsx)(R,{buttons:t})})})]})};$.defaultProps={buttons:[],buttonIcon:"",buttonLabel:"",direction:"left",leftSpace:!1};var H=$,U=a(24),G=a(10),q=a(26);const J=Object(r.b)(E);var Q=e=>{const{apiUrl:t,columnSearchValues:a,extendedSearchData:r,hasAdd:o,onAdd:s,onClearCache:c,onClearFiltering:d,onFilteringModalOpen:b,onOrderingReset:m,onRefresh:u,sorting:j,sortingFor:h,subject:x,visibleColumnsAsShortString:p}=e,g=J(e),f=Object(i.c)(),y=Object(i.d)((e=>e.configuration.hasSettings)),v=Object(i.d)((e=>e.configuration.hasPrint)),w=Object(i.d)((e=>e.configuration.hasFilters)),N=Object(i.d)((e=>e.configuration.hasColumnsButton)),k=Object(i.d)((e=>e.configuration.hasExportData)),C=Object(i.d)((e=>e.configuration.hasExportPdf)),_=Object(i.d)((e=>e.configuration.hasExportExcel)),V=o,M={apiUrl:t,columnSearchValues:a,sorting:j,sortingFor:h,visibleColumnsAsShortString:p,extendedSearchData:r};try{return V?Object(n.jsxs)("div",{className:g["datagrid-tools"],children:[o&&Object(n.jsx)(I,{link:!0,href:"/add",icon:"plus",label:Object(P.c)(B.a.ADD,x),onClick:e=>{e.preventDefault(),s()}}),Object(n.jsxs)("div",{children:[w&&Object(n.jsxs)("button",{className:r.length>0?g["success-button"]:g["simple-button"],onClick:b,children:[Object(n.jsx)(O.a,{icon:"filter"}),"\xa0",Object(P.b)(r.length)]}),w&&r.length>0&&Object(n.jsxs)("button",{className:g["danger-button"],onClick:d,children:[Object(n.jsx)(O.a,{icon:"eraser"}),"\xa0",l.a.t("filtering_remove_filters")]}),N&&Object(n.jsx)(I,{onClick:()=>(e=>e({type:G.a.MODAL_OPEN}))(f),label:l.a.t("columns"),icon:"list-alt"}),v&&Object(n.jsxs)("a",{className:g["simple-button"],href:Object(U.g)(M),rel:"noreferrer",target:"_blank",children:[Object(n.jsx)(O.a,{icon:"print"}),"\xa0",l.a.t("print")]}),k&&Object(n.jsx)(H,{buttons:[_&&{icon:"file-excel",text:"Excel",url:Object(U.a)(M),newTab:!0,key:"excel"},C&&{icon:"file-pdf",text:"PDF",url:Object(U.f)(M),newTab:!0,key:"pdf"}],buttonLabel:l.a.t("export_to_file"),buttonIcon:"download",leftSpace:!0}),y&&Object(n.jsx)(H,{leftSpace:!0,direction:K.a.RIGHT,buttons:[{icon:"broom",text:l.a.t("clear_cache"),onClick:c,key:"clear_cache"},{icon:"eraser",text:l.a.t("clear_filtering"),onClick:d,key:"clear_filtering"},{icon:"unlink",text:l.a.t("reset_ordering"),onClick:m,key:"reset_ordering"},{icon:"sync-alt",text:l.a.t("refresh"),onClick:u,key:"refresh"}],buttonLabel:l.a.t("settings"),buttonIcon:"cog"})]})]}):null}catch(S){return console.log(S),Object(n.jsx)(q.a,{})}};var X={wrapper:{composes:"border table-auto",width:"100%"}};const Y=Object(r.b)(X);var Z=e=>{const t=Y(e);return Object(n.jsx)("table",{className:t.wrapper,children:e.children})};var ee={"@keyframes fadeIn":{"0%":{opacity:"0.1"},"66%":{opacity:" 0.5"},"100%":{opacity:"1"}},"@keyframes shine":{to:{"background-position":"100% 0"}},"form-dialog":{composes:"hidden overflow-y-auto overflow-x-hidden fixed right-0 left-0 top-4 z-50 justify-center items-center h-modal md:h-full md:inset-0",display:e=>{let{formModalOpen:t}=e;return t?"block":"none"},opacity:"0","z-index":"10",animation:e=>{let{formModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"primary-button":{composes:"text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"},"secondary-button":{composes:"text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600"},"form-fields":{composes:"w-full md:w-auto overflow-auto",position:"relative","max-height":"calc(100vh - 240px)"},"skeleton-loader":{width:"100%",height:"15px",border:"1px solid rgb(239,239,239)","border-radius":"4px",display:"block","background-repeat":"repeat-y","background-size":"50px 500px","background-position":"0 0",background:"linear-gradient(to right, rgba(239, 239, 239, 0), rgba(239, 239, 239, 0.5) 50%, rgba(239, 239, 239, 0) 80%),rgb(255,255,255)",animation:"$shine 2s infinite","animation-delay":"0.3s"},"modal-delete-one":{composes:"overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full",display:e=>{let{deleteOneModalOpen:t}=e;return t?"block":"none"}},"modal-delete-multiple":{composes:"overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full",display:e=>{let{deleteMultipleModalOpen:t}=e;return t?"block":"none"}},"modal-header":{composes:"flex justify-between items-start p-4 rounded-t border-b dark:border-gray-600"},"modal-header-label":{composes:"text-xl font-semibold text-gray-900 dark:text-white"},"modal-dialog":{composes:"relative p-4 w-full max-w-2xl h-full md:h-auto"},"modal-content":{composes:"relative bg-white rounded-lg shadow dark:bg-gray-700"}};var te={"mini-grid":{composes:"table table-bordered table-hover","margin-bottom":"0px"},"scrolling-wrapper":{width:"100%",position:"relative","overflow-x":"auto"},"column-text":{"&>span":{"text-overflow":"ellipsis","white-space":"nowrap",overflow:"hidden"},"align-items":"center","max-width":"350px","min-width":"0","vertical-align":"middle",display:"flex",height:"38px"},"mini-grid-body":{".table &":{"border-top":"none"}}},ae=a(7);const re=Object(r.b)(te),le=e=>e.configuration.locale,oe=e=>e.configuration.dateFormat;var ne=e=>{let{visibleColumns:t,rows:a}=e;const r=re(),l=Object(i.d)(le),o=Object(i.d)(oe);return Object(n.jsx)("div",{className:r["scrolling-wrapper"],children:Object(n.jsxs)("table",{className:r["mini-grid"],children:[Object(n.jsx)("thead",{children:Object(n.jsx)("tr",{children:t.map((e=>e.dataType===ae.a.INVISIBLE?null:Object(n.jsx)("th",{children:e.displayAs},e.name)))})}),Object(n.jsx)("tbody",{className:r["mini-grid-body"],children:a.map(((e,a)=>Object(n.jsx)("tr",{children:t.map((t=>t.dataType===ae.a.INVISIBLE?null:Object(n.jsx)("td",{children:Object(n.jsx)("div",{className:r["column-text"],children:Object(m.a)(e[t.name],t.dataType,{permittedValues:t.permittedValues,fieldName:t.name,locale:l,dateFormat:o,primaryKeyValue:e.grocery_crud_extras.primaryKeyValue})})},t.name)))},a)))})]})})};const se=Object(r.b)(ee);var ce=e=>{const{deleteMultipleModalOpen:t,deleteMultipleModalClose:a,deleteMultiple:r,selectedIds:o,visibleColumnsWithDetails:s,rows:c}=e,i=se(e);return Object(n.jsx)("div",{className:i["modal-delete-multiple"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:Object(n.jsx)("div",{className:i["modal-dialog"],children:Object(n.jsxs)("div",{className:i["modal-content"],children:[Object(n.jsxs)("div",{className:i["modal-header"],children:[Object(n.jsx)("h3",{className:i["modal-header-label"],children:l.a.t("action_delete")}),Object(n.jsx)("button",{type:"button",className:"text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white",onClick:a,children:Object(n.jsx)("svg",{className:"w-5 h-5",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg",children:Object(n.jsx)("path",{fillRule:"evenodd",d:"M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z",clipRule:"evenodd"})})})]}),Object(n.jsxs)("div",{className:"modal-body",children:[Object(n.jsx)("p",{children:Object(P.a)(o.length)}),t&&Object(n.jsx)(ne,{visibleColumns:s,rows:c.filter((e=>o.includes(e.grocery_crud_extras.primaryKeyValue)))})]}),t&&Object(n.jsxs)("div",{className:"flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600",children:[Object(n.jsx)(I,{label:l.a.t("cancel"),onClick:a}),o.length>0&&Object(n.jsx)("button",{type:"button",className:"bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 text-white dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 font-bold py-2 px-4 rounded border border-red-500 shadow-sm",onClick:r,children:l.a.t("action_delete")})]})]})})})},ie=a(286);const de=Object(r.b)(ee);var be=e=>{let{deleteOneModalOpen:t,deleteOneModalClose:a,deleteOne:r,visibleColumnsWithDetails:o,primaryKeyValue:s,rows:c}=e;const i=de({deleteOneModalOpen:t});return Object(n.jsx)("div",{className:i["modal-delete-one"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:Object(n.jsx)("div",{className:i["modal-dialog"],children:Object(n.jsxs)("div",{className:i["modal-content"],children:[Object(n.jsxs)("div",{className:i["modal-header"],children:[Object(n.jsx)("h3",{className:i["modal-header-label"],children:l.a.t("action_delete")}),Object(n.jsx)("button",{type:"button",className:"text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white",onClick:a,children:Object(n.jsx)("svg",{className:"w-5 h-5",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg",children:Object(n.jsx)("path",{fillRule:"evenodd",d:"M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z",clipRule:"evenodd"})})})]}),Object(n.jsxs)("div",{className:"p-6 space-y-6",children:[Object(n.jsx)("p",{children:l.a.t("confirm_delete")}),t&&Object(n.jsx)(ie.a,{visibleColumns:o,rows:c.filter((e=>e.grocery_crud_extras.primaryKeyValue===s))})]}),t&&Object(n.jsxs)("div",{className:"flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600",children:[Object(n.jsx)(I,{label:l.a.t("cancel"),onClick:a}),Object(n.jsx)("button",{type:"button",className:"bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 text-white dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 font-bold py-2 px-4 rounded border border-red-500 shadow-sm",onClick:r,children:l.a.t("action_delete")})]})]})})})};var me={"error-dialog":{composes:"modal","z-index":"1100",display:e=>{let{showError:t}=e;return t?"block":"none"}},"error-details":{width:"100%",height:"200px"}};const ue=Object(r.b)(me),je=e=>{let{closeModal:t,showError:a,details:r,message:o}=e;const s=ue({showError:a});return Object(n.jsx)("div",{className:s["error-dialog"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:Object(n.jsx)("div",{className:"modal-dialog modal-xl",role:"document",children:Object(n.jsxs)("div",{className:"modal-content",children:[Object(n.jsxs)("div",{className:"modal-header",children:[Object(n.jsx)("h5",{className:"modal-title",children:l.a.t("error_generic_title")}),Object(n.jsx)("button",{type:"button",className:"btn-close","data-dismiss":"modal","aria-label":"Close",onClick:t})]}),Object(n.jsxs)("div",{className:"modal-body",children:[Object(n.jsxs)("div",{children:["Message: ",o]}),Object(n.jsx)("div",{children:"Error:"}),Object(n.jsx)("div",{children:Object(n.jsx)("textarea",{defaultValue:r||"",className:s["error-details"]})})]}),Object(n.jsx)("div",{className:"modal-footer",children:Object(n.jsx)("button",{type:"button",className:"btn btn-default btn-outline-dark","data-dismiss":"modal",onClick:t,children:l.a.t("close_modal")})})]})})})};je.defaultProps={formFields:[]};var he=je;var xe={"@keyframes fadeIn":{"0%":{opacity:"0.1"},"66%":{opacity:" 0.5"},"100%":{opacity:"1"}},"filtering-modal":{composes:"modal",display:e=>{let{filteringModalOpen:t}=e;return t?"block":"none"},opacity:"0",animation:e=>{let{filteringModalOpen:t}=e;return t?"$fadeIn 0.15s ease 0s normal forwards 1":"none"}},"form-fields":{"overflow-x":"hidden","overflow-y":"auto",position:"relative","max-height":"calc(100vh - 240px)"}},pe=a(230);var ge={"filtering-row":{composes:"row","padding-top":"10px","padding-bottom":"10px"}},fe=a(61),Oe=a(60),ye=a(267);const ve=Object(r.b)(ge);var we=e=>{const{onFilteringModalClose:t,columns:a,onSubmit:r,extendedSearchOperator:o,extendedSearchData:s}=e,{control:c,handleSubmit:i,getValues:d}=Object(pe.d)({defaultValues:{basic_operator:o||"AND",extended_search:s.length>0?s:[{name:a[0].name,filter:"contains",value:""}]}}),{fields:b,append:m,remove:u,insert:j}=Object(pe.c)({control:c,name:"extended_search"}),h=ve(e),x=b.length;return Object(n.jsxs)("form",{className:"form-horizontal",onSubmit:i((e=>{if(r){let t=[];e.extended_search.forEach((e=>{null!==e&&t.push(e)})),r(Object(fe.a)({...e,extended_search:t}))}}),((e,t)=>console.log(e,t))),children:[Object(n.jsxs)("div",{className:"modal-header",children:[Object(n.jsx)("h5",{className:"modal-title",children:l.a.t("filtering_filter_text")}),Object(n.jsx)("button",{type:"button",className:"btn-close","data-dismiss":"modal","aria-label":"Close",onClick:t})]}),Object(n.jsxs)("div",{className:"modal-body",children:[Object(n.jsxs)("div",{className:"row",children:[Object(n.jsxs)("label",{className:"col-md-3 control-label",children:[l.a.t("filtering_operator")," :"]}),Object(n.jsx)("div",{className:"col-md-3",children:Object(n.jsx)(pe.a,{render:e=>{let{field:{onChange:t,onBlur:a,value:r}}=e;return Object(n.jsxs)("select",{name:"basic_operator",onChange:t,className:"form-control form-select",defaultValue:r,children:[Object(n.jsx)("option",{value:"AND",children:l.a.t("filtering_and_statement")}),Object(n.jsx)("option",{value:"OR",children:l.a.t("filtering_or_statement")})]})},name:"basic_operator",control:c,defaultValue:"AND"})})]}),Object(n.jsx)("div",{children:b.map(((e,t)=>{const r=d("extended_search[".concat(t,"]")).name,l=a.find((e=>e.name===r));return Object(n.jsxs)("div",{className:h["filtering-row"],children:[Object(n.jsx)("div",{className:"col-md-1",children:Object(n.jsx)("button",{className:"btn btn-outline-dark btn-block",type:"button",onClick:()=>u(t),disabled:1===x,children:Object(n.jsx)(O.a,{icon:"trash"})})}),Object(n.jsx)("div",{className:"col-md-3",children:Object(n.jsx)(pe.a,{render:e=>{let{field:{onChange:r,onBlur:l,value:o}}=e;return Object(n.jsx)("select",{onChange:e=>{const l={...d("extended_search[".concat(t,"]"))},o=e.target.value,n=a.find((e=>e.name===l.name)),s=a.find((e=>e.name===o));Object(Oe.a)(n.dataType,s.dataType)&&(u(t),j(t,{name:o,filter:Object(Oe.d)(s.dataType),value:""})),r(e)},className:"form-control form-select",name:"extended_search[".concat(t,"].name"),value:o,children:a.map((e=>e.isSearchable&&Object(n.jsx)("option",{value:e.name,children:e.displayAs},e.name)))})},name:"extended_search[".concat(t,"].name"),control:c,defaultValue:e.firstName})}),Object(n.jsx)("div",{className:"col-md-3",children:Object(n.jsx)(pe.a,{render:e=>{let{field:{onChange:a,value:r}}=e;return Object(n.jsx)(ye.a,{onChange:e=>{const r={...d("extended_search[".concat(t,"]"))},l=e.target.value;Object(Oe.b)(r.filter,l)&&(u(t),j(t,{name:r.name,filter:l,value:Oe.c[l]?null:""})),a(e)},className:"form-control form-select",name:"extended_search[".concat(t,"].filter"),value:r,dataType:l.dataType})},name:"extended_search[".concat(t,"].filter"),control:c,defaultValue:e.filter})}),Object(n.jsx)("div",{className:"col-md-5",children:Object(n.jsx)(pe.a,{render:e=>{let{field:{onChange:a,onBlur:r,value:o}}=e;if(null===o)return null;const{dataType:s,permittedValues:c}=l,i=Object(V.l)(s);return Object(n.jsx)(i,{onChange:a,onBlur:r,className:h[Object(V.j)(s)],name:"extended_search[".concat(t,"].value"),value:o,required:!0,permittedValues:c})},name:"extended_search[".concat(t,"].value"),control:c,defaultValue:e.value})})]},e.id)}))}),Object(n.jsx)("div",{className:h["filtering-row"],children:Object(n.jsx)("div",{className:"col-md-12",children:Object(n.jsxs)("button",{type:"button",className:"btn btn-default btn-outline-dark",onClick:()=>{m({name:a[0].name,filter:"contains",value:""})},children:[Object(n.jsx)(O.a,{icon:"plus"}),"\xa0",l.a.t("filtering_add_more")]})})})]}),Object(n.jsxs)("div",{className:"modal-footer",children:[Object(n.jsx)("button",{type:"button",className:"btn btn-default btn-outline-dark","data-dismiss":"modal",onClick:t,children:l.a.t("filtering_cancel")}),Object(n.jsx)("button",{type:"submit",className:"btn btn-success delete-multiple-confirmation-button",children:l.a.t("filtering_filter_text")})]})]})};const Ne=Object(r.b)(xe);var ke=e=>{const{filteringModalOpen:t,onFilteringSubmit:a,columns:r}=e,l=Ne(e);return Object(n.jsx)("div",{className:l["filtering-modal"],tabIndex:"-1",role:"dialog","aria-labelledby":"myModalLabel",children:t&&Object(n.jsx)(we,{...e,fields:r,onSubmit:a})})},Ce=a(307);var _e=e=>{let{title:t,onClose:a}=e;return Object(n.jsxs)("div",{className:"flex justify-between items-start p-5 rounded-t border-b dark:border-gray-600",children:[Object(n.jsx)("h3",{className:"text-xl font-semibold text-gray-900 lg:text-2xl dark:text-white",children:t}),Object(n.jsx)("button",{onClick:a,type:"button",className:"text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white","data-modal-toggle":"defaultModal",children:Object(n.jsx)("svg",{className:"w-5 h-5",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg",children:Object(n.jsx)("path",{"fill-rule":"evenodd",d:"M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z","clip-rule":"evenodd"})})})]})},Ve=a(21);const Me=Object(r.b)(ee);var Se=e=>{let{modalLoading:t,formModalClose:a,formFields:r,formState:o,onFormSubmit:s,readOnly:c,publishEvents:i}=e;const{handleSubmit:d,control:b,setValue:m}=Object(pe.d)();Object(M.useEffect)((()=>{const e=e=>{let{detail:t}=e;m(t.fieldName,t.fieldValue)};return i&&Object(Ve.c)(Ve.a,e),()=>{i&&Object(Ve.d)(Ve.a,e)}}),[m,i]);const u=Me({modalLoading:t,formModalClose:a,formFields:r});return Object(n.jsx)(n.Fragment,{children:Object(n.jsx)("form",{onSubmit:d((e=>{s({formState:o,data:e})}),((e,t)=>console.log(e,t))),children:Object(n.jsx)("div",{className:"relative px-4 w-full h-full md:h-auto",children:Object(n.jsxs)("div",{className:"relative bg-white rounded-lg shadow dark:bg-gray-700",children:[Object(n.jsx)(_e,{title:Object(P.c)(o),onClose:a}),Object(n.jsx)("div",{className:u["form-fields"],children:t?"Loading...":r.map((e=>Object(n.jsxs)("div",{className:"flex px-4 py-3",children:[Object(n.jsx)("label",{className:"w-1/4",htmlFor:"gc-form-".concat(e.fieldName),children:e.displayAs}),Object(n.jsx)("div",{className:"w-3/4",children:Object(n.jsx)(Ce.a,{className:"px-4 py-3 border border-gray-200 w-full",name:e.fieldName,value:e.fieldValue,control:b,id:"form-".concat(e.fieldName)})})]},e.fieldName)))}),Object(n.jsxs)("div",{className:"flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600",children:[!c&&Object(n.jsxs)(n.Fragment,{children:[Object(n.jsxs)("label",{children:[Object(n.jsx)("input",{type:"checkbox"}),l.a.t("close_modal_on_save")]})," ","\xa0 \xa0"]}),Object(n.jsx)(I,{onClick:a,label:l.a.t("close_modal")}),!c&&Object(n.jsx)("button",{type:"submit",className:u["primary-button"],children:l.a.t("modal_save")})]})]})})})})};const Fe=Object(r.b)(ee),Ie=e=>{const{modalLoading:t,formModalClose:a,formFields:r,formState:o,onFormSubmit:s,formIsReadOnly:c}=e,i=Fe(e);return Object(n.jsx)("div",{className:i["form-dialog"],tabIndex:"-1",children:t?Object(n.jsx)(n.Fragment,{children:Object(n.jsx)("div",{className:"relative px-4 w-full h-full md:h-auto",children:Object(n.jsxs)("div",{className:"relative bg-white rounded-lg shadow dark:bg-gray-700",children:[Object(n.jsx)(_e,{title:Object(P.c)(o),onClose:a}),Object(n.jsx)("div",{className:"p-6 space-y-6",children:Object(n.jsx)("div",{className:i["skeleton-loader"]})}),Object(n.jsx)("div",{className:"flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600",children:Object(n.jsx)("button",{type:"button",onClick:a,className:i["secondary-button"],children:l.a.t("close_modal")})})]})})}):Object(n.jsx)(Se,{formFields:r,formModalClose:a,modalLoading:t,formState:o,onFormSubmit:s,readOnly:c})})};Ie.defaultProps={formFields:[]};var Le=Ie;const Te=Object(r.b)(S),De=e=>{const{buttons:t,maxButtons:a}=e,r=Te(e);return Object(n.jsxs)("div",{className:r["buttons-group"],children:[t.filter(((e,t)=>t<a-1)).map((e=>Object(n.jsx)(I,{onClick:t=>{t.preventDefault(),e.onClick&&e.onClick({primaryKeyValue:e.primaryKeyValue})},link:!0,href:e.url?e.url:"",icon:e.icon,label:e.text},e.key))),Object(n.jsx)(H,{buttons:t.filter(((e,t)=>t>=a-1)),buttonLabel:1===a?l.a.t("actions"):l.a.t("more")})]})};De.defaultProps={buttons:[],maxButtons:2};const Ae={ColumnsModal:c,DatagridBody:x,DatagridCheckbox:f,DatagridFooter:C,DatagridHeader:D,DatagridTitle:A,DatagridTools:Q,DatagridWrapper:Z,DeleteMultipleModal:ce,DeleteSingleModal:be,ErrorDialog:he,FilteringModal:ke,FormDialog:Le,GroupButtons:De};t.default=Ae},252:function(e,t,a){"use strict";a.d(t,"a",(function(){return r}));const r=e=>e.columnWidth},285:function(e,t,a){"use strict";const r={display:"block",position:"absolute",right:"-13px",height:"39px",width:"10px",cursor:"col-resize",opacity:"0.8","z-index":"10","&:hover":{background:"var(--gc-emphasis-background-color)",cursor:"col-resize"},"&:focus, &:active":{background:"transparent",cursor:"col-resize"}},l={"mini-grid":{composes:"table table-bordered table-hover","margin-bottom":"0px"},"interactive-grid":{composes:"table table-bordered",width:e=>{let{columnWidth:t}=e;return"object"===typeof t&&Object.keys(t).length>0?"auto":"100%"}},"scrolling-wrapper":{composes:"gc-mini-grid-scrolling-wrapper",width:"100%",position:"relative","overflow-x":"auto"},"column-text":{"&>span":{"text-overflow":"ellipsis","white-space":"nowrap",overflow:"hidden"},"align-items":"center","max-width":"350px","min-width":"0","vertical-align":"middle",display:"flex",height:"38px"},"mini-grid-body":{".table &":{"border-top":"none"}},description:{display:"flex","align-items":"center","justify-content":"space-between","margin-bottom":"10px"},"width-changing":{...r},"width-changing-last":{...r,right:"-8px"},"header-column":{display:"flex","justify-content":"space-between","align-items":"center",position:"relative","min-width":"100px","white-space":"nowrap","padding-right":"10px"}};t.a=l},286:function(e,t,a){"use strict";var r=a(31),l=a(285),o=a(238),n=a(9),s=a(7),c=a(252),i=a(1);const d=Object(r.b)(l.a),b=e=>e.configuration.locale,m=e=>e.configuration.dateFormat;t.a=e=>{let{visibleColumns:t,rows:a}=e;const r=Object(n.d)(b),l=Object(n.d)(m),u=Object(n.d)(c.a),j=d({columnWidth:u});return Object(i.jsx)("div",{className:j["scrolling-wrapper"],children:Object(i.jsxs)("table",{className:j["mini-grid"],children:[Object(i.jsx)("thead",{children:Object(i.jsx)("tr",{children:t.map((e=>e.dataType===s.a.INVISIBLE?null:Object(i.jsx)("th",{children:e.displayAs},e.name)))})}),Object(i.jsx)("tbody",{className:j["mini-grid-body"],children:a.map(((e,a)=>Object(i.jsx)("tr",{children:t.map((t=>t.dataType===s.a.INVISIBLE?null:Object(i.jsx)("td",{children:Object(i.jsx)("div",{className:j["column-text"],children:Object(o.a)(e[t.name],t.dataType,{permittedValues:t.permittedValues,fieldName:t.name,locale:r,dateFormat:l,primaryKeyValue:e.grocery_crud_extras.primaryKeyValue})})},t.name)))},a)))})]})})}}}]);
//# sourceMappingURL=102.90b99ca6.chunk.js.map