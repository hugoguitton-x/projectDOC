(window.webpackJsonp=window.webpackJsonp||[]).push([["manga"],{oBu8:function(t,e,n){"use strict";n.r(e);n("QWBl"),n("rB9j"),n("UxlC"),n("FZtP");var o=n("vDqi"),a=n.n(o);function i(t){t.preventDefault();var e=this.href,n=this;a.a.post(e).then((function(t){n.textContent=t.data.value,n.classList.contains("twitter-enabled")?n.classList.replace("twitter-enabled","twitter-disabled"):n.classList.replace("twitter-disabled","twitter-enabled")})).catch((function(t){}))}function l(t){t.preventDefault();var e=this.href,n=this;a.a.post(e).then((function(t){n.textContent=t.data.value,n.classList.contains("followed")?n.classList.replace("followed","unfollowed"):n.classList.replace("unfollowed","followed")})).catch((function(t){}))}document.querySelectorAll("a.twitter-action").forEach((function(t){t.addEventListener("click",i)})),document.querySelectorAll("a.follow-action").forEach((function(t){t.addEventListener("click",l)}));n("4mDm"),n("DQNa"),n("wLYn"),n("eoL8"),n("07d7"),n("5s+n"),n("p532"),n("JfAA"),n("PKPk"),n("3bBZ"),n("R5XZ"),n("Kz25");function c(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}new(function(){function t(e){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),null!==e&&(this.content=e.querySelector(".js-filter-content"),this.pagination=e.querySelector(".js-filter-pagination"),this.form=e.querySelector(".js-filter-form"),this.loader=e.querySelector("#loader"),this.bindEvents())}var e,n,o;return e=t,(n=[{key:"bindEvents",value:function(){var t=this;this.pagination.addEventListener("click",(function(e){"A"===e.target.tagName&&(e.preventDefault(),t.loadUrl(e.target.getAttribute("href")))})),this.form.querySelectorAll("input").forEach((function(e){e.addEventListener("input",(function(){clearTimeout(t.timeout),t.timeout=setTimeout(t.loadForm.bind(t),1e3)}))}))}},{key:"loadForm",value:function(){var t=new FormData(this.form),e=new URL(this.form.getAttribute("action")||window.location.href),n=new URLSearchParams;t.forEach((function(t,e){n.append(e,t)})),this.loadUrl(e.pathname+"?"+n.toString())}},{key:"loadUrl",value:function(t){var e=this;e.loader.style.display="block",a.a.get("".concat(t,"&ajax=1")).then((function(n){e.currentContent!==n.data.content&&(e.currentContent=n.data.content,e.content.innerHTML=n.data.content,e.content.querySelectorAll("a.twitter-action").forEach((function(t){t.addEventListener("click",e.onClickLinkTwitterAction)})),e.content.querySelectorAll("a.follow-action").forEach((function(t){t.addEventListener("click",e.onClickLinkFollowAction)}))),e.pagination.innerHTML=n.data.pagination,history.replaceState({},"",t),e.loader.style.display="none"})).catch((function(t){console.error(t)})).finally((function(){}))}},{key:"onClickLinkTwitterAction",value:function(t){t.preventDefault();var e=this.href,n=this;a.a.post(e).then((function(t){n.textContent=t.data.value,n.classList.contains("twitter-enabled")?n.classList.replace("twitter-enabled","twitter-disabled"):n.classList.replace("twitter-disabled","twitter-enabled")})).catch((function(t){}))}},{key:"onClickLinkFollowAction",value:function(t){t.preventDefault();var e=this.href,n=this;a.a.post(e).then((function(t){n.textContent=t.data.value,n.classList.contains("followed")?n.classList.replace("followed","unfollowed"):n.classList.replace("unfollowed","followed")})).catch((function(t){}))}}])&&c(e.prototype,n),o&&c(e,o),t}())(document.querySelector(".js-filter"))}},[["oBu8","runtime",1,3]]]);