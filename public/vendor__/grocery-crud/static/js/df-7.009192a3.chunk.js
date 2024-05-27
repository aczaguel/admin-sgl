(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[70],{202:function(e,a){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,a,t){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=function(e){return function(){var a=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=a.width?String(a.width):e.defaultWidth;return e.formats[t]||e.formats[e.defaultWidth]}},e.exports=a.default},205:function(e,a,t){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=function(e){return function(a,t){var n;if("formatting"===(null!==t&&void 0!==t&&t.context?String(t.context):"standalone")&&e.formattingValues){var i=e.defaultFormattingWidth||e.defaultWidth,r=null!==t&&void 0!==t&&t.width?String(t.width):i;n=e.formattingValues[r]||e.formattingValues[i]}else{var u=e.defaultWidth,o=null!==t&&void 0!==t&&t.width?String(t.width):e.defaultWidth;n=e.values[o]||e.values[u]}return n[e.argumentCallback?e.argumentCallback(a):a]}},e.exports=a.default},206:function(e,a,t){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=function(e){return function(a){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=t.width,i=n&&e.matchPatterns[n]||e.matchPatterns[e.defaultMatchWidth],r=a.match(i);if(!r)return null;var u,o=r[0],d=n&&e.parsePatterns[n]||e.parsePatterns[e.defaultParseWidth],l=Array.isArray(d)?function(e,a){for(var t=0;t<e.length;t++)if(a(e[t]))return t;return}(d,(function(e){return e.test(o)})):function(e,a){for(var t in e)if(e.hasOwnProperty(t)&&a(e[t]))return t;return}(d,(function(e){return e.test(o)}));return u=e.valueCallback?e.valueCallback(l):l,{value:u=t.valueCallback?t.valueCallback(u):u,rest:a.slice(o.length)}}},e.exports=a.default},207:function(e,a,t){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=function(e){return function(a){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=a.match(e.matchPattern);if(!n)return null;var i=n[0],r=a.match(e.parsePattern);if(!r)return null;var u=e.valueCallback?e.valueCallback(r[0]):r[0];return{value:u=t.valueCallback?t.valueCallback(u):u,rest:a.slice(i.length)}}},e.exports=a.default},548:function(e,a,t){"use strict";var n=t(202).default;Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i=n(t(549)),r=n(t(550)),u=n(t(551)),o=n(t(552)),d=n(t(553)),l={code:"az",formatDistance:i.default,formatLong:r.default,formatRelative:u.default,localize:o.default,match:d.default,options:{weekStartsOn:1,firstWeekContainsDate:1}};a.default=l,e.exports=a.default},549:function(e,a,t){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n={lessThanXSeconds:{one:"bir saniy\u0259d\u0259n az",other:"{{count}} bir saniy\u0259d\u0259n az"},xSeconds:{one:"1 saniy\u0259",other:"{{count}} saniy\u0259"},halfAMinute:"yar\u0131m d\u0259qiq\u0259",lessThanXMinutes:{one:"bir d\u0259qiq\u0259d\u0259n az",other:"{{count}} bir d\u0259qiq\u0259d\u0259n az"},xMinutes:{one:"bir d\u0259qiq\u0259",other:"{{count}} d\u0259qiq\u0259"},aboutXHours:{one:"t\u0259xmin\u0259n 1 saat",other:"t\u0259xmin\u0259n {{count}} saat"},xHours:{one:"1 saat",other:"{{count}} saat"},xDays:{one:"1 g\xfcn",other:"{{count}} g\xfcn"},aboutXWeeks:{one:"t\u0259xmin\u0259n 1 h\u0259ft\u0259",other:"t\u0259xmin\u0259n {{count}} h\u0259ft\u0259"},xWeeks:{one:"1 h\u0259ft\u0259",other:"{{count}} h\u0259ft\u0259"},aboutXMonths:{one:"t\u0259xmin\u0259n 1 ay",other:"t\u0259xmin\u0259n {{count}} ay"},xMonths:{one:"1 ay",other:"{{count}} ay"},aboutXYears:{one:"t\u0259xmin\u0259n 1 il",other:"t\u0259xmin\u0259n {{count}} il"},xYears:{one:"1 il",other:"{{count}} il"},overXYears:{one:"1 ild\u0259n \xe7ox",other:"{{count}} ild\u0259n \xe7ox"},almostXYears:{one:"dem\u0259k olar ki 1 il",other:"dem\u0259k olar ki {{count}} il"}},i=function(e,a,t){var i,r=n[e];return i="string"===typeof r?r:1===a?r.one:r.other.replace("{{count}}",String(a)),null!==t&&void 0!==t&&t.addSuffix?t.comparison&&t.comparison>0?i+" sonra":i+" \u0259vv\u0259l":i};a.default=i,e.exports=a.default},550:function(e,a,t){"use strict";var n=t(202).default;Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i=n(t(204)),r={date:(0,i.default)({formats:{full:"EEEE, do MMMM y 'il'",long:"do MMMM y 'il'",medium:"d MMM y 'il'",short:"dd.MM.yyyy"},defaultWidth:"full"}),time:(0,i.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,i.default)({formats:{full:"{{date}} {{time}} - 'd\u0259'",long:"{{date}} {{time}} - 'd\u0259'",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})};a.default=r,e.exports=a.default},551:function(e,a,t){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n={lastWeek:"'sonuncu' eeee p -'d\u0259'",yesterday:"'d\xfcn\u0259n' p -'d\u0259'",today:"'bug\xfcn' p -'d\u0259'",tomorrow:"'sabah' p -'d\u0259'",nextWeek:"eeee p -'d\u0259'",other:"P"},i=function(e,a,t,i){return n[e]};a.default=i,e.exports=a.default},552:function(e,a,t){"use strict";var n=t(202).default;Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i=n(t(205)),r={1:"-inci",5:"-inci",8:"-inci",70:"-inci",80:"-inci",2:"-nci",7:"-nci",20:"-nci",50:"-nci",3:"-\xfcnc\xfc",4:"-\xfcnc\xfc",100:"-\xfcnc\xfc",6:"-nc\u0131",9:"-uncu",10:"-uncu",30:"-uncu",60:"-\u0131nc\u0131",90:"-\u0131nc\u0131"},u={ordinalNumber:function(e,a){var t=Number(e),n=function(e){if(0===e)return e+"-\u0131nc\u0131";var a=e%10,t=e%100-a,n=e>=100?100:null;return r[a]?r[a]:r[t]?r[t]:null!==n?r[n]:""}(t);return t+n},era:(0,i.default)({values:{narrow:["e.\u0259","b.e"],abbreviated:["e.\u0259","b.e"],wide:["eram\u0131zdan \u0259vv\u0259l","bizim era"]},defaultWidth:"wide"}),quarter:(0,i.default)({values:{narrow:["1","2","3","4"],abbreviated:["K1","K2","K3","K4"],wide:["1ci kvartal","2ci kvartal","3c\xfc kvartal","4c\xfc kvartal"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,i.default)({values:{narrow:["Y","F","M","A","M","\u0130","\u0130","A","S","O","N","D"],abbreviated:["Yan","Fev","Mar","Apr","May","\u0130yun","\u0130yul","Avq","Sen","Okt","Noy","Dek"],wide:["Yanvar","Fevral","Mart","Aprel","May","\u0130yun","\u0130yul","Avqust","Sentyabr","Oktyabr","Noyabr","Dekabr"]},defaultWidth:"wide"}),day:(0,i.default)({values:{narrow:["B.","B.e","\xc7.a","\xc7.","C.a","C.","\u015e."],short:["B.","B.e","\xc7.a","\xc7.","C.a","C.","\u015e."],abbreviated:["Baz","Baz.e","\xc7\u0259r.a","\xc7\u0259r","C\xfcm.a","C\xfcm","\u015e\u0259"],wide:["Bazar","Bazar ert\u0259si","\xc7\u0259r\u015f\u0259nb\u0259 ax\u015fam\u0131","\xc7\u0259r\u015f\u0259nb\u0259","C\xfcm\u0259 ax\u015fam\u0131","C\xfcm\u0259","\u015e\u0259nb\u0259"]},defaultWidth:"wide"}),dayPeriod:(0,i.default)({values:{narrow:{am:"am",pm:"pm",midnight:"gec\u0259yar\u0131",noon:"g\xfcn",morning:"s\u0259h\u0259r",afternoon:"g\xfcnd\xfcz",evening:"ax\u015fam",night:"gec\u0259"},abbreviated:{am:"AM",pm:"PM",midnight:"gec\u0259yar\u0131",noon:"g\xfcn",morning:"s\u0259h\u0259r",afternoon:"g\xfcnd\xfcz",evening:"ax\u015fam",night:"gec\u0259"},wide:{am:"a.m.",pm:"p.m.",midnight:"gec\u0259yar\u0131",noon:"g\xfcn",morning:"s\u0259h\u0259r",afternoon:"g\xfcnd\xfcz",evening:"ax\u015fam",night:"gec\u0259"}},defaultWidth:"wide",formattingValues:{narrow:{am:"a",pm:"p",midnight:"gec\u0259yar\u0131",noon:"g\xfcn",morning:"s\u0259h\u0259r",afternoon:"g\xfcnd\xfcz",evening:"ax\u015fam",night:"gec\u0259"},abbreviated:{am:"AM",pm:"PM",midnight:"gec\u0259yar\u0131",noon:"g\xfcn",morning:"s\u0259h\u0259r",afternoon:"g\xfcnd\xfcz",evening:"ax\u015fam",night:"gec\u0259"},wide:{am:"a.m.",pm:"p.m.",midnight:"gec\u0259yar\u0131",noon:"g\xfcn",morning:"s\u0259h\u0259r",afternoon:"g\xfcnd\xfcz",evening:"ax\u015fam",night:"gec\u0259"}},defaultFormattingWidth:"wide"})};a.default=u,e.exports=a.default},553:function(e,a,t){"use strict";var n=t(202).default;Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i=n(t(206)),r={ordinalNumber:(0,n(t(207)).default)({matchPattern:/^(\d+)(-?(ci|inci|nci|uncu|\xfcnc\xfc|nc\u0131))?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,i.default)({matchPatterns:{narrow:/^(b|a)$/i,abbreviated:/^(b\.?\s?c\.?|b\.?\s?c\.?\s?e\.?|a\.?\s?d\.?|c\.?\s?e\.?)$/i,wide:/^(bizim eradan \u0259vv\u0259l|bizim era)$/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^b$/i,/^(a|c)$/i]},defaultParseWidth:"any"}),quarter:(0,i.default)({matchPatterns:{narrow:/^[1234]$/i,abbreviated:/^K[1234]$/i,wide:/^[1234](ci)? kvartal$/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,i.default)({matchPatterns:{narrow:/^[(?-i)yfmaisond]$/i,abbreviated:/^(Yan|Fev|Mar|Apr|May|\u0130yun|\u0130yul|Avq|Sen|Okt|Noy|Dek)$/i,wide:/^(Yanvar|Fevral|Mart|Aprel|May|\u0130yun|\u0130yul|Avgust|Sentyabr|Oktyabr|Noyabr|Dekabr)$/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^[(?-i)y]$/i,/^[(?-i)f]$/i,/^[(?-i)m]$/i,/^[(?-i)a]$/i,/^[(?-i)m]$/i,/^[(?-i)i]$/i,/^[(?-i)i]$/i,/^[(?-i)a]$/i,/^[(?-i)s]$/i,/^[(?-i)o]$/i,/^[(?-i)n]$/i,/^[(?-i)d]$/i],abbreviated:[/^Yan$/i,/^Fev$/i,/^Mar$/i,/^Apr$/i,/^May$/i,/^\u0130yun$/i,/^\u0130yul$/i,/^Avg$/i,/^Sen$/i,/^Okt$/i,/^Noy$/i,/^Dek$/i],wide:[/^Yanvar$/i,/^Fevral$/i,/^Mart$/i,/^Aprel$/i,/^May$/i,/^\u0130yun$/i,/^\u0130yul$/i,/^Avgust$/i,/^Sentyabr$/i,/^Oktyabr$/i,/^Noyabr$/i,/^Dekabr$/i]},defaultParseWidth:"narrow"}),day:(0,i.default)({matchPatterns:{narrow:/^(B\.|B\.e|\xc7\.a|\xc7\.|C\.a|C\.|\u015e\.)$/i,short:/^(B\.|B\.e|\xc7\.a|\xc7\.|C\.a|C\.|\u015e\.)$/i,abbreviated:/^(Baz\.e|\xc7\u0259r|\xc7\u0259r\.a|C\xfcm|C\xfcm\.a|\u015e\u0259)$/i,wide:/^(Bazar|Bazar ert\u0259si|\xc7\u0259r\u015f\u0259nb\u0259 ax\u015fam\u0131|\xc7\u0259r\u015f\u0259nb\u0259|C\xfcm\u0259 ax\u015fam\u0131|C\xfcm\u0259|\u015e\u0259nb\u0259)$/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^B\.$/i,/^B\.e$/i,/^\xc7\.a$/i,/^\xc7\.$/i,/^C\.a$/i,/^C\.$/i,/^\u015e\.$/i],abbreviated:[/^Baz$/i,/^Baz\.e$/i,/^\xc7\u0259r\.a$/i,/^\xc7\u0259r$/i,/^C\xfcm\.a$/i,/^C\xfcm$/i,/^\u015e\u0259$/i],wide:[/^Bazar$/i,/^Bazar ert\u0259si$/i,/^\xc7\u0259r\u015f\u0259nb\u0259 ax\u015fam\u0131$/i,/^\xc7\u0259r\u015f\u0259nb\u0259$/i,/^C\xfcm\u0259 ax\u015fam\u0131$/i,/^C\xfcm\u0259$/i,/^\u015e\u0259nb\u0259$/i],any:[/^B\.$/i,/^B\.e$/i,/^\xc7\.a$/i,/^\xc7\.$/i,/^C\.a$/i,/^C\.$/i,/^\u015e\.$/i]},defaultParseWidth:"any"}),dayPeriod:(0,i.default)({matchPatterns:{narrow:/^(a|p|gec\u0259yar\u0131|g\xfcn|s\u0259h\u0259r|g\xfcnd\xfcz|ax\u015fam|gec\u0259)$/i,any:/^(am|pm|a\.m\.|p\.m\.|AM|PM|gec\u0259yar\u0131|g\xfcn|s\u0259h\u0259r|g\xfcnd\xfcz|ax\u015fam|gec\u0259)$/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^a$/i,pm:/^p$/i,midnight:/^gec\u0259yar\u0131$/i,noon:/^g\xfcn$/i,morning:/s\u0259h\u0259r$/i,afternoon:/g\xfcnd\xfcz$/i,evening:/ax\u015fam$/i,night:/gec\u0259$/i}},defaultParseWidth:"any"})};a.default=r,e.exports=a.default}}]);
//# sourceMappingURL=df-7.009192a3.chunk.js.map