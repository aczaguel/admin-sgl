(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[77],{1097:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(1098)),i=r(a(1099)),o=r(a(1100)),u=r(a(1101)),d=r(a(1102)),s={code:"sq",formatDistance:n.default,formatLong:i.default,formatRelative:o.default,localize:u.default,match:d.default,options:{weekStartsOn:1,firstWeekContainsDate:1}};t.default=s,e.exports=t.default},1098:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={lessThanXSeconds:{one:"m\xeb pak se nj\xeb sekond\xeb",other:"m\xeb pak se {{count}} sekonda"},xSeconds:{one:"1 sekond\xeb",other:"{{count}} sekonda"},halfAMinute:"gjys\xebm minuti",lessThanXMinutes:{one:"m\xeb pak se nj\xeb minute",other:"m\xeb pak se {{count}} minuta"},xMinutes:{one:"1 minut\xeb",other:"{{count}} minuta"},aboutXHours:{one:"rreth 1 or\xeb",other:"rreth {{count}} or\xeb"},xHours:{one:"1 or\xeb",other:"{{count}} or\xeb"},xDays:{one:"1 dit\xeb",other:"{{count}} dit\xeb"},aboutXWeeks:{one:"rreth 1 jav\xeb",other:"rreth {{count}} jav\xeb"},xWeeks:{one:"1 jav\xeb",other:"{{count}} jav\xeb"},aboutXMonths:{one:"rreth 1 muaj",other:"rreth {{count}} muaj"},xMonths:{one:"1 muaj",other:"{{count}} muaj"},aboutXYears:{one:"rreth 1 vit",other:"rreth {{count}} vite"},xYears:{one:"1 vit",other:"{{count}} vite"},overXYears:{one:"mbi 1 vit",other:"mbi {{count}} vite"},almostXYears:{one:"pothuajse 1 vit",other:"pothuajse {{count}} vite"}},n=function(e,t,a){var n,i=r[e];return n="string"===typeof i?i:1===t?i.one:i.other.replace("{{count}}",String(t)),null!==a&&void 0!==a&&a.addSuffix?a.comparison&&a.comparison>0?"n\xeb "+n:n+" m\xeb par\xeb":n};t.default=n,e.exports=t.default},1099:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(204)),i={date:(0,n.default)({formats:{full:"EEEE, MMMM do, y",long:"MMMM do, y",medium:"MMM d, y",short:"MM/dd/yyyy"},defaultWidth:"full"}),time:(0,n.default)({formats:{full:"h:mm:ss a zzzz",long:"h:mm:ss a z",medium:"h:mm:ss a",short:"h:mm a"},defaultWidth:"full"}),dateTime:(0,n.default)({formats:{full:"{{date}} 'n\xeb' {{time}}",long:"{{date}} 'n\xeb' {{time}}",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})};t.default=i,e.exports=t.default},1100:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={lastWeek:"'t\xeb' eeee 'e shkuar n\xeb' p",yesterday:"'dje n\xeb' p",today:"'sot n\xeb' p",tomorrow:"'nes\xebr n\xeb' p",nextWeek:"eeee 'at' p",other:"P"},n=function(e,t,a,n){return r[e]};t.default=n,e.exports=t.default},1101:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(205)),i={ordinalNumber:function(e,t){var a=Number(e);return"hour"===(null===t||void 0===t?void 0:t.unit)?String(a):1===a?a+"-r\xeb":4===a?a+"t":a+"-t\xeb"},era:(0,n.default)({values:{narrow:["P","M"],abbreviated:["PK","MK"],wide:["Para Krishtit","Mbas Krishtit"]},defaultWidth:"wide"}),quarter:(0,n.default)({values:{narrow:["1","2","3","4"],abbreviated:["Q1","Q2","Q3","Q4"],wide:["4-mujori I","4-mujori II","4-mujori III","4-mujori IV"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,n.default)({values:{narrow:["J","S","M","P","M","Q","K","G","S","T","N","D"],abbreviated:["Jan","Shk","Mar","Pri","Maj","Qer","Kor","Gus","Sht","Tet","N\xebn","Dhj"],wide:["Janar","Shkurt","Mars","Prill","Maj","Qershor","Korrik","Gusht","Shtator","Tetor","N\xebntor","Dhjetor"]},defaultWidth:"wide"}),day:(0,n.default)({values:{narrow:["D","H","M","M","E","P","S"],short:["Di","H\xeb","Ma","M\xeb","En","Pr","Sh"],abbreviated:["Die","H\xebn","Mar","M\xebr","Enj","Pre","Sht"],wide:["Diel\xeb","H\xebn\xeb","Mart\xeb","M\xebrkur\xeb","Enjte","Premte","Shtun\xeb"]},defaultWidth:"wide"}),dayPeriod:(0,n.default)({values:{narrow:{am:"p",pm:"m",midnight:"m",noon:"d",morning:"m\xebngjes",afternoon:"dite",evening:"mbr\xebmje",night:"nat\xeb"},abbreviated:{am:"PD",pm:"MD",midnight:"mesn\xebt\xeb",noon:"drek",morning:"m\xebngjes",afternoon:"mbasdite",evening:"mbr\xebmje",night:"nat\xeb"},wide:{am:"p.d.",pm:"m.d.",midnight:"mesn\xebt\xeb",noon:"drek",morning:"m\xebngjes",afternoon:"mbasdite",evening:"mbr\xebmje",night:"nat\xeb"}},defaultWidth:"wide",formattingValues:{narrow:{am:"p",pm:"m",midnight:"m",noon:"d",morning:"n\xeb m\xebngjes",afternoon:"n\xeb mbasdite",evening:"n\xeb mbr\xebmje",night:"n\xeb mesnat\xeb"},abbreviated:{am:"PD",pm:"MD",midnight:"mesnat\xeb",noon:"drek",morning:"n\xeb m\xebngjes",afternoon:"n\xeb mbasdite",evening:"n\xeb mbr\xebmje",night:"n\xeb mesnat\xeb"},wide:{am:"p.d.",pm:"m.d.",midnight:"mesnat\xeb",noon:"drek",morning:"n\xeb m\xebngjes",afternoon:"n\xeb mbasdite",evening:"n\xeb mbr\xebmje",night:"n\xeb mesnat\xeb"}},defaultFormattingWidth:"wide"})};t.default=i,e.exports=t.default},1102:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(206)),i={ordinalNumber:(0,r(a(207)).default)({matchPattern:/^(\d+)(-r\xeb|-t\xeb|t|)?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,n.default)({matchPatterns:{narrow:/^(p|m)/i,abbreviated:/^(b\.?\s?c\.?|b\.?\s?c\.?\s?e\.?|a\.?\s?d\.?|c\.?\s?e\.?)/i,wide:/^(para krishtit|mbas krishtit)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^b/i,/^(p|m)/i]},defaultParseWidth:"any"}),quarter:(0,n.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^q[1234]/i,wide:/^[1234]-mujori (i{1,3}|iv)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,n.default)({matchPatterns:{narrow:/^[jsmpqkftnd]/i,abbreviated:/^(jan|shk|mar|pri|maj|qer|kor|gus|sht|tet|n\xebn|dhj)/i,wide:/^(janar|shkurt|mars|prill|maj|qershor|korrik|gusht|shtator|tetor|n\xebntor|dhjetor)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^j/i,/^s/i,/^m/i,/^p/i,/^m/i,/^q/i,/^k/i,/^g/i,/^s/i,/^t/i,/^n/i,/^d/i],any:[/^ja/i,/^shk/i,/^mar/i,/^pri/i,/^maj/i,/^qer/i,/^kor/i,/^gu/i,/^sht/i,/^tet/i,/^n/i,/^d/i]},defaultParseWidth:"any"}),day:(0,n.default)({matchPatterns:{narrow:/^[dhmeps]/i,short:/^(di|h\xeb|ma|m\xeb|en|pr|sh)/i,abbreviated:/^(die|h\xebn|mar|m\xebr|enj|pre|sht)/i,wide:/^(diel\xeb|h\xebn\xeb|mart\xeb|m\xebrkur\xeb|enjte|premte|shtun\xeb)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^d/i,/^h/i,/^m/i,/^m/i,/^e/i,/^p/i,/^s/i],any:[/^d/i,/^h/i,/^ma/i,/^m\xeb/i,/^e/i,/^p/i,/^s/i]},defaultParseWidth:"any"}),dayPeriod:(0,n.default)({matchPatterns:{narrow:/^(p|m|me|n\xeb (m\xebngjes|mbasdite|mbr\xebmje|mesnat\xeb))/i,any:/^([pm]\.?\s?d\.?|drek|n\xeb (m\xebngjes|mbasdite|mbr\xebmje|mesnat\xeb))/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^p/i,pm:/^m/i,midnight:/^me/i,noon:/^dr/i,morning:/m\xebngjes/i,afternoon:/mbasdite/i,evening:/mbr\xebmje/i,night:/nat\xeb/i}},defaultParseWidth:"any"})};t.default=i,e.exports=t.default},202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var r;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var n=e.defaultFormattingWidth||e.defaultWidth,i=null!==a&&void 0!==a&&a.width?String(a.width):n;r=e.formattingValues[i]||e.formattingValues[n]}else{var o=e.defaultWidth,u=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;r=e.values[u]||e.values[o]}return r[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=a.width,n=r&&e.matchPatterns[r]||e.matchPatterns[e.defaultMatchWidth],i=t.match(n);if(!i)return null;var o,u=i[0],d=r&&e.parsePatterns[r]||e.parsePatterns[e.defaultParseWidth],s=Array.isArray(d)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(d,(function(e){return e.test(u)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(d,(function(e){return e.test(u)}));return o=e.valueCallback?e.valueCallback(s):s,{value:o=a.valueCallback?a.valueCallback(o):o,rest:t.slice(u.length)}}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=t.match(e.matchPattern);if(!r)return null;var n=r[0],i=t.match(e.parsePattern);if(!i)return null;var o=e.valueCallback?e.valueCallback(i[0]):i[0];return{value:o=a.valueCallback?a.valueCallback(o):o,rest:t.slice(n.length)}}},e.exports=t.default}}]);
//# sourceMappingURL=df-77.85673d93.chunk.js.map