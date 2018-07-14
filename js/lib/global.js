(function (definition) {
  if (typeof exports === "object") {
    // CommonJS
    module.exports = definition();
  } else if (typeof define === "function" && define.amd) {
    // RequireJS
    define(definition);
  } else {
    // <script>
    Global = definition();
  }
})(function () {
  'use strict';
  return (function () {
    var Global = {};
    var G = Global;

    G.RID_MIN = 100000000000000;
    G.RID_MAX = G.RID_MIN * 10 - 1;
    G.rid = function rid() {
      return "_" + (Math.floor(Math.random() * (G.RID_MAX - G.RID_MIN + 1)) + G.RID_MIN).toString(10);
    };

    G.clone = function clone(origin) {
      return JSON.parse(JSON.stringify(origin));
    };

    G.language = window.navigator.language;

    G.messages = {};

    (function (messages) {
      messages.en = {};
      messages.ja = {};
      messages["ja-JP"] = ja;
      var en = messages.en;
      var ja = messages.ja;
      var key;
      
      key = "#hello";
      en[key] = "Hello";
      ja[key] = "こんにちは";

      key = "#confirmation";
      en[key] = "Confirmation";
      ja[key] = "確認";

      key = "#confirmation-message";
      en[key] = "Are you sure?";
      ja[key] = "よろしいですか?";

      key = "#ok";
      en[key] = "OK";
      ja[key] = "OK";

      key = "#cancel";
      en[key] = "Cancel";
      ja[key] = "キャンセル";

      key = "#please-input";
      en[key] = "Please input.";
      ja[key] = "入力して下さい";

      key = "#length-min-max";
      en[key] = "Minimum {min} characters and maximum {max} characters.";
      ja[key] = "{min}文字以上、{max}文字以下で入力して下さい";

    })(G.messages);

    var keyRex = /(#[\w-]+)\s*(\{[^}]+\})?/;
    G.getMsg = function (keyText) {
      var result = keyRex.exec(keyText);
      var key = result[1];
      key = (key || "").trim();
      var msglng = this.messages[this.language] || this.messages.en;
      var msg = (msglng && msglng[key]) || "";
      
      var opts = result[2];
      if (opts) {
        try {
          var optsObj = JSON.parse(opts);
          for (var name in optsObj) {
            msg = msg.replace("{" + name + "}", optsObj[name]);
          }
        } catch (e) {
        }
      }

      return msg;
    };

    G.putMsgs = function (selectors, rootElem) {
      rootElem = rootElem || document;
      selectors = selectors || ".msg";
      var elems = rootElem.querySelectorAll(selectors);
      for (var i = 0; i < elems.length; ++i) {
        var elem = elems.item(i);
        elem.setAttribute("data-original-inner-html", elem.innerHTML);
        elem.textContent = this.getMsg(elem.innerHTML);
      }
    };
    
    var modal = {
      Global: Global,
      html: '' +
      '<div id="__modal__" class="modal">' +
      ' <div class="modal-content">' +
      '   <div class="modal-header">' +
      '     <span class="modal-close">&times;</span>' +
      '     <div class="modal-header-content"><!-- #confirmation --></div>' +
      '   </div>' +
      '   <div class="modal-body"><!-- #confirmation-message --></div>' +
      '   <div class="modal-footer">' +
      '     <button type="button" class="modal-ok"><!-- #ok --></button>' +
      '     <button type="button" class="modal-cancel"><!-- #cancel --></button>' +
      '   </div>' +
      '  </div>' +
      '</div>' +
      '',
      create: function (opts) {
        var G = this.Global;
        var m = {};
        opts = opts || {};
        m.opts = opts;
        m.opts.ok = m.opts.ok || {};
        m.opts.cancel = m.opts.cancel || {};

        var ph = document.createElement("div");
        m.placeholder = ph;
        m.opts.id = m.opts.id || G.rid();
        m.placeholder.innerHTML = this.html.replace('id="__modal__"', 'id="' + m.opts.id + '"');
        m.modal = ph.querySelector("#" + m.opts.id);

        m.header = ph.querySelector(".modal-header-content");
        m.header.innerHTML = m.opts.header || "<h2>" + G.getMsg(m.header.innerHTML)  + "</h2>";

        m.body = ph.querySelector(".modal-body");
        m.body.innerHTML = m.opts.body || G.getMsg(m.body.innerHTML);
        
        m.okButton = ph.querySelector(".modal-ok");
        m.cancelButton = ph.querySelector(".modal-cancel");
        m.closeSpan = ph.querySelector(".modal-close");

        m.opts.ok.text = m.opts.ok.text || G.getMsg(m.okButton.innerHTML);
        m.okButton.textContent = m.opts.ok.text;

        m.opts.cancel.text = m.opts.cancel.text || G.getMsg(m.cancelButton.innerHTML);
        m.cancelButton.textContent = m.opts.cancel.text; 

        m.open = function () {
          document.querySelector("body").appendChild(this.placeholder);
          this.modal.style.display = "block";
          return this;
        };

        m.close = function () {
          this.modal.style.display = "none";
          this.destroy();
          return this;
        };

        m.destroy = function () {
          this.placeholder.parentNode.removeChild(this.placeholder);
          return this;
        };

        var okFun = (function (modal) {
          return function (event) {
            modal.close();
            if (modal.opts.ok.onclick) {
              modal.opts.ok.onclick.call(modal, event);
            }
          };
        })(m);

        var cancelFun = (function (modal) {
          return function (event) {
            modal.close();
            if (modal.opts.cancel.onclick) {
              modal.opts.cancel.onclick.call(modal, event);
            }
          };
        })(m);

        m.okButton.onclick = okFun;
        m.cancelButton.onclick = cancelFun;
        m.closeSpan.onclick = cancelFun;

        return m;
      }
    };

    Global.modal = modal;

    return Global;
  })();
});
