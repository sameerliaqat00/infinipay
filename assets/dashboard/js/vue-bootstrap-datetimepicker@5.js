!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t(require("jquery"),require("moment"),require("pc-bootstrap4-datetimepicker")):"function"==typeof define&&define.amd?define("VueBootstrapDatetimePicker",["jquery","moment","pc-bootstrap4-datetimepicker"],t):"object"==typeof exports?exports.VueBootstrapDatetimePicker=t(require("jquery"),require("moment"),require("pc-bootstrap4-datetimepicker")):e.VueBootstrapDatetimePicker=t(e.jQuery,e.moment,e["pc-bootstrap4-datetimepicker"])}(window,function(e,t,n){return function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}return n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=3)}([function(t,n){t.exports=e},function(e,n){e.exports=t},function(e,t){e.exports=n},function(e,t,n){"use strict";n.r(t);var r=n(0),o=n.n(r),i=n(1),u=n.n(i),a=(n(2),["hide","show","change","error","update"]),c=function(e,t,n,r,o,i,u,a){var c,p="function"==typeof e?e.options:e;if(t&&(p.render=t,p.staticRenderFns=[],p._compiled=!0),c)if(p.functional){p._injectStyles=c;var s=p.render;p.render=function(e,t){return c.call(t),s(e,t)}}else{var f=p.beforeCreate;p.beforeCreate=f?[].concat(f,c):[c]}return{exports:e,options:p}}({name:"date-picker",props:{value:{default:null,required:!0,validator:function(e){return null===e||e instanceof Date||"string"==typeof e||e instanceof String||e instanceof u.a}},config:{type:Object,default:function(){return{}}},wrap:{type:Boolean,default:!1}},data:function(){return{dp:null,elem:null}},mounted:function(){this.dp||(this.elem=o()(this.wrap?this.$el.parentNode:this.$el),this.elem.datetimepicker(this.config),this.dp=this.elem.data("DateTimePicker"),this.dp.date(this.value),this.elem.on("dp.change",this.onChange),this.registerEvents())},watch:{value:function(e){this.dp&&this.dp.date(e||null)},config:{deep:!0,handler:function(e){this.dp&&this.dp.options(e)}}},methods:{onChange:function(e){var t=e.date?e.date.format(this.dp.format()):null;this.$emit("input",t)},registerEvents:function(){var e=this;a.forEach(function(t){e.elem.on("dp."+t,function(){for(var n=arguments.length,r=Array(n),o=0;o<n;o++)r[o]=arguments[o];e.$emit.apply(e,["dp-"+t].concat(r))})})}},beforeDestroy:function(){this.dp&&(this.dp.destroy(),this.dp=null,this.elem=null)}},function(){var e=this.$createElement,t=this._self._c||e;return this.config.inline?t("div",{staticClass:"datetimepicker-inline"}):t("input",{staticClass:"form-control",attrs:{type:"text"}})});c.options.__file="component.vue";var p=c.exports;n.d(t,"Plugin",function(){return s}),n.d(t,"Component",function(){return p});var s=function(e,t){var n="date-picker";"string"==typeof t&&(n=t),e.component(n,p)};p.install=s,t.default=p}]).default});
