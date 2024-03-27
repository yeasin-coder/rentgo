(()=>{"use strict";var e={d:(r,t)=>{for(var o in t)e.o(t,o)&&!e.o(r,o)&&Object.defineProperty(r,o,{enumerable:!0,get:t[o]})},o:(e,r)=>Object.prototype.hasOwnProperty.call(e,r),r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},r={};function t(){return t=Object.assign?Object.assign.bind():function(e){for(var r=1;r<arguments.length;r++){var t=arguments[r];for(var o in t)Object.prototype.hasOwnProperty.call(t,o)&&(e[o]=t[o])}return e},t.apply(this,arguments)}e.r(r),e.d(r,{metadata:()=>c,name:()=>d,settings:()=>a});var o=wp.i18n.__,i=wp.blockEditor.useBlockProps,l=wp.components.Placeholder;const c=JSON.parse('{"apiVersion":2,"name":"jet-forms/choices-field","category":"jet-form-builder-fields","title":"Advanced Choices Field","description":"","icon":"<svg width=\\"52\\" height=\\"53\\" viewBox=\\"0 0 52 53\\" fill=\\"none\\" xmlns=\\"http://www.w3.org/2000/svg\\">\\n<path fill-rule=\\"evenodd\\" clip-rule=\\"evenodd\\" d=\\"M10 14C8.89543 14 8 14.8954 8 16V22C8 23.1046 8.89543 24 10 24H16C17.1046 24 18 23.1046 18 22V16C18 14.8954 17.1046 14 16 14H10ZM16 16H10V22H16V16Z\\" fill=\\"currentColor\\"/>\\n<path d=\\"M23 16C22.4477 16 22 16.4477 22 17C22 17.5523 22.4477 18 23 18H37C37.5523 18 38 17.5523 38 17C38 16.4477 37.5523 16 37 16H23Z\\" fill=\\"currentColor\\"/>\\n<path d=\\"M22 21C22 20.4477 22.4477 20 23 20H37C37.5523 20 38 20.4477 38 21C38 21.5523 37.5523 22 37 22H23C22.4477 22 22 21.5523 22 21Z\\" fill=\\"currentColor\\"/>\\n<path fill-rule=\\"evenodd\\" clip-rule=\\"evenodd\\" d=\\"M23.5 37C25.433 37 27 35.433 27 33.5C27 31.567 25.433 30 23.5 30C21.567 30 20 31.567 20 33.5C20 35.433 21.567 37 23.5 37ZM23.5 35C24.3284 35 25 34.3284 25 33.5C25 32.6716 24.3284 32 23.5 32C22.6716 32 22 32.6716 22 33.5C22 34.3284 22.6716 35 23.5 35Z\\" fill=\\"currentColor\\"/>\\n<path d=\\"M27.0789 39C27.3469 39 27.6037 39.1076 27.7917 39.2985L32.7127 44.2985C33.1001 44.6922 33.0951 45.3253 32.7015 45.7127C32.3078 46.1001 31.6747 46.0951 31.2873 45.7015L27.0789 41.4256L22.8706 45.7015C22.6826 45.8924 22.4259 46 22.1579 46C21.8899 46 21.6332 45.8924 21.4452 45.7015L18.5789 42.7892L15.7127 45.7015C15.3253 46.0951 14.6922 46.1001 14.2985 45.7127C13.9049 45.3253 13.8999 44.6922 14.2873 44.2985L17.8662 40.6622C18.0542 40.4712 18.311 40.3636 18.5789 40.3636C18.8469 40.3636 19.1037 40.4712 19.2917 40.6622L22.1579 43.5744L26.3662 39.2985C26.5542 39.1076 26.811 39 27.0789 39Z\\" fill=\\"currentColor\\"/>\\n<path fill-rule=\\"evenodd\\" clip-rule=\\"evenodd\\" d=\\"M12 0C9.79086 0 8 1.79086 8 4V7H4C1.79086 7 0 8.79086 0 11V49C0 51.2091 1.79086 53 4 53H42C44.2091 53 46 51.2091 46 49V44H48C50.2091 44 52 42.2091 52 40V4C52 1.79086 50.2091 0 48 0H12ZM42 9C43.1046 9 44 9.89543 44 11V49C44 50.1046 43.1046 51 42 51H4C2.89543 51 2 50.1046 2 49V11C2 9.89543 2.89543 9 4 9H42ZM10 4C10 2.89543 10.8954 2 12 2H48C49.1046 2 50 2.89543 50 4V40C50 41.1046 49.1046 42 48 42H46V11C46 8.79086 44.2091 7 42 7H10V4Z\\" fill=\\"currentColor\\"/>\\n</svg>","attributes":{"isPreview":{"type":"boolean","default":false},"value":{"type":"object","default":{}},"allow_multiple":{"type":"boolean","default":false},"label":{"type":"string","default":"","jfb":{"shortcode":true}},"name":{"type":"string","default":"field_name"},"desc":{"type":"string","default":"","jfb":{"shortcode":true}},"default":{"type":["string","array","number","boolean","object"],"default":""},"required":{"type":"boolean","default":false},"visibility":{"type":"string","default":""}},"keywords":["jetformbuilder","field","choices"],"textdomain":"jet-form-builder","supports":{"html":false,"jetFBSwitchPageOnChange":true,"jetFBSanitizeValue":true,"jetStyle":{"--jfb-choice-text":[".jet-form-builder-choice--item","color","text"],"--jfb-choice-bg":[".jet-form-builder-choice--item","color","background"],"--jfb-choice-border":[".jet-form-builder-choice--item","border"],"--jfb-choice-border-radius":[".jet-form-builder-choice--item","border","radius"],"--jfb-choice-hover-text":[".jet-form-builder-choice--item:hover","color","text"],"--jfb-choice-hover-bg":[".jet-form-builder-choice--item:hover","color","background"],"--jfb-choice-hover-border":[".jet-form-builder-choice--item:hover","border"],"--jfb-choice-hover-border-radius":[".jet-form-builder-choice--item:hover","border","radius"],"--jfb-choice-checked-text":[".jet-form-builder-choice--item.is-checked","color","text"],"--jfb-choice-checked-bg":[".jet-form-builder-choice--item.is-checked","color","background"],"--jfb-choice-checked-border":[".jet-form-builder-choice--item.is-checked","border"],"--jfb-choice-checked-border-radius":[".jet-form-builder-choice--item.is-checked","border","radius"]},"jetCustomWrapperLayout":true,"layout":{"allowSwitching":false,"allowInheriting":false,"allowVerticalAlignment":false,"allowSizingOnChildren":true,"default":{"type":"flex"}},"spacing":{"blockGap":["horizontal","vertical"],"margin":true,"padding":true,"units":["px","em","rem","vh","vw"]}},"providesContext":{"jet-forms/choices-field--multiple":"allow_multiple","jet-forms/choices-field--required":"required","jet-forms/choices-field--name":"name","jet-forms/choices-field--default":"default"},"usesContext":["jet-forms/repeater-field--name","jet-forms/repeater-row--default","jet-forms/repeater-row--current-index"],"viewScript":"jet-fb-advanced-choices","style":"jet-fb-advanced-choices"}');var d=c.name,n=c.icon,a={icon:wp.element.createElement("span",{dangerouslySetInnerHTML:{__html:n}}),edit:function(e){var r=i();return wp.element.createElement(l,t({instructions:o("You should update your WordPress to version 6.2 or higher","jet-form-builder"),label:o("Advanced Choices not supported","jet-form-builder")},r))}};(0,wp.hooks.addFilter)("jet.fb.register.fields","jet-form-builder/advanced-choices-not-supported",(function(e){return e.push(r),e}))})();