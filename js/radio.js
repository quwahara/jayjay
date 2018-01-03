(function(definition) {

  // CommonJS
  if (typeof exports === "object") {
    module.exports = definition();

    // RequireJS
  } else if (typeof define === "function" && define.amd) {
    define(definition);

    // <script>
  } else {
    Radio = definition();
  }

})(function() {
  'use strict';

  // Utils

  function isPrimitive(value) {
    var type = typeof value;
    return value == null || type == "undefined" || type == "boolean" || type == "number" || type == "string";
  }

  function isObject(value) {
    return value != null && value.constructor !== Array && (typeof value) == "object";
  }

  function isArray(value) {
    return value != null && value.constructor === Array;
  }

  function isFunction(value) {
    return typeof value === "function";
  }

  function cloneArrayLikeObject(array) {
    var clone = [];
    clone.length = array.length;
    for (var i = 0; i < array.length; ++i) {
      clone[i] = array[i];
    }
    return clone;
  }

  //
  // Radio
  // 

  var Radio = function Radio(options) {
    this.create(options);
  }

  Radio.prototype = {
    create : function(options) {
      Object.assign(this, options.model);
      this.$station = prepareObjectStation(this);
      Object.assign(this, options.methods);
      compileElements(options.root.children, this);

      if (options.phase) {
        if (isFunction(options.phase.activated)) {
          options.phase.activated.call(this);
        }
      }

      return this;
    }
  };

  Radio.utils = {};
  Radio.utils.queries = function() {
    var q = {};
    if (!window || !window.location || !window.location.search)
      return q;
    window.location.search.slice(1).split("&").forEach(function(nv) {
      var spl = nv.split("=");
      q[spl[0]] = spl[1];
    });
    return q;
  };

  function prepareObjectStation(actives) {
    var $station = {};
    $station.$channels = [];
    $station.$stations = [];

    for ( var prop in actives) {
      var value = actives[prop];
      if (isPrimitive(value)) {
        $station.$stations.push(prepareObjectPrimitiveStation($station, actives, prop));
      } else if (isObject(value)) {
        $station[prop] = prepareObjectStation(value);
      } else if (isArray(value)) {
        var substation = $station[prop] = prepareArrayStation(value, actives);
        (function() {
          Object.defineProperty(actives, prop, {
            get : function() {
              return substation.$value;
            },
            set : function(value) {
              console.log(value);
              substation.cast(value, actives);
            },
            enumerable : true,
            configurable : true
          });
        })();

      } // if
    } // for
    return $station;
  }
  
  

  function prepareArrayStation(activeArray, object) {
    var station = [];
    station.casting = false;
    station.object = object;
    station.$value = activeArray;
    station.receivers = [];
    station.subscribe = function(receiver) {
      receiver.rx(this);
      this.receivers.push(receiver);
    };
    station.cast = function(activeArray, src) {
      if (this.casting)
        return;
      this.casting = true;
      this.$value = activateArray(activeArray, station);
      for (var i = 0; i < this.receivers.length; ++i) {
        var receiver = this.receivers[i];
        if (receiver.object === src)
          continue;
        receiver.rx(this);
        // receiver.rx.call(receiver, this);
      }
      this.casting = false;
    };
    station.cast(activeArray, object);
    return station;
  }

  function activateArray(activeArray, station) {
    for (var i = 0; i < activeArray.length; ++i) {

      var value = activeArray[i];
      if (isPrimitive(value)) {
        // (function() {
        // var channel = new Channel();
        // station[i] = channel;
        // channel.$value = value;
        // })();
        throw new Error("Not implemented")

      } else if (isObject(value)) {
        // var substation = {};
        // station[i] = substation;
        // substation.$value = value;
        // prepareObjectStation(substation, value);
        station[i] = prepareObjectStation(value);
        station[i].$value = value;

      } else if (isArray(value)) {
        station[i] = prepareArrayStation(value, activeArray);
      } // if
    } // for
    return activeArray;
  }

  function prepareObjectPrimitiveStation(parentStation, target, prop) {
    return (function() {
      var station = new ObjectPrimitiveStation();
      Object.defineProperty(parentStation, prop, {
        get : function() {
          return station;
        },
        enumerable : true,
        configurable : true
      });
      station.$target = target;
      station.$prop = prop;
      station.$value = target[prop];
      var receiver = {
        object : target,
        rx : function(value) {
          station.$value = value;
        }
      };
      station.subscribe(receiver);
      Object.defineProperty(target, prop, {
        get : function() {
          return station.$value;
        },
        set : function(value) {
          station.$value = value;
          station.cast(value, station.$prop, station.$target);
        },
        enumerable : true,
        configurable : true
      });
      return station;
    })();
  }

  var ObjectPrimitiveStation = function ObjectPrimitiveStation() {
    this.receivers = [];
  };

  ObjectPrimitiveStation.prototype = {
    casting : false,
    $value : null,
    receivers : null,
    subscribe : function(receiver) {
      receiver.rx(this.$value);
      this.receivers.push(receiver);
    },
    cast : function(value, src) {
      if (this.casting)
        return;
      this.casting = true;
      this.value = value;
      for (var i = 0; i < this.receivers.length; ++i) {
        var receiver = this.receivers[i];
        if (receiver.object === src)
          continue;
        // >>
        receiver.rx(value);
        // receiver.rx.call(receiver.object, value);
      }
      this.casting = false;
    }
  };

  function compileAttrAttr(attr, elm, self) {
    var attrName = attr.name.substr("r-attr:".length);
    var model = attr.value;

    var station = loadStation(model, self);
    var receiver = {
      object : elm,
      attrName : attrName,
      rx : function(value) {
        this.object.setAttribute(this.attrName, value);
      }
    };
    station.subscribe(receiver);

  }

  function compileAttrValue(attr, elm, self) {
    var eventName = attr.name.substr("r-value:".length);
    var model = attr.value;

    var station = loadStation(model, self);
    var receiver = {
      object : elm,
      rx : function(value) {
        elm.value = value;
      }
    };
    station.subscribe(receiver);

    elm.addEventListener(eventName, (function() {
      station.cast(elm.value, elm);
    }));
  }

  function compileAttrText(attr, elm, self) {
    var station = loadStation(attr.value, self);
    var receiver = {
      object : elm,
      rx : function(value) {
        // >>
        // elm.innerText = value;
        this.object.innerText = value;
      }
    };
    station.subscribe(receiver);
  }

  function loadStation(model, self) {
    var keys = Object.keys(self);
    var func = eval("(function (" + keys.join(",") + "){ return $station." + model + "; }" + ")");
    var values = [];
    for ( var key in self) {
      values.push(self[key]);
    }
    return func.apply(self, values);
  }

  function buildHandler(handler, elm, self) {
    return (function () {
      var $self = self;
      // Enclose iteration contexts
      var $elm = elm;
      var $index = self.$index;
      var $item = self.$item;
      var bindHndler = handler.replace("(", ".bind($self)(");
      return (function() {
        $self.$event = arguments[0];
        $self.$elm = $elm;
        $self.$index = $index;
        $self.$item = $item;
        var keys = Object.keys($self);
        var values = [];
        for (var key in $self) {
          values.push($self[key]);
        }
        var prms = keys.join(',');
        // Create a function for the event invocation.
        // You can access Radio instance members in the created the function.
        // To be able to access the members of Radio instance,
        // the created function is called by the arguments
        // that are the members of Radio instance.
        var func = eval(
          ' (function (' + prms + ') {' +
          '   return ' + bindHndler + ';' +
          ' })'
        );
        return func.apply($self, values);
      });
    })();
  }

  function compileAttrOn(attr, elm, self) {
    var eventName = attr.name.substr("r-on:".length);
    var handlerName = attr.value;
    var handler = self[handlerName];
    console.log(handlerName, self, handler);
    elm.addEventListener(eventName, buildHandler(handlerName, elm, self).bind(self));
  }

  function compileAttrFor(attr, elm, self) {
    var parentElm = elm.parentElement;
    var model = elm.getAttribute("r-for");
    elm.removeAttribute("r-for");
    var html = elm.outerHTML;
    var arrayStation = loadStation(model, self);

    var receiver = {
      parentElm : elm.parentElement,
      html : elm.outerHTML,
      object : elm,
      rx : function(arrayStation) {
        var tmpElm = document.createElement(this.parentElm.tagName);
        this.parentElm.innerHTML = "";
        for (var i = 0, len = arrayStation.length; i < len; ++i) {
          var station = arrayStation[i];
          self.$index = i;
          self.$item = station.$value;
          self.$station.$item = station;
          tmpElm.innerHTML = html;
          this.parentElm.appendChild(tmpElm.firstElementChild);
          var childElm = this.parentElm.lastElementChild;
          compileElement(childElm, self);
        }
      }
    };

    arrayStation.subscribe(receiver);
  }

  function compileAttr(attr, elm, self) {
    if (attr.name.indexOf("r-value:") === 0) {
      compileAttrValue(attr, elm, self);
    } else if (attr.name === "r-text") {
      compileAttrText(attr, elm, self);
    } else if (attr.name.indexOf("r-attr:") === 0) {
      compileAttrAttr(attr, elm, self);
    } else if (attr.name.indexOf("r-on:") === 0) {
      compileAttrOn(attr, elm, self);
    }
  }

  function compileElements(elms, self) {
    for (var i = 0, len = elms.length; i < len; ++i) {
      compileElement(elms[i], self);
    }
  }

  function compileElement(elm, self) {

    var isFor = elm.hasAttribute("r-for");
    if (isFor) {
      var attr = elm.getAttributeNode("r-for");
      compileAttrFor(attr, elm, self);
      return;
    }

    var attrs = cloneArrayLikeObject(elm.attributes);
    var tag = elm.tagName.toLocaleLowerCase();
    for (var i = 0, len = attrs.length; i < len; ++i) {
      compileAttr(attrs[i], elm, self);
    }
    compileElements(elm.children, self);
  }

  return Radio;
});
