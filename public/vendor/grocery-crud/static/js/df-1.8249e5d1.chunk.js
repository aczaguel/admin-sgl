(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[5],{203:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},205:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},n=t.width?String(t.width):e.defaultWidth;return e.formats[n]||e.formats[e.defaultWidth]}},e.exports=t.default},206:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,n){var a;if("formatting"===(null!==n&&void 0!==n&&n.context?String(n.context):"standalone")&&e.formattingValues){var r=e.defaultFormattingWidth||e.defaultWidth,o=null!==n&&void 0!==n&&n.width?String(n.width):r;a=e.formattingValues[o]||e.formattingValues[r]}else{var i=e.defaultWidth,u=null!==n&&void 0!==n&&n.width?String(n.width):e.defaultWidth;a=e.values[u]||e.values[i]}return a[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},207:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},a=n.width,r=a&&e.matchPatterns[a]||e.matchPatterns[e.defaultMatchWidth],o=t.match(r);if(!o)return null;var i,u=o[0],d=a&&e.parsePatterns[a]||e.parsePatterns[e.defaultParseWidth],l=Array.isArray(d)?function(e,t){for(var n=0;n<e.length;n++)if(t(e[n]))return n;return}(d,(function(e){return e.test(u)})):function(e,t){for(var n in e)if(e.hasOwnProperty(n)&&t(e[n]))return n;return}(d,(function(e){return e.test(u)}));return i=e.valueCallback?e.valueCallback(l):l,{value:i=n.valueCallback?n.valueCallback(i):i,rest:t.slice(u.length)}}},e.exports=t.default},208:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},a=t.match(e.matchPattern);if(!a)return null;var r=a[0],o=t.match(e.parsePattern);if(!o)return null;var i=e.valueCallback?e.valueCallback(o[0]):o[0];return{value:i=n.valueCallback?n.valueCallback(i):i,rest:t.slice(r.length)}}},e.exports=t.default},514:function(e,t,n){"use strict";var a=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(515)),o=a(n(516)),i=a(n(517)),u=a(n(518)),d=a(n(519)),l={code:"ar",formatDistance:r.default,formatLong:o.default,formatRelative:i.default,localize:u.default,match:d.default,options:{weekStartsOn:6,firstWeekContainsDate:1}};t.default=l,e.exports=t.default},515:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={lessThanXSeconds:{one:"\u0623\u0642\u0644 \u0645\u0646 \u062b\u0627\u0646\u064a\u0629",two:"\u0623\u0642\u0644 \u0645\u0646 \u062b\u0627\u0646\u064a\u062a\u064a\u0646",threeToTen:"\u0623\u0642\u0644 \u0645\u0646 {{count}} \u062b\u0648\u0627\u0646\u064a",other:"\u0623\u0642\u0644 \u0645\u0646 {{count}} \u062b\u0627\u0646\u064a\u0629"},xSeconds:{one:"\u062b\u0627\u0646\u064a\u0629 \u0648\u0627\u062d\u062f\u0629",two:"\u062b\u0627\u0646\u064a\u062a\u0627\u0646",threeToTen:"{{count}} \u062b\u0648\u0627\u0646\u064a",other:"{{count}} \u062b\u0627\u0646\u064a\u0629"},halfAMinute:"\u0646\u0635\u0641 \u062f\u0642\u064a\u0642\u0629",lessThanXMinutes:{one:"\u0623\u0642\u0644 \u0645\u0646 \u062f\u0642\u064a\u0642\u0629",two:"\u0623\u0642\u0644 \u0645\u0646 \u062f\u0642\u064a\u0642\u062a\u064a\u0646",threeToTen:"\u0623\u0642\u0644 \u0645\u0646 {{count}} \u062f\u0642\u0627\u0626\u0642",other:"\u0623\u0642\u0644 \u0645\u0646 {{count}} \u062f\u0642\u064a\u0642\u0629"},xMinutes:{one:"\u062f\u0642\u064a\u0642\u0629 \u0648\u0627\u062d\u062f\u0629",two:"\u062f\u0642\u064a\u0642\u062a\u0627\u0646",threeToTen:"{{count}} \u062f\u0642\u0627\u0626\u0642",other:"{{count}} \u062f\u0642\u064a\u0642\u0629"},aboutXHours:{one:"\u0633\u0627\u0639\u0629 \u0648\u0627\u062d\u062f\u0629 \u062a\u0642\u0631\u064a\u0628\u0627\u064b",two:"\u0633\u0627\u0639\u062a\u064a\u0646 \u062a\u0642\u0631\u064a\u0628\u0627",threeToTen:"{{count}} \u0633\u0627\u0639\u0627\u062a \u062a\u0642\u0631\u064a\u0628\u0627\u064b",other:"{{count}} \u0633\u0627\u0639\u0629 \u062a\u0642\u0631\u064a\u0628\u0627\u064b"},xHours:{one:"\u0633\u0627\u0639\u0629 \u0648\u0627\u062d\u062f\u0629",two:"\u0633\u0627\u0639\u062a\u0627\u0646",threeToTen:"{{count}} \u0633\u0627\u0639\u0627\u062a",other:"{{count}} \u0633\u0627\u0639\u0629"},xDays:{one:"\u064a\u0648\u0645 \u0648\u0627\u062d\u062f",two:"\u064a\u0648\u0645\u0627\u0646",threeToTen:"{{count}} \u0623\u064a\u0627\u0645",other:"{{count}} \u064a\u0648\u0645"},aboutXWeeks:{one:"\u0623\u0633\u0628\u0648\u0639 \u0648\u0627\u062d\u062f \u062a\u0642\u0631\u064a\u0628\u0627",two:"\u0623\u0633\u0628\u0648\u0639\u064a\u0646 \u062a\u0642\u0631\u064a\u0628\u0627",threeToTen:"{{count}} \u0623\u0633\u0627\u0628\u064a\u0639 \u062a\u0642\u0631\u064a\u0628\u0627",other:"{{count}} \u0623\u0633\u0628\u0648\u0639\u0627 \u062a\u0642\u0631\u064a\u0628\u0627"},xWeeks:{one:"\u0623\u0633\u0628\u0648\u0639 \u0648\u0627\u062d\u062f",two:"\u0623\u0633\u0628\u0648\u0639\u0627\u0646",threeToTen:"{{count}} \u0623\u0633\u0627\u0628\u064a\u0639",other:"{{count}} \u0623\u0633\u0628\u0648\u0639\u0627"},aboutXMonths:{one:"\u0634\u0647\u0631 \u0648\u0627\u062d\u062f \u062a\u0642\u0631\u064a\u0628\u0627\u064b",two:"\u0634\u0647\u0631\u064a\u0646 \u062a\u0642\u0631\u064a\u0628\u0627",threeToTen:"{{count}} \u0623\u0634\u0647\u0631 \u062a\u0642\u0631\u064a\u0628\u0627",other:"{{count}} \u0634\u0647\u0631\u0627 \u062a\u0642\u0631\u064a\u0628\u0627\u064b"},xMonths:{one:"\u0634\u0647\u0631 \u0648\u0627\u062d\u062f",two:"\u0634\u0647\u0631\u0627\u0646",threeToTen:"{{count}} \u0623\u0634\u0647\u0631",other:"{{count}} \u0634\u0647\u0631\u0627"},aboutXYears:{one:"\u0633\u0646\u0629 \u0648\u0627\u062d\u062f\u0629 \u062a\u0642\u0631\u064a\u0628\u0627\u064b",two:"\u0633\u0646\u062a\u064a\u0646 \u062a\u0642\u0631\u064a\u0628\u0627",threeToTen:"{{count}} \u0633\u0646\u0648\u0627\u062a \u062a\u0642\u0631\u064a\u0628\u0627\u064b",other:"{{count}} \u0633\u0646\u0629 \u062a\u0642\u0631\u064a\u0628\u0627\u064b"},xYears:{one:"\u0633\u0646\u0629 \u0648\u0627\u062d\u062f",two:"\u0633\u0646\u062a\u0627\u0646",threeToTen:"{{count}} \u0633\u0646\u0648\u0627\u062a",other:"{{count}} \u0633\u0646\u0629"},overXYears:{one:"\u0623\u0643\u062b\u0631 \u0645\u0646 \u0633\u0646\u0629",two:"\u0623\u0643\u062b\u0631 \u0645\u0646 \u0633\u0646\u062a\u064a\u0646",threeToTen:"\u0623\u0643\u062b\u0631 \u0645\u0646 {{count}} \u0633\u0646\u0648\u0627\u062a",other:"\u0623\u0643\u062b\u0631 \u0645\u0646 {{count}} \u0633\u0646\u0629"},almostXYears:{one:"\u0645\u0627 \u064a\u0642\u0627\u0631\u0628 \u0633\u0646\u0629 \u0648\u0627\u062d\u062f\u0629",two:"\u0645\u0627 \u064a\u0642\u0627\u0631\u0628 \u0633\u0646\u062a\u064a\u0646",threeToTen:"\u0645\u0627 \u064a\u0642\u0627\u0631\u0628 {{count}} \u0633\u0646\u0648\u0627\u062a",other:"\u0645\u0627 \u064a\u0642\u0627\u0631\u0628 {{count}} \u0633\u0646\u0629"}},r=function(e,t,n){var r,o=a[e];return r="string"===typeof o?o:1===t?o.one:2===t?o.two:t<=10?o.threeToTen.replace("{{count}}",String(t)):o.other.replace("{{count}}",String(t)),null!==n&&void 0!==n&&n.addSuffix?n.comparison&&n.comparison>0?"\u062e\u0644\u0627\u0644 "+r:"\u0645\u0646\u0630 "+r:r};t.default=r,e.exports=t.default},516:function(e,t,n){"use strict";var a=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(205)),o={date:(0,r.default)({formats:{full:"EEEE\u060c do MMMM y",long:"do MMMM y",medium:"d MMM y",short:"dd/MM/yyyy"},defaultWidth:"full"}),time:(0,r.default)({formats:{full:"HH:mm:ss",long:"HH:mm:ss",medium:"HH:mm:ss",short:"HH:mm"},defaultWidth:"full"}),dateTime:(0,r.default)({formats:{full:"{{date}} '\u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' {{time}}",long:"{{date}} '\u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' {{time}}",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})};t.default=o,e.exports=t.default},517:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={lastWeek:"eeee '\u0627\u0644\u0645\u0627\u0636\u064a \u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' p",yesterday:"'\u0627\u0644\u0623\u0645\u0633 \u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' p",today:"'\u0627\u0644\u064a\u0648\u0645 \u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' p",tomorrow:"'\u063a\u062f\u0627 \u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' p",nextWeek:"eeee '\u0627\u0644\u0642\u0627\u062f\u0645 \u0639\u0646\u062f \u0627\u0644\u0633\u0627\u0639\u0629' p",other:"P"},r=function(e){return a[e]};t.default=r,e.exports=t.default},518:function(e,t,n){"use strict";var a=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(206)),o={ordinalNumber:function(e){return String(e)},era:(0,r.default)({values:{narrow:["\u0642","\u0628"],abbreviated:["\u0642.\u0645.","\u0628.\u0645."],wide:["\u0642\u0628\u0644 \u0627\u0644\u0645\u064a\u0644\u0627\u062f","\u0628\u0639\u062f \u0627\u0644\u0645\u064a\u0644\u0627\u062f"]},defaultWidth:"wide"}),quarter:(0,r.default)({values:{narrow:["1","2","3","4"],abbreviated:["\u06311","\u06312","\u06313","\u06314"],wide:["\u0627\u0644\u0631\u0628\u0639 \u0627\u0644\u0623\u0648\u0644","\u0627\u0644\u0631\u0628\u0639 \u0627\u0644\u062b\u0627\u0646\u064a","\u0627\u0644\u0631\u0628\u0639 \u0627\u0644\u062b\u0627\u0644\u062b","\u0627\u0644\u0631\u0628\u0639 \u0627\u0644\u0631\u0627\u0628\u0639"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,r.default)({values:{narrow:["\u064a","\u0641","\u0645","\u0623","\u0645","\u064a","\u064a","\u0623","\u0633","\u0623","\u0646","\u062f"],abbreviated:["\u064a\u0646\u0627\u064a\u0631","\u0641\u0628\u0631\u0627\u064a\u0631","\u0645\u0627\u0631\u0633","\u0623\u0628\u0631\u064a\u0644","\u0645\u0627\u064a\u0648","\u064a\u0648\u0646\u064a\u0648","\u064a\u0648\u0644\u064a\u0648","\u0623\u063a\u0633\u0637\u0633","\u0633\u0628\u062a\u0645\u0628\u0631","\u0623\u0643\u062a\u0648\u0628\u0631","\u0646\u0648\u0641\u0645\u0628\u0631","\u062f\u064a\u0633\u0645\u0628\u0631"],wide:["\u064a\u0646\u0627\u064a\u0631","\u0641\u0628\u0631\u0627\u064a\u0631","\u0645\u0627\u0631\u0633","\u0623\u0628\u0631\u064a\u0644","\u0645\u0627\u064a\u0648","\u064a\u0648\u0646\u064a\u0648","\u064a\u0648\u0644\u064a\u0648","\u0623\u063a\u0633\u0637\u0633","\u0633\u0628\u062a\u0645\u0628\u0631","\u0623\u0643\u062a\u0648\u0628\u0631","\u0646\u0648\u0641\u0645\u0628\u0631","\u062f\u064a\u0633\u0645\u0628\u0631"]},defaultWidth:"wide"}),day:(0,r.default)({values:{narrow:["\u062d","\u0646","\u062b","\u0631","\u062e","\u062c","\u0633"],short:["\u0623\u062d\u062f","\u0627\u062b\u0646\u064a\u0646","\u062b\u0644\u0627\u062b\u0627\u0621","\u0623\u0631\u0628\u0639\u0627\u0621","\u062e\u0645\u064a\u0633","\u062c\u0645\u0639\u0629","\u0633\u0628\u062a"],abbreviated:["\u0623\u062d\u062f","\u0627\u062b\u0646\u064a\u0646","\u062b\u0644\u0627\u062b\u0627\u0621","\u0623\u0631\u0628\u0639\u0627\u0621","\u062e\u0645\u064a\u0633","\u062c\u0645\u0639\u0629","\u0633\u0628\u062a"],wide:["\u0627\u0644\u0623\u062d\u062f","\u0627\u0644\u0627\u062b\u0646\u064a\u0646","\u0627\u0644\u062b\u0644\u0627\u062b\u0627\u0621","\u0627\u0644\u0623\u0631\u0628\u0639\u0627\u0621","\u0627\u0644\u062e\u0645\u064a\u0633","\u0627\u0644\u062c\u0645\u0639\u0629","\u0627\u0644\u0633\u0628\u062a"]},defaultWidth:"wide"}),dayPeriod:(0,r.default)({values:{narrow:{am:"\u0635",pm:"\u0645",morning:"\u0627\u0644\u0635\u0628\u0627\u062d",noon:"\u0627\u0644\u0638\u0647\u0631",afternoon:"\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631",evening:"\u0627\u0644\u0645\u0633\u0627\u0621",night:"\u0627\u0644\u0644\u064a\u0644",midnight:"\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644"},abbreviated:{am:"\u0635",pm:"\u0645",morning:"\u0627\u0644\u0635\u0628\u0627\u062d",noon:"\u0627\u0644\u0638\u0647\u0631",afternoon:"\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631",evening:"\u0627\u0644\u0645\u0633\u0627\u0621",night:"\u0627\u0644\u0644\u064a\u0644",midnight:"\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644"},wide:{am:"\u0635",pm:"\u0645",morning:"\u0627\u0644\u0635\u0628\u0627\u062d",noon:"\u0627\u0644\u0638\u0647\u0631",afternoon:"\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631",evening:"\u0627\u0644\u0645\u0633\u0627\u0621",night:"\u0627\u0644\u0644\u064a\u0644",midnight:"\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644"}},defaultWidth:"wide",formattingValues:{narrow:{am:"\u0635",pm:"\u0645",morning:"\u0641\u064a \u0627\u0644\u0635\u0628\u0627\u062d",noon:"\u0627\u0644\u0638\u0647\u0631",afternoon:"\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631",evening:"\u0641\u064a \u0627\u0644\u0645\u0633\u0627\u0621",night:"\u0641\u064a \u0627\u0644\u0644\u064a\u0644",midnight:"\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644"},abbreviated:{am:"\u0635",pm:"\u0645",morning:"\u0641\u064a \u0627\u0644\u0635\u0628\u0627\u062d",noon:"\u0627\u0644\u0638\u0647\u0631",afternoon:"\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631",evening:"\u0641\u064a \u0627\u0644\u0645\u0633\u0627\u0621",night:"\u0641\u064a \u0627\u0644\u0644\u064a\u0644",midnight:"\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644"},wide:{am:"\u0635",pm:"\u0645",morning:"\u0641\u064a \u0627\u0644\u0635\u0628\u0627\u062d",noon:"\u0627\u0644\u0638\u0647\u0631",afternoon:"\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631",evening:"\u0641\u064a \u0627\u0644\u0645\u0633\u0627\u0621",night:"\u0641\u064a \u0627\u0644\u0644\u064a\u0644",midnight:"\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644"}},defaultFormattingWidth:"wide"})};t.default=o,e.exports=t.default},519:function(e,t,n){"use strict";var a=n(203).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=a(n(208)),o=a(n(207)),i={ordinalNumber:(0,r.default)({matchPattern:/^(\d+)(th|st|nd|rd)?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,o.default)({matchPatterns:{narrow:/[\u0642\u0628]/,abbreviated:/[\u0642\u0628]\.\u0645\./,wide:/(\u0642\u0628\u0644|\u0628\u0639\u062f) \u0627\u0644\u0645\u064a\u0644\u0627\u062f/},defaultMatchWidth:"wide",parsePatterns:{any:[/\u0642\u0628\u0644/,/\u0628\u0639\u062f/]},defaultParseWidth:"any"}),quarter:(0,o.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/\u0631[1234]/,wide:/\u0627\u0644\u0631\u0628\u0639 (\u0627\u0644\u0623\u0648\u0644|\u0627\u0644\u062b\u0627\u0646\u064a|\u0627\u0644\u062b\u0627\u0644\u062b|\u0627\u0644\u0631\u0627\u0628\u0639)/},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,o.default)({matchPatterns:{narrow:/^[\u0623\u064a\u0641\u0645\u0633\u0646\u062f]/,abbreviated:/^(\u064a\u0646\u0627\u064a\u0631|\u0641\u0628\u0631\u0627\u064a\u0631|\u0645\u0627\u0631\u0633|\u0623\u0628\u0631\u064a\u0644|\u0645\u0627\u064a\u0648|\u064a\u0648\u0646\u064a\u0648|\u064a\u0648\u0644\u064a\u0648|\u0623\u063a\u0633\u0637\u0633|\u0633\u0628\u062a\u0645\u0628\u0631|\u0623\u0643\u062a\u0648\u0628\u0631|\u0646\u0648\u0641\u0645\u0628\u0631|\u062f\u064a\u0633\u0645\u0628\u0631)/,wide:/^(\u064a\u0646\u0627\u064a\u0631|\u0641\u0628\u0631\u0627\u064a\u0631|\u0645\u0627\u0631\u0633|\u0623\u0628\u0631\u064a\u0644|\u0645\u0627\u064a\u0648|\u064a\u0648\u0646\u064a\u0648|\u064a\u0648\u0644\u064a\u0648|\u0623\u063a\u0633\u0637\u0633|\u0633\u0628\u062a\u0645\u0628\u0631|\u0623\u0643\u062a\u0648\u0628\u0631|\u0646\u0648\u0641\u0645\u0628\u0631|\u062f\u064a\u0633\u0645\u0628\u0631)/},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u064a/i,/^\u0641/i,/^\u0645/i,/^\u0623/i,/^\u0645/i,/^\u064a/i,/^\u064a/i,/^\u0623/i,/^\u0633/i,/^\u0623/i,/^\u0646/i,/^\u062f/i],any:[/^\u064a\u0646\u0627\u064a\u0631/i,/^\u0641\u0628\u0631\u0627\u064a\u0631/i,/^\u0645\u0627\u0631\u0633/i,/^\u0623\u0628\u0631\u064a\u0644/i,/^\u0645\u0627\u064a\u0648/i,/^\u064a\u0648\u0646\u064a\u0648/i,/^\u064a\u0648\u0644\u064a\u0648/i,/^\u0623\u063a\u0633\u0637\u0633/i,/^\u0633\u0628\u062a\u0645\u0628\u0631/i,/^\u0623\u0643\u062a\u0648\u0628\u0631/i,/^\u0646\u0648\u0641\u0645\u0628\u0631/i,/^\u062f\u064a\u0633\u0645\u0628\u0631/i]},defaultParseWidth:"any"}),day:(0,o.default)({matchPatterns:{narrow:/^[\u062d\u0646\u062b\u0631\u062e\u062c\u0633]/i,short:/^(\u0623\u062d\u062f|\u0627\u062b\u0646\u064a\u0646|\u062b\u0644\u0627\u062b\u0627\u0621|\u0623\u0631\u0628\u0639\u0627\u0621|\u062e\u0645\u064a\u0633|\u062c\u0645\u0639\u0629|\u0633\u0628\u062a)/i,abbreviated:/^(\u0623\u062d\u062f|\u0627\u062b\u0646\u064a\u0646|\u062b\u0644\u0627\u062b\u0627\u0621|\u0623\u0631\u0628\u0639\u0627\u0621|\u062e\u0645\u064a\u0633|\u062c\u0645\u0639\u0629|\u0633\u0628\u062a)/i,wide:/^(\u0627\u0644\u0623\u062d\u062f|\u0627\u0644\u0627\u062b\u0646\u064a\u0646|\u0627\u0644\u062b\u0644\u0627\u062b\u0627\u0621|\u0627\u0644\u0623\u0631\u0628\u0639\u0627\u0621|\u0627\u0644\u062e\u0645\u064a\u0633|\u0627\u0644\u062c\u0645\u0639\u0629|\u0627\u0644\u0633\u0628\u062a)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u062d/i,/^\u0646/i,/^\u062b/i,/^\u0631/i,/^\u062e/i,/^\u062c/i,/^\u0633/i],wide:[/^\u0627\u0644\u0623\u062d\u062f/i,/^\u0627\u0644\u0627\u062b\u0646\u064a\u0646/i,/^\u0627\u0644\u062b\u0644\u0627\u062b\u0627\u0621/i,/^\u0627\u0644\u0623\u0631\u0628\u0639\u0627\u0621/i,/^\u0627\u0644\u062e\u0645\u064a\u0633/i,/^\u0627\u0644\u062c\u0645\u0639\u0629/i,/^\u0627\u0644\u0633\u0628\u062a/i],any:[/^\u0623\u062d/i,/^\u0627\u062b/i,/^\u062b/i,/^\u0623\u0631/i,/^\u062e/i,/^\u062c/i,/^\u0633/i]},defaultParseWidth:"any"}),dayPeriod:(0,o.default)({matchPatterns:{narrow:/^(\u0635|\u0645|\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644|\u0627\u0644\u0638\u0647\u0631|\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631|\u0641\u064a \u0627\u0644\u0635\u0628\u0627\u062d|\u0641\u064a \u0627\u0644\u0645\u0633\u0627\u0621|\u0641\u064a \u0627\u0644\u0644\u064a\u0644)/,any:/^(\u0635|\u0645|\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644|\u0627\u0644\u0638\u0647\u0631|\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631|\u0641\u064a \u0627\u0644\u0635\u0628\u0627\u062d|\u0641\u064a \u0627\u0644\u0645\u0633\u0627\u0621|\u0641\u064a \u0627\u0644\u0644\u064a\u0644)/},defaultMatchWidth:"any",parsePatterns:{any:{am:/^\u0635/,pm:/^\u0645/,midnight:/\u0645\u0646\u062a\u0635\u0641 \u0627\u0644\u0644\u064a\u0644/,noon:/\u0627\u0644\u0638\u0647\u0631/,afternoon:/\u0628\u0639\u062f \u0627\u0644\u0638\u0647\u0631/,morning:/\u0641\u064a \u0627\u0644\u0635\u0628\u0627\u062d/,evening:/\u0641\u064a \u0627\u0644\u0645\u0633\u0627\u0621/,night:/\u0641\u064a \u0627\u0644\u0644\u064a\u0644/}},defaultParseWidth:"any"})};t.default=i,e.exports=t.default}}]);