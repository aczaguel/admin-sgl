(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[75],{1085:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(1086)),o=a(n(1087)),u=a(n(1088)),i=a(n(1089)),d=a(n(1090)),l={code:"sk",formatDistance:r.default,formatLong:o.default,formatRelative:u.default,localize:i.default,match:d.default,options:{weekStartsOn:1,firstWeekContainsDate:4}};t.default=l,e.exports=t.default},1086:function(e,t,n){"use strict";function a(e,t,n){var a=function(e,t){return 1===t&&e.one?e.one:t>=2&&t<=4&&e.twoFour?e.twoFour:e.other}(e,t);return a[n].replace("{{count}}",String(t))}function r(e){var t="";return"almost"===e&&(t="takmer"),"about"===e&&(t="pribli\u017ene"),t.length>0?t+" ":""}function o(e){var t="";return"lessThan"===e&&(t="menej ne\u017e"),"over"===e&&(t="viac ne\u017e"),t.length>0?t+" ":""}Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var u={xSeconds:{one:{present:"sekunda",past:"sekundou",future:"sekundu"},twoFour:{present:"{{count}} sekundy",past:"{{count}} sekundami",future:"{{count}} sekundy"},other:{present:"{{count}} sek\xfand",past:"{{count}} sekundami",future:"{{count}} sek\xfand"}},halfAMinute:{other:{present:"pol min\xfaty",past:"pol min\xfatou",future:"pol min\xfaty"}},xMinutes:{one:{present:"min\xfata",past:"min\xfatou",future:"min\xfatu"},twoFour:{present:"{{count}} min\xfaty",past:"{{count}} min\xfatami",future:"{{count}} min\xfaty"},other:{present:"{{count}} min\xfat",past:"{{count}} min\xfatami",future:"{{count}} min\xfat"}},xHours:{one:{present:"hodina",past:"hodinou",future:"hodinu"},twoFour:{present:"{{count}} hodiny",past:"{{count}} hodinami",future:"{{count}} hodiny"},other:{present:"{{count}} hod\xedn",past:"{{count}} hodinami",future:"{{count}} hod\xedn"}},xDays:{one:{present:"de\u0148",past:"d\u0148om",future:"de\u0148"},twoFour:{present:"{{count}} dni",past:"{{count}} d\u0148ami",future:"{{count}} dni"},other:{present:"{{count}} dn\xed",past:"{{count}} d\u0148ami",future:"{{count}} dn\xed"}},xWeeks:{one:{present:"t\xfd\u017ede\u0148",past:"t\xfd\u017ed\u0148om",future:"t\xfd\u017ede\u0148"},twoFour:{present:"{{count}} t\xfd\u017edne",past:"{{count}} t\xfd\u017ed\u0148ami",future:"{{count}} t\xfd\u017edne"},other:{present:"{{count}} t\xfd\u017ed\u0148ov",past:"{{count}} t\xfd\u017ed\u0148ami",future:"{{count}} t\xfd\u017ed\u0148ov"}},xMonths:{one:{present:"mesiac",past:"mesiacom",future:"mesiac"},twoFour:{present:"{{count}} mesiace",past:"{{count}} mesiacmi",future:"{{count}} mesiace"},other:{present:"{{count}} mesiacov",past:"{{count}} mesiacmi",future:"{{count}} mesiacov"}},xYears:{one:{present:"rok",past:"rokom",future:"rok"},twoFour:{present:"{{count}} roky",past:"{{count}} rokmi",future:"{{count}} roky"},other:{present:"{{count}} rokov",past:"{{count}} rokmi",future:"{{count}} rokov"}}},i=function(e,t,n){var i,d=function(e){return["lessThan","about","over","almost"].filter((function(t){return!!e.match(new RegExp("^"+t))}))[0]}(e)||"",l=(i=e.substring(d.length)).charAt(0).toLowerCase()+i.slice(1),s=u[l];return null!==n&&void 0!==n&&n.addSuffix?n.comparison&&n.comparison>0?r(d)+"o "+o(d)+a(s,t,"future"):r(d)+"pred "+o(d)+a(s,t,"past"):r(d)+o(d)+a(s,t,"present")};t.default=i,e.exports=t.default},1087:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(204)),o={date:(0,r.default)({formats:{full:"EEEE d. MMMM y",long:"d. MMMM y",medium:"d. M. y",short:"d. M. y"},defaultWidth:"full"}),time:(0,r.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,r.default)({formats:{full:"{{date}}, {{time}}",long:"{{date}}, {{time}}",medium:"{{date}}, {{time}}",short:"{{date}} {{time}}"},defaultWidth:"full"})};t.default=o,e.exports=t.default},1088:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(229)),o=["nede\u013eu","pondelok","utorok","stredu","\u0161tvrtok","piatok","sobotu"];function u(e){return 4===e?"'vo' eeee 'o' p":"'v "+o[e]+" o' p"}var i={lastWeek:function(e,t,n){var a=e.getUTCDay();return(0,r.default)(e,t,n)?u(a):function(e){var t=o[e];switch(e){case 0:case 3:case 6:return"'minul\xfa "+t+" o' p";default:return"'minul\xfd' eeee 'o' p"}}(a)},yesterday:"'v\u010dera o' p",today:"'dnes o' p",tomorrow:"'zajtra o' p",nextWeek:function(e,t,n){var a=e.getUTCDay();return(0,r.default)(e,t,n)?u(a):function(e){var t=o[e];switch(e){case 0:case 4:case 6:return"'bud\xfacu "+t+" o' p";default:return"'bud\xfaci' eeee 'o' p"}}(a)},other:"P"},d=function(e,t,n,a){var r=i[e];return"function"===typeof r?r(t,n,a):r};t.default=d,e.exports=t.default},1089:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(205)),o={ordinalNumber:function(e,t){return Number(e)+"."},era:(0,r.default)({values:{narrow:["pred Kr.","po Kr."],abbreviated:["pred Kr.","po Kr."],wide:["pred Kristom","po Kristovi"]},defaultWidth:"wide"}),quarter:(0,r.default)({values:{narrow:["1","2","3","4"],abbreviated:["Q1","Q2","Q3","Q4"],wide:["1. \u0161tvr\u0165rok","2. \u0161tvr\u0165rok","3. \u0161tvr\u0165rok","4. \u0161tvr\u0165rok"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,r.default)({values:{narrow:["j","f","m","a","m","j","j","a","s","o","n","d"],abbreviated:["jan","feb","mar","apr","m\xe1j","j\xfan","j\xfal","aug","sep","okt","nov","dec"],wide:["janu\xe1r","febru\xe1r","marec","apr\xedl","m\xe1j","j\xfan","j\xfal","august","september","okt\xf3ber","november","december"]},defaultWidth:"wide",formattingValues:{narrow:["j","f","m","a","m","j","j","a","s","o","n","d"],abbreviated:["jan","feb","mar","apr","m\xe1j","j\xfan","j\xfal","aug","sep","okt","nov","dec"],wide:["janu\xe1ra","febru\xe1ra","marca","apr\xedla","m\xe1ja","j\xfana","j\xfala","augusta","septembra","okt\xf3bra","novembra","decembra"]},defaultFormattingWidth:"wide"}),day:(0,r.default)({values:{narrow:["n","p","u","s","\u0161","p","s"],short:["ne","po","ut","st","\u0161t","pi","so"],abbreviated:["ne","po","ut","st","\u0161t","pi","so"],wide:["nede\u013ea","pondelok","utorok","streda","\u0161tvrtok","piatok","sobota"]},defaultWidth:"wide"}),dayPeriod:(0,r.default)({values:{narrow:{am:"AM",pm:"PM",midnight:"poln.",noon:"pol.",morning:"r\xe1no",afternoon:"pop.",evening:"ve\u010d.",night:"noc"},abbreviated:{am:"AM",pm:"PM",midnight:"poln.",noon:"pol.",morning:"r\xe1no",afternoon:"popol.",evening:"ve\u010der",night:"noc"},wide:{am:"AM",pm:"PM",midnight:"polnoc",noon:"poludnie",morning:"r\xe1no",afternoon:"popoludnie",evening:"ve\u010der",night:"noc"}},defaultWidth:"wide",formattingValues:{narrow:{am:"AM",pm:"PM",midnight:"o poln.",noon:"nap.",morning:"r\xe1no",afternoon:"pop.",evening:"ve\u010d.",night:"v n."},abbreviated:{am:"AM",pm:"PM",midnight:"o poln.",noon:"napol.",morning:"r\xe1no",afternoon:"popol.",evening:"ve\u010der",night:"v noci"},wide:{am:"AM",pm:"PM",midnight:"o polnoci",noon:"napoludnie",morning:"r\xe1no",afternoon:"popoludn\xed",evening:"ve\u010der",night:"v noci"}},defaultFormattingWidth:"wide"})};t.default=o,e.exports=t.default},1090:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(206)),o={ordinalNumber:(0,a(n(207)).default)({matchPattern:/^(\d+)\.?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,r.default)({matchPatterns:{narrow:/^(pred Kr\.|pred n\. l\.|po Kr\.|n\. l\.)/i,abbreviated:/^(pred Kr\.|pred n\. l\.|po Kr\.|n\. l\.)/i,wide:/^(pred Kristom|pred na[\u0161s][\xedi]m letopo[\u010dc]tom|po Kristovi|n[\xe1a][\u0161s]ho letopo[\u010dc]tu)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^pr/i,/^(po|n)/i]},defaultParseWidth:"any"}),quarter:(0,r.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^q[1234]/i,wide:/^[1234]\. [\u0161s]tvr[\u0165t]rok/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,r.default)({matchPatterns:{narrow:/^[jfmasond]/i,abbreviated:/^(jan|feb|mar|apr|m[\xe1a]j|j[\xfau]n|j[\xfau]l|aug|sep|okt|nov|dec)/i,wide:/^(janu[\xe1a]ra?|febru[\xe1a]ra?|(marec|marca)|apr[\xedi]la?|m[\xe1a]ja?|j[\xfau]na?|j[\xfau]la?|augusta?|(september|septembra)|(okt[\xf3o]ber|okt[\xf3o]bra)|(november|novembra)|(december|decembra))/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^j/i,/^f/i,/^m/i,/^a/i,/^m/i,/^j/i,/^j/i,/^a/i,/^s/i,/^o/i,/^n/i,/^d/i],any:[/^ja/i,/^f/i,/^mar/i,/^ap/i,/^m[\xe1a]j/i,/^j[\xfau]n/i,/^j[\xfau]l/i,/^au/i,/^s/i,/^o/i,/^n/i,/^d/i]},defaultParseWidth:"any"}),day:(0,r.default)({matchPatterns:{narrow:/^[npus\u0161p]/i,short:/^(ne|po|ut|st|\u0161t|pi|so)/i,abbreviated:/^(ne|po|ut|st|\u0161t|pi|so)/i,wide:/^(nede[\u013el]a|pondelok|utorok|streda|[\u0161s]tvrtok|piatok|sobota])/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^n/i,/^p/i,/^u/i,/^s/i,/^\u0161/i,/^p/i,/^s/i],any:[/^n/i,/^po/i,/^u/i,/^st/i,/^(\u0161t|stv)/i,/^pi/i,/^so/i]},defaultParseWidth:"any"}),dayPeriod:(0,r.default)({matchPatterns:{narrow:/^(am|pm|(o )?poln\.?|(nap\.?|pol\.?)|r[\xe1a]no|pop\.?|ve[\u010dc]\.?|(v n\.?|noc))/i,abbreviated:/^(am|pm|(o )?poln\.?|(napol\.?|pol\.?)|r[\xe1a]no|pop\.?|ve[\u010dc]er|(v )?noci?)/i,any:/^(am|pm|(o )?polnoci?|(na)?poludnie|r[\xe1a]no|popoludn(ie|\xed|i)|ve[\u010dc]er|(v )?noci?)/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^am/i,pm:/^pm/i,midnight:/poln/i,noon:/^(nap|(na)?pol(\.|u))/i,morning:/^r[\xe1a]no/i,afternoon:/^pop/i,evening:/^ve[\u010dc]/i,night:/^(noc|v n\.)/i}},defaultParseWidth:"any"})};t.default=o,e.exports=t.default},202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},203:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t){if(t.length<e)throw new TypeError(e+" argument"+(e>1?"s":"")+" required, but only "+t.length+" present")},e.exports=t.default},204:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},n=t.width?String(t.width):e.defaultWidth;return e.formats[n]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,n){var a;if("formatting"===(null!==n&&void 0!==n&&n.context?String(n.context):"standalone")&&e.formattingValues){var r=e.defaultFormattingWidth||e.defaultWidth,o=null!==n&&void 0!==n&&n.width?String(n.width):r;a=e.formattingValues[o]||e.formattingValues[r]}else{var u=e.defaultWidth,i=null!==n&&void 0!==n&&n.width?String(n.width):e.defaultWidth;a=e.values[i]||e.values[u]}return a[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},a=n.width,r=a&&e.matchPatterns[a]||e.matchPatterns[e.defaultMatchWidth],o=t.match(r);if(!o)return null;var u,i=o[0],d=a&&e.parsePatterns[a]||e.parsePatterns[e.defaultParseWidth],l=Array.isArray(d)?function(e,t){for(var n=0;n<e.length;n++)if(t(e[n]))return n;return}(d,(function(e){return e.test(i)})):function(e,t){for(var n in e)if(e.hasOwnProperty(n)&&t(e[n]))return n;return}(d,(function(e){return e.test(i)}));return u=e.valueCallback?e.valueCallback(l):l,{value:u=n.valueCallback?n.valueCallback(u):u,rest:t.slice(i.length)}}},e.exports=t.default},207:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},a=t.match(e.matchPattern);if(!a)return null;var r=a[0],o=t.match(e.parsePattern);if(!o)return null;var u=e.valueCallback?e.valueCallback(o[0]):o[0];return{value:u=n.valueCallback?n.valueCallback(u):u,rest:t.slice(r.length)}}},e.exports=t.default},208:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){(0,o.default)(1,arguments);var t=Object.prototype.toString.call(e);return e instanceof Date||"object"===(0,r.default)(e)&&"[object Date]"===t?new Date(e.getTime()):"number"===typeof e||"[object Number]"===t?new Date(e):("string"!==typeof e&&"[object String]"!==t||"undefined"===typeof console||(console.warn("Starting with v2.0.0-beta.1 date-fns doesn't accept strings as date arguments. Please use `parseISO` to parse strings. See: https://github.com/date-fns/date-fns/blob/master/docs/upgradeGuide.md#string-arguments"),console.warn((new Error).stack)),new Date(NaN))};var r=a(n(228)),o=a(n(203));e.exports=t.default},209:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){if(null===e||!0===e||!1===e)return NaN;var t=Number(e);if(isNaN(t))return t;return t<0?Math.ceil(t):Math.floor(t)},e.exports=t.default},212:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.getDefaultOptions=function(){return a},t.setDefaultOptions=function(e){a=e};var a={}},227:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t){var n,a,d,l,s,f,c,p;(0,o.default)(1,arguments);var m=(0,i.getDefaultOptions)(),v=(0,u.default)(null!==(n=null!==(a=null!==(d=null!==(l=null===t||void 0===t?void 0:t.weekStartsOn)&&void 0!==l?l:null===t||void 0===t||null===(s=t.locale)||void 0===s||null===(f=s.options)||void 0===f?void 0:f.weekStartsOn)&&void 0!==d?d:m.weekStartsOn)&&void 0!==a?a:null===(c=m.locale)||void 0===c||null===(p=c.options)||void 0===p?void 0:p.weekStartsOn)&&void 0!==n?n:0);if(!(v>=0&&v<=6))throw new RangeError("weekStartsOn must be between 0 and 6 inclusively");var h=(0,r.default)(e),b=h.getUTCDay(),g=(b<v?7:0)+b-v;return h.setUTCDate(h.getUTCDate()-g),h.setUTCHours(0,0,0,0),h};var r=a(n(208)),o=a(n(203)),u=a(n(209)),i=n(212);e.exports=t.default},229:function(e,t,n){"use strict";var a=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t,n){(0,r.default)(2,arguments);var a=(0,o.default)(e,n),u=(0,o.default)(t,n);return a.getTime()===u.getTime()};var r=a(n(203)),o=a(n(227));e.exports=t.default}}]);
//# sourceMappingURL=df-75.162352ed.chunk.js.map