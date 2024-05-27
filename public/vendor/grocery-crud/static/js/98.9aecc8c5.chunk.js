(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[98],{203:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t){if(t.length<e)throw new TypeError(e+" argument"+(e>1?"s":"")+" required, but only "+t.length+" present")},e.exports=t.default},205:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},n=t.width?String(t.width):e.defaultWidth;return e.formats[n]||e.formats[e.defaultWidth]}},e.exports=t.default},206:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,n){var i;if("formatting"===(null!==n&&void 0!==n&&n.context?String(n.context):"standalone")&&e.formattingValues){var a=e.defaultFormattingWidth||e.defaultWidth,u=null!==n&&void 0!==n&&n.width?String(n.width):a;i=e.formattingValues[u]||e.formattingValues[a]}else{var r=e.defaultWidth,o=null!==n&&void 0!==n&&n.width?String(n.width):e.defaultWidth;i=e.values[o]||e.values[r]}return i[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},207:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=n.width,a=i&&e.matchPatterns[i]||e.matchPatterns[e.defaultMatchWidth],u=t.match(a);if(!u)return null;var r,o=u[0],l=i&&e.parsePatterns[i]||e.parsePatterns[e.defaultParseWidth],d=Array.isArray(l)?function(e,t){for(var n=0;n<e.length;n++)if(t(e[n]))return n;return}(l,(function(e){return e.test(o)})):function(e,t){for(var n in e)if(e.hasOwnProperty(n)&&t(e[n]))return n;return}(l,(function(e){return e.test(o)}));return r=e.valueCallback?e.valueCallback(d):d,{value:r=n.valueCallback?n.valueCallback(r):r,rest:t.slice(o.length)}}},e.exports=t.default},208:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=t.match(e.matchPattern);if(!i)return null;var a=i[0],u=t.match(e.parsePattern);if(!u)return null;var r=e.valueCallback?e.valueCallback(u[0]):u[0];return{value:r=n.valueCallback?n.valueCallback(r):r,rest:t.slice(a.length)}}},e.exports=t.default},209:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){(0,u.default)(1,arguments);var t=Object.prototype.toString.call(e);return e instanceof Date||"object"===(0,a.default)(e)&&"[object Date]"===t?new Date(e.getTime()):"number"===typeof e||"[object Number]"===t?new Date(e):("string"!==typeof e&&"[object String]"!==t||"undefined"===typeof console||(console.warn("Starting with v2.0.0-beta.1 date-fns doesn't accept strings as date arguments. Please use `parseISO` to parse strings. See: https://github.com/date-fns/date-fns/blob/master/docs/upgradeGuide.md#string-arguments"),console.warn((new Error).stack)),new Date(NaN))};var a=i(n(229)),u=i(n(204));e.exports=t.default},210:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){if(null===e||!0===e||!1===e)return NaN;var t=Number(e);if(isNaN(t))return t;return t<0?Math.ceil(t):Math.floor(t)},e.exports=t.default},213:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.getDefaultOptions=function(){return i},t.setDefaultOptions=function(e){i=e};var i={}},228:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t){var n,i,l,d,s,c,f,v;(0,u.default)(1,arguments);var g=(0,o.getDefaultOptions)(),p=(0,r.default)(null!==(n=null!==(i=null!==(l=null!==(d=null===t||void 0===t?void 0:t.weekStartsOn)&&void 0!==d?d:null===t||void 0===t||null===(s=t.locale)||void 0===s||null===(c=s.options)||void 0===c?void 0:c.weekStartsOn)&&void 0!==l?l:g.weekStartsOn)&&void 0!==i?i:null===(f=g.locale)||void 0===f||null===(v=f.options)||void 0===v?void 0:v.weekStartsOn)&&void 0!==n?n:0);if(!(p>=0&&p<=6))throw new RangeError("weekStartsOn must be between 0 and 6 inclusively");var m=(0,a.default)(e),h=m.getUTCDay(),b=(h<p?7:0)+h-p;return m.setUTCDate(m.getUTCDate()-b),m.setUTCHours(0,0,0,0),m};var a=i(n(209)),u=i(n(204)),r=i(n(210)),o=n(213);e.exports=t.default},230:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t,n){(0,a.default)(2,arguments);var i=(0,u.default)(e,n),r=(0,u.default)(t,n);return i.getTime()===r.getTime()};var a=i(n(204)),u=i(n(228));e.exports=t.default},961:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(962)),u=i(n(963)),r=i(n(964)),o=i(n(965)),l=i(n(966)),d={code:"kk",formatDistance:a.default,formatLong:u.default,formatRelative:r.default,localize:o.default,match:l.default,options:{weekStartsOn:1,firstWeekContainsDate:1}};t.default=d,e.exports=t.default},962:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i={lessThanXSeconds:{regular:{one:"1 \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u0430\u0437",singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u0430\u0437",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u0430\u0437",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u0430\u0437"},future:{one:"\u0431\u0456\u0440 \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},xSeconds:{regular:{singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434"},past:{singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434 \u0431\u04b1\u0440\u044b\u043d",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434 \u0431\u04b1\u0440\u044b\u043d",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434 \u0431\u04b1\u0440\u044b\u043d"},future:{singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},halfAMinute:function(e){return null!==e&&void 0!==e&&e.addSuffix?e.comparison&&e.comparison>0?"\u0436\u0430\u0440\u0442\u044b \u043c\u0438\u043d\u0443\u0442 \u0456\u0448\u0456\u043d\u0434\u0435":"\u0436\u0430\u0440\u0442\u044b \u043c\u0438\u043d\u0443\u0442 \u0431\u04b1\u0440\u044b\u043d":"\u0436\u0430\u0440\u0442\u044b \u043c\u0438\u043d\u0443\u0442"},lessThanXMinutes:{regular:{one:"1 \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u0430\u0437",singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u0430\u0437",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u0430\u0437",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u0430\u0437"},future:{one:"\u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u043c ",singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u043c",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u043c",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u043c"}},xMinutes:{regular:{singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442"},past:{singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442 \u0431\u04b1\u0440\u044b\u043d",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442 \u0431\u04b1\u0440\u044b\u043d",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442 \u0431\u04b1\u0440\u044b\u043d"},future:{singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},aboutXHours:{regular:{singularNominative:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0441\u0430\u0493\u0430\u0442",singularGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0441\u0430\u0493\u0430\u0442",pluralGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0441\u0430\u0493\u0430\u0442"},future:{singularNominative:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0441\u0430\u0493\u0430\u0442\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0441\u0430\u0493\u0430\u0442\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0441\u0430\u0493\u0430\u0442\u0442\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},xHours:{regular:{singularNominative:"{{count}} \u0441\u0430\u0493\u0430\u0442",singularGenitive:"{{count}} \u0441\u0430\u0493\u0430\u0442",pluralGenitive:"{{count}} \u0441\u0430\u0493\u0430\u0442"}},xDays:{regular:{singularNominative:"{{count}} \u043a\u04af\u043d",singularGenitive:"{{count}} \u043a\u04af\u043d",pluralGenitive:"{{count}} \u043a\u04af\u043d"},future:{singularNominative:"{{count}} \u043a\u04af\u043d\u043d\u0435\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"{{count}} \u043a\u04af\u043d\u043d\u0435\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"{{count}} \u043a\u04af\u043d\u043d\u0435\u043d \u043a\u0435\u0439\u0456\u043d"}},aboutXWeeks:{type:"weeks",one:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d 1 \u0430\u043f\u0442\u0430",other:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u043f\u0442\u0430"},xWeeks:{type:"weeks",one:"1 \u0430\u043f\u0442\u0430",other:"{{count}} \u0430\u043f\u0442\u0430"},aboutXMonths:{regular:{singularNominative:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u0439",singularGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u0439",pluralGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u0439"},future:{singularNominative:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u0439\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u0439\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0430\u0439\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},xMonths:{regular:{singularNominative:"{{count}} \u0430\u0439",singularGenitive:"{{count}} \u0430\u0439",pluralGenitive:"{{count}} \u0430\u0439"}},aboutXYears:{regular:{singularNominative:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0436\u044b\u043b",singularGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0436\u044b\u043b",pluralGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0436\u044b\u043b"},future:{singularNominative:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"\u0448\u0430\u043c\u0430\u043c\u0435\u043d {{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},xYears:{regular:{singularNominative:"{{count}} \u0436\u044b\u043b",singularGenitive:"{{count}} \u0436\u044b\u043b",pluralGenitive:"{{count}} \u0436\u044b\u043b"},future:{singularNominative:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}},overXYears:{regular:{singularNominative:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u0430\u0441\u0442\u0430\u043c",singularGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u0430\u0441\u0442\u0430\u043c",pluralGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u0430\u0441\u0442\u0430\u043c"},future:{singularNominative:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u0430\u0441\u0442\u0430\u043c",singularGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u0430\u0441\u0442\u0430\u043c",pluralGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u0430\u0441\u0442\u0430\u043c"}},almostXYears:{regular:{singularNominative:"{{count}} \u0436\u044b\u043b\u0493\u0430 \u0436\u0430\u049b\u044b\u043d",singularGenitive:"{{count}} \u0436\u044b\u043b\u0493\u0430 \u0436\u0430\u049b\u044b\u043d",pluralGenitive:"{{count}} \u0436\u044b\u043b\u0493\u0430 \u0436\u0430\u049b\u044b\u043d"},future:{singularNominative:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",singularGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d",pluralGenitive:"{{count}} \u0436\u044b\u043b\u0434\u0430\u043d \u043a\u0435\u0439\u0456\u043d"}}};function a(e,t){if(e.one&&1===t)return e.one;var n=t%10,i=t%100;return 1===n&&11!==i?e.singularNominative.replace("{{count}}",String(t)):n>=2&&n<=4&&(i<10||i>20)?e.singularGenitive.replace("{{count}}",String(t)):e.pluralGenitive.replace("{{count}}",String(t))}var u=function(e,t,n){var u=i[e];return"function"===typeof u?u(n):"weeks"===u.type?1===t?u.one:u.other.replace("{{count}}",String(t)):null!==n&&void 0!==n&&n.addSuffix?n.comparison&&n.comparison>0?u.future?a(u.future,t):a(u.regular,t)+" \u043a\u0435\u0439\u0456\u043d":u.past?a(u.past,t):a(u.regular,t)+" \u0431\u04b1\u0440\u044b\u043d":a(u.regular,t)};t.default=u,e.exports=t.default},963:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(205)),u={date:(0,a.default)({formats:{full:"EEEE, do MMMM y '\u0436.'",long:"do MMMM y '\u0436.'",medium:"d MMM y '\u0436.'",short:"dd.MM.yyyy"},defaultWidth:"full"}),time:(0,a.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,a.default)({formats:{any:"{{date}}, {{time}}"},defaultWidth:"any"})};t.default=u,e.exports=t.default},964:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(230)),u=["\u0436\u0435\u043a\u0441\u0435\u043d\u0431\u0456\u0434\u0435","\u0434\u04af\u0439\u0441\u0435\u043d\u0431\u0456\u0434\u0435","\u0441\u0435\u0439\u0441\u0435\u043d\u0431\u0456\u0434\u0435","\u0441\u04d9\u0440\u0441\u0435\u043d\u0431\u0456\u0434\u0435","\u0431\u0435\u0439\u0441\u0435\u043d\u0431\u0456\u0434\u0435","\u0436\u04b1\u043c\u0430\u0434\u0430","\u0441\u0435\u043d\u0431\u0456\u0434\u0435"];function r(e){return"'"+u[e]+" \u0441\u0430\u0493\u0430\u0442' p'-\u0434\u0435'"}var o={lastWeek:function(e,t,n){var i=e.getUTCDay();return(0,a.default)(e,t,n)?r(i):function(e){return"'\u04e9\u0442\u043a\u0435\u043d "+u[e]+" \u0441\u0430\u0493\u0430\u0442' p'-\u0434\u0435'"}(i)},yesterday:"'\u043a\u0435\u0448\u0435 \u0441\u0430\u0493\u0430\u0442' p'-\u0434\u0435'",today:"'\u0431\u04af\u0433\u0456\u043d \u0441\u0430\u0493\u0430\u0442' p'-\u0434\u0435'",tomorrow:"'\u0435\u0440\u0442\u0435\u04a3 \u0441\u0430\u0493\u0430\u0442' p'-\u0434\u0435'",nextWeek:function(e,t,n){var i=e.getUTCDay();return(0,a.default)(e,t,n)?r(i):function(e){return"'\u043a\u0435\u043b\u0435\u0441\u0456 "+u[e]+" \u0441\u0430\u0493\u0430\u0442' p'-\u0434\u0435'"}(i)},other:"P"},l=function(e,t,n,i){var a=o[e];return"function"===typeof a?a(t,n,i):a};t.default=l,e.exports=t.default},965:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(206)),u={0:"-\u0448\u0456",1:"-\u0448\u0456",2:"-\u0448\u0456",3:"-\u0448\u0456",4:"-\u0448\u0456",5:"-\u0448\u0456",6:"-\u0448\u044b",7:"-\u0448\u0456",8:"-\u0448\u0456",9:"-\u0448\u044b",10:"-\u0448\u044b",20:"-\u0448\u044b",30:"-\u0448\u044b",40:"-\u0448\u044b",50:"-\u0448\u0456",60:"-\u0448\u044b",70:"-\u0448\u0456",80:"-\u0448\u0456",90:"-\u0448\u044b",100:"-\u0448\u0456"},r={ordinalNumber:function(e,t){var n=Number(e),i=n>=100?100:null;return n+(u[n]||u[n%10]||i&&u[i]||"")},era:(0,a.default)({values:{narrow:["\u0431.\u0437.\u0434.","\u0431.\u0437."],abbreviated:["\u0431.\u0437.\u0434.","\u0431.\u0437."],wide:["\u0431\u0456\u0437\u0434\u0456\u04a3 \u0437\u0430\u043c\u0430\u043d\u044b\u043c\u044b\u0437\u0493\u0430 \u0434\u0435\u0439\u0456\u043d","\u0431\u0456\u0437\u0434\u0456\u04a3 \u0437\u0430\u043c\u0430\u043d\u044b\u043c\u044b\u0437"]},defaultWidth:"wide"}),quarter:(0,a.default)({values:{narrow:["1","2","3","4"],abbreviated:["1-\u0448\u0456 \u0442\u043e\u049b.","2-\u0448\u0456 \u0442\u043e\u049b.","3-\u0448\u0456 \u0442\u043e\u049b.","4-\u0448\u0456 \u0442\u043e\u049b."],wide:["1-\u0448\u0456 \u0442\u043e\u049b\u0441\u0430\u043d","2-\u0448\u0456 \u0442\u043e\u049b\u0441\u0430\u043d","3-\u0448\u0456 \u0442\u043e\u049b\u0441\u0430\u043d","4-\u0448\u0456 \u0442\u043e\u049b\u0441\u0430\u043d"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,a.default)({values:{narrow:["\u049a","\u0410","\u041d","\u0421","\u041c","\u041c","\u0428","\u0422","\u049a","\u049a","\u049a","\u0416"],abbreviated:["\u049b\u0430\u04a3","\u0430\u049b\u043f","\u043d\u0430\u0443","\u0441\u04d9\u0443","\u043c\u0430\u043c","\u043c\u0430\u0443","\u0448\u0456\u043b","\u0442\u0430\u043c","\u049b\u044b\u0440","\u049b\u0430\u0437","\u049b\u0430\u0440","\u0436\u0435\u043b"],wide:["\u049b\u0430\u04a3\u0442\u0430\u0440","\u0430\u049b\u043f\u0430\u043d","\u043d\u0430\u0443\u0440\u044b\u0437","\u0441\u04d9\u0443\u0456\u0440","\u043c\u0430\u043c\u044b\u0440","\u043c\u0430\u0443\u0441\u044b\u043c","\u0448\u0456\u043b\u0434\u0435","\u0442\u0430\u043c\u044b\u0437","\u049b\u044b\u0440\u043a\u04af\u0439\u0435\u043a","\u049b\u0430\u0437\u0430\u043d","\u049b\u0430\u0440\u0430\u0448\u0430","\u0436\u0435\u043b\u0442\u043e\u049b\u0441\u0430\u043d"]},defaultWidth:"wide",formattingValues:{narrow:["\u049a","\u0410","\u041d","\u0421","\u041c","\u041c","\u0428","\u0422","\u049a","\u049a","\u049a","\u0416"],abbreviated:["\u049b\u0430\u04a3","\u0430\u049b\u043f","\u043d\u0430\u0443","\u0441\u04d9\u0443","\u043c\u0430\u043c","\u043c\u0430\u0443","\u0448\u0456\u043b","\u0442\u0430\u043c","\u049b\u044b\u0440","\u049b\u0430\u0437","\u049b\u0430\u0440","\u0436\u0435\u043b"],wide:["\u049b\u0430\u04a3\u0442\u0430\u0440","\u0430\u049b\u043f\u0430\u043d","\u043d\u0430\u0443\u0440\u044b\u0437","\u0441\u04d9\u0443\u0456\u0440","\u043c\u0430\u043c\u044b\u0440","\u043c\u0430\u0443\u0441\u044b\u043c","\u0448\u0456\u043b\u0434\u0435","\u0442\u0430\u043c\u044b\u0437","\u049b\u044b\u0440\u043a\u04af\u0439\u0435\u043a","\u049b\u0430\u0437\u0430\u043d","\u049b\u0430\u0440\u0430\u0448\u0430","\u0436\u0435\u043b\u0442\u043e\u049b\u0441\u0430\u043d"]},defaultFormattingWidth:"wide"}),day:(0,a.default)({values:{narrow:["\u0416","\u0414","\u0421","\u0421","\u0411","\u0416","\u0421"],short:["\u0436\u0441","\u0434\u0441","\u0441\u0441","\u0441\u0440","\u0431\u0441","\u0436\u043c","\u0441\u0431"],abbreviated:["\u0436\u0441","\u0434\u0441","\u0441\u0441","\u0441\u0440","\u0431\u0441","\u0436\u043c","\u0441\u0431"],wide:["\u0436\u0435\u043a\u0441\u0435\u043d\u0431\u0456","\u0434\u04af\u0439\u0441\u0435\u043d\u0431\u0456","\u0441\u0435\u0439\u0441\u0435\u043d\u0431\u0456","\u0441\u04d9\u0440\u0441\u0435\u043d\u0431\u0456","\u0431\u0435\u0439\u0441\u0435\u043d\u0431\u0456","\u0436\u04b1\u043c\u0430","\u0441\u0435\u043d\u0431\u0456"]},defaultWidth:"wide"}),dayPeriod:(0,a.default)({values:{narrow:{am:"\u0422\u0414",pm:"\u0422\u041a",midnight:"\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b",noon:"\u0442\u04af\u0441",morning:"\u0442\u0430\u04a3",afternoon:"\u043a\u04af\u043d\u0434\u0456\u0437",evening:"\u043a\u0435\u0448",night:"\u0442\u04af\u043d"},wide:{am:"\u0422\u0414",pm:"\u0422\u041a",midnight:"\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b",noon:"\u0442\u04af\u0441",morning:"\u0442\u0430\u04a3",afternoon:"\u043a\u04af\u043d\u0434\u0456\u0437",evening:"\u043a\u0435\u0448",night:"\u0442\u04af\u043d"}},defaultWidth:"any",formattingValues:{narrow:{am:"\u0422\u0414",pm:"\u0422\u041a",midnight:"\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b\u043d\u0434\u0430",noon:"\u0442\u04af\u0441",morning:"\u0442\u0430\u04a3",afternoon:"\u043a\u04af\u043d",evening:"\u043a\u0435\u0448",night:"\u0442\u04af\u043d"},wide:{am:"\u0422\u0414",pm:"\u0422\u041a",midnight:"\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b\u043d\u0434\u0430",noon:"\u0442\u04af\u0441\u0442\u0435",morning:"\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3",afternoon:"\u043a\u04af\u043d\u0434\u0456\u0437",evening:"\u043a\u0435\u0448\u0442\u0435",night:"\u0442\u04af\u043d\u0434\u0435"}},defaultFormattingWidth:"wide"})};t.default=r,e.exports=t.default},966:function(e,t,n){"use strict";var i=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(207)),u={ordinalNumber:(0,i(n(208)).default)({matchPattern:/^(\d+)(-?(\u0448\u0456|\u0448\u044b))?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,a.default)({matchPatterns:{narrow:/^((\u0431 )?\u0437\.?\s?\u0434\.?)/i,abbreviated:/^((\u0431 )?\u0437\.?\s?\u0434\.?)/i,wide:/^(\u0431\u0456\u0437\u0434\u0456\u04a3 \u0437\u0430\u043c\u0430\u043d\u044b\u043c\u044b\u0437\u0493\u0430 \u0434\u0435\u0439\u0456\u043d|\u0431\u0456\u0437\u0434\u0456\u04a3 \u0437\u0430\u043c\u0430\u043d\u044b\u043c\u044b\u0437|\u0431\u0456\u0437\u0434\u0456\u04a3 \u0437\u0430\u043c\u0430\u043d\u044b\u043c\u044b\u0437\u0434\u0430\u043d)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^\u0431/i,/^\u0437/i]},defaultParseWidth:"any"}),quarter:(0,a.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^[1234](-?\u0448\u0456)? \u0442\u043e\u049b.?/i,wide:/^[1234](-?\u0448\u0456)? \u0442\u043e\u049b\u0441\u0430\u043d/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,a.default)({matchPatterns:{narrow:/^(\u049b|\u0430|\u043d|\u0441|\u043c|\u043c\u0430\u0443|\u0448|\u0442|\u049b\u044b\u0440|\u049b\u0430\u0437|\u049b\u0430\u0440|\u0436)/i,abbreviated:/^(\u049b\u0430\u04a3|\u0430\u049b\u043f|\u043d\u0430\u0443|\u0441\u04d9\u0443|\u043c\u0430\u043c|\u043c\u0430\u0443|\u0448\u0456\u043b|\u0442\u0430\u043c|\u049b\u044b\u0440|\u049b\u0430\u0437|\u049b\u0430\u0440|\u0436\u0435\u043b)/i,wide:/^(\u049b\u0430\u04a3\u0442\u0430\u0440|\u0430\u049b\u043f\u0430\u043d|\u043d\u0430\u0443\u0440\u044b\u0437|\u0441\u04d9\u0443\u0456\u0440|\u043c\u0430\u043c\u044b\u0440|\u043c\u0430\u0443\u0441\u044b\u043c|\u0448\u0456\u043b\u0434\u0435|\u0442\u0430\u043c\u044b\u0437|\u049b\u044b\u0440\u043a\u04af\u0439\u0435\u043a|\u049b\u0430\u0437\u0430\u043d|\u049b\u0430\u0440\u0430\u0448\u0430|\u0436\u0435\u043b\u0442\u043e\u049b\u0441\u0430\u043d)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u049b/i,/^\u0430/i,/^\u043d/i,/^\u0441/i,/^\u043c/i,/^\u043c/i,/^\u0448/i,/^\u0442/i,/^\u049b/i,/^\u049b/i,/^\u049b/i,/^\u0436/i],abbreviated:[/^\u049b\u0430\u04a3/i,/^\u0430\u049b\u043f/i,/^\u043d\u0430\u0443/i,/^\u0441\u04d9\u0443/i,/^\u043c\u0430\u043c/i,/^\u043c\u0430\u0443/i,/^\u0448\u0456\u043b/i,/^\u0442\u0430\u043c/i,/^\u049b\u044b\u0440/i,/^\u049b\u0430\u0437/i,/^\u049b\u0430\u0440/i,/^\u0436\u0435\u043b/i],any:[/^\u049b/i,/^\u0430/i,/^\u043d/i,/^\u0441/i,/^\u043c/i,/^\u043c/i,/^\u0448/i,/^\u0442/i,/^\u049b/i,/^\u049b/i,/^\u049b/i,/^\u0436/i]},defaultParseWidth:"any"}),day:(0,a.default)({matchPatterns:{narrow:/^(\u0436|\u0434|\u0441|\u0441|\u0431|\u0436|\u0441)/i,short:/^(\u0436\u0441|\u0434\u0441|\u0441\u0441|\u0441\u0440|\u0431\u0441|\u0436\u043c|\u0441\u0431)/i,wide:/^(\u0436\u0435\u043a\u0441\u0435\u043d\u0431\u0456|\u0434\u04af\u0439\u0441\u0435\u043d\u0431\u0456|\u0441\u0435\u0439\u0441\u0435\u043d\u0431\u0456|\u0441\u04d9\u0440\u0441\u0435\u043d\u0431\u0456|\u0431\u0435\u0439\u0441\u0435\u043d\u0431\u0456|\u0436\u04b1\u043c\u0430|\u0441\u0435\u043d\u0431\u0456)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u0436/i,/^\u0434/i,/^\u0441/i,/^\u0441/i,/^\u0431/i,/^\u0436/i,/^\u0441/i],short:[/^\u0436\u0441/i,/^\u0434\u0441/i,/^\u0441\u0441/i,/^\u0441\u0440/i,/^\u0431\u0441/i,/^\u0436\u043c/i,/^\u0441\u0431/i],any:[/^\u0436[\u0435\u043a]/i,/^\u0434[\u04af\u0439]/i,/^\u0441e[\u0439]/i,/^\u0441\u04d9[\u0440]/i,/^\u0431[\u0435\u0439]/i,/^\u0436[\u04b1\u043c]/i,/^\u0441\u0435[\u043d]/i]},defaultParseWidth:"any"}),dayPeriod:(0,a.default)({matchPatterns:{narrow:/^\u0422\.?\s?[\u0414\u041a]\.?|\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b\u043d\u0434\u0430|((\u0442\u04af\u0441\u0442\u0435|\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3|\u0442\u0430\u04a3\u0434\u0430|\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3|\u0442\u0430\u04a3\u043c\u0435\u043d|\u0442\u0430\u04a3|\u043a\u04af\u043d\u0434\u0456\u0437|\u043a\u04af\u043d|\u043a\u0435\u0448\u0442\u0435|\u043a\u0435\u0448|\u0442\u04af\u043d\u0434\u0435|\u0442\u04af\u043d)\.?)/i,wide:/^\u0422\.?\s?[\u0414\u041a]\.?|\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b\u043d\u0434\u0430|((\u0442\u04af\u0441\u0442\u0435|\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3|\u0442\u0430\u04a3\u0434\u0430|\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3|\u0442\u0430\u04a3\u043c\u0435\u043d|\u0442\u0430\u04a3|\u043a\u04af\u043d\u0434\u0456\u0437|\u043a\u04af\u043d|\u043a\u0435\u0448\u0442\u0435|\u043a\u0435\u0448|\u0442\u04af\u043d\u0434\u0435|\u0442\u04af\u043d)\.?)/i,any:/^\u0422\.?\s?[\u0414\u041a]\.?|\u0442\u04af\u043d \u043e\u0440\u0442\u0430\u0441\u044b\u043d\u0434\u0430|((\u0442\u04af\u0441\u0442\u0435|\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3|\u0442\u0430\u04a3\u0434\u0430|\u0442\u0430\u04a3\u0435\u0440\u0442\u0435\u04a3|\u0442\u0430\u04a3\u043c\u0435\u043d|\u0442\u0430\u04a3|\u043a\u04af\u043d\u0434\u0456\u0437|\u043a\u04af\u043d|\u043a\u0435\u0448\u0442\u0435|\u043a\u0435\u0448|\u0442\u04af\u043d\u0434\u0435|\u0442\u04af\u043d)\.?)/i},defaultMatchWidth:"wide",parsePatterns:{any:{am:/^\u0422\u0414/i,pm:/^\u0422\u041a/i,midnight:/^\u0442\u04af\u043d \u043e\u0440\u0442\u0430/i,noon:/^\u043a\u04af\u043d\u0434\u0456\u0437/i,morning:/\u0442\u0430\u04a3/i,afternoon:/\u0442\u04af\u0441/i,evening:/\u043a\u0435\u0448/i,night:/\u0442\u04af\u043d/i}},defaultParseWidth:"any"})};t.default=u,e.exports=t.default}}]);