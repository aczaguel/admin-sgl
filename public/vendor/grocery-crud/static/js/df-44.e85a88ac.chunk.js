(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[43],{203:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var n;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var i=e.defaultFormattingWidth||e.defaultWidth,r=null!==a&&void 0!==a&&a.width?String(a.width):i;n=e.formattingValues[r]||e.formattingValues[i]}else{var o=e.defaultWidth,d=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;n=e.values[d]||e.values[o]}return n[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=a.width,i=n&&e.matchPatterns[n]||e.matchPatterns[e.defaultMatchWidth],r=t.match(i);if(!r)return null;var o,d=r[0],u=n&&e.parsePatterns[n]||e.parsePatterns[e.defaultParseWidth],l=Array.isArray(u)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(u,(function(e){return e.test(d)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(u,(function(e){return e.test(d)}));return o=e.valueCallback?e.valueCallback(l):l,{value:o=a.valueCallback?a.valueCallback(o):o,rest:t.slice(d.length)}}},e.exports=t.default},208:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=t.match(e.matchPattern);if(!n)return null;var i=n[0],r=t.match(e.parsePattern);if(!r)return null;var o=e.valueCallback?e.valueCallback(r[0]):r[0];return{value:o=a.valueCallback?a.valueCallback(o):o,rest:t.slice(i.length)}}},e.exports=t.default},909:function(e,t,a){"use strict";var n=a(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a(910)),r=n(a(911)),o=n(a(912)),d=n(a(913)),u=n(a(914)),l={code:"ht",formatDistance:i.default,formatLong:r.default,formatRelative:o.default,localize:d.default,match:u.default,options:{weekStartsOn:1,firstWeekContainsDate:4}};t.default=l,e.exports=t.default},910:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={lessThanXSeconds:{one:"mwens pase yon segond",other:"mwens pase {{count}} segond"},xSeconds:{one:"1 segond",other:"{{count}} segond"},halfAMinute:"30 segond",lessThanXMinutes:{one:"mwens pase yon minit",other:"mwens pase {{count}} minit"},xMinutes:{one:"1 minit",other:"{{count}} minit"},aboutXHours:{one:"anviwon in\xe8",other:"anviwon {{count}} \xe8"},xHours:{one:"1 l\xe8",other:"{{count}} l\xe8"},xDays:{one:"1 jou",other:"{{count}} jou"},aboutXWeeks:{one:"anviwon 1 sem\xe8n",other:"anviwon {{count}} sem\xe8n"},xWeeks:{one:"1 sem\xe8n",other:"{{count}} sem\xe8n"},aboutXMonths:{one:"anviwon 1 mwa",other:"anviwon {{count}} mwa"},xMonths:{one:"1 mwa",other:"{{count}} mwa"},aboutXYears:{one:"anviwon 1 an",other:"anviwon {{count}} an"},xYears:{one:"1 an",other:"{{count}} an"},overXYears:{one:"plis pase 1 an",other:"plis pase {{count}} an"},almostXYears:{one:"pr\xe8ske 1 an",other:"pr\xe8ske {{count}} an"}},i=function(e,t,a){var i,r=n[e];return i="string"===typeof r?r:1===t?r.one:r.other.replace("{{count}}",String(t)),null!==a&&void 0!==a&&a.addSuffix?a.comparison&&a.comparison>0?"nan "+i:"sa f\xe8 "+i:i};t.default=i,e.exports=t.default},911:function(e,t,a){"use strict";var n=a(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a(205)),r={date:(0,i.default)({formats:{full:"EEEE d MMMM y",long:"d MMMM y",medium:"d MMM y",short:"dd/MM/y"},defaultWidth:"full"}),time:(0,i.default)({formats:{full:"HH:mm:ss zzzz",long:"HH:mm:ss z",medium:"HH:mm:ss",short:"HH:mm"},defaultWidth:"full"}),dateTime:(0,i.default)({formats:{full:"{{date}} 'nan l\xe8' {{time}}",long:"{{date}} 'nan l\xe8' {{time}}",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})};t.default=r,e.exports=t.default},912:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={lastWeek:"eeee 'pase nan l\xe8' p",yesterday:"'y\xe8 nan l\xe8' p",today:"'jodi a' p",tomorrow:"'demen nan l\xe8' p'",nextWeek:"eeee 'pwochen nan l\xe8' p",other:"P"},i=function(e,t,a,i){return n[e]};t.default=i,e.exports=t.default},913:function(e,t,a){"use strict";var n=a(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a(206)),r={ordinalNumber:function(e,t){var a=Number(e);return 0===a?String(a):a+(1===a?"ye":"y\xe8m")},era:(0,i.default)({values:{narrow:["av. J.-K","ap. J.-K"],abbreviated:["av. J.-K","ap. J.-K"],wide:["anvan Jezi Kris","apre Jezi Kris"]},defaultWidth:"wide"}),quarter:(0,i.default)({values:{narrow:["T1","T2","T3","T4"],abbreviated:["1ye trim.","2y\xe8m trim.","3y\xe8m trim.","4y\xe8m trim."],wide:["1ye trim\xe8s","2y\xe8m trim\xe8s","3y\xe8m trim\xe8s","4y\xe8m trim\xe8s"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,i.default)({values:{narrow:["J","F","M","A","M","J","J","O","S","O","N","D"],abbreviated:["janv.","fevr.","mas","avr.","me","jen","jiy\xe8","out","sept.","okt.","nov.","des."],wide:["janvye","fevrye","mas","avril","me","jen","jiy\xe8","out","septanm","okt\xf2b","novanm","desanm"]},defaultWidth:"wide"}),day:(0,i.default)({values:{narrow:["D","L","M","M","J","V","S"],short:["di","le","ma","m\xe8","je","va","sa"],abbreviated:["dim.","len.","mad.","m\xe8k.","jed.","van.","sam."],wide:["dimanch","lendi","madi","m\xe8kredi","jedi","vandredi","samdi"]},defaultWidth:"wide"}),dayPeriod:(0,i.default)({values:{narrow:{am:"AM",pm:"PM",midnight:"minwit",noon:"midi",morning:"mat.",afternoon:"ap.m.",evening:"swa",night:"mat."},abbreviated:{am:"AM",pm:"PM",midnight:"minwit",noon:"midi",morning:"maten",afternoon:"apr\xe8midi",evening:"swa",night:"maten"},wide:{am:"AM",pm:"PM",midnight:"minwit",noon:"midi",morning:"nan maten",afternoon:"nan apr\xe8midi",evening:"nan asw\xe8",night:"nan maten"}},defaultWidth:"wide"})};t.default=r,e.exports=t.default},914:function(e,t,a){"use strict";var n=a(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a(207)),r={ordinalNumber:(0,n(a(208)).default)({matchPattern:/^(\d+)(ye|y\xe8m)?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,i.default)({matchPatterns:{narrow:/^(av\.J\.K|ap\.J\.K|ap\.J\.-K)/i,abbreviated:/^(av\.J\.-K|av\.J-K|apr\.J\.-K|apr\.J-K|ap\.J-K)/i,wide:/^(avan Jezi Kris|apre Jezi Kris)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^av/i,/^ap/i]},defaultParseWidth:"any"}),quarter:(0,i.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^t[1234]/i,wide:/^[1234](ye|y\xe8m)? trim\xe8s/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,i.default)({matchPatterns:{narrow:/^[jfmasond]/i,abbreviated:/^(janv|fevr|mas|avr|me|jen|jiy\xe8|out|sept|okt|nov|des)\.?/i,wide:/^(janvye|fevrye|mas|avril|me|jen|jiy\xe8|out|septanm|okt\xf2b|novanm|desanm)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^j/i,/^f/i,/^m/i,/^a/i,/^m/i,/^j/i,/^j/i,/^o/i,/^s/i,/^o/i,/^n/i,/^d/i],any:[/^ja/i,/^f/i,/^ma/i,/^av/i,/^me/i,/^je/i,/^ji/i,/^ou/i,/^s/i,/^ok/i,/^n/i,/^d/i]},defaultParseWidth:"any"}),day:(0,i.default)({matchPatterns:{narrow:/^[lmjvsd]/i,short:/^(di|le|ma|me|je|va|sa)/i,abbreviated:/^(dim|len|mad|m\xe8k|jed|van|sam)\.?/i,wide:/^(dimanch|lendi|madi|m\xe8kredi|jedi|vandredi|samdi)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^d/i,/^l/i,/^m/i,/^m/i,/^j/i,/^v/i,/^s/i],any:[/^di/i,/^le/i,/^ma/i,/^m\xe8/i,/^je/i,/^va/i,/^sa/i]},defaultParseWidth:"any"}),dayPeriod:(0,i.default)({matchPatterns:{narrow:/^(a|p|minwit|midi|mat\.?|ap\.?m\.?|swa)/i,any:/^([ap]\.?\s?m\.?|nan maten|nan apr\xe8midi|nan asw\xe8)/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^a/i,pm:/^p/i,midnight:/^min/i,noon:/^mid/i,morning:/mat/i,afternoon:/ap/i,evening:/sw/i,night:/nwit/i}},defaultParseWidth:"any"})};t.default=r,e.exports=t.default}}]);