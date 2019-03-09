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

    G.config = {
      snackbarAutoloading: true,
    };

    G.RID_MIN = 100000000000000;
    G.RID_MAX = G.RID_MIN * 10 - 1;
    G.rid = function rid() {
      return "_" + (Math.floor(Math.random() * (G.RID_MAX - G.RID_MIN + 1)) + G.RID_MIN).toString(10);
    };

    G.clone = function clone(origin) {
      return JSON.parse(JSON.stringify(origin));
    };

    G.camelToKebab = function camelToKebab(p) {
      return p.replace(/([A-Z])/g,
        function (s) {
          return '-' + s.charAt(0).toLowerCase();
        }
      );
    };

    function isUndefined(v) {
      return typeof v === "undefined";
    }

    function isString(v) {
      return typeof v === "string";
    }

    function isObject(v) {
      return v && !Array.isArray(v) && (typeof v) === "object";
    }

    function isArray(v) {
      return Array.isArray(v);
    }

    function isFunction(fun) {
      return fun && {}.toString.call(fun) === '[object Function]';
    }

    function isElement(v) {
      return v && v.nodeType === Node.ELEMENT_NODE;
    }

    function isDocument(v) {
      return v === document;
    }

    function isPrimitive(v) {
      if (v == null) return false;
      var t = typeof v;
      return t === "string" || t === "number" || t === "boolean";
    }

    G.language = window.navigator.language;

    /********************************************************************************
     * Messages
     */

    G.messages = {};

    (function (messages) {
      messages.en = {};
      messages.ja = {};
      messages["ja-JP"] = ja;
      var en = messages.en;
      var ja = messages.ja;
      var key;

      // key requires to begin with "#" because it's to retrieve in html comment.

      key = "#a-for-b";
      en[key] = "{a} for {b}";
      ja[key] = "{b}には{a}";

      key = "#cancel";
      en[key] = "Cancel";
      ja[key] = "キャンセル";

      key = "#confirmation";
      en[key] = "Confirmation";
      ja[key] = "確認";

      key = "#confirmation-message";
      en[key] = "Are you sure?";
      ja[key] = "よろしいですか?";

      key = "#error-of-request";
      en[key] = "Request error";
      ja[key] = "リクエストエラー";

      key = "#error-of-setting-up-requesting";
      en[key] = "Setting up request error";
      ja[key] = "リクエスト準備エラー";

      key = "#hello";
      en[key] = "Hello";
      ja[key] = "こんにちは";

      key = "#http-status-400";
      en[key] = "Bad Request";
      ja[key] = "不正なリクエストです";

      key = "#http-status-403";
      en[key] = "Forbidden";
      ja[key] = "禁止されています";

      key = "#http-status-404";
      en[key] = "Not Found";
      ja[key] = "見つかりません";

      key = "#http-status-500";
      en[key] = "Internal Server Error";
      ja[key] = "サーバー内部エラー";

      key = "#length-min-max";
      en[key] = "Minimum {min} characters and maximum {max} characters.";
      ja[key] = "{min}文字以上、{max}文字以下で入力して下さい";

      key = "#login-failed";
      en[key] = "The login attempt failed.";
      ja[key] = "ログインに失敗しました";

      key = "#login-succeeded";
      en[key] = "The login attempt succeeded.";
      ja[key] = "ログインに成功しました";

      key = "#ok";
      en[key] = "OK";
      ja[key] = "OK";

      key = "#please-input";
      en[key] = "Please input";
      ja[key] = "入力して下さい";

      key = "#updated";
      en[key] = "Updated";
      ja[key] = "更新しました";

      key = "#value-missing";
      en[key] = "Requires";
      ja[key] = "必須です";

      key = "#violation-duplication";
      en[key] = "'{value}' was duplicated";
      ja[key] = "'{value}' は重複しています";

      key = "#violation-minlength";
      en[key] = "Requires {minlength} characters minimum";
      ja[key] = "{minlength}文字以上必要です";

    })(G.messages);

    var keyRex = /(#[\w-]+)\s*(\{[^}]+\})?/;
    var paramsRex = /\{([^}]+)\}/g;

    /**
     * Retrieves message and replace placeholer by paramters
     * 
     * Examples.
     * 
     * Case 1:
     * Only key, no paramters.
     * keyText: '#key', params: undefined
     * 
     * Case 2:
     * Key and inline paramters.
     * keyText: '#key {"ppp": "vvv"}', params: undefined
     * 
     * Case 3:
     * Key and argument paramters.
     * keyText: '#key', params: {"ppp": "vvv"}
     */
    G.getMsg = function getMsg(keyText, params) {
      var keyResult = keyRex.exec(keyText);
      if (keyResult === null) return "";

      // Peels key
      // [1] is first captured string
      var key = keyResult[1];
      key = (key || "").trim();

      // Load message
      var msglng = this.messages[this.language] || this.messages.en;
      var msg = (msglng && msglng[key]) || "";

      // Prepare inline params
      // [2] is second captured string
      var inlineParamsString = keyResult[2];
      var inlineParams = null;
      if (inlineParamsString) {
        try {
          inlineParams = JSON.parse(inlineParamsString);
        } catch (e) {}
      }
      if (!inlineParams || typeof inlineParams !== "object") {
        inlineParams = {};
      }

      // Sanitize params argurment
      if (!params || typeof params !== "object") {
        params = {};
      }

      // Replace placeholders in the message by params or inline params
      var paramResult;
      paramsRex.lastIndex = 0;
      paramResult = paramsRex.exec(msg);
      while (paramResult) {
        // paramResult[1] is 'aaa' if msg is '{aaa}'.
        var name = paramResult[1];
        var value;
        if (name in params) {
          value = params[name];
        } else if (name in inlineParams) {
          value = inlineParams[name];
        } else {
          value = "";
        }

        // paramResult[0] is '{aaa}' if msg is '{aaa}'.
        msg = msg.replace(paramResult[0], value);

        paramResult = paramsRex.exec(paramResult.input);
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

    /********************************************************************************
     * catcher
     */
    G.catcher = function (obj) {
      return function (error) {
        if (error.response) {
          obj.status = "#http-status-" + error.response.status;
        } else if (error.request) {
          obj.status = "#error-of-request";
        } else {
          obj.status = "#error-of-setting-up-requesting";
        }
      };
    };

    /********************************************************************************
     * modal
     */

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
        m.header.innerHTML = m.opts.header || "<h2>" + G.getMsg(m.header.innerHTML) + "</h2>";

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
          document.body.appendChild(this.placeholder);
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

    /********************************************************************************
     * snackbar
     */
    Global.snackbar = (function () {
      return function (arg) {
        var self = Global.snackbar;
        self.html = '' +
          '<div class="snackbar">' +
          '  <div class="window-btn-belt contact text-right" style="height: 20px; padding-right: 8px; border-bottom: 1px solid #555;">' +
          '      <!-- https://fontawesome.com/icons?d=gallery&s=solid&m=free -->' +
          '      <div class="window-btn min ib bdr1 pad4 none">' +
          '          <i class="far fa-window-minimize"></i>' +
          '      </div>' +
          '      <div class="window-btn max ib bdr1 pad4 none">' +
          '          <i class="far fa-window-maximize"></i>' +
          '      </div>' +
          '      <div class="window-btn close ib bdr1 pad4 none">' +
          '          <i class="far fa-window-close"></i>' +
          '      </div>' +
          '  </div>' +
          '  <div class="belt message">' +
          '  </div>' +
          '</div>' +
          '';

        var elm;
        if (isString(arg)) {
          elm = document.querySelector(arg);
          if (elm === null) {
            throw Error("Target element was not found by the selector string");
          }
        } else if (isElement(arg)) {
          elm = arg;
        } else {
          throw Error("Bad arg. The arg requires string for selector or target elemet");
        }

        elm.innerHTML = self.html;
        self.element = elm.querySelector(".snackbar");
        self.minBtn = self.element.querySelector(".window-btn.min");
        self.maxBtn = self.element.querySelector(".window-btn.max");
        self.closeBtn = self.element.querySelector(".window-btn.close");
        self.messageDiv = self.element.querySelector(".message");
        self.maximize = function () {
          self.minBtn.classList.remove("none");
          self.maxBtn.classList.add("none");
          // self.closeBtn.classList.remove("none");
          self.element.style.top = "calc(100vh - " + self.element.clientHeight + "px)";
        };
        self.minimize = function () {
          self.minBtn.classList.add("none");
          self.maxBtn.classList.remove("none");
          // self.closeBtn.classList.remove("none");
          self.element.style.top = "calc(100vh - " + self.element.querySelector(".window-btn-belt").clientHeight + "px)";
        };
        self.close = function () {
          self.minBtn.classList.add("none");
          self.maxBtn.classList.add("none");
          self.closeBtn.classList.add("none");
          self.element.style.top = "calc(100vh - " + self.element.querySelector(".window-btn-belt").clientHeight + "px)";
        };
        elm.querySelector(".window-btn.min").addEventListener("click", self.minimize);
        elm.querySelector(".window-btn.max").addEventListener("click", self.maximize);
        elm.querySelector(".window-btn.close").addEventListener("click", self.close);
        return self;
      };
    })();

    // Automatically prepare snackbar
    window.addEventListener("load", function (event) {
      if (Global.config.snackbarAutoloading) {
        var elm = document.getElementById("snackbar");
        if (elm === null) {
          elm = document.createElement("div");
          elm.id = "snackbar";
          document.body.appendChild(elm);
        }
        if (elm) {
          Global.snackbar(elm);
        }
      }
    });

    Global.snackbarByVlidity = function (src) {

      if (!src) {
        throw Error("src wasn't given.");
      }

      // Call this method again if src was an array.
      if (Array.isArray(src)) {
        for (var i = 0; i < src.length; ++i) {
          if (!Global.snackbarByVlidity(src[i])) {
            return false;
          }
        }
        return true;
      }

      // Call this method again if src was selector string.
      if (typeof src === "string") {
        var elms = document.querySelectorAll(src);
        for (var j = 0; j < elms.length; ++j) {
          if (!Global.snackbarByVlidity(elms.item(j))) {
            return false;
          }
        }
        return true;
      }

      var v = null,
        elm = null;
      // src was an element
      if (src.validity) {
        v = src.validity;
        elm = src;
      } else if (typeof src.valid === "boolean") {
        // src was a ValidityState Object
        v = src;
      }

      if (!v) {
        throw Error("It couldn't detect ValidityState Object by src.");
      }

      var props = ["valueMissing"];
      for (var k = 0; k < props.length; ++k) {
        if (v[props[k]]) {
          Global.snackbar.messageDiv.innerText = Global.getMsg("#" + Global.camelToKebab(props[k]));
          Global.snackbar.maximize();
          if (elm) {
            elm.focus();
          }
          break;
        }
      }

      return v.valid;
    };

    /**
     * Show snackbar by violations that is validated in host
     * 
     * @returns false if violation exists
     */
    Global.snackbarByViolations = function (violations) {

      if (!violations || !Array.isArray(violations)) {
        throw Error("violations weren't given.");
      }

      if (violations.length === 0) return true;

      var messages = [];
      for (var i = 0; i < violations.length; ++i) {
        var violation = violations[i];
        var message = Global.getMsg("#violation-" + violation.violation, violation);

        // Find label for the name
        var name = violation.name;
        var labelElm = document.querySelector("label[for='" + name + "'], th." + name + ", th ." + name);
        if (labelElm) {
          message = Global.getMsg("#a-for-b", {
            a: message,
            b: labelElm.textContent.trim()
          });
        }
        messages.push(message);
      }

      var html = "";
      for (var j = 0; j < messages.length; ++j) {
        html += "<p>" + messages[j] + "</p>";
      }

      Global.snackbar.messageDiv.innerHTML = html;
      Global.snackbar.maximize();

      return false;
    };

    /**
     * Show snackbar from error produced by communication
     * 
     * @returns false if violation exists
     */
    Global.snackbarByCatchFunction = function () {
      return function (error) {
        var key;
        if (error.response) {
          key = "#http-status-" + error.response.status;
          console.log(error.response);
        } else if (error.request) {
          key = "#error-of-request";
        } else {
          key = "#error-of-setting-up-requesting";
        }
        Global.snackbar.messageDiv.textContent = Global.getMsg(key);
        Global.snackbar.maximize();
      };
    };

    return Global;
  })();

});