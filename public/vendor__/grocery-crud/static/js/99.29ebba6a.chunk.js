(this["webpackJsonpgrocery-crud-3-frontend"]=this["webpackJsonpgrocery-crud-3-frontend"]||[]).push([[99],{1079:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(1080)),u=i(n(1081)),r=i(n(1082)),o=i(n(1083)),l=i(n(1084)),s={code:"ru",formatDistance:a.default,formatLong:u.default,formatRelative:r.default,localize:o.default,match:l.default,options:{weekStartsOn:1,firstWeekContainsDate:1}};t.default=s,e.exports=t.default},1080:function(e,t,n){"use strict";function i(e,t){if(void 0!==e.one&&1===t)return e.one;var n=t%10,i=t%100;return 1===n&&11!==i?e.singularNominative.replace("{{count}}",String(t)):n>=2&&n<=4&&(i<10||i>20)?e.singularGenitive.replace("{{count}}",String(t)):e.pluralGenitive.replace("{{count}}",String(t))}function a(e){return function(t,n){return null!==n&&void 0!==n&&n.addSuffix?n.comparison&&n.comparison>0?e.future?i(e.future,t):"\u0447\u0435\u0440\u0435\u0437 "+i(e.regular,t):e.past?i(e.past,t):i(e.regular,t)+" \u043d\u0430\u0437\u0430\u0434":i(e.regular,t)}}Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var u={lessThanXSeconds:a({regular:{one:"\u043c\u0435\u043d\u044c\u0448\u0435 \u0441\u0435\u043a\u0443\u043d\u0434\u044b",singularNominative:"\u043c\u0435\u043d\u044c\u0448\u0435 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u044b",singularGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434",pluralGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434"},future:{one:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 \u0441\u0435\u043a\u0443\u043d\u0434\u0443",singularNominative:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0443",singularGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u044b",pluralGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434"}}),xSeconds:a({regular:{singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0430",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u044b",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434"},past:{singularNominative:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0443 \u043d\u0430\u0437\u0430\u0434",singularGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u044b \u043d\u0430\u0437\u0430\u0434",pluralGenitive:"{{count}} \u0441\u0435\u043a\u0443\u043d\u0434 \u043d\u0430\u0437\u0430\u0434"},future:{singularNominative:"\u0447\u0435\u0440\u0435\u0437 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u0443",singularGenitive:"\u0447\u0435\u0440\u0435\u0437 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434\u044b",pluralGenitive:"\u0447\u0435\u0440\u0435\u0437 {{count}} \u0441\u0435\u043a\u0443\u043d\u0434"}}),halfAMinute:function(e,t){return null!==t&&void 0!==t&&t.addSuffix?t.comparison&&t.comparison>0?"\u0447\u0435\u0440\u0435\u0437 \u043f\u043e\u043b\u043c\u0438\u043d\u0443\u0442\u044b":"\u043f\u043e\u043b\u043c\u0438\u043d\u0443\u0442\u044b \u043d\u0430\u0437\u0430\u0434":"\u043f\u043e\u043b\u043c\u0438\u043d\u0443\u0442\u044b"},lessThanXMinutes:a({regular:{one:"\u043c\u0435\u043d\u044c\u0448\u0435 \u043c\u0438\u043d\u0443\u0442\u044b",singularNominative:"\u043c\u0435\u043d\u044c\u0448\u0435 {{count}} \u043c\u0438\u043d\u0443\u0442\u044b",singularGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435 {{count}} \u043c\u0438\u043d\u0443\u0442",pluralGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435 {{count}} \u043c\u0438\u043d\u0443\u0442"},future:{one:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 \u043c\u0438\u043d\u0443\u0442\u0443",singularNominative:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0438\u043d\u0443\u0442\u0443",singularGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0438\u043d\u0443\u0442\u044b",pluralGenitive:"\u043c\u0435\u043d\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0438\u043d\u0443\u0442"}}),xMinutes:a({regular:{singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0430",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u044b",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442"},past:{singularNominative:"{{count}} \u043c\u0438\u043d\u0443\u0442\u0443 \u043d\u0430\u0437\u0430\u0434",singularGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442\u044b \u043d\u0430\u0437\u0430\u0434",pluralGenitive:"{{count}} \u043c\u0438\u043d\u0443\u0442 \u043d\u0430\u0437\u0430\u0434"},future:{singularNominative:"\u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0438\u043d\u0443\u0442\u0443",singularGenitive:"\u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0438\u043d\u0443\u0442\u044b",pluralGenitive:"\u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0438\u043d\u0443\u0442"}}),aboutXHours:a({regular:{singularNominative:"\u043e\u043a\u043e\u043b\u043e {{count}} \u0447\u0430\u0441\u0430",singularGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u0447\u0430\u0441\u043e\u0432",pluralGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u0447\u0430\u0441\u043e\u0432"},future:{singularNominative:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u0447\u0430\u0441",singularGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u0447\u0430\u0441\u0430",pluralGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u0447\u0430\u0441\u043e\u0432"}}),xHours:a({regular:{singularNominative:"{{count}} \u0447\u0430\u0441",singularGenitive:"{{count}} \u0447\u0430\u0441\u0430",pluralGenitive:"{{count}} \u0447\u0430\u0441\u043e\u0432"}}),xDays:a({regular:{singularNominative:"{{count}} \u0434\u0435\u043d\u044c",singularGenitive:"{{count}} \u0434\u043d\u044f",pluralGenitive:"{{count}} \u0434\u043d\u0435\u0439"}}),aboutXWeeks:a({regular:{singularNominative:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043d\u0435\u0434\u0435\u043b\u0438",singularGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043d\u0435\u0434\u0435\u043b\u044c",pluralGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043d\u0435\u0434\u0435\u043b\u044c"},future:{singularNominative:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043d\u0435\u0434\u0435\u043b\u044e",singularGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043d\u0435\u0434\u0435\u043b\u0438",pluralGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043d\u0435\u0434\u0435\u043b\u044c"}}),xWeeks:a({regular:{singularNominative:"{{count}} \u043d\u0435\u0434\u0435\u043b\u044f",singularGenitive:"{{count}} \u043d\u0435\u0434\u0435\u043b\u0438",pluralGenitive:"{{count}} \u043d\u0435\u0434\u0435\u043b\u044c"}}),aboutXMonths:a({regular:{singularNominative:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043c\u0435\u0441\u044f\u0446\u0430",singularGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043c\u0435\u0441\u044f\u0446\u0435\u0432",pluralGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043c\u0435\u0441\u044f\u0446\u0435\u0432"},future:{singularNominative:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0435\u0441\u044f\u0446",singularGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0435\u0441\u044f\u0446\u0430",pluralGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043c\u0435\u0441\u044f\u0446\u0435\u0432"}}),xMonths:a({regular:{singularNominative:"{{count}} \u043c\u0435\u0441\u044f\u0446",singularGenitive:"{{count}} \u043c\u0435\u0441\u044f\u0446\u0430",pluralGenitive:"{{count}} \u043c\u0435\u0441\u044f\u0446\u0435\u0432"}}),aboutXYears:a({regular:{singularNominative:"\u043e\u043a\u043e\u043b\u043e {{count}} \u0433\u043e\u0434\u0430",singularGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043b\u0435\u0442",pluralGenitive:"\u043e\u043a\u043e\u043b\u043e {{count}} \u043b\u0435\u0442"},future:{singularNominative:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u0433\u043e\u0434",singularGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u0433\u043e\u0434\u0430",pluralGenitive:"\u043f\u0440\u0438\u0431\u043b\u0438\u0437\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0447\u0435\u0440\u0435\u0437 {{count}} \u043b\u0435\u0442"}}),xYears:a({regular:{singularNominative:"{{count}} \u0433\u043e\u0434",singularGenitive:"{{count}} \u0433\u043e\u0434\u0430",pluralGenitive:"{{count}} \u043b\u0435\u0442"}}),overXYears:a({regular:{singularNominative:"\u0431\u043e\u043b\u044c\u0448\u0435 {{count}} \u0433\u043e\u0434\u0430",singularGenitive:"\u0431\u043e\u043b\u044c\u0448\u0435 {{count}} \u043b\u0435\u0442",pluralGenitive:"\u0431\u043e\u043b\u044c\u0448\u0435 {{count}} \u043b\u0435\u0442"},future:{singularNominative:"\u0431\u043e\u043b\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u0433\u043e\u0434",singularGenitive:"\u0431\u043e\u043b\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u0433\u043e\u0434\u0430",pluralGenitive:"\u0431\u043e\u043b\u044c\u0448\u0435, \u0447\u0435\u043c \u0447\u0435\u0440\u0435\u0437 {{count}} \u043b\u0435\u0442"}}),almostXYears:a({regular:{singularNominative:"\u043f\u043e\u0447\u0442\u0438 {{count}} \u0433\u043e\u0434",singularGenitive:"\u043f\u043e\u0447\u0442\u0438 {{count}} \u0433\u043e\u0434\u0430",pluralGenitive:"\u043f\u043e\u0447\u0442\u0438 {{count}} \u043b\u0435\u0442"},future:{singularNominative:"\u043f\u043e\u0447\u0442\u0438 \u0447\u0435\u0440\u0435\u0437 {{count}} \u0433\u043e\u0434",singularGenitive:"\u043f\u043e\u0447\u0442\u0438 \u0447\u0435\u0440\u0435\u0437 {{count}} \u0433\u043e\u0434\u0430",pluralGenitive:"\u043f\u043e\u0447\u0442\u0438 \u0447\u0435\u0440\u0435\u0437 {{count}} \u043b\u0435\u0442"}})},r=function(e,t,n){return u[e](t,n)};t.default=r,e.exports=t.default},1081:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(204)),u={date:(0,a.default)({formats:{full:"EEEE, d MMMM y '\u0433.'",long:"d MMMM y '\u0433.'",medium:"d MMM y '\u0433.'",short:"dd.MM.y"},defaultWidth:"full"}),time:(0,a.default)({formats:{full:"H:mm:ss zzzz",long:"H:mm:ss z",medium:"H:mm:ss",short:"H:mm"},defaultWidth:"full"}),dateTime:(0,a.default)({formats:{any:"{{date}}, {{time}}"},defaultWidth:"any"})};t.default=u,e.exports=t.default},1082:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(229)),u=["\u0432\u043e\u0441\u043a\u0440\u0435\u0441\u0435\u043d\u044c\u0435","\u043f\u043e\u043d\u0435\u0434\u0435\u043b\u044c\u043d\u0438\u043a","\u0432\u0442\u043e\u0440\u043d\u0438\u043a","\u0441\u0440\u0435\u0434\u0443","\u0447\u0435\u0442\u0432\u0435\u0440\u0433","\u043f\u044f\u0442\u043d\u0438\u0446\u0443","\u0441\u0443\u0431\u0431\u043e\u0442\u0443"];function r(e){var t=u[e];return 2===e?"'\u0432\u043e "+t+" \u0432' p":"'\u0432 "+t+" \u0432' p"}var o={lastWeek:function(e,t,n){var i=e.getUTCDay();return(0,a.default)(e,t,n)?r(i):function(e){var t=u[e];switch(e){case 0:return"'\u0432 \u043f\u0440\u043e\u0448\u043b\u043e\u0435 "+t+" \u0432' p";case 1:case 2:case 4:return"'\u0432 \u043f\u0440\u043e\u0448\u043b\u044b\u0439 "+t+" \u0432' p";case 3:case 5:case 6:return"'\u0432 \u043f\u0440\u043e\u0448\u043b\u0443\u044e "+t+" \u0432' p"}}(i)},yesterday:"'\u0432\u0447\u0435\u0440\u0430 \u0432' p",today:"'\u0441\u0435\u0433\u043e\u0434\u043d\u044f \u0432' p",tomorrow:"'\u0437\u0430\u0432\u0442\u0440\u0430 \u0432' p",nextWeek:function(e,t,n){var i=e.getUTCDay();return(0,a.default)(e,t,n)?r(i):function(e){var t=u[e];switch(e){case 0:return"'\u0432 \u0441\u043b\u0435\u0434\u0443\u044e\u0449\u0435\u0435 "+t+" \u0432' p";case 1:case 2:case 4:return"'\u0432 \u0441\u043b\u0435\u0434\u0443\u044e\u0449\u0438\u0439 "+t+" \u0432' p";case 3:case 5:case 6:return"'\u0432 \u0441\u043b\u0435\u0434\u0443\u044e\u0449\u0443\u044e "+t+" \u0432' p"}}(i)},other:"P"},l=function(e,t,n,i){var a=o[e];return"function"===typeof a?a(t,n,i):a};t.default=l,e.exports=t.default},1083:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(205)),u={ordinalNumber:function(e,t){var n=Number(e),i=null===t||void 0===t?void 0:t.unit;return n+("date"===i?"-\u0435":"week"===i||"minute"===i||"second"===i?"-\u044f":"-\u0439")},era:(0,a.default)({values:{narrow:["\u0434\u043e \u043d.\u044d.","\u043d.\u044d."],abbreviated:["\u0434\u043e \u043d. \u044d.","\u043d. \u044d."],wide:["\u0434\u043e \u043d\u0430\u0448\u0435\u0439 \u044d\u0440\u044b","\u043d\u0430\u0448\u0435\u0439 \u044d\u0440\u044b"]},defaultWidth:"wide"}),quarter:(0,a.default)({values:{narrow:["1","2","3","4"],abbreviated:["1-\u0439 \u043a\u0432.","2-\u0439 \u043a\u0432.","3-\u0439 \u043a\u0432.","4-\u0439 \u043a\u0432."],wide:["1-\u0439 \u043a\u0432\u0430\u0440\u0442\u0430\u043b","2-\u0439 \u043a\u0432\u0430\u0440\u0442\u0430\u043b","3-\u0439 \u043a\u0432\u0430\u0440\u0442\u0430\u043b","4-\u0439 \u043a\u0432\u0430\u0440\u0442\u0430\u043b"]},defaultWidth:"wide",argumentCallback:function(e){return e-1}}),month:(0,a.default)({values:{narrow:["\u042f","\u0424","\u041c","\u0410","\u041c","\u0418","\u0418","\u0410","\u0421","\u041e","\u041d","\u0414"],abbreviated:["\u044f\u043d\u0432.","\u0444\u0435\u0432.","\u043c\u0430\u0440\u0442","\u0430\u043f\u0440.","\u043c\u0430\u0439","\u0438\u044e\u043d\u044c","\u0438\u044e\u043b\u044c","\u0430\u0432\u0433.","\u0441\u0435\u043d\u0442.","\u043e\u043a\u0442.","\u043d\u043e\u044f\u0431.","\u0434\u0435\u043a."],wide:["\u044f\u043d\u0432\u0430\u0440\u044c","\u0444\u0435\u0432\u0440\u0430\u043b\u044c","\u043c\u0430\u0440\u0442","\u0430\u043f\u0440\u0435\u043b\u044c","\u043c\u0430\u0439","\u0438\u044e\u043d\u044c","\u0438\u044e\u043b\u044c","\u0430\u0432\u0433\u0443\u0441\u0442","\u0441\u0435\u043d\u0442\u044f\u0431\u0440\u044c","\u043e\u043a\u0442\u044f\u0431\u0440\u044c","\u043d\u043e\u044f\u0431\u0440\u044c","\u0434\u0435\u043a\u0430\u0431\u0440\u044c"]},defaultWidth:"wide",formattingValues:{narrow:["\u042f","\u0424","\u041c","\u0410","\u041c","\u0418","\u0418","\u0410","\u0421","\u041e","\u041d","\u0414"],abbreviated:["\u044f\u043d\u0432.","\u0444\u0435\u0432.","\u043c\u0430\u0440.","\u0430\u043f\u0440.","\u043c\u0430\u044f","\u0438\u044e\u043d.","\u0438\u044e\u043b.","\u0430\u0432\u0433.","\u0441\u0435\u043d\u0442.","\u043e\u043a\u0442.","\u043d\u043e\u044f\u0431.","\u0434\u0435\u043a."],wide:["\u044f\u043d\u0432\u0430\u0440\u044f","\u0444\u0435\u0432\u0440\u0430\u043b\u044f","\u043c\u0430\u0440\u0442\u0430","\u0430\u043f\u0440\u0435\u043b\u044f","\u043c\u0430\u044f","\u0438\u044e\u043d\u044f","\u0438\u044e\u043b\u044f","\u0430\u0432\u0433\u0443\u0441\u0442\u0430","\u0441\u0435\u043d\u0442\u044f\u0431\u0440\u044f","\u043e\u043a\u0442\u044f\u0431\u0440\u044f","\u043d\u043e\u044f\u0431\u0440\u044f","\u0434\u0435\u043a\u0430\u0431\u0440\u044f"]},defaultFormattingWidth:"wide"}),day:(0,a.default)({values:{narrow:["\u0412","\u041f","\u0412","\u0421","\u0427","\u041f","\u0421"],short:["\u0432\u0441","\u043f\u043d","\u0432\u0442","\u0441\u0440","\u0447\u0442","\u043f\u0442","\u0441\u0431"],abbreviated:["\u0432\u0441\u043a","\u043f\u043d\u0434","\u0432\u0442\u0440","\u0441\u0440\u0434","\u0447\u0442\u0432","\u043f\u0442\u043d","\u0441\u0443\u0431"],wide:["\u0432\u043e\u0441\u043a\u0440\u0435\u0441\u0435\u043d\u044c\u0435","\u043f\u043e\u043d\u0435\u0434\u0435\u043b\u044c\u043d\u0438\u043a","\u0432\u0442\u043e\u0440\u043d\u0438\u043a","\u0441\u0440\u0435\u0434\u0430","\u0447\u0435\u0442\u0432\u0435\u0440\u0433","\u043f\u044f\u0442\u043d\u0438\u0446\u0430","\u0441\u0443\u0431\u0431\u043e\u0442\u0430"]},defaultWidth:"wide"}),dayPeriod:(0,a.default)({values:{narrow:{am:"\u0414\u041f",pm:"\u041f\u041f",midnight:"\u043f\u043e\u043b\u043d.",noon:"\u043f\u043e\u043b\u0434.",morning:"\u0443\u0442\u0440\u043e",afternoon:"\u0434\u0435\u043d\u044c",evening:"\u0432\u0435\u0447.",night:"\u043d\u043e\u0447\u044c"},abbreviated:{am:"\u0414\u041f",pm:"\u041f\u041f",midnight:"\u043f\u043e\u043b\u043d.",noon:"\u043f\u043e\u043b\u0434.",morning:"\u0443\u0442\u0440\u043e",afternoon:"\u0434\u0435\u043d\u044c",evening:"\u0432\u0435\u0447.",night:"\u043d\u043e\u0447\u044c"},wide:{am:"\u0414\u041f",pm:"\u041f\u041f",midnight:"\u043f\u043e\u043b\u043d\u043e\u0447\u044c",noon:"\u043f\u043e\u043b\u0434\u0435\u043d\u044c",morning:"\u0443\u0442\u0440\u043e",afternoon:"\u0434\u0435\u043d\u044c",evening:"\u0432\u0435\u0447\u0435\u0440",night:"\u043d\u043e\u0447\u044c"}},defaultWidth:"any",formattingValues:{narrow:{am:"\u0414\u041f",pm:"\u041f\u041f",midnight:"\u043f\u043e\u043b\u043d.",noon:"\u043f\u043e\u043b\u0434.",morning:"\u0443\u0442\u0440\u0430",afternoon:"\u0434\u043d\u044f",evening:"\u0432\u0435\u0447.",night:"\u043d\u043e\u0447\u0438"},abbreviated:{am:"\u0414\u041f",pm:"\u041f\u041f",midnight:"\u043f\u043e\u043b\u043d.",noon:"\u043f\u043e\u043b\u0434.",morning:"\u0443\u0442\u0440\u0430",afternoon:"\u0434\u043d\u044f",evening:"\u0432\u0435\u0447.",night:"\u043d\u043e\u0447\u0438"},wide:{am:"\u0414\u041f",pm:"\u041f\u041f",midnight:"\u043f\u043e\u043b\u043d\u043e\u0447\u044c",noon:"\u043f\u043e\u043b\u0434\u0435\u043d\u044c",morning:"\u0443\u0442\u0440\u0430",afternoon:"\u0434\u043d\u044f",evening:"\u0432\u0435\u0447\u0435\u0440\u0430",night:"\u043d\u043e\u0447\u0438"}},defaultFormattingWidth:"wide"})};t.default=u,e.exports=t.default},1084:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n(206)),u={ordinalNumber:(0,i(n(207)).default)({matchPattern:/^(\d+)(-?(\u0435|\u044f|\u0439|\u043e\u0435|\u044c\u0435|\u0430\u044f|\u044c\u044f|\u044b\u0439|\u043e\u0439|\u0438\u0439|\u044b\u0439))?/i,parsePattern:/\d+/i,valueCallback:function(e){return parseInt(e,10)}}),era:(0,a.default)({matchPatterns:{narrow:/^((\u0434\u043e )?\u043d\.?\s?\u044d\.?)/i,abbreviated:/^((\u0434\u043e )?\u043d\.?\s?\u044d\.?)/i,wide:/^(\u0434\u043e \u043d\u0430\u0448\u0435\u0439 \u044d\u0440\u044b|\u043d\u0430\u0448\u0435\u0439 \u044d\u0440\u044b|\u043d\u0430\u0448\u0430 \u044d\u0440\u0430)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^\u0434/i,/^\u043d/i]},defaultParseWidth:"any"}),quarter:(0,a.default)({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^[1234](-?[\u044b\u043e\u0438]?\u0439?)? \u043a\u0432.?/i,wide:/^[1234](-?[\u044b\u043e\u0438]?\u0439?)? \u043a\u0432\u0430\u0440\u0442\u0430\u043b/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:function(e){return e+1}}),month:(0,a.default)({matchPatterns:{narrow:/^[\u044f\u0444\u043c\u0430\u0438\u0441\u043e\u043d\u0434]/i,abbreviated:/^(\u044f\u043d\u0432|\u0444\u0435\u0432|\u043c\u0430\u0440\u0442?|\u0430\u043f\u0440|\u043c\u0430[\u0439\u044f]|\u0438\u044e\u043d[\u044c\u044f]?|\u0438\u044e\u043b[\u044c\u044f]?|\u0430\u0432\u0433|\u0441\u0435\u043d\u0442?|\u043e\u043a\u0442|\u043d\u043e\u044f\u0431?|\u0434\u0435\u043a)\.?/i,wide:/^(\u044f\u043d\u0432\u0430\u0440[\u044c\u044f]|\u0444\u0435\u0432\u0440\u0430\u043b[\u044c\u044f]|\u043c\u0430\u0440\u0442\u0430?|\u0430\u043f\u0440\u0435\u043b[\u044c\u044f]|\u043c\u0430[\u0439\u044f]|\u0438\u044e\u043d[\u044c\u044f]|\u0438\u044e\u043b[\u044c\u044f]|\u0430\u0432\u0433\u0443\u0441\u0442\u0430?|\u0441\u0435\u043d\u0442\u044f\u0431\u0440[\u044c\u044f]|\u043e\u043a\u0442\u044f\u0431\u0440[\u044c\u044f]|\u043e\u043a\u0442\u044f\u0431\u0440[\u044c\u044f]|\u043d\u043e\u044f\u0431\u0440[\u044c\u044f]|\u0434\u0435\u043a\u0430\u0431\u0440[\u044c\u044f])/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u044f/i,/^\u0444/i,/^\u043c/i,/^\u0430/i,/^\u043c/i,/^\u0438/i,/^\u0438/i,/^\u0430/i,/^\u0441/i,/^\u043e/i,/^\u043d/i,/^\u044f/i],any:[/^\u044f/i,/^\u0444/i,/^\u043c\u0430\u0440/i,/^\u0430\u043f/i,/^\u043c\u0430[\u0439\u044f]/i,/^\u0438\u044e\u043d/i,/^\u0438\u044e\u043b/i,/^\u0430\u0432/i,/^\u0441/i,/^\u043e/i,/^\u043d/i,/^\u0434/i]},defaultParseWidth:"any"}),day:(0,a.default)({matchPatterns:{narrow:/^[\u0432\u043f\u0441\u0447]/i,short:/^(\u0432\u0441|\u0432\u043e|\u043f\u043d|\u043f\u043e|\u0432\u0442|\u0441\u0440|\u0447\u0442|\u0447\u0435|\u043f\u0442|\u043f\u044f|\u0441\u0431|\u0441\u0443)\.?/i,abbreviated:/^(\u0432\u0441\u043a|\u0432\u043e\u0441|\u043f\u043d\u0434|\u043f\u043e\u043d|\u0432\u0442\u0440|\u0432\u0442\u043e|\u0441\u0440\u0434|\u0441\u0440\u0435|\u0447\u0442\u0432|\u0447\u0435\u0442|\u043f\u0442\u043d|\u043f\u044f\u0442|\u0441\u0443\u0431).?/i,wide:/^(\u0432\u043e\u0441\u043a\u0440\u0435\u0441\u0435\u043d\u044c[\u0435\u044f]|\u043f\u043e\u043d\u0435\u0434\u0435\u043b\u044c\u043d\u0438\u043a\u0430?|\u0432\u0442\u043e\u0440\u043d\u0438\u043a\u0430?|\u0441\u0440\u0435\u0434[\u0430\u044b]|\u0447\u0435\u0442\u0432\u0435\u0440\u0433\u0430?|\u043f\u044f\u0442\u043d\u0438\u0446[\u0430\u044b]|\u0441\u0443\u0431\u0431\u043e\u0442[\u0430\u044b])/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^\u0432/i,/^\u043f/i,/^\u0432/i,/^\u0441/i,/^\u0447/i,/^\u043f/i,/^\u0441/i],any:[/^\u0432[\u043e\u0441]/i,/^\u043f[\u043e\u043d]/i,/^\u0432/i,/^\u0441\u0440/i,/^\u0447/i,/^\u043f[\u044f\u0442]/i,/^\u0441[\u0443\u0431]/i]},defaultParseWidth:"any"}),dayPeriod:(0,a.default)({matchPatterns:{narrow:/^([\u0434\u043f]\u043f|\u043f\u043e\u043b\u043d\.?|\u043f\u043e\u043b\u0434\.?|\u0443\u0442\u0440[\u043e\u0430]|\u0434\u0435\u043d\u044c|\u0434\u043d\u044f|\u0432\u0435\u0447\.?|\u043d\u043e\u0447[\u044c\u0438])/i,abbreviated:/^([\u0434\u043f]\u043f|\u043f\u043e\u043b\u043d\.?|\u043f\u043e\u043b\u0434\.?|\u0443\u0442\u0440[\u043e\u0430]|\u0434\u0435\u043d\u044c|\u0434\u043d\u044f|\u0432\u0435\u0447\.?|\u043d\u043e\u0447[\u044c\u0438])/i,wide:/^([\u0434\u043f]\u043f|\u043f\u043e\u043b\u043d\u043e\u0447\u044c|\u043f\u043e\u043b\u0434\u0435\u043d\u044c|\u0443\u0442\u0440[\u043e\u0430]|\u0434\u0435\u043d\u044c|\u0434\u043d\u044f|\u0432\u0435\u0447\u0435\u0440\u0430?|\u043d\u043e\u0447[\u044c\u0438])/i},defaultMatchWidth:"wide",parsePatterns:{any:{am:/^\u0434\u043f/i,pm:/^\u043f\u043f/i,midnight:/^\u043f\u043e\u043b\u043d/i,noon:/^\u043f\u043e\u043b\u0434/i,morning:/^\u0443/i,afternoon:/^\u0434[\u0435\u043d]/i,evening:/^\u0432/i,night:/^\u043d/i}},defaultParseWidth:"any"})};t.default=u,e.exports=t.default},202:function(e,t){e.exports=function(e){return e&&e.__esModule?e:{default:e}},e.exports.__esModule=!0,e.exports.default=e.exports},203:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t){if(t.length<e)throw new TypeError(e+" argument"+(e>1?"s":"")+" required, but only "+t.length+" present")},e.exports=t.default},204:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},n=t.width?String(t.width):e.defaultWidth;return e.formats[n]||e.formats[e.defaultWidth]}},e.exports=t.default},205:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t,n){var i;if("formatting"===(null!==n&&void 0!==n&&n.context?String(n.context):"standalone")&&e.formattingValues){var a=e.defaultFormattingWidth||e.defaultWidth,u=null!==n&&void 0!==n&&n.width?String(n.width):a;i=e.formattingValues[u]||e.formattingValues[a]}else{var r=e.defaultWidth,o=null!==n&&void 0!==n&&n.width?String(n.width):e.defaultWidth;i=e.values[o]||e.values[r]}return i[e.argumentCallback?e.argumentCallback(t):t]}},e.exports=t.default},206:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=n.width,a=i&&e.matchPatterns[i]||e.matchPatterns[e.defaultMatchWidth],u=t.match(a);if(!u)return null;var r,o=u[0],l=i&&e.parsePatterns[i]||e.parsePatterns[e.defaultParseWidth],s=Array.isArray(l)?function(e,t){for(var n=0;n<e.length;n++)if(t(e[n]))return n;return}(l,(function(e){return e.test(o)})):function(e,t){for(var n in e)if(e.hasOwnProperty(n)&&t(e[n]))return n;return}(l,(function(e){return e.test(o)}));return r=e.valueCallback?e.valueCallback(s):s,{value:r=n.valueCallback?n.valueCallback(r):r,rest:t.slice(o.length)}}},e.exports=t.default},207:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){return function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=t.match(e.matchPattern);if(!i)return null;var a=i[0],u=t.match(e.parsePattern);if(!u)return null;var r=e.valueCallback?e.valueCallback(u[0]):u[0];return{value:r=n.valueCallback?n.valueCallback(r):r,rest:t.slice(a.length)}}},e.exports=t.default},208:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){(0,u.default)(1,arguments);var t=Object.prototype.toString.call(e);return e instanceof Date||"object"===(0,a.default)(e)&&"[object Date]"===t?new Date(e.getTime()):"number"===typeof e||"[object Number]"===t?new Date(e):("string"!==typeof e&&"[object String]"!==t||"undefined"===typeof console||(console.warn("Starting with v2.0.0-beta.1 date-fns doesn't accept strings as date arguments. Please use `parseISO` to parse strings. See: https://github.com/date-fns/date-fns/blob/master/docs/upgradeGuide.md#string-arguments"),console.warn((new Error).stack)),new Date(NaN))};var a=i(n(228)),u=i(n(203));e.exports=t.default},209:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e){if(null===e||!0===e||!1===e)return NaN;var t=Number(e);if(isNaN(t))return t;return t<0?Math.ceil(t):Math.floor(t)},e.exports=t.default},212:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.getDefaultOptions=function(){return i},t.setDefaultOptions=function(e){i=e};var i={}},227:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t){var n,i,l,s,d,c,f,v;(0,u.default)(1,arguments);var g=(0,o.getDefaultOptions)(),m=(0,r.default)(null!==(n=null!==(i=null!==(l=null!==(s=null===t||void 0===t?void 0:t.weekStartsOn)&&void 0!==s?s:null===t||void 0===t||null===(d=t.locale)||void 0===d||null===(c=d.options)||void 0===c?void 0:c.weekStartsOn)&&void 0!==l?l:g.weekStartsOn)&&void 0!==i?i:null===(f=g.locale)||void 0===f||null===(v=f.options)||void 0===v?void 0:v.weekStartsOn)&&void 0!==n?n:0);if(!(m>=0&&m<=6))throw new RangeError("weekStartsOn must be between 0 and 6 inclusively");var p=(0,a.default)(e),h=p.getUTCDay(),b=(h<m?7:0)+h-m;return p.setUTCDate(p.getUTCDate()-b),p.setUTCHours(0,0,0,0),p};var a=i(n(208)),u=i(n(203)),r=i(n(209)),o=n(212);e.exports=t.default},229:function(e,t,n){"use strict";var i=n(202).default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=function(e,t,n){(0,a.default)(2,arguments);var i=(0,u.default)(e,n),r=(0,u.default)(t,n);return i.getTime()===r.getTime()};var a=i(n(203)),u=i(n(227));e.exports=t.default}}]);
//# sourceMappingURL=99.29ebba6a.chunk.js.map