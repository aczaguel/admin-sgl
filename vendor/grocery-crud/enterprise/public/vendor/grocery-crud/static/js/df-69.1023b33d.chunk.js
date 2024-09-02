(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[68],{1050:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(1051)),i=n(a(1052)),d=n(a(1053)),o=n(a(1054)),u=n(a(1055)),s={code:"oc",formatDistance:r.default,formatLong:i.default,formatRelative:d.default,localize:o.default,match:u.default,options:{weekStartsOn:1,firstWeekContainsDate:4}};t.default=s,e.exports=t.default},1051:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={lessThanXSeconds:{one:"mens d\u2019una segonda",other:"mens de {{count}} segondas"},xSeconds:{one:"1 segonda",other:"{{count}} segondas"},halfAMinute:"30 segondas",lessThanXMinutes:{one:"mens d\u2019una minuta",other:"mens de {{count}} minutas"},xMinutes:{one:"1 minuta",other:"{{count}} minutas"},aboutXHours:{one:"environ 1 ora",other:"environ {{count}} oras"},xHours:{one:"1 ora",other:"{{count}} oras"},xDays:{one:"1 jorn",other:"{{count}} jorns"},aboutXWeeks:{one:"environ 1 setmana",other:"environ {{count}} setmanas"},xWeeks:{one:"1 setmana",other:"{{count}} setmanas"},aboutXMonths:{one:"environ 1 mes",other:"environ {{count}} meses"},xMonths:{one:"1 mes",other:"{{count}} meses"},aboutXYears:{one:"environ 1 an",other:"environ {{count}} ans"},xYears:{one:"1 an",other:"{{count}} ans"},overXYears:{one:"mai d\u2019un an",other:"mai de {{count}} ans"},almostXYears:{one:"gaireben un an",other:"gaireben {{count}} ans"}},r=function(e,t,a){var r,i=n[e];return r="string"===typeof i?i:1===t?i.one:i.other.replace("{{count}}",String(t)),null!==a&&void 0!==a&&a.addSuffix?a.comparison&&a.comparison>0?"d\u2019aqu\xed "+r:"fa "+r:r};t.default=r,e.exports=t.default},1052:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(204)),i={date:(0,r.default)({formats:{full:"EEEE d 'de' MMMM y",long:"d 'de' MMMM y",medium:"d MMM y",short:"dd/MM/y"},defaultWidth:"full"}),time:(0,r.default)({formats:{full:"HH:mm:ss zzzz",long:"HH:mm:ss z",medium:"HH:mm:ss",short:"HH:mm"},defaultWidth:"full"}),dateTime:(0,r.default)({formats:{full:"{{date}} 'a' {{time}}",long:"{{date}} 'a' {{time}}",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})};t.default=i,e.exports=t.default},1053:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={lastWeek:"eeee 'passat a' p",yesterday:"'i\xe8r a' p",today:"'u\xe8i a' p",tomorrow:"'deman a' p",nextWeek:"eeee 'a' p",other:"P"},r=function(e,t,a,r){return n[e]};t.default=r,e.exports=t.default},1054:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(205)),i={ordinalNumber:function(e,t){var a,n=Number(e),r=null===t||void 0===t?void 0:t.unit;switch(n){case 1:a="\xe8r";break;case 2:a="nd";break;default:a="en"}return"year"!==r&&"week"!==r&&"hour"!==r&&"minute"!==r&&"second"!==r||(a+="a"),n+a},era:(0,r.default)({values:{narrow:["ab. J.C.","apr. J.C."],abbreviated:["ab. J.C.","apr. J.C."],wide:["abans J\xe8sus-Crist","apr\xe8s J\xe8sus-Crist"]},defaultWidth:"wide"}),quarter:(0,r.default)({values:{narrow:["T1","T2","T3","T4"],abbreviated:["1\xe8r trim.","2nd trim.","3en trim.","4en trim."],wide:["1\xe8r trim\xe8stre","2nd trim\xe8stre","3en trim\xe8stre","4en trim\xe8stre"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,r.default)({values:{narrow:["GN","FB","M\xc7","AB","MA","JN","JL","AG","ST","OC","NV","DC"],abbreviated:["gen.","febr.","mar\xe7","abr.","mai","junh","jul.","ag.","set.","oct.","nov.","dec."],wide:["geni\xe8r","febri\xe8r","mar\xe7","abril","mai","junh","julhet","agost","setembre","oct\xf2bre","novembre","decembre"]},defaultWidth:"wide"}),day:(0,r.default)({values:{narrow:["dg.","dl.","dm.","dc.","dj.","dv.","ds."],short:["dg.","dl.","dm.","dc.","dj.","dv.","ds."],abbreviated:["dg.","dl.","dm.","dc.","dj.","dv.","ds."],wide:["dimenge","diluns","dimars","dim\xe8cres","dij\xf2us","divendres","dissabte"]},defaultWidth:"wide"}),dayPeriod:(0,r.default)({values:{narrow:{am:"am",pm:"pm",midnight:"mi\xe8janu\xe8ch",noon:"mi\xe8gjorn",morning:"matin",afternoon:"apr\xe8p-mi\xe8gjorn",evening:"v\xe8spre",night:"nu\xe8ch"},abbreviated:{am:"a.m.",pm:"p.m.",midnight:"mi\xe8janu\xe8ch",noon:"mi\xe8gjorn",morning:"matin",afternoon:"apr\xe8p-mi\xe8gjorn",evening:"v\xe8spre",night:"nu\xe8ch"},wide:{am:"a.m.",pm:"p.m.",midnight:"mi\xe8janu\xe8ch",noon:"mi\xe8gjorn",morning:"matin",afternoon:"apr\xe8p-mi\xe8gjorn",evening:"v\xe8spre",night:"nu\xe8ch"}},defaultWidth:"wide",formattingValues:{narrow:{am:"am",pm:"pm",midnight:"mi\xe8janu\xe8ch",noon:"mi\xe8gjorn",morning:"del matin",afternoon:"de l\u2019apr\xe8p-mi\xe8gjorn",evening:"del ser",night:"de la nu\xe8ch"},abbreviated:{am:"AM",pm:"PM",midnight:"mi\xe8janu\xe8ch",noon:"mi\xe8gjorn",morning:"del matin",afternoon:"de l\u2019apr\xe8p-mi\xe8gjorn",evening:"del ser",night:"de la nu\xe8ch"},wide:{am:"ante meridiem",pm:"post meridiem",midnight:"mi\xe8janu\xe8ch",noon:"mi\xe8gjorn",morning:"del matin",afternoon:"de l\u2019apr\xe8p-mi\xe8gjorn",evening:"del ser",night:"de la nu\xe8ch"}},defaultFormattingWidth:"wide"})};t.default=i,e.exports=t.default},1055:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(206)),i={ordinalNumber:(0,n(a(207)).default)({matchPattern:/^(\d+)(\xe8r|nd|en)?[a]?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,r.default)({matchPatterns:{narrow:/^(ab\.J\.C|apr\.J\.C|apr\.J\.-C)/i,abbreviated:/^(ab\.J\.-C|ab\.J-C|apr\.J\.-C|apr\.J-C|ap\.J-C)/i,wide:/^(abans J\xe8sus-Crist|apr\xe8s J\xe8sus-Crist)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^ab/i,/^ap/i]},defaultParseWidth:"any"}),quarter:(0,r.default)({matchPatterns:{narrow:/^T[1234]/i,abbreviated:/^[1234](\xe8r|nd|en)? trim\.?/i,wide:/^[1234](\xe8r|nd|en)? trim\xe8stre/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,r.default)({matchPatterns:{narrow:/^(GN|FB|M\xc7|AB|MA|JN|JL|AG|ST|OC|NV|DC)/i,abbreviated:/^(gen|febr|mar\xe7|abr|mai|junh|jul|ag|set|oct|nov|dec)\.?/i,wide:/^(geni\xe8r|febri\xe8r|mar\xe7|abril|mai|junh|julhet|agost|setembre|oct\xf2bre|novembre|decembre)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^g/i,/^f/i,/^ma[r?]|M\xc7/i,/^ab/i,/^ma[i?]/i,/^ju[n?]|JN/i,/^ju[l?]|JL/i,/^ag/i,/^s/i,/^o/i,/^n/i,/^d/i]},defaultParseWidth:"any"}),day:(0,r.default)({matchPatterns:{narrow:/^d[glmcjvs]\.?/i,short:/^d[glmcjvs]\.?/i,abbreviated:/^d[glmcjvs]\.?/i,wide:/^(dimenge|diluns|dimars|dim\xe8cres|dij\xf2us|divendres|dissabte)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^dg/i,/^dl/i,/^dm/i,/^dc/i,/^dj/i,/^dv/i,/^ds/i],short:[/^dg/i,/^dl/i,/^dm/i,/^dc/i,/^dj/i,/^dv/i,/^ds/i],abbreviated:[/^dg/i,/^dl/i,/^dm/i,/^dc/i,/^dj/i,/^dv/i,/^ds/i],any:[/^dg|dime/i,/^dl|dil/i,/^dm|dima/i,/^dc|dim\xe8/i,/^dj|dij/i,/^dv|div/i,/^ds|dis/i]},defaultParseWidth:"any"}),dayPeriod:(0,r.default)({matchPatterns:{any:/(^(a\.?m|p\.?m))|(ante meridiem|post meridiem)|((del |de la |de l\u2019)(matin|apr\xe8p-mi\xe8gjorn|v\xe8spre|ser|nu\xe8ch))/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/(^a)|ante meridiem/i,pm:/(^p)|post meridiem/i,midnight:/^mi\xe8j/i,noon:/^mi\xe8g/i,morning:/matin/i,afternoon:/apr\xe8p-mi\xe8gjorn/i,evening:/v\xe8spre|ser/i,night:/nu\xe8ch/i}},defaultParseWidth:"any"})};t.default=i,e.exports=t.default},202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var n;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var r=e.defaultFormattingWidth||e.defaultWidth,i=null!==a&&void 0!==a&&a.width?String(a.width):r;n=e.formattingValues[i]||e.formattingValues[r]}else{var d=e.defaultWidth,o=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;n=e.values[o]||e.values[d]}return n[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=a.width,r=n&&e.matchPatterns[n]||e.matchPatterns[e.defaultMatchWidth],i=t.match(r);if(!i)return null;var d,o=i[0],u=n&&e.parsePatterns[n]||e.parsePatterns[e.defaultParseWidth],s=Array.isArray(u)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(u,(function(e){return e.test(o)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(u,(function(e){return e.test(o)}));return d=e.valueCallback?e.valueCallback(s):s,{value:d=a.valueCallback?a.valueCallback(d):d,rest:t.slice(o.length)}}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=t.match(e.matchPattern);if(!n)return null;var r=n[0],i=t.match(e.parsePattern);if(!i)return null;var d=e.valueCallback?e.valueCallback(i[0]):i[0];return{value:d=a.valueCallback?a.valueCallback(d):d,rest:t.slice(r.length)}}},e.exports=t.default}}]);