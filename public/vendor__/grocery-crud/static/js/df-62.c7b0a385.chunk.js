(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[62],{1007:function(e,t,a){"use strict";var i=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=i(a(1008)),n=i(a(1009)),u=i(a(1010)),o=i(a(1011)),d=i(a(1012)),l={code:"mn",formatDistance:r.default,formatLong:n.default,formatRelative:u.default,localize:o.default,match:d.default,options:{weekStartsOn:1,firstWeekContainsDate:1}};t.default=l,e.exports=t.default},1008:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i={lessThanXSeconds:{one:"\u0441\u0435\u043a\u0443\u043d\u0434 \u0445\u04af\u0440\u044d\u0445\u0433\u04af\u0439",other:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434 \u0445\u04af\u0440\u044d\u0445\u0433\u04af\u0439"},xSeconds:{one:"1 \u0441\u0435\u043a\u0443\u043d\u0434",other:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434"},halfAMinute:"\u0445\u0430\u0433\u0430\u0441 \u043c\u0438\u043d\u0443\u0442",lessThanXMinutes:{one:"\u043c\u0438\u043d\u0443\u0442 \u0445\u04af\u0440\u044d\u0445\u0433\u04af\u0439",other:"{{count}} \u043c\u0438\u043d\u0443\u0442 \u0445\u04af\u0440\u044d\u0445\u0433\u04af\u0439"},xMinutes:{one:"1 \u043c\u0438\u043d\u0443\u0442",other:"{{count}} \u043c\u0438\u043d\u0443\u0442"},aboutXHours:{one:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 1 \u0446\u0430\u0433",other:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 {{count}} \u0446\u0430\u0433"},xHours:{one:"1 \u0446\u0430\u0433",other:"{{count}} \u0446\u0430\u0433"},xDays:{one:"1 \u04e9\u0434\u04e9\u0440",other:"{{count}} \u04e9\u0434\u04e9\u0440"},aboutXWeeks:{one:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 1 \u0434\u043e\u043b\u043e\u043e \u0445\u043e\u043d\u043e\u0433",other:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 {{count}} \u0434\u043e\u043b\u043e\u043e \u0445\u043e\u043d\u043e\u0433"},xWeeks:{one:"1 \u0434\u043e\u043b\u043e\u043e \u0445\u043e\u043d\u043e\u0433",other:"{{count}} \u0434\u043e\u043b\u043e\u043e \u0445\u043e\u043d\u043e\u0433"},aboutXMonths:{one:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 1 \u0441\u0430\u0440",other:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 {{count}} \u0441\u0430\u0440"},xMonths:{one:"1 \u0441\u0430\u0440",other:"{{count}} \u0441\u0430\u0440"},aboutXYears:{one:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 1 \u0436\u0438\u043b",other:"\u043e\u0439\u0440\u043e\u043b\u0446\u043e\u043e\u0433\u043e\u043e\u0440 {{count}} \u0436\u0438\u043b"},xYears:{one:"1 \u0436\u0438\u043b",other:"{{count}} \u0436\u0438\u043b"},overXYears:{one:"1 \u0436\u0438\u043b \u0433\u0430\u0440\u0430\u043d",other:"{{count}} \u0436\u0438\u043b \u0433\u0430\u0440\u0430\u043d"},almostXYears:{one:"\u0431\u0430\u0440\u0430\u0433 1 \u0436\u0438\u043b",other:"\u0431\u0430\u0440\u0430\u0433 {{count}} \u0436\u0438\u043b"}},r=function(e,t,a){var r,n=i[e];if(r="string"===typeof n?n:1===t?n.one:n.other.replace("{{count}}",String(t)),null!==a&&void 0!==a&&a.addSuffix){var u=r.split(" "),o=u.pop();switch(r=u.join(" "),o){case"\u0441\u0435\u043a\u0443\u043d\u0434":r+=" \u0441\u0435\u043a\u0443\u043d\u0434\u0438\u0439\u043d";break;case"\u043c\u0438\u043d\u0443\u0442":r+=" \u043c\u0438\u043d\u0443\u0442\u044b\u043d";break;case"\u0446\u0430\u0433":r+=" \u0446\u0430\u0433\u0438\u0439\u043d";break;case"\u04e9\u0434\u04e9\u0440":r+=" \u04e9\u0434\u0440\u0438\u0439\u043d";break;case"\u0441\u0430\u0440":r+=" \u0441\u0430\u0440\u044b\u043d";break;case"\u0436\u0438\u043b":r+=" \u0436\u0438\u043b\u0438\u0439\u043d";break;case"\u0445\u043e\u043d\u043e\u0433":r+=" \u0445\u043e\u043d\u043e\u0433\u0438\u0439\u043d";break;case"\u0433\u0430\u0440\u0430\u043d":r+=" \u0433\u0430\u0440\u0430\u043d\u044b";break;case"\u0445\u04af\u0440\u044d\u0445\u0433\u04af\u0439":r+=" \u0445\u04af\u0440\u044d\u0445\u0433\u04af\u0439 \u0445\u0443\u0433\u0430\u0446\u0430\u0430\u043d\u044b";break;default:r+=o+"-\u043d"}return a.comparison&&a.comparison>0?r+" \u0434\u0430\u0440\u0430\u0430":r+" \u04e9\u043c\u043d\u04e9"}return r};t.default=r,e.exports=t.default},1009:function(e,t,a){"use strict";var i=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=i(a(204)),n={date:(0,r.default)({formats:{full:"y '\u043e\u043d\u044b' MMMM'\u044b\u043d' d, EEEE '\u0433\u0430\u0440\u0430\u0433'",long:"y '\u043e\u043d\u044b' MMMM'\u044b\u043d' d",medium:"y '\u043e\u043d\u044b' MMM'\u044b\u043d' d",short:"y.MM.dd"},defaultWidth:"full"}),time:(0,r.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,r.default)({formats:{full:"{{date}} {{time}}",long:"{{date}} {{time}}",medium:"{{date}} {{time}}",short:"{{date}} {{time}}"},defaultWidth:"full"})};t.default=n,e.exports=t.default},1010:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i={lastWeek:"'\u04e9\u043d\u0433\u04e9\u0440\u0441\u04e9\u043d' eeee '\u0433\u0430\u0440\u0430\u0433\u0438\u0439\u043d' p '\u0446\u0430\u0433\u0442'",yesterday:"'\u04e9\u0447\u0438\u0433\u0434\u04e9\u0440' p '\u0446\u0430\u0433\u0442'",today:"'\u04e9\u043d\u04e9\u04e9\u0434\u04e9\u0440' p '\u0446\u0430\u0433\u0442'",tomorrow:"'\u043c\u0430\u0440\u0433\u0430\u0430\u0448' p '\u0446\u0430\u0433\u0442'",nextWeek:"'\u0438\u0440\u044d\u0445' eeee '\u0433\u0430\u0440\u0430\u0433\u0438\u0439\u043d' p '\u0446\u0430\u0433\u0442'",other:"P"},r=function(e,t,a,r){return i[e]};t.default=r,e.exports=t.default},1011:function(e,t,a){"use strict";var i=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=i(a(205)),n={ordinalNumber:function(e,t){return String(e)},era:(0,r.default)({values:{narrow:["\u041d\u0422\u04e8","\u041d\u0422"],abbreviated:["\u041d\u0422\u04e8","\u041d\u0422"],wide:["\u043d\u0438\u0439\u0442\u0438\u0439\u043d \u0442\u043e\u043e\u043b\u043b\u044b\u043d \u04e9\u043c\u043d\u04e9\u0445","\u043d\u0438\u0439\u0442\u0438\u0439\u043d \u0442\u043e\u043e\u043b\u043b\u044b\u043d"]},defaultWidth:"wide"}),quarter:(0,r.default)({values:{narrow:["I","II","III","IV"],abbreviated:["I \u0443\u043b\u0438\u0440\u0430\u043b","II \u0443\u043b\u0438\u0440\u0430\u043b","III \u0443\u043b\u0438\u0440\u0430\u043b","IV \u0443\u043b\u0438\u0440\u0430\u043b"],wide:["1-\u0440 \u0443\u043b\u0438\u0440\u0430\u043b","2-\u0440 \u0443\u043b\u0438\u0440\u0430\u043b","3-\u0440 \u0443\u043b\u0438\u0440\u0430\u043b","4-\u0440 \u0443\u043b\u0438\u0440\u0430\u043b"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,r.default)({values:{narrow:["I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII"],abbreviated:["1-\u0440 \u0441\u0430\u0440","2-\u0440 \u0441\u0430\u0440","3-\u0440 \u0441\u0430\u0440","4-\u0440 \u0441\u0430\u0440","5-\u0440 \u0441\u0430\u0440","6-\u0440 \u0441\u0430\u0440","7-\u0440 \u0441\u0430\u0440","8-\u0440 \u0441\u0430\u0440","9-\u0440 \u0441\u0430\u0440","10-\u0440 \u0441\u0430\u0440","11-\u0440 \u0441\u0430\u0440","12-\u0440 \u0441\u0430\u0440"],wide:["\u041d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0425\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0413\u0443\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0414\u04e9\u0440\u04e9\u0432\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0422\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0417\u0443\u0440\u0433\u0430\u0430\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0414\u043e\u043b\u043e\u043e\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u041d\u0430\u0439\u043c\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0415\u0441\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0410\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0410\u0440\u0432\u0430\u043d\u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0410\u0440\u0432\u0430\u043d \u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440"]},defaultWidth:"wide",formattingValues:{narrow:["I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII"],abbreviated:["1-\u0440 \u0441\u0430\u0440","2-\u0440 \u0441\u0430\u0440","3-\u0440 \u0441\u0430\u0440","4-\u0440 \u0441\u0430\u0440","5-\u0440 \u0441\u0430\u0440","6-\u0440 \u0441\u0430\u0440","7-\u0440 \u0441\u0430\u0440","8-\u0440 \u0441\u0430\u0440","9-\u0440 \u0441\u0430\u0440","10-\u0440 \u0441\u0430\u0440","11-\u0440 \u0441\u0430\u0440","12-\u0440 \u0441\u0430\u0440"],wide:["\u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0433\u0443\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0434\u04e9\u0440\u04e9\u0432\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0442\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0437\u0443\u0440\u0433\u0430\u0430\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0434\u043e\u043b\u043e\u043e\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u043d\u0430\u0439\u043c\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0435\u0441\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0430\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440","\u0430\u0440\u0432\u0430\u043d\u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440","\u0430\u0440\u0432\u0430\u043d \u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440"]},defaultFormattingWidth:"wide"}),day:(0,r.default)({values:{narrow:["\u041d","\u0414","\u041c","\u041b","\u041f","\u0411","\u0411"],short:["\u041d\u044f","\u0414\u0430","\u041c\u044f","\u041b\u0445","\u041f\u04af","\u0411\u0430","\u0411\u044f"],abbreviated:["\u041d\u044f\u043c","\u0414\u0430\u0432","\u041c\u044f\u0433","\u041b\u0445\u0430","\u041f\u04af\u0440","\u0411\u0430\u0430","\u0411\u044f\u043c"],wide:["\u041d\u044f\u043c","\u0414\u0430\u0432\u0430\u0430","\u041c\u044f\u0433\u043c\u0430\u0440","\u041b\u0445\u0430\u0433\u0432\u0430","\u041f\u04af\u0440\u044d\u0432","\u0411\u0430\u0430\u0441\u0430\u043d","\u0411\u044f\u043c\u0431\u0430"]},defaultWidth:"wide",formattingValues:{narrow:["\u041d","\u0414","\u041c","\u041b","\u041f","\u0411","\u0411"],short:["\u041d\u044f","\u0414\u0430","\u041c\u044f","\u041b\u0445","\u041f\u04af","\u0411\u0430","\u0411\u044f"],abbreviated:["\u041d\u044f\u043c","\u0414\u0430\u0432","\u041c\u044f\u0433","\u041b\u0445\u0430","\u041f\u04af\u0440","\u0411\u0430\u0430","\u0411\u044f\u043c"],wide:["\u043d\u044f\u043c","\u0434\u0430\u0432\u0430\u0430","\u043c\u044f\u0433\u043c\u0430\u0440","\u043b\u0445\u0430\u0433\u0432\u0430","\u043f\u04af\u0440\u044d\u0432","\u0431\u0430\u0430\u0441\u0430\u043d","\u0431\u044f\u043c\u0431\u0430"]},defaultFormattingWidth:"wide"}),dayPeriod:(0,r.default)({values:{narrow:{am:"\u04af.\u04e9.",pm:"\u04af.\u0445.",midnight:"\u0448\u04e9\u043d\u04e9 \u0434\u0443\u043d\u0434",noon:"\u04af\u0434 \u0434\u0443\u043d\u0434",morning:"\u04e9\u0433\u043b\u04e9\u04e9",afternoon:"\u04e9\u0434\u04e9\u0440",evening:"\u043e\u0440\u043e\u0439",night:"\u0448\u04e9\u043d\u04e9"},abbreviated:{am:"\u04af.\u04e9.",pm:"\u04af.\u0445.",midnight:"\u0448\u04e9\u043d\u04e9 \u0434\u0443\u043d\u0434",noon:"\u04af\u0434 \u0434\u0443\u043d\u0434",morning:"\u04e9\u0433\u043b\u04e9\u04e9",afternoon:"\u04e9\u0434\u04e9\u0440",evening:"\u043e\u0440\u043e\u0439",night:"\u0448\u04e9\u043d\u04e9"},wide:{am:"\u04af.\u04e9.",pm:"\u04af.\u0445.",midnight:"\u0448\u04e9\u043d\u04e9 \u0434\u0443\u043d\u0434",noon:"\u04af\u0434 \u0434\u0443\u043d\u0434",morning:"\u04e9\u0433\u043b\u04e9\u04e9",afternoon:"\u04e9\u0434\u04e9\u0440",evening:"\u043e\u0440\u043e\u0439",night:"\u0448\u04e9\u043d\u04e9"}},defaultWidth:"wide"})};t.default=n,e.exports=t.default},1012:function(e,t,a){"use strict";var i=a(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=i(a(206)),n={ordinalNumber:(0,i(a(207)).default)({matchPattern:/\d+/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,r.default)({matchPatterns:{narrow:/^(\u043d\u0442\u04e9|\u043d\u0442)/i,abbreviated:/^(\u043d\u0442\u04e9|\u043d\u0442)/i,wide:/^(\u043d\u0438\u0439\u0442\u0438\u0439\u043d \u0442\u043e\u043e\u043b\u043b\u044b\u043d \u04e9\u043c\u043d\u04e9|\u043d\u0438\u0439\u0442\u0438\u0439\u043d \u0442\u043e\u043e\u043b\u043b\u044b\u043d)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^(\u043d\u0442\u04e9|\u043d\u0438\u0439\u0442\u0438\u0439\u043d \u0442\u043e\u043e\u043b\u043b\u044b\u043d \u04e9\u043c\u043d\u04e9)/i,/^(\u043d\u0442|\u043d\u0438\u0439\u0442\u0438\u0439\u043d \u0442\u043e\u043e\u043b\u043b\u044b\u043d)/i]},defaultParseWidth:"any"}),quarter:(0,r.default)({matchPatterns:{narrow:/^(iv|iii|ii|i)/i,abbreviated:/^(iv|iii|ii|i) \u0443\u043b\u0438\u0440\u0430\u043b/i,wide:/^[1-4]-\u0440 \u0443\u043b\u0438\u0440\u0430\u043b/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^(i(\s|$)|1)/i,/^(ii(\s|$)|2)/i,/^(iii(\s|$)|3)/i,/^(iv(\s|$)|4)/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,r.default)({matchPatterns:{narrow:/^(xii|xi|x|ix|viii|vii|vi|v|iv|iii|ii|i)/i,abbreviated:/^(1-\u0440 \u0441\u0430\u0440|2-\u0440 \u0441\u0430\u0440|3-\u0440 \u0441\u0430\u0440|4-\u0440 \u0441\u0430\u0440|5-\u0440 \u0441\u0430\u0440|6-\u0440 \u0441\u0430\u0440|7-\u0440 \u0441\u0430\u0440|8-\u0440 \u0441\u0430\u0440|9-\u0440 \u0441\u0430\u0440|10-\u0440 \u0441\u0430\u0440|11-\u0440 \u0441\u0430\u0440|12-\u0440 \u0441\u0430\u0440)/i,wide:/^(\u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440|\u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u0433\u0443\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u0434\u04e9\u0440\u04e9\u0432\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440|\u0442\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u0437\u0443\u0440\u0433\u0430\u0430\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u0434\u043e\u043b\u043e\u043e\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u043d\u0430\u0439\u043c\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u0435\u0441\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440|\u0430\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440|\u0430\u0440\u0432\u0430\u043d \u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440 \u0441\u0430\u0440|\u0430\u0440\u0432\u0430\u043d \u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440 \u0441\u0430\u0440)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^i$/i,/^ii$/i,/^iii$/i,/^iv$/i,/^v$/i,/^vi$/i,/^vii$/i,/^viii$/i,/^ix$/i,/^x$/i,/^xi$/i,/^xii$/i],any:[/^(1|\u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440)/i,/^(2|\u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(3|\u0433\u0443\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(4|\u0434\u04e9\u0440\u04e9\u0432\u0434\u04af\u0433\u044d\u044d\u0440)/i,/^(5|\u0442\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(6|\u0437\u0443\u0440\u0433\u0430\u0430\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(7|\u0434\u043e\u043b\u043e\u043e\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(8|\u043d\u0430\u0439\u043c\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(9|\u0435\u0441\u0434\u04af\u0433\u044d\u044d\u0440)/i,/^(10|\u0430\u0440\u0430\u0432\u0434\u0443\u0433\u0430\u0430\u0440)/i,/^(11|\u0430\u0440\u0432\u0430\u043d \u043d\u044d\u0433\u0434\u04af\u0433\u044d\u044d\u0440)/i,/^(12|\u0430\u0440\u0432\u0430\u043d \u0445\u043e\u0451\u0440\u0434\u0443\u0433\u0430\u0430\u0440)/i]},defaultParseWidth:"any"}),day:(0,r.default)({matchPatterns:{narrow:/^[\u043d\u0434\u043c\u043b\u043f\u0431\u0431]/i,short:/^(\u043d\u044f|\u0434\u0430|\u043c\u044f|\u043b\u0445|\u043f\u04af|\u0431\u0430|\u0431\u044f)/i,abbreviated:/^(\u043d\u044f\u043c|\u0434\u0430\u0432|\u043c\u044f\u0433|\u043b\u0445\u0430|\u043f\u04af\u0440|\u0431\u0430\u0430|\u0431\u044f\u043c)/i,wide:/^(\u043d\u044f\u043c|\u0434\u0430\u0432\u0430\u0430|\u043c\u044f\u0433\u043c\u0430\u0440|\u043b\u0445\u0430\u0433\u0432\u0430|\u043f\u04af\u0440\u044d\u0432|\u0431\u0430\u0430\u0441\u0430\u043d|\u0431\u044f\u043c\u0431\u0430)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u043d/i,/^\u0434/i,/^\u043c/i,/^\u043b/i,/^\u043f/i,/^\u0431/i,/^\u0431/i],any:[/^\u043d\u044f/i,/^\u0434\u0430/i,/^\u043c\u044f/i,/^\u043b\u0445/i,/^\u043f\u04af/i,/^\u0431\u0430/i,/^\u0431\u044f/i]},defaultParseWidth:"any"}),dayPeriod:(0,r.default)({matchPatterns:{narrow:/^(\u04af\.\u04e9\.|\u04af\.\u0445\.|\u0448\u04e9\u043d\u04e9 \u0434\u0443\u043d\u0434|\u04af\u0434 \u0434\u0443\u043d\u0434|\u04e9\u0433\u043b\u04e9\u04e9|\u04e9\u0434\u04e9\u0440|\u043e\u0440\u043e\u0439|\u0448\u04e9\u043d\u04e9)/i,any:/^(\u04af\.\u04e9\.|\u04af\.\u0445\.|\u0448\u04e9\u043d\u04e9 \u0434\u0443\u043d\u0434|\u04af\u0434 \u0434\u0443\u043d\u0434|\u04e9\u0433\u043b\u04e9\u04e9|\u04e9\u0434\u04e9\u0440|\u043e\u0440\u043e\u0439|\u0448\u04e9\u043d\u04e9)/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^\u04af\.\u04e9\./i,pm:/^\u04af\.\u0445\./i,midnight:/^\u0448\u04e9\u043d\u04e9 \u0434\u0443\u043d\u0434/i,noon:/^\u04af\u0434 \u0434\u0443\u043d\u0434/i,morning:/\u04e9\u0433\u043b\u04e9\u04e9/i,afternoon:/\u04e9\u0434\u04e9\u0440/i,evening:/\u043e\u0440\u043e\u0439/i,night:/\u0448\u04e9\u043d\u04e9/i}},defaultParseWidth:"any"})};t.default=n,e.exports=t.default},202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},204:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},a=t.width?String(t.width):e.defaultWidth;return e.formats[a]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,a){var i;if("formatting"===(null!==a&&void 0!==a&&a.context?String(a.context):"standalone")&&e.formattingValues){var r=e.defaultFormattingWidth||e.defaultWidth,n=null!==a&&void 0!==a&&a.width?String(a.width):r;i=e.formattingValues[n]||e.formattingValues[r]}else{var u=e.defaultWidth,o=null!==a&&void 0!==a&&a.width?String(a.width):e.defaultWidth;i=e.values[o]||e.values[u]}return i[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=a.width,r=i&&e.matchPatterns[i]||e.matchPatterns[e.defaultMatchWidth],n=t.match(r);if(!n)return null;var u,o=n[0],d=i&&e.parsePatterns[i]||e.parsePatterns[e.defaultParseWidth],l=Array.isArray(d)?function(e,t){for(var a=0;a<e.length;a++)if(t(e[a]))return a;return}(d,(function(e){return e.test(o)})):function(e,t){for(var a in e)if(e.hasOwnProperty(a)&&t(e[a]))return a;return}(d,(function(e){return e.test(o)}));return u=e.valueCallback?e.valueCallback(l):l,{value:u=a.valueCallback?a.valueCallback(u):u,rest:t.slice(o.length)}}},e.exports=t.default},207:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=t.match(e.matchPattern);if(!i)return null;var r=i[0],n=t.match(e.parsePattern);if(!n)return null;var u=e.valueCallback?e.valueCallback(n[0]):n[0];return{value:u=a.valueCallback?a.valueCallback(u):u,rest:t.slice(r.length)}}},e.exports=t.default}}]);
//# sourceMappingURL=df-62.c7b0a385.chunk.js.map