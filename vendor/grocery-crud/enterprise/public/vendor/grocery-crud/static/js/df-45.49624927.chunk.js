(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[43],{202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var r;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var n=e.defaultFormattingWidth||e.defaultWidth,i=null!==a&&void 0!==a&&a.width?String(a.width):n;r=e.formattingValues[i]||e.formattingValues[n]}else{var d=e.defaultWidth,l=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;r=e.values[l]||e.values[d]}return r[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=a.width,n=r&&e.matchPatterns[r]||e.matchPatterns[e.defaultMatchWidth],i=t.match(n);if(!i)return null;var d,l=i[0],u=r&&e.parsePatterns[r]||e.parsePatterns[e.defaultParseWidth],s=Array.isArray(u)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(u,(function(e){return e.test(l)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(u,(function(e){return e.test(l)}));return d=e.valueCallback?e.valueCallback(s):s,{value:d=a.valueCallback?a.valueCallback(d):d,rest:t.slice(l.length)}}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=t.match(e.matchPattern);if(!r)return null;var n=r[0],i=t.match(e.parsePattern);if(!i)return null;var d=e.valueCallback?e.valueCallback(i[0]):i[0];return{value:d=a.valueCallback?a.valueCallback(d):d,rest:t.slice(n.length)}}},e.exports=t.default},914:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(915)),i=r(a(916)),d=r(a(917)),l=r(a(918)),u=r(a(919)),s={code:"hu",formatDistance:n.default,formatLong:i.default,formatRelative:d.default,localize:l.default,match:u.default,options:{weekStartsOn:1,firstWeekContainsDate:4}};t.default=s,e.exports=t.default},915:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={about:"k\xf6r\xfclbel\xfcl",over:"t\xf6bb mint",almost:"majdnem",lessthan:"kevesebb mint"},n={xseconds:" m\xe1sodperc",halfaminute:"f\xe9l perc",xminutes:" perc",xhours:" \xf3ra",xdays:" nap",xweeks:" h\xe9t",xmonths:" h\xf3nap",xyears:" \xe9v"},i={xseconds:{"-1":" m\xe1sodperccel ezel\u0151tt",1:" m\xe1sodperc m\xfalva",0:" m\xe1sodperce"},halfaminute:{"-1":"f\xe9l perccel ezel\u0151tt",1:"f\xe9l perc m\xfalva",0:"f\xe9l perce"},xminutes:{"-1":" perccel ezel\u0151tt",1:" perc m\xfalva",0:" perce"},xhours:{"-1":" \xf3r\xe1val ezel\u0151tt",1:" \xf3ra m\xfalva",0:" \xf3r\xe1ja"},xdays:{"-1":" nappal ezel\u0151tt",1:" nap m\xfalva",0:" napja"},xweeks:{"-1":" h\xe9ttel ezel\u0151tt",1:" h\xe9t m\xfalva",0:" hete"},xmonths:{"-1":" h\xf3nappal ezel\u0151tt",1:" h\xf3nap m\xfalva",0:" h\xf3napja"},xyears:{"-1":" \xe9vvel ezel\u0151tt",1:" \xe9v m\xfalva",0:" \xe9ve"}},d=function(e,t,a){var d=e.match(/about|over|almost|lessthan/i),l=d?e.replace(d[0],""):e,u=!0===(null===a||void 0===a?void 0:a.addSuffix),s=l.toLowerCase(),o=(null===a||void 0===a?void 0:a.comparison)||0,f=u?i[s][o]:n[s],c="halfaminute"===s?f:t+f;if(d){var v=d[0].toLowerCase();c=r[v]+" "+c}return c};t.default=d,e.exports=t.default},916:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(204)),i={date:(0,n.default)({formats:{full:"y. MMMM d., EEEE",long:"y. MMMM d.",medium:"y. MMM d.",short:"y. MM. dd."},defaultWidth:"full"}),time:(0,n.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,n.default)({formats:{full:"{{date}} {{time}}",long:"{{date}} {{time}}",medium:"{{date}} {{time}}",short:"{{date}} {{time}}"},defaultWidth:"full"})};t.default=i,e.exports=t.default},917:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=["vas\xe1rnap","h\xe9tf\u0151n","kedden","szerd\xe1n","cs\xfct\xf6rt\xf6k\xf6n","p\xe9nteken","szombaton"];function n(e){return function(t){var a=r[t.getUTCDay()];return"".concat(e?"":"'m\xfalt' ","'").concat(a,"' p'-kor'")}}var i={lastWeek:n(!1),yesterday:"'tegnap' p'-kor'",today:"'ma' p'-kor'",tomorrow:"'holnap' p'-kor'",nextWeek:n(!0),other:"P"},d=function(e,t){var a=i[e];return"function"===typeof a?a(t):a};t.default=d,e.exports=t.default},918:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(205)),i={ordinalNumber:function(e,t){return Number(e)+"."},era:(0,n.default)({values:{narrow:["ie.","isz."],abbreviated:["i. e.","i. sz."],wide:["Krisztus el\u0151tt","id\u0151sz\xe1m\xedt\xe1sunk szerint"]},defaultWidth:"wide"}),quarter:(0,n.default)({values:{narrow:["1.","2.","3.","4."],abbreviated:["1. n.\xe9v","2. n.\xe9v","3. n.\xe9v","4. n.\xe9v"],wide:["1. negyed\xe9v","2. negyed\xe9v","3. negyed\xe9v","4. negyed\xe9v"]},defaultWidth:"wide",argumentCallback:function(e){return e-1},formattingValues:{narrow:["I.","II.","III.","IV."],abbreviated:["I. n.\xe9v","II. n.\xe9v","III. n.\xe9v","IV. n.\xe9v"],wide:["I. negyed\xe9v","II. negyed\xe9v","III. negyed\xe9v","IV. negyed\xe9v"]},defaultFormattingWidth:"wide"}),month:(0,n.default)({values:{narrow:["J","F","M","\xc1","M","J","J","A","Sz","O","N","D"],abbreviated:["jan.","febr.","m\xe1rc.","\xe1pr.","m\xe1j.","j\xfan.","j\xfal.","aug.","szept.","okt.","nov.","dec."],wide:["janu\xe1r","febru\xe1r","m\xe1rcius","\xe1prilis","m\xe1jus","j\xfanius","j\xfalius","augusztus","szeptember","okt\xf3ber","november","december"]},defaultWidth:"wide"}),day:(0,n.default)({values:{narrow:["V","H","K","Sz","Cs","P","Sz"],short:["V","H","K","Sze","Cs","P","Szo"],abbreviated:["V","H","K","Sze","Cs","P","Szo"],wide:["vas\xe1rnap","h\xe9tf\u0151","kedd","szerda","cs\xfct\xf6rt\xf6k","p\xe9ntek","szombat"]},defaultWidth:"wide"}),dayPeriod:(0,n.default)({values:{narrow:{am:"de.",pm:"du.",midnight:"\xe9jf\xe9l",noon:"d\xe9l",morning:"reggel",afternoon:"du.",evening:"este",night:"\xe9jjel"},abbreviated:{am:"de.",pm:"du.",midnight:"\xe9jf\xe9l",noon:"d\xe9l",morning:"reggel",afternoon:"du.",evening:"este",night:"\xe9jjel"},wide:{am:"de.",pm:"du.",midnight:"\xe9jf\xe9l",noon:"d\xe9l",morning:"reggel",afternoon:"d\xe9lut\xe1n",evening:"este",night:"\xe9jjel"}},defaultWidth:"wide"})};t.default=i,e.exports=t.default},919:function(e,t,a){"use strict";var r=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=r(a(206)),i={ordinalNumber:(0,r(a(207)).default)({matchPattern:/^(\d+)\.?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,n.default)({matchPatterns:{narrow:/^(ie\.|isz\.)/i,abbreviated:/^(i\.\s?e\.?|b?\s?c\s?e|i\.\s?sz\.?)/i,wide:/^(Krisztus el\u0151tt|id\u0151sz\xe1m\xedt\xe1sunk el\u0151tt|id\u0151sz\xe1m\xedt\xe1sunk szerint|i\. sz\.)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/ie/i,/isz/i],abbreviated:[/^(i\.?\s?e\.?|b\s?ce)/i,/^(i\.?\s?sz\.?|c\s?e)/i],any:[/el\u0151tt/i,/(szerint|i. sz.)/i]},defaultParseWidth:"any"}),quarter:(0,n.default)({matchPatterns:{narrow:/^[1234]\.?/i,abbreviated:/^[1234]?\.?\s?n\.\xe9v/i,wide:/^([1234]|I|II|III|IV)?\.?\s?negyed\xe9v/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1|I$/i,/2|II$/i,/3|III/i,/4|IV/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,n.default)({matchPatterns:{narrow:/^[jfma\xe1sond]|sz/i,abbreviated:/^(jan\.?|febr\.?|m\xe1rc\.?|\xe1pr\.?|m\xe1j\.?|j\xfan\.?|j\xfal\.?|aug\.?|szept\.?|okt\.?|nov\.?|dec\.?)/i,wide:/^(janu\xe1r|febru\xe1r|m\xe1rcius|\xe1prilis|m\xe1jus|j\xfanius|j\xfalius|augusztus|szeptember|okt\xf3ber|november|december)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^j/i,/^f/i,/^m/i,/^a|\xe1/i,/^m/i,/^j/i,/^j/i,/^a/i,/^s|sz/i,/^o/i,/^n/i,/^d/i],any:[/^ja/i,/^f/i,/^m\xe1r/i,/^\xe1p/i,/^m\xe1j/i,/^j\xfan/i,/^j\xfal/i,/^au/i,/^s/i,/^o/i,/^n/i,/^d/i]},defaultParseWidth:"any"}),day:(0,n.default)({matchPatterns:{narrow:/^([vhkpc]|sz|cs|sz)/i,short:/^([vhkp]|sze|cs|szo)/i,abbreviated:/^([vhkp]|sze|cs|szo)/i,wide:/^(vas\xe1rnap|h\xe9tf\u0151|kedd|szerda|cs\xfct\xf6rt\xf6k|p\xe9ntek|szombat)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^v/i,/^h/i,/^k/i,/^sz/i,/^c/i,/^p/i,/^sz/i],any:[/^v/i,/^h/i,/^k/i,/^sze/i,/^c/i,/^p/i,/^szo/i]},defaultParseWidth:"any"}),dayPeriod:(0,n.default)({matchPatterns:{any:/^((de|du)\.?|\xe9jf\xe9l|d\xe9lut\xe1n|d\xe9l|reggel|este|\xe9jjel)/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^de\.?/i,pm:/^du\.?/i,midnight:/^\xe9jf/i,noon:/^d\xe9/i,morning:/reg/i,afternoon:/^d\xe9lu\.?/i,evening:/es/i,night:/\xe9jj/i}},defaultParseWidth:"any"})};t.default=i,e.exports=t.default}}]);