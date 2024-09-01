(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[35],{202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var n;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var r=e.defaultFormattingWidth||e.defaultWidth,i=null!==a&&void 0!==a&&a.width?String(a.width):r;n=e.formattingValues[i]||e.formattingValues[r]}else{var o=e.defaultWidth,d=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;n=e.values[d]||e.values[o]}return n[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=a.width,r=n&&e.matchPatterns[n]||e.matchPatterns[e.defaultMatchWidth],i=t.match(r);if(!i)return null;var o,d=i[0],u=n&&e.parsePatterns[n]||e.parsePatterns[e.defaultParseWidth],s=Array.isArray(u)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(u,(function(e){return e.test(d)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(u,(function(e){return e.test(d)}));return o=e.valueCallback?e.valueCallback(s):s,{value:o=a.valueCallback?a.valueCallback(o):o,rest:t.slice(d.length)}}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=t.match(e.matchPattern);if(!n)return null;var r=n[0],i=t.match(e.parsePattern);if(!i)return null;var o=e.valueCallback?e.valueCallback(i[0]):i[0];return{value:o=a.valueCallback?a.valueCallback(o):o,rest:t.slice(r.length)}}},e.exports=t.default},866:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(867)),i=n(a(868)),o=n(a(869)),d=n(a(870)),u=n(a(871)),s={code:"fy",formatDistance:r.default,formatLong:i.default,formatRelative:o.default,localize:d.default,match:u.default,options:{weekStartsOn:1,firstWeekContainsDate:4}};t.default=s,e.exports=t.default},867:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={lessThanXSeconds:{one:"minder as 1 sekonde",other:"minder as {{count}} sekonden"},xSeconds:{one:"1 sekonde",other:"{{count}} sekonden"},halfAMinute:"oardel min\xfat",lessThanXMinutes:{one:"minder as 1 min\xfat",other:"minder as {{count}} minuten"},xMinutes:{one:"1 min\xfat",other:"{{count}} minuten"},aboutXHours:{one:"sawat 1 oere",other:"sawat {{count}} oere"},xHours:{one:"1 oere",other:"{{count}} oere"},xDays:{one:"1 dei",other:"{{count}} dagen"},aboutXWeeks:{one:"sawat 1 wike",other:"sawat {{count}} wiken"},xWeeks:{one:"1 wike",other:"{{count}} wiken"},aboutXMonths:{one:"sawat 1 moanne",other:"sawat {{count}} moannen"},xMonths:{one:"1 moanne",other:"{{count}} moannen"},aboutXYears:{one:"sawat 1 jier",other:"sawat {{count}} jier"},xYears:{one:"1 jier",other:"{{count}} jier"},overXYears:{one:"mear as 1 jier",other:"mear as {{count}}s jier"},almostXYears:{one:"hast 1 jier",other:"hast {{count}} jier"}},r=function(e,t,a){var r,i=n[e];return r="string"===typeof i?i:1===t?i.one:i.other.replace("{{count}}",String(t)),null!==a&&void 0!==a&&a.addSuffix?a.comparison&&a.comparison>0?"oer "+r:r+" lyn":r};t.default=r,e.exports=t.default},868:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(204)),i={date:(0,r.default)({formats:{full:"EEEE d MMMM y",long:"d MMMM y",medium:"d MMM y",short:"dd-MM-y"},defaultWidth:"full"}),time:(0,r.default)({formats:{full:"HH:mm:ss zzzz",long:"HH:mm:ss z",medium:"HH:mm:ss",short:"HH:mm"},defaultWidth:"full"}),dateTime:(0,r.default)({formats:{full:"{{date}} 'om' {{time}}",long:"{{date}} 'om' {{time}}",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})};t.default=i,e.exports=t.default},869:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n={lastWeek:"'\xf4fr\xfbne' eeee 'om' p",yesterday:"'juster om' p",today:"'hjoed om' p",tomorrow:"'moarn om' p",nextWeek:"eeee 'om' p",other:"P"},r=function(e,t,a,r){return n[e]};t.default=r,e.exports=t.default},870:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(205)),i={ordinalNumber:function(e,t){return Number(e)+"e"},era:(0,r.default)({values:{narrow:["f.K.","n.K."],abbreviated:["f.Kr.","n.Kr."],wide:["foar Kristus","nei Kristus"]},defaultWidth:"wide"}),quarter:(0,r.default)({values:{narrow:["1","2","3","4"],abbreviated:["K1","K2","K3","K4"],wide:["1e fearnsjier","2e fearnsjier","3e fearnsjier","4e fearnsjier"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,r.default)({values:{narrow:["j","f","m","a","m","j","j","a","s","o","n","d"],abbreviated:["jan.","feb.","mrt.","apr.","mai.","jun.","jul.","aug.","sep.","okt.","nov.","des."],wide:["jannewaris","febrewaris","maart","april","maaie","juny","july","augustus","septimber","oktober","novimber","desimber"]},defaultWidth:"wide"}),day:(0,r.default)({values:{narrow:["s","m","t","w","t","f","s"],short:["si","mo","ti","wo","to","fr","so"],abbreviated:["snein","moa","tii","woa","ton","fre","sneon"],wide:["snein","moandei","tiisdei","woansdei","tongersdei","freed","sneon"]},defaultWidth:"wide"}),dayPeriod:(0,r.default)({values:{narrow:{am:"AM",pm:"PM",midnight:"middernacht",noon:"middei",morning:"moarns",afternoon:"middeis",evening:"j\xfbns",night:"nachts"},abbreviated:{am:"AM",pm:"PM",midnight:"middernacht",noon:"middei",morning:"moarns",afternoon:"middeis",evening:"j\xfbns",night:"nachts"},wide:{am:"AM",pm:"PM",midnight:"middernacht",noon:"middei",morning:"moarns",afternoon:"middeis",evening:"j\xfbns",night:"nachts"}},defaultWidth:"wide"})};t.default=i,e.exports=t.default},871:function(e,t,a){"use strict";var n=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=n(a(206)),i={ordinalNumber:(0,n(a(207)).default)({matchPattern:/^(\d+)e?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,r.default)({matchPatterns:{narrow:/^([fn]\.? ?K\.?)/,abbreviated:/^([fn]\. ?Kr\.?)/,wide:/^((foar|nei) Kristus)/},defaultMatchWidth:"wide",parsePatterns:{any:[/^f/,/^n/]},defaultParseWidth:"any"}),quarter:(0,r.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^K[1234]/i,wide:/^[1234]e fearnsjier/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,r.default)({matchPatterns:{narrow:/^[jfmasond]/i,abbreviated:/^(jan.|feb.|mrt.|apr.|mai.|jun.|jul.|aug.|sep.|okt.|nov.|des.)/i,wide:/^(jannewaris|febrewaris|maart|april|maaie|juny|july|augustus|septimber|oktober|novimber|desimber)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^j/i,/^f/i,/^m/i,/^a/i,/^m/i,/^j/i,/^j/i,/^a/i,/^s/i,/^o/i,/^n/i,/^d/i],any:[/^jan/i,/^feb/i,/^m(r|a)/i,/^apr/i,/^mai/i,/^jun/i,/^jul/i,/^aug/i,/^sep/i,/^okt/i,/^nov/i,/^des/i]},defaultParseWidth:"any"}),day:(0,r.default)({matchPatterns:{narrow:/^[smtwf]/i,short:/^(si|mo|ti|wo|to|fr|so)/i,abbreviated:/^(snein|moa|tii|woa|ton|fre|sneon)/i,wide:/^(snein|moandei|tiisdei|woansdei|tongersdei|freed|sneon)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^s/i,/^m/i,/^t/i,/^w/i,/^t/i,/^f/i,/^s/i],any:[/^sn/i,/^mo/i,/^ti/i,/^wo/i,/^to/i,/^fr/i,/^sn/i]},defaultParseWidth:"any"}),dayPeriod:(0,r.default)({matchPatterns:{any:/^(am|pm|middernacht|middeis|moarns|middei|j\xfbns|nachts)/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^am/i,pm:/^pm/i,midnight:/^middernacht/i,noon:/^middei/i,morning:/moarns/i,afternoon:/^middeis/i,evening:/j\xfbns/i,night:/nachts/i}},defaultParseWidth:"any"})};t.default=i,e.exports=t.default}}]);
//# sourceMappingURL=df-37.3199e1f1.chunk.js.map