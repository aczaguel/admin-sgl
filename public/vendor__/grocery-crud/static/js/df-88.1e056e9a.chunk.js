(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[89],{1163:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(1164)),i=r(a(1165)),u=r(a(1166)),o=r(a(1167)),d=r(a(1168)),l={code:"uz-Cyrl",formatDistance:n.default,formatLong:i.default,formatRelative:u.default,localize:o.default,match:d.default,options:{weekStartsOn:1,firstWeekContainsDate:1}};t.default=l,e.exports=t.default},1164:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={lessThanXSeconds:{one:"1 \u0441\u043e\u043d\u0438\u044f\u0434\u0430\u043d \u043a\u0430\u043c",other:"{{count}} \u0441\u043e\u043d\u0438\u044f\u0434\u0430\u043d \u043a\u0430\u043c"},xSeconds:{one:"1 \u0441\u043e\u043d\u0438\u044f",other:"{{count}} \u0441\u043e\u043d\u0438\u044f"},halfAMinute:"\u044f\u0440\u0438\u043c \u0434\u0430\u049b\u0438\u049b\u0430",lessThanXMinutes:{one:"1 \u0434\u0430\u049b\u0438\u049b\u0430\u0434\u0430\u043d \u043a\u0430\u043c",other:"{{count}} \u0434\u0430\u049b\u0438\u049b\u0430\u0434\u0430\u043d \u043a\u0430\u043c"},xMinutes:{one:"1 \u0434\u0430\u049b\u0438\u049b\u0430",other:"{{count}} \u0434\u0430\u049b\u0438\u049b\u0430"},aboutXHours:{one:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d 1 \u0441\u043e\u0430\u0442",other:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d {{count}} \u0441\u043e\u0430\u0442"},xHours:{one:"1 \u0441\u043e\u0430\u0442",other:"{{count}} \u0441\u043e\u0430\u0442"},xDays:{one:"1 \u043a\u0443\u043d",other:"{{count}} \u043a\u0443\u043d"},aboutXWeeks:{one:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d 1 \u0445\u0430\u0444\u0442\u0430",other:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d {{count}} \u0445\u0430\u0444\u0442\u0430"},xWeeks:{one:"1 \u0445\u0430\u0444\u0442\u0430",other:"{{count}} \u0445\u0430\u0444\u0442\u0430"},aboutXMonths:{one:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d 1 \u043e\u0439",other:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d {{count}} \u043e\u0439"},xMonths:{one:"1 \u043e\u0439",other:"{{count}} \u043e\u0439"},aboutXYears:{one:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d 1 \u0439\u0438\u043b",other:"\u0442\u0430\u0445\u043c\u0438\u043d\u0430\u043d {{count}} \u0439\u0438\u043b"},xYears:{one:"1 \u0439\u0438\u043b",other:"{{count}} \u0439\u0438\u043b"},overXYears:{one:"1 \u0439\u0438\u043b\u0434\u0430\u043d \u043a\u045e\u043f",other:"{{count}} \u0439\u0438\u043b\u0434\u0430\u043d \u043a\u045e\u043f"},almostXYears:{one:"\u0434\u0435\u044f\u0440\u043b\u0438 1 \u0439\u0438\u043b",other:"\u0434\u0435\u044f\u0440\u043b\u0438 {{count}} \u0439\u0438\u043b"}},n=function(e,t,a){var n,i=r[e];return n="string"===typeof i?i:1===t?i.one:i.other.replace("{{count}}",String(t)),null!==a&&void 0!==a&&a.addSuffix?a.comparison&&a.comparison>0?n+"\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d":n+" \u043e\u043b\u0434\u0438\u043d":n};t.default=n,e.exports=t.default},1165:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(204)),i={date:(0,n.default)({formats:{full:"EEEE, do MMMM, y",long:"do MMMM, y",medium:"d MMM, y",short:"dd/MM/yyyy"},defaultWidth:"full"}),time:(0,n.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,n.default)({formats:{any:"{{date}}, {{time}}"},defaultWidth:"any"})};t.default=i,e.exports=t.default},1166:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={lastWeek:"'\u045e\u0442\u0433\u0430\u043d' eeee p '\u0434\u0430'",yesterday:"'\u043a\u0435\u0447\u0430' p '\u0434\u0430'",today:"'\u0431\u0443\u0433\u0443\u043d' p '\u0434\u0430'",tomorrow:"'\u044d\u0440\u0442\u0430\u0433\u0430' p '\u0434\u0430'",nextWeek:"eeee p '\u0434\u0430'",other:"P"},n=function(e,t,a,n){return r[e]};t.default=n,e.exports=t.default},1167:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(205)),i={ordinalNumber:function(e,t){return String(e)},era:(0,n.default)({values:{narrow:["\u041c.\u0410","\u041c"],abbreviated:["\u041c.\u0410","\u041c"],wide:["\u041c\u0438\u043b\u043e\u0434\u0434\u0430\u043d \u0410\u0432\u0432\u0430\u043b\u0433\u0438","\u041c\u0438\u043b\u043e\u0434\u0438\u0439"]},defaultWidth:"wide"}),quarter:(0,n.default)({values:{narrow:["1","2","3","4"],abbreviated:["1-\u0447\u043e\u0440.","2-\u0447\u043e\u0440.","3-\u0447\u043e\u0440.","4-\u0447\u043e\u0440."],wide:["1-\u0447\u043e\u0440\u0430\u043a","2-\u0447\u043e\u0440\u0430\u043a","3-\u0447\u043e\u0440\u0430\u043a","4-\u0447\u043e\u0440\u0430\u043a"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,n.default)({values:{narrow:["\u042f","\u0424","\u041c","\u0410","\u041c","\u0418","\u0418","\u0410","\u0421","\u041e","\u041d","\u0414"],abbreviated:["\u044f\u043d\u0432","\u0444\u0435\u0432","\u043c\u0430\u0440","\u0430\u043f\u0440","\u043c\u0430\u0439","\u0438\u044e\u043d","\u0438\u044e\u043b","\u0430\u0432\u0433","\u0441\u0435\u043d","\u043e\u043a\u0442","\u043d\u043e\u044f","\u0434\u0435\u043a"],wide:["\u044f\u043d\u0432\u0430\u0440","\u0444\u0435\u0432\u0440\u0430\u043b","\u043c\u0430\u0440\u0442","\u0430\u043f\u0440\u0435\u043b","\u043c\u0430\u0439","\u0438\u044e\u043d","\u0438\u044e\u043b","\u0430\u0432\u0433\u0443\u0441\u0442","\u0441\u0435\u043d\u0442\u0430\u0431\u0440","\u043e\u043a\u0442\u0430\u0431\u0440","\u043d\u043e\u044f\u0431\u0440","\u0434\u0435\u043a\u0430\u0431\u0440"]},defaultWidth:"wide"}),day:(0,n.default)({values:{narrow:["\u042f","\u0414","\u0421","\u0427","\u041f","\u0416","\u0428"],short:["\u044f\u043a","\u0434\u0443","\u0441\u0435","\u0447\u043e","\u043f\u0430","\u0436\u0443","\u0448\u0430"],abbreviated:["\u044f\u043a\u0448","\u0434\u0443\u0448","\u0441\u0435\u0448","\u0447\u043e\u0440","\u043f\u0430\u0439","\u0436\u0443\u043c","\u0448\u0430\u043d"],wide:["\u044f\u043a\u0448\u0430\u043d\u0431\u0430","\u0434\u0443\u0448\u0430\u043d\u0431\u0430","\u0441\u0435\u0448\u0430\u043d\u0431\u0430","\u0447\u043e\u0440\u0448\u0430\u043d\u0431\u0430","\u043f\u0430\u0439\u0448\u0430\u043d\u0431\u0430","\u0436\u0443\u043c\u0430","\u0448\u0430\u043d\u0431\u0430"]},defaultWidth:"wide"}),dayPeriod:(0,n.default)({values:{any:{am:"\u041f.\u041e.",pm:"\u041f.\u041a.",midnight:"\u044f\u0440\u0438\u043c \u0442\u0443\u043d",noon:"\u043f\u0435\u0448\u0438\u043d",morning:"\u044d\u0440\u0442\u0430\u043b\u0430\u0431",afternoon:"\u043f\u0435\u0448\u0438\u043d\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d",evening:"\u043a\u0435\u0447\u0430\u0441\u0438",night:"\u0442\u0443\u043d"}},defaultWidth:"any",formattingValues:{any:{am:"\u041f.\u041e.",pm:"\u041f.\u041a.",midnight:"\u044f\u0440\u0438\u043c \u0442\u0443\u043d",noon:"\u043f\u0435\u0448\u0438\u043d",morning:"\u044d\u0440\u0442\u0430\u043b\u0430\u0431",afternoon:"\u043f\u0435\u0448\u0438\u043d\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d",evening:"\u043a\u0435\u0447\u0430\u0441\u0438",night:"\u0442\u0443\u043d"}},defaultFormattingWidth:"any"})};t.default=i,e.exports=t.default},1168:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(206)),i={ordinalNumber:(0,r(a(207)).default)({matchPattern:/^(\d+)(\u0447\u0438)?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,n.default)({matchPatterns:{narrow:/^(\u043c\.\u0430|\u043c\.)/i,abbreviated:/^(\u043c\.\u0430|\u043c\.)/i,wide:/^(\u043c\u0438\u043b\u043e\u0434\u0434\u0430\u043d \u0430\u0432\u0432\u0430\u043b|\u043c\u0438\u043b\u043e\u0434\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^\u043c/i,/^\u0430/i]},defaultParseWidth:"any"}),quarter:(0,n.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^[1234]-\u0447\u043e\u0440./i,wide:/^[1234]-\u0447\u043e\u0440\u0430\u043a/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,n.default)({matchPatterns:{narrow:/^[\u044f\u0444\u043c\u0430\u043c\u0438\u0438\u0430\u0441\u043e\u043d\u0434]/i,abbreviated:/^(\u044f\u043d\u0432|\u0444\u0435\u0432|\u043c\u0430\u0440|\u0430\u043f\u0440|\u043c\u0430\u0439|\u0438\u044e\u043d|\u0438\u044e\u043b|\u0430\u0432\u0433|\u0441\u0435\u043d|\u043e\u043a\u0442|\u043d\u043e\u044f|\u0434\u0435\u043a)/i,wide:/^(\u044f\u043d\u0432\u0430\u0440|\u0444\u0435\u0432\u0440\u0430\u043b|\u043c\u0430\u0440\u0442|\u0430\u043f\u0440\u0435\u043b|\u043c\u0430\u0439|\u0438\u044e\u043d|\u0438\u044e\u043b|\u0430\u0432\u0433\u0443\u0441\u0442|\u0441\u0435\u043d\u0442\u0430\u0431\u0440|\u043e\u043a\u0442\u0430\u0431\u0440|\u043d\u043e\u044f\u0431\u0440|\u0434\u0435\u043a\u0430\u0431\u0440)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u044f/i,/^\u0444/i,/^\u043c/i,/^\u0430/i,/^\u043c/i,/^\u0438/i,/^\u0438/i,/^\u0430/i,/^\u0441/i,/^\u043e/i,/^\u043d/i,/^\u0434/i],any:[/^\u044f/i,/^\u0444/i,/^\u043c\u0430\u0440/i,/^\u0430\u043f/i,/^\u043c\u0430\u0439/i,/^\u0438\u044e\u043d/i,/^\u0438\u044e\u043b/i,/^\u0430\u0432/i,/^\u0441/i,/^\u043e/i,/^\u043d/i,/^\u0434/i]},defaultParseWidth:"any"}),day:(0,n.default)({matchPatterns:{narrow:/^[\u044f\u0434\u0441\u0447\u043f\u0436\u0448]/i,short:/^(\u044f\u043a|\u0434\u0443|\u0441\u0435|\u0447\u043e|\u043f\u0430|\u0436\u0443|\u0448\u0430)/i,abbreviated:/^(\u044f\u043a\u0448|\u0434\u0443\u0448|\u0441\u0435\u0448|\u0447\u043e\u0440|\u043f\u0430\u0439|\u0436\u0443\u043c|\u0448\u0430\u043d)/i,wide:/^(\u044f\u043a\u0448\u0430\u043d\u0431\u0430|\u0434\u0443\u0448\u0430\u043d\u0431\u0430|\u0441\u0435\u0448\u0430\u043d\u0431\u0430|\u0447\u043e\u0440\u0448\u0430\u043d\u0431\u0430|\u043f\u0430\u0439\u0448\u0430\u043d\u0431\u0430|\u0436\u0443\u043c\u0430|\u0448\u0430\u043d\u0431\u0430)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u044f/i,/^\u0434/i,/^\u0441/i,/^\u0447/i,/^\u043f/i,/^\u0436/i,/^\u0448/i],any:[/^\u044f\u043a/i,/^\u0434\u0443/i,/^\u0441\u0435/i,/^\u0447\u043e\u0440/i,/^\u043f\u0430\u0439/i,/^\u0436\u0443/i,/^\u0448\u0430\u043d/i]},defaultParseWidth:"any"}),dayPeriod:(0,n.default)({matchPatterns:{any:/^(\u043f\.\u043e\.|\u043f\.\u043a\.|\u044f\u0440\u0438\u043c \u0442\u0443\u043d|\u043f\u0435\u0448\u0438\u043d\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d|(\u044d\u0440\u0442\u0430\u043b\u0430\u0431|\u043f\u0435\u0448\u0438\u043d\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d|\u043a\u0435\u0447\u0430\u0441\u0438|\u0442\u0443\u043d))/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^\u043f\.\u043e\./i,pm:/^\u043f\.\u043a\./i,midnight:/^\u044f\u0440\u0438\u043c \u0442\u0443\u043d/i,noon:/^\u043f\u0435\u0448\u0438\u043d\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d/i,morning:/\u044d\u0440\u0442\u0430\u043b\u0430\u0431/i,afternoon:/\u043f\u0435\u0448\u0438\u043d\u0434\u0430\u043d \u043a\u0435\u0439\u0438\u043d/i,evening:/\u043a\u0435\u0447\u0430\u0441\u0438/i,night:/\u0442\u0443\u043d/i}},defaultParseWidth:"any"})};t.default=i,e.exports=t.default},202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var r;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var n=e.defaultFormattingWidth||e.defaultWidth,i=null!==a&&void 0!==a&&a.width?String(a.width):n;r=e.formattingValues[i]||e.formattingValues[n]}else{var u=e.defaultWidth,o=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;r=e.values[o]||e.values[u]}return r[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=a.width,n=r&&e.matchPatterns[r]||e.matchPatterns[e.defaultMatchWidth],i=t.match(n);if(!i)return null;var u,o=i[0],d=r&&e.parsePatterns[r]||e.parsePatterns[e.defaultParseWidth],l=Array.isArray(d)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(d,(function(e){return e.test(o)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(d,(function(e){return e.test(o)}));return u=e.valueCallback?e.valueCallback(l):l,{value:u=a.valueCallback?a.valueCallback(u):u,rest:t.slice(o.length)}}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=t.match(e.matchPattern);if(!r)return null;var n=r[0],i=t.match(e.parsePattern);if(!i)return null;var u=e.valueCallback?e.valueCallback(i[0]):i[0];return{value:u=a.valueCallback?a.valueCallback(u):u,rest:t.slice(n.length)}}},e.exports=t.default}}]);
//# sourceMappingURL=df-88.1e056e9a.chunk.js.map