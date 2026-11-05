/*! jQuery v2.2.4 | (c) jQuery Foundation | jquery.org/license */
!function (a, b) {
  "object" == typeof module && "object" == typeof module.exports ? module.exports = a.document ? b(a, !0) : function (a) {
    if (!a.document) throw new Error("jQuery requires a window with a document");
    return b(a)
  } : b(a)
}("undefined" != typeof window ? window : this, function (a, b) {
  var c = [], d = a.document, e = c.slice, f = c.concat, g = c.push, h = c.indexOf, i = {}, j = i.toString,
    k = i.hasOwnProperty, l = {}, m = "2.2.4", n = function (a, b) {
      return new n.fn.init(a, b)
    }, o = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, p = /^-ms-/, q = /-([\da-z])/gi, r = function (a, b) {
      return b.toUpperCase()
    };
  n.fn = n.prototype = {
    jquery: m, constructor: n, selector: "", length: 0, toArray: function () {
      return e.call(this)
    }, get: function (a) {
      return null != a ? 0 > a ? this[a + this.length] : this[a] : e.call(this)
    }, pushStack: function (a) {
      var b = n.merge(this.constructor(), a);
      return b.prevObject = this, b.context = this.context, b
    }, each: function (a) {
      return n.each(this, a)
    }, map: function (a) {
      return this.pushStack(n.map(this, function (b, c) {
        return a.call(b, c, b)
      }))
    }, slice: function () {
      return this.pushStack(e.apply(this, arguments))
    }, first: function () {
      return this.eq(0)
    }, last: function () {
      return this.eq(-1)
    }, eq: function (a) {
      var b = this.length, c = +a + (0 > a ? b : 0);
      return this.pushStack(c >= 0 && b > c ? [this[c]] : [])
    }, end: function () {
      return this.prevObject || this.constructor()
    }, push: g, sort: c.sort, splice: c.splice
  }, n.extend = n.fn.extend = function () {
    var a, b, c, d, e, f, g = arguments[0] || {}, h = 1, i = arguments.length, j = !1;
    for ("boolean" == typeof g && (j = g, g = arguments[h] || {}, h++), "object" == typeof g || n.isFunction(g) || (g = {}), h === i && (g = this, h--); i > h; h++) if (null != (a = arguments[h])) for (b in a) c = g[b], d = a[b], g !== d && (j && d && (n.isPlainObject(d) || (e = n.isArray(d))) ? (e ? (e = !1, f = c && n.isArray(c) ? c : []) : f = c && n.isPlainObject(c) ? c : {}, g[b] = n.extend(j, f, d)) : void 0 !== d && (g[b] = d));
    return g
  }, n.extend({
    expando: "jQuery" + (m + Math.random()).replace(/\D/g, ""), isReady: !0, error: function (a) {
      throw new Error(a)
    }, noop: function () {
    }, isFunction: function (a) {
      return "function" === n.type(a)
    }, isArray: Array.isArray, isWindow: function (a) {
      return null != a && a === a.window
    }, isNumeric: function (a) {
      var b = a && a.toString();
      return !n.isArray(a) && b - parseFloat(b) + 1 >= 0
    }, isPlainObject: function (a) {
      var b;
      if ("object" !== n.type(a) || a.nodeType || n.isWindow(a)) return !1;
      if (a.constructor && !k.call(a, "constructor") && !k.call(a.constructor.prototype || {}, "isPrototypeOf")) return !1;
      for (b in a) ;
      return void 0 === b || k.call(a, b)
    }, isEmptyObject: function (a) {
      var b;
      for (b in a) return !1;
      return !0
    }, type: function (a) {
      return null == a ? a + "" : "object" == typeof a || "function" == typeof a ? i[j.call(a)] || "object" : typeof a
    }, globalEval: function (a) {
      var b, c = eval;
      a = n.trim(a), a && (1 === a.indexOf("use strict") ? (b = d.createElement("script"), b.text = a, d.head.appendChild(b).parentNode.removeChild(b)) : c(a))
    }, camelCase: function (a) {
      return a.replace(p, "ms-").replace(q, r)
    }, nodeName: function (a, b) {
      return a.nodeName && a.nodeName.toLowerCase() === b.toLowerCase()
    }, each: function (a, b) {
      var c, d = 0;
      if (s(a)) {
        for (c = a.length; c > d; d++) if (b.call(a[d], d, a[d]) === !1) break
      } else for (d in a) if (b.call(a[d], d, a[d]) === !1) break;
      return a
    }, trim: function (a) {
      return null == a ? "" : (a + "").replace(o, "")
    }, makeArray: function (a, b) {
      var c = b || [];
      return null != a && (s(Object(a)) ? n.merge(c, "string" == typeof a ? [a] : a) : g.call(c, a)), c
    }, inArray: function (a, b, c) {
      return null == b ? -1 : h.call(b, a, c)
    }, merge: function (a, b) {
      for (var c = +b.length, d = 0, e = a.length; c > d; d++) a[e++] = b[d];
      return a.length = e, a
    }, grep: function (a, b, c) {
      for (var d, e = [], f = 0, g = a.length, h = !c; g > f; f++) d = !b(a[f], f), d !== h && e.push(a[f]);
      return e
    }, map: function (a, b, c) {
      var d, e, g = 0, h = [];
      if (s(a)) for (d = a.length; d > g; g++) e = b(a[g], g, c), null != e && h.push(e); else for (g in a) e = b(a[g], g, c), null != e && h.push(e);
      return f.apply([], h)
    }, guid: 1, proxy: function (a, b) {
      var c, d, f;
      return "string" == typeof b && (c = a[b], b = a, a = c), n.isFunction(a) ? (d = e.call(arguments, 2), f = function () {
        return a.apply(b || this, d.concat(e.call(arguments)))
      }, f.guid = a.guid = a.guid || n.guid++, f) : void 0
    }, now: Date.now, support: l
  }), "function" == typeof Symbol && (n.fn[Symbol.iterator] = c[Symbol.iterator]), n.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "), function (a, b) {
    i["[object " + b + "]"] = b.toLowerCase()
  });

  function s(a) {
    var b = !!a && "length" in a && a.length, c = n.type(a);
    return "function" === c || n.isWindow(a) ? !1 : "array" === c || 0 === b || "number" == typeof b && b > 0 && b - 1 in a
  }

  var t = function (a) {
    var b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u = "sizzle" + 1 * new Date, v = a.document, w = 0,
      x = 0, y = ga(), z = ga(), A = ga(), B = function (a, b) {
        return a === b && (l = !0), 0
      }, C = 1 << 31, D = {}.hasOwnProperty, E = [], F = E.pop, G = E.push, H = E.push, I = E.slice,
      J = function (a, b) {
        for (var c = 0, d = a.length; d > c; c++) if (a[c] === b) return c;
        return -1
      },
      K = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",
      L = "[\\x20\\t\\r\\n\\f]", M = "(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",
      N = "\\[" + L + "*(" + M + ")(?:" + L + "*([*^$|!~]?=)" + L + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + M + "))|)" + L + "*\\]",
      O = ":(" + M + ")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|" + N + ")*)|.*)\\)|)",
      P = new RegExp(L + "+", "g"), Q = new RegExp("^" + L + "+|((?:^|[^\\\\])(?:\\\\.)*)" + L + "+$", "g"),
      R = new RegExp("^" + L + "*," + L + "*"), S = new RegExp("^" + L + "*([>+~]|" + L + ")" + L + "*"),
      T = new RegExp("=" + L + "*([^\\]'\"]*?)" + L + "*\\]", "g"), U = new RegExp(O),
      V = new RegExp("^" + M + "$"), W = {
        ID: new RegExp("^#(" + M + ")"),
        CLASS: new RegExp("^\\.(" + M + ")"),
        TAG: new RegExp("^(" + M + "|[*])"),
        ATTR: new RegExp("^" + N),
        PSEUDO: new RegExp("^" + O),
        CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + L + "*(even|odd|(([+-]|)(\\d*)n|)" + L + "*(?:([+-]|)" + L + "*(\\d+)|))" + L + "*\\)|)", "i"),
        bool: new RegExp("^(?:" + K + ")$", "i"),
        needsContext: new RegExp("^" + L + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + L + "*((?:-\\d)?\\d*)" + L + "*\\)|)(?=[^-]|$)", "i")
      }, X = /^(?:input|select|textarea|button)$/i, Y = /^h\d$/i, Z = /^[^{]+\{\s*\[native \w/,
      $ = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/, _ = /[+~]/, aa = /'|\\/g,
      ba = new RegExp("\\\\([\\da-f]{1,6}" + L + "?|(" + L + ")|.)", "ig"), ca = function (a, b, c) {
        var d = "0x" + b - 65536;
        return d !== d || c ? b : 0 > d ? String.fromCharCode(d + 65536) : String.fromCharCode(d >> 10 | 55296, 1023 & d | 56320)
      }, da = function () {
        m()
      };
    try {
      H.apply(E = I.call(v.childNodes), v.childNodes), E[v.childNodes.length].nodeType
    } catch (ea) {
      H = {
        apply: E.length ? function (a, b) {
          G.apply(a, I.call(b))
        } : function (a, b) {
          var c = a.length, d = 0;
          while (a[c++] = b[d++]) ;
          a.length = c - 1
        }
      }
    }

    function fa(a, b, d, e) {
      var f, h, j, k, l, o, r, s, w = b && b.ownerDocument, x = b ? b.nodeType : 9;
      if (d = d || [], "string" != typeof a || !a || 1 !== x && 9 !== x && 11 !== x) return d;
      if (!e && ((b ? b.ownerDocument || b : v) !== n && m(b), b = b || n, p)) {
        if (11 !== x && (o = $.exec(a))) if (f = o[1]) {
          if (9 === x) {
            if (!(j = b.getElementById(f))) return d;
            if (j.id === f) return d.push(j), d
          } else if (w && (j = w.getElementById(f)) && t(b, j) && j.id === f) return d.push(j), d
        } else {
          if (o[2]) return H.apply(d, b.getElementsByTagName(a)), d;
          if ((f = o[3]) && c.getElementsByClassName && b.getElementsByClassName) return H.apply(d, b.getElementsByClassName(f)), d
        }
        if (c.qsa && !A[a + " "] && (!q || !q.test(a))) {
          if (1 !== x) w = b, s = a; else if ("object" !== b.nodeName.toLowerCase()) {
            (k = b.getAttribute("id")) ? k = k.replace(aa, "\\$&") : b.setAttribute("id", k = u), r = g(a), h = r.length, l = V.test(k) ? "#" + k : "[id='" + k + "']";
            while (h--) r[h] = l + " " + qa(r[h]);
            s = r.join(","), w = _.test(a) && oa(b.parentNode) || b
          }
          if (s) try {
            return H.apply(d, w.querySelectorAll(s)), d
          } catch (y) {
          } finally {
            k === u && b.removeAttribute("id")
          }
        }
      }
      return i(a.replace(Q, "$1"), b, d, e)
    }

    function ga() {
      var a = [];

      function b(c, e) {
        return a.push(c + " ") > d.cacheLength && delete b[a.shift()], b[c + " "] = e
      }

      return b
    }

    function ha(a) {
      return a[u] = !0, a
    }

    function ia(a) {
      var b = n.createElement("div");
      try {
        return !!a(b)
      } catch (c) {
        return !1
      } finally {
        b.parentNode && b.parentNode.removeChild(b), b = null
      }
    }

    function ja(a, b) {
      var c = a.split("|"), e = c.length;
      while (e--) d.attrHandle[c[e]] = b
    }

    function ka(a, b) {
      var c = b && a,
        d = c && 1 === a.nodeType && 1 === b.nodeType && (~b.sourceIndex || C) - (~a.sourceIndex || C);
      if (d) return d;
      if (c) while (c = c.nextSibling) if (c === b) return -1;
      return a ? 1 : -1
    }

    function la(a) {
      return function (b) {
        var c = b.nodeName.toLowerCase();
        return "input" === c && b.type === a
      }
    }

    function ma(a) {
      return function (b) {
        var c = b.nodeName.toLowerCase();
        return ("input" === c || "button" === c) && b.type === a
      }
    }

    function na(a) {
      return ha(function (b) {
        return b = +b, ha(function (c, d) {
          var e, f = a([], c.length, b), g = f.length;
          while (g--) c[e = f[g]] && (c[e] = !(d[e] = c[e]))
        })
      })
    }

    function oa(a) {
      return a && "undefined" != typeof a.getElementsByTagName && a
    }

    c = fa.support = {}, f = fa.isXML = function (a) {
      var b = a && (a.ownerDocument || a).documentElement;
      return b ? "HTML" !== b.nodeName : !1
    }, m = fa.setDocument = function (a) {
      var b, e, g = a ? a.ownerDocument || a : v;
      return g !== n && 9 === g.nodeType && g.documentElement ? (n = g, o = n.documentElement, p = !f(n), (e = n.defaultView) && e.top !== e && (e.addEventListener ? e.addEventListener("unload", da, !1) : e.attachEvent && e.attachEvent("onunload", da)), c.attributes = ia(function (a) {
        return a.className = "i", !a.getAttribute("className")
      }), c.getElementsByTagName = ia(function (a) {
        return a.appendChild(n.createComment("")), !a.getElementsByTagName("*").length
      }), c.getElementsByClassName = Z.test(n.getElementsByClassName), c.getById = ia(function (a) {
        return o.appendChild(a).id = u, !n.getElementsByName || !n.getElementsByName(u).length
      }), c.getById ? (d.find.ID = function (a, b) {
        if ("undefined" != typeof b.getElementById && p) {
          var c = b.getElementById(a);
          return c ? [c] : []
        }
      }, d.filter.ID = function (a) {
        var b = a.replace(ba, ca);
        return function (a) {
          return a.getAttribute("id") === b
        }
      }) : (delete d.find.ID, d.filter.ID = function (a) {
        var b = a.replace(ba, ca);
        return function (a) {
          var c = "undefined" != typeof a.getAttributeNode && a.getAttributeNode("id");
          return c && c.value === b
        }
      }), d.find.TAG = c.getElementsByTagName ? function (a, b) {
        return "undefined" != typeof b.getElementsByTagName ? b.getElementsByTagName(a) : c.qsa ? b.querySelectorAll(a) : void 0
      } : function (a, b) {
        var c, d = [], e = 0, f = b.getElementsByTagName(a);
        if ("*" === a) {
          while (c = f[e++]) 1 === c.nodeType && d.push(c);
          return d
        }
        return f
      }, d.find.CLASS = c.getElementsByClassName && function (a, b) {
        return "undefined" != typeof b.getElementsByClassName && p ? b.getElementsByClassName(a) : void 0
      }, r = [], q = [], (c.qsa = Z.test(n.querySelectorAll)) && (ia(function (a) {
        o.appendChild(a).innerHTML = "<a id='" + u + "'></a><select id='" + u + "-\r\\' msallowcapture=''><option selected=''></option></select>", a.querySelectorAll("[msallowcapture^='']").length && q.push("[*^$]=" + L + "*(?:''|\"\")"), a.querySelectorAll("[selected]").length || q.push("\\[" + L + "*(?:value|" + K + ")"), a.querySelectorAll("[id~=" + u + "-]").length || q.push("~="), a.querySelectorAll(":checked").length || q.push(":checked"), a.querySelectorAll("a#" + u + "+*").length || q.push(".#.+[+~]")
      }), ia(function (a) {
        var b = n.createElement("input");
        b.setAttribute("type", "hidden"), a.appendChild(b).setAttribute("name", "D"), a.querySelectorAll("[name=d]").length && q.push("name" + L + "*[*^$|!~]?="), a.querySelectorAll(":enabled").length || q.push(":enabled", ":disabled"), a.querySelectorAll("*,:x"), q.push(",.*:")
      })), (c.matchesSelector = Z.test(s = o.matches || o.webkitMatchesSelector || o.mozMatchesSelector || o.oMatchesSelector || o.msMatchesSelector)) && ia(function (a) {
        c.disconnectedMatch = s.call(a, "div"), s.call(a, "[s!='']:x"), r.push("!=", O)
      }), q = q.length && new RegExp(q.join("|")), r = r.length && new RegExp(r.join("|")), b = Z.test(o.compareDocumentPosition), t = b || Z.test(o.contains) ? function (a, b) {
        var c = 9 === a.nodeType ? a.documentElement : a, d = b && b.parentNode;
        return a === d || !(!d || 1 !== d.nodeType || !(c.contains ? c.contains(d) : a.compareDocumentPosition && 16 & a.compareDocumentPosition(d)))
      } : function (a, b) {
        if (b) while (b = b.parentNode) if (b === a) return !0;
        return !1
      }, B = b ? function (a, b) {
        if (a === b) return l = !0, 0;
        var d = !a.compareDocumentPosition - !b.compareDocumentPosition;
        return d ? d : (d = (a.ownerDocument || a) === (b.ownerDocument || b) ? a.compareDocumentPosition(b) : 1, 1 & d || !c.sortDetached && b.compareDocumentPosition(a) === d ? a === n || a.ownerDocument === v && t(v, a) ? -1 : b === n || b.ownerDocument === v && t(v, b) ? 1 : k ? J(k, a) - J(k, b) : 0 : 4 & d ? -1 : 1)
      } : function (a, b) {
        if (a === b) return l = !0, 0;
        var c, d = 0, e = a.parentNode, f = b.parentNode, g = [a], h = [b];
        if (!e || !f) return a === n ? -1 : b === n ? 1 : e ? -1 : f ? 1 : k ? J(k, a) - J(k, b) : 0;
        if (e === f) return ka(a, b);
        c = a;
        while (c = c.parentNode) g.unshift(c);
        c = b;
        while (c = c.parentNode) h.unshift(c);
        while (g[d] === h[d]) d++;
        return d ? ka(g[d], h[d]) : g[d] === v ? -1 : h[d] === v ? 1 : 0
      }, n) : n
    }, fa.matches = function (a, b) {
      return fa(a, null, null, b)
    }, fa.matchesSelector = function (a, b) {
      if ((a.ownerDocument || a) !== n && m(a), b = b.replace(T, "='$1']"), c.matchesSelector && p && !A[b + " "] && (!r || !r.test(b)) && (!q || !q.test(b))) try {
        var d = s.call(a, b);
        if (d || c.disconnectedMatch || a.document && 11 !== a.document.nodeType) return d
      } catch (e) {
      }
      return fa(b, n, null, [a]).length > 0
    }, fa.contains = function (a, b) {
      return (a.ownerDocument || a) !== n && m(a), t(a, b)
    }, fa.attr = function (a, b) {
      (a.ownerDocument || a) !== n && m(a);
      var e = d.attrHandle[b.toLowerCase()],
        f = e && D.call(d.attrHandle, b.toLowerCase()) ? e(a, b, !p) : void 0;
      return void 0 !== f ? f : c.attributes || !p ? a.getAttribute(b) : (f = a.getAttributeNode(b)) && f.specified ? f.value : null
    }, fa.error = function (a) {
      throw new Error("Syntax error, unrecognized expression: " + a)
    }, fa.uniqueSort = function (a) {
      var b, d = [], e = 0, f = 0;
      if (l = !c.detectDuplicates, k = !c.sortStable && a.slice(0), a.sort(B), l) {
        while (b = a[f++]) b === a[f] && (e = d.push(f));
        while (e--) a.splice(d[e], 1)
      }
      return k = null, a
    }, e = fa.getText = function (a) {
      var b, c = "", d = 0, f = a.nodeType;
      if (f) {
        if (1 === f || 9 === f || 11 === f) {
          if ("string" == typeof a.textContent) return a.textContent;
          for (a = a.firstChild; a; a = a.nextSibling) c += e(a)
        } else if (3 === f || 4 === f) return a.nodeValue
      } else while (b = a[d++]) c += e(b);
      return c
    }, d = fa.selectors = {
      cacheLength: 50,
      createPseudo: ha,
      match: W,
      attrHandle: {},
      find: {},
      relative: {
        ">": {dir: "parentNode", first: !0},
        " ": {dir: "parentNode"},
        "+": {dir: "previousSibling", first: !0},
        "~": {dir: "previousSibling"}
      },
      preFilter: {
        ATTR: function (a) {
          return a[1] = a[1].replace(ba, ca), a[3] = (a[3] || a[4] || a[5] || "").replace(ba, ca), "~=" === a[2] && (a[3] = " " + a[3] + " "), a.slice(0, 4)
        }, CHILD: function (a) {
          return a[1] = a[1].toLowerCase(), "nth" === a[1].slice(0, 3) ? (a[3] || fa.error(a[0]), a[4] = +(a[4] ? a[5] + (a[6] || 1) : 2 * ("even" === a[3] || "odd" === a[3])), a[5] = +(a[7] + a[8] || "odd" === a[3])) : a[3] && fa.error(a[0]), a
        }, PSEUDO: function (a) {
          var b, c = !a[6] && a[2];
          return W.CHILD.test(a[0]) ? null : (a[3] ? a[2] = a[4] || a[5] || "" : c && U.test(c) && (b = g(c, !0)) && (b = c.indexOf(")", c.length - b) - c.length) && (a[0] = a[0].slice(0, b), a[2] = c.slice(0, b)), a.slice(0, 3))
        }
      },
      filter: {
        TAG: function (a) {
          var b = a.replace(ba, ca).toLowerCase();
          return "*" === a ? function () {
            return !0
          } : function (a) {
            return a.nodeName && a.nodeName.toLowerCase() === b
          }
        }, CLASS: function (a) {
          var b = y[a + " "];
          return b || (b = new RegExp("(^|" + L + ")" + a + "(" + L + "|$)")) && y(a, function (a) {
            return b.test("string" == typeof a.className && a.className || "undefined" != typeof a.getAttribute && a.getAttribute("class") || "")
          })
        }, ATTR: function (a, b, c) {
          return function (d) {
            var e = fa.attr(d, a);
            return null == e ? "!=" === b : b ? (e += "", "=" === b ? e === c : "!=" === b ? e !== c : "^=" === b ? c && 0 === e.indexOf(c) : "*=" === b ? c && e.indexOf(c) > -1 : "$=" === b ? c && e.slice(-c.length) === c : "~=" === b ? (" " + e.replace(P, " ") + " ").indexOf(c) > -1 : "|=" === b ? e === c || e.slice(0, c.length + 1) === c + "-" : !1) : !0
          }
        }, CHILD: function (a, b, c, d, e) {
          var f = "nth" !== a.slice(0, 3), g = "last" !== a.slice(-4), h = "of-type" === b;
          return 1 === d && 0 === e ? function (a) {
            return !!a.parentNode
          } : function (b, c, i) {
            var j, k, l, m, n, o, p = f !== g ? "nextSibling" : "previousSibling", q = b.parentNode,
              r = h && b.nodeName.toLowerCase(), s = !i && !h, t = !1;
            if (q) {
              if (f) {
                while (p) {
                  m = b;
                  while (m = m[p]) if (h ? m.nodeName.toLowerCase() === r : 1 === m.nodeType) return !1;
                  o = p = "only" === a && !o && "nextSibling"
                }
                return !0
              }
              if (o = [g ? q.firstChild : q.lastChild], g && s) {
                m = q, l = m[u] || (m[u] = {}), k = l[m.uniqueID] || (l[m.uniqueID] = {}), j = k[a] || [], n = j[0] === w && j[1], t = n && j[2], m = n && q.childNodes[n];
                while (m = ++n && m && m[p] || (t = n = 0) || o.pop()) if (1 === m.nodeType && ++t && m === b) {
                  k[a] = [w, n, t];
                  break
                }
              } else if (s && (m = b, l = m[u] || (m[u] = {}), k = l[m.uniqueID] || (l[m.uniqueID] = {}), j = k[a] || [], n = j[0] === w && j[1], t = n), t === !1) while (m = ++n && m && m[p] || (t = n = 0) || o.pop()) if ((h ? m.nodeName.toLowerCase() === r : 1 === m.nodeType) && ++t && (s && (l = m[u] || (m[u] = {}), k = l[m.uniqueID] || (l[m.uniqueID] = {}), k[a] = [w, t]), m === b)) break;
              return t -= e, t === d || t % d === 0 && t / d >= 0
            }
          }
        }, PSEUDO: function (a, b) {
          var c, e = d.pseudos[a] || d.setFilters[a.toLowerCase()] || fa.error("unsupported pseudo: " + a);
          return e[u] ? e(b) : e.length > 1 ? (c = [a, a, "", b], d.setFilters.hasOwnProperty(a.toLowerCase()) ? ha(function (a, c) {
            var d, f = e(a, b), g = f.length;
            while (g--) d = J(a, f[g]), a[d] = !(c[d] = f[g])
          }) : function (a) {
            return e(a, 0, c)
          }) : e
        }
      },
      pseudos: {
        not: ha(function (a) {
          var b = [], c = [], d = h(a.replace(Q, "$1"));
          return d[u] ? ha(function (a, b, c, e) {
            var f, g = d(a, null, e, []), h = a.length;
            while (h--) (f = g[h]) && (a[h] = !(b[h] = f))
          }) : function (a, e, f) {
            return b[0] = a, d(b, null, f, c), b[0] = null, !c.pop()
          }
        }), has: ha(function (a) {
          return function (b) {
            return fa(a, b).length > 0
          }
        }), contains: ha(function (a) {
          return a = a.replace(ba, ca), function (b) {
            return (b.textContent || b.innerText || e(b)).indexOf(a) > -1
          }
        }), lang: ha(function (a) {
          return V.test(a || "") || fa.error("unsupported lang: " + a), a = a.replace(ba, ca).toLowerCase(), function (b) {
            var c;
            do if (c = p ? b.lang : b.getAttribute("xml:lang") || b.getAttribute("lang")) return c = c.toLowerCase(), c === a || 0 === c.indexOf(a + "-"); while ((b = b.parentNode) && 1 === b.nodeType);
            return !1
          }
        }), target: function (b) {
          var c = a.location && a.location.hash;
          return c && c.slice(1) === b.id
        }, root: function (a) {
          return a === o
        }, focus: function (a) {
          return a === n.activeElement && (!n.hasFocus || n.hasFocus()) && !!(a.type || a.href || ~a.tabIndex)
        }, enabled: function (a) {
          return a.disabled === !1
        }, disabled: function (a) {
          return a.disabled === !0
        }, checked: function (a) {
          var b = a.nodeName.toLowerCase();
          return "input" === b && !!a.checked || "option" === b && !!a.selected
        }, selected: function (a) {
          return a.parentNode && a.parentNode.selectedIndex, a.selected === !0
        }, empty: function (a) {
          for (a = a.firstChild; a; a = a.nextSibling) if (a.nodeType < 6) return !1;
          return !0
        }, parent: function (a) {
          return !d.pseudos.empty(a)
        }, header: function (a) {
          return Y.test(a.nodeName)
        }, input: function (a) {
          return X.test(a.nodeName)
        }, button: function (a) {
          var b = a.nodeName.toLowerCase();
          return "input" === b && "button" === a.type || "button" === b
        }, text: function (a) {
          var b;
          return "input" === a.nodeName.toLowerCase() && "text" === a.type && (null == (b = a.getAttribute("type")) || "text" === b.toLowerCase())
        }, first: na(function () {
          return [0]
        }), last: na(function (a, b) {
          return [b - 1]
        }), eq: na(function (a, b, c) {
          return [0 > c ? c + b : c]
        }), even: na(function (a, b) {
          for (var c = 0; b > c; c += 2) a.push(c);
          return a
        }), odd: na(function (a, b) {
          for (var c = 1; b > c; c += 2) a.push(c);
          return a
        }), lt: na(function (a, b, c) {
          for (var d = 0 > c ? c + b : c; --d >= 0;) a.push(d);
          return a
        }), gt: na(function (a, b, c) {
          for (var d = 0 > c ? c + b : c; ++d < b;) a.push(d);
          return a
        })
      }
    }, d.pseudos.nth = d.pseudos.eq;
    for (b in {radio: !0, checkbox: !0, file: !0, password: !0, image: !0}) d.pseudos[b] = la(b);
    for (b in {submit: !0, reset: !0}) d.pseudos[b] = ma(b);

    function pa() {
    }

    pa.prototype = d.filters = d.pseudos, d.setFilters = new pa, g = fa.tokenize = function (a, b) {
      var c, e, f, g, h, i, j, k = z[a + " "];
      if (k) return b ? 0 : k.slice(0);
      h = a, i = [], j = d.preFilter;
      while (h) {
        c && !(e = R.exec(h)) || (e && (h = h.slice(e[0].length) || h), i.push(f = [])), c = !1, (e = S.exec(h)) && (c = e.shift(), f.push({
          value: c,
          type: e[0].replace(Q, " ")
        }), h = h.slice(c.length));
        for (g in d.filter) !(e = W[g].exec(h)) || j[g] && !(e = j[g](e)) || (c = e.shift(), f.push({
          value: c,
          type: g,
          matches: e
        }), h = h.slice(c.length));
        if (!c) break
      }
      return b ? h.length : h ? fa.error(a) : z(a, i).slice(0)
    };

    function qa(a) {
      for (var b = 0, c = a.length, d = ""; c > b; b++) d += a[b].value;
      return d
    }

    function ra(a, b, c) {
      var d = b.dir, e = c && "parentNode" === d, f = x++;
      return b.first ? function (b, c, f) {
        while (b = b[d]) if (1 === b.nodeType || e) return a(b, c, f)
      } : function (b, c, g) {
        var h, i, j, k = [w, f];
        if (g) {
          while (b = b[d]) if ((1 === b.nodeType || e) && a(b, c, g)) return !0
        } else while (b = b[d]) if (1 === b.nodeType || e) {
          if (j = b[u] || (b[u] = {}), i = j[b.uniqueID] || (j[b.uniqueID] = {}), (h = i[d]) && h[0] === w && h[1] === f) return k[2] = h[2];
          if (i[d] = k, k[2] = a(b, c, g)) return !0
        }
      }
    }

    function sa(a) {
      return a.length > 1 ? function (b, c, d) {
        var e = a.length;
        while (e--) if (!a[e](b, c, d)) return !1;
        return !0
      } : a[0]
    }

    function ta(a, b, c) {
      for (var d = 0, e = b.length; e > d; d++) fa(a, b[d], c);
      return c
    }

    function ua(a, b, c, d, e) {
      for (var f, g = [], h = 0, i = a.length, j = null != b; i > h; h++) (f = a[h]) && (c && !c(f, d, e) || (g.push(f), j && b.push(h)));
      return g
    }

    function va(a, b, c, d, e, f) {
      return d && !d[u] && (d = va(d)), e && !e[u] && (e = va(e, f)), ha(function (f, g, h, i) {
        var j, k, l, m = [], n = [], o = g.length, p = f || ta(b || "*", h.nodeType ? [h] : h, []),
          q = !a || !f && b ? p : ua(p, m, a, h, i), r = c ? e || (f ? a : o || d) ? [] : g : q;
        if (c && c(q, r, h, i), d) {
          j = ua(r, n), d(j, [], h, i), k = j.length;
          while (k--) (l = j[k]) && (r[n[k]] = !(q[n[k]] = l))
        }
        if (f) {
          if (e || a) {
            if (e) {
              j = [], k = r.length;
              while (k--) (l = r[k]) && j.push(q[k] = l);
              e(null, r = [], j, i)
            }
            k = r.length;
            while (k--) (l = r[k]) && (j = e ? J(f, l) : m[k]) > -1 && (f[j] = !(g[j] = l))
          }
        } else r = ua(r === g ? r.splice(o, r.length) : r), e ? e(null, g, r, i) : H.apply(g, r)
      })
    }

    function wa(a) {
      for (var b, c, e, f = a.length, g = d.relative[a[0].type], h = g || d.relative[" "], i = g ? 1 : 0, k = ra(function (a) {
        return a === b
      }, h, !0), l = ra(function (a) {
        return J(b, a) > -1
      }, h, !0), m = [function (a, c, d) {
        var e = !g && (d || c !== j) || ((b = c).nodeType ? k(a, c, d) : l(a, c, d));
        return b = null, e
      }]; f > i; i++) if (c = d.relative[a[i].type]) m = [ra(sa(m), c)]; else {
        if (c = d.filter[a[i].type].apply(null, a[i].matches), c[u]) {
          for (e = ++i; f > e; e++) if (d.relative[a[e].type]) break;
          return va(i > 1 && sa(m), i > 1 && qa(a.slice(0, i - 1).concat({value: " " === a[i - 2].type ? "*" : ""})).replace(Q, "$1"), c, e > i && wa(a.slice(i, e)), f > e && wa(a = a.slice(e)), f > e && qa(a))
        }
        m.push(c)
      }
      return sa(m)
    }

    function xa(a, b) {
      var c = b.length > 0, e = a.length > 0, f = function (f, g, h, i, k) {
        var l, o, q, r = 0, s = "0", t = f && [], u = [], v = j, x = f || e && d.find.TAG("*", k),
          y = w += null == v ? 1 : Math.random() || .1, z = x.length;
        for (k && (j = g === n || g || k); s !== z && null != (l = x[s]); s++) {
          if (e && l) {
            o = 0, g || l.ownerDocument === n || (m(l), h = !p);
            while (q = a[o++]) if (q(l, g || n, h)) {
              i.push(l);
              break
            }
            k && (w = y)
          }
          c && ((l = !q && l) && r--, f && t.push(l))
        }
        if (r += s, c && s !== r) {
          o = 0;
          while (q = b[o++]) q(t, u, g, h);
          if (f) {
            if (r > 0) while (s--) t[s] || u[s] || (u[s] = F.call(i));
            u = ua(u)
          }
          H.apply(i, u), k && !f && u.length > 0 && r + b.length > 1 && fa.uniqueSort(i)
        }
        return k && (w = y, j = v), t
      };
      return c ? ha(f) : f
    }

    return h = fa.compile = function (a, b) {
      var c, d = [], e = [], f = A[a + " "];
      if (!f) {
        b || (b = g(a)), c = b.length;
        while (c--) f = wa(b[c]), f[u] ? d.push(f) : e.push(f);
        f = A(a, xa(e, d)), f.selector = a
      }
      return f
    }, i = fa.select = function (a, b, e, f) {
      var i, j, k, l, m, n = "function" == typeof a && a, o = !f && g(a = n.selector || a);
      if (e = e || [], 1 === o.length) {
        if (j = o[0] = o[0].slice(0), j.length > 2 && "ID" === (k = j[0]).type && c.getById && 9 === b.nodeType && p && d.relative[j[1].type]) {
          if (b = (d.find.ID(k.matches[0].replace(ba, ca), b) || [])[0], !b) return e;
          n && (b = b.parentNode), a = a.slice(j.shift().value.length)
        }
        i = W.needsContext.test(a) ? 0 : j.length;
        while (i--) {
          if (k = j[i], d.relative[l = k.type]) break;
          if ((m = d.find[l]) && (f = m(k.matches[0].replace(ba, ca), _.test(j[0].type) && oa(b.parentNode) || b))) {
            if (j.splice(i, 1), a = f.length && qa(j), !a) return H.apply(e, f), e;
            break
          }
        }
      }
      return (n || h(a, o))(f, b, !p, e, !b || _.test(a) && oa(b.parentNode) || b), e
    }, c.sortStable = u.split("").sort(B).join("") === u, c.detectDuplicates = !!l, m(), c.sortDetached = ia(function (a) {
      return 1 & a.compareDocumentPosition(n.createElement("div"))
    }), ia(function (a) {
      return a.innerHTML = "<a href='#'></a>", "#" === a.firstChild.getAttribute("href")
    }) || ja("type|href|height|width", function (a, b, c) {
      return c ? void 0 : a.getAttribute(b, "type" === b.toLowerCase() ? 1 : 2)
    }), c.attributes && ia(function (a) {
      return a.innerHTML = "<input/>", a.firstChild.setAttribute("value", ""), "" === a.firstChild.getAttribute("value")
    }) || ja("value", function (a, b, c) {
      return c || "input" !== a.nodeName.toLowerCase() ? void 0 : a.defaultValue
    }), ia(function (a) {
      return null == a.getAttribute("disabled")
    }) || ja(K, function (a, b, c) {
      var d;
      return c ? void 0 : a[b] === !0 ? b.toLowerCase() : (d = a.getAttributeNode(b)) && d.specified ? d.value : null
    }), fa
  }(a);
  n.find = t, n.expr = t.selectors, n.expr[":"] = n.expr.pseudos, n.uniqueSort = n.unique = t.uniqueSort, n.text = t.getText, n.isXMLDoc = t.isXML, n.contains = t.contains;
  var u = function (a, b, c) {
    var d = [], e = void 0 !== c;
    while ((a = a[b]) && 9 !== a.nodeType) if (1 === a.nodeType) {
      if (e && n(a).is(c)) break;
      d.push(a)
    }
    return d
  }, v = function (a, b) {
    for (var c = []; a; a = a.nextSibling) 1 === a.nodeType && a !== b && c.push(a);
    return c
  }, w = n.expr.match.needsContext, x = /^<([\w-]+)\s*\/?>(?:<\/\1>|)$/, y = /^.[^:#\[\.,]*$/;

  function z(a, b, c) {
    if (n.isFunction(b)) return n.grep(a, function (a, d) {
      return !!b.call(a, d, a) !== c
    });
    if (b.nodeType) return n.grep(a, function (a) {
      return a === b !== c
    });
    if ("string" == typeof b) {
      if (y.test(b)) return n.filter(b, a, c);
      b = n.filter(b, a)
    }
    return n.grep(a, function (a) {
      return h.call(b, a) > -1 !== c
    })
  }

  n.filter = function (a, b, c) {
    var d = b[0];
    return c && (a = ":not(" + a + ")"), 1 === b.length && 1 === d.nodeType ? n.find.matchesSelector(d, a) ? [d] : [] : n.find.matches(a, n.grep(b, function (a) {
      return 1 === a.nodeType
    }))
  }, n.fn.extend({
    find: function (a) {
      var b, c = this.length, d = [], e = this;
      if ("string" != typeof a) return this.pushStack(n(a).filter(function () {
        for (b = 0; c > b; b++) if (n.contains(e[b], this)) return !0
      }));
      for (b = 0; c > b; b++) n.find(a, e[b], d);
      return d = this.pushStack(c > 1 ? n.unique(d) : d), d.selector = this.selector ? this.selector + " " + a : a, d
    }, filter: function (a) {
      return this.pushStack(z(this, a || [], !1))
    }, not: function (a) {
      return this.pushStack(z(this, a || [], !0))
    }, is: function (a) {
      return !!z(this, "string" == typeof a && w.test(a) ? n(a) : a || [], !1).length
    }
  });
  var A, B = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/, C = n.fn.init = function (a, b, c) {
    var e, f;
    if (!a) return this;
    if (c = c || A, "string" == typeof a) {
      if (e = "<" === a[0] && ">" === a[a.length - 1] && a.length >= 3 ? [null, a, null] : B.exec(a), !e || !e[1] && b) return !b || b.jquery ? (b || c).find(a) : this.constructor(b).find(a);
      if (e[1]) {
        if (b = b instanceof n ? b[0] : b, n.merge(this, n.parseHTML(e[1], b && b.nodeType ? b.ownerDocument || b : d, !0)), x.test(e[1]) && n.isPlainObject(b)) for (e in b) n.isFunction(this[e]) ? this[e](b[e]) : this.attr(e, b[e]);
        return this
      }
      return f = d.getElementById(e[2]), f && f.parentNode && (this.length = 1, this[0] = f), this.context = d, this.selector = a, this
    }
    return a.nodeType ? (this.context = this[0] = a, this.length = 1, this) : n.isFunction(a) ? void 0 !== c.ready ? c.ready(a) : a(n) : (void 0 !== a.selector && (this.selector = a.selector, this.context = a.context), n.makeArray(a, this))
  };
  C.prototype = n.fn, A = n(d);
  var D = /^(?:parents|prev(?:Until|All))/, E = {children: !0, contents: !0, next: !0, prev: !0};
  n.fn.extend({
    has: function (a) {
      var b = n(a, this), c = b.length;
      return this.filter(function () {
        for (var a = 0; c > a; a++) if (n.contains(this, b[a])) return !0
      })
    }, closest: function (a, b) {
      for (var c, d = 0, e = this.length, f = [], g = w.test(a) || "string" != typeof a ? n(a, b || this.context) : 0; e > d; d++) for (c = this[d]; c && c !== b; c = c.parentNode) if (c.nodeType < 11 && (g ? g.index(c) > -1 : 1 === c.nodeType && n.find.matchesSelector(c, a))) {
        f.push(c);
        break
      }
      return this.pushStack(f.length > 1 ? n.uniqueSort(f) : f)
    }, index: function (a) {
      return a ? "string" == typeof a ? h.call(n(a), this[0]) : h.call(this, a.jquery ? a[0] : a) : this[0] && this[0].parentNode ? this.first().prevAll().length : -1
    }, add: function (a, b) {
      return this.pushStack(n.uniqueSort(n.merge(this.get(), n(a, b))))
    }, addBack: function (a) {
      return this.add(null == a ? this.prevObject : this.prevObject.filter(a))
    }
  });

  function F(a, b) {
    while ((a = a[b]) && 1 !== a.nodeType) ;
    return a
  }

  n.each({
    parent: function (a) {
      var b = a.parentNode;
      return b && 11 !== b.nodeType ? b : null
    }, parents: function (a) {
      return u(a, "parentNode")
    }, parentsUntil: function (a, b, c) {
      return u(a, "parentNode", c)
    }, next: function (a) {
      return F(a, "nextSibling")
    }, prev: function (a) {
      return F(a, "previousSibling")
    }, nextAll: function (a) {
      return u(a, "nextSibling")
    }, prevAll: function (a) {
      return u(a, "previousSibling")
    }, nextUntil: function (a, b, c) {
      return u(a, "nextSibling", c)
    }, prevUntil: function (a, b, c) {
      return u(a, "previousSibling", c)
    }, siblings: function (a) {
      return v((a.parentNode || {}).firstChild, a)
    }, children: function (a) {
      return v(a.firstChild)
    }, contents: function (a) {
      return a.contentDocument || n.merge([], a.childNodes)
    }
  }, function (a, b) {
    n.fn[a] = function (c, d) {
      var e = n.map(this, b, c);
      return "Until" !== a.slice(-5) && (d = c), d && "string" == typeof d && (e = n.filter(d, e)), this.length > 1 && (E[a] || n.uniqueSort(e), D.test(a) && e.reverse()), this.pushStack(e)
    }
  });
  var G = /\S+/g;

  function H(a) {
    var b = {};
    return n.each(a.match(G) || [], function (a, c) {
      b[c] = !0
    }), b
  }

  n.Callbacks = function (a) {
    a = "string" == typeof a ? H(a) : n.extend({}, a);
    var b, c, d, e, f = [], g = [], h = -1, i = function () {
      for (e = a.once, d = b = !0; g.length; h = -1) {
        c = g.shift();
        while (++h < f.length) f[h].apply(c[0], c[1]) === !1 && a.stopOnFalse && (h = f.length, c = !1)
      }
      a.memory || (c = !1), b = !1, e && (f = c ? [] : "")
    }, j = {
      add: function () {
        return f && (c && !b && (h = f.length - 1, g.push(c)), function d(b) {
          n.each(b, function (b, c) {
            n.isFunction(c) ? a.unique && j.has(c) || f.push(c) : c && c.length && "string" !== n.type(c) && d(c)
          })
        }(arguments), c && !b && i()), this
      }, remove: function () {
        return n.each(arguments, function (a, b) {
          var c;
          while ((c = n.inArray(b, f, c)) > -1) f.splice(c, 1), h >= c && h--
        }), this
      }, has: function (a) {
        return a ? n.inArray(a, f) > -1 : f.length > 0
      }, empty: function () {
        return f && (f = []), this
      }, disable: function () {
        return e = g = [], f = c = "", this
      }, disabled: function () {
        return !f
      }, lock: function () {
        return e = g = [], c || (f = c = ""), this
      }, locked: function () {
        return !!e
      }, fireWith: function (a, c) {
        return e || (c = c || [], c = [a, c.slice ? c.slice() : c], g.push(c), b || i()), this
      }, fire: function () {
        return j.fireWith(this, arguments), this
      }, fired: function () {
        return !!d
      }
    };
    return j
  }, n.extend({
    Deferred: function (a) {
      var b = [["resolve", "done", n.Callbacks("once memory"), "resolved"], ["reject", "fail", n.Callbacks("once memory"), "rejected"], ["notify", "progress", n.Callbacks("memory")]],
        c = "pending", d = {
          state: function () {
            return c
          }, always: function () {
            return e.done(arguments).fail(arguments), this
          }, then: function () {
            var a = arguments;
            return n.Deferred(function (c) {
              n.each(b, function (b, f) {
                var g = n.isFunction(a[b]) && a[b];
                e[f[1]](function () {
                  var a = g && g.apply(this, arguments);
                  a && n.isFunction(a.promise) ? a.promise().progress(c.notify).done(c.resolve).fail(c.reject) : c[f[0] + "With"](this === d ? c.promise() : this, g ? [a] : arguments)
                })
              }), a = null
            }).promise()
          }, promise: function (a) {
            return null != a ? n.extend(a, d) : d
          }
        }, e = {};
      return d.pipe = d.then, n.each(b, function (a, f) {
        var g = f[2], h = f[3];
        d[f[1]] = g.add, h && g.add(function () {
          c = h
        }, b[1 ^ a][2].disable, b[2][2].lock), e[f[0]] = function () {
          return e[f[0] + "With"](this === e ? d : this, arguments), this
        }, e[f[0] + "With"] = g.fireWith
      }), d.promise(e), a && a.call(e, e), e
    }, when: function (a) {
      var b = 0, c = e.call(arguments), d = c.length, f = 1 !== d || a && n.isFunction(a.promise) ? d : 0,
        g = 1 === f ? a : n.Deferred(), h = function (a, b, c) {
          return function (d) {
            b[a] = this, c[a] = arguments.length > 1 ? e.call(arguments) : d, c === i ? g.notifyWith(b, c) : --f || g.resolveWith(b, c)
          }
        }, i, j, k;
      if (d > 1) for (i = new Array(d), j = new Array(d), k = new Array(d); d > b; b++) c[b] && n.isFunction(c[b].promise) ? c[b].promise().progress(h(b, j, i)).done(h(b, k, c)).fail(g.reject) : --f;
      return f || g.resolveWith(k, c), g.promise()
    }
  });
  var I;
  n.fn.ready = function (a) {
    return n.ready.promise().done(a), this
  }, n.extend({
    isReady: !1, readyWait: 1, holdReady: function (a) {
      a ? n.readyWait++ : n.ready(!0)
    }, ready: function (a) {
      (a === !0 ? --n.readyWait : n.isReady) || (n.isReady = !0, a !== !0 && --n.readyWait > 0 || (I.resolveWith(d, [n]), n.fn.triggerHandler && (n(d).triggerHandler("ready"), n(d).off("ready"))))
    }
  });

  function J() {
    d.removeEventListener("DOMContentLoaded", J), a.removeEventListener("load", J), n.ready()
  }

  n.ready.promise = function (b) {
    return I || (I = n.Deferred(), "complete" === d.readyState || "loading" !== d.readyState && !d.documentElement.doScroll ? a.setTimeout(n.ready) : (d.addEventListener("DOMContentLoaded", J), a.addEventListener("load", J))), I.promise(b)
  }, n.ready.promise();
  var K = function (a, b, c, d, e, f, g) {
    var h = 0, i = a.length, j = null == c;
    if ("object" === n.type(c)) {
      e = !0;
      for (h in c) K(a, b, h, c[h], !0, f, g)
    } else if (void 0 !== d && (e = !0, n.isFunction(d) || (g = !0), j && (g ? (b.call(a, d), b = null) : (j = b, b = function (a, b, c) {
      return j.call(n(a), c)
    })), b)) for (; i > h; h++) b(a[h], c, g ? d : d.call(a[h], h, b(a[h], c)));
    return e ? a : j ? b.call(a) : i ? b(a[0], c) : f
  }, L = function (a) {
    return 1 === a.nodeType || 9 === a.nodeType || !+a.nodeType
  };

  function M() {
    this.expando = n.expando + M.uid++
  }

  M.uid = 1, M.prototype = {
    register: function (a, b) {
      var c = b || {};
      return a.nodeType ? a[this.expando] = c : Object.defineProperty(a, this.expando, {
        value: c,
        writable: !0,
        configurable: !0
      }), a[this.expando]
    }, cache: function (a) {
      if (!L(a)) return {};
      var b = a[this.expando];
      return b || (b = {}, L(a) && (a.nodeType ? a[this.expando] = b : Object.defineProperty(a, this.expando, {
        value: b,
        configurable: !0
      }))), b
    }, set: function (a, b, c) {
      var d, e = this.cache(a);
      if ("string" == typeof b) e[b] = c; else for (d in b) e[d] = b[d];
      return e
    }, get: function (a, b) {
      return void 0 === b ? this.cache(a) : a[this.expando] && a[this.expando][b]
    }, access: function (a, b, c) {
      var d;
      return void 0 === b || b && "string" == typeof b && void 0 === c ? (d = this.get(a, b), void 0 !== d ? d : this.get(a, n.camelCase(b))) : (this.set(a, b, c), void 0 !== c ? c : b)
    }, remove: function (a, b) {
      var c, d, e, f = a[this.expando];
      if (void 0 !== f) {
        if (void 0 === b) this.register(a); else {
          n.isArray(b) ? d = b.concat(b.map(n.camelCase)) : (e = n.camelCase(b), b in f ? d = [b, e] : (d = e, d = d in f ? [d] : d.match(G) || [])), c = d.length;
          while (c--) delete f[d[c]]
        }
        (void 0 === b || n.isEmptyObject(f)) && (a.nodeType ? a[this.expando] = void 0 : delete a[this.expando])
      }
    }, hasData: function (a) {
      var b = a[this.expando];
      return void 0 !== b && !n.isEmptyObject(b)
    }
  };
  var N = new M, O = new M, P = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/, Q = /[A-Z]/g;

  function R(a, b, c) {
    var d;
    if (void 0 === c && 1 === a.nodeType) if (d = "data-" + b.replace(Q, "-$&").toLowerCase(), c = a.getAttribute(d), "string" == typeof c) {
      try {
        c = "true" === c ? !0 : "false" === c ? !1 : "null" === c ? null : +c + "" === c ? +c : P.test(c) ? n.parseJSON(c) : c;
      } catch (e) {
      }
      O.set(a, b, c)
    } else c = void 0;
    return c
  }

  n.extend({
    hasData: function (a) {
      return O.hasData(a) || N.hasData(a)
    }, data: function (a, b, c) {
      return O.access(a, b, c)
    }, removeData: function (a, b) {
      O.remove(a, b)
    }, _data: function (a, b, c) {
      return N.access(a, b, c)
    }, _removeData: function (a, b) {
      N.remove(a, b)
    }
  }), n.fn.extend({
    data: function (a, b) {
      var c, d, e, f = this[0], g = f && f.attributes;
      if (void 0 === a) {
        if (this.length && (e = O.get(f), 1 === f.nodeType && !N.get(f, "hasDataAttrs"))) {
          c = g.length;
          while (c--) g[c] && (d = g[c].name, 0 === d.indexOf("data-") && (d = n.camelCase(d.slice(5)), R(f, d, e[d])));
          N.set(f, "hasDataAttrs", !0)
        }
        return e
      }
      return "object" == typeof a ? this.each(function () {
        O.set(this, a)
      }) : K(this, function (b) {
        var c, d;
        if (f && void 0 === b) {
          if (c = O.get(f, a) || O.get(f, a.replace(Q, "-$&").toLowerCase()), void 0 !== c) return c;
          if (d = n.camelCase(a), c = O.get(f, d), void 0 !== c) return c;
          if (c = R(f, d, void 0), void 0 !== c) return c
        } else d = n.camelCase(a), this.each(function () {
          var c = O.get(this, d);
          O.set(this, d, b), a.indexOf("-") > -1 && void 0 !== c && O.set(this, a, b)
        })
      }, null, b, arguments.length > 1, null, !0)
    }, removeData: function (a) {
      return this.each(function () {
        O.remove(this, a)
      })
    }
  }), n.extend({
    queue: function (a, b, c) {
      var d;
      return a ? (b = (b || "fx") + "queue", d = N.get(a, b), c && (!d || n.isArray(c) ? d = N.access(a, b, n.makeArray(c)) : d.push(c)), d || []) : void 0
    }, dequeue: function (a, b) {
      b = b || "fx";
      var c = n.queue(a, b), d = c.length, e = c.shift(), f = n._queueHooks(a, b), g = function () {
        n.dequeue(a, b)
      };
      "inprogress" === e && (e = c.shift(), d--), e && ("fx" === b && c.unshift("inprogress"), delete f.stop, e.call(a, g, f)), !d && f && f.empty.fire()
    }, _queueHooks: function (a, b) {
      var c = b + "queueHooks";
      return N.get(a, c) || N.access(a, c, {
        empty: n.Callbacks("once memory").add(function () {
          N.remove(a, [b + "queue", c])
        })
      })
    }
  }), n.fn.extend({
    queue: function (a, b) {
      var c = 2;
      return "string" != typeof a && (b = a, a = "fx", c--), arguments.length < c ? n.queue(this[0], a) : void 0 === b ? this : this.each(function () {
        var c = n.queue(this, a, b);
        n._queueHooks(this, a), "fx" === a && "inprogress" !== c[0] && n.dequeue(this, a)
      })
    }, dequeue: function (a) {
      return this.each(function () {
        n.dequeue(this, a)
      })
    }, clearQueue: function (a) {
      return this.queue(a || "fx", [])
    }, promise: function (a, b) {
      var c, d = 1, e = n.Deferred(), f = this, g = this.length, h = function () {
        --d || e.resolveWith(f, [f])
      };
      "string" != typeof a && (b = a, a = void 0), a = a || "fx";
      while (g--) c = N.get(f[g], a + "queueHooks"), c && c.empty && (d++, c.empty.add(h));
      return h(), e.promise(b)
    }
  });
  var S = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source, T = new RegExp("^(?:([+-])=|)(" + S + ")([a-z%]*)$", "i"),
    U = ["Top", "Right", "Bottom", "Left"], V = function (a, b) {
      return a = b || a, "none" === n.css(a, "display") || !n.contains(a.ownerDocument, a)
    };

  function W(a, b, c, d) {
    var e, f = 1, g = 20, h = d ? function () {
        return d.cur()
      } : function () {
        return n.css(a, b, "")
      }, i = h(), j = c && c[3] || (n.cssNumber[b] ? "" : "px"),
      k = (n.cssNumber[b] || "px" !== j && +i) && T.exec(n.css(a, b));
    if (k && k[3] !== j) {
      j = j || k[3], c = c || [], k = +i || 1;
      do f = f || ".5", k /= f, n.style(a, b, k + j); while (f !== (f = h() / i) && 1 !== f && --g)
    }
    return c && (k = +k || +i || 0, e = c[1] ? k + (c[1] + 1) * c[2] : +c[2], d && (d.unit = j, d.start = k, d.end = e)), e
  }

  var X = /^(?:checkbox|radio)$/i, Y = /<([\w:-]+)/, Z = /^$|\/(?:java|ecma)script/i, $ = {
    option: [1, "<select multiple='multiple'>", "</select>"],
    thead: [1, "<table>", "</table>"],
    col: [2, "<table><colgroup>", "</colgroup></table>"],
    tr: [2, "<table><tbody>", "</tbody></table>"],
    td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
    _default: [0, "", ""]
  };
  $.optgroup = $.option, $.tbody = $.tfoot = $.colgroup = $.caption = $.thead, $.th = $.td;

  function _(a, b) {
    var c = "undefined" != typeof a.getElementsByTagName ? a.getElementsByTagName(b || "*") : "undefined" != typeof a.querySelectorAll ? a.querySelectorAll(b || "*") : [];
    return void 0 === b || b && n.nodeName(a, b) ? n.merge([a], c) : c
  }

  function aa(a, b) {
    for (var c = 0, d = a.length; d > c; c++) N.set(a[c], "globalEval", !b || N.get(b[c], "globalEval"))
  }

  var ba = /<|&#?\w+;/;

  function ca(a, b, c, d, e) {
    for (var f, g, h, i, j, k, l = b.createDocumentFragment(), m = [], o = 0, p = a.length; p > o; o++) if (f = a[o], f || 0 === f) if ("object" === n.type(f)) n.merge(m, f.nodeType ? [f] : f); else if (ba.test(f)) {
      g = g || l.appendChild(b.createElement("div")), h = (Y.exec(f) || ["", ""])[1].toLowerCase(), i = $[h] || $._default, g.innerHTML = i[1] + n.htmlPrefilter(f) + i[2], k = i[0];
      while (k--) g = g.lastChild;
      n.merge(m, g.childNodes), g = l.firstChild, g.textContent = ""
    } else m.push(b.createTextNode(f));
    l.textContent = "", o = 0;
    while (f = m[o++]) if (d && n.inArray(f, d) > -1) e && e.push(f); else if (j = n.contains(f.ownerDocument, f), g = _(l.appendChild(f), "script"), j && aa(g), c) {
      k = 0;
      while (f = g[k++]) Z.test(f.type || "") && c.push(f)
    }
    return l
  }

  !function () {
    var a = d.createDocumentFragment(), b = a.appendChild(d.createElement("div")), c = d.createElement("input");
    c.setAttribute("type", "radio"), c.setAttribute("checked", "checked"), c.setAttribute("name", "t"), b.appendChild(c), l.checkClone = b.cloneNode(!0).cloneNode(!0).lastChild.checked, b.innerHTML = "<textarea>x</textarea>", l.noCloneChecked = !!b.cloneNode(!0).lastChild.defaultValue
  }();
  var da = /^key/, ea = /^(?:mouse|pointer|contextmenu|drag|drop)|click/, fa = /^([^.]*)(?:\.(.+)|)/;

  function ga() {
    return !0
  }

  function ha() {
    return !1
  }

  function ia() {
    try {
      return d.activeElement
    } catch (a) {
    }
  }

  function ja(a, b, c, d, e, f) {
    var g, h;
    if ("object" == typeof b) {
      "string" != typeof c && (d = d || c, c = void 0);
      for (h in b) ja(a, h, c, d, b[h], f);
      return a
    }
    if (null == d && null == e ? (e = c, d = c = void 0) : null == e && ("string" == typeof c ? (e = d, d = void 0) : (e = d, d = c, c = void 0)), e === !1) e = ha; else if (!e) return a;
    return 1 === f && (g = e, e = function (a) {
      return n().off(a), g.apply(this, arguments)
    }, e.guid = g.guid || (g.guid = n.guid++)), a.each(function () {
      n.event.add(this, b, e, d, c)
    })
  }

  n.event = {
    global: {},
    add: function (a, b, c, d, e) {
      var f, g, h, i, j, k, l, m, o, p, q, r = N.get(a);
      if (r) {
        c.handler && (f = c, c = f.handler, e = f.selector), c.guid || (c.guid = n.guid++), (i = r.events) || (i = r.events = {}), (g = r.handle) || (g = r.handle = function (b) {
          return "undefined" != typeof n && n.event.triggered !== b.type ? n.event.dispatch.apply(a, arguments) : void 0
        }), b = (b || "").match(G) || [""], j = b.length;
        while (j--) h = fa.exec(b[j]) || [], o = q = h[1], p = (h[2] || "").split(".").sort(), o && (l = n.event.special[o] || {}, o = (e ? l.delegateType : l.bindType) || o, l = n.event.special[o] || {}, k = n.extend({
          type: o,
          origType: q,
          data: d,
          handler: c,
          guid: c.guid,
          selector: e,
          needsContext: e && n.expr.match.needsContext.test(e),
          namespace: p.join(".")
        }, f), (m = i[o]) || (m = i[o] = [], m.delegateCount = 0, l.setup && l.setup.call(a, d, p, g) !== !1 || a.addEventListener && a.addEventListener(o, g)), l.add && (l.add.call(a, k), k.handler.guid || (k.handler.guid = c.guid)), e ? m.splice(m.delegateCount++, 0, k) : m.push(k), n.event.global[o] = !0)
      }
    },
    remove: function (a, b, c, d, e) {
      var f, g, h, i, j, k, l, m, o, p, q, r = N.hasData(a) && N.get(a);
      if (r && (i = r.events)) {
        b = (b || "").match(G) || [""], j = b.length;
        while (j--) if (h = fa.exec(b[j]) || [], o = q = h[1], p = (h[2] || "").split(".").sort(), o) {
          l = n.event.special[o] || {}, o = (d ? l.delegateType : l.bindType) || o, m = i[o] || [], h = h[2] && new RegExp("(^|\\.)" + p.join("\\.(?:.*\\.|)") + "(\\.|$)"), g = f = m.length;
          while (f--) k = m[f], !e && q !== k.origType || c && c.guid !== k.guid || h && !h.test(k.namespace) || d && d !== k.selector && ("**" !== d || !k.selector) || (m.splice(f, 1), k.selector && m.delegateCount--, l.remove && l.remove.call(a, k));
          g && !m.length && (l.teardown && l.teardown.call(a, p, r.handle) !== !1 || n.removeEvent(a, o, r.handle), delete i[o])
        } else for (o in i) n.event.remove(a, o + b[j], c, d, !0);
        n.isEmptyObject(i) && N.remove(a, "handle events")
      }
    },
    dispatch: function (a) {
      a = n.event.fix(a);
      var b, c, d, f, g, h = [], i = e.call(arguments), j = (N.get(this, "events") || {})[a.type] || [],
        k = n.event.special[a.type] || {};
      if (i[0] = a, a.delegateTarget = this, !k.preDispatch || k.preDispatch.call(this, a) !== !1) {
        h = n.event.handlers.call(this, a, j), b = 0;
        while ((f = h[b++]) && !a.isPropagationStopped()) {
          a.currentTarget = f.elem, c = 0;
          while ((g = f.handlers[c++]) && !a.isImmediatePropagationStopped()) a.rnamespace && !a.rnamespace.test(g.namespace) || (a.handleObj = g, a.data = g.data, d = ((n.event.special[g.origType] || {}).handle || g.handler).apply(f.elem, i), void 0 !== d && (a.result = d) === !1 && (a.preventDefault(), a.stopPropagation()))
        }
        return k.postDispatch && k.postDispatch.call(this, a), a.result
      }
    },
    handlers: function (a, b) {
      var c, d, e, f, g = [], h = b.delegateCount, i = a.target;
      if (h && i.nodeType && ("click" !== a.type || isNaN(a.button) || a.button < 1)) for (; i !== this; i = i.parentNode || this) if (1 === i.nodeType && (i.disabled !== !0 || "click" !== a.type)) {
        for (d = [], c = 0; h > c; c++) f = b[c], e = f.selector + " ", void 0 === d[e] && (d[e] = f.needsContext ? n(e, this).index(i) > -1 : n.find(e, this, null, [i]).length), d[e] && d.push(f);
        d.length && g.push({elem: i, handlers: d})
      }
      return h < b.length && g.push({elem: this, handlers: b.slice(h)}), g
    },
    props: "altKey bubbles cancelable ctrlKey currentTarget detail eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),
    fixHooks: {},
    keyHooks: {
      props: "char charCode key keyCode".split(" "), filter: function (a, b) {
        return null == a.which && (a.which = null != b.charCode ? b.charCode : b.keyCode), a
      }
    },
    mouseHooks: {
      props: "button buttons clientX clientY offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
      filter: function (a, b) {
        var c, e, f, g = b.button;
        return null == a.pageX && null != b.clientX && (c = a.target.ownerDocument || d, e = c.documentElement, f = c.body, a.pageX = b.clientX + (e && e.scrollLeft || f && f.scrollLeft || 0) - (e && e.clientLeft || f && f.clientLeft || 0), a.pageY = b.clientY + (e && e.scrollTop || f && f.scrollTop || 0) - (e && e.clientTop || f && f.clientTop || 0)), a.which || void 0 === g || (a.which = 1 & g ? 1 : 2 & g ? 3 : 4 & g ? 2 : 0), a
      }
    },
    fix: function (a) {
      if (a[n.expando]) return a;
      var b, c, e, f = a.type, g = a, h = this.fixHooks[f];
      h || (this.fixHooks[f] = h = ea.test(f) ? this.mouseHooks : da.test(f) ? this.keyHooks : {}), e = h.props ? this.props.concat(h.props) : this.props, a = new n.Event(g), b = e.length;
      while (b--) c = e[b], a[c] = g[c];
      return a.target || (a.target = d), 3 === a.target.nodeType && (a.target = a.target.parentNode), h.filter ? h.filter(a, g) : a
    },
    special: {
      load: {noBubble: !0}, focus: {
        trigger: function () {
          return this !== ia() && this.focus ? (this.focus(), !1) : void 0
        }, delegateType: "focusin"
      }, blur: {
        trigger: function () {
          return this === ia() && this.blur ? (this.blur(), !1) : void 0
        }, delegateType: "focusout"
      }, click: {
        trigger: function () {
          return "checkbox" === this.type && this.click && n.nodeName(this, "input") ? (this.click(), !1) : void 0
        }, _default: function (a) {
          return n.nodeName(a.target, "a")
        }
      }, beforeunload: {
        postDispatch: function (a) {
          void 0 !== a.result && a.originalEvent && (a.originalEvent.returnValue = a.result)
        }
      }
    }
  }, n.removeEvent = function (a, b, c) {
    a.removeEventListener && a.removeEventListener(b, c)
  }, n.Event = function (a, b) {
    return this instanceof n.Event ? (a && a.type ? (this.originalEvent = a, this.type = a.type, this.isDefaultPrevented = a.defaultPrevented || void 0 === a.defaultPrevented && a.returnValue === !1 ? ga : ha) : this.type = a, b && n.extend(this, b), this.timeStamp = a && a.timeStamp || n.now(), void (this[n.expando] = !0)) : new n.Event(a, b)
  }, n.Event.prototype = {
    constructor: n.Event,
    isDefaultPrevented: ha,
    isPropagationStopped: ha,
    isImmediatePropagationStopped: ha,
    isSimulated: !1,
    preventDefault: function () {
      var a = this.originalEvent;
      this.isDefaultPrevented = ga, a && !this.isSimulated && a.preventDefault()
    },
    stopPropagation: function () {
      var a = this.originalEvent;
      this.isPropagationStopped = ga, a && !this.isSimulated && a.stopPropagation()
    },
    stopImmediatePropagation: function () {
      var a = this.originalEvent;
      this.isImmediatePropagationStopped = ga, a && !this.isSimulated && a.stopImmediatePropagation(), this.stopPropagation()
    }
  }, n.each({
    mouseenter: "mouseover",
    mouseleave: "mouseout",
    pointerenter: "pointerover",
    pointerleave: "pointerout"
  }, function (a, b) {
    n.event.special[a] = {
      delegateType: b, bindType: b, handle: function (a) {
        var c, d = this, e = a.relatedTarget, f = a.handleObj;
        return e && (e === d || n.contains(d, e)) || (a.type = f.origType, c = f.handler.apply(this, arguments), a.type = b), c
      }
    }
  }), n.fn.extend({
    on: function (a, b, c, d) {
      return ja(this, a, b, c, d)
    }, one: function (a, b, c, d) {
      return ja(this, a, b, c, d, 1)
    }, off: function (a, b, c) {
      var d, e;
      if (a && a.preventDefault && a.handleObj) return d = a.handleObj, n(a.delegateTarget).off(d.namespace ? d.origType + "." + d.namespace : d.origType, d.selector, d.handler), this;
      if ("object" == typeof a) {
        for (e in a) this.off(e, b, a[e]);
        return this
      }
      return b !== !1 && "function" != typeof b || (c = b, b = void 0), c === !1 && (c = ha), this.each(function () {
        n.event.remove(this, a, c, b)
      })
    }
  });
  var ka = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:-]+)[^>]*)\/>/gi, la = /<script|<style|<link/i,
    ma = /checked\s*(?:[^=]|=\s*.checked.)/i, na = /^true\/(.*)/, oa = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;

  function pa(a, b) {
    return n.nodeName(a, "table") && n.nodeName(11 !== b.nodeType ? b : b.firstChild, "tr") ? a.getElementsByTagName("tbody")[0] || a.appendChild(a.ownerDocument.createElement("tbody")) : a
  }

  function qa(a) {
    return a.type = (null !== a.getAttribute("type")) + "/" + a.type, a
  }

  function ra(a) {
    var b = na.exec(a.type);
    return b ? a.type = b[1] : a.removeAttribute("type"), a
  }

  function sa(a, b) {
    var c, d, e, f, g, h, i, j;
    if (1 === b.nodeType) {
      if (N.hasData(a) && (f = N.access(a), g = N.set(b, f), j = f.events)) {
        delete g.handle, g.events = {};
        for (e in j) for (c = 0, d = j[e].length; d > c; c++) n.event.add(b, e, j[e][c])
      }
      O.hasData(a) && (h = O.access(a), i = n.extend({}, h), O.set(b, i))
    }
  }

  function ta(a, b) {
    var c = b.nodeName.toLowerCase();
    "input" === c && X.test(a.type) ? b.checked = a.checked : "input" !== c && "textarea" !== c || (b.defaultValue = a.defaultValue)
  }

  function ua(a, b, c, d) {
    b = f.apply([], b);
    var e, g, h, i, j, k, m = 0, o = a.length, p = o - 1, q = b[0], r = n.isFunction(q);
    if (r || o > 1 && "string" == typeof q && !l.checkClone && ma.test(q)) return a.each(function (e) {
      var f = a.eq(e);
      r && (b[0] = q.call(this, e, f.html())), ua(f, b, c, d)
    });
    if (o && (e = ca(b, a[0].ownerDocument, !1, a, d), g = e.firstChild, 1 === e.childNodes.length && (e = g), g || d)) {
      for (h = n.map(_(e, "script"), qa), i = h.length; o > m; m++) j = e, m !== p && (j = n.clone(j, !0, !0), i && n.merge(h, _(j, "script"))), c.call(a[m], j, m);
      if (i) for (k = h[h.length - 1].ownerDocument, n.map(h, ra), m = 0; i > m; m++) j = h[m], Z.test(j.type || "") && !N.access(j, "globalEval") && n.contains(k, j) && (j.src ? n._evalUrl && n._evalUrl(j.src) : n.globalEval(j.textContent.replace(oa, "")))
    }
    return a
  }

  function va(a, b, c) {
    for (var d, e = b ? n.filter(b, a) : a, f = 0; null != (d = e[f]); f++) c || 1 !== d.nodeType || n.cleanData(_(d)), d.parentNode && (c && n.contains(d.ownerDocument, d) && aa(_(d, "script")), d.parentNode.removeChild(d));
    return a
  }

  n.extend({
    htmlPrefilter: function (a) {
      return a.replace(ka, "<$1></$2>")
    }, clone: function (a, b, c) {
      var d, e, f, g, h = a.cloneNode(!0), i = n.contains(a.ownerDocument, a);
      if (!(l.noCloneChecked || 1 !== a.nodeType && 11 !== a.nodeType || n.isXMLDoc(a))) for (g = _(h), f = _(a), d = 0, e = f.length; e > d; d++) ta(f[d], g[d]);
      if (b) if (c) for (f = f || _(a), g = g || _(h), d = 0, e = f.length; e > d; d++) sa(f[d], g[d]); else sa(a, h);
      return g = _(h, "script"), g.length > 0 && aa(g, !i && _(a, "script")), h
    }, cleanData: function (a) {
      for (var b, c, d, e = n.event.special, f = 0; void 0 !== (c = a[f]); f++) if (L(c)) {
        if (b = c[N.expando]) {
          if (b.events) for (d in b.events) e[d] ? n.event.remove(c, d) : n.removeEvent(c, d, b.handle);
          c[N.expando] = void 0
        }
        c[O.expando] && (c[O.expando] = void 0)
      }
    }
  }), n.fn.extend({
    domManip: ua, detach: function (a) {
      return va(this, a, !0)
    }, remove: function (a) {
      return va(this, a)
    }, text: function (a) {
      return K(this, function (a) {
        return void 0 === a ? n.text(this) : this.empty().each(function () {
          1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || (this.textContent = a)
        })
      }, null, a, arguments.length)
    }, append: function () {
      return ua(this, arguments, function (a) {
        if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
          var b = pa(this, a);
          b.appendChild(a)
        }
      })
    }, prepend: function () {
      return ua(this, arguments, function (a) {
        if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
          var b = pa(this, a);
          b.insertBefore(a, b.firstChild)
        }
      })
    }, before: function () {
      return ua(this, arguments, function (a) {
        this.parentNode && this.parentNode.insertBefore(a, this)
      })
    }, after: function () {
      return ua(this, arguments, function (a) {
        this.parentNode && this.parentNode.insertBefore(a, this.nextSibling)
      })
    }, empty: function () {
      for (var a, b = 0; null != (a = this[b]); b++) 1 === a.nodeType && (n.cleanData(_(a, !1)), a.textContent = "");
      return this
    }, clone: function (a, b) {
      return a = null == a ? !1 : a, b = null == b ? a : b, this.map(function () {
        return n.clone(this, a, b)
      })
    }, html: function (a) {
      return K(this, function (a) {
        var b = this[0] || {}, c = 0, d = this.length;
        if (void 0 === a && 1 === b.nodeType) return b.innerHTML;
        if ("string" == typeof a && !la.test(a) && !$[(Y.exec(a) || ["", ""])[1].toLowerCase()]) {
          a = n.htmlPrefilter(a);
          try {
            for (; d > c; c++) b = this[c] || {}, 1 === b.nodeType && (n.cleanData(_(b, !1)), b.innerHTML = a);
            b = 0
          } catch (e) {
          }
        }
        b && this.empty().append(a)
      }, null, a, arguments.length)
    }, replaceWith: function () {
      var a = [];
      return ua(this, arguments, function (b) {
        var c = this.parentNode;
        n.inArray(this, a) < 0 && (n.cleanData(_(this)), c && c.replaceChild(b, this))
      }, a)
    }
  }), n.each({
    appendTo: "append",
    prependTo: "prepend",
    insertBefore: "before",
    insertAfter: "after",
    replaceAll: "replaceWith"
  }, function (a, b) {
    n.fn[a] = function (a) {
      for (var c, d = [], e = n(a), f = e.length - 1, h = 0; f >= h; h++) c = h === f ? this : this.clone(!0), n(e[h])[b](c), g.apply(d, c.get());
      return this.pushStack(d)
    }
  });
  var wa, xa = {HTML: "block", BODY: "block"};

  function ya(a, b) {
    var c = n(b.createElement(a)).appendTo(b.body), d = n.css(c[0], "display");
    return c.detach(), d
  }

  function za(a) {
    var b = d, c = xa[a];
    return c || (c = ya(a, b), "none" !== c && c || (wa = (wa || n("<iframe frameborder='0' width='0' height='0'/>")).appendTo(b.documentElement), b = wa[0].contentDocument, b.write(), b.close(), c = ya(a, b), wa.detach()), xa[a] = c), c
  }

  var Aa = /^margin/, Ba = new RegExp("^(" + S + ")(?!px)[a-z%]+$", "i"), Ca = function (b) {
    var c = b.ownerDocument.defaultView;
    return c && c.opener || (c = a), c.getComputedStyle(b)
  }, Da = function (a, b, c, d) {
    var e, f, g = {};
    for (f in b) g[f] = a.style[f], a.style[f] = b[f];
    e = c.apply(a, d || []);
    for (f in b) a.style[f] = g[f];
    return e
  }, Ea = d.documentElement;
  !function () {
    var b, c, e, f, g = d.createElement("div"), h = d.createElement("div");
    if (h.style) {
      h.style.backgroundClip = "content-box", h.cloneNode(!0).style.backgroundClip = "", l.clearCloneStyle = "content-box" === h.style.backgroundClip, g.style.cssText = "border:0;width:8px;height:0;top:0;left:-9999px;padding:0;margin-top:1px;position:absolute", g.appendChild(h);

      function i() {
        h.style.cssText = "-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;position:relative;display:block;margin:auto;border:1px;padding:1px;top:1%;width:50%", h.innerHTML = "", Ea.appendChild(g);
        var d = a.getComputedStyle(h);
        b = "1%" !== d.top, f = "2px" === d.marginLeft, c = "4px" === d.width, h.style.marginRight = "50%", e = "4px" === d.marginRight, Ea.removeChild(g)
      }

      n.extend(l, {
        pixelPosition: function () {
          return i(), b
        }, boxSizingReliable: function () {
          return null == c && i(), c
        }, pixelMarginRight: function () {
          return null == c && i(), e
        }, reliableMarginLeft: function () {
          return null == c && i(), f
        }, reliableMarginRight: function () {
          var b, c = h.appendChild(d.createElement("div"));
          return c.style.cssText = h.style.cssText = "-webkit-box-sizing:content-box;box-sizing:content-box;display:block;margin:0;border:0;padding:0", c.style.marginRight = c.style.width = "0", h.style.width = "1px", Ea.appendChild(g), b = !parseFloat(a.getComputedStyle(c).marginRight), Ea.removeChild(g), h.removeChild(c), b
        }
      })
    }
  }();

  function Fa(a, b, c) {
    var d, e, f, g, h = a.style;
    return c = c || Ca(a), g = c ? c.getPropertyValue(b) || c[b] : void 0, "" !== g && void 0 !== g || n.contains(a.ownerDocument, a) || (g = n.style(a, b)), c && !l.pixelMarginRight() && Ba.test(g) && Aa.test(b) && (d = h.width, e = h.minWidth, f = h.maxWidth, h.minWidth = h.maxWidth = h.width = g, g = c.width, h.width = d, h.minWidth = e, h.maxWidth = f), void 0 !== g ? g + "" : g
  }

  function Ga(a, b) {
    return {
      get: function () {
        return a() ? void delete this.get : (this.get = b).apply(this, arguments)
      }
    }
  }

  var Ha = /^(none|table(?!-c[ea]).+)/, Ia = {position: "absolute", visibility: "hidden", display: "block"},
    Ja = {letterSpacing: "0", fontWeight: "400"}, Ka = ["Webkit", "O", "Moz", "ms"],
    La = d.createElement("div").style;

  function Ma(a) {
    if (a in La) return a;
    var b = a[0].toUpperCase() + a.slice(1), c = Ka.length;
    while (c--) if (a = Ka[c] + b, a in La) return a
  }

  function Na(a, b, c) {
    var d = T.exec(b);
    return d ? Math.max(0, d[2] - (c || 0)) + (d[3] || "px") : b
  }

  function Oa(a, b, c, d, e) {
    for (var f = c === (d ? "border" : "content") ? 4 : "width" === b ? 1 : 0, g = 0; 4 > f; f += 2) "margin" === c && (g += n.css(a, c + U[f], !0, e)), d ? ("content" === c && (g -= n.css(a, "padding" + U[f], !0, e)), "margin" !== c && (g -= n.css(a, "border" + U[f] + "Width", !0, e))) : (g += n.css(a, "padding" + U[f], !0, e), "padding" !== c && (g += n.css(a, "border" + U[f] + "Width", !0, e)));
    return g
  }

  function Pa(a, b, c) {
    var d = !0, e = "width" === b ? a.offsetWidth : a.offsetHeight, f = Ca(a),
      g = "border-box" === n.css(a, "boxSizing", !1, f);
    if (0 >= e || null == e) {
      if (e = Fa(a, b, f), (0 > e || null == e) && (e = a.style[b]), Ba.test(e)) return e;
      d = g && (l.boxSizingReliable() || e === a.style[b]), e = parseFloat(e) || 0
    }
    return e + Oa(a, b, c || (g ? "border" : "content"), d, f) + "px"
  }

  function Qa(a, b) {
    for (var c, d, e, f = [], g = 0, h = a.length; h > g; g++) d = a[g], d.style && (f[g] = N.get(d, "olddisplay"), c = d.style.display, b ? (f[g] || "none" !== c || (d.style.display = ""), "" === d.style.display && V(d) && (f[g] = N.access(d, "olddisplay", za(d.nodeName)))) : (e = V(d), "none" === c && e || N.set(d, "olddisplay", e ? c : n.css(d, "display"))));
    for (g = 0; h > g; g++) d = a[g], d.style && (b && "none" !== d.style.display && "" !== d.style.display || (d.style.display = b ? f[g] || "" : "none"));
    return a
  }

  n.extend({
    cssHooks: {
      opacity: {
        get: function (a, b) {
          if (b) {
            var c = Fa(a, "opacity");
            return "" === c ? "1" : c
          }
        }
      }
    },
    cssNumber: {
      animationIterationCount: !0,
      columnCount: !0,
      fillOpacity: !0,
      flexGrow: !0,
      flexShrink: !0,
      fontWeight: !0,
      lineHeight: !0,
      opacity: !0,
      order: !0,
      orphans: !0,
      widows: !0,
      zIndex: !0,
      zoom: !0
    },
    cssProps: {"float": "cssFloat"},
    style: function (a, b, c, d) {
      if (a && 3 !== a.nodeType && 8 !== a.nodeType && a.style) {
        var e, f, g, h = n.camelCase(b), i = a.style;
        return b = n.cssProps[h] || (n.cssProps[h] = Ma(h) || h), g = n.cssHooks[b] || n.cssHooks[h], void 0 === c ? g && "get" in g && void 0 !== (e = g.get(a, !1, d)) ? e : i[b] : (f = typeof c, "string" === f && (e = T.exec(c)) && e[1] && (c = W(a, b, e), f = "number"), null != c && c === c && ("number" === f && (c += e && e[3] || (n.cssNumber[h] ? "" : "px")), l.clearCloneStyle || "" !== c || 0 !== b.indexOf("background") || (i[b] = "inherit"), g && "set" in g && void 0 === (c = g.set(a, c, d)) || (i[b] = c)), void 0)
      }
    },
    css: function (a, b, c, d) {
      var e, f, g, h = n.camelCase(b);
      return b = n.cssProps[h] || (n.cssProps[h] = Ma(h) || h), g = n.cssHooks[b] || n.cssHooks[h], g && "get" in g && (e = g.get(a, !0, c)), void 0 === e && (e = Fa(a, b, d)), "normal" === e && b in Ja && (e = Ja[b]), "" === c || c ? (f = parseFloat(e), c === !0 || isFinite(f) ? f || 0 : e) : e
    }
  }), n.each(["height", "width"], function (a, b) {
    n.cssHooks[b] = {
      get: function (a, c, d) {
        return c ? Ha.test(n.css(a, "display")) && 0 === a.offsetWidth ? Da(a, Ia, function () {
          return Pa(a, b, d)
        }) : Pa(a, b, d) : void 0
      }, set: function (a, c, d) {
        var e, f = d && Ca(a), g = d && Oa(a, b, d, "border-box" === n.css(a, "boxSizing", !1, f), f);
        return g && (e = T.exec(c)) && "px" !== (e[3] || "px") && (a.style[b] = c, c = n.css(a, b)), Na(a, c, g)
      }
    }
  }), n.cssHooks.marginLeft = Ga(l.reliableMarginLeft, function (a, b) {
    return b ? (parseFloat(Fa(a, "marginLeft")) || a.getBoundingClientRect().left - Da(a, {marginLeft: 0}, function () {
      return a.getBoundingClientRect().left
    })) + "px" : void 0
  }), n.cssHooks.marginRight = Ga(l.reliableMarginRight, function (a, b) {
    return b ? Da(a, {display: "inline-block"}, Fa, [a, "marginRight"]) : void 0
  }), n.each({margin: "", padding: "", border: "Width"}, function (a, b) {
    n.cssHooks[a + b] = {
      expand: function (c) {
        for (var d = 0, e = {}, f = "string" == typeof c ? c.split(" ") : [c]; 4 > d; d++) e[a + U[d] + b] = f[d] || f[d - 2] || f[0];
        return e
      }
    }, Aa.test(a) || (n.cssHooks[a + b].set = Na)
  }), n.fn.extend({
    css: function (a, b) {
      return K(this, function (a, b, c) {
        var d, e, f = {}, g = 0;
        if (n.isArray(b)) {
          for (d = Ca(a), e = b.length; e > g; g++) f[b[g]] = n.css(a, b[g], !1, d);
          return f
        }
        return void 0 !== c ? n.style(a, b, c) : n.css(a, b)
      }, a, b, arguments.length > 1)
    }, show: function () {
      return Qa(this, !0)
    }, hide: function () {
      return Qa(this)
    }, toggle: function (a) {
      return "boolean" == typeof a ? a ? this.show() : this.hide() : this.each(function () {
        V(this) ? n(this).show() : n(this).hide()
      })
    }
  });

  function Ra(a, b, c, d, e) {
    return new Ra.prototype.init(a, b, c, d, e)
  }

  n.Tween = Ra, Ra.prototype = {
    constructor: Ra, init: function (a, b, c, d, e, f) {
      this.elem = a, this.prop = c, this.easing = e || n.easing._default, this.options = b, this.start = this.now = this.cur(), this.end = d, this.unit = f || (n.cssNumber[c] ? "" : "px")
    }, cur: function () {
      var a = Ra.propHooks[this.prop];
      return a && a.get ? a.get(this) : Ra.propHooks._default.get(this)
    }, run: function (a) {
      var b, c = Ra.propHooks[this.prop];
      return this.options.duration ? this.pos = b = n.easing[this.easing](a, this.options.duration * a, 0, 1, this.options.duration) : this.pos = b = a, this.now = (this.end - this.start) * b + this.start, this.options.step && this.options.step.call(this.elem, this.now, this), c && c.set ? c.set(this) : Ra.propHooks._default.set(this), this
    }
  }, Ra.prototype.init.prototype = Ra.prototype, Ra.propHooks = {
    _default: {
      get: function (a) {
        var b;
        return 1 !== a.elem.nodeType || null != a.elem[a.prop] && null == a.elem.style[a.prop] ? a.elem[a.prop] : (b = n.css(a.elem, a.prop, ""), b && "auto" !== b ? b : 0)
      }, set: function (a) {
        n.fx.step[a.prop] ? n.fx.step[a.prop](a) : 1 !== a.elem.nodeType || null == a.elem.style[n.cssProps[a.prop]] && !n.cssHooks[a.prop] ? a.elem[a.prop] = a.now : n.style(a.elem, a.prop, a.now + a.unit)
      }
    }
  }, Ra.propHooks.scrollTop = Ra.propHooks.scrollLeft = {
    set: function (a) {
      a.elem.nodeType && a.elem.parentNode && (a.elem[a.prop] = a.now)
    }
  }, n.easing = {
    linear: function (a) {
      return a
    }, swing: function (a) {
      return .5 - Math.cos(a * Math.PI) / 2
    }, _default: "swing"
  }, n.fx = Ra.prototype.init, n.fx.step = {};
  var Sa, Ta, Ua = /^(?:toggle|show|hide)$/, Va = /queueHooks$/;

  function Wa() {
    return a.setTimeout(function () {
      Sa = void 0
    }), Sa = n.now()
  }

  function Xa(a, b) {
    var c, d = 0, e = {height: a};
    for (b = b ? 1 : 0; 4 > d; d += 2 - b) c = U[d], e["margin" + c] = e["padding" + c] = a;
    return b && (e.opacity = e.width = a), e
  }

  function Ya(a, b, c) {
    for (var d, e = (_a.tweeners[b] || []).concat(_a.tweeners["*"]), f = 0, g = e.length; g > f; f++) if (d = e[f].call(c, b, a)) return d
  }

  function Za(a, b, c) {
    var d, e, f, g, h, i, j, k, l = this, m = {}, o = a.style, p = a.nodeType && V(a), q = N.get(a, "fxshow");
    c.queue || (h = n._queueHooks(a, "fx"), null == h.unqueued && (h.unqueued = 0, i = h.empty.fire, h.empty.fire = function () {
      h.unqueued || i()
    }), h.unqueued++, l.always(function () {
      l.always(function () {
        h.unqueued--, n.queue(a, "fx").length || h.empty.fire()
      })
    })), 1 === a.nodeType && ("height" in b || "width" in b) && (c.overflow = [o.overflow, o.overflowX, o.overflowY], j = n.css(a, "display"), k = "none" === j ? N.get(a, "olddisplay") || za(a.nodeName) : j, "inline" === k && "none" === n.css(a, "float") && (o.display = "inline-block")), c.overflow && (o.overflow = "hidden", l.always(function () {
      o.overflow = c.overflow[0], o.overflowX = c.overflow[1], o.overflowY = c.overflow[2]
    }));
    for (d in b) if (e = b[d], Ua.exec(e)) {
      if (delete b[d], f = f || "toggle" === e, e === (p ? "hide" : "show")) {
        if ("show" !== e || !q || void 0 === q[d]) continue;
        p = !0
      }
      m[d] = q && q[d] || n.style(a, d)
    } else j = void 0;
    if (n.isEmptyObject(m)) "inline" === ("none" === j ? za(a.nodeName) : j) && (o.display = j); else {
      q ? "hidden" in q && (p = q.hidden) : q = N.access(a, "fxshow", {}), f && (q.hidden = !p), p ? n(a).show() : l.done(function () {
        n(a).hide()
      }), l.done(function () {
        var b;
        N.remove(a, "fxshow");
        for (b in m) n.style(a, b, m[b])
      });
      for (d in m) g = Ya(p ? q[d] : 0, d, l), d in q || (q[d] = g.start, p && (g.end = g.start, g.start = "width" === d || "height" === d ? 1 : 0))
    }
  }

  function $a(a, b) {
    var c, d, e, f, g;
    for (c in a) if (d = n.camelCase(c), e = b[d], f = a[c], n.isArray(f) && (e = f[1], f = a[c] = f[0]), c !== d && (a[d] = f, delete a[c]), g = n.cssHooks[d], g && "expand" in g) {
      f = g.expand(f), delete a[d];
      for (c in f) c in a || (a[c] = f[c], b[c] = e)
    } else b[d] = e
  }

  function _a(a, b, c) {
    var d, e, f = 0, g = _a.prefilters.length, h = n.Deferred().always(function () {
      delete i.elem
    }), i = function () {
      if (e) return !1;
      for (var b = Sa || Wa(), c = Math.max(0, j.startTime + j.duration - b), d = c / j.duration || 0, f = 1 - d, g = 0, i = j.tweens.length; i > g; g++) j.tweens[g].run(f);
      return h.notifyWith(a, [j, f, c]), 1 > f && i ? c : (h.resolveWith(a, [j]), !1)
    }, j = h.promise({
      elem: a,
      props: n.extend({}, b),
      opts: n.extend(!0, {specialEasing: {}, easing: n.easing._default}, c),
      originalProperties: b,
      originalOptions: c,
      startTime: Sa || Wa(),
      duration: c.duration,
      tweens: [],
      createTween: function (b, c) {
        var d = n.Tween(a, j.opts, b, c, j.opts.specialEasing[b] || j.opts.easing);
        return j.tweens.push(d), d
      },
      stop: function (b) {
        var c = 0, d = b ? j.tweens.length : 0;
        if (e) return this;
        for (e = !0; d > c; c++) j.tweens[c].run(1);
        return b ? (h.notifyWith(a, [j, 1, 0]), h.resolveWith(a, [j, b])) : h.rejectWith(a, [j, b]), this
      }
    }), k = j.props;
    for ($a(k, j.opts.specialEasing); g > f; f++) if (d = _a.prefilters[f].call(j, a, k, j.opts)) return n.isFunction(d.stop) && (n._queueHooks(j.elem, j.opts.queue).stop = n.proxy(d.stop, d)), d;
    return n.map(k, Ya, j), n.isFunction(j.opts.start) && j.opts.start.call(a, j), n.fx.timer(n.extend(i, {
      elem: a,
      anim: j,
      queue: j.opts.queue
    })), j.progress(j.opts.progress).done(j.opts.done, j.opts.complete).fail(j.opts.fail).always(j.opts.always)
  }

  n.Animation = n.extend(_a, {
    tweeners: {
      "*": [function (a, b) {
        var c = this.createTween(a, b);
        return W(c.elem, a, T.exec(b), c), c
      }]
    }, tweener: function (a, b) {
      n.isFunction(a) ? (b = a, a = ["*"]) : a = a.match(G);
      for (var c, d = 0, e = a.length; e > d; d++) c = a[d], _a.tweeners[c] = _a.tweeners[c] || [], _a.tweeners[c].unshift(b)
    }, prefilters: [Za], prefilter: function (a, b) {
      b ? _a.prefilters.unshift(a) : _a.prefilters.push(a)
    }
  }), n.speed = function (a, b, c) {
    var d = a && "object" == typeof a ? n.extend({}, a) : {
      complete: c || !c && b || n.isFunction(a) && a,
      duration: a,
      easing: c && b || b && !n.isFunction(b) && b
    };
    return d.duration = n.fx.off ? 0 : "number" == typeof d.duration ? d.duration : d.duration in n.fx.speeds ? n.fx.speeds[d.duration] : n.fx.speeds._default, null != d.queue && d.queue !== !0 || (d.queue = "fx"), d.old = d.complete, d.complete = function () {
      n.isFunction(d.old) && d.old.call(this), d.queue && n.dequeue(this, d.queue)
    }, d
  }, n.fn.extend({
    fadeTo: function (a, b, c, d) {
      return this.filter(V).css("opacity", 0).show().end().animate({opacity: b}, a, c, d)
    }, animate: function (a, b, c, d) {
      var e = n.isEmptyObject(a), f = n.speed(b, c, d), g = function () {
        var b = _a(this, n.extend({}, a), f);
        (e || N.get(this, "finish")) && b.stop(!0)
      };
      return g.finish = g, e || f.queue === !1 ? this.each(g) : this.queue(f.queue, g)
    }, stop: function (a, b, c) {
      var d = function (a) {
        var b = a.stop;
        delete a.stop, b(c)
      };
      return "string" != typeof a && (c = b, b = a, a = void 0), b && a !== !1 && this.queue(a || "fx", []), this.each(function () {
        var b = !0, e = null != a && a + "queueHooks", f = n.timers, g = N.get(this);
        if (e) g[e] && g[e].stop && d(g[e]); else for (e in g) g[e] && g[e].stop && Va.test(e) && d(g[e]);
        for (e = f.length; e--;) f[e].elem !== this || null != a && f[e].queue !== a || (f[e].anim.stop(c), b = !1, f.splice(e, 1));
        !b && c || n.dequeue(this, a)
      })
    }, finish: function (a) {
      return a !== !1 && (a = a || "fx"), this.each(function () {
        var b, c = N.get(this), d = c[a + "queue"], e = c[a + "queueHooks"], f = n.timers, g = d ? d.length : 0;
        for (c.finish = !0, n.queue(this, a, []), e && e.stop && e.stop.call(this, !0), b = f.length; b--;) f[b].elem === this && f[b].queue === a && (f[b].anim.stop(!0), f.splice(b, 1));
        for (b = 0; g > b; b++) d[b] && d[b].finish && d[b].finish.call(this);
        delete c.finish
      })
    }
  }), n.each(["toggle", "show", "hide"], function (a, b) {
    var c = n.fn[b];
    n.fn[b] = function (a, d, e) {
      return null == a || "boolean" == typeof a ? c.apply(this, arguments) : this.animate(Xa(b, !0), a, d, e)
    }
  }), n.each({
    slideDown: Xa("show"),
    slideUp: Xa("hide"),
    slideToggle: Xa("toggle"),
    fadeIn: {opacity: "show"},
    fadeOut: {opacity: "hide"},
    fadeToggle: {opacity: "toggle"}
  }, function (a, b) {
    n.fn[a] = function (a, c, d) {
      return this.animate(b, a, c, d)
    }
  }), n.timers = [], n.fx.tick = function () {
    var a, b = 0, c = n.timers;
    for (Sa = n.now(); b < c.length; b++) a = c[b], a() || c[b] !== a || c.splice(b--, 1);
    c.length || n.fx.stop(), Sa = void 0
  }, n.fx.timer = function (a) {
    n.timers.push(a), a() ? n.fx.start() : n.timers.pop()
  }, n.fx.interval = 13, n.fx.start = function () {
    Ta || (Ta = a.setInterval(n.fx.tick, n.fx.interval))
  }, n.fx.stop = function () {
    a.clearInterval(Ta), Ta = null
  }, n.fx.speeds = {slow: 600, fast: 200, _default: 400}, n.fn.delay = function (b, c) {
    return b = n.fx ? n.fx.speeds[b] || b : b, c = c || "fx", this.queue(c, function (c, d) {
      var e = a.setTimeout(c, b);
      d.stop = function () {
        a.clearTimeout(e)
      }
    })
  }, function () {
    var a = d.createElement("input"), b = d.createElement("select"), c = b.appendChild(d.createElement("option"));
    a.type = "checkbox", l.checkOn = "" !== a.value, l.optSelected = c.selected, b.disabled = !0, l.optDisabled = !c.disabled, a = d.createElement("input"), a.value = "t", a.type = "radio", l.radioValue = "t" === a.value
  }();
  var ab, bb = n.expr.attrHandle;
  n.fn.extend({
    attr: function (a, b) {
      return K(this, n.attr, a, b, arguments.length > 1)
    }, removeAttr: function (a) {
      return this.each(function () {
        n.removeAttr(this, a)
      })
    }
  }), n.extend({
    attr: function (a, b, c) {
      var d, e, f = a.nodeType;
      if (3 !== f && 8 !== f && 2 !== f) return "undefined" == typeof a.getAttribute ? n.prop(a, b, c) : (1 === f && n.isXMLDoc(a) || (b = b.toLowerCase(), e = n.attrHooks[b] || (n.expr.match.bool.test(b) ? ab : void 0)), void 0 !== c ? null === c ? void n.removeAttr(a, b) : e && "set" in e && void 0 !== (d = e.set(a, c, b)) ? d : (a.setAttribute(b, c + ""), c) : e && "get" in e && null !== (d = e.get(a, b)) ? d : (d = n.find.attr(a, b), null == d ? void 0 : d))
    }, attrHooks: {
      type: {
        set: function (a, b) {
          if (!l.radioValue && "radio" === b && n.nodeName(a, "input")) {
            var c = a.value;
            return a.setAttribute("type", b), c && (a.value = c), b
          }
        }
      }
    }, removeAttr: function (a, b) {
      var c, d, e = 0, f = b && b.match(G);
      if (f && 1 === a.nodeType) while (c = f[e++]) d = n.propFix[c] || c, n.expr.match.bool.test(c) && (a[d] = !1), a.removeAttribute(c)
    }
  }), ab = {
    set: function (a, b, c) {
      return b === !1 ? n.removeAttr(a, c) : a.setAttribute(c, c), c
    }
  }, n.each(n.expr.match.bool.source.match(/\w+/g), function (a, b) {
    var c = bb[b] || n.find.attr;
    bb[b] = function (a, b, d) {
      var e, f;
      return d || (f = bb[b], bb[b] = e, e = null != c(a, b, d) ? b.toLowerCase() : null, bb[b] = f), e
    }
  });
  var cb = /^(?:input|select|textarea|button)$/i, db = /^(?:a|area)$/i;
  n.fn.extend({
    prop: function (a, b) {
      return K(this, n.prop, a, b, arguments.length > 1)
    }, removeProp: function (a) {
      return this.each(function () {
        delete this[n.propFix[a] || a]
      })
    }
  }), n.extend({
    prop: function (a, b, c) {
      var d, e, f = a.nodeType;
      if (3 !== f && 8 !== f && 2 !== f) return 1 === f && n.isXMLDoc(a) || (b = n.propFix[b] || b, e = n.propHooks[b]),
        void 0 !== c ? e && "set" in e && void 0 !== (d = e.set(a, c, b)) ? d : a[b] = c : e && "get" in e && null !== (d = e.get(a, b)) ? d : a[b]
    }, propHooks: {
      tabIndex: {
        get: function (a) {
          var b = n.find.attr(a, "tabindex");
          return b ? parseInt(b, 10) : cb.test(a.nodeName) || db.test(a.nodeName) && a.href ? 0 : -1
        }
      }
    }, propFix: {"for": "htmlFor", "class": "className"}
  }), l.optSelected || (n.propHooks.selected = {
    get: function (a) {
      var b = a.parentNode;
      return b && b.parentNode && b.parentNode.selectedIndex, null
    }, set: function (a) {
      var b = a.parentNode;
      b && (b.selectedIndex, b.parentNode && b.parentNode.selectedIndex)
    }
  }), n.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function () {
    n.propFix[this.toLowerCase()] = this
  });
  var eb = /[\t\r\n\f]/g;

  function fb(a) {
    return a.getAttribute && a.getAttribute("class") || ""
  }

  n.fn.extend({
    addClass: function (a) {
      var b, c, d, e, f, g, h, i = 0;
      if (n.isFunction(a)) return this.each(function (b) {
        n(this).addClass(a.call(this, b, fb(this)))
      });
      if ("string" == typeof a && a) {
        b = a.match(G) || [];
        while (c = this[i++]) if (e = fb(c), d = 1 === c.nodeType && (" " + e + " ").replace(eb, " ")) {
          g = 0;
          while (f = b[g++]) d.indexOf(" " + f + " ") < 0 && (d += f + " ");
          h = n.trim(d), e !== h && c.setAttribute("class", h)
        }
      }
      return this
    }, removeClass: function (a) {
      var b, c, d, e, f, g, h, i = 0;
      if (n.isFunction(a)) return this.each(function (b) {
        n(this).removeClass(a.call(this, b, fb(this)))
      });
      if (!arguments.length) return this.attr("class", "");
      if ("string" == typeof a && a) {
        b = a.match(G) || [];
        while (c = this[i++]) if (e = fb(c), d = 1 === c.nodeType && (" " + e + " ").replace(eb, " ")) {
          g = 0;
          while (f = b[g++]) while (d.indexOf(" " + f + " ") > -1) d = d.replace(" " + f + " ", " ");
          h = n.trim(d), e !== h && c.setAttribute("class", h)
        }
      }
      return this
    }, toggleClass: function (a, b) {
      var c = typeof a;
      return "boolean" == typeof b && "string" === c ? b ? this.addClass(a) : this.removeClass(a) : n.isFunction(a) ? this.each(function (c) {
        n(this).toggleClass(a.call(this, c, fb(this), b), b)
      }) : this.each(function () {
        var b, d, e, f;
        if ("string" === c) {
          d = 0, e = n(this), f = a.match(G) || [];
          while (b = f[d++]) e.hasClass(b) ? e.removeClass(b) : e.addClass(b)
        } else void 0 !== a && "boolean" !== c || (b = fb(this), b && N.set(this, "__className__", b), this.setAttribute && this.setAttribute("class", b || a === !1 ? "" : N.get(this, "__className__") || ""))
      })
    }, hasClass: function (a) {
      var b, c, d = 0;
      b = " " + a + " ";
      while (c = this[d++]) if (1 === c.nodeType && (" " + fb(c) + " ").replace(eb, " ").indexOf(b) > -1) return !0;
      return !1
    }
  });
  var gb = /\r/g, hb = /[\x20\t\r\n\f]+/g;
  n.fn.extend({
    val: function (a) {
      var b, c, d, e = this[0];
      {
        if (arguments.length) return d = n.isFunction(a), this.each(function (c) {
          var e;
          1 === this.nodeType && (e = d ? a.call(this, c, n(this).val()) : a, null == e ? e = "" : "number" == typeof e ? e += "" : n.isArray(e) && (e = n.map(e, function (a) {
            return null == a ? "" : a + ""
          })), b = n.valHooks[this.type] || n.valHooks[this.nodeName.toLowerCase()], b && "set" in b && void 0 !== b.set(this, e, "value") || (this.value = e))
        });
        if (e) return b = n.valHooks[e.type] || n.valHooks[e.nodeName.toLowerCase()], b && "get" in b && void 0 !== (c = b.get(e, "value")) ? c : (c = e.value, "string" == typeof c ? c.replace(gb, "") : null == c ? "" : c)
      }
    }
  }), n.extend({
    valHooks: {
      option: {
        get: function (a) {
          var b = n.find.attr(a, "value");
          return null != b ? b : n.trim(n.text(a)).replace(hb, " ")
        }
      }, select: {
        get: function (a) {
          for (var b, c, d = a.options, e = a.selectedIndex, f = "select-one" === a.type || 0 > e, g = f ? null : [], h = f ? e + 1 : d.length, i = 0 > e ? h : f ? e : 0; h > i; i++) if (c = d[i], (c.selected || i === e) && (l.optDisabled ? !c.disabled : null === c.getAttribute("disabled")) && (!c.parentNode.disabled || !n.nodeName(c.parentNode, "optgroup"))) {
            if (b = n(c).val(), f) return b;
            g.push(b)
          }
          return g
        }, set: function (a, b) {
          var c, d, e = a.options, f = n.makeArray(b), g = e.length;
          while (g--) d = e[g], (d.selected = n.inArray(n.valHooks.option.get(d), f) > -1) && (c = !0);
          return c || (a.selectedIndex = -1), f
        }
      }
    }
  }), n.each(["radio", "checkbox"], function () {
    n.valHooks[this] = {
      set: function (a, b) {
        return n.isArray(b) ? a.checked = n.inArray(n(a).val(), b) > -1 : void 0
      }
    }, l.checkOn || (n.valHooks[this].get = function (a) {
      return null === a.getAttribute("value") ? "on" : a.value
    })
  });
  var ib = /^(?:focusinfocus|focusoutblur)$/;
  n.extend(n.event, {
    trigger: function (b, c, e, f) {
      var g, h, i, j, l, m, o, p = [e || d], q = k.call(b, "type") ? b.type : b,
        r = k.call(b, "namespace") ? b.namespace.split(".") : [];
      if (h = i = e = e || d, 3 !== e.nodeType && 8 !== e.nodeType && !ib.test(q + n.event.triggered) && (q.indexOf(".") > -1 && (r = q.split("."), q = r.shift(), r.sort()), l = q.indexOf(":") < 0 && "on" + q, b = b[n.expando] ? b : new n.Event(q, "object" == typeof b && b), b.isTrigger = f ? 2 : 3, b.namespace = r.join("."), b.rnamespace = b.namespace ? new RegExp("(^|\\.)" + r.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, b.result = void 0, b.target || (b.target = e), c = null == c ? [b] : n.makeArray(c, [b]), o = n.event.special[q] || {}, f || !o.trigger || o.trigger.apply(e, c) !== !1)) {
        if (!f && !o.noBubble && !n.isWindow(e)) {
          for (j = o.delegateType || q, ib.test(j + q) || (h = h.parentNode); h; h = h.parentNode) p.push(h), i = h;
          i === (e.ownerDocument || d) && p.push(i.defaultView || i.parentWindow || a)
        }
        g = 0;
        while ((h = p[g++]) && !b.isPropagationStopped()) b.type = g > 1 ? j : o.bindType || q, m = (N.get(h, "events") || {})[b.type] && N.get(h, "handle"), m && m.apply(h, c), m = l && h[l], m && m.apply && L(h) && (b.result = m.apply(h, c), b.result === !1 && b.preventDefault());
        return b.type = q, f || b.isDefaultPrevented() || o._default && o._default.apply(p.pop(), c) !== !1 || !L(e) || l && n.isFunction(e[q]) && !n.isWindow(e) && (i = e[l], i && (e[l] = null), n.event.triggered = q, e[q](), n.event.triggered = void 0, i && (e[l] = i)), b.result
      }
    }, simulate: function (a, b, c) {
      var d = n.extend(new n.Event, c, {type: a, isSimulated: !0});
      n.event.trigger(d, null, b)
    }
  }), n.fn.extend({
    trigger: function (a, b) {
      return this.each(function () {
        n.event.trigger(a, b, this)
      })
    }, triggerHandler: function (a, b) {
      var c = this[0];
      return c ? n.event.trigger(a, b, c, !0) : void 0
    }
  }), n.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "), function (a, b) {
    n.fn[b] = function (a, c) {
      return arguments.length > 0 ? this.on(b, null, a, c) : this.trigger(b)
    }
  }), n.fn.extend({
    hover: function (a, b) {
      return this.mouseenter(a).mouseleave(b || a)
    }
  }), l.focusin = "onfocusin" in a, l.focusin || n.each({focus: "focusin", blur: "focusout"}, function (a, b) {
    var c = function (a) {
      n.event.simulate(b, a.target, n.event.fix(a))
    };
    n.event.special[b] = {
      setup: function () {
        var d = this.ownerDocument || this, e = N.access(d, b);
        e || d.addEventListener(a, c, !0), N.access(d, b, (e || 0) + 1)
      }, teardown: function () {
        var d = this.ownerDocument || this, e = N.access(d, b) - 1;
        e ? N.access(d, b, e) : (d.removeEventListener(a, c, !0), N.remove(d, b))
      }
    }
  });
  var jb = a.location, kb = n.now(), lb = /\?/;
  n.parseJSON = function (a) {
    return JSON.parse(a + "")
  }, n.parseXML = function (b) {
    var c;
    if (!b || "string" != typeof b) return null;
    try {
      c = (new a.DOMParser).parseFromString(b, "text/xml")
    } catch (d) {
      c = void 0
    }
    return c && !c.getElementsByTagName("parsererror").length || n.error("Invalid XML: " + b), c
  };
  var mb = /#.*$/, nb = /([?&])_=[^&]*/, ob = /^(.*?):[ \t]*([^\r\n]*)$/gm,
    pb = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/, qb = /^(?:GET|HEAD)$/, rb = /^\/\//, sb = {},
    tb = {}, ub = "*/".concat("*"), vb = d.createElement("a");
  vb.href = jb.href;

  function wb(a) {
    return function (b, c) {
      "string" != typeof b && (c = b, b = "*");
      var d, e = 0, f = b.toLowerCase().match(G) || [];
      if (n.isFunction(c)) while (d = f[e++]) "+" === d[0] ? (d = d.slice(1) || "*", (a[d] = a[d] || []).unshift(c)) : (a[d] = a[d] || []).push(c)
    }
  }

  function xb(a, b, c, d) {
    var e = {}, f = a === tb;

    function g(h) {
      var i;
      return e[h] = !0, n.each(a[h] || [], function (a, h) {
        var j = h(b, c, d);
        return "string" != typeof j || f || e[j] ? f ? !(i = j) : void 0 : (b.dataTypes.unshift(j), g(j), !1)
      }), i
    }

    return g(b.dataTypes[0]) || !e["*"] && g("*")
  }

  function yb(a, b) {
    var c, d, e = n.ajaxSettings.flatOptions || {};
    for (c in b) void 0 !== b[c] && ((e[c] ? a : d || (d = {}))[c] = b[c]);
    return d && n.extend(!0, a, d), a
  }

  function zb(a, b, c) {
    var d, e, f, g, h = a.contents, i = a.dataTypes;
    while ("*" === i[0]) i.shift(), void 0 === d && (d = a.mimeType || b.getResponseHeader("Content-Type"));
    if (d) for (e in h) if (h[e] && h[e].test(d)) {
      i.unshift(e);
      break
    }
    if (i[0] in c) f = i[0]; else {
      for (e in c) {
        if (!i[0] || a.converters[e + " " + i[0]]) {
          f = e;
          break
        }
        g || (g = e)
      }
      f = f || g
    }
    return f ? (f !== i[0] && i.unshift(f), c[f]) : void 0
  }

  function Ab(a, b, c, d) {
    var e, f, g, h, i, j = {}, k = a.dataTypes.slice();
    if (k[1]) for (g in a.converters) j[g.toLowerCase()] = a.converters[g];
    f = k.shift();
    while (f) if (a.responseFields[f] && (c[a.responseFields[f]] = b), !i && d && a.dataFilter && (b = a.dataFilter(b, a.dataType)), i = f, f = k.shift()) if ("*" === f) f = i; else if ("*" !== i && i !== f) {
      if (g = j[i + " " + f] || j["* " + f], !g) for (e in j) if (h = e.split(" "), h[1] === f && (g = j[i + " " + h[0]] || j["* " + h[0]])) {
        g === !0 ? g = j[e] : j[e] !== !0 && (f = h[0], k.unshift(h[1]));
        break
      }
      if (g !== !0) if (g && a["throws"]) b = g(b); else try {
        b = g(b)
      } catch (l) {
        return {state: "parsererror", error: g ? l : "No conversion from " + i + " to " + f}
      }
    }
    return {state: "success", data: b}
  }

  n.extend({
    active: 0,
    lastModified: {},
    etag: {},
    ajaxSettings: {
      url: jb.href,
      type: "GET",
      isLocal: pb.test(jb.protocol),
      global: !0,
      processData: !0,
      async: !0,
      contentType: "application/x-www-form-urlencoded; charset=UTF-8",
      accepts: {
        "*": ub,
        text: "text/plain",
        html: "text/html",
        xml: "application/xml, text/xml",
        json: "application/json, text/javascript"
      },
      contents: {xml: /\bxml\b/, html: /\bhtml/, json: /\bjson\b/},
      responseFields: {xml: "responseXML", text: "responseText", json: "responseJSON"},
      converters: {"* text": String, "text html": !0, "text json": n.parseJSON, "text xml": n.parseXML},
      flatOptions: {url: !0, context: !0}
    },
    ajaxSetup: function (a, b) {
      return b ? yb(yb(a, n.ajaxSettings), b) : yb(n.ajaxSettings, a)
    },
    ajaxPrefilter: wb(sb),
    ajaxTransport: wb(tb),
    ajax: function (b, c) {
      "object" == typeof b && (c = b, b = void 0), c = c || {};
      var e, f, g, h, i, j, k, l, m = n.ajaxSetup({}, c), o = m.context || m,
        p = m.context && (o.nodeType || o.jquery) ? n(o) : n.event, q = n.Deferred(),
        r = n.Callbacks("once memory"), s = m.statusCode || {}, t = {}, u = {}, v = 0, w = "canceled", x = {
          readyState: 0, getResponseHeader: function (a) {
            var b;
            if (2 === v) {
              if (!h) {
                h = {};
                while (b = ob.exec(g)) h[b[1].toLowerCase()] = b[2]
              }
              b = h[a.toLowerCase()]
            }
            return null == b ? null : b
          }, getAllResponseHeaders: function () {
            return 2 === v ? g : null
          }, setRequestHeader: function (a, b) {
            var c = a.toLowerCase();
            return v || (a = u[c] = u[c] || a, t[a] = b), this
          }, overrideMimeType: function (a) {
            return v || (m.mimeType = a), this
          }, statusCode: function (a) {
            var b;
            if (a) if (2 > v) for (b in a) s[b] = [s[b], a[b]]; else x.always(a[x.status]);
            return this
          }, abort: function (a) {
            var b = a || w;
            return e && e.abort(b), z(0, b), this
          }
        };
      if (q.promise(x).complete = r.add, x.success = x.done, x.error = x.fail, m.url = ((b || m.url || jb.href) + "").replace(mb, "").replace(rb, jb.protocol + "//"), m.type = c.method || c.type || m.method || m.type, m.dataTypes = n.trim(m.dataType || "*").toLowerCase().match(G) || [""], null == m.crossDomain) {
        j = d.createElement("a");
        try {
          j.href = m.url, j.href = j.href, m.crossDomain = vb.protocol + "//" + vb.host != j.protocol + "//" + j.host
        } catch (y) {
          m.crossDomain = !0
        }
      }
      if (m.data && m.processData && "string" != typeof m.data && (m.data = n.param(m.data, m.traditional)), xb(sb, m, c, x), 2 === v) return x;
      k = n.event && m.global, k && 0 === n.active++ && n.event.trigger("ajaxStart"), m.type = m.type.toUpperCase(), m.hasContent = !qb.test(m.type), f = m.url, m.hasContent || (m.data && (f = m.url += (lb.test(f) ? "&" : "?") + m.data, delete m.data), m.cache === !1 && (m.url = nb.test(f) ? f.replace(nb, "$1_=" + kb++) : f + (lb.test(f) ? "&" : "?") + "_=" + kb++)), m.ifModified && (n.lastModified[f] && x.setRequestHeader("If-Modified-Since", n.lastModified[f]), n.etag[f] && x.setRequestHeader("If-None-Match", n.etag[f])), (m.data && m.hasContent && m.contentType !== !1 || c.contentType) && x.setRequestHeader("Content-Type", m.contentType), x.setRequestHeader("Accept", m.dataTypes[0] && m.accepts[m.dataTypes[0]] ? m.accepts[m.dataTypes[0]] + ("*" !== m.dataTypes[0] ? ", " + ub + "; q=0.01" : "") : m.accepts["*"]);
      for (l in m.headers) x.setRequestHeader(l, m.headers[l]);
      if (m.beforeSend && (m.beforeSend.call(o, x, m) === !1 || 2 === v)) return x.abort();
      w = "abort";
      for (l in {success: 1, error: 1, complete: 1}) x[l](m[l]);
      if (e = xb(tb, m, c, x)) {
        if (x.readyState = 1, k && p.trigger("ajaxSend", [x, m]), 2 === v) return x;
        m.async && m.timeout > 0 && (i = a.setTimeout(function () {
          x.abort("timeout")
        }, m.timeout));
        try {
          v = 1, e.send(t, z)
        } catch (y) {
          if (!(2 > v)) throw y;
          z(-1, y)
        }
      } else z(-1, "No Transport");

      function z(b, c, d, h) {
        var j, l, t, u, w, y = c;
        2 !== v && (v = 2, i && a.clearTimeout(i), e = void 0, g = h || "", x.readyState = b > 0 ? 4 : 0, j = b >= 200 && 300 > b || 304 === b, d && (u = zb(m, x, d)), u = Ab(m, u, x, j), j ? (m.ifModified && (w = x.getResponseHeader("Last-Modified"), w && (n.lastModified[f] = w), w = x.getResponseHeader("etag"), w && (n.etag[f] = w)), 204 === b || "HEAD" === m.type ? y = "nocontent" : 304 === b ? y = "notmodified" : (y = u.state, l = u.data, t = u.error, j = !t)) : (t = y, !b && y || (y = "error", 0 > b && (b = 0))), x.status = b, x.statusText = (c || y) + "", j ? q.resolveWith(o, [l, y, x]) : q.rejectWith(o, [x, y, t]), x.statusCode(s), s = void 0, k && p.trigger(j ? "ajaxSuccess" : "ajaxError", [x, m, j ? l : t]), r.fireWith(o, [x, y]), k && (p.trigger("ajaxComplete", [x, m]), --n.active || n.event.trigger("ajaxStop")))
      }

      return x
    },
    getJSON: function (a, b, c) {
      return n.get(a, b, c, "json")
    },
    getScript: function (a, b) {
      return n.get(a, void 0, b, "script")
    }
  }), n.each(["get", "post"], function (a, b) {
    n[b] = function (a, c, d, e) {
      return n.isFunction(c) && (e = e || d, d = c, c = void 0), n.ajax(n.extend({
        url: a,
        type: b,
        dataType: e,
        data: c,
        success: d
      }, n.isPlainObject(a) && a))
    }
  }), n._evalUrl = function (a) {
    return n.ajax({url: a, type: "GET", dataType: "script", async: !1, global: !1, "throws": !0})
  }, n.fn.extend({
    wrapAll: function (a) {
      var b;
      return n.isFunction(a) ? this.each(function (b) {
        n(this).wrapAll(a.call(this, b))
      }) : (this[0] && (b = n(a, this[0].ownerDocument).eq(0).clone(!0), this[0].parentNode && b.insertBefore(this[0]), b.map(function () {
        var a = this;
        while (a.firstElementChild) a = a.firstElementChild;
        return a
      }).append(this)), this)
    }, wrapInner: function (a) {
      return n.isFunction(a) ? this.each(function (b) {
        n(this).wrapInner(a.call(this, b))
      }) : this.each(function () {
        var b = n(this), c = b.contents();
        c.length ? c.wrapAll(a) : b.append(a)
      })
    }, wrap: function (a) {
      var b = n.isFunction(a);
      return this.each(function (c) {
        n(this).wrapAll(b ? a.call(this, c) : a)
      })
    }, unwrap: function () {
      return this.parent().each(function () {
        n.nodeName(this, "body") || n(this).replaceWith(this.childNodes)
      }).end()
    }
  }), n.expr.filters.hidden = function (a) {
    return !n.expr.filters.visible(a)
  }, n.expr.filters.visible = function (a) {
    return a.offsetWidth > 0 || a.offsetHeight > 0 || a.getClientRects().length > 0
  };
  var Bb = /%20/g, Cb = /\[\]$/, Db = /\r?\n/g, Eb = /^(?:submit|button|image|reset|file)$/i,
    Fb = /^(?:input|select|textarea|keygen)/i;

  function Gb(a, b, c, d) {
    var e;
    if (n.isArray(b)) n.each(b, function (b, e) {
      c || Cb.test(a) ? d(a, e) : Gb(a + "[" + ("object" == typeof e && null != e ? b : "") + "]", e, c, d)
    }); else if (c || "object" !== n.type(b)) d(a, b); else for (e in b) Gb(a + "[" + e + "]", b[e], c, d)
  }

  n.param = function (a, b) {
    var c, d = [], e = function (a, b) {
      b = n.isFunction(b) ? b() : null == b ? "" : b, d[d.length] = encodeURIComponent(a) + "=" + encodeURIComponent(b)
    };
    if (void 0 === b && (b = n.ajaxSettings && n.ajaxSettings.traditional), n.isArray(a) || a.jquery && !n.isPlainObject(a)) n.each(a, function () {
      e(this.name, this.value)
    }); else for (c in a) Gb(c, a[c], b, e);
    return d.join("&").replace(Bb, "+")
  }, n.fn.extend({
    serialize: function () {
      return n.param(this.serializeArray())
    }, serializeArray: function () {
      return this.map(function () {
        var a = n.prop(this, "elements");
        return a ? n.makeArray(a) : this
      }).filter(function () {
        var a = this.type;
        return this.name && !n(this).is(":disabled") && Fb.test(this.nodeName) && !Eb.test(a) && (this.checked || !X.test(a))
      }).map(function (a, b) {
        var c = n(this).val();
        return null == c ? null : n.isArray(c) ? n.map(c, function (a) {
          return {name: b.name, value: a.replace(Db, "\r\n")}
        }) : {name: b.name, value: c.replace(Db, "\r\n")}
      }).get()
    }
  }), n.ajaxSettings.xhr = function () {
    try {
      return new a.XMLHttpRequest
    } catch (b) {
    }
  };
  var Hb = {0: 200, 1223: 204}, Ib = n.ajaxSettings.xhr();
  l.cors = !!Ib && "withCredentials" in Ib, l.ajax = Ib = !!Ib, n.ajaxTransport(function (b) {
    var c, d;
    return l.cors || Ib && !b.crossDomain ? {
      send: function (e, f) {
        var g, h = b.xhr();
        if (h.open(b.type, b.url, b.async, b.username, b.password), b.xhrFields) for (g in b.xhrFields) h[g] = b.xhrFields[g];
        b.mimeType && h.overrideMimeType && h.overrideMimeType(b.mimeType), b.crossDomain || e["X-Requested-With"] || (e["X-Requested-With"] = "XMLHttpRequest");
        for (g in e) h.setRequestHeader(g, e[g]);
        c = function (a) {
          return function () {
            c && (c = d = h.onload = h.onerror = h.onabort = h.onreadystatechange = null, "abort" === a ? h.abort() : "error" === a ? "number" != typeof h.status ? f(0, "error") : f(h.status, h.statusText) : f(Hb[h.status] || h.status, h.statusText, "text" !== (h.responseType || "text") || "string" != typeof h.responseText ? {binary: h.response} : {text: h.responseText}, h.getAllResponseHeaders()))
          }
        }, h.onload = c(), d = h.onerror = c("error"), void 0 !== h.onabort ? h.onabort = d : h.onreadystatechange = function () {
          4 === h.readyState && a.setTimeout(function () {
            c && d()
          })
        }, c = c("abort");
        try {
          h.send(b.hasContent && b.data || null)
        } catch (i) {
          if (c) throw i
        }
      }, abort: function () {
        c && c()
      }
    } : void 0
  }), n.ajaxSetup({
    accepts: {script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},
    contents: {script: /\b(?:java|ecma)script\b/},
    converters: {
      "text script": function (a) {
        return n.globalEval(a), a
      }
    }
  }), n.ajaxPrefilter("script", function (a) {
    void 0 === a.cache && (a.cache = !1), a.crossDomain && (a.type = "GET")
  }), n.ajaxTransport("script", function (a) {
    if (a.crossDomain) {
      var b, c;
      return {
        send: function (e, f) {
          b = n("<script>").prop({charset: a.scriptCharset, src: a.url}).on("load error", c = function (a) {
            b.remove(), c = null, a && f("error" === a.type ? 404 : 200, a.type)
          }), d.head.appendChild(b[0])
        }, abort: function () {
          c && c()
        }
      }
    }
  });
  var Jb = [], Kb = /(=)\?(?=&|$)|\?\?/;
  n.ajaxSetup({
    jsonp: "callback", jsonpCallback: function () {
      var a = Jb.pop() || n.expando + "_" + kb++;
      return this[a] = !0, a
    }
  }), n.ajaxPrefilter("json jsonp", function (b, c, d) {
    var e, f, g,
      h = b.jsonp !== !1 && (Kb.test(b.url) ? "url" : "string" == typeof b.data && 0 === (b.contentType || "").indexOf("application/x-www-form-urlencoded") && Kb.test(b.data) && "data");
    return h || "jsonp" === b.dataTypes[0] ? (e = b.jsonpCallback = n.isFunction(b.jsonpCallback) ? b.jsonpCallback() : b.jsonpCallback, h ? b[h] = b[h].replace(Kb, "$1" + e) : b.jsonp !== !1 && (b.url += (lb.test(b.url) ? "&" : "?") + b.jsonp + "=" + e), b.converters["script json"] = function () {
      return g || n.error(e + " was not called"), g[0]
    }, b.dataTypes[0] = "json", f = a[e], a[e] = function () {
      g = arguments
    }, d.always(function () {
      void 0 === f ? n(a).removeProp(e) : a[e] = f, b[e] && (b.jsonpCallback = c.jsonpCallback, Jb.push(e)), g && n.isFunction(f) && f(g[0]), g = f = void 0
    }), "script") : void 0
  }), n.parseHTML = function (a, b, c) {
    if (!a || "string" != typeof a) return null;
    "boolean" == typeof b && (c = b, b = !1), b = b || d;
    var e = x.exec(a), f = !c && [];
    return e ? [b.createElement(e[1])] : (e = ca([a], b, f), f && f.length && n(f).remove(), n.merge([], e.childNodes))
  };
  var Lb = n.fn.load;
  n.fn.load = function (a, b, c) {
    if ("string" != typeof a && Lb) return Lb.apply(this, arguments);
    var d, e, f, g = this, h = a.indexOf(" ");
    return h > -1 && (d = n.trim(a.slice(h)), a = a.slice(0, h)), n.isFunction(b) ? (c = b, b = void 0) : b && "object" == typeof b && (e = "POST"), g.length > 0 && n.ajax({
      url: a,
      type: e || "GET",
      dataType: "html",
      data: b
    }).done(function (a) {
      f = arguments, g.html(d ? n("<div>").append(n.parseHTML(a)).find(d) : a)
    }).always(c && function (a, b) {
      g.each(function () {
        c.apply(this, f || [a.responseText, b, a])
      })
    }), this
  }, n.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function (a, b) {
    n.fn[b] = function (a) {
      return this.on(b, a)
    }
  }), n.expr.filters.animated = function (a) {
    return n.grep(n.timers, function (b) {
      return a === b.elem
    }).length
  };

  function Mb(a) {
    return n.isWindow(a) ? a : 9 === a.nodeType && a.defaultView
  }

  n.offset = {
    setOffset: function (a, b, c) {
      var d, e, f, g, h, i, j, k = n.css(a, "position"), l = n(a), m = {};
      "static" === k && (a.style.position = "relative"), h = l.offset(), f = n.css(a, "top"), i = n.css(a, "left"), j = ("absolute" === k || "fixed" === k) && (f + i).indexOf("auto") > -1, j ? (d = l.position(), g = d.top, e = d.left) : (g = parseFloat(f) || 0, e = parseFloat(i) || 0), n.isFunction(b) && (b = b.call(a, c, n.extend({}, h))), null != b.top && (m.top = b.top - h.top + g), null != b.left && (m.left = b.left - h.left + e), "using" in b ? b.using.call(a, m) : l.css(m)
    }
  }, n.fn.extend({
    offset: function (a) {
      if (arguments.length) return void 0 === a ? this : this.each(function (b) {
        n.offset.setOffset(this, a, b)
      });
      var b, c, d = this[0], e = {top: 0, left: 0}, f = d && d.ownerDocument;
      if (f) return b = f.documentElement, n.contains(b, d) ? (e = d.getBoundingClientRect(), c = Mb(f), {
        top: e.top + c.pageYOffset - b.clientTop,
        left: e.left + c.pageXOffset - b.clientLeft
      }) : e
    }, position: function () {
      if (this[0]) {
        var a, b, c = this[0], d = {top: 0, left: 0};
        return "fixed" === n.css(c, "position") ? b = c.getBoundingClientRect() : (a = this.offsetParent(), b = this.offset(), n.nodeName(a[0], "html") || (d = a.offset()), d.top += n.css(a[0], "borderTopWidth", !0), d.left += n.css(a[0], "borderLeftWidth", !0)), {
          top: b.top - d.top - n.css(c, "marginTop", !0),
          left: b.left - d.left - n.css(c, "marginLeft", !0)
        }
      }
    }, offsetParent: function () {
      return this.map(function () {
        var a = this.offsetParent;
        while (a && "static" === n.css(a, "position")) a = a.offsetParent;
        return a || Ea
      })
    }
  }), n.each({scrollLeft: "pageXOffset", scrollTop: "pageYOffset"}, function (a, b) {
    var c = "pageYOffset" === b;
    n.fn[a] = function (d) {
      return K(this, function (a, d, e) {
        var f = Mb(a);
        return void 0 === e ? f ? f[b] : a[d] : void (f ? f.scrollTo(c ? f.pageXOffset : e, c ? e : f.pageYOffset) : a[d] = e)
      }, a, d, arguments.length)
    }
  }), n.each(["top", "left"], function (a, b) {
    n.cssHooks[b] = Ga(l.pixelPosition, function (a, c) {
      return c ? (c = Fa(a, b), Ba.test(c) ? n(a).position()[b] + "px" : c) : void 0
    })
  }), n.each({Height: "height", Width: "width"}, function (a, b) {
    n.each({padding: "inner" + a, content: b, "": "outer" + a}, function (c, d) {
      n.fn[d] = function (d, e) {
        var f = arguments.length && (c || "boolean" != typeof d),
          g = c || (d === !0 || e === !0 ? "margin" : "border");
        return K(this, function (b, c, d) {
          var e;
          return n.isWindow(b) ? b.document.documentElement["client" + a] : 9 === b.nodeType ? (e = b.documentElement, Math.max(b.body["scroll" + a], e["scroll" + a], b.body["offset" + a], e["offset" + a], e["client" + a])) : void 0 === d ? n.css(b, c, g) : n.style(b, c, d, g)
        }, b, f ? d : void 0, f, null)
      }
    })
  }), n.fn.extend({
    bind: function (a, b, c) {
      return this.on(a, null, b, c)
    }, unbind: function (a, b) {
      return this.off(a, null, b)
    }, delegate: function (a, b, c, d) {
      return this.on(b, a, c, d)
    }, undelegate: function (a, b, c) {
      return 1 === arguments.length ? this.off(a, "**") : this.off(b, a || "**", c)
    }, size: function () {
      return this.length
    }
  }), n.fn.andSelf = n.fn.addBack, "function" == typeof define && define.amd && define("jquery", [], function () {
    return n
  });
  var Nb = a.jQuery, Ob = a.$;
  return n.noConflict = function (b) {
    return a.$ === n && (a.$ = Ob), b && a.jQuery === n && (a.jQuery = Nb), n
  }, b || (a.jQuery = a.$ = n), n
});
/*!
 * Bootstrap v3.3.5 (http://getbootstrap.com)
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under the MIT license
 */
if ("undefined" == typeof jQuery) throw new Error("Bootstrap's JavaScript requires jQuery");
+function (a) {
  "use strict";
  var b = a.fn.jquery.split(" ")[0].split(".");
  if (b[0] < 2 && b[1] < 9 || 1 == b[0] && 9 == b[1] && b[2] < 1) throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher")
}(jQuery), +function (a) {
  "use strict";

  function b() {
    var a = document.createElement("bootstrap"), b = {
      WebkitTransition: "webkitTransitionEnd",
      MozTransition: "transitionend",
      OTransition: "oTransitionEnd otransitionend",
      transition: "transitionend"
    };
    for (var c in b) if (void 0 !== a.style[c]) return {end: b[c]};
    return !1
  }

  a.fn.emulateTransitionEnd = function (b) {
    var c = !1, d = this;
    a(this).one("bsTransitionEnd", function () {
      c = !0
    });
    var e = function () {
      c || a(d).trigger(a.support.transition.end)
    };
    return setTimeout(e, b), this
  }, a(function () {
    a.support.transition = b(), a.support.transition && (a.event.special.bsTransitionEnd = {
      bindType: a.support.transition.end,
      delegateType: a.support.transition.end,
      handle: function (b) {
        return a(b.target).is(this) ? b.handleObj.handler.apply(this, arguments) : void 0
      }
    })
  })
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var c = a(this), e = c.data("bs.alert");
      e || c.data("bs.alert", e = new d(this)), "string" == typeof b && e[b].call(c)
    })
  }

  var c = '[data-dismiss="alert"]', d = function (b) {
    a(b).on("click", c, this.close)
  };
  d.VERSION = "3.3.5", d.TRANSITION_DURATION = 150, d.prototype.close = function (b) {
    function c() {
      g.detach().trigger("closed.bs.alert").remove()
    }

    var e = a(this), f = e.attr("data-target");
    f || (f = e.attr("href"), f = f && f.replace(/.*(?=#[^\s]*$)/, ""));
    var g = a(f);
    b && b.preventDefault(), g.length || (g = e.closest(".alert")), g.trigger(b = a.Event("close.bs.alert")), b.isDefaultPrevented() || (g.removeClass("in"), a.support.transition && g.hasClass("fade") ? g.one("bsTransitionEnd", c).emulateTransitionEnd(d.TRANSITION_DURATION) : c())
  };
  var e = a.fn.alert;
  a.fn.alert = b, a.fn.alert.Constructor = d, a.fn.alert.noConflict = function () {
    return a.fn.alert = e, this
  }, a(document).on("click.bs.alert.data-api", c, d.prototype.close)
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.button"), f = "object" == typeof b && b;
      e || d.data("bs.button", e = new c(this, f)), "toggle" == b ? e.toggle() : b && e.setState(b)
    })
  }

  var c = function (b, d) {
    this.$element = a(b), this.options = a.extend({}, c.DEFAULTS, d), this.isLoading = !1
  };
  c.VERSION = "3.3.5", c.DEFAULTS = {loadingText: "loading..."}, c.prototype.setState = function (b) {
    var c = "disabled", d = this.$element, e = d.is("input") ? "val" : "html", f = d.data();
    b += "Text", null == f.resetText && d.data("resetText", d[e]()), setTimeout(a.proxy(function () {
      d[e](null == f[b] ? this.options[b] : f[b]), "loadingText" == b ? (this.isLoading = !0, d.addClass(c).attr(c, c)) : this.isLoading && (this.isLoading = !1, d.removeClass(c).removeAttr(c))
    }, this), 0)
  }, c.prototype.toggle = function () {
    var a = !0, b = this.$element.closest('[data-toggle="buttons"]');
    if (b.length) {
      var c = this.$element.find("input");
      "radio" == c.prop("type") ? (c.prop("checked") && (a = !1), b.find(".active").removeClass("active"), this.$element.addClass("active")) : "checkbox" == c.prop("type") && (c.prop("checked") !== this.$element.hasClass("active") && (a = !1), this.$element.toggleClass("active")), c.prop("checked", this.$element.hasClass("active")), a && c.trigger("change")
    } else this.$element.attr("aria-pressed", !this.$element.hasClass("active")), this.$element.toggleClass("active")
  };
  var d = a.fn.button;
  a.fn.button = b, a.fn.button.Constructor = c, a.fn.button.noConflict = function () {
    return a.fn.button = d, this
  }, a(document).on("click.bs.button.data-api", '[data-toggle^="button"]', function (c) {
    var d = a(c.target);
    d.hasClass("btn") || (d = d.closest(".btn")), b.call(d, "toggle"), a(c.target).is('input[type="radio"]') || a(c.target).is('input[type="checkbox"]') || c.preventDefault()
  }).on("focus.bs.button.data-api blur.bs.button.data-api", '[data-toggle^="button"]', function (b) {
    a(b.target).closest(".btn").toggleClass("focus", /^focus(in)?$/.test(b.type))
  })
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.carousel"),
        f = a.extend({}, c.DEFAULTS, d.data(), "object" == typeof b && b),
        g = "string" == typeof b ? b : f.slide;
      e || d.data("bs.carousel", e = new c(this, f)), "number" == typeof b ? e.to(b) : g ? e[g]() : f.interval && e.pause().cycle()
    })
  }

  var c = function (b, c) {
    this.$element = a(b), this.$indicators = this.$element.find(".carousel-indicators"), this.options = c, this.paused = null, this.sliding = null, this.interval = null, this.$active = null, this.$items = null, this.options.keyboard && this.$element.on("keydown.bs.carousel", a.proxy(this.keydown, this)), "hover" == this.options.pause && !("ontouchstart" in document.documentElement) && this.$element.on("mouseenter.bs.carousel", a.proxy(this.pause, this)).on("mouseleave.bs.carousel", a.proxy(this.cycle, this))
  };
  c.VERSION = "3.3.5", c.TRANSITION_DURATION = 600, c.DEFAULTS = {
    interval: 5e3,
    pause: "hover",
    wrap: !0,
    keyboard: !0
  }, c.prototype.keydown = function (a) {
    if (!/input|textarea/i.test(a.target.tagName)) {
      switch (a.which) {
        case 37:
          this.prev();
          break;
        case 39:
          this.next();
          break;
        default:
          return
      }
      a.preventDefault()
    }
  }, c.prototype.cycle = function (b) {
    return b || (this.paused = !1), this.interval && clearInterval(this.interval), this.options.interval && !this.paused && (this.interval = setInterval(a.proxy(this.next, this), this.options.interval)), this
  }, c.prototype.getItemIndex = function (a) {
    return this.$items = a.parent().children(".item"), this.$items.index(a || this.$active)
  }, c.prototype.getItemForDirection = function (a, b) {
    var c = this.getItemIndex(b), d = "prev" == a && 0 === c || "next" == a && c == this.$items.length - 1;
    if (d && !this.options.wrap) return b;
    var e = "prev" == a ? -1 : 1, f = (c + e) % this.$items.length;
    return this.$items.eq(f)
  }, c.prototype.to = function (a) {
    var b = this, c = this.getItemIndex(this.$active = this.$element.find(".item.active"));
    return a > this.$items.length - 1 || 0 > a ? void 0 : this.sliding ? this.$element.one("slid.bs.carousel", function () {
      b.to(a)
    }) : c == a ? this.pause().cycle() : this.slide(a > c ? "next" : "prev", this.$items.eq(a))
  }, c.prototype.pause = function (b) {
    return b || (this.paused = !0), this.$element.find(".next, .prev").length && a.support.transition && (this.$element.trigger(a.support.transition.end), this.cycle(!0)), this.interval = clearInterval(this.interval), this
  }, c.prototype.next = function () {
    return this.sliding ? void 0 : this.slide("next")
  }, c.prototype.prev = function () {
    return this.sliding ? void 0 : this.slide("prev")
  }, c.prototype.slide = function (b, d) {
    var e = this.$element.find(".item.active"), f = d || this.getItemForDirection(b, e), g = this.interval,
      h = "next" == b ? "left" : "right", i = this;
    if (f.hasClass("active")) return this.sliding = !1;
    var j = f[0], k = a.Event("slide.bs.carousel", {relatedTarget: j, direction: h});
    if (this.$element.trigger(k), !k.isDefaultPrevented()) {
      if (this.sliding = !0, g && this.pause(), this.$indicators.length) {
        this.$indicators.find(".active").removeClass("active");
        var l = a(this.$indicators.children()[this.getItemIndex(f)]);
        l && l.addClass("active")
      }
      var m = a.Event("slid.bs.carousel", {relatedTarget: j, direction: h});
      return a.support.transition && this.$element.hasClass("slide") ? (f.addClass(b), f[0].offsetWidth, e.addClass(h), f.addClass(h), e.one("bsTransitionEnd", function () {
        f.removeClass([b, h].join(" ")).addClass("active"), e.removeClass(["active", h].join(" ")), i.sliding = !1, setTimeout(function () {
          i.$element.trigger(m)
        }, 0)
      }).emulateTransitionEnd(c.TRANSITION_DURATION)) : (e.removeClass("active"), f.addClass("active"), this.sliding = !1, this.$element.trigger(m)), g && this.cycle(), this
    }
  };
  var d = a.fn.carousel;
  a.fn.carousel = b, a.fn.carousel.Constructor = c, a.fn.carousel.noConflict = function () {
    return a.fn.carousel = d, this
  };
  var e = function (c) {
    var d, e = a(this), f = a(e.attr("data-target") || (d = e.attr("href")) && d.replace(/.*(?=#[^\s]+$)/, ""));
    if (f.hasClass("carousel")) {
      var g = a.extend({}, f.data(), e.data()), h = e.attr("data-slide-to");
      h && (g.interval = !1), b.call(f, g), h && f.data("bs.carousel").to(h), c.preventDefault()
    }
  };
  a(document).on("click.bs.carousel.data-api", "[data-slide]", e).on("click.bs.carousel.data-api", "[data-slide-to]", e), a(window).on("load", function () {
    a('[data-ride="carousel"]').each(function () {
      var c = a(this);
      b.call(c, c.data())
    })
  })
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    var c, d = b.attr("data-target") || (c = b.attr("href")) && c.replace(/.*(?=#[^\s]+$)/, "");
    return a(d)
  }

  function c(b) {
    return this.each(function () {
      var c = a(this), e = c.data("bs.collapse"),
        f = a.extend({}, d.DEFAULTS, c.data(), "object" == typeof b && b);
      !e && f.toggle && /show|hide/.test(b) && (f.toggle = !1), e || c.data("bs.collapse", e = new d(this, f)), "string" == typeof b && e[b]()
    })
  }

  var d = function (b, c) {
    this.$element = a(b), this.options = a.extend({}, d.DEFAULTS, c), this.$trigger = a('[data-toggle="collapse"][href="#' + b.id + '"],[data-toggle="collapse"][data-target="#' + b.id + '"]'), this.transitioning = null, this.options.parent ? this.$parent = this.getParent() : this.addAriaAndCollapsedClass(this.$element, this.$trigger), this.options.toggle && this.toggle()
  };
  d.VERSION = "3.3.5", d.TRANSITION_DURATION = 350, d.DEFAULTS = {toggle: !0}, d.prototype.dimension = function () {
    var a = this.$element.hasClass("width");
    return a ? "width" : "height"
  }, d.prototype.show = function () {
    if (!this.transitioning && !this.$element.hasClass("in")) {
      var b, e = this.$parent && this.$parent.children(".panel").children(".in, .collapsing");
      if (!(e && e.length && (b = e.data("bs.collapse"), b && b.transitioning))) {
        var f = a.Event("show.bs.collapse");
        if (this.$element.trigger(f), !f.isDefaultPrevented()) {
          e && e.length && (c.call(e, "hide"), b || e.data("bs.collapse", null));
          var g = this.dimension();
          this.$element.removeClass("collapse").addClass("collapsing")[g](0).attr("aria-expanded", !0), this.$trigger.removeClass("collapsed").attr("aria-expanded", !0), this.transitioning = 1;
          var h = function () {
            this.$element.removeClass("collapsing").addClass("collapse in")[g](""), this.transitioning = 0, this.$element.trigger("shown.bs.collapse")
          };
          if (!a.support.transition) return h.call(this);
          var i = a.camelCase(["scroll", g].join("-"));
          this.$element.one("bsTransitionEnd", a.proxy(h, this)).emulateTransitionEnd(d.TRANSITION_DURATION)[g](this.$element[0][i])
        }
      }
    }
  }, d.prototype.hide = function () {
    if (!this.transitioning && this.$element.hasClass("in")) {
      var b = a.Event("hide.bs.collapse");
      if (this.$element.trigger(b), !b.isDefaultPrevented()) {
        var c = this.dimension();
        this.$element[c](this.$element[c]())[0].offsetHeight, this.$element.addClass("collapsing").removeClass("collapse in").attr("aria-expanded", !1), this.$trigger.addClass("collapsed").attr("aria-expanded", !1), this.transitioning = 1;
        var e = function () {
          this.transitioning = 0, this.$element.removeClass("collapsing").addClass("collapse").trigger("hidden.bs.collapse")
        };
        return a.support.transition ? void this.$element[c](0).one("bsTransitionEnd", a.proxy(e, this)).emulateTransitionEnd(d.TRANSITION_DURATION) : e.call(this)
      }
    }
  }, d.prototype.toggle = function () {
    this[this.$element.hasClass("in") ? "hide" : "show"]()
  }, d.prototype.getParent = function () {
    return a(this.options.parent).find('[data-toggle="collapse"][data-parent="' + this.options.parent + '"]').each(a.proxy(function (c, d) {
      var e = a(d);
      this.addAriaAndCollapsedClass(b(e), e)
    }, this)).end()
  }, d.prototype.addAriaAndCollapsedClass = function (a, b) {
    var c = a.hasClass("in");
    a.attr("aria-expanded", c), b.toggleClass("collapsed", !c).attr("aria-expanded", c)
  };
  var e = a.fn.collapse;
  a.fn.collapse = c, a.fn.collapse.Constructor = d, a.fn.collapse.noConflict = function () {
    return a.fn.collapse = e, this
  }, a(document).on("click.bs.collapse.data-api", '[data-toggle="collapse"]', function (d) {
    var e = a(this);
    e.attr("data-target") || d.preventDefault();
    var f = b(e), g = f.data("bs.collapse"), h = g ? "toggle" : e.data();
    c.call(f, h)
  })
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    var c = b.attr("data-target");
    c || (c = b.attr("href"), c = c && /#[A-Za-z]/.test(c) && c.replace(/.*(?=#[^\s]*$)/, ""));
    var d = c && a(c);
    return d && d.length ? d : b.parent()
  }

  function c(c) {
    c && 3 === c.which || (a(e).remove(), a(f).each(function () {
      var d = a(this), e = b(d), f = {relatedTarget: this};
      e.hasClass("open") && (c && "click" == c.type && /input|textarea/i.test(c.target.tagName) && a.contains(e[0], c.target) || (e.trigger(c = a.Event("hide.bs.dropdown", f)), c.isDefaultPrevented() || (d.attr("aria-expanded", "false"), e.removeClass("open").trigger("hidden.bs.dropdown", f))))
    }))
  }

  function d(b) {
    return this.each(function () {
      var c = a(this), d = c.data("bs.dropdown");
      d || c.data("bs.dropdown", d = new g(this)), "string" == typeof b && d[b].call(c)
    })
  }

  var e = ".dropdown-backdrop", f = '[data-toggle="dropdown"]', g = function (b) {
    a(b).on("click.bs.dropdown", this.toggle)
  };
  g.VERSION = "3.3.5", g.prototype.toggle = function (d) {
    var e = a(this);
    if (!e.is(".disabled, :disabled")) {
      var f = b(e), g = f.hasClass("open");
      if (c(), !g) {
        "ontouchstart" in document.documentElement && !f.closest(".navbar-nav").length && a(document.createElement("div")).addClass("dropdown-backdrop").insertAfter(a(this)).on("click", c);
        var h = {relatedTarget: this};
        if (f.trigger(d = a.Event("show.bs.dropdown", h)), d.isDefaultPrevented()) return;
        e.trigger("focus").attr("aria-expanded", "true"), f.toggleClass("open").trigger("shown.bs.dropdown", h)
      }
      return !1
    }
  }, g.prototype.keydown = function (c) {
    if (/(38|40|27|32)/.test(c.which) && !/input|textarea/i.test(c.target.tagName)) {
      var d = a(this);
      if (c.preventDefault(), c.stopPropagation(), !d.is(".disabled, :disabled")) {
        var e = b(d), g = e.hasClass("open");
        if (!g && 27 != c.which || g && 27 == c.which) return 27 == c.which && e.find(f).trigger("focus"), d.trigger("click");
        var h = " li:not(.disabled):visible a", i = e.find(".dropdown-menu" + h);
        if (i.length) {
          var j = i.index(c.target);
          38 == c.which && j > 0 && j--, 40 == c.which && j < i.length - 1 && j++, ~j || (j = 0), i.eq(j).trigger("focus")
        }
      }
    }
  };
  var h = a.fn.dropdown;
  a.fn.dropdown = d, a.fn.dropdown.Constructor = g, a.fn.dropdown.noConflict = function () {
    return a.fn.dropdown = h, this
  }, a(document).on("click.bs.dropdown.data-api", c).on("click.bs.dropdown.data-api", ".dropdown form", function (a) {
    a.stopPropagation()
  }).on("click.bs.dropdown.data-api", f, g.prototype.toggle).on("keydown.bs.dropdown.data-api", f, g.prototype.keydown).on("keydown.bs.dropdown.data-api", ".dropdown-menu", g.prototype.keydown)
}(jQuery), +function (a) {
  "use strict";

  function b(b, d) {
    return this.each(function () {
      var e = a(this), f = e.data("bs.modal"), g = a.extend({}, c.DEFAULTS, e.data(), "object" == typeof b && b);
      f || e.data("bs.modal", f = new c(this, g)), "string" == typeof b ? f[b](d) : g.show && f.show(d)
    })
  }

  var c = function (b, c) {
    this.options = c, this.$body = a(document.body), this.$element = a(b), this.$dialog = this.$element.find(".modal-dialog"), this.$backdrop = null, this.isShown = null, this.originalBodyPad = null, this.scrollbarWidth = 0, this.ignoreBackdropClick = !1, this.options.remote && this.$element.find(".modal-content").load(this.options.remote, a.proxy(function () {
      this.$element.trigger("loaded.bs.modal")
    }, this))
  };
  c.VERSION = "3.3.5", c.TRANSITION_DURATION = 300, c.BACKDROP_TRANSITION_DURATION = 150, c.DEFAULTS = {
    backdrop: !0,
    keyboard: !0,
    show: !0
  }, c.prototype.toggle = function (a) {
    return this.isShown ? this.hide() : this.show(a)
  }, c.prototype.show = function (b) {
    var d = this, e = a.Event("show.bs.modal", {relatedTarget: b});
    this.$element.trigger(e), this.isShown || e.isDefaultPrevented() || (this.isShown = !0, this.checkScrollbar(), this.setScrollbar(), this.$body.addClass("modal-open"), this.escape(), this.resize(), this.$element.on("click.dismiss.bs.modal", '[data-dismiss="modal"]', a.proxy(this.hide, this)), this.$dialog.on("mousedown.dismiss.bs.modal", function () {
      d.$element.one("mouseup.dismiss.bs.modal", function (b) {
        a(b.target).is(d.$element) && (d.ignoreBackdropClick = !0)
      })
    }), this.backdrop(function () {
      var e = a.support.transition && d.$element.hasClass("fade");
      d.$element.parent().length || d.$element.appendTo(d.$body), d.$element.show().scrollTop(0), d.adjustDialog(), e && d.$element[0].offsetWidth, d.$element.addClass("in"), d.enforceFocus();
      var f = a.Event("shown.bs.modal", {relatedTarget: b});
      e ? d.$dialog.one("bsTransitionEnd", function () {
        d.$element.trigger("focus").trigger(f)
      }).emulateTransitionEnd(c.TRANSITION_DURATION) : d.$element.trigger("focus").trigger(f)
    }))
  }, c.prototype.hide = function (b) {
    b && b.preventDefault(), b = a.Event("hide.bs.modal"), this.$element.trigger(b), this.isShown && !b.isDefaultPrevented() && (this.isShown = !1, this.escape(), this.resize(), a(document).off("focusin.bs.modal"), this.$element.removeClass("in").off("click.dismiss.bs.modal").off("mouseup.dismiss.bs.modal"), this.$dialog.off("mousedown.dismiss.bs.modal"), a.support.transition && this.$element.hasClass("fade") ? this.$element.one("bsTransitionEnd", a.proxy(this.hideModal, this)).emulateTransitionEnd(c.TRANSITION_DURATION) : this.hideModal())
  }, c.prototype.enforceFocus = function () {
    a(document).off("focusin.bs.modal").on("focusin.bs.modal", a.proxy(function (a) {
      this.$element[0] === a.target || this.$element.has(a.target).length || this.$element.trigger("focus")
    }, this))
  }, c.prototype.escape = function () {
    this.isShown && this.options.keyboard ? this.$element.on("keydown.dismiss.bs.modal", a.proxy(function (a) {
      27 == a.which && this.hide()
    }, this)) : this.isShown || this.$element.off("keydown.dismiss.bs.modal")
  }, c.prototype.resize = function () {
    this.isShown ? a(window).on("resize.bs.modal", a.proxy(this.handleUpdate, this)) : a(window).off("resize.bs.modal")
  }, c.prototype.hideModal = function () {
    var a = this;
    this.$element.hide(), this.backdrop(function () {
      a.$body.removeClass("modal-open"), a.resetAdjustments(), a.resetScrollbar(), a.$element.trigger("hidden.bs.modal")
    })
  }, c.prototype.removeBackdrop = function () {
    this.$backdrop && this.$backdrop.remove(), this.$backdrop = null
  }, c.prototype.backdrop = function (b) {
    var d = this, e = this.$element.hasClass("fade") ? "fade" : "";
    if (this.isShown && this.options.backdrop) {
      var f = a.support.transition && e;
      if (this.$backdrop = a(document.createElement("div")).addClass("modal-backdrop " + e).appendTo(this.$body), this.$element.on("click.dismiss.bs.modal", a.proxy(function (a) {
        return this.ignoreBackdropClick ? void (this.ignoreBackdropClick = !1) : void (a.target === a.currentTarget && ("static" == this.options.backdrop ? this.$element[0].focus() : this.hide()))
      }, this)), f && this.$backdrop[0].offsetWidth, this.$backdrop.addClass("in"), !b) return;
      f ? this.$backdrop.one("bsTransitionEnd", b).emulateTransitionEnd(c.BACKDROP_TRANSITION_DURATION) : b()
    } else if (!this.isShown && this.$backdrop) {
      this.$backdrop.removeClass("in");
      var g = function () {
        d.removeBackdrop(), b && b()
      };
      a.support.transition && this.$element.hasClass("fade") ? this.$backdrop.one("bsTransitionEnd", g).emulateTransitionEnd(c.BACKDROP_TRANSITION_DURATION) : g()
    } else b && b()
  }, c.prototype.handleUpdate = function () {
    this.adjustDialog()
  }, c.prototype.adjustDialog = function () {
    var a = this.$element[0].scrollHeight > document.documentElement.clientHeight;
    this.$element.css({
      paddingLeft: !this.bodyIsOverflowing && a ? this.scrollbarWidth : "",
      paddingRight: this.bodyIsOverflowing && !a ? this.scrollbarWidth : ""
    })
  }, c.prototype.resetAdjustments = function () {
    this.$element.css({paddingLeft: "", paddingRight: ""})
  }, c.prototype.checkScrollbar = function () {
    var a = window.innerWidth;
    if (!a) {
      var b = document.documentElement.getBoundingClientRect();
      a = b.right - Math.abs(b.left)
    }
    this.bodyIsOverflowing = document.body.clientWidth < a, this.scrollbarWidth = this.measureScrollbar()
  }, c.prototype.setScrollbar = function () {
    var a = parseInt(this.$body.css("padding-right") || 0, 10);
    this.originalBodyPad = document.body.style.paddingRight || "", this.bodyIsOverflowing && this.$body.css("padding-right", a + this.scrollbarWidth)
  }, c.prototype.resetScrollbar = function () {
    this.$body.css("padding-right", this.originalBodyPad)
  }, c.prototype.measureScrollbar = function () {
    var a = document.createElement("div");
    a.className = "modal-scrollbar-measure", this.$body.append(a);
    var b = a.offsetWidth - a.clientWidth;
    return this.$body[0].removeChild(a), b
  };
  var d = a.fn.modal;
  a.fn.modal = b, a.fn.modal.Constructor = c, a.fn.modal.noConflict = function () {
    return a.fn.modal = d, this
  }, a(document).on("click.bs.modal.data-api", '[data-toggle="modal"]', function (c) {
    var d = a(this), e = d.attr("href"), f = a(d.attr("data-target") || e && e.replace(/.*(?=#[^\s]+$)/, "")),
      g = f.data("bs.modal") ? "toggle" : a.extend({remote: !/#/.test(e) && e}, f.data(), d.data());
    d.is("a") && c.preventDefault(), f.one("show.bs.modal", function (a) {
      a.isDefaultPrevented() || f.one("hidden.bs.modal", function () {
        d.is(":visible") && d.trigger("focus")
      })
    }), b.call(f, g, this)
  })
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.tooltip"), f = "object" == typeof b && b;
      (e || !/destroy|hide/.test(b)) && (e || d.data("bs.tooltip", e = new c(this, f)), "string" == typeof b && e[b]())
    })
  }

  var c = function (a, b) {
    this.type = null, this.options = null, this.enabled = null, this.timeout = null, this.hoverState = null, this.$element = null, this.inState = null, this.init("tooltip", a, b)
  };
  c.VERSION = "3.3.5", c.TRANSITION_DURATION = 150, c.DEFAULTS = {
    animation: !0,
    placement: "top",
    selector: !1,
    template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
    trigger: "hover focus",
    title: "",
    delay: 0,
    html: !1,
    container: !1,
    viewport: {selector: "body", padding: 0}
  }, c.prototype.init = function (b, c, d) {
    if (this.enabled = !0, this.type = b, this.$element = a(c), this.options = this.getOptions(d), this.$viewport = this.options.viewport && a(a.isFunction(this.options.viewport) ? this.options.viewport.call(this, this.$element) : this.options.viewport.selector || this.options.viewport), this.inState = {
      click: !1,
      hover: !1,
      focus: !1
    }, this.$element[0] instanceof document.constructor && !this.options.selector) throw new Error("`selector` option must be specified when initializing " + this.type + " on the window.document object!");
    for (var e = this.options.trigger.split(" "), f = e.length; f--;) {
      var g = e[f];
      if ("click" == g) this.$element.on("click." + this.type, this.options.selector, a.proxy(this.toggle, this)); else if ("manual" != g) {
        var h = "hover" == g ? "mouseenter" : "focusin", i = "hover" == g ? "mouseleave" : "focusout";
        this.$element.on(h + "." + this.type, this.options.selector, a.proxy(this.enter, this)), this.$element.on(i + "." + this.type, this.options.selector, a.proxy(this.leave, this))
      }
    }
    this.options.selector ? this._options = a.extend({}, this.options, {
      trigger: "manual",
      selector: ""
    }) : this.fixTitle()
  }, c.prototype.getDefaults = function () {
    return c.DEFAULTS
  }, c.prototype.getOptions = function (b) {
    return b = a.extend({}, this.getDefaults(), this.$element.data(), b), b.delay && "number" == typeof b.delay && (b.delay = {
      show: b.delay,
      hide: b.delay
    }), b
  }, c.prototype.getDelegateOptions = function () {
    var b = {}, c = this.getDefaults();
    return this._options && a.each(this._options, function (a, d) {
      c[a] != d && (b[a] = d)
    }), b
  }, c.prototype.enter = function (b) {
    var c = b instanceof this.constructor ? b : a(b.currentTarget).data("bs." + this.type);
    return c || (c = new this.constructor(b.currentTarget, this.getDelegateOptions()), a(b.currentTarget).data("bs." + this.type, c)), b instanceof a.Event && (c.inState["focusin" == b.type ? "focus" : "hover"] = !0), c.tip().hasClass("in") || "in" == c.hoverState ? void (c.hoverState = "in") : (clearTimeout(c.timeout), c.hoverState = "in", c.options.delay && c.options.delay.show ? void (c.timeout = setTimeout(function () {
      "in" == c.hoverState && c.show()
    }, c.options.delay.show)) : c.show())
  }, c.prototype.isInStateTrue = function () {
    for (var a in this.inState) if (this.inState[a]) return !0;
    return !1
  }, c.prototype.leave = function (b) {
    var c = b instanceof this.constructor ? b : a(b.currentTarget).data("bs." + this.type);
    return c || (c = new this.constructor(b.currentTarget, this.getDelegateOptions()), a(b.currentTarget).data("bs." + this.type, c)), b instanceof a.Event && (c.inState["focusout" == b.type ? "focus" : "hover"] = !1), c.isInStateTrue() ? void 0 : (clearTimeout(c.timeout), c.hoverState = "out", c.options.delay && c.options.delay.hide ? void (c.timeout = setTimeout(function () {
      "out" == c.hoverState && c.hide()
    }, c.options.delay.hide)) : c.hide())
  }, c.prototype.show = function () {
    var b = a.Event("show.bs." + this.type);
    if (this.hasContent() && this.enabled) {
      this.$element.trigger(b);
      var d = a.contains(this.$element[0].ownerDocument.documentElement, this.$element[0]);
      if (b.isDefaultPrevented() || !d) return;
      var e = this, f = this.tip(), g = this.getUID(this.type);
      this.setContent(), f.attr("id", g), this.$element.attr("aria-describedby", g), this.options.animation && f.addClass("fade");
      var h = "function" == typeof this.options.placement ? this.options.placement.call(this, f[0], this.$element[0]) : this.options.placement,
        i = /\s?auto?\s?/i, j = i.test(h);
      j && (h = h.replace(i, "") || "top"), f.detach().css({
        top: 0,
        left: 0,
        display: "block"
      }).addClass(h).data("bs." + this.type, this), this.options.container ? f.appendTo(this.options.container) : f.insertAfter(this.$element), this.$element.trigger("inserted.bs." + this.type);
      var k = this.getPosition(), l = f[0].offsetWidth, m = f[0].offsetHeight;
      if (j) {
        var n = h, o = this.getPosition(this.$viewport);
        h = "bottom" == h && k.bottom + m > o.bottom ? "top" : "top" == h && k.top - m < o.top ? "bottom" : "right" == h && k.right + l > o.width ? "left" : "left" == h && k.left - l < o.left ? "right" : h, f.removeClass(n).addClass(h)
      }
      var p = this.getCalculatedOffset(h, k, l, m);
      this.applyPlacement(p, h);
      var q = function () {
        var a = e.hoverState;
        e.$element.trigger("shown.bs." + e.type), e.hoverState = null, "out" == a && e.leave(e)
      };
      a.support.transition && this.$tip.hasClass("fade") ? f.one("bsTransitionEnd", q).emulateTransitionEnd(c.TRANSITION_DURATION) : q()
    }
  }, c.prototype.applyPlacement = function (b, c) {
    var d = this.tip(), e = d[0].offsetWidth, f = d[0].offsetHeight, g = parseInt(d.css("margin-top"), 10),
      h = parseInt(d.css("margin-left"), 10);
    isNaN(g) && (g = 0), isNaN(h) && (h = 0), b.top += g, b.left += h, a.offset.setOffset(d[0], a.extend({
      using: function (a) {
        d.css({top: Math.round(a.top), left: Math.round(a.left)})
      }
    }, b), 0), d.addClass("in");
    var i = d[0].offsetWidth, j = d[0].offsetHeight;
    "top" == c && j != f && (b.top = b.top + f - j);
    var k = this.getViewportAdjustedDelta(c, b, i, j);
    k.left ? b.left += k.left : b.top += k.top;
    var l = /top|bottom/.test(c), m = l ? 2 * k.left - e + i : 2 * k.top - f + j,
      n = l ? "offsetWidth" : "offsetHeight";
    d.offset(b), this.replaceArrow(m, d[0][n], l)
  }, c.prototype.replaceArrow = function (a, b, c) {
    this.arrow().css(c ? "left" : "top", 50 * (1 - a / b) + "%").css(c ? "top" : "left", "")
  }, c.prototype.setContent = function () {
    var a = this.tip(), b = this.getTitle();
    a.find(".tooltip-inner")[this.options.html ? "html" : "text"](b), a.removeClass("fade in top bottom left right")
  }, c.prototype.hide = function (b) {
    function d() {
      "in" != e.hoverState && f.detach(), e.$element.removeAttr("aria-describedby").trigger("hidden.bs." + e.type), b && b()
    }

    var e = this, f = a(this.$tip), g = a.Event("hide.bs." + this.type);
    return this.$element.trigger(g), g.isDefaultPrevented() ? void 0 : (f.removeClass("in"), a.support.transition && f.hasClass("fade") ? f.one("bsTransitionEnd", d).emulateTransitionEnd(c.TRANSITION_DURATION) : d(), this.hoverState = null, this)
  }, c.prototype.fixTitle = function () {
    var a = this.$element;
    (a.attr("title") || "string" != typeof a.attr("data-original-title")) && a.attr("data-original-title", a.attr("title") || "").attr("title", "")
  }, c.prototype.hasContent = function () {
    return this.getTitle()
  }, c.prototype.getPosition = function (b) {
    b = b || this.$element;
    var c = b[0], d = "BODY" == c.tagName, e = c.getBoundingClientRect();
    null == e.width && (e = a.extend({}, e, {width: e.right - e.left, height: e.bottom - e.top}));
    var f = d ? {top: 0, left: 0} : b.offset(),
      g = {scroll: d ? document.documentElement.scrollTop || document.body.scrollTop : b.scrollTop()},
      h = d ? {width: a(window).width(), height: a(window).height()} : null;
    return a.extend({}, e, g, h, f)
  }, c.prototype.getCalculatedOffset = function (a, b, c, d) {
    return "bottom" == a ? {
      top: b.top + b.height,
      left: b.left + b.width / 2 - c / 2
    } : "top" == a ? {
      top: b.top - d,
      left: b.left + b.width / 2 - c / 2
    } : "left" == a ? {top: b.top + b.height / 2 - d / 2, left: b.left - c} : {
      top: b.top + b.height / 2 - d / 2,
      left: b.left + b.width
    }
  }, c.prototype.getViewportAdjustedDelta = function (a, b, c, d) {
    var e = {top: 0, left: 0};
    if (!this.$viewport) return e;
    var f = this.options.viewport && this.options.viewport.padding || 0, g = this.getPosition(this.$viewport);
    if (/right|left/.test(a)) {
      var h = b.top - f - g.scroll, i = b.top + f - g.scroll + d;
      h < g.top ? e.top = g.top - h : i > g.top + g.height && (e.top = g.top + g.height - i)
    } else {
      var j = b.left - f, k = b.left + f + c;
      j < g.left ? e.left = g.left - j : k > g.right && (e.left = g.left + g.width - k)
    }
    return e
  }, c.prototype.getTitle = function () {
    var a, b = this.$element, c = this.options;
    return a = b.attr("data-original-title") || ("function" == typeof c.title ? c.title.call(b[0]) : c.title)
  }, c.prototype.getUID = function (a) {
    do a += ~~(1e6 * Math.random()); while (document.getElementById(a));
    return a
  }, c.prototype.tip = function () {
    if (!this.$tip && (this.$tip = a(this.options.template), 1 != this.$tip.length)) throw new Error(this.type + " `template` option must consist of exactly 1 top-level element!");
    return this.$tip
  }, c.prototype.arrow = function () {
    return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow")
  }, c.prototype.enable = function () {
    this.enabled = !0
  }, c.prototype.disable = function () {
    this.enabled = !1
  }, c.prototype.toggleEnabled = function () {
    this.enabled = !this.enabled
  }, c.prototype.toggle = function (b) {
    var c = this;
    b && (c = a(b.currentTarget).data("bs." + this.type), c || (c = new this.constructor(b.currentTarget, this.getDelegateOptions()), a(b.currentTarget).data("bs." + this.type, c))), b ? (c.inState.click = !c.inState.click, c.isInStateTrue() ? c.enter(c) : c.leave(c)) : c.tip().hasClass("in") ? c.leave(c) : c.enter(c)
  }, c.prototype.destroy = function () {
    var a = this;
    clearTimeout(this.timeout), this.hide(function () {
      a.$element.off("." + a.type).removeData("bs." + a.type), a.$tip && a.$tip.detach(), a.$tip = null, a.$arrow = null, a.$viewport = null
    })
  };
  var d = a.fn.tooltip;
  a.fn.tooltip = b, a.fn.tooltip.Constructor = c, a.fn.tooltip.noConflict = function () {
    return a.fn.tooltip = d, this
  }
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.popover"), f = "object" == typeof b && b;
      (e || !/destroy|hide/.test(b)) && (e || d.data("bs.popover", e = new c(this, f)), "string" == typeof b && e[b]())
    })
  }

  var c = function (a, b) {
    this.init("popover", a, b)
  };
  if (!a.fn.tooltip) throw new Error("Popover requires tooltip.js");
  c.VERSION = "3.3.5", c.DEFAULTS = a.extend({}, a.fn.tooltip.Constructor.DEFAULTS, {
    placement: "right",
    trigger: "click",
    content: "",
    template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
  }), c.prototype = a.extend({}, a.fn.tooltip.Constructor.prototype), c.prototype.constructor = c, c.prototype.getDefaults = function () {
    return c.DEFAULTS
  }, c.prototype.setContent = function () {
    var a = this.tip(), b = this.getTitle(), c = this.getContent();
    a.find(".popover-title")[this.options.html ? "html" : "text"](b), a.find(".popover-content").children().detach().end()[this.options.html ? "string" == typeof c ? "html" : "append" : "text"](c), a.removeClass("fade top bottom left right in"), a.find(".popover-title").html() || a.find(".popover-title").hide()
  }, c.prototype.hasContent = function () {
    return this.getTitle() || this.getContent()
  }, c.prototype.getContent = function () {
    var a = this.$element, b = this.options;
    return a.attr("data-content") || ("function" == typeof b.content ? b.content.call(a[0]) : b.content)
  }, c.prototype.arrow = function () {
    return this.$arrow = this.$arrow || this.tip().find(".arrow")
  };
  var d = a.fn.popover;
  a.fn.popover = b, a.fn.popover.Constructor = c, a.fn.popover.noConflict = function () {
    return a.fn.popover = d, this
  }
}(jQuery), +function (a) {
  "use strict";

  function b(c, d) {
    this.$body = a(document.body), this.$scrollElement = a(a(c).is(document.body) ? window : c), this.options = a.extend({}, b.DEFAULTS, d), this.selector = (this.options.target || "") + " .nav li > a", this.offsets = [], this.targets = [], this.activeTarget = null, this.scrollHeight = 0, this.$scrollElement.on("scroll.bs.scrollspy", a.proxy(this.process, this)), this.refresh(), this.process()
  }

  function c(c) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.scrollspy"), f = "object" == typeof c && c;
      e || d.data("bs.scrollspy", e = new b(this, f)), "string" == typeof c && e[c]()
    })
  }

  b.VERSION = "3.3.5", b.DEFAULTS = {offset: 10}, b.prototype.getScrollHeight = function () {
    return this.$scrollElement[0].scrollHeight || Math.max(this.$body[0].scrollHeight, document.documentElement.scrollHeight)
  }, b.prototype.refresh = function () {
    var b = this, c = "offset", d = 0;
    this.offsets = [], this.targets = [], this.scrollHeight = this.getScrollHeight(), a.isWindow(this.$scrollElement[0]) || (c = "position", d = this.$scrollElement.scrollTop()), this.$body.find(this.selector).map(function () {
      var b = a(this), e = b.data("target") || b.attr("href"), f = /^#./.test(e) && a(e);
      return f && f.length && f.is(":visible") && [[f[c]().top + d, e]] || null
    }).sort(function (a, b) {
      return a[0] - b[0]
    }).each(function () {
      b.offsets.push(this[0]), b.targets.push(this[1])
    })
  }, b.prototype.process = function () {
    var a, b = this.$scrollElement.scrollTop() + this.options.offset, c = this.getScrollHeight(),
      d = this.options.offset + c - this.$scrollElement.height(), e = this.offsets, f = this.targets,
      g = this.activeTarget;
    if (this.scrollHeight != c && this.refresh(), b >= d) return g != (a = f[f.length - 1]) && this.activate(a);
    if (g && b < e[0]) return this.activeTarget = null, this.clear();
    for (a = e.length; a--;) g != f[a] && b >= e[a] && (void 0 === e[a + 1] || b < e[a + 1]) && this.activate(f[a])
  }, b.prototype.activate = function (b) {
    this.activeTarget = b, this.clear();
    var c = this.selector + '[data-target="' + b + '"],' + this.selector + '[href="' + b + '"]',
      d = a(c).parents("li").addClass("active");
    d.parent(".dropdown-menu").length && (d = d.closest("li.dropdown").addClass("active")),
      d.trigger("activate.bs.scrollspy")
  }, b.prototype.clear = function () {
    a(this.selector).parentsUntil(this.options.target, ".active").removeClass("active")
  };
  var d = a.fn.scrollspy;
  a.fn.scrollspy = c, a.fn.scrollspy.Constructor = b, a.fn.scrollspy.noConflict = function () {
    return a.fn.scrollspy = d, this
  }, a(window).on("load.bs.scrollspy.data-api", function () {
    a('[data-spy="scroll"]').each(function () {
      var b = a(this);
      c.call(b, b.data())
    })
  })
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.tab");
      e || d.data("bs.tab", e = new c(this)), "string" == typeof b && e[b]()
    })
  }

  var c = function (b) {
    this.element = a(b)
  };
  c.VERSION = "3.3.5", c.TRANSITION_DURATION = 150, c.prototype.show = function () {
    var b = this.element, c = b.closest("ul:not(.dropdown-menu)"), d = b.data("target");
    if (d || (d = b.attr("href"), d = d && d.replace(/.*(?=#[^\s]*$)/, "")), !b.parent("li").hasClass("active")) {
      var e = c.find(".active:last a"), f = a.Event("hide.bs.tab", {relatedTarget: b[0]}),
        g = a.Event("show.bs.tab", {relatedTarget: e[0]});
      if (e.trigger(f), b.trigger(g), !g.isDefaultPrevented() && !f.isDefaultPrevented()) {
        var h = a(d);
        this.activate(b.closest("li"), c), this.activate(h, h.parent(), function () {
          e.trigger({type: "hidden.bs.tab", relatedTarget: b[0]}), b.trigger({
            type: "shown.bs.tab",
            relatedTarget: e[0]
          })
        })
      }
    }
  }, c.prototype.activate = function (b, d, e) {
    function f() {
      g.removeClass("active").find("> .dropdown-menu > .active").removeClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded", !1), b.addClass("active").find('[data-toggle="tab"]').attr("aria-expanded", !0), h ? (b[0].offsetWidth, b.addClass("in")) : b.removeClass("fade"), b.parent(".dropdown-menu").length && b.closest("li.dropdown").addClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded", !0), e && e()
    }

    var g = d.find("> .active"),
      h = e && a.support.transition && (g.length && g.hasClass("fade") || !!d.find("> .fade").length);
    g.length && h ? g.one("bsTransitionEnd", f).emulateTransitionEnd(c.TRANSITION_DURATION) : f(), g.removeClass("in")
  };
  var d = a.fn.tab;
  a.fn.tab = b, a.fn.tab.Constructor = c, a.fn.tab.noConflict = function () {
    return a.fn.tab = d, this
  };
  var e = function (c) {
    c.preventDefault(), b.call(a(this), "show")
  };
  a(document).on("click.bs.tab.data-api", '[data-toggle="tab"]', e).on("click.bs.tab.data-api", '[data-toggle="pill"]', e)
}(jQuery), +function (a) {
  "use strict";

  function b(b) {
    return this.each(function () {
      var d = a(this), e = d.data("bs.affix"), f = "object" == typeof b && b;
      e || d.data("bs.affix", e = new c(this, f)), "string" == typeof b && e[b]()
    })
  }

  var c = function (b, d) {
    this.options = a.extend({}, c.DEFAULTS, d), this.$target = a(this.options.target).on("scroll.bs.affix.data-api", a.proxy(this.checkPosition, this)).on("click.bs.affix.data-api", a.proxy(this.checkPositionWithEventLoop, this)), this.$element = a(b), this.affixed = null, this.unpin = null, this.pinnedOffset = null, this.checkPosition()
  };
  c.VERSION = "3.3.5", c.RESET = "affix affix-top affix-bottom", c.DEFAULTS = {
    offset: 0,
    target: window
  }, c.prototype.getState = function (a, b, c, d) {
    var e = this.$target.scrollTop(), f = this.$element.offset(), g = this.$target.height();
    if (null != c && "top" == this.affixed) return c > e ? "top" : !1;
    if ("bottom" == this.affixed) return null != c ? e + this.unpin <= f.top ? !1 : "bottom" : a - d >= e + g ? !1 : "bottom";
    var h = null == this.affixed, i = h ? e : f.top, j = h ? g : b;
    return null != c && c >= e ? "top" : null != d && i + j >= a - d ? "bottom" : !1
  }, c.prototype.getPinnedOffset = function () {
    if (this.pinnedOffset) return this.pinnedOffset;
    this.$element.removeClass(c.RESET).addClass("affix");
    var a = this.$target.scrollTop(), b = this.$element.offset();
    return this.pinnedOffset = b.top - a
  }, c.prototype.checkPositionWithEventLoop = function () {
    setTimeout(a.proxy(this.checkPosition, this), 1)
  }, c.prototype.checkPosition = function () {
    if (this.$element.is(":visible")) {
      var b = this.$element.height(), d = this.options.offset, e = d.top, f = d.bottom,
        g = Math.max(a(document).height(), a(document.body).height());
      "object" != typeof d && (f = e = d), "function" == typeof e && (e = d.top(this.$element)), "function" == typeof f && (f = d.bottom(this.$element));
      var h = this.getState(g, b, e, f);
      if (this.affixed != h) {
        null != this.unpin && this.$element.css("top", "");
        var i = "affix" + (h ? "-" + h : ""), j = a.Event(i + ".bs.affix");
        if (this.$element.trigger(j), j.isDefaultPrevented()) return;
        this.affixed = h, this.unpin = "bottom" == h ? this.getPinnedOffset() : null, this.$element.removeClass(c.RESET).addClass(i).trigger(i.replace("affix", "affixed") + ".bs.affix")
      }
      "bottom" == h && this.$element.offset({top: g - b - f})
    }
  };
  var d = a.fn.affix;
  a.fn.affix = b, a.fn.affix.Constructor = c, a.fn.affix.noConflict = function () {
    return a.fn.affix = d, this
  }, a(window).on("load", function () {
    a('[data-spy="affix"]').each(function () {
      var c = a(this), d = c.data();
      d.offset = d.offset || {}, null != d.offsetBottom && (d.offset.bottom = d.offsetBottom), null != d.offsetTop && (d.offset.top = d.offsetTop), b.call(c, d)
    })
  })
}(jQuery);

/**
 * Swiper 9.1.1
 * Most modern mobile touch slider and framework with hardware accelerated transitions
 * https://swiperjs.com
 *
 * Copyright 2014-2023 Vladimir Kharlampidi
 *
 * Released under the MIT License
 *
 * Released on: March 16, 2023
 */

!function (e, t) {
  "object" == typeof exports && "undefined" != typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define(t) : (e = "undefined" != typeof globalThis ? globalThis : e || self).Swiper = t()
}(this, (function () {
  "use strict";

  function e(e) {
    return null !== e && "object" == typeof e && "constructor" in e && e.constructor === Object
  }

  function t(s, a) {
    void 0 === s && (s = {}), void 0 === a && (a = {}), Object.keys(a).forEach((i => {
      void 0 === s[i] ? s[i] = a[i] : e(a[i]) && e(s[i]) && Object.keys(a[i]).length > 0 && t(s[i], a[i])
    }))
  }

  const s = {
    body: {},
    addEventListener() {
    },
    removeEventListener() {
    },
    activeElement: {
      blur() {
      }, nodeName: ""
    },
    querySelector: () => null,
    querySelectorAll: () => [],
    getElementById: () => null,
    createEvent: () => ({
      initEvent() {
      }
    }),
    createElement: () => ({
      children: [], childNodes: [], style: {}, setAttribute() {
      }, getElementsByTagName: () => []
    }),
    createElementNS: () => ({}),
    importNode: () => null,
    location: {hash: "", host: "", hostname: "", href: "", origin: "", pathname: "", protocol: "", search: ""}
  };

  function a() {
    const e = "undefined" != typeof document ? document : {};
    return t(e, s), e
  }

  const i = {
    document: s,
    navigator: {userAgent: ""},
    location: {hash: "", host: "", hostname: "", href: "", origin: "", pathname: "", protocol: "", search: ""},
    history: {
      replaceState() {
      }, pushState() {
      }, go() {
      }, back() {
      }
    },
    CustomEvent: function () {
      return this
    },
    addEventListener() {
    },
    removeEventListener() {
    },
    getComputedStyle: () => ({getPropertyValue: () => ""}),
    Image() {
    },
    Date() {
    },
    screen: {},
    setTimeout() {
    },
    clearTimeout() {
    },
    matchMedia: () => ({}),
    requestAnimationFrame: e => "undefined" == typeof setTimeout ? (e(), null) : setTimeout(e, 0),
    cancelAnimationFrame(e) {
      "undefined" != typeof setTimeout && clearTimeout(e)
    }
  };

  function r() {
    const e = "undefined" != typeof window ? window : {};
    return t(e, i), e
  }

  function n(e, t) {
    return void 0 === t && (t = 0), setTimeout(e, t)
  }

  function l() {
    return Date.now()
  }

  function o(e, t) {
    void 0 === t && (t = "x");
    const s = r();
    let a, i, n;
    const l = function (e) {
      const t = r();
      let s;
      return t.getComputedStyle && (s = t.getComputedStyle(e, null)), !s && e.currentStyle && (s = e.currentStyle), s || (s = e.style), s
    }(e);
    return s.WebKitCSSMatrix ? (i = l.transform || l.webkitTransform, i.split(",").length > 6 && (i = i.split(", ").map((e => e.replace(",", "."))).join(", ")), n = new s.WebKitCSSMatrix("none" === i ? "" : i)) : (n = l.MozTransform || l.OTransform || l.MsTransform || l.msTransform || l.transform || l.getPropertyValue("transform").replace("translate(", "matrix(1, 0, 0, 1,"), a = n.toString().split(",")), "x" === t && (i = s.WebKitCSSMatrix ? n.m41 : 16 === a.length ? parseFloat(a[12]) : parseFloat(a[4])), "y" === t && (i = s.WebKitCSSMatrix ? n.m42 : 16 === a.length ? parseFloat(a[13]) : parseFloat(a[5])), i || 0
  }

  function d(e) {
    return "object" == typeof e && null !== e && e.constructor && "Object" === Object.prototype.toString.call(e).slice(8, -1)
  }

  function c(e) {
    return "undefined" != typeof window && void 0 !== window.HTMLElement ? e instanceof HTMLElement : e && (1 === e.nodeType || 11 === e.nodeType)
  }

  function p() {
    const e = Object(arguments.length <= 0 ? void 0 : arguments[0]), t = ["__proto__", "constructor", "prototype"];
    for (let s = 1; s < arguments.length; s += 1) {
      const a = s < 0 || arguments.length <= s ? void 0 : arguments[s];
      if (null != a && !c(a)) {
        const s = Object.keys(Object(a)).filter((e => t.indexOf(e) < 0));
        for (let t = 0, i = s.length; t < i; t += 1) {
          const i = s[t], r = Object.getOwnPropertyDescriptor(a, i);
          void 0 !== r && r.enumerable && (d(e[i]) && d(a[i]) ? a[i].__swiper__ ? e[i] = a[i] : p(e[i], a[i]) : !d(e[i]) && d(a[i]) ? (e[i] = {}, a[i].__swiper__ ? e[i] = a[i] : p(e[i], a[i])) : e[i] = a[i])
        }
      }
    }
    return e
  }

  function u(e, t, s) {
    e.style.setProperty(t, s)
  }

  function m(e) {
    let {swiper: t, targetPosition: s, side: a} = e;
    const i = r(), n = -t.translate;
    let l, o = null;
    const d = t.params.speed;
    t.wrapperEl.style.scrollSnapType = "none", i.cancelAnimationFrame(t.cssModeFrameID);
    const c = s > n ? "next" : "prev", p = (e, t) => "next" === c && e >= t || "prev" === c && e <= t, u = () => {
      l = (new Date).getTime(), null === o && (o = l);
      const e = Math.max(Math.min((l - o) / d, 1), 0), r = .5 - Math.cos(e * Math.PI) / 2;
      let c = n + r * (s - n);
      if (p(c, s) && (c = s), t.wrapperEl.scrollTo({[a]: c}), p(c, s)) return t.wrapperEl.style.overflow = "hidden", t.wrapperEl.style.scrollSnapType = "", setTimeout((() => {
        t.wrapperEl.style.overflow = "", t.wrapperEl.scrollTo({[a]: c})
      })), void i.cancelAnimationFrame(t.cssModeFrameID);
      t.cssModeFrameID = i.requestAnimationFrame(u)
    };
    u()
  }

  function h(e) {
    return e.querySelector(".swiper-slide-transform") || e.shadowEl && e.shadowEl.querySelector(".swiper-slide-transform") || e
  }

  function f(e, t) {
    return void 0 === t && (t = ""), [...e.children].filter((e => e.matches(t)))
  }

  function g(e, t) {
    void 0 === t && (t = []);
    const s = document.createElement(e);
    return s.classList.add(...Array.isArray(t) ? t : [t]), s
  }

  function v(e) {
    const t = r(), s = a(), i = e.getBoundingClientRect(), n = s.body, l = e.clientTop || n.clientTop || 0,
      o = e.clientLeft || n.clientLeft || 0, d = e === t ? t.scrollY : e.scrollTop,
      c = e === t ? t.scrollX : e.scrollLeft;
    return {top: i.top + d - l, left: i.left + c - o}
  }

  function w(e, t) {
    return r().getComputedStyle(e, null).getPropertyValue(t)
  }

  function b(e) {
    let t, s = e;
    if (s) {
      for (t = 0; null !== (s = s.previousSibling);) 1 === s.nodeType && (t += 1);
      return t
    }
  }

  function y(e, t) {
    const s = [];
    let a = e.parentElement;
    for (; a;) t ? a.matches(t) && s.push(a) : s.push(a), a = a.parentElement;
    return s
  }

  function E(e, t) {
    t && e.addEventListener("transitionend", (function s(a) {
      a.target === e && (t.call(e, a), e.removeEventListener("transitionend", s))
    }))
  }

  function x(e, t, s) {
    const a = r();
    return s ? e["width" === t ? "offsetWidth" : "offsetHeight"] + parseFloat(a.getComputedStyle(e, null).getPropertyValue("width" === t ? "margin-right" : "margin-top")) + parseFloat(a.getComputedStyle(e, null).getPropertyValue("width" === t ? "margin-left" : "margin-bottom")) : e.offsetWidth
  }

  let S, T, M;

  function C() {
    return S || (S = function () {
      const e = r(), t = a();
      return {
        smoothScroll: t.documentElement && "scrollBehavior" in t.documentElement.style,
        touch: !!("ontouchstart" in e || e.DocumentTouch && t instanceof e.DocumentTouch)
      }
    }()), S
  }

  function P(e) {
    return void 0 === e && (e = {}), T || (T = function (e) {
      let {userAgent: t} = void 0 === e ? {} : e;
      const s = C(), a = r(), i = a.navigator.platform, n = t || a.navigator.userAgent,
        l = {ios: !1, android: !1}, o = a.screen.width, d = a.screen.height,
        c = n.match(/(Android);?[\s\/]+([\d.]+)?/);
      let p = n.match(/(iPad).*OS\s([\d_]+)/);
      const u = n.match(/(iPod)(.*OS\s([\d_]+))?/), m = !p && n.match(/(iPhone\sOS|iOS)\s([\d_]+)/),
        h = "Win32" === i;
      let f = "MacIntel" === i;
      return !p && f && s.touch && ["1024x1366", "1366x1024", "834x1194", "1194x834", "834x1112", "1112x834", "768x1024", "1024x768", "820x1180", "1180x820", "810x1080", "1080x810"].indexOf(`${o}x${d}`) >= 0 && (p = n.match(/(Version)\/([\d.]+)/), p || (p = [0, 1, "13_0_0"]), f = !1), c && !h && (l.os = "android", l.android = !0), (p || m || u) && (l.os = "ios", l.ios = !0), l
    }(e)), T
  }

  function L() {
    return M || (M = function () {
      const e = r();
      let t = !1;

      function s() {
        const t = e.navigator.userAgent.toLowerCase();
        return t.indexOf("safari") >= 0 && t.indexOf("chrome") < 0 && t.indexOf("android") < 0
      }

      if (s()) {
        const s = String(e.navigator.userAgent);
        if (s.includes("Version/")) {
          const [e, a] = s.split("Version/")[1].split(" ")[0].split(".").map((e => Number(e)));
          t = e < 16 || 16 === e && a < 2
        }
      }
      return {
        isSafari: t || s(),
        needPerspectiveFix: t,
        isWebView: /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(e.navigator.userAgent)
      }
    }()), M
  }

  var A = {
    on(e, t, s) {
      const a = this;
      if (!a.eventsListeners || a.destroyed) return a;
      if ("function" != typeof t) return a;
      const i = s ? "unshift" : "push";
      return e.split(" ").forEach((e => {
        a.eventsListeners[e] || (a.eventsListeners[e] = []), a.eventsListeners[e][i](t)
      })), a
    }, once(e, t, s) {
      const a = this;
      if (!a.eventsListeners || a.destroyed) return a;
      if ("function" != typeof t) return a;

      function i() {
        a.off(e, i), i.__emitterProxy && delete i.__emitterProxy;
        for (var s = arguments.length, r = new Array(s), n = 0; n < s; n++) r[n] = arguments[n];
        t.apply(a, r)
      }

      return i.__emitterProxy = t, a.on(e, i, s)
    }, onAny(e, t) {
      const s = this;
      if (!s.eventsListeners || s.destroyed) return s;
      if ("function" != typeof e) return s;
      const a = t ? "unshift" : "push";
      return s.eventsAnyListeners.indexOf(e) < 0 && s.eventsAnyListeners[a](e), s
    }, offAny(e) {
      const t = this;
      if (!t.eventsListeners || t.destroyed) return t;
      if (!t.eventsAnyListeners) return t;
      const s = t.eventsAnyListeners.indexOf(e);
      return s >= 0 && t.eventsAnyListeners.splice(s, 1), t
    }, off(e, t) {
      const s = this;
      return !s.eventsListeners || s.destroyed ? s : s.eventsListeners ? (e.split(" ").forEach((e => {
        void 0 === t ? s.eventsListeners[e] = [] : s.eventsListeners[e] && s.eventsListeners[e].forEach(((a, i) => {
          (a === t || a.__emitterProxy && a.__emitterProxy === t) && s.eventsListeners[e].splice(i, 1)
        }))
      })), s) : s
    }, emit() {
      const e = this;
      if (!e.eventsListeners || e.destroyed) return e;
      if (!e.eventsListeners) return e;
      let t, s, a;
      for (var i = arguments.length, r = new Array(i), n = 0; n < i; n++) r[n] = arguments[n];
      "string" == typeof r[0] || Array.isArray(r[0]) ? (t = r[0], s = r.slice(1, r.length), a = e) : (t = r[0].events, s = r[0].data, a = r[0].context || e), s.unshift(a);
      return (Array.isArray(t) ? t : t.split(" ")).forEach((t => {
        e.eventsAnyListeners && e.eventsAnyListeners.length && e.eventsAnyListeners.forEach((e => {
          e.apply(a, [t, ...s])
        })), e.eventsListeners && e.eventsListeners[t] && e.eventsListeners[t].forEach((e => {
          e.apply(a, s)
        }))
      })), e
    }
  };
  var z = {
    updateSize: function () {
      const e = this;
      let t, s;
      const a = e.el;
      t = void 0 !== e.params.width && null !== e.params.width ? e.params.width : a.clientWidth, s = void 0 !== e.params.height && null !== e.params.height ? e.params.height : a.clientHeight, 0 === t && e.isHorizontal() || 0 === s && e.isVertical() || (t = t - parseInt(w(a, "padding-left") || 0, 10) - parseInt(w(a, "padding-right") || 0, 10), s = s - parseInt(w(a, "padding-top") || 0, 10) - parseInt(w(a, "padding-bottom") || 0, 10), Number.isNaN(t) && (t = 0), Number.isNaN(s) && (s = 0), Object.assign(e, {
        width: t,
        height: s,
        size: e.isHorizontal() ? t : s
      }))
    }, updateSlides: function () {
      const e = this;

      function t(t) {
        return e.isHorizontal() ? t : {
          width: "height",
          "margin-top": "margin-left",
          "margin-bottom ": "margin-right",
          "margin-left": "margin-top",
          "margin-right": "margin-bottom",
          "padding-left": "padding-top",
          "padding-right": "padding-bottom",
          marginRight: "marginBottom"
        }[t]
      }

      function s(e, s) {
        return parseFloat(e.getPropertyValue(t(s)) || 0)
      }

      const a = e.params, {wrapperEl: i, slidesEl: r, size: n, rtlTranslate: l, wrongRTL: o} = e,
        d = e.virtual && a.virtual.enabled, c = d ? e.virtual.slides.length : e.slides.length,
        p = f(r, `.${e.params.slideClass}, swiper-slide`), m = d ? e.virtual.slides.length : p.length;
      let h = [];
      const g = [], v = [];
      let b = a.slidesOffsetBefore;
      "function" == typeof b && (b = a.slidesOffsetBefore.call(e));
      let y = a.slidesOffsetAfter;
      "function" == typeof y && (y = a.slidesOffsetAfter.call(e));
      const E = e.snapGrid.length, S = e.slidesGrid.length;
      let T = a.spaceBetween, M = -b, C = 0, P = 0;
      if (void 0 === n) return;
      "string" == typeof T && T.indexOf("%") >= 0 && (T = parseFloat(T.replace("%", "")) / 100 * n), e.virtualSize = -T, p.forEach((e => {
        l ? e.style.marginLeft = "" : e.style.marginRight = "", e.style.marginBottom = "", e.style.marginTop = ""
      })), a.centeredSlides && a.cssMode && (u(i, "--swiper-centered-offset-before", ""), u(i, "--swiper-centered-offset-after", ""));
      const L = a.grid && a.grid.rows > 1 && e.grid;
      let A;
      L && e.grid.initSlides(m);
      const z = "auto" === a.slidesPerView && a.breakpoints && Object.keys(a.breakpoints).filter((e => void 0 !== a.breakpoints[e].slidesPerView)).length > 0;
      for (let i = 0; i < m; i += 1) {
        let r;
        if (A = 0, p[i] && (r = p[i]), L && e.grid.updateSlide(i, r, m, t), !p[i] || "none" !== w(r, "display")) {
          if ("auto" === a.slidesPerView) {
            z && (p[i].style[t("width")] = "");
            const n = getComputedStyle(r), l = r.style.transform, o = r.style.webkitTransform;
            if (l && (r.style.transform = "none"), o && (r.style.webkitTransform = "none"), a.roundLengths) A = e.isHorizontal() ? x(r, "width", !0) : x(r, "height", !0); else {
              const e = s(n, "width"), t = s(n, "padding-left"), a = s(n, "padding-right"),
                i = s(n, "margin-left"), l = s(n, "margin-right"), o = n.getPropertyValue("box-sizing");
              if (o && "border-box" === o) A = e + i + l; else {
                const {clientWidth: s, offsetWidth: n} = r;
                A = e + t + a + i + l + (n - s)
              }
            }
            l && (r.style.transform = l), o && (r.style.webkitTransform = o), a.roundLengths && (A = Math.floor(A))
          } else A = (n - (a.slidesPerView - 1) * T) / a.slidesPerView, a.roundLengths && (A = Math.floor(A)), p[i] && (p[i].style[t("width")] = `${A}px`);
          p[i] && (p[i].swiperSlideSize = A), v.push(A), a.centeredSlides ? (M = M + A / 2 + C / 2 + T, 0 === C && 0 !== i && (M = M - n / 2 - T), 0 === i && (M = M - n / 2 - T), Math.abs(M) < .001 && (M = 0), a.roundLengths && (M = Math.floor(M)), P % a.slidesPerGroup == 0 && h.push(M), g.push(M)) : (a.roundLengths && (M = Math.floor(M)), (P - Math.min(e.params.slidesPerGroupSkip, P)) % e.params.slidesPerGroup == 0 && h.push(M), g.push(M), M = M + A + T), e.virtualSize += A + T, C = A, P += 1
        }
      }
      if (e.virtualSize = Math.max(e.virtualSize, n) + y, l && o && ("slide" === a.effect || "coverflow" === a.effect) && (i.style.width = `${e.virtualSize + a.spaceBetween}px`), a.setWrapperSize && (i.style[t("width")] = `${e.virtualSize + a.spaceBetween}px`), L && e.grid.updateWrapperSize(A, h, t), !a.centeredSlides) {
        const t = [];
        for (let s = 0; s < h.length; s += 1) {
          let i = h[s];
          a.roundLengths && (i = Math.floor(i)), h[s] <= e.virtualSize - n && t.push(i)
        }
        h = t, Math.floor(e.virtualSize - n) - Math.floor(h[h.length - 1]) > 1 && h.push(e.virtualSize - n)
      }
      if (d && a.loop) {
        const t = v[0] + T;
        if (a.slidesPerGroup > 1) {
          const s = Math.ceil((e.virtual.slidesBefore + e.virtual.slidesAfter) / a.slidesPerGroup),
            i = t * a.slidesPerGroup;
          for (let e = 0; e < s; e += 1) h.push(h[h.length - 1] + i)
        }
        for (let s = 0; s < e.virtual.slidesBefore + e.virtual.slidesAfter; s += 1) 1 === a.slidesPerGroup && h.push(h[h.length - 1] + t), g.push(g[g.length - 1] + t), e.virtualSize += t
      }
      if (0 === h.length && (h = [0]), 0 !== a.spaceBetween) {
        const s = e.isHorizontal() && l ? "marginLeft" : t("marginRight");
        p.filter(((e, t) => !(a.cssMode && !a.loop) || t !== p.length - 1)).forEach((e => {
          e.style[s] = `${T}px`
        }))
      }
      if (a.centeredSlides && a.centeredSlidesBounds) {
        let e = 0;
        v.forEach((t => {
          e += t + (a.spaceBetween ? a.spaceBetween : 0)
        })), e -= a.spaceBetween;
        const t = e - n;
        h = h.map((e => e < 0 ? -b : e > t ? t + y : e))
      }
      if (a.centerInsufficientSlides) {
        let e = 0;
        if (v.forEach((t => {
          e += t + (a.spaceBetween ? a.spaceBetween : 0)
        })), e -= a.spaceBetween, e < n) {
          const t = (n - e) / 2;
          h.forEach(((e, s) => {
            h[s] = e - t
          })), g.forEach(((e, s) => {
            g[s] = e + t
          }))
        }
      }
      if (Object.assign(e, {
        slides: p,
        snapGrid: h,
        slidesGrid: g,
        slidesSizesGrid: v
      }), a.centeredSlides && a.cssMode && !a.centeredSlidesBounds) {
        u(i, "--swiper-centered-offset-before", -h[0] + "px"), u(i, "--swiper-centered-offset-after", e.size / 2 - v[v.length - 1] / 2 + "px");
        const t = -e.snapGrid[0], s = -e.slidesGrid[0];
        e.snapGrid = e.snapGrid.map((e => e + t)), e.slidesGrid = e.slidesGrid.map((e => e + s))
      }
      if (m !== c && e.emit("slidesLengthChange"), h.length !== E && (e.params.watchOverflow && e.checkOverflow(), e.emit("snapGridLengthChange")), g.length !== S && e.emit("slidesGridLengthChange"), a.watchSlidesProgress && e.updateSlidesOffset(), !(d || a.cssMode || "slide" !== a.effect && "fade" !== a.effect)) {
        const t = `${a.containerModifierClass}backface-hidden`, s = e.el.classList.contains(t);
        m <= a.maxBackfaceHiddenSlides ? s || e.el.classList.add(t) : s && e.el.classList.remove(t)
      }
    }, updateAutoHeight: function (e) {
      const t = this, s = [], a = t.virtual && t.params.virtual.enabled;
      let i, r = 0;
      "number" == typeof e ? t.setTransition(e) : !0 === e && t.setTransition(t.params.speed);
      const n = e => a ? t.getSlideIndexByData(e) : t.slides[e];
      if ("auto" !== t.params.slidesPerView && t.params.slidesPerView > 1) if (t.params.centeredSlides) (t.visibleSlides || []).forEach((e => {
        s.push(e)
      })); else for (i = 0; i < Math.ceil(t.params.slidesPerView); i += 1) {
        const e = t.activeIndex + i;
        if (e > t.slides.length && !a) break;
        s.push(n(e))
      } else s.push(n(t.activeIndex));
      for (i = 0; i < s.length; i += 1) if (void 0 !== s[i]) {
        const e = s[i].offsetHeight;
        r = e > r ? e : r
      }
      (r || 0 === r) && (t.wrapperEl.style.height = `${r}px`)
    }, updateSlidesOffset: function () {
      const e = this, t = e.slides,
        s = e.isElement ? e.isHorizontal() ? e.wrapperEl.offsetLeft : e.wrapperEl.offsetTop : 0;
      for (let a = 0; a < t.length; a += 1) t[a].swiperSlideOffset = (e.isHorizontal() ? t[a].offsetLeft : t[a].offsetTop) - s
    }, updateSlidesProgress: function (e) {
      void 0 === e && (e = this && this.translate || 0);
      const t = this, s = t.params, {slides: a, rtlTranslate: i, snapGrid: r} = t;
      if (0 === a.length) return;
      void 0 === a[0].swiperSlideOffset && t.updateSlidesOffset();
      let n = -e;
      i && (n = e), a.forEach((e => {
        e.classList.remove(s.slideVisibleClass)
      })), t.visibleSlidesIndexes = [], t.visibleSlides = [];
      for (let e = 0; e < a.length; e += 1) {
        const l = a[e];
        let o = l.swiperSlideOffset;
        s.cssMode && s.centeredSlides && (o -= a[0].swiperSlideOffset);
        const d = (n + (s.centeredSlides ? t.minTranslate() : 0) - o) / (l.swiperSlideSize + s.spaceBetween),
          c = (n - r[0] + (s.centeredSlides ? t.minTranslate() : 0) - o) / (l.swiperSlideSize + s.spaceBetween),
          p = -(n - o), u = p + t.slidesSizesGrid[e];
        (p >= 0 && p < t.size - 1 || u > 1 && u <= t.size || p <= 0 && u >= t.size) && (t.visibleSlides.push(l), t.visibleSlidesIndexes.push(e), a[e].classList.add(s.slideVisibleClass)), l.progress = i ? -d : d, l.originalProgress = i ? -c : c
      }
    }, updateProgress: function (e) {
      const t = this;
      if (void 0 === e) {
        const s = t.rtlTranslate ? -1 : 1;
        e = t && t.translate && t.translate * s || 0
      }
      const s = t.params, a = t.maxTranslate() - t.minTranslate();
      let {progress: i, isBeginning: r, isEnd: n, progressLoop: l} = t;
      const o = r, d = n;
      if (0 === a) i = 0, r = !0, n = !0; else {
        i = (e - t.minTranslate()) / a;
        const s = Math.abs(e - t.minTranslate()) < 1, l = Math.abs(e - t.maxTranslate()) < 1;
        r = s || i <= 0, n = l || i >= 1, s && (i = 0), l && (i = 1)
      }
      if (s.loop) {
        const s = t.getSlideIndexByData(0), a = t.getSlideIndexByData(t.slides.length - 1), i = t.slidesGrid[s],
          r = t.slidesGrid[a], n = t.slidesGrid[t.slidesGrid.length - 1], o = Math.abs(e);
        l = o >= i ? (o - i) / n : (o + n - r) / n, l > 1 && (l -= 1)
      }
      Object.assign(t, {
        progress: i,
        progressLoop: l,
        isBeginning: r,
        isEnd: n
      }), (s.watchSlidesProgress || s.centeredSlides && s.autoHeight) && t.updateSlidesProgress(e), r && !o && t.emit("reachBeginning toEdge"), n && !d && t.emit("reachEnd toEdge"), (o && !r || d && !n) && t.emit("fromEdge"), t.emit("progress", i)
    }, updateSlidesClasses: function () {
      const e = this, {slides: t, params: s, slidesEl: a, activeIndex: i} = e, r = e.virtual && s.virtual.enabled,
        n = e => f(a, `.${s.slideClass}${e}, swiper-slide${e}`)[0];
      let l;
      if (t.forEach((e => {
        e.classList.remove(s.slideActiveClass, s.slideNextClass, s.slidePrevClass)
      })), r) if (s.loop) {
        let t = i - e.virtual.slidesBefore;
        t < 0 && (t = e.virtual.slides.length + t), t >= e.virtual.slides.length && (t -= e.virtual.slides.length), l = n(`[data-swiper-slide-index="${t}"]`)
      } else l = n(`[data-swiper-slide-index="${i}"]`); else l = t[i];
      if (l) {
        l.classList.add(s.slideActiveClass);
        let e = function (e, t) {
          const s = [];
          for (; e.nextElementSibling;) {
            const a = e.nextElementSibling;
            t ? a.matches(t) && s.push(a) : s.push(a), e = a
          }
          return s
        }(l, `.${s.slideClass}, swiper-slide`)[0];
        s.loop && !e && (e = t[0]), e && e.classList.add(s.slideNextClass);
        let a = function (e, t) {
          const s = [];
          for (; e.previousElementSibling;) {
            const a = e.previousElementSibling;
            t ? a.matches(t) && s.push(a) : s.push(a), e = a
          }
          return s
        }(l, `.${s.slideClass}, swiper-slide`)[0];
        s.loop && 0 === !a && (a = t[t.length - 1]), a && a.classList.add(s.slidePrevClass)
      }
      e.emitSlidesClasses()
    }, updateActiveIndex: function (e) {
      const t = this,
        s = t.rtlTranslate ? t.translate : -t.translate, {
          snapGrid: a,
          params: i,
          activeIndex: r,
          realIndex: n,
          snapIndex: l
        } = t;
      let o, d = e;
      const c = e => {
        let s = e - t.virtual.slidesBefore;
        return s < 0 && (s = t.virtual.slides.length + s), s >= t.virtual.slides.length && (s -= t.virtual.slides.length), s
      };
      if (void 0 === d && (d = function (e) {
        const {slidesGrid: t, params: s} = e, a = e.rtlTranslate ? e.translate : -e.translate;
        let i;
        for (let e = 0; e < t.length; e += 1) void 0 !== t[e + 1] ? a >= t[e] && a < t[e + 1] - (t[e + 1] - t[e]) / 2 ? i = e : a >= t[e] && a < t[e + 1] && (i = e + 1) : a >= t[e] && (i = e);
        return s.normalizeSlideIndex && (i < 0 || void 0 === i) && (i = 0), i
      }(t)), a.indexOf(s) >= 0) o = a.indexOf(s); else {
        const e = Math.min(i.slidesPerGroupSkip, d);
        o = e + Math.floor((d - e) / i.slidesPerGroup)
      }
      if (o >= a.length && (o = a.length - 1), d === r) return o !== l && (t.snapIndex = o, t.emit("snapIndexChange")), void (t.params.loop && t.virtual && t.params.virtual.enabled && (t.realIndex = c(d)));
      let p;
      p = t.virtual && i.virtual.enabled && i.loop ? c(d) : t.slides[d] ? parseInt(t.slides[d].getAttribute("data-swiper-slide-index") || d, 10) : d, Object.assign(t, {
        snapIndex: o,
        realIndex: p,
        previousIndex: r,
        activeIndex: d
      }), t.emit("activeIndexChange"), t.emit("snapIndexChange"), n !== p && t.emit("realIndexChange"), (t.initialized || t.params.runCallbacksOnInit) && t.emit("slideChange")
    }, updateClickedSlide: function (e) {
      const t = this, s = t.params, a = e.closest(`.${s.slideClass}, swiper-slide`);
      let i, r = !1;
      if (a) for (let e = 0; e < t.slides.length; e += 1) if (t.slides[e] === a) {
        r = !0, i = e;
        break
      }
      if (!a || !r) return t.clickedSlide = void 0, void (t.clickedIndex = void 0);
      t.clickedSlide = a, t.virtual && t.params.virtual.enabled ? t.clickedIndex = parseInt(a.getAttribute("data-swiper-slide-index"), 10) : t.clickedIndex = i, s.slideToClickedSlide && void 0 !== t.clickedIndex && t.clickedIndex !== t.activeIndex && t.slideToClickedSlide()
    }
  };
  var $ = {
    getTranslate: function (e) {
      void 0 === e && (e = this.isHorizontal() ? "x" : "y");
      const {params: t, rtlTranslate: s, translate: a, wrapperEl: i} = this;
      if (t.virtualTranslate) return s ? -a : a;
      if (t.cssMode) return a;
      let r = o(i, e);
      return s && (r = -r), r || 0
    }, setTranslate: function (e, t) {
      const s = this, {rtlTranslate: a, params: i, wrapperEl: r, progress: n} = s;
      let l, o = 0, d = 0;
      s.isHorizontal() ? o = a ? -e : e : d = e, i.roundLengths && (o = Math.floor(o), d = Math.floor(d)), i.cssMode ? r[s.isHorizontal() ? "scrollLeft" : "scrollTop"] = s.isHorizontal() ? -o : -d : i.virtualTranslate || (r.style.transform = `translate3d(${o}px, ${d}px, 0px)`), s.previousTranslate = s.translate, s.translate = s.isHorizontal() ? o : d;
      const c = s.maxTranslate() - s.minTranslate();
      l = 0 === c ? 0 : (e - s.minTranslate()) / c, l !== n && s.updateProgress(e), s.emit("setTranslate", s.translate, t)
    }, minTranslate: function () {
      return -this.snapGrid[0]
    }, maxTranslate: function () {
      return -this.snapGrid[this.snapGrid.length - 1]
    }, translateTo: function (e, t, s, a, i) {
      void 0 === e && (e = 0), void 0 === t && (t = this.params.speed), void 0 === s && (s = !0), void 0 === a && (a = !0);
      const r = this, {params: n, wrapperEl: l} = r;
      if (r.animating && n.preventInteractionOnTransition) return !1;
      const o = r.minTranslate(), d = r.maxTranslate();
      let c;
      if (c = a && e > o ? o : a && e < d ? d : e, r.updateProgress(c), n.cssMode) {
        const e = r.isHorizontal();
        if (0 === t) l[e ? "scrollLeft" : "scrollTop"] = -c; else {
          if (!r.support.smoothScroll) return m({
            swiper: r,
            targetPosition: -c,
            side: e ? "left" : "top"
          }), !0;
          l.scrollTo({[e ? "left" : "top"]: -c, behavior: "smooth"})
        }
        return !0
      }
      return 0 === t ? (r.setTransition(0), r.setTranslate(c), s && (r.emit("beforeTransitionStart", t, i), r.emit("transitionEnd"))) : (r.setTransition(t), r.setTranslate(c), s && (r.emit("beforeTransitionStart", t, i), r.emit("transitionStart")), r.animating || (r.animating = !0, r.onTranslateToWrapperTransitionEnd || (r.onTranslateToWrapperTransitionEnd = function (e) {
        r && !r.destroyed && e.target === this && (r.wrapperEl.removeEventListener("transitionend", r.onTranslateToWrapperTransitionEnd), r.onTranslateToWrapperTransitionEnd = null, delete r.onTranslateToWrapperTransitionEnd, s && r.emit("transitionEnd"))
      }), r.wrapperEl.addEventListener("transitionend", r.onTranslateToWrapperTransitionEnd))), !0
    }
  };

  function k(e) {
    let {swiper: t, runCallbacks: s, direction: a, step: i} = e;
    const {activeIndex: r, previousIndex: n} = t;
    let l = a;
    if (l || (l = r > n ? "next" : r < n ? "prev" : "reset"), t.emit(`transition${i}`), s && r !== n) {
      if ("reset" === l) return void t.emit(`slideResetTransition${i}`);
      t.emit(`slideChangeTransition${i}`), "next" === l ? t.emit(`slideNextTransition${i}`) : t.emit(`slidePrevTransition${i}`)
    }
  }

  var I = {
    slideTo: function (e, t, s, a, i) {
      void 0 === e && (e = 0), void 0 === t && (t = this.params.speed), void 0 === s && (s = !0), "string" == typeof e && (e = parseInt(e, 10));
      const r = this;
      let n = e;
      n < 0 && (n = 0);
      const {
        params: l,
        snapGrid: o,
        slidesGrid: d,
        previousIndex: c,
        activeIndex: p,
        rtlTranslate: u,
        wrapperEl: h,
        enabled: f
      } = r;
      if (r.animating && l.preventInteractionOnTransition || !f && !a && !i) return !1;
      const g = Math.min(r.params.slidesPerGroupSkip, n);
      let v = g + Math.floor((n - g) / r.params.slidesPerGroup);
      v >= o.length && (v = o.length - 1);
      const w = -o[v];
      if (l.normalizeSlideIndex) for (let e = 0; e < d.length; e += 1) {
        const t = -Math.floor(100 * w), s = Math.floor(100 * d[e]), a = Math.floor(100 * d[e + 1]);
        void 0 !== d[e + 1] ? t >= s && t < a - (a - s) / 2 ? n = e : t >= s && t < a && (n = e + 1) : t >= s && (n = e)
      }
      if (r.initialized && n !== p) {
        if (!r.allowSlideNext && w < r.translate && w < r.minTranslate()) return !1;
        if (!r.allowSlidePrev && w > r.translate && w > r.maxTranslate() && (p || 0) !== n) return !1
      }
      let b;
      if (n !== (c || 0) && s && r.emit("beforeSlideChangeStart"), r.updateProgress(w), b = n > p ? "next" : n < p ? "prev" : "reset", u && -w === r.translate || !u && w === r.translate) return r.updateActiveIndex(n), l.autoHeight && r.updateAutoHeight(), r.updateSlidesClasses(), "slide" !== l.effect && r.setTranslate(w), "reset" !== b && (r.transitionStart(s, b), r.transitionEnd(s, b)), !1;
      if (l.cssMode) {
        const e = r.isHorizontal(), s = u ? w : -w;
        if (0 === t) {
          const t = r.virtual && r.params.virtual.enabled;
          t && (r.wrapperEl.style.scrollSnapType = "none", r._immediateVirtual = !0), t && !r._cssModeVirtualInitialSet && r.params.initialSlide > 0 ? (r._cssModeVirtualInitialSet = !0, requestAnimationFrame((() => {
            h[e ? "scrollLeft" : "scrollTop"] = s
          }))) : h[e ? "scrollLeft" : "scrollTop"] = s, t && requestAnimationFrame((() => {
            r.wrapperEl.style.scrollSnapType = "", r._immediateVirtual = !1
          }))
        } else {
          if (!r.support.smoothScroll) return m({swiper: r, targetPosition: s, side: e ? "left" : "top"}), !0;
          h.scrollTo({[e ? "left" : "top"]: s, behavior: "smooth"})
        }
        return !0
      }
      return r.setTransition(t), r.setTranslate(w), r.updateActiveIndex(n), r.updateSlidesClasses(), r.emit("beforeTransitionStart", t, a), r.transitionStart(s, b), 0 === t ? r.transitionEnd(s, b) : r.animating || (r.animating = !0, r.onSlideToWrapperTransitionEnd || (r.onSlideToWrapperTransitionEnd = function (e) {
        r && !r.destroyed && e.target === this && (r.wrapperEl.removeEventListener("transitionend", r.onSlideToWrapperTransitionEnd), r.onSlideToWrapperTransitionEnd = null, delete r.onSlideToWrapperTransitionEnd, r.transitionEnd(s, b))
      }), r.wrapperEl.addEventListener("transitionend", r.onSlideToWrapperTransitionEnd)), !0
    }, slideToLoop: function (e, t, s, a) {
      if (void 0 === e && (e = 0), void 0 === t && (t = this.params.speed), void 0 === s && (s = !0), "string" == typeof e) {
        e = parseInt(e, 10)
      }
      const i = this;
      let r = e;
      return i.params.loop && (i.virtual && i.params.virtual.enabled ? r += i.virtual.slidesBefore : r = i.getSlideIndexByData(r)), i.slideTo(r, t, s, a)
    }, slideNext: function (e, t, s) {
      void 0 === e && (e = this.params.speed), void 0 === t && (t = !0);
      const a = this, {enabled: i, params: r, animating: n} = a;
      if (!i) return a;
      let l = r.slidesPerGroup;
      "auto" === r.slidesPerView && 1 === r.slidesPerGroup && r.slidesPerGroupAuto && (l = Math.max(a.slidesPerViewDynamic("current", !0), 1));
      const o = a.activeIndex < r.slidesPerGroupSkip ? 1 : l, d = a.virtual && r.virtual.enabled;
      if (r.loop) {
        if (n && !d && r.loopPreventsSliding) return !1;
        a.loopFix({direction: "next"}), a._clientLeft = a.wrapperEl.clientLeft
      }
      return r.rewind && a.isEnd ? a.slideTo(0, e, t, s) : a.slideTo(a.activeIndex + o, e, t, s)
    }, slidePrev: function (e, t, s) {
      void 0 === e && (e = this.params.speed), void 0 === t && (t = !0);
      const a = this, {params: i, snapGrid: r, slidesGrid: n, rtlTranslate: l, enabled: o, animating: d} = a;
      if (!o) return a;
      const c = a.virtual && i.virtual.enabled;
      if (i.loop) {
        if (d && !c && i.loopPreventsSliding) return !1;
        a.loopFix({direction: "prev"}), a._clientLeft = a.wrapperEl.clientLeft
      }

      function p(e) {
        return e < 0 ? -Math.floor(Math.abs(e)) : Math.floor(e)
      }

      const u = p(l ? a.translate : -a.translate), m = r.map((e => p(e)));
      let h = r[m.indexOf(u) - 1];
      if (void 0 === h && i.cssMode) {
        let e;
        r.forEach(((t, s) => {
          u >= t && (e = s)
        })), void 0 !== e && (h = r[e > 0 ? e - 1 : e])
      }
      let f = 0;
      if (void 0 !== h && (f = n.indexOf(h), f < 0 && (f = a.activeIndex - 1), "auto" === i.slidesPerView && 1 === i.slidesPerGroup && i.slidesPerGroupAuto && (f = f - a.slidesPerViewDynamic("previous", !0) + 1, f = Math.max(f, 0))), i.rewind && a.isBeginning) {
        const i = a.params.virtual && a.params.virtual.enabled && a.virtual ? a.virtual.slides.length - 1 : a.slides.length - 1;
        return a.slideTo(i, e, t, s)
      }
      return a.slideTo(f, e, t, s)
    }, slideReset: function (e, t, s) {
      return void 0 === e && (e = this.params.speed), void 0 === t && (t = !0), this.slideTo(this.activeIndex, e, t, s)
    }, slideToClosest: function (e, t, s, a) {
      void 0 === e && (e = this.params.speed), void 0 === t && (t = !0), void 0 === a && (a = .5);
      const i = this;
      let r = i.activeIndex;
      const n = Math.min(i.params.slidesPerGroupSkip, r), l = n + Math.floor((r - n) / i.params.slidesPerGroup),
        o = i.rtlTranslate ? i.translate : -i.translate;
      if (o >= i.snapGrid[l]) {
        const e = i.snapGrid[l];
        o - e > (i.snapGrid[l + 1] - e) * a && (r += i.params.slidesPerGroup)
      } else {
        const e = i.snapGrid[l - 1];
        o - e <= (i.snapGrid[l] - e) * a && (r -= i.params.slidesPerGroup)
      }
      return r = Math.max(r, 0), r = Math.min(r, i.slidesGrid.length - 1), i.slideTo(r, e, t, s)
    }, slideToClickedSlide: function () {
      const e = this, {params: t, slidesEl: s} = e,
        a = "auto" === t.slidesPerView ? e.slidesPerViewDynamic() : t.slidesPerView;
      let i, r = e.clickedIndex;
      const l = e.isElement ? "swiper-slide" : `.${t.slideClass}`;
      if (t.loop) {
        if (e.animating) return;
        i = parseInt(e.clickedSlide.getAttribute("data-swiper-slide-index"), 10), t.centeredSlides ? r < e.loopedSlides - a / 2 || r > e.slides.length - e.loopedSlides + a / 2 ? (e.loopFix(), r = e.getSlideIndex(f(s, `${l}[data-swiper-slide-index="${i}"]`)[0]), n((() => {
          e.slideTo(r)
        }))) : e.slideTo(r) : r > e.slides.length - a ? (e.loopFix(), r = e.getSlideIndex(f(s, `${l}[data-swiper-slide-index="${i}"]`)[0]), n((() => {
          e.slideTo(r)
        }))) : e.slideTo(r)
      } else e.slideTo(r)
    }
  };
  var O = {
    loopCreate: function (e) {
      const t = this, {params: s, slidesEl: a} = t;
      if (!s.loop || t.virtual && t.params.virtual.enabled) return;
      f(a, `.${s.slideClass}, swiper-slide`).forEach(((e, t) => {
        e.setAttribute("data-swiper-slide-index", t)
      })), t.loopFix({slideRealIndex: e, direction: s.centeredSlides ? void 0 : "next"})
    }, loopFix: function (e) {
      let {
        slideRealIndex: t,
        slideTo: s = !0,
        direction: a,
        setTranslate: i,
        activeSlideIndex: r,
        byController: n,
        byMousewheel: l
      } = void 0 === e ? {} : e;
      const o = this;
      if (!o.params.loop) return;
      o.emit("beforeLoopFix");
      const {slides: d, allowSlidePrev: c, allowSlideNext: p, slidesEl: u, params: m} = o;
      if (o.allowSlidePrev = !0, o.allowSlideNext = !0, o.virtual && m.virtual.enabled) return s && (m.centeredSlides || 0 !== o.snapIndex ? m.centeredSlides && o.snapIndex < m.slidesPerView ? o.slideTo(o.virtual.slides.length + o.snapIndex, 0, !1, !0) : o.snapIndex === o.snapGrid.length - 1 && o.slideTo(o.virtual.slidesBefore, 0, !1, !0) : o.slideTo(o.virtual.slides.length, 0, !1, !0)), o.allowSlidePrev = c, o.allowSlideNext = p, void o.emit("loopFix");
      const h = "auto" === m.slidesPerView ? o.slidesPerViewDynamic() : Math.ceil(parseFloat(m.slidesPerView, 10));
      let f = m.loopedSlides || h;
      f % m.slidesPerGroup != 0 && (f += m.slidesPerGroup - f % m.slidesPerGroup), o.loopedSlides = f;
      const g = [], v = [];
      let w = o.activeIndex;
      void 0 === r ? r = o.getSlideIndex(o.slides.filter((e => e.classList.contains(m.slideActiveClass)))[0]) : w = r;
      const b = "next" === a || !a, y = "prev" === a || !a;
      let E = 0, x = 0;
      if (r < f) {
        E = Math.max(f - r, m.slidesPerGroup);
        for (let e = 0; e < f - r; e += 1) {
          const t = e - Math.floor(e / d.length) * d.length;
          g.push(d.length - t - 1)
        }
      } else if (r > o.slides.length - 2 * f) {
        x = Math.max(r - (o.slides.length - 2 * f), m.slidesPerGroup);
        for (let e = 0; e < x; e += 1) {
          const t = e - Math.floor(e / d.length) * d.length;
          v.push(t)
        }
      }
      if (y && g.forEach((e => {
        u.prepend(o.slides[e])
      })), b && v.forEach((e => {
        u.append(o.slides[e])
      })), o.recalcSlides(), m.watchSlidesProgress && o.updateSlidesOffset(), s) if (g.length > 0 && y) if (void 0 === t) {
        const e = o.slidesGrid[w], t = o.slidesGrid[w + E] - e;
        l ? o.setTranslate(o.translate - t) : (o.slideTo(w + E, 0, !1, !0), i && (o.touches[o.isHorizontal() ? "startX" : "startY"] += t))
      } else i && o.slideToLoop(t, 0, !1, !0); else if (v.length > 0 && b) if (void 0 === t) {
        const e = o.slidesGrid[w], t = o.slidesGrid[w - x] - e;
        l ? o.setTranslate(o.translate - t) : (o.slideTo(w - x, 0, !1, !0), i && (o.touches[o.isHorizontal() ? "startX" : "startY"] += t))
      } else o.slideToLoop(t, 0, !1, !0);
      if (o.allowSlidePrev = c, o.allowSlideNext = p, o.controller && o.controller.control && !n) {
        const e = {
          slideRealIndex: t,
          slideTo: !1,
          direction: a,
          setTranslate: i,
          activeSlideIndex: r,
          byController: !0
        };
        Array.isArray(o.controller.control) ? o.controller.control.forEach((t => {
          !t.destroyed && t.params.loop && t.loopFix(e)
        })) : o.controller.control instanceof o.constructor && o.controller.control.params.loop && o.controller.control.loopFix(e)
      }
      o.emit("loopFix")
    }, loopDestroy: function () {
      const e = this, {params: t, slidesEl: s} = e;
      if (!t.loop || e.virtual && e.params.virtual.enabled) return;
      e.recalcSlides();
      const a = [];
      e.slides.forEach((e => {
        const t = void 0 === e.swiperSlideIndex ? 1 * e.getAttribute("data-swiper-slide-index") : e.swiperSlideIndex;
        a[t] = e
      })), e.slides.forEach((e => {
        e.removeAttribute("data-swiper-slide-index")
      })), a.forEach((e => {
        s.append(e)
      })), e.recalcSlides(), e.slideTo(e.realIndex, 0)
    }
  };

  function D(e) {
    const t = this, s = a(), i = r(), n = t.touchEventsData;
    n.evCache.push(e);
    const {params: o, touches: d, enabled: c} = t;
    if (!c) return;
    if (!o.simulateTouch && "mouse" === e.pointerType) return;
    if (t.animating && o.preventInteractionOnTransition) return;
    !t.animating && o.cssMode && o.loop && t.loopFix();
    let p = e;
    p.originalEvent && (p = p.originalEvent);
    let u = p.target;
    if ("wrapper" === o.touchEventsTarget && !t.wrapperEl.contains(u)) return;
    if ("which" in p && 3 === p.which) return;
    if ("button" in p && p.button > 0) return;
    if (n.isTouched && n.isMoved) return;
    const m = !!o.noSwipingClass && "" !== o.noSwipingClass, h = e.composedPath ? e.composedPath() : e.path;
    m && p.target && p.target.shadowRoot && h && (u = h[0]);
    const f = o.noSwipingSelector ? o.noSwipingSelector : `.${o.noSwipingClass}`,
      g = !(!p.target || !p.target.shadowRoot);
    if (o.noSwiping && (g ? function (e, t) {
      return void 0 === t && (t = this), function t(s) {
        if (!s || s === a() || s === r()) return null;
        s.assignedSlot && (s = s.assignedSlot);
        const i = s.closest(e);
        return i || s.getRootNode ? i || t(s.getRootNode().host) : null
      }(t)
    }(f, u) : u.closest(f))) return void (t.allowClick = !0);
    if (o.swipeHandler && !u.closest(o.swipeHandler)) return;
    d.currentX = p.pageX, d.currentY = p.pageY;
    const v = d.currentX, w = d.currentY, b = o.edgeSwipeDetection || o.iOSEdgeSwipeDetection,
      y = o.edgeSwipeThreshold || o.iOSEdgeSwipeThreshold;
    if (b && (v <= y || v >= i.innerWidth - y)) {
      if ("prevent" !== b) return;
      e.preventDefault()
    }
    Object.assign(n, {
      isTouched: !0,
      isMoved: !1,
      allowTouchCallbacks: !0,
      isScrolling: void 0,
      startMoving: void 0
    }), d.startX = v, d.startY = w, n.touchStartTime = l(), t.allowClick = !0, t.updateSize(), t.swipeDirection = void 0, o.threshold > 0 && (n.allowThresholdMove = !1);
    let E = !0;
    u.matches(n.focusableElements) && (E = !1, "SELECT" === u.nodeName && (n.isTouched = !1)), s.activeElement && s.activeElement.matches(n.focusableElements) && s.activeElement !== u && s.activeElement.blur();
    const x = E && t.allowTouchMove && o.touchStartPreventDefault;
    !o.touchStartForcePreventDefault && !x || u.isContentEditable || p.preventDefault(), t.params.freeMode && t.params.freeMode.enabled && t.freeMode && t.animating && !o.cssMode && t.freeMode.onTouchStart(), t.emit("touchStart", p)
  }

  function G(e) {
    const t = a(), s = this, i = s.touchEventsData, {params: r, touches: n, rtlTranslate: o, enabled: d} = s;
    if (!d) return;
    if (!r.simulateTouch && "mouse" === e.pointerType) return;
    let c = e;
    if (c.originalEvent && (c = c.originalEvent), !i.isTouched) return void (i.startMoving && i.isScrolling && s.emit("touchMoveOpposite", c));
    const p = i.evCache.findIndex((e => e.pointerId === c.pointerId));
    p >= 0 && (i.evCache[p] = c);
    const u = i.evCache.length > 1 ? i.evCache[0] : c, m = u.pageX, h = u.pageY;
    if (c.preventedByNestedSwiper) return n.startX = m, void (n.startY = h);
    if (!s.allowTouchMove) return c.target.matches(i.focusableElements) || (s.allowClick = !1), void (i.isTouched && (Object.assign(n, {
      startX: m,
      startY: h,
      prevX: s.touches.currentX,
      prevY: s.touches.currentY,
      currentX: m,
      currentY: h
    }), i.touchStartTime = l()));
    if (r.touchReleaseOnEdges && !r.loop) if (s.isVertical()) {
      if (h < n.startY && s.translate <= s.maxTranslate() || h > n.startY && s.translate >= s.minTranslate()) return i.isTouched = !1, void (i.isMoved = !1)
    } else if (m < n.startX && s.translate <= s.maxTranslate() || m > n.startX && s.translate >= s.minTranslate()) return;
    if (t.activeElement && c.target === t.activeElement && c.target.matches(i.focusableElements)) return i.isMoved = !0, void (s.allowClick = !1);
    if (i.allowTouchCallbacks && s.emit("touchMove", c), c.targetTouches && c.targetTouches.length > 1) return;
    n.currentX = m, n.currentY = h;
    const f = n.currentX - n.startX, g = n.currentY - n.startY;
    if (s.params.threshold && Math.sqrt(f ** 2 + g ** 2) < s.params.threshold) return;
    if (void 0 === i.isScrolling) {
      let e;
      s.isHorizontal() && n.currentY === n.startY || s.isVertical() && n.currentX === n.startX ? i.isScrolling = !1 : f * f + g * g >= 25 && (e = 180 * Math.atan2(Math.abs(g), Math.abs(f)) / Math.PI, i.isScrolling = s.isHorizontal() ? e > r.touchAngle : 90 - e > r.touchAngle)
    }
    if (i.isScrolling && s.emit("touchMoveOpposite", c), void 0 === i.startMoving && (n.currentX === n.startX && n.currentY === n.startY || (i.startMoving = !0)), i.isScrolling || s.zoom && s.params.zoom && s.params.zoom.enabled && i.evCache.length > 1) return void (i.isTouched = !1);
    if (!i.startMoving) return;
    s.allowClick = !1, !r.cssMode && c.cancelable && c.preventDefault(), r.touchMoveStopPropagation && !r.nested && c.stopPropagation();
    let v = s.isHorizontal() ? f : g, w = s.isHorizontal() ? n.currentX - n.previousX : n.currentY - n.previousY;
    r.oneWayMovement && (v = Math.abs(v) * (o ? 1 : -1), w = Math.abs(w) * (o ? 1 : -1)), n.diff = v, v *= r.touchRatio, o && (v = -v, w = -w);
    const b = s.touchesDirection;
    s.swipeDirection = v > 0 ? "prev" : "next", s.touchesDirection = w > 0 ? "prev" : "next";
    const y = s.params.loop && !r.cssMode;
    if (!i.isMoved) {
      if (y && s.loopFix({direction: s.swipeDirection}), i.startTranslate = s.getTranslate(), s.setTransition(0), s.animating) {
        const e = new window.CustomEvent("transitionend", {bubbles: !0, cancelable: !0});
        s.wrapperEl.dispatchEvent(e)
      }
      i.allowMomentumBounce = !1, !r.grabCursor || !0 !== s.allowSlideNext && !0 !== s.allowSlidePrev || s.setGrabCursor(!0), s.emit("sliderFirstMove", c)
    }
    let E;
    i.isMoved && b !== s.touchesDirection && y && Math.abs(v) >= 1 && (s.loopFix({
      direction: s.swipeDirection,
      setTranslate: !0
    }), E = !0), s.emit("sliderMove", c), i.isMoved = !0, i.currentTranslate = v + i.startTranslate;
    let x = !0, S = r.resistanceRatio;
    if (r.touchReleaseOnEdges && (S = 0), v > 0 ? (y && !E && i.currentTranslate > (r.centeredSlides ? s.minTranslate() - s.size / 2 : s.minTranslate()) && s.loopFix({
      direction: "prev",
      setTranslate: !0,
      activeSlideIndex: 0
    }), i.currentTranslate > s.minTranslate() && (x = !1, r.resistance && (i.currentTranslate = s.minTranslate() - 1 + (-s.minTranslate() + i.startTranslate + v) ** S))) : v < 0 && (y && !E && i.currentTranslate < (r.centeredSlides ? s.maxTranslate() + s.size / 2 : s.maxTranslate()) && s.loopFix({
      direction: "next",
      setTranslate: !0,
      activeSlideIndex: s.slides.length - ("auto" === r.slidesPerView ? s.slidesPerViewDynamic() : Math.ceil(parseFloat(r.slidesPerView, 10)))
    }), i.currentTranslate < s.maxTranslate() && (x = !1, r.resistance && (i.currentTranslate = s.maxTranslate() + 1 - (s.maxTranslate() - i.startTranslate - v) ** S))), x && (c.preventedByNestedSwiper = !0), !s.allowSlideNext && "next" === s.swipeDirection && i.currentTranslate < i.startTranslate && (i.currentTranslate = i.startTranslate), !s.allowSlidePrev && "prev" === s.swipeDirection && i.currentTranslate > i.startTranslate && (i.currentTranslate = i.startTranslate), s.allowSlidePrev || s.allowSlideNext || (i.currentTranslate = i.startTranslate), r.threshold > 0) {
      if (!(Math.abs(v) > r.threshold || i.allowThresholdMove)) return void (i.currentTranslate = i.startTranslate);
      if (!i.allowThresholdMove) return i.allowThresholdMove = !0, n.startX = n.currentX, n.startY = n.currentY, i.currentTranslate = i.startTranslate, void (n.diff = s.isHorizontal() ? n.currentX - n.startX : n.currentY - n.startY)
    }
    r.followFinger && !r.cssMode && ((r.freeMode && r.freeMode.enabled && s.freeMode || r.watchSlidesProgress) && (s.updateActiveIndex(), s.updateSlidesClasses()), s.params.freeMode && r.freeMode.enabled && s.freeMode && s.freeMode.onTouchMove(), s.updateProgress(i.currentTranslate), s.setTranslate(i.currentTranslate))
  }

  function B(e) {
    const t = this, s = t.touchEventsData, a = s.evCache.findIndex((t => t.pointerId === e.pointerId));
    if (a >= 0 && s.evCache.splice(a, 1), ["pointercancel", "pointerout", "pointerleave"].includes(e.type)) {
      if (!("pointercancel" === e.type && (t.browser.isSafari || t.browser.isWebView))) return
    }
    const {params: i, touches: r, rtlTranslate: o, slidesGrid: d, enabled: c} = t;
    if (!c) return;
    if (!i.simulateTouch && "mouse" === e.pointerType) return;
    let p = e;
    if (p.originalEvent && (p = p.originalEvent), s.allowTouchCallbacks && t.emit("touchEnd", p), s.allowTouchCallbacks = !1, !s.isTouched) return s.isMoved && i.grabCursor && t.setGrabCursor(!1), s.isMoved = !1, void (s.startMoving = !1);
    i.grabCursor && s.isMoved && s.isTouched && (!0 === t.allowSlideNext || !0 === t.allowSlidePrev) && t.setGrabCursor(!1);
    const u = l(), m = u - s.touchStartTime;
    if (t.allowClick) {
      const e = p.path || p.composedPath && p.composedPath();
      t.updateClickedSlide(e && e[0] || p.target), t.emit("tap click", p), m < 300 && u - s.lastClickTime < 300 && t.emit("doubleTap doubleClick", p)
    }
    if (s.lastClickTime = l(), n((() => {
      t.destroyed || (t.allowClick = !0)
    })), !s.isTouched || !s.isMoved || !t.swipeDirection || 0 === r.diff || s.currentTranslate === s.startTranslate) return s.isTouched = !1, s.isMoved = !1, void (s.startMoving = !1);
    let h;
    if (s.isTouched = !1, s.isMoved = !1, s.startMoving = !1, h = i.followFinger ? o ? t.translate : -t.translate : -s.currentTranslate, i.cssMode) return;
    if (t.params.freeMode && i.freeMode.enabled) return void t.freeMode.onTouchEnd({currentPos: h});
    let f = 0, g = t.slidesSizesGrid[0];
    for (let e = 0; e < d.length; e += e < i.slidesPerGroupSkip ? 1 : i.slidesPerGroup) {
      const t = e < i.slidesPerGroupSkip - 1 ? 1 : i.slidesPerGroup;
      void 0 !== d[e + t] ? h >= d[e] && h < d[e + t] && (f = e, g = d[e + t] - d[e]) : h >= d[e] && (f = e, g = d[d.length - 1] - d[d.length - 2])
    }
    let v = null, w = null;
    i.rewind && (t.isBeginning ? w = t.params.virtual && t.params.virtual.enabled && t.virtual ? t.virtual.slides.length - 1 : t.slides.length - 1 : t.isEnd && (v = 0));
    const b = (h - d[f]) / g, y = f < i.slidesPerGroupSkip - 1 ? 1 : i.slidesPerGroup;
    if (m > i.longSwipesMs) {
      if (!i.longSwipes) return void t.slideTo(t.activeIndex);
      "next" === t.swipeDirection && (b >= i.longSwipesRatio ? t.slideTo(i.rewind && t.isEnd ? v : f + y) : t.slideTo(f)), "prev" === t.swipeDirection && (b > 1 - i.longSwipesRatio ? t.slideTo(f + y) : null !== w && b < 0 && Math.abs(b) > i.longSwipesRatio ? t.slideTo(w) : t.slideTo(f))
    } else {
      if (!i.shortSwipes) return void t.slideTo(t.activeIndex);
      t.navigation && (p.target === t.navigation.nextEl || p.target === t.navigation.prevEl) ? p.target === t.navigation.nextEl ? t.slideTo(f + y) : t.slideTo(f) : ("next" === t.swipeDirection && t.slideTo(null !== v ? v : f + y), "prev" === t.swipeDirection && t.slideTo(null !== w ? w : f))
    }
  }

  let H;

  function X() {
    const e = this, {params: t, el: s} = e;
    if (s && 0 === s.offsetWidth) return;
    t.breakpoints && e.setBreakpoint();
    const {allowSlideNext: a, allowSlidePrev: i, snapGrid: r} = e, n = e.virtual && e.params.virtual.enabled;
    e.allowSlideNext = !0, e.allowSlidePrev = !0, e.updateSize(), e.updateSlides(), e.updateSlidesClasses();
    const l = n && t.loop;
    !("auto" === t.slidesPerView || t.slidesPerView > 1) || !e.isEnd || e.isBeginning || e.params.centeredSlides || l ? e.params.loop && !n ? e.slideToLoop(e.realIndex, 0, !1, !0) : e.slideTo(e.activeIndex, 0, !1, !0) : e.slideTo(e.slides.length - 1, 0, !1, !0), e.autoplay && e.autoplay.running && e.autoplay.paused && (clearTimeout(H), H = setTimeout((() => {
      e.autoplay && e.autoplay.running && e.autoplay.paused && e.autoplay.resume()
    }), 500)), e.allowSlidePrev = i, e.allowSlideNext = a, e.params.watchOverflow && r !== e.snapGrid && e.checkOverflow()
  }

  function Y(e) {
    const t = this;
    t.enabled && (t.allowClick || (t.params.preventClicks && e.preventDefault(), t.params.preventClicksPropagation && t.animating && (e.stopPropagation(), e.stopImmediatePropagation())))
  }

  function q() {
    const e = this, {wrapperEl: t, rtlTranslate: s, enabled: a} = e;
    if (!a) return;
    let i;
    e.previousTranslate = e.translate, e.isHorizontal() ? e.translate = -t.scrollLeft : e.translate = -t.scrollTop, 0 === e.translate && (e.translate = 0), e.updateActiveIndex(), e.updateSlidesClasses();
    const r = e.maxTranslate() - e.minTranslate();
    i = 0 === r ? 0 : (e.translate - e.minTranslate()) / r, i !== e.progress && e.updateProgress(s ? -e.translate : e.translate), e.emit("setTranslate", e.translate, !1)
  }

  const N = (e, t) => {
    if (!e || e.destroyed || !e.params) return;
    const s = t.closest(e.isElement ? "swiper-slide" : `.${e.params.slideClass}`);
    if (s) {
      const t = s.querySelector(`.${e.params.lazyPreloaderClass}`);
      t && t.remove()
    }
  };

  function R(e) {
    N(this, e.target), this.update()
  }

  let _ = !1;

  function F() {
  }

  const V = (e, t) => {
    const s = a(), {params: i, el: r, wrapperEl: n, device: l} = e, o = !!i.nested,
      d = "on" === t ? "addEventListener" : "removeEventListener", c = t;
    r[d]("pointerdown", e.onTouchStart, {passive: !1}), s[d]("pointermove", e.onTouchMove, {
      passive: !1,
      capture: o
    }), s[d]("pointerup", e.onTouchEnd, {passive: !0}), s[d]("pointercancel", e.onTouchEnd, {passive: !0}), s[d]("pointerout", e.onTouchEnd, {passive: !0}), s[d]("pointerleave", e.onTouchEnd, {passive: !0}), (i.preventClicks || i.preventClicksPropagation) && r[d]("click", e.onClick, !0), i.cssMode && n[d]("scroll", e.onScroll), i.updateOnWindowResize ? e[c](l.ios || l.android ? "resize orientationchange observerUpdate" : "resize observerUpdate", X, !0) : e[c]("observerUpdate", X, !0), r[d]("load", e.onLoad, {capture: !0})
  };
  const W = (e, t) => e.grid && t.grid && t.grid.rows > 1;
  var j = {
    init: !0,
    direction: "horizontal",
    oneWayMovement: !1,
    touchEventsTarget: "wrapper",
    initialSlide: 0,
    speed: 300,
    cssMode: !1,
    updateOnWindowResize: !0,
    resizeObserver: !0,
    nested: !1,
    createElements: !1,
    enabled: !0,
    focusableElements: "input, select, option, textarea, button, video, label",
    width: null,
    height: null,
    preventInteractionOnTransition: !1,
    userAgent: null,
    url: null,
    edgeSwipeDetection: !1,
    edgeSwipeThreshold: 20,
    autoHeight: !1,
    setWrapperSize: !1,
    virtualTranslate: !1,
    effect: "slide",
    breakpoints: void 0,
    breakpointsBase: "window",
    spaceBetween: 0,
    slidesPerView: 1,
    slidesPerGroup: 1,
    slidesPerGroupSkip: 0,
    slidesPerGroupAuto: !1,
    centeredSlides: !1,
    centeredSlidesBounds: !1,
    slidesOffsetBefore: 0,
    slidesOffsetAfter: 0,
    normalizeSlideIndex: !0,
    centerInsufficientSlides: !1,
    watchOverflow: !0,
    roundLengths: !1,
    touchRatio: 1,
    touchAngle: 45,
    simulateTouch: !0,
    shortSwipes: !0,
    longSwipes: !0,
    longSwipesRatio: .5,
    longSwipesMs: 300,
    followFinger: !0,
    allowTouchMove: !0,
    threshold: 5,
    touchMoveStopPropagation: !1,
    touchStartPreventDefault: !0,
    touchStartForcePreventDefault: !1,
    touchReleaseOnEdges: !1,
    uniqueNavElements: !0,
    resistance: !0,
    resistanceRatio: .85,
    watchSlidesProgress: !1,
    grabCursor: !1,
    preventClicks: !0,
    preventClicksPropagation: !0,
    slideToClickedSlide: !1,
    loop: !1,
    loopedSlides: null,
    loopPreventsSliding: !0,
    rewind: !1,
    allowSlidePrev: !0,
    allowSlideNext: !0,
    swipeHandler: null,
    noSwiping: !0,
    noSwipingClass: "swiper-no-swiping",
    noSwipingSelector: null,
    passiveListeners: !0,
    maxBackfaceHiddenSlides: 10,
    containerModifierClass: "swiper-",
    slideClass: "swiper-slide",
    slideActiveClass: "swiper-slide-active",
    slideVisibleClass: "swiper-slide-visible",
    slideNextClass: "swiper-slide-next",
    slidePrevClass: "swiper-slide-prev",
    wrapperClass: "swiper-wrapper",
    lazyPreloaderClass: "swiper-lazy-preloader",
    runCallbacksOnInit: !0,
    _emitClasses: !1
  };

  function U(e, t) {
    return function (s) {
      void 0 === s && (s = {});
      const a = Object.keys(s)[0], i = s[a];
      "object" == typeof i && null !== i ? (["navigation", "pagination", "scrollbar"].indexOf(a) >= 0 && !0 === e[a] && (e[a] = {auto: !0}), a in e && "enabled" in i ? (!0 === e[a] && (e[a] = {enabled: !0}), "object" != typeof e[a] || "enabled" in e[a] || (e[a].enabled = !0), e[a] || (e[a] = {enabled: !1}), p(t, s)) : p(t, s)) : p(t, s)
    }
  }

  const K = {
    eventsEmitter: A, update: z, translate: $, transition: {
      setTransition: function (e, t) {
        const s = this;
        s.params.cssMode || (s.wrapperEl.style.transitionDuration = `${e}ms`), s.emit("setTransition", e, t)
      }, transitionStart: function (e, t) {
        void 0 === e && (e = !0);
        const s = this, {params: a} = s;
        a.cssMode || (a.autoHeight && s.updateAutoHeight(), k({
          swiper: s,
          runCallbacks: e,
          direction: t,
          step: "Start"
        }))
      }, transitionEnd: function (e, t) {
        void 0 === e && (e = !0);
        const s = this, {params: a} = s;
        s.animating = !1, a.cssMode || (s.setTransition(0), k({
          swiper: s,
          runCallbacks: e,
          direction: t,
          step: "End"
        }))
      }
    }, slide: I, loop: O, grabCursor: {
      setGrabCursor: function (e) {
        const t = this;
        if (!t.params.simulateTouch || t.params.watchOverflow && t.isLocked || t.params.cssMode) return;
        const s = "container" === t.params.touchEventsTarget ? t.el : t.wrapperEl;
        t.isElement && (t.__preventObserver__ = !0), s.style.cursor = "move", s.style.cursor = e ? "grabbing" : "grab", t.isElement && requestAnimationFrame((() => {
          t.__preventObserver__ = !1
        }))
      }, unsetGrabCursor: function () {
        const e = this;
        e.params.watchOverflow && e.isLocked || e.params.cssMode || (e.isElement && (e.__preventObserver__ = !0), e["container" === e.params.touchEventsTarget ? "el" : "wrapperEl"].style.cursor = "", e.isElement && requestAnimationFrame((() => {
          e.__preventObserver__ = !1
        })))
      }
    }, events: {
      attachEvents: function () {
        const e = this, t = a(), {params: s} = e;
        e.onTouchStart = D.bind(e), e.onTouchMove = G.bind(e), e.onTouchEnd = B.bind(e), s.cssMode && (e.onScroll = q.bind(e)), e.onClick = Y.bind(e), e.onLoad = R.bind(e), _ || (t.addEventListener("touchstart", F), _ = !0), V(e, "on")
      }, detachEvents: function () {
        V(this, "off")
      }
    }, breakpoints: {
      setBreakpoint: function () {
        const e = this, {realIndex: t, initialized: s, params: a, el: i} = e, r = a.breakpoints;
        if (!r || r && 0 === Object.keys(r).length) return;
        const n = e.getBreakpoint(r, e.params.breakpointsBase, e.el);
        if (!n || e.currentBreakpoint === n) return;
        const l = (n in r ? r[n] : void 0) || e.originalParams, o = W(e, a), d = W(e, l), c = a.enabled;
        o && !d ? (i.classList.remove(`${a.containerModifierClass}grid`, `${a.containerModifierClass}grid-column`), e.emitContainerClasses()) : !o && d && (i.classList.add(`${a.containerModifierClass}grid`), (l.grid.fill && "column" === l.grid.fill || !l.grid.fill && "column" === a.grid.fill) && i.classList.add(`${a.containerModifierClass}grid-column`), e.emitContainerClasses()), ["navigation", "pagination", "scrollbar"].forEach((t => {
          const s = a[t] && a[t].enabled, i = l[t] && l[t].enabled;
          s && !i && e[t].disable(), !s && i && e[t].enable()
        }));
        const u = l.direction && l.direction !== a.direction,
          m = a.loop && (l.slidesPerView !== a.slidesPerView || u);
        u && s && e.changeDirection(), p(e.params, l);
        const h = e.params.enabled;
        Object.assign(e, {
          allowTouchMove: e.params.allowTouchMove,
          allowSlideNext: e.params.allowSlideNext,
          allowSlidePrev: e.params.allowSlidePrev
        }), c && !h ? e.disable() : !c && h && e.enable(), e.currentBreakpoint = n, e.emit("_beforeBreakpoint", l), m && s && (e.loopDestroy(), e.loopCreate(t), e.updateSlides()), e.emit("breakpoint", l)
      }, getBreakpoint: function (e, t, s) {
        if (void 0 === t && (t = "window"), !e || "container" === t && !s) return;
        let a = !1;
        const i = r(), n = "window" === t ? i.innerHeight : s.clientHeight, l = Object.keys(e).map((e => {
          if ("string" == typeof e && 0 === e.indexOf("@")) {
            const t = parseFloat(e.substr(1));
            return {value: n * t, point: e}
          }
          return {value: e, point: e}
        }));
        l.sort(((e, t) => parseInt(e.value, 10) - parseInt(t.value, 10)));
        for (let e = 0; e < l.length; e += 1) {
          const {point: r, value: n} = l[e];
          "window" === t ? i.matchMedia(`(min-width: ${n}px)`).matches && (a = r) : n <= s.clientWidth && (a = r)
        }
        return a || "max"
      }
    }, checkOverflow: {
      checkOverflow: function () {
        const e = this, {isLocked: t, params: s} = e, {slidesOffsetBefore: a} = s;
        if (a) {
          const t = e.slides.length - 1, s = e.slidesGrid[t] + e.slidesSizesGrid[t] + 2 * a;
          e.isLocked = e.size > s
        } else e.isLocked = 1 === e.snapGrid.length;
        !0 === s.allowSlideNext && (e.allowSlideNext = !e.isLocked), !0 === s.allowSlidePrev && (e.allowSlidePrev = !e.isLocked), t && t !== e.isLocked && (e.isEnd = !1), t !== e.isLocked && e.emit(e.isLocked ? "lock" : "unlock")
      }
    }, classes: {
      addClasses: function () {
        const e = this, {classNames: t, params: s, rtl: a, el: i, device: r} = e, n = function (e, t) {
          const s = [];
          return e.forEach((e => {
            "object" == typeof e ? Object.keys(e).forEach((a => {
              e[a] && s.push(t + a)
            })) : "string" == typeof e && s.push(t + e)
          })), s
        }(["initialized", s.direction, {"free-mode": e.params.freeMode && s.freeMode.enabled}, {autoheight: s.autoHeight}, {rtl: a}, {grid: s.grid && s.grid.rows > 1}, {"grid-column": s.grid && s.grid.rows > 1 && "column" === s.grid.fill}, {android: r.android}, {ios: r.ios}, {"css-mode": s.cssMode}, {centered: s.cssMode && s.centeredSlides}, {"watch-progress": s.watchSlidesProgress}], s.containerModifierClass);
        t.push(...n), i.classList.add(...t), e.emitContainerClasses()
      }, removeClasses: function () {
        const {el: e, classNames: t} = this;
        e.classList.remove(...t), this.emitContainerClasses()
      }
    }
  }, Z = {};

  class Q {
    constructor() {
      let e, t;
      for (var s = arguments.length, i = new Array(s), r = 0; r < s; r++) i[r] = arguments[r];
      1 === i.length && i[0].constructor && "Object" === Object.prototype.toString.call(i[0]).slice(8, -1) ? t = i[0] : [e, t] = i, t || (t = {}), t = p({}, t), e && !t.el && (t.el = e);
      const n = a();
      if (t.el && "string" == typeof t.el && n.querySelectorAll(t.el).length > 1) {
        const e = [];
        return n.querySelectorAll(t.el).forEach((s => {
          const a = p({}, t, {el: s});
          e.push(new Q(a))
        })), e
      }
      const o = this;
      o.__swiper__ = !0, o.support = C(), o.device = P({userAgent: t.userAgent}), o.browser = L(), o.eventsListeners = {}, o.eventsAnyListeners = [], o.modules = [...o.__modules__], t.modules && Array.isArray(t.modules) && o.modules.push(...t.modules);
      const d = {};
      o.modules.forEach((e => {
        e({
          params: t,
          swiper: o,
          extendParams: U(t, d),
          on: o.on.bind(o),
          once: o.once.bind(o),
          off: o.off.bind(o),
          emit: o.emit.bind(o)
        })
      }));
      const c = p({}, j, d);
      return o.params = p({}, c, Z, t), o.originalParams = p({}, o.params), o.passedParams = p({}, t), o.params && o.params.on && Object.keys(o.params.on).forEach((e => {
        o.on(e, o.params.on[e])
      })), o.params && o.params.onAny && o.onAny(o.params.onAny), Object.assign(o, {
        enabled: o.params.enabled,
        el: e,
        classNames: [],
        slides: [],
        slidesGrid: [],
        snapGrid: [],
        slidesSizesGrid: [],
        isHorizontal: () => "horizontal" === o.params.direction,
        isVertical: () => "vertical" === o.params.direction,
        activeIndex: 0,
        realIndex: 0,
        isBeginning: !0,
        isEnd: !1,
        translate: 0,
        previousTranslate: 0,
        progress: 0,
        velocity: 0,
        animating: !1,
        allowSlideNext: o.params.allowSlideNext,
        allowSlidePrev: o.params.allowSlidePrev,
        touchEventsData: {
          isTouched: void 0,
          isMoved: void 0,
          allowTouchCallbacks: void 0,
          touchStartTime: void 0,
          isScrolling: void 0,
          currentTranslate: void 0,
          startTranslate: void 0,
          allowThresholdMove: void 0,
          focusableElements: o.params.focusableElements,
          lastClickTime: l(),
          clickTimeout: void 0,
          velocities: [],
          allowMomentumBounce: void 0,
          startMoving: void 0,
          evCache: []
        },
        allowClick: !0,
        allowTouchMove: o.params.allowTouchMove,
        touches: {startX: 0, startY: 0, currentX: 0, currentY: 0, diff: 0},
        imagesToLoad: [],
        imagesLoaded: 0
      }), o.emit("_swiper"), o.params.init && o.init(), o
    }

    getSlideIndex(e) {
      const {slidesEl: t, params: s} = this, a = b(f(t, `.${s.slideClass}, swiper-slide`)[0]);
      return b(e) - a
    }

    getSlideIndexByData(e) {
      return this.getSlideIndex(this.slides.filter((t => 1 * t.getAttribute("data-swiper-slide-index") === e))[0])
    }

    recalcSlides() {
      const {slidesEl: e, params: t} = this;
      this.slides = f(e, `.${t.slideClass}, swiper-slide`)
    }

    enable() {
      const e = this;
      e.enabled || (e.enabled = !0, e.params.grabCursor && e.setGrabCursor(), e.emit("enable"))
    }

    disable() {
      const e = this;
      e.enabled && (e.enabled = !1, e.params.grabCursor && e.unsetGrabCursor(), e.emit("disable"))
    }

    setProgress(e, t) {
      const s = this;
      e = Math.min(Math.max(e, 0), 1);
      const a = s.minTranslate(), i = (s.maxTranslate() - a) * e + a;
      s.translateTo(i, void 0 === t ? 0 : t), s.updateActiveIndex(), s.updateSlidesClasses()
    }

    emitContainerClasses() {
      const e = this;
      if (!e.params._emitClasses || !e.el) return;
      const t = e.el.className.split(" ").filter((t => 0 === t.indexOf("swiper") || 0 === t.indexOf(e.params.containerModifierClass)));
      e.emit("_containerClasses", t.join(" "))
    }

    getSlideClasses(e) {
      const t = this;
      return t.destroyed ? "" : e.className.split(" ").filter((e => 0 === e.indexOf("swiper-slide") || 0 === e.indexOf(t.params.slideClass))).join(" ")
    }

    emitSlidesClasses() {
      const e = this;
      if (!e.params._emitClasses || !e.el) return;
      const t = [];
      e.slides.forEach((s => {
        const a = e.getSlideClasses(s);
        t.push({slideEl: s, classNames: a}), e.emit("_slideClass", s, a)
      })), e.emit("_slideClasses", t)
    }

    slidesPerViewDynamic(e, t) {
      void 0 === e && (e = "current"), void 0 === t && (t = !1);
      const {params: s, slides: a, slidesGrid: i, slidesSizesGrid: r, size: n, activeIndex: l} = this;
      let o = 1;
      if (s.centeredSlides) {
        let e, t = a[l].swiperSlideSize;
        for (let s = l + 1; s < a.length; s += 1) a[s] && !e && (t += a[s].swiperSlideSize, o += 1, t > n && (e = !0));
        for (let s = l - 1; s >= 0; s -= 1) a[s] && !e && (t += a[s].swiperSlideSize, o += 1, t > n && (e = !0))
      } else if ("current" === e) for (let e = l + 1; e < a.length; e += 1) {
        (t ? i[e] + r[e] - i[l] < n : i[e] - i[l] < n) && (o += 1)
      } else for (let e = l - 1; e >= 0; e -= 1) {
        i[l] - i[e] < n && (o += 1)
      }
      return o
    }

    update() {
      const e = this;
      if (!e || e.destroyed) return;
      const {snapGrid: t, params: s} = e;

      function a() {
        const t = e.rtlTranslate ? -1 * e.translate : e.translate,
          s = Math.min(Math.max(t, e.maxTranslate()), e.minTranslate());
        e.setTranslate(s), e.updateActiveIndex(), e.updateSlidesClasses()
      }

      let i;
      s.breakpoints && e.setBreakpoint(), [...e.el.querySelectorAll('[loading="lazy"]')].forEach((t => {
        t.complete && N(e, t)
      })), e.updateSize(), e.updateSlides(), e.updateProgress(), e.updateSlidesClasses(), e.params.freeMode && e.params.freeMode.enabled ? (a(), e.params.autoHeight && e.updateAutoHeight()) : (i = ("auto" === e.params.slidesPerView || e.params.slidesPerView > 1) && e.isEnd && !e.params.centeredSlides ? e.slideTo(e.slides.length - 1, 0, !1, !0) : e.slideTo(e.activeIndex, 0, !1, !0), i || a()), s.watchOverflow && t !== e.snapGrid && e.checkOverflow(), e.emit("update")
    }

    changeDirection(e, t) {
      void 0 === t && (t = !0);
      const s = this, a = s.params.direction;
      return e || (e = "horizontal" === a ? "vertical" : "horizontal"), e === a || "horizontal" !== e && "vertical" !== e || (s.el.classList.remove(`${s.params.containerModifierClass}${a}`), s.el.classList.add(`${s.params.containerModifierClass}${e}`), s.emitContainerClasses(), s.params.direction = e, s.slides.forEach((t => {
        "vertical" === e ? t.style.width = "" : t.style.height = ""
      })), s.emit("changeDirection"), t && s.update()), s
    }

    changeLanguageDirection(e) {
      const t = this;
      t.rtl && "rtl" === e || !t.rtl && "ltr" === e || (t.rtl = "rtl" === e, t.rtlTranslate = "horizontal" === t.params.direction && t.rtl, t.rtl ? (t.el.classList.add(`${t.params.containerModifierClass}rtl`), t.el.dir = "rtl") : (t.el.classList.remove(`${t.params.containerModifierClass}rtl`), t.el.dir = "ltr"), t.update())
    }

    mount(e) {
      const t = this;
      if (t.mounted) return !0;
      let s = e || t.params.el;
      if ("string" == typeof s && (s = document.querySelector(s)), !s) return !1;
      s.swiper = t, s.shadowEl && (t.isElement = !0);
      const a = () => `.${(t.params.wrapperClass || "").trim().split(" ").join(".")}`;
      let i = (() => {
        if (s && s.shadowRoot && s.shadowRoot.querySelector) {
          return s.shadowRoot.querySelector(a())
        }
        return f(s, a())[0]
      })();
      return !i && t.params.createElements && (i = g("div", t.params.wrapperClass), s.append(i), f(s, `.${t.params.slideClass}`).forEach((e => {
        i.append(e)
      }))), Object.assign(t, {
        el: s,
        wrapperEl: i,
        slidesEl: t.isElement ? s : i,
        mounted: !0,
        rtl: "rtl" === s.dir.toLowerCase() || "rtl" === w(s, "direction"),
        rtlTranslate: "horizontal" === t.params.direction && ("rtl" === s.dir.toLowerCase() || "rtl" === w(s, "direction")),
        wrongRTL: "-webkit-box" === w(i, "display")
      }), !0
    }

    init(e) {
      const t = this;
      if (t.initialized) return t;
      return !1 === t.mount(e) || (t.emit("beforeInit"), t.params.breakpoints && t.setBreakpoint(), t.addClasses(), t.updateSize(), t.updateSlides(), t.params.watchOverflow && t.checkOverflow(), t.params.grabCursor && t.enabled && t.setGrabCursor(), t.params.loop && t.virtual && t.params.virtual.enabled ? t.slideTo(t.params.initialSlide + t.virtual.slidesBefore, 0, t.params.runCallbacksOnInit, !1, !0) : t.slideTo(t.params.initialSlide, 0, t.params.runCallbacksOnInit, !1, !0), t.params.loop && t.loopCreate(), t.attachEvents(), [...t.el.querySelectorAll('[loading="lazy"]')].forEach((e => {
        e.complete ? N(t, e) : e.addEventListener("load", (e => {
          N(t, e.target)
        }))
      })), t.initialized = !0, t.emit("init"), t.emit("afterInit")), t
    }

    destroy(e, t) {
      void 0 === e && (e = !0), void 0 === t && (t = !0);
      const s = this, {params: a, el: i, wrapperEl: r, slides: n} = s;
      return void 0 === s.params || s.destroyed || (s.emit("beforeDestroy"), s.initialized = !1, s.detachEvents(), a.loop && s.loopDestroy(), t && (s.removeClasses(), i.removeAttribute("style"), r.removeAttribute("style"), n && n.length && n.forEach((e => {
        e.classList.remove(a.slideVisibleClass, a.slideActiveClass, a.slideNextClass, a.slidePrevClass), e.removeAttribute("style"), e.removeAttribute("data-swiper-slide-index")
      }))), s.emit("destroy"), Object.keys(s.eventsListeners).forEach((e => {
        s.off(e)
      })), !1 !== e && (s.el.swiper = null, function (e) {
        const t = e;
        Object.keys(t).forEach((e => {
          try {
            t[e] = null
          } catch (e) {
          }
          try {
            delete t[e]
          } catch (e) {
          }
        }))
      }(s)), s.destroyed = !0), null
    }

    static extendDefaults(e) {
      p(Z, e)
    }

    static get extendedDefaults() {
      return Z
    }

    static get defaults() {
      return j
    }

    static installModule(e) {
      Q.prototype.__modules__ || (Q.prototype.__modules__ = []);
      const t = Q.prototype.__modules__;
      "function" == typeof e && t.indexOf(e) < 0 && t.push(e)
    }

    static use(e) {
      return Array.isArray(e) ? (e.forEach((e => Q.installModule(e))), Q) : (Q.installModule(e), Q)
    }
  }

  function J(e, t, s, a) {
    return e.params.createElements && Object.keys(a).forEach((i => {
      if (!s[i] && !0 === s.auto) {
        let r = f(e.el, `.${a[i]}`)[0];
        r || (r = g("div", a[i]), r.className = a[i], e.el.append(r)), s[i] = r, t[i] = r
      }
    })), s
  }

  function ee(e) {
    return void 0 === e && (e = ""), `.${e.trim().replace(/([\.:!+\/])/g, "\\$1").replace(/ /g, ".")}`
  }

  function te(e) {
    const t = this, {params: s, slidesEl: a} = t;
    s.loop && t.loopDestroy();
    const i = e => {
      if ("string" == typeof e) {
        const t = document.createElement("div");
        t.innerHTML = e, a.append(t.children[0]), t.innerHTML = ""
      } else a.append(e)
    };
    if ("object" == typeof e && "length" in e) for (let t = 0; t < e.length; t += 1) e[t] && i(e[t]); else i(e);
    t.recalcSlides(), s.loop && t.loopCreate(), s.observer && !t.isElement || t.update()
  }

  function se(e) {
    const t = this, {params: s, activeIndex: a, slidesEl: i} = t;
    s.loop && t.loopDestroy();
    let r = a + 1;
    const n = e => {
      if ("string" == typeof e) {
        const t = document.createElement("div");
        t.innerHTML = e, i.prepend(t.children[0]), t.innerHTML = ""
      } else i.prepend(e)
    };
    if ("object" == typeof e && "length" in e) {
      for (let t = 0; t < e.length; t += 1) e[t] && n(e[t]);
      r = a + e.length
    } else n(e);
    t.recalcSlides(), s.loop && t.loopCreate(), s.observer && !t.isElement || t.update(), t.slideTo(r, 0, !1)
  }

  function ae(e, t) {
    const s = this, {params: a, activeIndex: i, slidesEl: r} = s;
    let n = i;
    a.loop && (n -= s.loopedSlides, s.loopDestroy(), s.recalcSlides());
    const l = s.slides.length;
    if (e <= 0) return void s.prependSlide(t);
    if (e >= l) return void s.appendSlide(t);
    let o = n > e ? n + 1 : n;
    const d = [];
    for (let t = l - 1; t >= e; t -= 1) {
      const e = s.slides[t];
      e.remove(), d.unshift(e)
    }
    if ("object" == typeof t && "length" in t) {
      for (let e = 0; e < t.length; e += 1) t[e] && r.append(t[e]);
      o = n > e ? n + t.length : n
    } else r.append(t);
    for (let e = 0; e < d.length; e += 1) r.append(d[e]);
    s.recalcSlides(), a.loop && s.loopCreate(), a.observer && !s.isElement || s.update(), a.loop ? s.slideTo(o + s.loopedSlides, 0, !1) : s.slideTo(o, 0, !1)
  }

  function ie(e) {
    const t = this, {params: s, activeIndex: a} = t;
    let i = a;
    s.loop && (i -= t.loopedSlides, t.loopDestroy());
    let r, n = i;
    if ("object" == typeof e && "length" in e) {
      for (let s = 0; s < e.length; s += 1) r = e[s], t.slides[r] && t.slides[r].remove(), r < n && (n -= 1);
      n = Math.max(n, 0)
    } else r = e, t.slides[r] && t.slides[r].remove(), r < n && (n -= 1), n = Math.max(n, 0);
    t.recalcSlides(), s.loop && t.loopCreate(), s.observer && !t.isElement || t.update(), s.loop ? t.slideTo(n + t.loopedSlides, 0, !1) : t.slideTo(n, 0, !1)
  }

  function re() {
    const e = this, t = [];
    for (let s = 0; s < e.slides.length; s += 1) t.push(s);
    e.removeSlide(t)
  }

  function ne(e) {
    const {
      effect: t,
      swiper: s,
      on: a,
      setTranslate: i,
      setTransition: r,
      overwriteParams: n,
      perspective: l,
      recreateShadows: o,
      getEffectParams: d
    } = e;
    let c;
    a("beforeInit", (() => {
      if (s.params.effect !== t) return;
      s.classNames.push(`${s.params.containerModifierClass}${t}`), l && l() && s.classNames.push(`${s.params.containerModifierClass}3d`);
      const e = n ? n() : {};
      Object.assign(s.params, e), Object.assign(s.originalParams, e)
    })), a("setTranslate", (() => {
      s.params.effect === t && i()
    })), a("setTransition", ((e, a) => {
      s.params.effect === t && r(a)
    })), a("transitionEnd", (() => {
      if (s.params.effect === t && o) {
        if (!d || !d().slideShadows) return;
        s.slides.forEach((e => {
          e.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach((e => e.remove()))
        })), o()
      }
    })), a("virtualUpdate", (() => {
      s.params.effect === t && (s.slides.length || (c = !0), requestAnimationFrame((() => {
        c && s.slides && s.slides.length && (i(), c = !1)
      })))
    }))
  }

  function le(e, t) {
    const s = h(t);
    return s !== t && (s.style.backfaceVisibility = "hidden", s.style["-webkit-backface-visibility"] = "hidden"), s
  }

  function oe(e) {
    let {swiper: t, duration: s, transformElements: a, allSlides: i} = e;
    const {activeIndex: r} = t;
    if (t.params.virtualTranslate && 0 !== s) {
      let e, s = !1;
      e = i ? a : a.filter((e => {
        const s = e.classList.contains("swiper-slide-transform") ? (e => {
          if (!e.parentElement) return t.slides.filter((t => t.shadowEl && t.shadowEl === e.parentNode))[0];
          return e.parentElement
        })(e) : e;
        return t.getSlideIndex(s) === r
      })), e.forEach((e => {
        E(e, (() => {
          if (s) return;
          if (!t || t.destroyed) return;
          s = !0, t.animating = !1;
          const e = new window.CustomEvent("transitionend", {bubbles: !0, cancelable: !0});
          t.wrapperEl.dispatchEvent(e)
        }))
      }))
    }
  }

  function de(e, t, s) {
    const a = "swiper-slide-shadow" + (s ? `-${s}` : ""), i = h(t);
    let r = i.querySelector(`.${a}`);
    return r || (r = g("div", "swiper-slide-shadow" + (s ? `-${s}` : "")), i.append(r)), r
  }

  Object.keys(K).forEach((e => {
    Object.keys(K[e]).forEach((t => {
      Q.prototype[t] = K[e][t]
    }))
  })), Q.use([function (e) {
    let {swiper: t, on: s, emit: a} = e;
    const i = r();
    let n = null, l = null;
    const o = () => {
      t && !t.destroyed && t.initialized && (a("beforeResize"), a("resize"))
    }, d = () => {
      t && !t.destroyed && t.initialized && a("orientationchange")
    };
    s("init", (() => {
      t.params.resizeObserver && void 0 !== i.ResizeObserver ? t && !t.destroyed && t.initialized && (n = new ResizeObserver((e => {
        l = i.requestAnimationFrame((() => {
          const {width: s, height: a} = t;
          let i = s, r = a;
          e.forEach((e => {
            let {contentBoxSize: s, contentRect: a, target: n} = e;
            n && n !== t.el || (i = a ? a.width : (s[0] || s).inlineSize, r = a ? a.height : (s[0] || s).blockSize)
          })), i === s && r === a || o()
        }))
      })), n.observe(t.el)) : (i.addEventListener("resize", o), i.addEventListener("orientationchange", d))
    })), s("destroy", (() => {
      l && i.cancelAnimationFrame(l), n && n.unobserve && t.el && (n.unobserve(t.el), n = null), i.removeEventListener("resize", o), i.removeEventListener("orientationchange", d)
    }))
  }, function (e) {
    let {swiper: t, extendParams: s, on: a, emit: i} = e;
    const n = [], l = r(), o = function (e, s) {
      void 0 === s && (s = {});
      const a = new (l.MutationObserver || l.WebkitMutationObserver)((e => {
        if (t.__preventObserver__) return;
        if (1 === e.length) return void i("observerUpdate", e[0]);
        const s = function () {
          i("observerUpdate", e[0])
        };
        l.requestAnimationFrame ? l.requestAnimationFrame(s) : l.setTimeout(s, 0)
      }));
      a.observe(e, {
        attributes: void 0 === s.attributes || s.attributes,
        childList: void 0 === s.childList || s.childList,
        characterData: void 0 === s.characterData || s.characterData
      }), n.push(a)
    };
    s({observer: !1, observeParents: !1, observeSlideChildren: !1}), a("init", (() => {
      if (t.params.observer) {
        if (t.params.observeParents) {
          const e = y(t.el);
          for (let t = 0; t < e.length; t += 1) o(e[t])
        }
        o(t.el, {childList: t.params.observeSlideChildren}), o(t.wrapperEl, {attributes: !1})
      }
    })), a("destroy", (() => {
      n.forEach((e => {
        e.disconnect()
      })), n.splice(0, n.length)
    }))
  }]);
  const ce = [function (e) {
    let t, {swiper: s, extendParams: i, on: r, emit: n} = e;
    i({
      virtual: {
        enabled: !1,
        slides: [],
        cache: !0,
        renderSlide: null,
        renderExternal: null,
        renderExternalUpdate: !0,
        addSlidesBefore: 0,
        addSlidesAfter: 0
      }
    });
    const l = a();
    s.virtual = {cache: {}, from: void 0, to: void 0, slides: [], offset: 0, slidesGrid: []};
    const o = l.createElement("div");

    function d(e, t) {
      const a = s.params.virtual;
      if (a.cache && s.virtual.cache[t]) return s.virtual.cache[t];
      let i;
      return a.renderSlide ? (i = a.renderSlide.call(s, e, t), "string" == typeof i && (o.innerHTML = i, i = o.children[0])) : i = s.isElement ? g("swiper-slide") : g("div", s.params.slideClass), i.setAttribute("data-swiper-slide-index", t), a.renderSlide || (i.innerHTML = e), a.cache && (s.virtual.cache[t] = i), i
    }

    function c(e) {
      const {slidesPerView: t, slidesPerGroup: a, centeredSlides: i, loop: r} = s.params, {
        addSlidesBefore: l,
        addSlidesAfter: o
      } = s.params.virtual, {from: c, to: p, slides: u, slidesGrid: m, offset: h} = s.virtual;
      s.params.cssMode || s.updateActiveIndex();
      const g = s.activeIndex || 0;
      let v, w, b;
      v = s.rtlTranslate ? "right" : s.isHorizontal() ? "left" : "top", i ? (w = Math.floor(t / 2) + a + o, b = Math.floor(t / 2) + a + l) : (w = t + (a - 1) + o, b = (r ? t : a) + l);
      let y = g - b, E = g + w;
      r || (y = Math.max(y, 0), E = Math.min(E, u.length - 1));
      let x = (s.slidesGrid[y] || 0) - (s.slidesGrid[0] || 0);

      function S() {
        s.updateSlides(), s.updateProgress(), s.updateSlidesClasses(), n("virtualUpdate")
      }

      if (r && g >= b ? (y -= b, i || (x += s.slidesGrid[0])) : r && g < b && (y = -b, i && (x += s.slidesGrid[0])), Object.assign(s.virtual, {
        from: y,
        to: E,
        offset: x,
        slidesGrid: s.slidesGrid,
        slidesBefore: b,
        slidesAfter: w
      }), c === y && p === E && !e) return s.slidesGrid !== m && x !== h && s.slides.forEach((e => {
        e.style[v] = `${x}px`
      })), s.updateProgress(), void n("virtualUpdate");
      if (s.params.virtual.renderExternal) return s.params.virtual.renderExternal.call(s, {
        offset: x,
        from: y,
        to: E,
        slides: function () {
          const e = [];
          for (let t = y; t <= E; t += 1) e.push(u[t]);
          return e
        }()
      }), void (s.params.virtual.renderExternalUpdate ? S() : n("virtualUpdate"));
      const T = [], M = [], C = e => {
        let t = e;
        return e < 0 ? t = u.length + e : t >= u.length && (t -= u.length), t
      };
      if (e) s.slidesEl.querySelectorAll(`.${s.params.slideClass}, swiper-slide`).forEach((e => {
        e.remove()
      })); else for (let e = c; e <= p; e += 1) if (e < y || e > E) {
        const t = C(e);
        s.slidesEl.querySelectorAll(`.${s.params.slideClass}[data-swiper-slide-index="${t}"], swiper-slide[data-swiper-slide-index="${t}"]`).forEach((e => {
          e.remove()
        }))
      }
      const P = r ? -u.length : 0, L = r ? 2 * u.length : u.length;
      for (let t = P; t < L; t += 1) if (t >= y && t <= E) {
        const s = C(t);
        void 0 === p || e ? M.push(s) : (t > p && M.push(s), t < c && T.push(s))
      }
      if (M.forEach((e => {
        s.slidesEl.append(d(u[e], e))
      })), r) for (let e = T.length - 1; e >= 0; e -= 1) {
        const t = T[e];
        s.slidesEl.prepend(d(u[t], t))
      } else T.sort(((e, t) => t - e)), T.forEach((e => {
        s.slidesEl.prepend(d(u[e], e))
      }));
      f(s.slidesEl, ".swiper-slide, swiper-slide").forEach((e => {
        e.style[v] = `${x}px`
      })), S()
    }

    r("beforeInit", (() => {
      if (!s.params.virtual.enabled) return;
      let e;
      if (void 0 === s.passedParams.virtual.slides) {
        const t = [...s.slidesEl.children].filter((e => e.matches(`.${s.params.slideClass}, swiper-slide`)));
        t && t.length && (s.virtual.slides = [...t], e = !0, t.forEach(((e, t) => {
          e.setAttribute("data-swiper-slide-index", t), s.virtual.cache[t] = e, e.remove()
        })))
      }
      e || (s.virtual.slides = s.params.virtual.slides), s.classNames.push(`${s.params.containerModifierClass}virtual`), s.params.watchSlidesProgress = !0, s.originalParams.watchSlidesProgress = !0, s.params.initialSlide || c()
    })), r("setTranslate", (() => {
      s.params.virtual.enabled && (s.params.cssMode && !s._immediateVirtual ? (clearTimeout(t), t = setTimeout((() => {
        c()
      }), 100)) : c())
    })), r("init update resize", (() => {
      s.params.virtual.enabled && s.params.cssMode && u(s.wrapperEl, "--swiper-virtual-size", `${s.virtualSize}px`)
    })), Object.assign(s.virtual, {
      appendSlide: function (e) {
        if ("object" == typeof e && "length" in e) for (let t = 0; t < e.length; t += 1) e[t] && s.virtual.slides.push(e[t]); else s.virtual.slides.push(e);
        c(!0)
      }, prependSlide: function (e) {
        const t = s.activeIndex;
        let a = t + 1, i = 1;
        if (Array.isArray(e)) {
          for (let t = 0; t < e.length; t += 1) e[t] && s.virtual.slides.unshift(e[t]);
          a = t + e.length, i = e.length
        } else s.virtual.slides.unshift(e);
        if (s.params.virtual.cache) {
          const e = s.virtual.cache, t = {};
          Object.keys(e).forEach((s => {
            const a = e[s], r = a.getAttribute("data-swiper-slide-index");
            r && a.setAttribute("data-swiper-slide-index", parseInt(r, 10) + i), t[parseInt(s, 10) + i] = a
          })), s.virtual.cache = t
        }
        c(!0), s.slideTo(a, 0)
      }, removeSlide: function (e) {
        if (null == e) return;
        let t = s.activeIndex;
        if (Array.isArray(e)) for (let a = e.length - 1; a >= 0; a -= 1) s.virtual.slides.splice(e[a], 1), s.params.virtual.cache && delete s.virtual.cache[e[a]], e[a] < t && (t -= 1), t = Math.max(t, 0); else s.virtual.slides.splice(e, 1), s.params.virtual.cache && delete s.virtual.cache[e], e < t && (t -= 1), t = Math.max(t, 0);
        c(!0), s.slideTo(t, 0)
      }, removeAllSlides: function () {
        s.virtual.slides = [], s.params.virtual.cache && (s.virtual.cache = {}), c(!0), s.slideTo(0, 0)
      }, update: c
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: i, emit: n} = e;
    const l = a(), o = r();

    function d(e) {
      if (!t.enabled) return;
      const {rtlTranslate: s} = t;
      let a = e;
      a.originalEvent && (a = a.originalEvent);
      const i = a.keyCode || a.charCode, r = t.params.keyboard.pageUpDown, d = r && 33 === i, c = r && 34 === i,
        p = 37 === i, u = 39 === i, m = 38 === i, h = 40 === i;
      if (!t.allowSlideNext && (t.isHorizontal() && u || t.isVertical() && h || c)) return !1;
      if (!t.allowSlidePrev && (t.isHorizontal() && p || t.isVertical() && m || d)) return !1;
      if (!(a.shiftKey || a.altKey || a.ctrlKey || a.metaKey || l.activeElement && l.activeElement.nodeName && ("input" === l.activeElement.nodeName.toLowerCase() || "textarea" === l.activeElement.nodeName.toLowerCase()))) {
        if (t.params.keyboard.onlyInViewport && (d || c || p || u || m || h)) {
          let e = !1;
          if (y(t.el, `.${t.params.slideClass}, swiper-slide`).length > 0 && 0 === y(t.el, `.${t.params.slideActiveClass}`).length) return;
          const a = t.el, i = a.clientWidth, r = a.clientHeight, n = o.innerWidth, l = o.innerHeight,
            d = v(a);
          s && (d.left -= a.scrollLeft);
          const c = [[d.left, d.top], [d.left + i, d.top], [d.left, d.top + r], [d.left + i, d.top + r]];
          for (let t = 0; t < c.length; t += 1) {
            const s = c[t];
            if (s[0] >= 0 && s[0] <= n && s[1] >= 0 && s[1] <= l) {
              if (0 === s[0] && 0 === s[1]) continue;
              e = !0
            }
          }
          if (!e) return
        }
        t.isHorizontal() ? ((d || c || p || u) && (a.preventDefault ? a.preventDefault() : a.returnValue = !1), ((c || u) && !s || (d || p) && s) && t.slideNext(), ((d || p) && !s || (c || u) && s) && t.slidePrev()) : ((d || c || m || h) && (a.preventDefault ? a.preventDefault() : a.returnValue = !1), (c || h) && t.slideNext(), (d || m) && t.slidePrev()), n("keyPress", i)
      }
    }

    function c() {
      t.keyboard.enabled || (l.addEventListener("keydown", d), t.keyboard.enabled = !0)
    }

    function p() {
      t.keyboard.enabled && (l.removeEventListener("keydown", d), t.keyboard.enabled = !1)
    }

    t.keyboard = {enabled: !1}, s({keyboard: {enabled: !1, onlyInViewport: !0, pageUpDown: !0}}), i("init", (() => {
      t.params.keyboard.enabled && c()
    })), i("destroy", (() => {
      t.keyboard.enabled && p()
    })), Object.assign(t.keyboard, {enable: c, disable: p})
  }, function (e) {
    let {swiper: t, extendParams: s, on: a, emit: i} = e;
    const o = r();
    let d;
    s({
      mousewheel: {
        enabled: !1,
        releaseOnEdges: !1,
        invert: !1,
        forceToAxis: !1,
        sensitivity: 1,
        eventsTarget: "container",
        thresholdDelta: null,
        thresholdTime: null
      }
    }), t.mousewheel = {enabled: !1};
    let c, p = l();
    const u = [];

    function m() {
      t.enabled && (t.mouseEntered = !0)
    }

    function h() {
      t.enabled && (t.mouseEntered = !1)
    }

    function f(e) {
      return !(t.params.mousewheel.thresholdDelta && e.delta < t.params.mousewheel.thresholdDelta) && (!(t.params.mousewheel.thresholdTime && l() - p < t.params.mousewheel.thresholdTime) && (e.delta >= 6 && l() - p < 60 || (e.direction < 0 ? t.isEnd && !t.params.loop || t.animating || (t.slideNext(), i("scroll", e.raw)) : t.isBeginning && !t.params.loop || t.animating || (t.slidePrev(), i("scroll", e.raw)), p = (new o.Date).getTime(), !1)))
    }

    function g(e) {
      let s = e, a = !0;
      if (!t.enabled) return;
      const r = t.params.mousewheel;
      t.params.cssMode && s.preventDefault();
      let o = t.el;
      "container" !== t.params.mousewheel.eventsTarget && (o = document.querySelector(t.params.mousewheel.eventsTarget));
      const p = o && o.contains(s.target);
      if (!t.mouseEntered && !p && !r.releaseOnEdges) return !0;
      s.originalEvent && (s = s.originalEvent);
      let m = 0;
      const h = t.rtlTranslate ? -1 : 1, g = function (e) {
        let t = 0, s = 0, a = 0, i = 0;
        return "detail" in e && (s = e.detail), "wheelDelta" in e && (s = -e.wheelDelta / 120), "wheelDeltaY" in e && (s = -e.wheelDeltaY / 120), "wheelDeltaX" in e && (t = -e.wheelDeltaX / 120), "axis" in e && e.axis === e.HORIZONTAL_AXIS && (t = s, s = 0), a = 10 * t, i = 10 * s, "deltaY" in e && (i = e.deltaY), "deltaX" in e && (a = e.deltaX), e.shiftKey && !a && (a = i, i = 0), (a || i) && e.deltaMode && (1 === e.deltaMode ? (a *= 40, i *= 40) : (a *= 800, i *= 800)), a && !t && (t = a < 1 ? -1 : 1), i && !s && (s = i < 1 ? -1 : 1), {
          spinX: t,
          spinY: s,
          pixelX: a,
          pixelY: i
        }
      }(s);
      if (r.forceToAxis) if (t.isHorizontal()) {
        if (!(Math.abs(g.pixelX) > Math.abs(g.pixelY))) return !0;
        m = -g.pixelX * h
      } else {
        if (!(Math.abs(g.pixelY) > Math.abs(g.pixelX))) return !0;
        m = -g.pixelY
      } else m = Math.abs(g.pixelX) > Math.abs(g.pixelY) ? -g.pixelX * h : -g.pixelY;
      if (0 === m) return !0;
      r.invert && (m = -m);
      let v = t.getTranslate() + m * r.sensitivity;
      if (v >= t.minTranslate() && (v = t.minTranslate()), v <= t.maxTranslate() && (v = t.maxTranslate()), a = !!t.params.loop || !(v === t.minTranslate() || v === t.maxTranslate()), a && t.params.nested && s.stopPropagation(), t.params.freeMode && t.params.freeMode.enabled) {
        const e = {time: l(), delta: Math.abs(m), direction: Math.sign(m)},
          a = c && e.time < c.time + 500 && e.delta <= c.delta && e.direction === c.direction;
        if (!a) {
          c = void 0;
          let l = t.getTranslate() + m * r.sensitivity;
          const o = t.isBeginning, p = t.isEnd;
          if (l >= t.minTranslate() && (l = t.minTranslate()), l <= t.maxTranslate() && (l = t.maxTranslate()), t.setTransition(0), t.setTranslate(l), t.updateProgress(), t.updateActiveIndex(), t.updateSlidesClasses(), (!o && t.isBeginning || !p && t.isEnd) && t.updateSlidesClasses(), t.params.loop && t.loopFix({
            direction: e.direction < 0 ? "next" : "prev",
            byMousewheel: !0
          }), t.params.freeMode.sticky) {
            clearTimeout(d), d = void 0, u.length >= 15 && u.shift();
            const s = u.length ? u[u.length - 1] : void 0, a = u[0];
            if (u.push(e), s && (e.delta > s.delta || e.direction !== s.direction)) u.splice(0); else if (u.length >= 15 && e.time - a.time < 500 && a.delta - e.delta >= 1 && e.delta <= 6) {
              const s = m > 0 ? .8 : .2;
              c = e, u.splice(0), d = n((() => {
                t.slideToClosest(t.params.speed, !0, void 0, s)
              }), 0)
            }
            d || (d = n((() => {
              c = e, u.splice(0), t.slideToClosest(t.params.speed, !0, void 0, .5)
            }), 500))
          }
          if (a || i("scroll", s), t.params.autoplay && t.params.autoplayDisableOnInteraction && t.autoplay.stop(), l === t.minTranslate() || l === t.maxTranslate()) return !0
        }
      } else {
        const s = {time: l(), delta: Math.abs(m), direction: Math.sign(m), raw: e};
        u.length >= 2 && u.shift();
        const a = u.length ? u[u.length - 1] : void 0;
        if (u.push(s), a ? (s.direction !== a.direction || s.delta > a.delta || s.time > a.time + 150) && f(s) : f(s), function (e) {
          const s = t.params.mousewheel;
          if (e.direction < 0) {
            if (t.isEnd && !t.params.loop && s.releaseOnEdges) return !0
          } else if (t.isBeginning && !t.params.loop && s.releaseOnEdges) return !0;
          return !1
        }(s)) return !0
      }
      return s.preventDefault ? s.preventDefault() : s.returnValue = !1, !1
    }

    function v(e) {
      let s = t.el;
      "container" !== t.params.mousewheel.eventsTarget && (s = document.querySelector(t.params.mousewheel.eventsTarget)), s[e]("mouseenter", m), s[e]("mouseleave", h), s[e]("wheel", g)
    }

    function w() {
      return t.params.cssMode ? (t.wrapperEl.removeEventListener("wheel", g), !0) : !t.mousewheel.enabled && (v("addEventListener"), t.mousewheel.enabled = !0, !0)
    }

    function b() {
      return t.params.cssMode ? (t.wrapperEl.addEventListener(event, g), !0) : !!t.mousewheel.enabled && (v("removeEventListener"), t.mousewheel.enabled = !1, !0)
    }

    a("init", (() => {
      !t.params.mousewheel.enabled && t.params.cssMode && b(), t.params.mousewheel.enabled && w()
    })), a("destroy", (() => {
      t.params.cssMode && w(), t.mousewheel.enabled && b()
    })), Object.assign(t.mousewheel, {enable: w, disable: b})
  }, function (e) {
    let {swiper: t, extendParams: s, on: a, emit: i} = e;
    s({
      navigation: {
        nextEl: null,
        prevEl: null,
        hideOnClick: !1,
        disabledClass: "swiper-button-disabled",
        hiddenClass: "swiper-button-hidden",
        lockClass: "swiper-button-lock",
        navigationDisabledClass: "swiper-navigation-disabled"
      }
    }), t.navigation = {nextEl: null, prevEl: null};
    const r = e => (Array.isArray(e) || (e = [e].filter((e => !!e))), e);

    function n(e) {
      let s;
      return e && "string" == typeof e && t.isElement && (s = t.el.shadowRoot.querySelector(e), s) ? s : (e && ("string" == typeof e && (s = [...document.querySelectorAll(e)]), t.params.uniqueNavElements && "string" == typeof e && s.length > 1 && 1 === t.el.querySelectorAll(e).length && (s = t.el.querySelector(e))), e && !s ? e : s)
    }

    function l(e, s) {
      const a = t.params.navigation;
      (e = r(e)).forEach((e => {
        e && (e.classList[s ? "add" : "remove"](...a.disabledClass.split(" ")), "BUTTON" === e.tagName && (e.disabled = s), t.params.watchOverflow && t.enabled && e.classList[t.isLocked ? "add" : "remove"](a.lockClass))
      }))
    }

    function o() {
      const {nextEl: e, prevEl: s} = t.navigation;
      if (t.params.loop) return l(s, !1), void l(e, !1);
      l(s, t.isBeginning && !t.params.rewind), l(e, t.isEnd && !t.params.rewind)
    }

    function d(e) {
      e.preventDefault(), (!t.isBeginning || t.params.loop || t.params.rewind) && (t.slidePrev(), i("navigationPrev"))
    }

    function c(e) {
      e.preventDefault(), (!t.isEnd || t.params.loop || t.params.rewind) && (t.slideNext(), i("navigationNext"))
    }

    function p() {
      const e = t.params.navigation;
      if (t.params.navigation = J(t, t.originalParams.navigation, t.params.navigation, {
        nextEl: "swiper-button-next",
        prevEl: "swiper-button-prev"
      }), !e.nextEl && !e.prevEl) return;
      let s = n(e.nextEl), a = n(e.prevEl);
      Object.assign(t.navigation, {nextEl: s, prevEl: a}), s = r(s), a = r(a);
      const i = (s, a) => {
        s && s.addEventListener("click", "next" === a ? c : d), !t.enabled && s && s.classList.add(...e.lockClass.split(" "))
      };
      s.forEach((e => i(e, "next"))), a.forEach((e => i(e, "prev")))
    }

    function u() {
      let {nextEl: e, prevEl: s} = t.navigation;
      e = r(e), s = r(s);
      const a = (e, s) => {
        e.removeEventListener("click", "next" === s ? c : d), e.classList.remove(...t.params.navigation.disabledClass.split(" "))
      };
      e.forEach((e => a(e, "next"))), s.forEach((e => a(e, "prev")))
    }

    a("init", (() => {
      !1 === t.params.navigation.enabled ? m() : (p(), o())
    })), a("toEdge fromEdge lock unlock", (() => {
      o()
    })), a("destroy", (() => {
      u()
    })), a("enable disable", (() => {
      let {nextEl: e, prevEl: s} = t.navigation;
      e = r(e), s = r(s), [...e, ...s].filter((e => !!e)).forEach((e => e.classList[t.enabled ? "remove" : "add"](t.params.navigation.lockClass)))
    })), a("click", ((e, s) => {
      let {nextEl: a, prevEl: n} = t.navigation;
      a = r(a), n = r(n);
      const l = s.target;
      if (t.params.navigation.hideOnClick && !n.includes(l) && !a.includes(l)) {
        if (t.pagination && t.params.pagination && t.params.pagination.clickable && (t.pagination.el === l || t.pagination.el.contains(l))) return;
        let e;
        a.length ? e = a[0].classList.contains(t.params.navigation.hiddenClass) : n.length && (e = n[0].classList.contains(t.params.navigation.hiddenClass)), i(!0 === e ? "navigationShow" : "navigationHide"), [...a, ...n].filter((e => !!e)).forEach((e => e.classList.toggle(t.params.navigation.hiddenClass)))
      }
    }));
    const m = () => {
      t.el.classList.add(...t.params.navigation.navigationDisabledClass.split(" ")), u()
    };
    Object.assign(t.navigation, {
      enable: () => {
        t.el.classList.remove(...t.params.navigation.navigationDisabledClass.split(" ")), p(), o()
      }, disable: m, update: o, init: p, destroy: u
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a, emit: i} = e;
    const r = "swiper-pagination";
    let n;
    s({
      pagination: {
        el: null,
        bulletElement: "span",
        clickable: !1,
        hideOnClick: !1,
        renderBullet: null,
        renderProgressbar: null,
        renderFraction: null,
        renderCustom: null,
        progressbarOpposite: !1,
        type: "bullets",
        dynamicBullets: !1,
        dynamicMainBullets: 1,
        formatFractionCurrent: e => e,
        formatFractionTotal: e => e,
        bulletClass: `${r}-bullet`,
        bulletActiveClass: `${r}-bullet-active`,
        modifierClass: `${r}-`,
        currentClass: `${r}-current`,
        totalClass: `${r}-total`,
        hiddenClass: `${r}-hidden`,
        progressbarFillClass: `${r}-progressbar-fill`,
        progressbarOppositeClass: `${r}-progressbar-opposite`,
        clickableClass: `${r}-clickable`,
        lockClass: `${r}-lock`,
        horizontalClass: `${r}-horizontal`,
        verticalClass: `${r}-vertical`,
        paginationDisabledClass: `${r}-disabled`
      }
    }), t.pagination = {el: null, bullets: []};
    let l = 0;
    const o = e => (Array.isArray(e) || (e = [e].filter((e => !!e))), e);

    function d() {
      return !t.params.pagination.el || !t.pagination.el || Array.isArray(t.pagination.el) && 0 === t.pagination.el.length
    }

    function c(e, s) {
      const {bulletActiveClass: a} = t.params.pagination;
      e && (e = e[("prev" === s ? "previous" : "next") + "ElementSibling"]) && (e.classList.add(`${a}-${s}`), (e = e[("prev" === s ? "previous" : "next") + "ElementSibling"]) && e.classList.add(`${a}-${s}-${s}`))
    }

    function p(e) {
      const s = e.target.closest(ee(t.params.pagination.bulletClass));
      if (!s) return;
      e.preventDefault();
      const a = b(s) * t.params.slidesPerGroup;
      if (t.params.loop) {
        if (t.realIndex === a) return;
        (a < t.loopedSlides || a > t.slides.length - t.loopedSlides) && t.loopFix({
          direction: a < t.loopedSlides ? "prev" : "next",
          activeSlideIndex: a,
          slideTo: !1
        }), t.slideToLoop(a)
      } else t.slideTo(a)
    }

    function u() {
      const e = t.rtl, s = t.params.pagination;
      if (d()) return;
      let a, r = t.pagination.el;
      r = o(r);
      const p = t.virtual && t.params.virtual.enabled ? t.virtual.slides.length : t.slides.length,
        u = t.params.loop ? Math.ceil(p / t.params.slidesPerGroup) : t.snapGrid.length;
      if (a = t.params.loop ? t.params.slidesPerGroup > 1 ? Math.floor(t.realIndex / t.params.slidesPerGroup) : t.realIndex : void 0 !== t.snapIndex ? t.snapIndex : t.activeIndex || 0, "bullets" === s.type && t.pagination.bullets && t.pagination.bullets.length > 0) {
        const i = t.pagination.bullets;
        let o, d, p;
        if (s.dynamicBullets && (n = x(i[0], t.isHorizontal() ? "width" : "height", !0), r.forEach((e => {
          e.style[t.isHorizontal() ? "width" : "height"] = n * (s.dynamicMainBullets + 4) + "px"
        })), s.dynamicMainBullets > 1 && void 0 !== t.previousIndex && (l += a - (t.previousIndex || 0), l > s.dynamicMainBullets - 1 ? l = s.dynamicMainBullets - 1 : l < 0 && (l = 0)), o = Math.max(a - l, 0), d = o + (Math.min(i.length, s.dynamicMainBullets) - 1), p = (d + o) / 2), i.forEach((e => {
          const t = [...["", "-next", "-next-next", "-prev", "-prev-prev", "-main"].map((e => `${s.bulletActiveClass}${e}`))].map((e => "string" == typeof e && e.includes(" ") ? e.split(" ") : e)).flat();
          e.classList.remove(...t)
        })), r.length > 1) i.forEach((e => {
          const t = b(e);
          t === a && e.classList.add(...s.bulletActiveClass.split(" ")), s.dynamicBullets && (t >= o && t <= d && e.classList.add(...`${s.bulletActiveClass}-main`.split(" ")), t === o && c(e, "prev"), t === d && c(e, "next"))
        })); else {
          const e = i[a];
          if (e && e.classList.add(...s.bulletActiveClass.split(" ")), s.dynamicBullets) {
            const e = i[o], t = i[d];
            for (let e = o; e <= d; e += 1) i[e] && i[e].classList.add(...`${s.bulletActiveClass}-main`.split(" "));
            c(e, "prev"), c(t, "next")
          }
        }
        if (s.dynamicBullets) {
          const a = Math.min(i.length, s.dynamicMainBullets + 4), r = (n * a - n) / 2 - p * n,
            l = e ? "right" : "left";
          i.forEach((e => {
            e.style[t.isHorizontal() ? l : "top"] = `${r}px`
          }))
        }
      }
      r.forEach(((e, r) => {
        if ("fraction" === s.type && (e.querySelectorAll(ee(s.currentClass)).forEach((e => {
          e.textContent = s.formatFractionCurrent(a + 1)
        })), e.querySelectorAll(ee(s.totalClass)).forEach((e => {
          e.textContent = s.formatFractionTotal(u)
        }))), "progressbar" === s.type) {
          let i;
          i = s.progressbarOpposite ? t.isHorizontal() ? "vertical" : "horizontal" : t.isHorizontal() ? "horizontal" : "vertical";
          const r = (a + 1) / u;
          let n = 1, l = 1;
          "horizontal" === i ? n = r : l = r, e.querySelectorAll(ee(s.progressbarFillClass)).forEach((e => {
            e.style.transform = `translate3d(0,0,0) scaleX(${n}) scaleY(${l})`, e.style.transitionDuration = `${t.params.speed}ms`
          }))
        }
        "custom" === s.type && s.renderCustom ? (e.innerHTML = s.renderCustom(t, a + 1, u), 0 === r && i("paginationRender", e)) : (0 === r && i("paginationRender", e), i("paginationUpdate", e)), t.params.watchOverflow && t.enabled && e.classList[t.isLocked ? "add" : "remove"](s.lockClass)
      }))
    }

    function m() {
      const e = t.params.pagination;
      if (d()) return;
      const s = t.virtual && t.params.virtual.enabled ? t.virtual.slides.length : t.slides.length;
      let a = t.pagination.el;
      a = o(a);
      let r = "";
      if ("bullets" === e.type) {
        let a = t.params.loop ? Math.ceil(s / t.params.slidesPerGroup) : t.snapGrid.length;
        t.params.freeMode && t.params.freeMode.enabled && a > s && (a = s);
        for (let s = 0; s < a; s += 1) e.renderBullet ? r += e.renderBullet.call(t, s, e.bulletClass) : r += `<${e.bulletElement} class="${e.bulletClass}"></${e.bulletElement}>`
      }
      "fraction" === e.type && (r = e.renderFraction ? e.renderFraction.call(t, e.currentClass, e.totalClass) : `<span class="${e.currentClass}"></span> / <span class="${e.totalClass}"></span>`), "progressbar" === e.type && (r = e.renderProgressbar ? e.renderProgressbar.call(t, e.progressbarFillClass) : `<span class="${e.progressbarFillClass}"></span>`), t.pagination.bullets = [], a.forEach((s => {
        "custom" !== e.type && (s.innerHTML = r || ""), "bullets" === e.type && t.pagination.bullets.push(...s.querySelectorAll(ee(e.bulletClass)))
      })), "custom" !== e.type && i("paginationRender", a[0])
    }

    function h() {
      t.params.pagination = J(t, t.originalParams.pagination, t.params.pagination, {el: "swiper-pagination"});
      const e = t.params.pagination;
      if (!e.el) return;
      let s;
      "string" == typeof e.el && t.isElement && (s = t.el.shadowRoot.querySelector(e.el)), s || "string" != typeof e.el || (s = [...document.querySelectorAll(e.el)]), s || (s = e.el), s && 0 !== s.length && (t.params.uniqueNavElements && "string" == typeof e.el && Array.isArray(s) && s.length > 1 && (s = [...t.el.querySelectorAll(e.el)], s.length > 1 && (s = s.filter((e => y(e, ".swiper")[0] === t.el))[0])), Array.isArray(s) && 1 === s.length && (s = s[0]), Object.assign(t.pagination, {el: s}), s = o(s), s.forEach((s => {
        "bullets" === e.type && e.clickable && s.classList.add(e.clickableClass), s.classList.add(e.modifierClass + e.type), s.classList.add(t.isHorizontal() ? e.horizontalClass : e.verticalClass), "bullets" === e.type && e.dynamicBullets && (s.classList.add(`${e.modifierClass}${e.type}-dynamic`), l = 0, e.dynamicMainBullets < 1 && (e.dynamicMainBullets = 1)), "progressbar" === e.type && e.progressbarOpposite && s.classList.add(e.progressbarOppositeClass), e.clickable && s.addEventListener("click", p), t.enabled || s.classList.add(e.lockClass)
      })))
    }

    function f() {
      const e = t.params.pagination;
      if (d()) return;
      let s = t.pagination.el;
      s && (s = o(s), s.forEach((s => {
        s.classList.remove(e.hiddenClass), s.classList.remove(e.modifierClass + e.type), s.classList.remove(t.isHorizontal() ? e.horizontalClass : e.verticalClass), e.clickable && s.removeEventListener("click", p)
      }))), t.pagination.bullets && t.pagination.bullets.forEach((t => t.classList.remove(...e.bulletActiveClass.split(" "))))
    }

    a("init", (() => {
      !1 === t.params.pagination.enabled ? g() : (h(), m(), u())
    })), a("activeIndexChange", (() => {
      void 0 === t.snapIndex && u()
    })), a("snapIndexChange", (() => {
      u()
    })), a("snapGridLengthChange", (() => {
      m(), u()
    })), a("destroy", (() => {
      f()
    })), a("enable disable", (() => {
      let {el: e} = t.pagination;
      e && (e = o(e), e.forEach((e => e.classList[t.enabled ? "remove" : "add"](t.params.pagination.lockClass))))
    })), a("lock unlock", (() => {
      u()
    })), a("click", ((e, s) => {
      const a = s.target;
      let {el: r} = t.pagination;
      if (Array.isArray(r) || (r = [r].filter((e => !!e))), t.params.pagination.el && t.params.pagination.hideOnClick && r && r.length > 0 && !a.classList.contains(t.params.pagination.bulletClass)) {
        if (t.navigation && (t.navigation.nextEl && a === t.navigation.nextEl || t.navigation.prevEl && a === t.navigation.prevEl)) return;
        const e = r[0].classList.contains(t.params.pagination.hiddenClass);
        i(!0 === e ? "paginationShow" : "paginationHide"), r.forEach((e => e.classList.toggle(t.params.pagination.hiddenClass)))
      }
    }));
    const g = () => {
      t.el.classList.add(t.params.pagination.paginationDisabledClass);
      let {el: e} = t.pagination;
      e && (e = o(e), e.forEach((e => e.classList.add(t.params.pagination.paginationDisabledClass)))), f()
    };
    Object.assign(t.pagination, {
      enable: () => {
        t.el.classList.remove(t.params.pagination.paginationDisabledClass);
        let {el: e} = t.pagination;
        e && (e = o(e), e.forEach((e => e.classList.remove(t.params.pagination.paginationDisabledClass)))), h(), m(), u()
      }, disable: g, render: m, update: u, init: h, destroy: f
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: i, emit: r} = e;
    const l = a();
    let o, d, c, p, u = !1, m = null, h = null;

    function f() {
      if (!t.params.scrollbar.el || !t.scrollbar.el) return;
      const {scrollbar: e, rtlTranslate: s} = t, {dragEl: a, el: i} = e, r = t.params.scrollbar,
        n = t.params.loop ? t.progressLoop : t.progress;
      let l = d, o = (c - d) * n;
      s ? (o = -o, o > 0 ? (l = d - o, o = 0) : -o + d > c && (l = c + o)) : o < 0 ? (l = d + o, o = 0) : o + d > c && (l = c - o), t.isHorizontal() ? (a.style.transform = `translate3d(${o}px, 0, 0)`, a.style.width = `${l}px`) : (a.style.transform = `translate3d(0px, ${o}px, 0)`, a.style.height = `${l}px`), r.hide && (clearTimeout(m), i.style.opacity = 1, m = setTimeout((() => {
        i.style.opacity = 0, i.style.transitionDuration = "400ms"
      }), 1e3))
    }

    function w() {
      if (!t.params.scrollbar.el || !t.scrollbar.el) return;
      const {scrollbar: e} = t, {dragEl: s, el: a} = e;
      s.style.width = "", s.style.height = "", c = t.isHorizontal() ? a.offsetWidth : a.offsetHeight, p = t.size / (t.virtualSize + t.params.slidesOffsetBefore - (t.params.centeredSlides ? t.snapGrid[0] : 0)), d = "auto" === t.params.scrollbar.dragSize ? c * p : parseInt(t.params.scrollbar.dragSize, 10), t.isHorizontal() ? s.style.width = `${d}px` : s.style.height = `${d}px`, a.style.display = p >= 1 ? "none" : "", t.params.scrollbar.hide && (a.style.opacity = 0), t.params.watchOverflow && t.enabled && e.el.classList[t.isLocked ? "add" : "remove"](t.params.scrollbar.lockClass)
    }

    function b(e) {
      return t.isHorizontal() ? e.clientX : e.clientY
    }

    function y(e) {
      const {scrollbar: s, rtlTranslate: a} = t, {el: i} = s;
      let r;
      r = (b(e) - v(i)[t.isHorizontal() ? "left" : "top"] - (null !== o ? o : d / 2)) / (c - d), r = Math.max(Math.min(r, 1), 0), a && (r = 1 - r);
      const n = t.minTranslate() + (t.maxTranslate() - t.minTranslate()) * r;
      t.updateProgress(n), t.setTranslate(n), t.updateActiveIndex(), t.updateSlidesClasses()
    }

    function E(e) {
      const s = t.params.scrollbar, {scrollbar: a, wrapperEl: i} = t, {el: n, dragEl: l} = a;
      u = !0, o = e.target === l ? b(e) - e.target.getBoundingClientRect()[t.isHorizontal() ? "left" : "top"] : null, e.preventDefault(), e.stopPropagation(), i.style.transitionDuration = "100ms", l.style.transitionDuration = "100ms", y(e), clearTimeout(h), n.style.transitionDuration = "0ms", s.hide && (n.style.opacity = 1), t.params.cssMode && (t.wrapperEl.style["scroll-snap-type"] = "none"), r("scrollbarDragStart", e)
    }

    function x(e) {
      const {scrollbar: s, wrapperEl: a} = t, {el: i, dragEl: n} = s;
      u && (e.preventDefault ? e.preventDefault() : e.returnValue = !1, y(e), a.style.transitionDuration = "0ms", i.style.transitionDuration = "0ms", n.style.transitionDuration = "0ms", r("scrollbarDragMove", e))
    }

    function S(e) {
      const s = t.params.scrollbar, {scrollbar: a, wrapperEl: i} = t, {el: l} = a;
      u && (u = !1, t.params.cssMode && (t.wrapperEl.style["scroll-snap-type"] = "", i.style.transitionDuration = ""), s.hide && (clearTimeout(h), h = n((() => {
        l.style.opacity = 0, l.style.transitionDuration = "400ms"
      }), 1e3)), r("scrollbarDragEnd", e), s.snapOnRelease && t.slideToClosest())
    }

    function T(e) {
      const {scrollbar: s, params: a} = t, i = s.el;
      if (!i) return;
      const r = i, n = !!a.passiveListeners && {passive: !1, capture: !1},
        o = !!a.passiveListeners && {passive: !0, capture: !1};
      if (!r) return;
      const d = "on" === e ? "addEventListener" : "removeEventListener";
      r[d]("pointerdown", E, n), l[d]("pointermove", x, n), l[d]("pointerup", S, o)
    }

    function M() {
      const {scrollbar: e, el: s} = t;
      t.params.scrollbar = J(t, t.originalParams.scrollbar, t.params.scrollbar, {el: "swiper-scrollbar"});
      const a = t.params.scrollbar;
      if (!a.el) return;
      let i, r;
      "string" == typeof a.el && t.isElement && (i = t.el.shadowRoot.querySelector(a.el)), i || "string" != typeof a.el ? i || (i = a.el) : i = l.querySelectorAll(a.el), t.params.uniqueNavElements && "string" == typeof a.el && i.length > 1 && 1 === s.querySelectorAll(a.el).length && (i = s.querySelector(a.el)), i.length > 0 && (i = i[0]), i.classList.add(t.isHorizontal() ? a.horizontalClass : a.verticalClass), i && (r = i.querySelector(`.${t.params.scrollbar.dragClass}`), r || (r = g("div", t.params.scrollbar.dragClass), i.append(r))), Object.assign(e, {
        el: i,
        dragEl: r
      }), a.draggable && t.params.scrollbar.el && t.scrollbar.el && T("on"), i && i.classList[t.enabled ? "remove" : "add"](t.params.scrollbar.lockClass)
    }

    function C() {
      const e = t.params.scrollbar, s = t.scrollbar.el;
      s && s.classList.remove(t.isHorizontal() ? e.horizontalClass : e.verticalClass), t.params.scrollbar.el && t.scrollbar.el && T("off")
    }

    s({
      scrollbar: {
        el: null,
        dragSize: "auto",
        hide: !1,
        draggable: !1,
        snapOnRelease: !0,
        lockClass: "swiper-scrollbar-lock",
        dragClass: "swiper-scrollbar-drag",
        scrollbarDisabledClass: "swiper-scrollbar-disabled",
        horizontalClass: "swiper-scrollbar-horizontal",
        verticalClass: "swiper-scrollbar-vertical"
      }
    }), t.scrollbar = {el: null, dragEl: null}, i("init", (() => {
      !1 === t.params.scrollbar.enabled ? P() : (M(), w(), f())
    })), i("update resize observerUpdate lock unlock", (() => {
      w()
    })), i("setTranslate", (() => {
      f()
    })), i("setTransition", ((e, s) => {
      !function (e) {
        t.params.scrollbar.el && t.scrollbar.el && (t.scrollbar.dragEl.style.transitionDuration = `${e}ms`)
      }(s)
    })), i("enable disable", (() => {
      const {el: e} = t.scrollbar;
      e && e.classList[t.enabled ? "remove" : "add"](t.params.scrollbar.lockClass)
    })), i("destroy", (() => {
      C()
    }));
    const P = () => {
      t.el.classList.add(t.params.scrollbar.scrollbarDisabledClass), t.scrollbar.el && t.scrollbar.el.classList.add(t.params.scrollbar.scrollbarDisabledClass), C()
    };
    Object.assign(t.scrollbar, {
      enable: () => {
        t.el.classList.remove(t.params.scrollbar.scrollbarDisabledClass), t.scrollbar.el && t.scrollbar.el.classList.remove(t.params.scrollbar.scrollbarDisabledClass), M(), w(), f()
      }, disable: P, updateSize: w, setTranslate: f, init: M, destroy: C
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({parallax: {enabled: !1}});
    const i = (e, s) => {
      const {rtl: a} = t, i = a ? -1 : 1, r = e.getAttribute("data-swiper-parallax") || "0";
      let n = e.getAttribute("data-swiper-parallax-x"), l = e.getAttribute("data-swiper-parallax-y");
      const o = e.getAttribute("data-swiper-parallax-scale"), d = e.getAttribute("data-swiper-parallax-opacity"),
        c = e.getAttribute("data-swiper-parallax-rotate");
      if (n || l ? (n = n || "0", l = l || "0") : t.isHorizontal() ? (n = r, l = "0") : (l = r, n = "0"), n = n.indexOf("%") >= 0 ? parseInt(n, 10) * s * i + "%" : n * s * i + "px", l = l.indexOf("%") >= 0 ? parseInt(l, 10) * s + "%" : l * s + "px", null != d) {
        const t = d - (d - 1) * (1 - Math.abs(s));
        e.style.opacity = t
      }
      let p = `translate3d(${n}, ${l}, 0px)`;
      if (null != o) {
        p += ` scale(${o - (o - 1) * (1 - Math.abs(s))})`
      }
      if (c && null != c) {
        p += ` rotate(${c * s * -1}deg)`
      }
      e.style.transform = p
    }, r = () => {
      const {el: e, slides: s, progress: a, snapGrid: r} = t;
      f(e, "[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale]").forEach((e => {
        i(e, a)
      })), s.forEach(((e, s) => {
        let n = e.progress;
        t.params.slidesPerGroup > 1 && "auto" !== t.params.slidesPerView && (n += Math.ceil(s / 2) - a * (r.length - 1)), n = Math.min(Math.max(n, -1), 1), e.querySelectorAll("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale], [data-swiper-parallax-rotate]").forEach((e => {
          i(e, n)
        }))
      }))
    };
    a("beforeInit", (() => {
      t.params.parallax.enabled && (t.params.watchSlidesProgress = !0, t.originalParams.watchSlidesProgress = !0)
    })), a("init", (() => {
      t.params.parallax.enabled && r()
    })), a("setTranslate", (() => {
      t.params.parallax.enabled && r()
    })), a("setTransition", ((e, s) => {
      t.params.parallax.enabled && function (e) {
        void 0 === e && (e = t.params.speed);
        const {el: s} = t;
        s.querySelectorAll("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale]").forEach((t => {
          let s = parseInt(t.getAttribute("data-swiper-parallax-duration"), 10) || e;
          0 === e && (s = 0), t.style.transitionDuration = `${s}ms`
        }))
      }(s)
    }))
  }, function (e) {
    let {swiper: t, extendParams: s, on: a, emit: i} = e;
    const n = r();
    s({
      zoom: {
        enabled: !1,
        maxRatio: 3,
        minRatio: 1,
        toggle: !0,
        containerClass: "swiper-zoom-container",
        zoomedSlideClass: "swiper-slide-zoomed"
      }
    }), t.zoom = {enabled: !1};
    let l, d, c = 1, p = !1;
    const u = [], m = {
      slideEl: void 0,
      slideWidth: void 0,
      slideHeight: void 0,
      imageEl: void 0,
      imageWrapEl: void 0,
      maxRatio: 3
    }, h = {
      isTouched: void 0,
      isMoved: void 0,
      currentX: void 0,
      currentY: void 0,
      minX: void 0,
      minY: void 0,
      maxX: void 0,
      maxY: void 0,
      width: void 0,
      height: void 0,
      startX: void 0,
      startY: void 0,
      touchesStart: {},
      touchesCurrent: {}
    }, g = {x: void 0, y: void 0, prevPositionX: void 0, prevPositionY: void 0, prevTime: void 0};
    let w = 1;

    function b() {
      if (u.length < 2) return 1;
      const e = u[0].pageX, t = u[0].pageY, s = u[1].pageX, a = u[1].pageY;
      return Math.sqrt((s - e) ** 2 + (a - t) ** 2)
    }

    function E(e) {
      const s = t.isElement ? "swiper-slide" : `.${t.params.slideClass}`;
      return !!e.target.matches(s) || t.slides.filter((t => t.contains(e.target))).length > 0
    }

    function x(e) {
      if ("mouse" === e.pointerType && u.splice(0, u.length), !E(e)) return;
      const s = t.params.zoom;
      if (l = !1, d = !1, u.push(e), !(u.length < 2)) {
        if (l = !0, m.scaleStart = b(), !m.slideEl) {
          m.slideEl = e.target.closest(`.${t.params.slideClass}, swiper-slide`), m.slideEl || (m.slideEl = t.slides[t.activeIndex]);
          let a = m.slideEl.querySelector(`.${s.containerClass}`);
          if (a && (a = a.querySelectorAll("picture, img, svg, canvas, .swiper-zoom-target")[0]), m.imageEl = a, m.imageWrapEl = a ? y(m.imageEl, `.${s.containerClass}`)[0] : void 0, !m.imageWrapEl) return void (m.imageEl = void 0);
          m.maxRatio = m.imageWrapEl.getAttribute("data-swiper-zoom") || s.maxRatio
        }
        if (m.imageEl) {
          const [e, t] = function () {
            if (u.length < 2) return {x: null, y: null};
            const e = m.imageEl.getBoundingClientRect();
            return [(u[0].pageX + (u[1].pageX - u[0].pageX) / 2 - e.x) / c, (u[0].pageY + (u[1].pageY - u[0].pageY) / 2 - e.y) / c]
          }();
          m.imageEl.style.transformOrigin = `${e}px ${t}px`, m.imageEl.style.transitionDuration = "0ms"
        }
        p = !0
      }
    }

    function S(e) {
      if (!E(e)) return;
      const s = t.params.zoom, a = t.zoom, i = u.findIndex((t => t.pointerId === e.pointerId));
      i >= 0 && (u[i] = e), u.length < 2 || (d = !0, m.scaleMove = b(), m.imageEl && (a.scale = m.scaleMove / m.scaleStart * c, a.scale > m.maxRatio && (a.scale = m.maxRatio - 1 + (a.scale - m.maxRatio + 1) ** .5), a.scale < s.minRatio && (a.scale = s.minRatio + 1 - (s.minRatio - a.scale + 1) ** .5), m.imageEl.style.transform = `translate3d(0,0,0) scale(${a.scale})`))
    }

    function T(e) {
      if (!E(e)) return;
      if ("mouse" === e.pointerType && "pointerout" === e.type) return;
      const s = t.params.zoom, a = t.zoom, i = u.findIndex((t => t.pointerId === e.pointerId));
      i >= 0 && u.splice(i, 1), l && d && (l = !1, d = !1, m.imageEl && (a.scale = Math.max(Math.min(a.scale, m.maxRatio), s.minRatio), m.imageEl.style.transitionDuration = `${t.params.speed}ms`, m.imageEl.style.transform = `translate3d(0,0,0) scale(${a.scale})`, c = a.scale, p = !1, 1 === a.scale && (m.slideEl = void 0)))
    }

    function M(e) {
      if (!E(e) || !function (e) {
        const s = `.${t.params.zoom.containerClass}`;
        return !!e.target.matches(s) || [...t.el.querySelectorAll(s)].filter((t => t.contains(e.target))).length > 0
      }(e)) return;
      const s = t.zoom;
      if (!m.imageEl) return;
      if (t.allowClick = !1, !h.isTouched || !m.slideEl) return;
      h.isMoved || (h.width = m.imageEl.offsetWidth, h.height = m.imageEl.offsetHeight, h.startX = o(m.imageWrapEl, "x") || 0, h.startY = o(m.imageWrapEl, "y") || 0, m.slideWidth = m.slideEl.offsetWidth, m.slideHeight = m.slideEl.offsetHeight, m.imageWrapEl.style.transitionDuration = "0ms");
      const a = h.width * s.scale, i = h.height * s.scale;
      if (!(a < m.slideWidth && i < m.slideHeight)) {
        if (h.minX = Math.min(m.slideWidth / 2 - a / 2, 0), h.maxX = -h.minX, h.minY = Math.min(m.slideHeight / 2 - i / 2, 0), h.maxY = -h.minY, h.touchesCurrent.x = u.length > 0 ? u[0].pageX : e.pageX, h.touchesCurrent.y = u.length > 0 ? u[0].pageY : e.pageY, !h.isMoved && !p) {
          if (t.isHorizontal() && (Math.floor(h.minX) === Math.floor(h.startX) && h.touchesCurrent.x < h.touchesStart.x || Math.floor(h.maxX) === Math.floor(h.startX) && h.touchesCurrent.x > h.touchesStart.x)) return void (h.isTouched = !1);
          if (!t.isHorizontal() && (Math.floor(h.minY) === Math.floor(h.startY) && h.touchesCurrent.y < h.touchesStart.y || Math.floor(h.maxY) === Math.floor(h.startY) && h.touchesCurrent.y > h.touchesStart.y)) return void (h.isTouched = !1)
        }
        e.cancelable && e.preventDefault(), e.stopPropagation(), h.isMoved = !0, h.currentX = h.touchesCurrent.x - h.touchesStart.x + h.startX, h.currentY = h.touchesCurrent.y - h.touchesStart.y + h.startY, h.currentX < h.minX && (h.currentX = h.minX + 1 - (h.minX - h.currentX + 1) ** .8), h.currentX > h.maxX && (h.currentX = h.maxX - 1 + (h.currentX - h.maxX + 1) ** .8), h.currentY < h.minY && (h.currentY = h.minY + 1 - (h.minY - h.currentY + 1) ** .8), h.currentY > h.maxY && (h.currentY = h.maxY - 1 + (h.currentY - h.maxY + 1) ** .8), g.prevPositionX || (g.prevPositionX = h.touchesCurrent.x), g.prevPositionY || (g.prevPositionY = h.touchesCurrent.y), g.prevTime || (g.prevTime = Date.now()), g.x = (h.touchesCurrent.x - g.prevPositionX) / (Date.now() - g.prevTime) / 2, g.y = (h.touchesCurrent.y - g.prevPositionY) / (Date.now() - g.prevTime) / 2, Math.abs(h.touchesCurrent.x - g.prevPositionX) < 2 && (g.x = 0), Math.abs(h.touchesCurrent.y - g.prevPositionY) < 2 && (g.y = 0), g.prevPositionX = h.touchesCurrent.x, g.prevPositionY = h.touchesCurrent.y, g.prevTime = Date.now(), m.imageWrapEl.style.transform = `translate3d(${h.currentX}px, ${h.currentY}px,0)`
      }
    }

    function C() {
      const e = t.zoom;
      m.slideEl && t.previousIndex !== t.activeIndex && (m.imageEl && (m.imageEl.style.transform = "translate3d(0,0,0) scale(1)"), m.imageWrapEl && (m.imageWrapEl.style.transform = "translate3d(0,0,0)"), e.scale = 1, c = 1, m.slideEl = void 0, m.imageEl = void 0, m.imageWrapEl = void 0)
    }

    function P(e) {
      const s = t.zoom, a = t.params.zoom;
      if (!m.slideEl) {
        e && e.target && (m.slideEl = e.target.closest(`.${t.params.slideClass}, swiper-slide`)), m.slideEl || (t.params.virtual && t.params.virtual.enabled && t.virtual ? m.slideEl = f(t.slidesEl, `.${t.params.slideActiveClass}`)[0] : m.slideEl = t.slides[t.activeIndex]);
        let s = m.slideEl.querySelector(`.${a.containerClass}`);
        s && (s = s.querySelectorAll("picture, img, svg, canvas, .swiper-zoom-target")[0]), m.imageEl = s, m.imageWrapEl = s ? y(m.imageEl, `.${a.containerClass}`)[0] : void 0
      }
      if (!m.imageEl || !m.imageWrapEl) return;
      let i, r, l, o, d, p, u, g, w, b, E, x, S, T, M, C, P, L;
      t.params.cssMode && (t.wrapperEl.style.overflow = "hidden", t.wrapperEl.style.touchAction = "none"), m.slideEl.classList.add(`${a.zoomedSlideClass}`), void 0 === h.touchesStart.x && e ? (i = e.pageX, r = e.pageY) : (i = h.touchesStart.x, r = h.touchesStart.y);
      const A = "number" == typeof e ? e : null;
      1 === c && A && (i = void 0, r = void 0), s.scale = A || m.imageWrapEl.getAttribute("data-swiper-zoom") || a.maxRatio, c = A || m.imageWrapEl.getAttribute("data-swiper-zoom") || a.maxRatio, !e || 1 === c && A ? (u = 0, g = 0) : (P = m.slideEl.offsetWidth, L = m.slideEl.offsetHeight, l = v(m.slideEl).left + n.scrollX, o = v(m.slideEl).top + n.scrollY, d = l + P / 2 - i, p = o + L / 2 - r, w = m.imageEl.offsetWidth, b = m.imageEl.offsetHeight, E = w * s.scale, x = b * s.scale, S = Math.min(P / 2 - E / 2, 0), T = Math.min(L / 2 - x / 2, 0), M = -S, C = -T, u = d * s.scale, g = p * s.scale, u < S && (u = S), u > M && (u = M), g < T && (g = T), g > C && (g = C)), m.imageWrapEl.style.transitionDuration = "300ms", m.imageWrapEl.style.transform = `translate3d(${u}px, ${g}px,0)`, m.imageEl.style.transitionDuration = "300ms", m.imageEl.style.transform = `translate3d(0,0,0) scale(${s.scale})`
    }

    function L() {
      const e = t.zoom, s = t.params.zoom;
      if (!m.slideEl) {
        t.params.virtual && t.params.virtual.enabled && t.virtual ? m.slideEl = f(t.slidesEl, `.${t.params.slideActiveClass}`)[0] : m.slideEl = t.slides[t.activeIndex];
        let e = m.slideEl.querySelector(`.${s.containerClass}`);
        e && (e = e.querySelectorAll("picture, img, svg, canvas, .swiper-zoom-target")[0]), m.imageEl = e, m.imageWrapEl = e ? y(m.imageEl, `.${s.containerClass}`)[0] : void 0
      }
      m.imageEl && m.imageWrapEl && (t.params.cssMode && (t.wrapperEl.style.overflow = "", t.wrapperEl.style.touchAction = ""), e.scale = 1, c = 1, m.imageWrapEl.style.transitionDuration = "300ms", m.imageWrapEl.style.transform = "translate3d(0,0,0)", m.imageEl.style.transitionDuration = "300ms", m.imageEl.style.transform = "translate3d(0,0,0) scale(1)", m.slideEl.classList.remove(`${s.zoomedSlideClass}`), m.slideEl = void 0)
    }

    function A(e) {
      const s = t.zoom;
      s.scale && 1 !== s.scale ? L() : P(e)
    }

    function z() {
      return {
        passiveListener: !!t.params.passiveListeners && {passive: !0, capture: !1},
        activeListenerWithCapture: !t.params.passiveListeners || {passive: !1, capture: !0}
      }
    }

    function $() {
      const e = t.zoom;
      if (e.enabled) return;
      e.enabled = !0;
      const {passiveListener: s, activeListenerWithCapture: a} = z();
      t.wrapperEl.addEventListener("pointerdown", x, s), t.wrapperEl.addEventListener("pointermove", S, a), ["pointerup", "pointercancel", "pointerout"].forEach((e => {
        t.wrapperEl.addEventListener(e, T, s)
      })), t.wrapperEl.addEventListener("pointermove", M, a)
    }

    function k() {
      const e = t.zoom;
      if (!e.enabled) return;
      e.enabled = !1;
      const {passiveListener: s, activeListenerWithCapture: a} = z();
      t.wrapperEl.removeEventListener("pointerdown", x, s), t.wrapperEl.removeEventListener("pointermove", S, a), ["pointerup", "pointercancel", "pointerout"].forEach((e => {
        t.wrapperEl.removeEventListener(e, T, s)
      })), t.wrapperEl.removeEventListener("pointermove", M, a)
    }

    Object.defineProperty(t.zoom, "scale", {
      get: () => w, set(e) {
        if (w !== e) {
          const t = m.imageEl, s = m.slideEl;
          i("zoomChange", e, t, s)
        }
        w = e
      }
    }), a("init", (() => {
      t.params.zoom.enabled && $()
    })), a("destroy", (() => {
      k()
    })), a("touchStart", ((e, s) => {
      t.zoom.enabled && function (e) {
        const s = t.device;
        m.imageEl && (h.isTouched || (s.android && e.cancelable && e.preventDefault(), h.isTouched = !0, h.touchesStart.x = e.pageX, h.touchesStart.y = e.pageY))
      }(s)
    })), a("touchEnd", ((e, s) => {
      t.zoom.enabled && function () {
        const e = t.zoom;
        if (!m.imageEl) return;
        if (!h.isTouched || !h.isMoved) return h.isTouched = !1, void (h.isMoved = !1);
        h.isTouched = !1, h.isMoved = !1;
        let s = 300, a = 300;
        const i = g.x * s, r = h.currentX + i, n = g.y * a, l = h.currentY + n;
        0 !== g.x && (s = Math.abs((r - h.currentX) / g.x)), 0 !== g.y && (a = Math.abs((l - h.currentY) / g.y));
        const o = Math.max(s, a);
        h.currentX = r, h.currentY = l;
        const d = h.width * e.scale, c = h.height * e.scale;
        h.minX = Math.min(m.slideWidth / 2 - d / 2, 0), h.maxX = -h.minX, h.minY = Math.min(m.slideHeight / 2 - c / 2, 0), h.maxY = -h.minY, h.currentX = Math.max(Math.min(h.currentX, h.maxX), h.minX), h.currentY = Math.max(Math.min(h.currentY, h.maxY), h.minY), m.imageWrapEl.style.transitionDuration = `${o}ms`, m.imageWrapEl.style.transform = `translate3d(${h.currentX}px, ${h.currentY}px,0)`
      }()
    })), a("doubleTap", ((e, s) => {
      !t.animating && t.params.zoom.enabled && t.zoom.enabled && t.params.zoom.toggle && A(s)
    })), a("transitionEnd", (() => {
      t.zoom.enabled && t.params.zoom.enabled && C()
    })), a("slideChange", (() => {
      t.zoom.enabled && t.params.zoom.enabled && t.params.cssMode && C()
    })), Object.assign(t.zoom, {enable: $, disable: k, in: P, out: L, toggle: A})
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;

    function i(e, t) {
      const s = function () {
        let e, t, s;
        return (a, i) => {
          for (t = -1, e = a.length; e - t > 1;) s = e + t >> 1, a[s] <= i ? t = s : e = s;
          return e
        }
      }();
      let a, i;
      return this.x = e, this.y = t, this.lastIndex = e.length - 1, this.interpolate = function (e) {
        return e ? (i = s(this.x, e), a = i - 1, (e - this.x[a]) * (this.y[i] - this.y[a]) / (this.x[i] - this.x[a]) + this.y[a]) : 0
      }, this
    }

    function r() {
      t.controller.control && t.controller.spline && (t.controller.spline = void 0, delete t.controller.spline)
    }

    s({
      controller: {
        control: void 0,
        inverse: !1,
        by: "slide"
      }
    }), t.controller = {control: void 0}, a("beforeInit", (() => {
      if ("undefined" != typeof window && ("string" == typeof t.params.controller.control || t.params.controller.control instanceof HTMLElement)) {
        const e = document.querySelector(t.params.controller.control);
        if (e && e.swiper) t.controller.control = e.swiper; else if (e) {
          const s = a => {
            t.controller.control = a.detail[0], t.update(), e.removeEventListener("init", s)
          };
          e.addEventListener("init", s)
        }
      } else t.controller.control = t.params.controller.control
    })), a("update", (() => {
      r()
    })), a("resize", (() => {
      r()
    })), a("observerUpdate", (() => {
      r()
    })), a("setTranslate", ((e, s, a) => {
      t.controller.control && t.controller.setTranslate(s, a)
    })), a("setTransition", ((e, s, a) => {
      t.controller.control && t.controller.setTransition(s, a)
    })), Object.assign(t.controller, {
      setTranslate: function (e, s) {
        const a = t.controller.control;
        let r, n;
        const l = t.constructor;

        function o(e) {
          const s = t.rtlTranslate ? -t.translate : t.translate;
          "slide" === t.params.controller.by && (!function (e) {
            t.controller.spline || (t.controller.spline = t.params.loop ? new i(t.slidesGrid, e.slidesGrid) : new i(t.snapGrid, e.snapGrid))
          }(e), n = -t.controller.spline.interpolate(-s)), n && "container" !== t.params.controller.by || (r = (e.maxTranslate() - e.minTranslate()) / (t.maxTranslate() - t.minTranslate()), n = (s - t.minTranslate()) * r + e.minTranslate()), t.params.controller.inverse && (n = e.maxTranslate() - n), e.updateProgress(n), e.setTranslate(n, t), e.updateActiveIndex(), e.updateSlidesClasses()
        }

        if (Array.isArray(a)) for (let e = 0; e < a.length; e += 1) a[e] !== s && a[e] instanceof l && o(a[e]); else a instanceof l && s !== a && o(a)
      }, setTransition: function (e, s) {
        const a = t.constructor, i = t.controller.control;
        let r;

        function l(s) {
          s.setTransition(e, t), 0 !== e && (s.transitionStart(), s.params.autoHeight && n((() => {
            s.updateAutoHeight()
          })), E(s.wrapperEl, (() => {
            i && s.transitionEnd()
          })))
        }

        if (Array.isArray(i)) for (r = 0; r < i.length; r += 1) i[r] !== s && i[r] instanceof a && l(i[r]); else i instanceof a && s !== i && l(i)
      }
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({
      a11y: {
        enabled: !0,
        notificationClass: "swiper-notification",
        prevSlideMessage: "Previous slide",
        nextSlideMessage: "Next slide",
        firstSlideMessage: "This is the first slide",
        lastSlideMessage: "This is the last slide",
        paginationBulletMessage: "Go to slide {{index}}",
        slideLabelMessage: "{{index}} / {{slidesLength}}",
        containerMessage: null,
        containerRoleDescriptionMessage: null,
        itemRoleDescriptionMessage: null,
        slideRole: "group",
        id: null
      }
    }), t.a11y = {clicked: !1};
    let i = null;

    function r(e) {
      const t = i;
      0 !== t.length && (t.innerHTML = "", t.innerHTML = e)
    }

    const n = e => (Array.isArray(e) || (e = [e].filter((e => !!e))), e);

    function l(e) {
      (e = n(e)).forEach((e => {
        e.setAttribute("tabIndex", "0")
      }))
    }

    function o(e) {
      (e = n(e)).forEach((e => {
        e.setAttribute("tabIndex", "-1")
      }))
    }

    function d(e, t) {
      (e = n(e)).forEach((e => {
        e.setAttribute("role", t)
      }))
    }

    function c(e, t) {
      (e = n(e)).forEach((e => {
        e.setAttribute("aria-roledescription", t)
      }))
    }

    function p(e, t) {
      (e = n(e)).forEach((e => {
        e.setAttribute("aria-label", t)
      }))
    }

    function u(e) {
      (e = n(e)).forEach((e => {
        e.setAttribute("aria-disabled", !0)
      }))
    }

    function m(e) {
      (e = n(e)).forEach((e => {
        e.setAttribute("aria-disabled", !1)
      }))
    }

    function h(e) {
      if (13 !== e.keyCode && 32 !== e.keyCode) return;
      const s = t.params.a11y, a = e.target;
      t.pagination && t.pagination.el && (a === t.pagination.el || t.pagination.el.contains(e.target)) && !e.target.matches(ee(t.params.pagination.bulletClass)) || (t.navigation && t.navigation.nextEl && a === t.navigation.nextEl && (t.isEnd && !t.params.loop || t.slideNext(), t.isEnd ? r(s.lastSlideMessage) : r(s.nextSlideMessage)), t.navigation && t.navigation.prevEl && a === t.navigation.prevEl && (t.isBeginning && !t.params.loop || t.slidePrev(), t.isBeginning ? r(s.firstSlideMessage) : r(s.prevSlideMessage)), t.pagination && a.matches(ee(t.params.pagination.bulletClass)) && a.click())
    }

    function f() {
      return t.pagination && t.pagination.bullets && t.pagination.bullets.length
    }

    function v() {
      return f() && t.params.pagination.clickable
    }

    const w = (e, t, s) => {
      l(e), "BUTTON" !== e.tagName && (d(e, "button"), e.addEventListener("keydown", h)), p(e, s), function (e, t) {
        (e = n(e)).forEach((e => {
          e.setAttribute("aria-controls", t)
        }))
      }(e, t)
    }, y = () => {
      t.a11y.clicked = !0
    }, E = () => {
      requestAnimationFrame((() => {
        requestAnimationFrame((() => {
          t.destroyed || (t.a11y.clicked = !1)
        }))
      }))
    }, x = e => {
      if (t.a11y.clicked) return;
      const s = e.target.closest(`.${t.params.slideClass}, swiper-slide`);
      if (!s || !t.slides.includes(s)) return;
      const a = t.slides.indexOf(s) === t.activeIndex,
        i = t.params.watchSlidesProgress && t.visibleSlides && t.visibleSlides.includes(s);
      a || i || e.sourceCapabilities && e.sourceCapabilities.firesTouchEvents || (t.isHorizontal() ? t.el.scrollLeft = 0 : t.el.scrollTop = 0, t.slideTo(t.slides.indexOf(s), 0))
    }, S = () => {
      const e = t.params.a11y;
      e.itemRoleDescriptionMessage && c(t.slides, e.itemRoleDescriptionMessage), e.slideRole && d(t.slides, e.slideRole);
      const s = t.slides.length;
      e.slideLabelMessage && t.slides.forEach(((a, i) => {
        const r = t.params.loop ? parseInt(a.getAttribute("data-swiper-slide-index"), 10) : i;
        p(a, e.slideLabelMessage.replace(/\{\{index\}\}/, r + 1).replace(/\{\{slidesLength\}\}/, s))
      }))
    }, T = () => {
      const e = t.params.a11y;
      t.el.append(i);
      const s = t.el;
      e.containerRoleDescriptionMessage && c(s, e.containerRoleDescriptionMessage), e.containerMessage && p(s, e.containerMessage);
      const a = t.wrapperEl,
        r = e.id || a.getAttribute("id") || `swiper-wrapper-${l = 16, void 0 === l && (l = 16), "x".repeat(l).replace(/x/g, (() => Math.round(16 * Math.random()).toString(16)))}`;
      var l;
      const o = t.params.autoplay && t.params.autoplay.enabled ? "off" : "polite";
      var d;
      d = r, n(a).forEach((e => {
        e.setAttribute("id", d)
      })), function (e, t) {
        (e = n(e)).forEach((e => {
          e.setAttribute("aria-live", t)
        }))
      }(a, o), S();
      let {nextEl: u, prevEl: m} = t.navigation ? t.navigation : {};
      if (u = n(u), m = n(m), u && u.forEach((t => w(t, r, e.nextSlideMessage))), m && m.forEach((t => w(t, r, e.prevSlideMessage))), v()) {
        (Array.isArray(t.pagination.el) ? t.pagination.el : [t.pagination.el]).forEach((e => {
          e.addEventListener("keydown", h)
        }))
      }
      t.el.addEventListener("focus", x, !0), t.el.addEventListener("pointerdown", y, !0), t.el.addEventListener("pointerup", E, !0)
    };
    a("beforeInit", (() => {
      i = g("span", t.params.a11y.notificationClass), i.setAttribute("aria-live", "assertive"), i.setAttribute("aria-atomic", "true"), t.isElement && i.setAttribute("slot", "container-end")
    })), a("afterInit", (() => {
      t.params.a11y.enabled && T()
    })), a("slidesLengthChange snapGridLengthChange slidesGridLengthChange", (() => {
      t.params.a11y.enabled && S()
    })), a("fromEdge toEdge afterInit lock unlock", (() => {
      t.params.a11y.enabled && function () {
        if (t.params.loop || t.params.rewind || !t.navigation) return;
        const {nextEl: e, prevEl: s} = t.navigation;
        s && (t.isBeginning ? (u(s), o(s)) : (m(s), l(s))), e && (t.isEnd ? (u(e), o(e)) : (m(e), l(e)))
      }()
    })), a("paginationUpdate", (() => {
      t.params.a11y.enabled && function () {
        const e = t.params.a11y;
        f() && t.pagination.bullets.forEach((s => {
          t.params.pagination.clickable && (l(s), t.params.pagination.renderBullet || (d(s, "button"), p(s, e.paginationBulletMessage.replace(/\{\{index\}\}/, b(s) + 1)))), s.matches(ee(t.params.pagination.bulletActiveClass)) ? s.setAttribute("aria-current", "true") : s.removeAttribute("aria-current")
        }))
      }()
    })), a("destroy", (() => {
      t.params.a11y.enabled && function () {
        i && i.length > 0 && i.remove();
        let {nextEl: e, prevEl: s} = t.navigation ? t.navigation : {};
        e = n(e), s = n(s), e && e.forEach((e => e.removeEventListener("keydown", h))), s && s.forEach((e => e.removeEventListener("keydown", h))), v() && (Array.isArray(t.pagination.el) ? t.pagination.el : [t.pagination.el]).forEach((e => {
          e.removeEventListener("keydown", h)
        }));
        t.el.removeEventListener("focus", x, !0), t.el.removeEventListener("pointerdown", y, !0), t.el.removeEventListener("pointerup", E, !0)
      }()
    }))
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({history: {enabled: !1, root: "", replaceState: !1, key: "slides", keepQuery: !1}});
    let i = !1, n = {};
    const l = e => e.toString().replace(/\s+/g, "-").replace(/[^\w-]+/g, "").replace(/--+/g, "-").replace(/^-+/, "").replace(/-+$/, ""),
      o = e => {
        const t = r();
        let s;
        s = e ? new URL(e) : t.location;
        const a = s.pathname.slice(1).split("/").filter((e => "" !== e)), i = a.length;
        return {key: a[i - 2], value: a[i - 1]}
      }, d = (e, s) => {
        const a = r();
        if (!i || !t.params.history.enabled) return;
        let n;
        n = t.params.url ? new URL(t.params.url) : a.location;
        const o = t.slides[s];
        let d = l(o.getAttribute("data-history"));
        if (t.params.history.root.length > 0) {
          let s = t.params.history.root;
          "/" === s[s.length - 1] && (s = s.slice(0, s.length - 1)), d = `${s}/${e ? `${e}/` : ""}${d}`
        } else n.pathname.includes(e) || (d = `${e ? `${e}/` : ""}${d}`);
        t.params.history.keepQuery && (d += n.search);
        const c = a.history.state;
        c && c.value === d || (t.params.history.replaceState ? a.history.replaceState({value: d}, null, d) : a.history.pushState({value: d}, null, d))
      }, c = (e, s, a) => {
        if (s) for (let i = 0, r = t.slides.length; i < r; i += 1) {
          const r = t.slides[i];
          if (l(r.getAttribute("data-history")) === s) {
            const s = t.getSlideIndex(r);
            t.slideTo(s, e, a)
          }
        } else t.slideTo(0, e, a)
      }, p = () => {
        n = o(t.params.url), c(t.params.speed, n.value, !1)
      };
    a("init", (() => {
      t.params.history.enabled && (() => {
        const e = r();
        if (t.params.history) {
          if (!e.history || !e.history.pushState) return t.params.history.enabled = !1, void (t.params.hashNavigation.enabled = !0);
          i = !0, n = o(t.params.url), n.key || n.value ? (c(0, n.value, t.params.runCallbacksOnInit), t.params.history.replaceState || e.addEventListener("popstate", p)) : t.params.history.replaceState || e.addEventListener("popstate", p)
        }
      })()
    })), a("destroy", (() => {
      t.params.history.enabled && (() => {
        const e = r();
        t.params.history.replaceState || e.removeEventListener("popstate", p)
      })()
    })), a("transitionEnd _freeModeNoMomentumRelease", (() => {
      i && d(t.params.history.key, t.activeIndex)
    })), a("slideChange", (() => {
      i && t.params.cssMode && d(t.params.history.key, t.activeIndex)
    }))
  }, function (e) {
    let {swiper: t, extendParams: s, emit: i, on: n} = e, l = !1;
    const o = a(), d = r();
    s({hashNavigation: {enabled: !1, replaceState: !1, watchState: !1}});
    const c = () => {
      i("hashChange");
      const e = o.location.hash.replace("#", "");
      if (e !== t.slides[t.activeIndex].getAttribute("data-hash")) {
        const s = t.getSlideIndex(f(t.slidesEl, `.${t.params.slideClass}[data-hash="${e}"], swiper-slide[data-hash="${e}"]`)[0]);
        if (void 0 === s) return;
        t.slideTo(s)
      }
    }, p = () => {
      if (l && t.params.hashNavigation.enabled) if (t.params.hashNavigation.replaceState && d.history && d.history.replaceState) d.history.replaceState(null, null, `#${t.slides[t.activeIndex].getAttribute("data-hash")}` || ""), i("hashSet"); else {
        const e = t.slides[t.activeIndex], s = e.getAttribute("data-hash") || e.getAttribute("data-history");
        o.location.hash = s || "", i("hashSet")
      }
    };
    n("init", (() => {
      t.params.hashNavigation.enabled && (() => {
        if (!t.params.hashNavigation.enabled || t.params.history && t.params.history.enabled) return;
        l = !0;
        const e = o.location.hash.replace("#", "");
        if (e) {
          const s = 0;
          for (let a = 0, i = t.slides.length; a < i; a += 1) {
            const i = t.slides[a];
            if ((i.getAttribute("data-hash") || i.getAttribute("data-history")) === e) {
              const e = t.getSlideIndex(i);
              t.slideTo(e, s, t.params.runCallbacksOnInit, !0)
            }
          }
        }
        t.params.hashNavigation.watchState && d.addEventListener("hashchange", c)
      })()
    })), n("destroy", (() => {
      t.params.hashNavigation.enabled && t.params.hashNavigation.watchState && d.removeEventListener("hashchange", c)
    })), n("transitionEnd _freeModeNoMomentumRelease", (() => {
      l && p()
    })), n("slideChange", (() => {
      l && t.params.cssMode && p()
    }))
  }, function (e) {
    let t, s, {swiper: i, extendParams: r, on: n, emit: l, params: o} = e;
    i.autoplay = {running: !1, paused: !1, timeLeft: 0}, r({
      autoplay: {
        enabled: !1,
        delay: 3e3,
        waitForTransition: !0,
        disableOnInteraction: !0,
        stopOnLastSlide: !1,
        reverseDirection: !1,
        pauseOnMouseEnter: !1
      }
    });
    let d, c, p, u, m, h, f, g = o && o.autoplay ? o.autoplay.delay : 3e3,
      v = o && o.autoplay ? o.autoplay.delay : 3e3, w = (new Date).getTime;

    function b(e) {
      i && !i.destroyed && i.wrapperEl && e.target === i.wrapperEl && (i.wrapperEl.removeEventListener("transitionend", b), M())
    }

    const y = () => {
      if (i.destroyed || !i.autoplay.running) return;
      i.autoplay.paused ? c = !0 : c && (v = d, c = !1);
      const e = i.autoplay.paused ? d : w + v - (new Date).getTime();
      i.autoplay.timeLeft = e, l("autoplayTimeLeft", e, e / g), s = requestAnimationFrame((() => {
        y()
      }))
    }, E = e => {
      if (i.destroyed || !i.autoplay.running) return;
      cancelAnimationFrame(s), y();
      let a = void 0 === e ? i.params.autoplay.delay : e;
      g = i.params.autoplay.delay, v = i.params.autoplay.delay;
      const r = (() => {
        let e;
        if (e = i.virtual && i.params.virtual.enabled ? i.slides.filter((e => e.classList.contains("swiper-slide-active")))[0] : i.slides[i.activeIndex], !e) return;
        return parseInt(e.getAttribute("data-swiper-autoplay"), 10)
      })();
      !Number.isNaN(r) && r > 0 && void 0 === e && (a = r, g = r, v = r), d = a;
      const n = i.params.speed, o = () => {
        i && !i.destroyed && (i.params.autoplay.reverseDirection ? !i.isBeginning || i.params.loop || i.params.rewind ? (i.slidePrev(n, !0, !0), l("autoplay")) : i.params.autoplay.stopOnLastSlide || (i.slideTo(i.slides.length - 1, n, !0, !0), l("autoplay")) : !i.isEnd || i.params.loop || i.params.rewind ? (i.slideNext(n, !0, !0), l("autoplay")) : i.params.autoplay.stopOnLastSlide || (i.slideTo(0, n, !0, !0), l("autoplay")), i.params.cssMode && (w = (new Date).getTime(), requestAnimationFrame((() => {
          E()
        }))))
      };
      return a > 0 ? (clearTimeout(t), t = setTimeout((() => {
        o()
      }), a)) : requestAnimationFrame((() => {
        o()
      })), a
    }, x = () => {
      i.autoplay.running = !0, E(), l("autoplayStart")
    }, S = () => {
      i.autoplay.running = !1, clearTimeout(t), cancelAnimationFrame(s), l("autoplayStop")
    }, T = (e, s) => {
      if (i.destroyed || !i.autoplay.running) return;
      clearTimeout(t), e || (f = !0);
      const a = () => {
        l("autoplayPause"), i.params.autoplay.waitForTransition ? i.wrapperEl.addEventListener("transitionend", b) : M()
      };
      if (i.autoplay.paused = !0, s) return h && (d = i.params.autoplay.delay), h = !1, void a();
      const r = d || i.params.autoplay.delay;
      d = r - ((new Date).getTime() - w), i.isEnd && d < 0 && !i.params.loop || (d < 0 && (d = 0), a())
    }, M = () => {
      i.isEnd && d < 0 && !i.params.loop || i.destroyed || !i.autoplay.running || (w = (new Date).getTime(), f ? (f = !1, E(d)) : E(), i.autoplay.paused = !1, l("autoplayResume"))
    }, C = () => {
      if (i.destroyed || !i.autoplay.running) return;
      const e = a();
      "hidden" === e.visibilityState && (f = !0, T(!0)), "visible" === e.visibilityState && M()
    }, P = e => {
      "mouse" === e.pointerType && (f = !0, T(!0))
    }, L = e => {
      "mouse" === e.pointerType && i.autoplay.paused && M()
    };
    n("init", (() => {
      i.params.autoplay.enabled && (i.params.autoplay.pauseOnMouseEnter && (i.el.addEventListener("pointerenter", P), i.el.addEventListener("pointerleave", L)), a().addEventListener("visibilitychange", C), w = (new Date).getTime(), x())
    })), n("destroy", (() => {
      i.el.removeEventListener("pointerenter", P), i.el.removeEventListener("pointerleave", L), a().removeEventListener("visibilitychange", C), i.autoplay.running && S()
    })), n("beforeTransitionStart", ((e, t, s) => {
      !i.destroyed && i.autoplay.running && (s || !i.params.autoplay.disableOnInteraction ? T(!0, !0) : S())
    })), n("sliderFirstMove", (() => {
      !i.destroyed && i.autoplay.running && (i.params.autoplay.disableOnInteraction ? S() : (p = !0, u = !1, f = !1, m = setTimeout((() => {
        f = !0, u = !0, T(!0)
      }), 200)))
    })), n("touchEnd", (() => {
      if (!i.destroyed && i.autoplay.running && p) {
        if (clearTimeout(m), clearTimeout(t), i.params.autoplay.disableOnInteraction) return u = !1, void (p = !1);
        u && i.params.cssMode && M(), u = !1, p = !1
      }
    })), n("slideChange", (() => {
      !i.destroyed && i.autoplay.running && (h = !0)
    })), Object.assign(i.autoplay, {start: x, stop: S, pause: T, resume: M})
  }, function (e) {
    let {swiper: t, extendParams: s, on: i} = e;
    s({
      thumbs: {
        swiper: null,
        multipleActiveThumbs: !0,
        autoScrollOffset: 0,
        slideThumbActiveClass: "swiper-slide-thumb-active",
        thumbsContainerClass: "swiper-thumbs"
      }
    });
    let r = !1, n = !1;

    function l() {
      const e = t.thumbs.swiper;
      if (!e || e.destroyed) return;
      const s = e.clickedIndex, a = e.clickedSlide;
      if (a && a.classList.contains(t.params.thumbs.slideThumbActiveClass)) return;
      if (null == s) return;
      let i;
      i = e.params.loop ? parseInt(e.clickedSlide.getAttribute("data-swiper-slide-index"), 10) : s, t.params.loop ? t.slideToLoop(i) : t.slideTo(i)
    }

    function o() {
      const {thumbs: e} = t.params;
      if (r) return !1;
      r = !0;
      const s = t.constructor;
      if (e.swiper instanceof s) t.thumbs.swiper = e.swiper, Object.assign(t.thumbs.swiper.originalParams, {
        watchSlidesProgress: !0,
        slideToClickedSlide: !1
      }), Object.assign(t.thumbs.swiper.params, {
        watchSlidesProgress: !0,
        slideToClickedSlide: !1
      }), t.thumbs.swiper.update(); else if (d(e.swiper)) {
        const a = Object.assign({}, e.swiper);
        Object.assign(a, {watchSlidesProgress: !0, slideToClickedSlide: !1}), t.thumbs.swiper = new s(a), n = !0
      }
      return t.thumbs.swiper.el.classList.add(t.params.thumbs.thumbsContainerClass), t.thumbs.swiper.on("tap", l), !0
    }

    function c(e) {
      const s = t.thumbs.swiper;
      if (!s || s.destroyed) return;
      const a = "auto" === s.params.slidesPerView ? s.slidesPerViewDynamic() : s.params.slidesPerView;
      let i = 1;
      const r = t.params.thumbs.slideThumbActiveClass;
      if (t.params.slidesPerView > 1 && !t.params.centeredSlides && (i = t.params.slidesPerView), t.params.thumbs.multipleActiveThumbs || (i = 1), i = Math.floor(i), s.slides.forEach((e => e.classList.remove(r))), s.params.loop || s.params.virtual && s.params.virtual.enabled) for (let e = 0; e < i; e += 1) f(s.slidesEl, `[data-swiper-slide-index="${t.realIndex + e}"]`).forEach((e => {
        e.classList.add(r)
      })); else for (let e = 0; e < i; e += 1) s.slides[t.realIndex + e] && s.slides[t.realIndex + e].classList.add(r);
      const n = t.params.thumbs.autoScrollOffset, l = n && !s.params.loop;
      if (t.realIndex !== s.realIndex || l) {
        const i = s.activeIndex;
        let r, o;
        if (s.params.loop) {
          const e = s.slides.filter((e => e.getAttribute("data-swiper-slide-index") === `${t.realIndex}`))[0];
          r = s.slides.indexOf(e), o = t.activeIndex > t.previousIndex ? "next" : "prev"
        } else r = t.realIndex, o = r > t.previousIndex ? "next" : "prev";
        l && (r += "next" === o ? n : -1 * n), s.visibleSlidesIndexes && s.visibleSlidesIndexes.indexOf(r) < 0 && (s.params.centeredSlides ? r = r > i ? r - Math.floor(a / 2) + 1 : r + Math.floor(a / 2) - 1 : r > i && s.params.slidesPerGroup, s.slideTo(r, e ? 0 : void 0))
      }
    }

    t.thumbs = {swiper: null}, i("beforeInit", (() => {
      const {thumbs: e} = t.params;
      if (e && e.swiper) if ("string" == typeof e.swiper || e.swiper instanceof HTMLElement) {
        const s = a(), i = () => {
          const a = "string" == typeof e.swiper ? s.querySelector(e.swiper) : e.swiper;
          if (a && a.swiper) e.swiper = a.swiper, o(), c(!0); else if (a) {
            const s = i => {
              e.swiper = i.detail[0], a.removeEventListener("init", s), o(), c(!0), e.swiper.update(), t.update()
            };
            a.addEventListener("init", s)
          }
          return a
        }, r = () => {
          if (t.destroyed) return;
          i() || requestAnimationFrame(r)
        };
        requestAnimationFrame(r)
      } else o(), c(!0)
    })), i("slideChange update resize observerUpdate", (() => {
      c()
    })), i("setTransition", ((e, s) => {
      const a = t.thumbs.swiper;
      a && !a.destroyed && a.setTransition(s)
    })), i("beforeDestroy", (() => {
      const e = t.thumbs.swiper;
      e && !e.destroyed && n && e.destroy()
    })), Object.assign(t.thumbs, {init: o, update: c})
  }, function (e) {
    let {swiper: t, extendParams: s, emit: a, once: i} = e;
    s({
      freeMode: {
        enabled: !1,
        momentum: !0,
        momentumRatio: 1,
        momentumBounce: !0,
        momentumBounceRatio: 1,
        momentumVelocityRatio: 1,
        sticky: !1,
        minimumVelocity: .02
      }
    }), Object.assign(t, {
      freeMode: {
        onTouchStart: function () {
          const e = t.getTranslate();
          t.setTranslate(e), t.setTransition(0), t.touchEventsData.velocities.length = 0, t.freeMode.onTouchEnd({currentPos: t.rtl ? t.translate : -t.translate})
        }, onTouchMove: function () {
          const {touchEventsData: e, touches: s} = t;
          0 === e.velocities.length && e.velocities.push({
            position: s[t.isHorizontal() ? "startX" : "startY"],
            time: e.touchStartTime
          }), e.velocities.push({position: s[t.isHorizontal() ? "currentX" : "currentY"], time: l()})
        }, onTouchEnd: function (e) {
          let {currentPos: s} = e;
          const {params: r, wrapperEl: n, rtlTranslate: o, snapGrid: d, touchEventsData: c} = t,
            p = l() - c.touchStartTime;
          if (s < -t.minTranslate()) t.slideTo(t.activeIndex); else if (s > -t.maxTranslate()) t.slides.length < d.length ? t.slideTo(d.length - 1) : t.slideTo(t.slides.length - 1); else {
            if (r.freeMode.momentum) {
              if (c.velocities.length > 1) {
                const e = c.velocities.pop(), s = c.velocities.pop(), a = e.position - s.position,
                  i = e.time - s.time;
                t.velocity = a / i, t.velocity /= 2, Math.abs(t.velocity) < r.freeMode.minimumVelocity && (t.velocity = 0), (i > 150 || l() - e.time > 300) && (t.velocity = 0)
              } else t.velocity = 0;
              t.velocity *= r.freeMode.momentumVelocityRatio, c.velocities.length = 0;
              let e = 1e3 * r.freeMode.momentumRatio;
              const s = t.velocity * e;
              let p = t.translate + s;
              o && (p = -p);
              let u, m = !1;
              const h = 20 * Math.abs(t.velocity) * r.freeMode.momentumBounceRatio;
              let f;
              if (p < t.maxTranslate()) r.freeMode.momentumBounce ? (p + t.maxTranslate() < -h && (p = t.maxTranslate() - h), u = t.maxTranslate(), m = !0, c.allowMomentumBounce = !0) : p = t.maxTranslate(), r.loop && r.centeredSlides && (f = !0); else if (p > t.minTranslate()) r.freeMode.momentumBounce ? (p - t.minTranslate() > h && (p = t.minTranslate() + h), u = t.minTranslate(), m = !0, c.allowMomentumBounce = !0) : p = t.minTranslate(), r.loop && r.centeredSlides && (f = !0); else if (r.freeMode.sticky) {
                let e;
                for (let t = 0; t < d.length; t += 1) if (d[t] > -p) {
                  e = t;
                  break
                }
                p = Math.abs(d[e] - p) < Math.abs(d[e - 1] - p) || "next" === t.swipeDirection ? d[e] : d[e - 1], p = -p
              }
              if (f && i("transitionEnd", (() => {
                t.loopFix()
              })), 0 !== t.velocity) {
                if (e = o ? Math.abs((-p - t.translate) / t.velocity) : Math.abs((p - t.translate) / t.velocity), r.freeMode.sticky) {
                  const s = Math.abs((o ? -p : p) - t.translate),
                    a = t.slidesSizesGrid[t.activeIndex];
                  e = s < a ? r.speed : s < 2 * a ? 1.5 * r.speed : 2.5 * r.speed
                }
              } else if (r.freeMode.sticky) return void t.slideToClosest();
              r.freeMode.momentumBounce && m ? (t.updateProgress(u), t.setTransition(e), t.setTranslate(p), t.transitionStart(!0, t.swipeDirection), t.animating = !0, E(n, (() => {
                t && !t.destroyed && c.allowMomentumBounce && (a("momentumBounce"), t.setTransition(r.speed), setTimeout((() => {
                  t.setTranslate(u), E(n, (() => {
                    t && !t.destroyed && t.transitionEnd()
                  }))
                }), 0))
              }))) : t.velocity ? (a("_freeModeNoMomentumRelease"), t.updateProgress(p), t.setTransition(e), t.setTranslate(p), t.transitionStart(!0, t.swipeDirection), t.animating || (t.animating = !0, E(n, (() => {
                t && !t.destroyed && t.transitionEnd()
              })))) : t.updateProgress(p), t.updateActiveIndex(), t.updateSlidesClasses()
            } else {
              if (r.freeMode.sticky) return void t.slideToClosest();
              r.freeMode && a("_freeModeNoMomentumRelease")
            }
            (!r.freeMode.momentum || p >= r.longSwipesMs) && (t.updateProgress(), t.updateActiveIndex(), t.updateSlidesClasses())
          }
        }
      }
    })
  }, function (e) {
    let t, s, a, {swiper: i, extendParams: r} = e;
    r({grid: {rows: 1, fill: "column"}}), i.grid = {
      initSlides: e => {
        const {slidesPerView: r} = i.params, {rows: n, fill: l} = i.params.grid;
        s = t / n, a = Math.floor(e / n), t = Math.floor(e / n) === e / n ? e : Math.ceil(e / n) * n, "auto" !== r && "row" === l && (t = Math.max(t, r * n))
      }, updateSlide: (e, r, n, l) => {
        const {slidesPerGroup: o, spaceBetween: d} = i.params, {rows: c, fill: p} = i.params.grid;
        let u, m, h;
        if ("row" === p && o > 1) {
          const s = Math.floor(e / (o * c)), a = e - c * o * s,
            i = 0 === s ? o : Math.min(Math.ceil((n - s * c * o) / c), o);
          h = Math.floor(a / i), m = a - h * i + s * o, u = m + h * t / c, r.style.order = u
        } else "column" === p ? (m = Math.floor(e / c), h = e - m * c, (m > a || m === a && h === c - 1) && (h += 1, h >= c && (h = 0, m += 1))) : (h = Math.floor(e / s), m = e - h * s);
        r.style[l("margin-top")] = 0 !== h ? d && `${d}px` : ""
      }, updateWrapperSize: (e, s, a) => {
        const {spaceBetween: r, centeredSlides: n, roundLengths: l} = i.params, {rows: o} = i.params.grid;
        if (i.virtualSize = (e + r) * t, i.virtualSize = Math.ceil(i.virtualSize / o) - r, i.wrapperEl.style[a("width")] = `${i.virtualSize + r}px`, n) {
          const e = [];
          for (let t = 0; t < s.length; t += 1) {
            let a = s[t];
            l && (a = Math.floor(a)), s[t] < i.virtualSize + s[0] && e.push(a)
          }
          s.splice(0, s.length), s.push(...e)
        }
      }
    }
  }, function (e) {
    let {swiper: t} = e;
    Object.assign(t, {
      appendSlide: te.bind(t),
      prependSlide: se.bind(t),
      addSlide: ae.bind(t),
      removeSlide: ie.bind(t),
      removeAllSlides: re.bind(t)
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({fadeEffect: {crossFade: !1}}), ne({
      effect: "fade",
      swiper: t,
      on: a,
      setTranslate: () => {
        const {slides: e} = t;
        t.params.fadeEffect;
        for (let s = 0; s < e.length; s += 1) {
          const e = t.slides[s];
          let a = -e.swiperSlideOffset;
          t.params.virtualTranslate || (a -= t.translate);
          let i = 0;
          t.isHorizontal() || (i = a, a = 0);
          const r = t.params.fadeEffect.crossFade ? Math.max(1 - Math.abs(e.progress), 0) : 1 + Math.min(Math.max(e.progress, -1), 0),
            n = le(0, e);
          n.style.opacity = r, n.style.transform = `translate3d(${a}px, ${i}px, 0px)`
        }
      },
      setTransition: e => {
        const s = t.slides.map((e => h(e)));
        s.forEach((t => {
          t.style.transitionDuration = `${e}ms`
        })), oe({swiper: t, duration: e, transformElements: s, allSlides: !0})
      },
      overwriteParams: () => ({
        slidesPerView: 1,
        slidesPerGroup: 1,
        watchSlidesProgress: !0,
        spaceBetween: 0,
        virtualTranslate: !t.params.cssMode
      })
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({cubeEffect: {slideShadows: !0, shadow: !0, shadowOffset: 20, shadowScale: .94}});
    const i = (e, t, s) => {
      let a = s ? e.querySelector(".swiper-slide-shadow-left") : e.querySelector(".swiper-slide-shadow-top"),
        i = s ? e.querySelector(".swiper-slide-shadow-right") : e.querySelector(".swiper-slide-shadow-bottom");
      a || (a = g("div", "swiper-slide-shadow-" + (s ? "left" : "top")), e.append(a)), i || (i = g("div", "swiper-slide-shadow-" + (s ? "right" : "bottom")), e.append(i)), a && (a.style.opacity = Math.max(-t, 0)), i && (i.style.opacity = Math.max(t, 0))
    };
    ne({
      effect: "cube",
      swiper: t,
      on: a,
      setTranslate: () => {
        const {el: e, wrapperEl: s, slides: a, width: r, height: n, rtlTranslate: l, size: o, browser: d} = t,
          c = t.params.cubeEffect, p = t.isHorizontal(), u = t.virtual && t.params.virtual.enabled;
        let m, h = 0;
        c.shadow && (p ? (m = t.slidesEl.querySelector(".swiper-cube-shadow"), m || (m = g("div", "swiper-cube-shadow"), t.slidesEl.append(m)), m.style.height = `${r}px`) : (m = e.querySelector(".swiper-cube-shadow"), m || (m = g("div", "swiper-cube-shadow"), e.append(m))));
        for (let e = 0; e < a.length; e += 1) {
          const t = a[e];
          let s = e;
          u && (s = parseInt(t.getAttribute("data-swiper-slide-index"), 10));
          let r = 90 * s, n = Math.floor(r / 360);
          l && (r = -r, n = Math.floor(-r / 360));
          const d = Math.max(Math.min(t.progress, 1), -1);
          let m = 0, f = 0, g = 0;
          s % 4 == 0 ? (m = 4 * -n * o, g = 0) : (s - 1) % 4 == 0 ? (m = 0, g = 4 * -n * o) : (s - 2) % 4 == 0 ? (m = o + 4 * n * o, g = o) : (s - 3) % 4 == 0 && (m = -o, g = 3 * o + 4 * o * n), l && (m = -m), p || (f = m, m = 0);
          const v = `rotateX(${p ? 0 : -r}deg) rotateY(${p ? r : 0}deg) translate3d(${m}px, ${f}px, ${g}px)`;
          d <= 1 && d > -1 && (h = 90 * s + 90 * d, l && (h = 90 * -s - 90 * d)), t.style.transform = v, c.slideShadows && i(t, d, p)
        }
        if (s.style.transformOrigin = `50% 50% -${o / 2}px`, s.style["-webkit-transform-origin"] = `50% 50% -${o / 2}px`, c.shadow) if (p) m.style.transform = `translate3d(0px, ${r / 2 + c.shadowOffset}px, ${-r / 2}px) rotateX(90deg) rotateZ(0deg) scale(${c.shadowScale})`; else {
          const e = Math.abs(h) - 90 * Math.floor(Math.abs(h) / 90),
            t = 1.5 - (Math.sin(2 * e * Math.PI / 360) / 2 + Math.cos(2 * e * Math.PI / 360) / 2),
            s = c.shadowScale, a = c.shadowScale / t, i = c.shadowOffset;
          m.style.transform = `scale3d(${s}, 1, ${a}) translate3d(0px, ${n / 2 + i}px, ${-n / 2 / a}px) rotateX(-90deg)`
        }
        const f = (d.isSafari || d.isWebView) && d.needPerspectiveFix ? -o / 2 : 0;
        s.style.transform = `translate3d(0px,0,${f}px) rotateX(${t.isHorizontal() ? 0 : h}deg) rotateY(${t.isHorizontal() ? -h : 0}deg)`, s.style.setProperty("--swiper-cube-translate-z", `${f}px`)
      },
      setTransition: e => {
        const {el: s, slides: a} = t;
        if (a.forEach((t => {
          t.style.transitionDuration = `${e}ms`, t.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach((t => {
            t.style.transitionDuration = `${e}ms`
          }))
        })), t.params.cubeEffect.shadow && !t.isHorizontal()) {
          const t = s.querySelector(".swiper-cube-shadow");
          t && (t.style.transitionDuration = `${e}ms`)
        }
      },
      recreateShadows: () => {
        const e = t.isHorizontal();
        t.slides.forEach((t => {
          const s = Math.max(Math.min(t.progress, 1), -1);
          i(t, s, e)
        }))
      },
      getEffectParams: () => t.params.cubeEffect,
      perspective: () => !0,
      overwriteParams: () => ({
        slidesPerView: 1,
        slidesPerGroup: 1,
        watchSlidesProgress: !0,
        resistanceRatio: 0,
        spaceBetween: 0,
        centeredSlides: !1,
        virtualTranslate: !0
      })
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({flipEffect: {slideShadows: !0, limitRotation: !0}});
    const i = (e, s, a) => {
      let i = t.isHorizontal() ? e.querySelector(".swiper-slide-shadow-left") : e.querySelector(".swiper-slide-shadow-top"),
        r = t.isHorizontal() ? e.querySelector(".swiper-slide-shadow-right") : e.querySelector(".swiper-slide-shadow-bottom");
      i || (i = de(0, e, t.isHorizontal() ? "left" : "top")), r || (r = de(0, e, t.isHorizontal() ? "right" : "bottom")), i && (i.style.opacity = Math.max(-s, 0)), r && (r.style.opacity = Math.max(s, 0))
    };
    ne({
      effect: "flip",
      swiper: t,
      on: a,
      setTranslate: () => {
        const {slides: e, rtlTranslate: s} = t, a = t.params.flipEffect;
        for (let r = 0; r < e.length; r += 1) {
          const n = e[r];
          let l = n.progress;
          t.params.flipEffect.limitRotation && (l = Math.max(Math.min(n.progress, 1), -1));
          const o = n.swiperSlideOffset;
          let d = -180 * l, c = 0, p = t.params.cssMode ? -o - t.translate : -o, u = 0;
          t.isHorizontal() ? s && (d = -d) : (u = p, p = 0, c = -d, d = 0), n.style.zIndex = -Math.abs(Math.round(l)) + e.length, a.slideShadows && i(n, l);
          const m = `translate3d(${p}px, ${u}px, 0px) rotateX(${c}deg) rotateY(${d}deg)`;
          le(0, n).style.transform = m
        }
      },
      setTransition: e => {
        const s = t.slides.map((e => h(e)));
        s.forEach((t => {
          t.style.transitionDuration = `${e}ms`, t.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach((t => {
            t.style.transitionDuration = `${e}ms`
          }))
        })), oe({swiper: t, duration: e, transformElements: s})
      },
      recreateShadows: () => {
        t.params.flipEffect;
        t.slides.forEach((e => {
          let s = e.progress;
          t.params.flipEffect.limitRotation && (s = Math.max(Math.min(e.progress, 1), -1)), i(e, s)
        }))
      },
      getEffectParams: () => t.params.flipEffect,
      perspective: () => !0,
      overwriteParams: () => ({
        slidesPerView: 1,
        slidesPerGroup: 1,
        watchSlidesProgress: !0,
        spaceBetween: 0,
        virtualTranslate: !t.params.cssMode
      })
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({coverflowEffect: {rotate: 50, stretch: 0, depth: 100, scale: 1, modifier: 1, slideShadows: !0}}), ne({
      effect: "coverflow", swiper: t, on: a, setTranslate: () => {
        const {width: e, height: s, slides: a, slidesSizesGrid: i} = t, r = t.params.coverflowEffect,
          n = t.isHorizontal(), l = t.translate, o = n ? e / 2 - l : s / 2 - l, d = n ? r.rotate : -r.rotate,
          c = r.depth;
        for (let e = 0, t = a.length; e < t; e += 1) {
          const t = a[e], s = i[e], l = (o - t.swiperSlideOffset - s / 2) / s,
            p = "function" == typeof r.modifier ? r.modifier(l) : l * r.modifier;
          let u = n ? d * p : 0, m = n ? 0 : d * p, h = -c * Math.abs(p), f = r.stretch;
          "string" == typeof f && -1 !== f.indexOf("%") && (f = parseFloat(r.stretch) / 100 * s);
          let g = n ? 0 : f * p, v = n ? f * p : 0, w = 1 - (1 - r.scale) * Math.abs(p);
          Math.abs(v) < .001 && (v = 0), Math.abs(g) < .001 && (g = 0), Math.abs(h) < .001 && (h = 0), Math.abs(u) < .001 && (u = 0), Math.abs(m) < .001 && (m = 0), Math.abs(w) < .001 && (w = 0);
          const b = `translate3d(${v}px,${g}px,${h}px)  rotateX(${m}deg) rotateY(${u}deg) scale(${w})`;
          if (le(0, t).style.transform = b, t.style.zIndex = 1 - Math.abs(Math.round(p)), r.slideShadows) {
            let e = n ? t.querySelector(".swiper-slide-shadow-left") : t.querySelector(".swiper-slide-shadow-top"),
              s = n ? t.querySelector(".swiper-slide-shadow-right") : t.querySelector(".swiper-slide-shadow-bottom");
            e || (e = de(0, t, n ? "left" : "top")), s || (s = de(0, t, n ? "right" : "bottom")), e && (e.style.opacity = p > 0 ? p : 0), s && (s.style.opacity = -p > 0 ? -p : 0)
          }
        }
      }, setTransition: e => {
        t.slides.map((e => h(e))).forEach((t => {
          t.style.transitionDuration = `${e}ms`, t.querySelectorAll(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").forEach((t => {
            t.style.transitionDuration = `${e}ms`
          }))
        }))
      }, perspective: () => !0, overwriteParams: () => ({watchSlidesProgress: !0})
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({
      creativeEffect: {
        limitProgress: 1,
        shadowPerProgress: !1,
        progressMultiplier: 1,
        perspective: !0,
        prev: {translate: [0, 0, 0], rotate: [0, 0, 0], opacity: 1, scale: 1},
        next: {translate: [0, 0, 0], rotate: [0, 0, 0], opacity: 1, scale: 1}
      }
    });
    const i = e => "string" == typeof e ? e : `${e}px`;
    ne({
      effect: "creative",
      swiper: t,
      on: a,
      setTranslate: () => {
        const {slides: e, wrapperEl: s, slidesSizesGrid: a} = t,
          r = t.params.creativeEffect, {progressMultiplier: n} = r, l = t.params.centeredSlides;
        if (l) {
          const e = a[0] / 2 - t.params.slidesOffsetBefore || 0;
          s.style.transform = `translateX(calc(50% - ${e}px))`
        }
        for (let s = 0; s < e.length; s += 1) {
          const a = e[s], o = a.progress,
            d = Math.min(Math.max(a.progress, -r.limitProgress), r.limitProgress);
          let c = d;
          l || (c = Math.min(Math.max(a.originalProgress, -r.limitProgress), r.limitProgress));
          const p = a.swiperSlideOffset, u = [t.params.cssMode ? -p - t.translate : -p, 0, 0], m = [0, 0, 0];
          let h = !1;
          t.isHorizontal() || (u[1] = u[0], u[0] = 0);
          let f = {translate: [0, 0, 0], rotate: [0, 0, 0], scale: 1, opacity: 1};
          d < 0 ? (f = r.next, h = !0) : d > 0 && (f = r.prev, h = !0), u.forEach(((e, t) => {
            u[t] = `calc(${e}px + (${i(f.translate[t])} * ${Math.abs(d * n)}))`
          })), m.forEach(((e, t) => {
            m[t] = f.rotate[t] * Math.abs(d * n)
          })), a.style.zIndex = -Math.abs(Math.round(o)) + e.length;
          const g = u.join(", "), v = `rotateX(${m[0]}deg) rotateY(${m[1]}deg) rotateZ(${m[2]}deg)`,
            w = c < 0 ? `scale(${1 + (1 - f.scale) * c * n})` : `scale(${1 - (1 - f.scale) * c * n})`,
            b = c < 0 ? 1 + (1 - f.opacity) * c * n : 1 - (1 - f.opacity) * c * n,
            y = `translate3d(${g}) ${v} ${w}`;
          if (h && f.shadow || !h) {
            let e = a.querySelector(".swiper-slide-shadow");
            if (!e && f.shadow && (e = de(0, a)), e) {
              const t = r.shadowPerProgress ? d * (1 / r.limitProgress) : d;
              e.style.opacity = Math.min(Math.max(Math.abs(t), 0), 1)
            }
          }
          const E = le(0, a);
          E.style.transform = y, E.style.opacity = b, f.origin && (E.style.transformOrigin = f.origin)
        }
      },
      setTransition: e => {
        const s = t.slides.map((e => h(e)));
        s.forEach((t => {
          t.style.transitionDuration = `${e}ms`, t.querySelectorAll(".swiper-slide-shadow").forEach((t => {
            t.style.transitionDuration = `${e}ms`
          }))
        })), oe({swiper: t, duration: e, transformElements: s, allSlides: !0})
      },
      perspective: () => t.params.creativeEffect.perspective,
      overwriteParams: () => ({watchSlidesProgress: !0, virtualTranslate: !t.params.cssMode})
    })
  }, function (e) {
    let {swiper: t, extendParams: s, on: a} = e;
    s({cardsEffect: {slideShadows: !0, rotate: !0, perSlideRotate: 2, perSlideOffset: 8}}), ne({
      effect: "cards",
      swiper: t,
      on: a,
      setTranslate: () => {
        const {slides: e, activeIndex: s} = t,
          a = t.params.cardsEffect, {startTranslate: i, isTouched: r} = t.touchEventsData, n = t.translate;
        for (let l = 0; l < e.length; l += 1) {
          const o = e[l], d = o.progress, c = Math.min(Math.max(d, -4), 4);
          let p = o.swiperSlideOffset;
          t.params.centeredSlides && !t.params.cssMode && (t.wrapperEl.style.transform = `translateX(${t.minTranslate()}px)`), t.params.centeredSlides && t.params.cssMode && (p -= e[0].swiperSlideOffset);
          let u = t.params.cssMode ? -p - t.translate : -p, m = 0;
          const h = -100 * Math.abs(c);
          let f = 1, g = -a.perSlideRotate * c, v = a.perSlideOffset - .75 * Math.abs(c);
          const w = t.virtual && t.params.virtual.enabled ? t.virtual.from + l : l,
            b = (w === s || w === s - 1) && c > 0 && c < 1 && (r || t.params.cssMode) && n < i,
            y = (w === s || w === s + 1) && c < 0 && c > -1 && (r || t.params.cssMode) && n > i;
          if (b || y) {
            const e = (1 - Math.abs((Math.abs(c) - .5) / .5)) ** .5;
            g += -28 * c * e, f += -.5 * e, v += 96 * e, m = -25 * e * Math.abs(c) + "%"
          }
          if (u = c < 0 ? `calc(${u}px + (${v * Math.abs(c)}%))` : c > 0 ? `calc(${u}px + (-${v * Math.abs(c)}%))` : `${u}px`, !t.isHorizontal()) {
            const e = m;
            m = u, u = e
          }
          const E = c < 0 ? "" + (1 + (1 - f) * c) : "" + (1 - (1 - f) * c),
            x = `\n        translate3d(${u}, ${m}, ${h}px)\n        rotateZ(${a.rotate ? g : 0}deg)\n        scale(${E})\n      `;
          if (a.slideShadows) {
            let e = o.querySelector(".swiper-slide-shadow");
            e || (e = de(0, o)), e && (e.style.opacity = Math.min(Math.max((Math.abs(c) - .5) / .5, 0), 1))
          }
          o.style.zIndex = -Math.abs(Math.round(d)) + e.length;
          le(0, o).style.transform = x
        }
      },
      setTransition: e => {
        const s = t.slides.map((e => h(e)));
        s.forEach((t => {
          t.style.transitionDuration = `${e}ms`, t.querySelectorAll(".swiper-slide-shadow").forEach((t => {
            t.style.transitionDuration = `${e}ms`
          }))
        })), oe({swiper: t, duration: e, transformElements: s})
      },
      perspective: () => !0,
      overwriteParams: () => ({watchSlidesProgress: !0, virtualTranslate: !t.params.cssMode})
    })
  }];
  return Q.use(ce), Q
}));
//# sourceMappingURL=swiper-bundle.min.js.map
/*!
 * Datepicker v1.0.10
 * https://fengyuanchen.github.io/datepicker
 *
 * Copyright 2014-present Chen Fengyuan
 * Released under the MIT license
 *
 * Date: 2020-09-29T14:46:10.983Z
 */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('jquery')) :
    typeof define === 'function' && define.amd ? define(['jquery'], factory) :
      (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.jQuery));
}(this, (function ($) {
  'use strict';

  $ = $ && $.hasOwnProperty('default') ? $['default'] : $;

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }


  var d = new Date();
  d.setFullYear(d.getFullYear() - 18);

  var DEFAULTS = {
    // Show the datepicker automatically when initialized
    autoShow: false,
    // Hide the datepicker automatically when picked
    autoHide: false,
    // Pick the initial date automatically when initialized
    autoPick: false,
    // Enable inline mode
    inline: false,
    // A element (or selector) for putting the datepicker
    container: null,
    // A element (or selector) for triggering the datepicker
    trigger: null,
    // The ISO language code (built-in: en-US)
    language: '',
    // The date string format
    format: 'dd-mm-yyyy',
    // The initial date
    date: null,
    // The start view date
    startDate: null,
    // The end view date
//    endDate: '01-01-2006',
    endDate: d.toLocaleDateString().replace(".", "-").replace(".", "-"),
    // The start view when initialized
    startView: 0,
    // 0 for days, 1 for months, 2 for years
    // The start day of the week
    // 0 for Sunday, 1 for Monday, 2 for Tuesday, 3 for Wednesday,
    // 4 for Thursday, 5 for Friday, 6 for Saturday
    weekStart: 0,
    // Show year before month on the datepicker header
    yearFirst: false,
    // A string suffix to the year number.
    yearSuffix: '',
    // Days' name of the week.
    days: ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'Пʼятниця', 'Субота'],
    daysShort: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    daysMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    months: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
    monthsShort: ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'],
    // A element tag for each item of years, months and days
    itemTag: 'li',
    // A class (CSS) for muted date item
    mutedClass: 'muted',
    // A class (CSS) for picked date item
    pickedClass: 'picked',
    // A class (CSS) for disabled date item
    disabledClass: 'disabled',
    // A class (CSS) for highlight date item
    highlightedClass: 'highlighted',
    // The template of the datepicker
    template: '<div class="datepicker-container">' + '<div class="datepicker-panel" data-view="years picker">' + '<ul>' + '<li data-view="years prev">&lsaquo;</li>' + '<li data-view="years current"></li>' + '<li data-view="years next">&rsaquo;</li>' + '</ul>' + '<ul data-view="years"></ul>' + '</div>' + '<div class="datepicker-panel" data-view="months picker">' + '<ul>' + '<li data-view="year prev">&lsaquo;</li>' + '<li data-view="year current"></li>' + '<li data-view="year next">&rsaquo;</li>' + '</ul>' + '<ul data-view="months"></ul>' + '</div>' + '<div class="datepicker-panel" data-view="days picker">' + '<ul>' + '<li data-view="month prev">&lsaquo;</li>' + '<li data-view="month current"></li>' + '<li data-view="month next">&rsaquo;</li>' + '</ul>' + '<ul data-view="week"></ul>' + '<ul data-view="days"></ul>' + '</div>' + '</div>',
    // The offset top or bottom of the datepicker from the element
    offset: 10,
    // The `z-index` of the datepicker
    zIndex: 1000,
    // Filter each date item (return `false` to disable a date item)
    filter: null,
    // Event shortcuts
    show: null,
    hide: null,
    pick: null
  };

  var IS_BROWSER = typeof window !== 'undefined';
  var WINDOW = IS_BROWSER ? window : {};
  var IS_TOUCH_DEVICE = IS_BROWSER ? 'ontouchstart' in WINDOW.document.documentElement : false;
  var NAMESPACE = 'datepicker';
  var EVENT_CLICK = "click.".concat(NAMESPACE);
  var EVENT_FOCUS = "focus.".concat(NAMESPACE);
  var EVENT_HIDE = "hide.".concat(NAMESPACE);
  var EVENT_KEYUP = "keyup.".concat(NAMESPACE);
  var EVENT_PICK = "pick.".concat(NAMESPACE);
  var EVENT_RESIZE = "resize.".concat(NAMESPACE);
  var EVENT_SCROLL = "scroll.".concat(NAMESPACE);
  var EVENT_SHOW = "show.".concat(NAMESPACE);
  var EVENT_TOUCH_START = "touchstart.".concat(NAMESPACE);
  var CLASS_HIDE = "".concat(NAMESPACE, "-hide");
  var LANGUAGES = {};
  var VIEWS = {
    DAYS: 0,
    MONTHS: 1,
    YEARS: 2
  };

  var toString = Object.prototype.toString;

  function typeOf(obj) {
    return toString.call(obj).slice(8, -1).toLowerCase();
  }

  function isString(value) {
    return typeof value === 'string';
  }

  var isNaN = Number.isNaN || WINDOW.isNaN;

  function isNumber(value) {
    return typeof value === 'number' && !isNaN(value);
  }

  function isUndefined(value) {
    return typeof value === 'undefined';
  }

  function isDate(value) {
    return typeOf(value) === 'date' && !isNaN(value.getTime());
  }

  function proxy(fn, context) {
    for (var _len = arguments.length, args = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
      args[_key - 2] = arguments[_key];
    }

    return function () {
      for (var _len2 = arguments.length, args2 = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        args2[_key2] = arguments[_key2];
      }

      return fn.apply(context, args.concat(args2));
    };
  }

  function selectorOf(view) {
    return "[data-view=\"".concat(view, "\"]");
  }

  function isLeapYear(year) {
    return year % 4 === 0 && year % 100 !== 0 || year % 400 === 0;
  }

  function getDaysInMonth(year, month) {
    return [31, isLeapYear(year) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
  }

  function getMinDay(year, month, day) {
    return Math.min(day, getDaysInMonth(year, month));
  }

  var formatParts = /(y|m|d)+/g;

  function parseFormat(format) {
    var source = String(format).toLowerCase();
    var parts = source.match(formatParts);

    if (!parts || parts.length === 0) {
      throw new Error('Invalid date format.');
    }

    format = {
      source: source,
      parts: parts
    };
    $.each(parts, function (i, part) {
      switch (part) {
        case 'dd':
        case 'd':
          format.hasDay = true;
          break;

        case 'mm':
        case 'm':
          format.hasMonth = true;
          break;

        case 'yyyy':
        case 'yy':
          format.hasYear = true;
          break;
      }
    });
    return format;
  }

  function getScrollParent(element) {
    var includeHidden = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var $element = $(element);
    var position = $element.css('position');
    var excludeStaticParent = position === 'absolute';
    var overflowRegex = includeHidden ? /auto|scroll|hidden/ : /auto|scroll/;
    var scrollParent = $element.parents().filter(function (index, parent) {
      var $parent = $(parent);

      if (excludeStaticParent && $parent.css('position') === 'static') {
        return false;
      }

      return overflowRegex.test($parent.css('overflow') + $parent.css('overflow-y') + $parent.css('overflow-x'));
    }).eq(0);
    return position === 'fixed' || !scrollParent.length ? $(element.ownerDocument || document) : scrollParent;
  }

  /**
   * Add leading zeroes to the given value
   * @param {number} value - The value to add.
   * @param {number} [length=1] - The expected value length.
   * @returns {string} Returns converted value.
   */

  function addLeadingZero(value) {
    var length = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
    var str = String(Math.abs(value));
    var i = str.length;
    var result = '';

    if (value < 0) {
      result += '-';
    }

    while (i < length) {
      i += 1;
      result += '0';
    }

    return result + str;
  }

  var REGEXP_DIGITS = /\d+/g;
  var methods = {
    // Show the datepicker
    show: function show() {
      if (!this.built) {
        this.build();
      }

      if (this.shown) {
        return;
      }

      if (this.trigger(EVENT_SHOW).isDefaultPrevented()) {
        return;
      }

      this.shown = true;
      this.$picker.removeClass(CLASS_HIDE).on(EVENT_CLICK, $.proxy(this.click, this));
      this.showView(this.options.startView);

      if (!this.inline) {
        this.$scrollParent.on(EVENT_SCROLL, $.proxy(this.place, this));
        $(window).on(EVENT_RESIZE, this.onResize = proxy(this.place, this));
        $(document).on(EVENT_CLICK, this.onGlobalClick = proxy(this.globalClick, this));
        $(document).on(EVENT_KEYUP, this.onGlobalKeyup = proxy(this.globalKeyup, this));

        if (IS_TOUCH_DEVICE) {
          $(document).on(EVENT_TOUCH_START, this.onTouchStart = proxy(this.touchstart, this));
        }

        this.place();
      }
    },
    // Hide the datepicker
    hide: function hide() {
      if (!this.shown) {
        return;
      }

      if (this.trigger(EVENT_HIDE).isDefaultPrevented()) {
        return;
      }

      this.shown = false;
      this.$picker.addClass(CLASS_HIDE).off(EVENT_CLICK, this.click);

      if (!this.inline) {
        this.$scrollParent.off(EVENT_SCROLL, this.place);
        $(window).off(EVENT_RESIZE, this.onResize);
        $(document).off(EVENT_CLICK, this.onGlobalClick);
        $(document).off(EVENT_KEYUP, this.onGlobalKeyup);

        if (IS_TOUCH_DEVICE) {
          $(document).off(EVENT_TOUCH_START, this.onTouchStart);
        }
      }
    },
    toggle: function toggle() {
      if (this.shown) {
        this.hide();
      } else {
        this.show();
      }
    },
    // Update the datepicker with the current input value
    update: function update() {
      var value = this.getValue();

      if (value === this.oldValue) {
        return;
      }

      this.setDate(value, true);
      this.oldValue = value;
    },

    /**
     * Pick the current date to the element
     *
     * @param {String} _view (private)
     */
    pick: function pick(_view) {
      var $this = this.$element;
      var date = this.date;

      if (this.trigger(EVENT_PICK, {
        view: _view || '',
        date: date
      }).isDefaultPrevented()) {
        return;
      }

      date = this.formatDate(this.date);
      this.setValue(date);

      if (this.isInput) {
        $this.trigger('input');
        $this.trigger('change');
      }
    },
    // Reset the datepicker
    reset: function reset() {
      this.setDate(this.initialDate, true);
      this.setValue(this.initialValue);

      if (this.shown) {
        this.showView(this.options.startView);
      }
    },

    /**
     * Get the month name with given argument or the current date
     *
     * @param {Number} month (optional)
     * @param {Boolean} shortForm (optional)
     * @return {String} (month name)
     */
    getMonthName: function getMonthName(month, shortForm) {
      var options = this.options;
      var monthsShort = options.monthsShort;
      var months = options.months;

      if ($.isNumeric(month)) {
        month = Number(month);
      } else if (isUndefined(shortForm)) {
        shortForm = month;
      }

      if (shortForm === true) {
        months = monthsShort;
      }

      return months[isNumber(month) ? month : this.date.getMonth()];
    },

    /**
     * Get the day name with given argument or the current date
     *
     * @param {Number} day (optional)
     * @param {Boolean} shortForm (optional)
     * @param {Boolean} min (optional)
     * @return {String} (day name)
     */
    getDayName: function getDayName(day, shortForm, min) {
      var options = this.options;
      var days = options.days;

      if ($.isNumeric(day)) {
        day = Number(day);
      } else {
        if (isUndefined(min)) {
          min = shortForm;
        }

        if (isUndefined(shortForm)) {
          shortForm = day;
        }
      }

      if (min) {
        days = options.daysMin;
      } else if (shortForm) {
        days = options.daysShort;
      }

      return days[isNumber(day) ? day : this.date.getDay()];
    },

    /**
     * Get the current date
     *
     * @param {Boolean} formatted (optional)
     * @return {Date|String} (date)
     */
    getDate: function getDate(formatted) {
      var date = this.date;
      return formatted ? this.formatDate(date) : new Date(date);
    },

    /**
     * Set the current date with a new date
     *
     * @param {Date} date
     * @param {Boolean} _updated (private)
     */
    setDate: function setDate(date, _updated) {
      var filter = this.options.filter;

      if (isDate(date) || isString(date)) {
        date = this.parseDate(date);

        if ($.isFunction(filter) && filter.call(this.$element, date, 'day') === false) {
          return;
        }

        this.date = date;
        this.viewDate = new Date(date);

        if (!_updated) {
          this.pick();
        }

        if (this.built) {
          this.render();
        }
      }
    },

    /**
     * Set the start view date with a new date
     *
     * @param {Date|string|null} date
     */
    setStartDate: function setStartDate(date) {
      if (isDate(date) || isString(date)) {
        this.startDate = this.parseDate(date);
      } else {
        this.startDate = null;
      }

      if (this.built) {
        this.render();
      }
    },

    /**
     * Set the end view date with a new date
     *
     * @param {Date|string|null} date
     */
    setEndDate: function setEndDate(date) {
      if (isDate(date) || isString(date)) {
        this.endDate = this.parseDate(date);
      } else {
        this.endDate = null;
      }

      if (this.built) {
        this.render();
      }
    },

    /**
     * Parse a date string with the set date format
     *
     * @param {String} date
     * @return {Date} (parsed date)
     */
    parseDate: function parseDate(date) {
      var format = this.format;
      var parts = [];

      if (!isDate(date)) {
        if (isString(date)) {
          parts = date.match(REGEXP_DIGITS) || [];
        }

        date = date ? new Date(date) : new Date();

        if (!isDate(date)) {
          date = new Date();
        }

        if (parts.length === format.parts.length) {
          // Set year and month first
          $.each(parts, function (i, part) {
            var value = parseInt(part, 10);

            switch (format.parts[i]) {
              case 'yy':
                date.setFullYear(2000 + value);
                break;

              case 'yyyy':
                // Converts 2-digit year to 2000+
                date.setFullYear(part.length === 2 ? 2000 + value : value);
                break;

              case 'mm':
              case 'm':
                date.setMonth(value - 1);
                break;
            }
          }); // Set day in the last to avoid converting `31/10/2019` to `01/10/2019`

          $.each(parts, function (i, part) {
            var value = parseInt(part, 10);

            switch (format.parts[i]) {
              case 'dd':
              case 'd':
                date.setDate(value);
                break;
            }
          });
        }
      } // Ignore hours, minutes, seconds and milliseconds to avoid side effect (#192)


      return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    },

    /**
     * Format a date object to a string with the set date format
     *
     * @param {Date} date
     * @return {String} (formatted date)
     */
    formatDate: function formatDate(date) {
      var format = this.format;
      var formatted = '';

      if (isDate(date)) {
        var year = date.getFullYear();
        var month = date.getMonth();
        var day = date.getDate();
        var values = {
          d: day,
          dd: addLeadingZero(day, 2),
          m: month + 1,
          mm: addLeadingZero(month + 1, 2),
          yy: String(year).substring(2),
          yyyy: addLeadingZero(year, 4)
        };
        formatted = format.source;
        $.each(format.parts, function (i, part) {
          formatted = formatted.replace(part, values[part]);
        });
      }

      return formatted;
    },
    // Destroy the datepicker and remove the instance from the target element
    destroy: function destroy() {
      this.unbind();
      this.unbuild();
      this.$element.removeData(NAMESPACE);
    }
  };

  var handlers = {
    click: function click(e) {
      var $target = $(e.target);
      var options = this.options,
        date = this.date,
        viewDate = this.viewDate,
        format = this.format;
      e.stopPropagation();
      e.preventDefault();

      if ($target.hasClass('disabled')) {
        return;
      }

      var view = $target.data('view');
      var viewYear = viewDate.getFullYear();
      var viewMonth = viewDate.getMonth();
      var viewDay = viewDate.getDate();

      switch (view) {
        case 'years prev':
        case 'years next': {
          viewYear = view === 'years prev' ? viewYear - 10 : viewYear + 10;
          viewDate.setFullYear(viewYear);
          viewDate.setDate(getMinDay(viewYear, viewMonth, viewDay));
          this.renderYears();
          break;
        }

        case 'year prev':
        case 'year next':
          viewYear = view === 'year prev' ? viewYear - 1 : viewYear + 1;
          viewDate.setFullYear(viewYear);
          viewDate.setDate(getMinDay(viewYear, viewMonth, viewDay));
          this.renderMonths();
          break;

        case 'year current':
          if (format.hasYear) {
            this.showView(VIEWS.YEARS);
          }

          break;

        case 'year picked':
          if (format.hasMonth) {
            this.showView(VIEWS.MONTHS);
          } else {
            $target.siblings(".".concat(options.pickedClass)).removeClass(options.pickedClass).data('view', 'year');
            this.hideView();
          }

          this.pick('year');
          break;

        case 'year':
          viewYear = parseInt($target.text(), 10); // Set date first to avoid month changing (#195)

          date.setDate(getMinDay(viewYear, viewMonth, viewDay));
          date.setFullYear(viewYear);
          viewDate.setDate(getMinDay(viewYear, viewMonth, viewDay));
          viewDate.setFullYear(viewYear);

          if (format.hasMonth) {
            this.showView(VIEWS.MONTHS);
          } else {
            $target.addClass(options.pickedClass).data('view', 'year picked').siblings(".".concat(options.pickedClass)).removeClass(options.pickedClass).data('view', 'year');
            this.hideView();
          }

          this.pick('year');
          break;

        case 'month prev':
        case 'month next':
          viewMonth = view === 'month prev' ? viewMonth - 1 : viewMonth + 1;

          if (viewMonth < 0) {
            viewYear -= 1;
            viewMonth += 12;
          } else if (viewMonth > 11) {
            viewYear += 1;
            viewMonth -= 12;
          }

          viewDate.setFullYear(viewYear);
          viewDate.setDate(getMinDay(viewYear, viewMonth, viewDay));
          viewDate.setMonth(viewMonth);
          this.renderDays();
          break;

        case 'month current':
          if (format.hasMonth) {
            this.showView(VIEWS.MONTHS);
          }

          break;

        case 'month picked':
          if (format.hasDay) {
            this.showView(VIEWS.DAYS);
          } else {
            $target.siblings(".".concat(options.pickedClass)).removeClass(options.pickedClass).data('view', 'month');
            this.hideView();
          }

          this.pick('month');
          break;

        case 'month':
          viewMonth = $.inArray($target.text(), options.monthsShort);
          date.setFullYear(viewYear); // Set date before month to avoid month changing (#195)

          date.setDate(getMinDay(viewYear, viewMonth, viewDay));
          date.setMonth(viewMonth);
          viewDate.setFullYear(viewYear);
          viewDate.setDate(getMinDay(viewYear, viewMonth, viewDay));
          viewDate.setMonth(viewMonth);

          if (format.hasDay) {
            this.showView(VIEWS.DAYS);
          } else {
            $target.addClass(options.pickedClass).data('view', 'month picked').siblings(".".concat(options.pickedClass)).removeClass(options.pickedClass).data('view', 'month');
            this.hideView();
          }

          this.pick('month');
          break;

        case 'day prev':
        case 'day next':
        case 'day':
          if (view === 'day prev') {
            viewMonth -= 1;
          } else if (view === 'day next') {
            viewMonth += 1;
          }

          viewDay = parseInt($target.text(), 10); // Set date to 1 to avoid month changing (#195)

          date.setDate(1);
          date.setFullYear(viewYear);
          date.setMonth(viewMonth);
          date.setDate(viewDay);
          viewDate.setDate(1);
          viewDate.setFullYear(viewYear);
          viewDate.setMonth(viewMonth);
          viewDate.setDate(viewDay);
          this.renderDays();

          if (view === 'day') {
            this.hideView();
          }

          this.pick('day');
          break;

        case 'day picked':
          this.hideView();
          this.pick('day');
          break;
      }
    },
    globalClick: function globalClick(_ref) {
      var target = _ref.target;
      var element = this.element,
        $trigger = this.$trigger;
      var trigger = $trigger[0];
      var hidden = true;

      while (target !== document) {
        if (target === trigger || target === element) {
          hidden = false;
          break;
        }

        target = target.parentNode;
      }

      if (hidden) {
        this.hide();
      }
    },
    keyup: function keyup() {
      this.update();
    },
    globalKeyup: function globalKeyup(_ref2) {
      var target = _ref2.target,
        key = _ref2.key,
        keyCode = _ref2.keyCode;

      if (this.isInput && target !== this.element && this.shown && (key === 'Tab' || keyCode === 9)) {
        this.hide();
      }
    },
    touchstart: function touchstart(_ref3) {
      var target = _ref3.target;

      // Emulate click in touch devices to support hiding the picker automatically (#197).
      if (this.isInput && target !== this.element && !$.contains(this.$picker[0], target)) {
        this.hide();
        this.element.blur();
      }
    }
  };

  var render = {
    render: function render() {
      this.renderYears();
      this.renderMonths();
      this.renderDays();
    },
    renderWeek: function renderWeek() {
      var _this = this;

      var items = [];
      var _this$options = this.options,
        weekStart = _this$options.weekStart,
        daysMin = _this$options.daysMin;
      weekStart = parseInt(weekStart, 10) % 7;
      daysMin = daysMin.slice(weekStart).concat(daysMin.slice(0, weekStart));
      $.each(daysMin, function (i, day) {
        items.push(_this.createItem({
          text: day
        }));
      });
      this.$week.html(items.join(''));
    },
    renderYears: function renderYears() {
      var options = this.options,
        startDate = this.startDate,
        endDate = this.endDate;
      var disabledClass = options.disabledClass,
        filter = options.filter,
        yearSuffix = options.yearSuffix;
      var viewYear = this.viewDate.getFullYear();
      var now = new Date();
      var thisYear = now.getFullYear();
      var year = this.date.getFullYear();
      var start = -5;
      var end = 6;
      var items = [];
      var prevDisabled = false;
      var nextDisabled = false;
      var i;

      for (i = start; i <= end; i += 1) {
        var date = new Date(viewYear + i, 1, 1);
        var disabled = false;

        if (startDate) {
          disabled = date.getFullYear() < startDate.getFullYear();

          if (i === start) {
            prevDisabled = disabled;
          }
        }

        if (!disabled && endDate) {
          disabled = date.getFullYear() > endDate.getFullYear();

          if (i === end) {
            nextDisabled = disabled;
          }
        }

        if (!disabled && filter) {
          disabled = filter.call(this.$element, date, 'year') === false;
        }

        var picked = viewYear + i === year;
        var view = picked ? 'year picked' : 'year';
        items.push(this.createItem({
          picked: picked,
          disabled: disabled,
          text: viewYear + i,
          view: disabled ? 'year disabled' : view,
          highlighted: date.getFullYear() === thisYear
        }));
      }

      this.$yearsPrev.toggleClass(disabledClass, prevDisabled);
      this.$yearsNext.toggleClass(disabledClass, nextDisabled);
      this.$yearsCurrent.toggleClass(disabledClass, true).html("".concat(viewYear + start + yearSuffix, " - ").concat(viewYear + end).concat(yearSuffix));
      this.$years.html(items.join(''));
    },
    renderMonths: function renderMonths() {
      var options = this.options,
        startDate = this.startDate,
        endDate = this.endDate,
        viewDate = this.viewDate;
      var disabledClass = options.disabledClass || '';
      var months = options.monthsShort;
      var filter = $.isFunction(options.filter) && options.filter;
      var viewYear = viewDate.getFullYear();
      var now = new Date();
      var thisYear = now.getFullYear();
      var thisMonth = now.getMonth();
      var year = this.date.getFullYear();
      var month = this.date.getMonth();
      var items = [];
      var prevDisabled = false;
      var nextDisabled = false;
      var i;

      for (i = 0; i <= 11; i += 1) {
        var date = new Date(viewYear, i, 1);
        var disabled = false;

        if (startDate) {
          prevDisabled = date.getFullYear() === startDate.getFullYear();
          disabled = prevDisabled && date.getMonth() < startDate.getMonth();
        }

        if (!disabled && endDate) {
          nextDisabled = date.getFullYear() === endDate.getFullYear();
          disabled = nextDisabled && date.getMonth() > endDate.getMonth();
        }

        if (!disabled && filter) {
          disabled = filter.call(this.$element, date, 'month') === false;
        }

        var picked = viewYear === year && i === month;
        var view = picked ? 'month picked' : 'month';
        items.push(this.createItem({
          disabled: disabled,
          picked: picked,
          highlighted: viewYear === thisYear && date.getMonth() === thisMonth,
          index: i,
          text: months[i],
          view: disabled ? 'month disabled' : view
        }));
      }

      this.$yearPrev.toggleClass(disabledClass, prevDisabled);
      this.$yearNext.toggleClass(disabledClass, nextDisabled);
      this.$yearCurrent.toggleClass(disabledClass, prevDisabled && nextDisabled).html(viewYear + options.yearSuffix || '');
      this.$months.html(items.join(''));
    },
    renderDays: function renderDays() {
      var $element = this.$element,
        options = this.options,
        startDate = this.startDate,
        endDate = this.endDate,
        viewDate = this.viewDate,
        currentDate = this.date;
      var disabledClass = options.disabledClass,
        filter = options.filter,
        months = options.months,
        weekStart = options.weekStart,
        yearSuffix = options.yearSuffix;
      var viewYear = viewDate.getFullYear();
      var viewMonth = viewDate.getMonth();
      var now = new Date();
      var thisYear = now.getFullYear();
      var thisMonth = now.getMonth();
      var thisDay = now.getDate();
      var year = currentDate.getFullYear();
      var month = currentDate.getMonth();
      var day = currentDate.getDate();
      var length;
      var i;
      var n; // Days of prev month
      // -----------------------------------------------------------------------

      var prevItems = [];
      var prevViewYear = viewYear;
      var prevViewMonth = viewMonth;
      var prevDisabled = false;

      if (viewMonth === 0) {
        prevViewYear -= 1;
        prevViewMonth = 11;
      } else {
        prevViewMonth -= 1;
      } // The length of the days of prev month


      length = getDaysInMonth(prevViewYear, prevViewMonth); // The first day of current month

      var firstDay = new Date(viewYear, viewMonth, 1); // The visible length of the days of prev month
      // [0,1,2,3,4,5,6] - [0,1,2,3,4,5,6] => [-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6]

      n = firstDay.getDay() - parseInt(weekStart, 10) % 7; // [-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6] => [1,2,3,4,5,6,7]

      if (n <= 0) {
        n += 7;
      }

      if (startDate) {
        prevDisabled = firstDay.getTime() <= startDate.getTime();
      }

      for (i = length - (n - 1); i <= length; i += 1) {
        var prevViewDate = new Date(prevViewYear, prevViewMonth, i);
        var disabled = false;

        if (startDate) {
          disabled = prevViewDate.getTime() < startDate.getTime();
        }

        if (!disabled && filter) {
          disabled = filter.call($element, prevViewDate, 'day') === false;
        }

        prevItems.push(this.createItem({
          disabled: disabled,
          highlighted: prevViewYear === thisYear && prevViewMonth === thisMonth && prevViewDate.getDate() === thisDay,
          muted: true,
          picked: prevViewYear === year && prevViewMonth === month && i === day,
          text: i,
          view: 'day prev'
        }));
      } // Days of next month
      // -----------------------------------------------------------------------


      var nextItems = [];
      var nextViewYear = viewYear;
      var nextViewMonth = viewMonth;
      var nextDisabled = false;

      if (viewMonth === 11) {
        nextViewYear += 1;
        nextViewMonth = 0;
      } else {
        nextViewMonth += 1;
      } // The length of the days of current month


      length = getDaysInMonth(viewYear, viewMonth); // The visible length of next month (42 means 6 rows and 7 columns)

      n = 42 - (prevItems.length + length); // The last day of current month

      var lastDate = new Date(viewYear, viewMonth, length);

      if (endDate) {
        nextDisabled = lastDate.getTime() >= endDate.getTime();
      }

      for (i = 1; i <= n; i += 1) {
        var date = new Date(nextViewYear, nextViewMonth, i);
        var picked = nextViewYear === year && nextViewMonth === month && i === day;
        var _disabled = false;

        if (endDate) {
          _disabled = date.getTime() > endDate.getTime();
        }

        if (!_disabled && filter) {
          _disabled = filter.call($element, date, 'day') === false;
        }

        nextItems.push(this.createItem({
          disabled: _disabled,
          picked: picked,
          highlighted: nextViewYear === thisYear && nextViewMonth === thisMonth && date.getDate() === thisDay,
          muted: true,
          text: i,
          view: 'day next'
        }));
      } // Days of current month
      // -----------------------------------------------------------------------


      var items = [];

      for (i = 1; i <= length; i += 1) {
        var _date = new Date(viewYear, viewMonth, i);

        var _disabled2 = false;

        if (startDate) {
          _disabled2 = _date.getTime() < startDate.getTime();
        }

        if (!_disabled2 && endDate) {
          _disabled2 = _date.getTime() > endDate.getTime();
        }

        if (!_disabled2 && filter) {
          _disabled2 = filter.call($element, _date, 'day') === false;
        }

        var _picked = viewYear === year && viewMonth === month && i === day;

        var view = _picked ? 'day picked' : 'day';
        items.push(this.createItem({
          disabled: _disabled2,
          picked: _picked,
          highlighted: viewYear === thisYear && viewMonth === thisMonth && _date.getDate() === thisDay,
          text: i,
          view: _disabled2 ? 'day disabled' : view
        }));
      } // Render days picker
      // -----------------------------------------------------------------------


      this.$monthPrev.toggleClass(disabledClass, prevDisabled);
      this.$monthNext.toggleClass(disabledClass, nextDisabled);
      this.$monthCurrent.toggleClass(disabledClass, prevDisabled && nextDisabled).html(options.yearFirst ? "".concat(viewYear + yearSuffix, " ").concat(months[viewMonth]) : "".concat(months[viewMonth], " ").concat(viewYear).concat(yearSuffix));
      this.$days.html(prevItems.join('') + items.join('') + nextItems.join(''));
    }
  };

  var CLASS_TOP_LEFT = "".concat(NAMESPACE, "-top-left");
  var CLASS_TOP_RIGHT = "".concat(NAMESPACE, "-top-right");
  var CLASS_BOTTOM_LEFT = "".concat(NAMESPACE, "-bottom-left");
  var CLASS_BOTTOM_RIGHT = "".concat(NAMESPACE, "-bottom-right");
  var CLASS_PLACEMENTS = [CLASS_TOP_LEFT, CLASS_TOP_RIGHT, CLASS_BOTTOM_LEFT, CLASS_BOTTOM_RIGHT].join(' ');

  var Datepicker = /*#__PURE__*/function () {
    function Datepicker(element) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      _classCallCheck(this, Datepicker);

      this.$element = $(element);
      this.element = element;
      this.options = $.extend({}, DEFAULTS, LANGUAGES[options.language], $.isPlainObject(options) && options);
      this.$scrollParent = getScrollParent(element, true);
      this.built = false;
      this.shown = false;
      this.isInput = false;
      this.inline = false;
      this.initialValue = '';
      this.initialDate = null;
      this.startDate = null;
      this.endDate = null;
      this.init();
    }

    _createClass(Datepicker, [{
      key: "init",
      value: function init() {
        var $this = this.$element,
          options = this.options;
        var startDate = options.startDate,
          endDate = options.endDate,
          date = options.date;
        this.$trigger = $(options.trigger);
        this.isInput = $this.is('input') || $this.is('textarea');
        this.inline = options.inline && (options.container || !this.isInput);
        this.format = parseFormat(options.format);
        var initialValue = this.getValue();
        this.initialValue = initialValue;
        this.oldValue = initialValue;
        date = this.parseDate(date || initialValue);

        if (startDate) {
          startDate = this.parseDate(startDate);

          if (date.getTime() < startDate.getTime()) {
            date = new Date(startDate);
          }

          this.startDate = startDate;
        }

        if (endDate) {
          endDate = this.parseDate(endDate);

          if (startDate && endDate.getTime() < startDate.getTime()) {
            endDate = new Date(startDate);
          }

          if (date.getTime() > endDate.getTime()) {
            date = new Date(endDate);
          }

          this.endDate = endDate;
        }

        this.date = date;
        this.viewDate = new Date(date);
        this.initialDate = new Date(this.date);
        this.bind();

        if (options.autoShow || this.inline) {
          this.show();
        }

        if (options.autoPick) {
          this.pick();
        }
      }
    }, {
      key: "build",
      value: function build() {
        if (this.built) {
          return;
        }

        this.built = true;
        var $this = this.$element,
          options = this.options;
        var $picker = $(options.template);
        this.$picker = $picker;
        this.$week = $picker.find(selectorOf('week')); // Years view

        this.$yearsPicker = $picker.find(selectorOf('years picker'));
        this.$yearsPrev = $picker.find(selectorOf('years prev'));
        this.$yearsNext = $picker.find(selectorOf('years next'));
        this.$yearsCurrent = $picker.find(selectorOf('years current'));
        this.$years = $picker.find(selectorOf('years')); // Months view

        this.$monthsPicker = $picker.find(selectorOf('months picker'));
        this.$yearPrev = $picker.find(selectorOf('year prev'));
        this.$yearNext = $picker.find(selectorOf('year next'));
        this.$yearCurrent = $picker.find(selectorOf('year current'));
        this.$months = $picker.find(selectorOf('months')); // Days view

        this.$daysPicker = $picker.find(selectorOf('days picker'));
        this.$monthPrev = $picker.find(selectorOf('month prev'));
        this.$monthNext = $picker.find(selectorOf('month next'));
        this.$monthCurrent = $picker.find(selectorOf('month current'));
        this.$days = $picker.find(selectorOf('days'));

        if (this.inline) {
          $(options.container || $this).append($picker.addClass("".concat(NAMESPACE, "-inline")));
        } else {
          $(document.body).append($picker.addClass("".concat(NAMESPACE, "-dropdown")));
          $picker.addClass(CLASS_HIDE).css({
            zIndex: parseInt(options.zIndex, 10)
          });
        }

        this.renderWeek();
      }
    }, {
      key: "unbuild",
      value: function unbuild() {
        if (!this.built) {
          return;
        }

        this.built = false;
        this.$picker.remove();
      }
    }, {
      key: "bind",
      value: function bind() {
        var options = this.options,
          $this = this.$element;

        if ($.isFunction(options.show)) {
          $this.on(EVENT_SHOW, options.show);
        }

        if ($.isFunction(options.hide)) {
          $this.on(EVENT_HIDE, options.hide);
        }

        if ($.isFunction(options.pick)) {
          $this.on(EVENT_PICK, options.pick);
        }

        if (this.isInput) {
          $this.on(EVENT_KEYUP, $.proxy(this.keyup, this));
        }

        if (!this.inline) {
          if (options.trigger) {
            this.$trigger.on(EVENT_CLICK, $.proxy(this.toggle, this));
          } else if (this.isInput) {
            $this.on(EVENT_FOCUS, $.proxy(this.show, this));
          } else {
            $this.on(EVENT_CLICK, $.proxy(this.show, this));
          }
        }
      }
    }, {
      key: "unbind",
      value: function unbind() {
        var $this = this.$element,
          options = this.options;

        if ($.isFunction(options.show)) {
          $this.off(EVENT_SHOW, options.show);
        }

        if ($.isFunction(options.hide)) {
          $this.off(EVENT_HIDE, options.hide);
        }

        if ($.isFunction(options.pick)) {
          $this.off(EVENT_PICK, options.pick);
        }

        if (this.isInput) {
          $this.off(EVENT_KEYUP, this.keyup);
        }

        if (!this.inline) {
          if (options.trigger) {
            this.$trigger.off(EVENT_CLICK, this.toggle);
          } else if (this.isInput) {
            $this.off(EVENT_FOCUS, this.show);
          } else {
            $this.off(EVENT_CLICK, this.show);
          }
        }
      }
    }, {
      key: "showView",
      value: function showView(view) {
        var $yearsPicker = this.$yearsPicker,
          $monthsPicker = this.$monthsPicker,
          $daysPicker = this.$daysPicker,
          format = this.format;

        if (format.hasYear || format.hasMonth || format.hasDay) {
          switch (Number(view)) {
            case VIEWS.YEARS:
              $monthsPicker.addClass(CLASS_HIDE);
              $daysPicker.addClass(CLASS_HIDE);

              if (format.hasYear) {
                this.renderYears();
                $yearsPicker.removeClass(CLASS_HIDE);
                this.place();
              } else {
                this.showView(VIEWS.DAYS);
              }

              break;

            case VIEWS.MONTHS:
              $yearsPicker.addClass(CLASS_HIDE);
              $daysPicker.addClass(CLASS_HIDE);

              if (format.hasMonth) {
                this.renderMonths();
                $monthsPicker.removeClass(CLASS_HIDE);
                this.place();
              } else {
                this.showView(VIEWS.YEARS);
              }

              break;
            // case VIEWS.DAYS:

            default:
              $yearsPicker.addClass(CLASS_HIDE);
              $monthsPicker.addClass(CLASS_HIDE);

              if (format.hasDay) {
                this.renderDays();
                $daysPicker.removeClass(CLASS_HIDE);
                this.place();
              } else {
                this.showView(VIEWS.MONTHS);
              }

          }
        }
      }
    }, {
      key: "hideView",
      value: function hideView() {
        if (!this.inline && this.options.autoHide) {
          this.hide();
        }
      }
    }, {
      key: "place",
      value: function place() {
        if (this.inline) {
          return;
        }

        var $this = this.$element,
          options = this.options,
          $picker = this.$picker;
        var containerWidth = $(document).outerWidth();
        var containerHeight = $(document).outerHeight();
        var elementWidth = $this.outerWidth();
        var elementHeight = $this.outerHeight();
        var width = $picker.width();
        var height = $picker.height();

        var _$this$offset = $this.offset(),
          left = _$this$offset.left,
          top = _$this$offset.top;

        var offset = parseFloat(options.offset);
        var placement = CLASS_TOP_LEFT;

        if (isNaN(offset)) {
          offset = 10;
        }

        if (top > height && top + elementHeight + height > containerHeight) {
          top -= height + offset;
          placement = CLASS_BOTTOM_LEFT;
        } else {
          top += elementHeight + offset;
        }

        if (left + width > containerWidth) {
          left += elementWidth - width;
          placement = placement.replace('left', 'right');
        }

        $picker.removeClass(CLASS_PLACEMENTS).addClass(placement).css({
          top: top,
          left: left
        });
      } // A shortcut for triggering custom events

    }, {
      key: "trigger",
      value: function trigger(type, data) {
        var e = $.Event(type, data);
        this.$element.trigger(e);
        return e;
      }
    }, {
      key: "createItem",
      value: function createItem(data) {
        var options = this.options;
        var itemTag = options.itemTag;
        var item = {
          text: '',
          view: '',
          muted: false,
          picked: false,
          disabled: false,
          highlighted: false
        };
        var classes = [];
        $.extend(item, data);

        if (item.muted) {
          classes.push(options.mutedClass);
        }

        if (item.highlighted) {
          classes.push(options.highlightedClass);
        }

        if (item.picked) {
          classes.push(options.pickedClass);
        }

        if (item.disabled) {
          classes.push(options.disabledClass);
        }

        return "<".concat(itemTag, " class=\"").concat(classes.join(' '), "\" data-view=\"").concat(item.view, "\">").concat(item.text, "</").concat(itemTag, ">");
      }
    }, {
      key: "getValue",
      value: function getValue() {
        var $this = this.$element;
        return this.isInput ? $this.val() : $this.text();
      }
    }, {
      key: "setValue",
      value: function setValue() {
        var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
        var $this = this.$element;

        if (this.isInput) {
          $this.val(value);
        } else if (!this.inline || this.options.container) {
          $this.text(value);
        }
      }
    }], [{
      key: "setDefaults",
      value: function setDefaults() {
        var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        $.extend(DEFAULTS, LANGUAGES[options.language], $.isPlainObject(options) && options);
      }
    }]);

    return Datepicker;
  }();

  if ($.extend) {
    $.extend(Datepicker.prototype, render, handlers, methods);
  }

  if ($.fn) {
    var AnotherDatepicker = $.fn.datepicker;

    $.fn.datepicker = function jQueryDatepicker(option) {
      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        args[_key - 1] = arguments[_key];
      }

      var result;
      this.each(function (i, element) {
        var $element = $(element);
        var isDestroy = option === 'destroy';
        var datepicker = $element.data(NAMESPACE);

        if (!datepicker) {
          if (isDestroy) {
            return;
          }

          var options = $.extend({}, $element.data(), $.isPlainObject(option) && option);
          datepicker = new Datepicker(element, options);
          $element.data(NAMESPACE, datepicker);
        }

        if (isString(option)) {
          var fn = datepicker[option];

          if ($.isFunction(fn)) {
            result = fn.apply(datepicker, args);

            if (isDestroy) {
              $element.removeData(NAMESPACE);
            }
          }
        }
      });
      return !isUndefined(result) ? result : this;
    };

    $.fn.datepicker.Constructor = Datepicker;
    $.fn.datepicker.languages = LANGUAGES;
    $.fn.datepicker.setDefaults = Datepicker.setDefaults;

    $.fn.datepicker.noConflict = function noConflict() {
      $.fn.datepicker = AnotherDatepicker;
      return this;
    };
  }

})));
/**
 *  Ajax Autocomplete for jQuery, version 1.4.11
 *  (c) 2017 Tomas Kirda
 *
 *  Ajax Autocomplete for jQuery is freely distributable under the terms of an MIT-style license.
 *  For details, see the web site: https://github.com/devbridge/jQuery-Autocomplete
 */
!function (t) {
  "use strict";
  "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof exports && "function" == typeof require ? t(require("jquery")) : t(jQuery)
}(function (t) {
  "use strict";
  var e = {
    escapeRegExChars: function (t) {
      return t.replace(/[|\\{}()[\]^$+*?.]/g, "\\$&")
    }, createNode: function (t) {
      var e = document.createElement("div");
      return e.className = t, e.style.position = "absolute", e.style.display = "none", e
    }
  }, s = 27, i = 9, n = 13, o = 38, a = 39, u = 40, l = t.noop;

  function r(e, s) {
    this.element = e, this.el = t(e), this.suggestions = [], this.badQueries = [], this.selectedIndex = -1, this.currentValue = this.element.value, this.timeoutId = null, this.cachedResponse = {}, this.onChangeTimeout = null, this.onChange = null, this.isLocal = !1, this.suggestionsContainer = null, this.noSuggestionsContainer = null, this.options = t.extend(!0, {}, r.defaults, s), this.classes = {
      selected: "autocomplete-selected",
      suggestion: "autocomplete-suggestion"
    }, this.hint = null, this.hintValue = "", this.selection = null, this.initialize(), this.setOptions(s)
  }

  r.utils = e, t.Autocomplete = r, r.defaults = {
    ajaxSettings: {},
    autoSelectFirst: !1,
    appendTo: "body",
    serviceUrl: null,
    lookup: null,
    onSelect: null,
    onHint: null,
    width: "auto",
    minChars: 1,
    maxHeight: 300,
    deferRequestBy: 0,
    params: {},
    formatResult: function (t, s) {
      if (!s) return t.value;
      var i = "(" + e.escapeRegExChars(s) + ")";
      return t.value.replace(new RegExp(i, "gi"), "<strong>$1</strong>").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/&lt;(\/?strong)&gt;/g, "<$1>")
    },
    formatGroup: function (t, e) {
      return '<div class="autocomplete-group">' + e + "</div>"
    },
    delimiter: null,
    zIndex: 9999,
    type: "GET",
    noCache: !1,
    onSearchStart: l,
    onSearchComplete: l,
    onSearchError: l,
    preserveInput: !1,
    containerClass: "autocomplete-suggestions",
    tabDisabled: !1,
    dataType: "text",
    currentRequest: null,
    triggerSelectOnValidInput: !0,
    preventBadQueries: !0,
    lookupFilter: function (t, e, s) {
      return -1 !== t.value.toLowerCase().indexOf(s)
    },
    paramName: "query",
    transformResult: function (e) {
      return "string" == typeof e ? t.parseJSON(e) : e
    },
    showNoSuggestionNotice: !1,
    noSuggestionNotice: "No results",
    orientation: "bottom",
    forceFixPosition: !1
  }, r.prototype = {
    initialize: function () {
      var e, s = this, i = "." + s.classes.suggestion, n = s.classes.selected, o = s.options;
      s.element.setAttribute("autocomplete", "off"), s.noSuggestionsContainer = t('<div class="autocomplete-no-suggestion"></div>').html(this.options.noSuggestionNotice).get(0), s.suggestionsContainer = r.utils.createNode(o.containerClass), (e = t(s.suggestionsContainer)).appendTo(o.appendTo || "body"), "auto" !== o.width && e.css("width", o.width), e.on("mouseover.autocomplete", i, function () {
        s.activate(t(this).data("index"))
      }), e.on("mouseout.autocomplete", function () {
        s.selectedIndex = -1, e.children("." + n).removeClass(n)
      }), e.on("click.autocomplete", i, function () {
        s.select(t(this).data("index"))
      }), e.on("click.autocomplete", function () {
        clearTimeout(s.blurTimeoutId)
      }), s.fixPositionCapture = function () {
        s.visible && s.fixPosition()
      }, t(window).on("resize.autocomplete", s.fixPositionCapture), s.el.on("keydown.autocomplete", function (t) {
        s.onKeyPress(t)
      }), s.el.on("keyup.autocomplete", function (t) {
        s.onKeyUp(t)
      }), s.el.on("blur.autocomplete", function () {
        s.onBlur()
      }), s.el.on("focus.autocomplete", function () {
        s.onFocus()
      }), s.el.on("change.autocomplete", function (t) {
        s.onKeyUp(t)
      }), s.el.on("input.autocomplete", function (t) {
        s.onKeyUp(t)
      })
    }, onFocus: function () {
      this.disabled || (this.fixPosition(), this.el.val().length >= this.options.minChars && this.onValueChange())
    }, onBlur: function () {
      var e = this, s = e.options, i = e.el.val(), n = e.getQuery(i);
      e.blurTimeoutId = setTimeout(function () {
        e.hide(), e.selection && e.currentValue !== n && (s.onInvalidateSelection || t.noop).call(e.element)
      }, 200)
    }, abortAjax: function () {
      this.currentRequest && (this.currentRequest.abort(), this.currentRequest = null)
    }, setOptions: function (e) {
      var s = t.extend({}, this.options, e);
      this.isLocal = Array.isArray(s.lookup), this.isLocal && (s.lookup = this.verifySuggestionsFormat(s.lookup)), s.orientation = this.validateOrientation(s.orientation, "bottom"), t(this.suggestionsContainer).css({
        "max-height": s.maxHeight + "px",
        width: s.width + "px",
        "z-index": s.zIndex
      }), this.options = s
    }, clearCache: function () {
      this.cachedResponse = {}, this.badQueries = []
    }, clear: function () {
      this.clearCache(), this.currentValue = "", this.suggestions = []
    }, disable: function () {
      this.disabled = !0, clearTimeout(this.onChangeTimeout), this.abortAjax()
    }, enable: function () {
      this.disabled = !1
    }, fixPosition: function () {
      var e = t(this.suggestionsContainer), s = e.parent().get(0);
      if (s === document.body || this.options.forceFixPosition) {
        var i = this.options.orientation, n = e.outerHeight(), o = this.el.outerHeight(), a = this.el.offset(),
          u = {top: a.top, left: a.left};
        if ("auto" === i) {
          var l = t(window).height(), r = t(window).scrollTop(), h = -r + a.top - n,
            c = r + l - (a.top + o + n);
          i = Math.max(h, c) === h ? "top" : "bottom"
        }
        if (u.top += "top" === i ? -n : o, s !== document.body) {
          var g, d = e.css("opacity");
          this.visible || e.css("opacity", 0).show(), g = e.offsetParent().offset(), u.top -= g.top, u.top += s.scrollTop, u.left -= g.left, this.visible || e.css("opacity", d).hide()
        }
        "auto" === this.options.width && (u.width = this.el.outerWidth() + "px"), e.css(u)
      }
    }, isCursorAtEnd: function () {
      var t, e = this.el.val().length, s = this.element.selectionStart;
      return "number" == typeof s ? s === e : !document.selection || ((t = document.selection.createRange()).moveStart("character", -e), e === t.text.length)
    }, onKeyPress: function (t) {
      if (this.disabled || this.visible || t.which !== u || !this.currentValue) {
        if (!this.disabled && this.visible) {
          switch (t.which) {
            case s:
              this.el.val(this.currentValue), this.hide();
              break;
            case a:
              if (this.hint && this.options.onHint && this.isCursorAtEnd()) {
                this.selectHint();
                break
              }
              return;
            case i:
              if (this.hint && this.options.onHint) return void this.selectHint();
              if (-1 === this.selectedIndex) return void this.hide();
              if (this.select(this.selectedIndex), !1 === this.options.tabDisabled) return;
              break;
            case n:
              if (-1 === this.selectedIndex) return void this.hide();
              this.select(this.selectedIndex);
              break;
            case o:
              this.moveUp();
              break;
            case u:
              this.moveDown();
              break;
            default:
              return
          }
          t.stopImmediatePropagation(), t.preventDefault()
        }
      } else this.suggest()
    }, onKeyUp: function (t) {
      var e = this;
      if (!e.disabled) {
        switch (t.which) {
          case o:
          case u:
            return
        }
        clearTimeout(e.onChangeTimeout), e.currentValue !== e.el.val() && (e.findBestHint(), e.options.deferRequestBy > 0 ? e.onChangeTimeout = setTimeout(function () {
          e.onValueChange()
        }, e.options.deferRequestBy) : e.onValueChange())
      }
    }, onValueChange: function () {
      if (this.ignoreValueChange) this.ignoreValueChange = !1; else {
        var e = this.options, s = this.el.val(), i = this.getQuery(s);
        this.selection && this.currentValue !== i && (this.selection = null, (e.onInvalidateSelection || t.noop).call(this.element)), clearTimeout(this.onChangeTimeout), this.currentValue = s, this.selectedIndex = -1, e.triggerSelectOnValidInput && this.isExactMatch(i) ? this.select(0) : i.length < e.minChars ? this.hide() : this.getSuggestions(i)
      }
    }, isExactMatch: function (t) {
      var e = this.suggestions;
      return 1 === e.length && e[0].value.toLowerCase() === t.toLowerCase()
    }, getQuery: function (e) {
      var s, i = this.options.delimiter;
      return i ? (s = e.split(i), t.trim(s[s.length - 1])) : e
    }, getSuggestionsLocal: function (e) {
      var s, i = this.options, n = e.toLowerCase(), o = i.lookupFilter, a = parseInt(i.lookupLimit, 10);
      return s = {
        suggestions: t.grep(i.lookup, function (t) {
          return o(t, e, n)
        })
      }, a && s.suggestions.length > a && (s.suggestions = s.suggestions.slice(0, a)), s
    }, getSuggestions: function (e) {
      var s, i, n, o, a = this, u = a.options, l = u.serviceUrl;
      u.params[u.paramName] = e, !1 !== u.onSearchStart.call(a.element, u.params) && (i = u.ignoreParams ? null : u.params, t.isFunction(u.lookup) ? u.lookup(e, function (t) {
        a.suggestions = t.suggestions, a.suggest(), u.onSearchComplete.call(a.element, e, t.suggestions)
      }) : (a.isLocal ? s = a.getSuggestionsLocal(e) : (t.isFunction(l) && (l = l.call(a.element, e)), n = l + "?" + t.param(i || {}), s = a.cachedResponse[n]), s && Array.isArray(s.suggestions) ? (a.suggestions = s.suggestions, a.suggest(), u.onSearchComplete.call(a.element, e, s.suggestions)) : a.isBadQuery(e) ? u.onSearchComplete.call(a.element, e, []) : (a.abortAjax(), o = {
        url: l,
        data: i,
        type: u.type,
        dataType: u.dataType
      }, t.extend(o, u.ajaxSettings), a.currentRequest = t.ajax(o).done(function (t) {
        var s;
        a.currentRequest = null, s = u.transformResult(t, e), a.processResponse(s, e, n), u.onSearchComplete.call(a.element, e, s.suggestions)
      }).fail(function (t, s, i) {
        u.onSearchError.call(a.element, e, t, s, i)
      }))))
    }, isBadQuery: function (t) {
      if (!this.options.preventBadQueries) return !1;
      for (var e = this.badQueries, s = e.length; s--;) if (0 === t.indexOf(e[s])) return !0;
      return !1
    }, hide: function () {
      var e = t(this.suggestionsContainer);
      t.isFunction(this.options.onHide) && this.visible && this.options.onHide.call(this.element, e), this.visible = !1, this.selectedIndex = -1, clearTimeout(this.onChangeTimeout), t(this.suggestionsContainer).hide(), this.onHint(null)
    }, suggest: function () {
      if (this.suggestions.length) {
        var e, s = this.options, i = s.groupBy, n = s.formatResult, o = this.getQuery(this.currentValue),
          a = this.classes.suggestion, u = this.classes.selected, l = t(this.suggestionsContainer),
          r = t(this.noSuggestionsContainer), h = s.beforeRender, c = "";
        s.triggerSelectOnValidInput && this.isExactMatch(o) ? this.select(0) : (t.each(this.suggestions, function (t, u) {
          i && (c += function (t, n) {
            var o = t.data[i];
            return e === o ? "" : (e = o, s.formatGroup(t, e))
          }(u, 0)), c += '<div class="' + a + '" data-index="' + t + '">' + n(u, o, t) + "</div>"
        }), this.adjustContainerWidth(), r.detach(), l.html(c), t.isFunction(h) && h.call(this.element, l, this.suggestions), this.fixPosition(), l.show(), s.autoSelectFirst && (this.selectedIndex = 0, l.scrollTop(0), l.children("." + a).first().addClass(u)), this.visible = !0, this.findBestHint())
      } else this.options.showNoSuggestionNotice ? this.noSuggestions() : this.hide()
    }, noSuggestions: function () {
      var e = this.options.beforeRender, s = t(this.suggestionsContainer), i = t(this.noSuggestionsContainer);
      this.adjustContainerWidth(), i.detach(), s.empty(), s.append(i), t.isFunction(e) && e.call(this.element, s, this.suggestions), this.fixPosition(), s.show(), this.visible = !0
    }, adjustContainerWidth: function () {
      var e, s = this.options, i = t(this.suggestionsContainer);
      "auto" === s.width ? (e = this.el.outerWidth(), i.css("width", e > 0 ? e : 300)) : "flex" === s.width && i.css("width", "")
    }, findBestHint: function () {
      var e = this.el.val().toLowerCase(), s = null;
      e && (t.each(this.suggestions, function (t, i) {
        var n = 0 === i.value.toLowerCase().indexOf(e);
        return n && (s = i), !n
      }), this.onHint(s))
    }, onHint: function (e) {
      var s = this.options.onHint, i = "";
      e && (i = this.currentValue + e.value.substr(this.currentValue.length)), this.hintValue !== i && (this.hintValue = i, this.hint = e, t.isFunction(s) && s.call(this.element, i))
    }, verifySuggestionsFormat: function (e) {
      return e.length && "string" == typeof e[0] ? t.map(e, function (t) {
        return {value: t, data: null}
      }) : e
    }, validateOrientation: function (e, s) {
      return e = t.trim(e || "").toLowerCase(), -1 === t.inArray(e, ["auto", "bottom", "top"]) && (e = s), e
    }, processResponse: function (t, e, s) {
      var i = this.options;
      t.suggestions = this.verifySuggestionsFormat(t.suggestions), i.noCache || (this.cachedResponse[s] = t, i.preventBadQueries && !t.suggestions.length && this.badQueries.push(e)), e === this.getQuery(this.currentValue) && (this.suggestions = t.suggestions, this.suggest())
    }, activate: function (e) {
      var s, i = this.classes.selected, n = t(this.suggestionsContainer),
        o = n.find("." + this.classes.suggestion);
      return n.find("." + i).removeClass(i), this.selectedIndex = e, -1 !== this.selectedIndex && o.length > this.selectedIndex ? (s = o.get(this.selectedIndex), t(s).addClass(i), s) : null
    }, selectHint: function () {
      var e = t.inArray(this.hint, this.suggestions);
      this.select(e)
    }, select: function (t) {
      this.hide(), this.onSelect(t)
    }, moveUp: function () {
      if (-1 !== this.selectedIndex) return 0 === this.selectedIndex ? (t(this.suggestionsContainer).children("." + this.classes.suggestion).first().removeClass(this.classes.selected), this.selectedIndex = -1, this.ignoreValueChange = !1, this.el.val(this.currentValue), void this.findBestHint()) : void this.adjustScroll(this.selectedIndex - 1)
    }, moveDown: function () {
      this.selectedIndex !== this.suggestions.length - 1 && this.adjustScroll(this.selectedIndex + 1)
    }, adjustScroll: function (e) {
      var s = this.activate(e);
      if (s) {
        var i, n, o, a = t(s).outerHeight();
        i = s.offsetTop, o = (n = t(this.suggestionsContainer).scrollTop()) + this.options.maxHeight - a, i < n ? t(this.suggestionsContainer).scrollTop(i) : i > o && t(this.suggestionsContainer).scrollTop(i - this.options.maxHeight + a), this.options.preserveInput || (this.ignoreValueChange = !0, this.el.val(this.getValue(this.suggestions[e].value))), this.onHint(null)
      }
    }, onSelect: function (e) {
      var s = this.options.onSelect, i = this.suggestions[e];
      this.currentValue = this.getValue(i.value), this.currentValue === this.el.val() || this.options.preserveInput || this.el.val(this.currentValue), this.onHint(null), this.suggestions = [], this.selection = i, t.isFunction(s) && s.call(this.element, i)
    }, getValue: function (t) {
      var e, s, i = this.options.delimiter;
      return i ? 1 === (s = (e = this.currentValue).split(i)).length ? t : e.substr(0, e.length - s[s.length - 1].length) + t : t
    }, dispose: function () {
      this.el.off(".autocomplete").removeData("autocomplete"), t(window).off("resize.autocomplete", this.fixPositionCapture), t(this.suggestionsContainer).remove()
    }
  }, t.fn.devbridgeAutocomplete = function (e, s) {
    return arguments.length ? this.each(function () {
      var i = t(this), n = i.data("autocomplete");
      "string" == typeof e ? n && "function" == typeof n[e] && n[e](s) : (n && n.dispose && n.dispose(), n = new r(this, e), i.data("autocomplete", n))
    }) : this.first().data("autocomplete")
  }, t.fn.autocomplete || (t.fn.autocomplete = t.fn.devbridgeAutocomplete)
});


const {Mask, MaskInput, vMaska} = Maska

$(document).ready(function () {
  //mask
  $('.phone-mask').on('keyup', function (e) {
    detectedMobileErrors($(this))
  });

  if ($('.phone-mask').length) {
    new MaskInput('.phone-mask', {
      mask: "+38(0A#) ###-##-##",
      tokens: {
        A: {pattern: /[1-9]/,}
      }
    });
  }


  $('body').delegate('.js-phone-input', 'keyup', function () {
    let val = $(this).val().replace(/-/g, '').replace(/_/g, '');
    if (val.length === 16) {

      if (!detectedMobileCode($(this).val())) {
        $(".js-btn-send").attr("disabled", "disabled");
      } else {
        $(".js-btn-send").removeAttr("disabled");
        $('.js-phone-input').removeClass('error')
      }
    } else {
      $(".js-btn-send").attr("disabled", "disabled");
    }

    detectedMobileErrors($(this))
  });

  // Checkout: тот же контроль кода оператора для телефона клиента и получателя
  $('body').on('keyup input blur', '#customer_telephone, #shipping_address_receiver_phone', function () {
    detectedMobileErrors($(this));
  });


  $(window).scroll(function () {
    if ($(this).scrollTop() > 500) {
      $(".anchor-top").fadeIn(400);
    } else {
      $(".anchor-top").fadeOut(400);
    }
  });

  if ($(this).scrollTop() > 500) {
    $(".anchor-top").fadeIn();
  }

  $(".js-anchor").on("click", "a", function (event) {
    event.preventDefault();
    var id = $(this).attr('href'),
      top = $(id).offset().top;
    $('body,html').animate({scrollTop: top}, 1000);
  });


  // $('.phone-mask').mask('+38 (999) 999-99-99');

  // $(".phone-mask").mask("+38 (999) 999-99-99");

  $('.datepicker').datepicker();


  // custom select
  // $('.custom-dropdown .dropdown-header').on('click', function () {
  //     $(this).closest('.custom-dropdown').toggleClass('open');
  // });

  $('.custom-dropdown .dropdown-header').on('click', function () {
    let $currentDropdown = $(this).closest('.custom-dropdown');

    // Закрываем все кроме текущего
    $('.custom-dropdown').not($currentDropdown).removeClass('open');

    // Переключаем открытие текущего
    $currentDropdown.toggleClass('open');
  });

  $('.custom-dropdown .dropdown-options li').on('click', function () {
    let value = $(this).text();
    $(this).closest('.custom-dropdown').find('.selected-option').text(value);
    $(this).parent().find('li').removeClass('selected');
    $(this).addClass('selected');
    $(this).closest('.custom-dropdown').removeClass('open');
  });



  $(document).on('click', '.custom-dropdown-img .dropdown-header', function (e) {
    e.preventDefault();
    e.stopPropagation();
    let $dropdown = $(this).closest('.custom-dropdown-img');
    $('.custom-dropdown-img').not($dropdown).removeClass('open');
    $dropdown.toggleClass('open');
  });

  $(document).on('click', '.custom-dropdown-img .dropdown-list li', function (e) {
    if ($(this).hasClass('js-vapors-punch-option') || $(this).closest('.custom-dropdown-img--gift').length) {
      return;
    }

    let selectedValue = $(this).data('value');
    $(this).parent().find('li').removeClass('selected');
    $(this).addClass('selected');
    $(this).parent().siblings('.dropdown-header').html($(this).html());
    $(this).parent().siblings('input').val(selectedValue);
    $(this).closest('.custom-dropdown-img').removeClass('open');
  });


  // custom select close
  $(document).mouseup(function (e) {
    let container = $(".custom-dropdown");
    let containerOne = $(".dropdown-multiple-one");
    let containerTwo = $(".dropdown-multiple-two");
    if (container.has(e.target).length === 0) {
      container.removeClass('open');
    }
    if (!$(e.target).closest('.custom-dropdown-img').length) {
      $('.custom-dropdown-img').removeClass('open');
    }
    if (containerOne.has(e.target).length === 0) {
      containerOne.removeClass('open');
    }
    if (containerTwo.has(e.target).length === 0) {
      containerTwo.removeClass('open');
    }
  });
// end custom select close
  // end custom select


  // autocomplete


  $('#autocomplete-field').autocomplete({
    lookup: function (query, done) {
      // Do Ajax call or lookup locally, when done,
      // call the callback and pass your results:
      var result = {
        suggestions: [
          {"value": "Львів", "data": "LV"},
          {"value": "Харків", "data": "KH"},
          {"value": "Одеса", "data": "OD"}
        ]
      };

      done(result);
    },
  });

  // end autocomplete

// modals
  function isAgeGateOpen() {
    return $('body').attr('data-age-gate') === '1';
  }

  function hasOpenSiteModal() {
    var open = false;

    $('.modal').each(function () {
      var $modal = $(this);

      // Confirmed age gate must not keep scroll lock
      if ($modal.hasClass('modal-age') && !isAgeGateOpen()) {
        if ($modal.css('display') !== 'none') {
          $modal.stop(true, true).hide();
        }
        return;
      }

      if ($modal.css('display') !== 'none') {
        open = true;
        return false;
      }
    });

    if ($('.battle-pass__daily-reward.is-open').length) {
      open = true;
    }

    return open;
  }

  function syncBodyModalLock() {
    if (isAgeGateOpen()) {
      $('body').addClass('is-open-modal is-age-gate');
      return;
    }

    $('body').removeClass('is-age-gate');

    if (hasOpenSiteModal()) {
      $('body').addClass('is-open-modal');
    } else {
      $('body').removeClass('is-open-modal');
    }
  }

  function syncAgeGateBodyClass() {
    syncBodyModalLock();
  }

  function openSiteModal($modal) {
    if (isAgeGateOpen()) {
      return false;
    }

    if (!$modal || !$modal.length) {
      return false;
    }

    $modal.stop(true, true).fadeIn(400);
    $('body').addClass('is-open-modal');
    return true;
  }

  function closeSiteModal($modal) {
    if (!$modal || !$modal.length) {
      return;
    }

    // Age gate closes only via .allow-18
    if ($modal.hasClass('modal-age')) {
      return;
    }

    $modal.stop(true, true).fadeOut(400, function () {
      $(this).hide();
      syncBodyModalLock();
    });
  }

  window.syncBodyModalLock = syncBodyModalLock;
  window.openSiteModal = openSiteModal;
  window.closeSiteModal = closeSiteModal;

  syncAgeGateBodyClass();

  // Block shop chrome / Binotel while age is not confirmed
  document.addEventListener('click', function (e) {
    if (!isAgeGateOpen()) {
      return;
    }

    var target = e.target.closest('a, button, [role="button"], input, textarea, select, [type="submit"]');
    if (!target) {
      return;
    }

    if (target.classList.contains('allow-18') || target.closest('.allow-18, .modal-age, .age-gate-bar, .modal-age__menu')) {
      return;
    }

    if (target.closest('[id^="bingc"], [class*="bingc"]')) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    // Allow reading links inside the article content only
    if (target.closest('#content, .information-information, .content-page') && target.tagName === 'A' && !target.closest('.header, .footer, .btn-wheel-action-wrap, .battle-pass__poster')) {
      return;
    }

    if (target.closest('.header, .footer, .btn-wheel-action-wrap, .battle-pass__poster, .modal:not(.modal-age), .nts-fixed-contact, .anchor-top, .mobile__phones')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  document.addEventListener('submit', function (e) {
    if (!isAgeGateOpen()) {
      return;
    }
    if (e.target && e.target.closest && e.target.closest('[id^="bingc"], [class*="bingc"]')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  $('.js-open-modal').click(function () {
    let id = $(this).data('modal');

    openSiteModal($(`.${id}`));
    return false
  });


  $(".js-open-review").click(function () {
    openSiteModal($('.modal--review'));
    return false
  });


  $(".js-open-authorization").click(function () {
    openSiteModal($('.modal--authorization'));
    return false
  });
  $(".js-open-basket").click(function () {
    openSiteModal($('.modal--basket'));
    return false
  });
  $(".js-open-toast").click(function () {
    if (isAgeGateOpen()) {
      return false;
    }

    $('.toast').addClass('is-show');

    // TODO: автоскрытие 4 sek
    setTimeout(function () {
      $('.toast').removeClass('is-show');
    }, 4000);
    return false
  });

  $(".toast__close").click(function () {
    $('.toast').removeClass('is-show');
    return false
  });

  $('.btn-wheel-action, .js-open-fortune').click(function () {
    openSiteModal($('.modal--wheel'));
    return false;
  });

  $('.js-btn-wheel-action-hide').click(function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (isAgeGateOpen()) {
      return false;
    }
    setCookie('fortune_hidden', '1', 1, 'days');
    $(this).closest('.btn-wheel-action-wrap').fadeOut(200);
    return false;
  });



  $('body').on('click', '.modal__overlay:not(.modal__overlay--disabled), .modal__close, .js-modal-close', function (e) {
    // Confirm age has its own handler; do not treat it as generic close
    if ($(this).hasClass('allow-18') || $(e.target).closest('.allow-18').length) {
      return;
    }

    var $modal = $(this).closest('.modal');
    closeSiteModal($modal);
    return false
  });


  $('.allow-18').click(function (e) {
    e.preventDefault();
    e.stopPropagation();

    var date = new Date();
    date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000)); // Встановлюємо тайм-аут на 30 днів

    var expires = "expires=" + date.toUTCString();
    document.cookie = "cookie_name=modal_age_visible; " + expires + "; path=/";

    $('body').removeAttr('data-age-gate').removeClass('is-age-gate');
    $('.modal-age, .age-gate-bar').stop(true, true).fadeOut(400, function () {
      $(this).hide();
      syncBodyModalLock();
    });

    if (typeof window.loadBinotelGetCall === 'function') {
      window.loadBinotelGetCall();
    }

    return false;
  });

  // end modals

  // plus - minus input
  /*
  $('.btn-minus').click(function () {
    var $input = $(this).parent().find('input');
    var count = parseInt($input.val()) - 1;
    count = count < 1 ? 1 : count;
    $input.val(count);
    $input.change();
    return false;
  });
  $('.btn-plus').click(function () {
    var $input = $(this).parent().find('input');
    $input.val(parseInt($input.val()) + 1);
    $input.change();
    return false;
  });
*/
  // end plus - minus input


  $('.radio-pickup').change(function () {
    if ($(this).is(":checked")) {
      $(".radio-courier-wrapper").addClass('disabled');
      $(".radio-pickup-wrapper").removeClass('disabled');
    }
  });

  $('.radio-courier').change(function () {
    if ($(this).is(":checked")) {
      $(".radio-courier-wrapper").removeClass('disabled');
      $(".radio-pickup-wrapper").addClass('disabled');
    }
  });


  $('.checkbox-recipient').change(function () {
    if ($(this).is(":checked")) {
      $(".js-recipient").addClass('disabled');
    } else {
      $(".js-recipient").removeClass('disabled');
    }
  });


// tabs
  $('.card__tabs-button, .card__open-characteristic, .card__open-reviews, .card__open-availability').click(function () {
    let openEl = $('.' + $(this).data('open'));

    $(openEl).siblings().removeClass('is-open');
    $(this).siblings().removeClass('is-open');
    $(this).addClass('is-open');
    $(openEl).addClass('is-open');

  });


  $(".js-scroll").on("click", "a", function (event) {
    event.preventDefault();
    var id = $(this).attr('href'),
      top = $(id).offset().top;
    $('body,html').animate({scrollTop: top - 126}, 1000);
  });

  $(".js-scroll-reviews").on("click", "a", function (event) {
    event.preventDefault();
    var id = $(this).data('href'),
      top = $(id).offset().top;
    $('body,html').animate({scrollTop: top - 126}, 1000);
    $('.card__tabs-button').removeClass('is-open');
    $('.button-tab-reviews').addClass('is-open');
  });

  $(".js-scroll-characteristic").on("click", "a", function (event) {
    event.preventDefault();
    var id = $(this).data('href'),
      top = $(id).offset().top;
    $('body,html').animate({scrollTop: top - 126}, 1000);
    $('.card__tabs-button').removeClass('is-open');
    $('.button-tab-characteristic').addClass('is-open');
  });


  $(".js-scroll-availability").on("click", "a", function (event) {
    event.preventDefault();
    var id = $(this).data('href'),
      top = $(id).offset().top;
    $('body,html').animate({scrollTop: top - 126}, 1000);
    $('.card__tabs-button').removeClass('is-open');
    $('.button-tab-availability').addClass('is-open');
  });

  $('body').delegate('.checkout__tabs-button', 'click', function () {
    var openEl = $('.' + $(this).data('open'));

//    $(openEl).siblings().removeClass('is-open');
    $(this).siblings().removeClass('is-open');
    $(this).addClass('is-open');

    if ($('.checkout__tabs-button:first-child').hasClass('is-open')) {
      $('.row-customer_firstname').show();
      $('.row-customer_lastname').show();
      $('.row-customer_fax').show();
    } else {
      $('.row-customer_firstname').hide();
      $('.row-customer_lastname').hide();
      $('.row-customer_fax').hide();
    }


//    $(openEl).addClass('is-open');
  });


  // checkout order tabs — start

  $('body').on('click', '.checkout__order-tabs-button', function () {
    let openEl = $('#' + $(this).data('open'));

    $(this).siblings('.checkout__order-tabs-button').removeClass('is-open');
    $(this).addClass('is-open');
    openEl.siblings('.checkout__order-tabs-panel').removeClass('is-open');
    openEl.addClass('is-open');

    if ($(this).data('open') === 'checkout-order-additional' && window.checkoutAdditionalSwiper) {
      window.checkoutAdditionalSwiper.update();
    }
  });
  // checkout order tabs — end



  // BASKET SLIDER
  basketTopSaleProducts();
  //


  let swiper = new Swiper(".home__slider", {
    navigation: {
      nextEl: ".home .swiper-button-next",
      prevEl: ".home .swiper-button-prev"
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true
    },
    spaceBetween: 0,
    slidesPerView: 'auto',
    loop: true,
  });

  let swiperHomePartners = new Swiper(".home-partners__slider", {
    speed: 800,
    loop: false,
    slidesPerView: 'auto',
    autoplay: {
      delay: 2500,
      disableOnInteraction: false,
    },
  });

  if (window.matchMedia("(max-width: 992px)").matches) {
    // if($('.home__slider').length) swiper.destroy();
    if ($('.home-partners__slider').length) swiperHomePartners.destroy();
  }


  let ocfCategorySlider = new Swiper('.js-ocf-category-slider', {
    speed: 800,
    loop: false,
    slidesPerView: 'auto',
    watchOverflow: true,
    // autoplay: {
    //     delay: 3000, // 3 секунды
    //     disableOnInteraction: false, // слайдер продолжает автопрокрутку даже после взаимодействия
    //     pauseOnMouseEnter: true,
    // },
    navigation: {
      nextEl: ".js-ocf-category-slider .swiper-button-next",
      prevEl: ".js-ocf-category-slider .swiper-button-prev"
    },
    on: {
      slideChangeTransitionEnd: function () {
        const currentTranslate = this.translate;
        const maxTranslate = this.maxTranslate();

        // Если мы близко к концу (менее 5px до maxTranslate),
        // принудительно показываем последний слайд
        if (Math.abs(currentTranslate - maxTranslate) < 5) {
          this.slideTo(this.slides.length - 1, 0); // без анимации, сразу
        }

        // Обновляем навигацию, чтобы правильно показать/скрыть кнопки
        this.navigation.update();
      }
    }
  })

  let swiper2 = new Swiper(".card__slider", {
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev"
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true
    }
  });


  $('.header__search [type="search"]').on('focus', function () {
    if ($(window).width() < 768) {
      $('body').addClass('mobile-search-active');
    }
  });

  $(document).on('mouseup touchstart', function (e) {
    if ($('.header__search-wrapper').length) {
      let hm_search = $(".header__search-wrapper");

      if (!hm_search.is(e.target) && hm_search.has(e.target).length === 0) {
        $('body').removeClass('mobile-search-active');

        if ($(window).width() < 768) $('#live-search').hide();
      }
    }
  });


  (function () {
    let menuWrapper = document.querySelector('.profile__menu-wrapper');
    let activeLink = document.querySelector('.profile__menu li a.is-active');

    if (!menuWrapper || !activeLink || window.innerWidth >= 1024) {
      return;
    }

    let wrapperRect = menuWrapper.getBoundingClientRect();
    let linkRect = activeLink.getBoundingClientRect();
    let scrollLeft = menuWrapper.scrollLeft
      + (linkRect.left - wrapperRect.left)
      - (wrapperRect.width / 2)
      + (linkRect.width / 2);

    menuWrapper.scrollLeft = Math.max(0, scrollLeft);
  })();

  // let profileMenu = document.querySelector('.profile__menu');
  // if ($('.profile__menu a.is-active').length) {
  //   let profileLink = $('.profile__menu li a.is-active')
  //   let itemOffsetLeft = profileLink.offset().left;
  //   let containerScrollLeft = $(".profile__menu").scrollLeft();
  //   let scrollLeftUpd = containerScrollLeft + itemOffsetLeft - 40;
  //   $('.profile__menu').animate({
  //     scrollLeft: scrollLeftUpd
  //   }, 0);
  // }


  $('.anchor-actions__categories,.js-open-mobile-category').click(function () {
    $('body').toggleClass('is-open-categories');
    return false;
  });
  $('.drop-categories__close').click(function () {
    $('body').removeClass('is-open-categories');
    return false;
  });
  $('.header__burger').click(function () {
    $('body').removeClass('js-open-phones js-open-search').toggleClass('is-open-menu');
    return false;
  });
  $('.header__btn-phone, .mobile__phones-close').click(function () {
    $('body').removeClass('is-open-menu js-open-search').toggleClass('js-open-phones');
  });
  $('.header__btn-search').click(function () {
    $('body').removeClass('is-open-menu js-open-phones').toggleClass('js-open-search');
  });


  $('.header__search-close').click(function (e) {
    $('.header__search').removeClass('js-zindex js-focus');
    $('body').removeClass('mobile-search-active js-open-search');
    $('.header__search-wrapper').removeClass('is-active');
    $('.search--overlay').hide();
    $('#input_search').val('');
  });


  $("#input_search").on({
    focus: function () {
      if ($(window).width > 640) {
        $('.header__search').addClass('js-zindex js-focus');
        $('.header__search-wrapper').addClass('is-active');

        $('.search--overlay').fadeIn(300);
      }
    }
  });

  $('.search--overlay').click(function (e) {
    if ($(window).width > 640) {
      $('.header__search').removeClass('js-focus js-zindex');
      $('.header__search-wrapper').removeClass('is-active');
      $(this).hide();
    }
  });


// accordion
  (function () {
    var initAccordion = function (itemsClass, headerClass, bodyClass) {
      var $items = $(itemsClass);

      $items.find(headerClass).on('click', function () {
        $item = $(this).parent();
        $items.not($item).removeClass('is-open');
        $items.not($item).find(headerClass).removeClass('is-open');
        $items.not($item).find(bodyClass).slideUp(400);


        if ($(this).hasClass('is-open')) {
          $item.removeClass('is-open');
          $(this).removeClass('is-open');
          $(this).siblings(bodyClass).slideUp(400);

        } else {
          $item.addClass('is-open');
          $(this).addClass('is-open');
          $(this).siblings(bodyClass).slideDown(400);

        }
      });
    };

    // suppliers
    initAccordion('.contacts__store', '.contacts__store-header', '.contacts__store-body');


  })($);


  //страница сравнения


  let comparisonTable, productSliders = [];

  if (document.querySelector(".comparison__table_slider")) {
    comparisonTable = new Swiper(".comparison__table_slider", {
      slidesPerView: 2,
      spaceBetween: 0,
      speed: 300,

      breakpoints: {
        992: {
          slidesPerView: 3,
        },

        1200: {
          slidesPerView: 4,
        },

      }
    });

    comparisonTable.on("slideChangeTransitionStart", (swiper) => {
      if (productSliders.length) {
        productSliders.map(slider => {
          slider.slideTo(swiper.activeIndex, 300);
        })
      }
    })
  }

  document.querySelectorAll(".comparison__products_inner").forEach(sliderElement => {

    let slider = new Swiper(sliderElement, {
      slidesPerView: 2,
      spaceBetween: 0,
      speed: 300,
      navigation: {
        nextEl: ".comparison__products_controls .swiper-button-next",
        prevEl: ".comparison__products_controls .swiper-button-prev"
      },

      breakpoints: {

        992: {
          slidesPerView: 3,
        },

        1200: {
          slidesPerView: 4,
        }
      }
    });

    slider.on("slideChangeTransitionStart", (swiper) => {
      comparisonTable.slideTo(swiper.activeIndex, 300)
    })

    productSliders.push(slider);

  })

  if (productSliders.length) {
    productSliders.map(slider => {
      slider.on("slideChangeTransitionStart", (swiper) => comparisonTable.slideTo(swiper.activeIndex, 300))
    })
  }


  document.body.addEventListener("click", (event) => {

    if (event.target.closest(".product__remove")) {

      const product = event.target.closest(".product"),
        slider = document.querySelector(".comparison__products_inner");

      var product_id = product.getAttribute("data-id");

      let removeIndex = false;

      slider.querySelectorAll(".swiper-slide").forEach((slide, index) => {
        if (product.closest(".swiper-slide").getAttribute("aria-label") == slide.getAttribute("aria-label")) removeIndex = index;
      })

      document.querySelectorAll(".comparison__products_inner, .comparison__table_slider").forEach(sliderElement => {
        sliderElement.querySelectorAll(".swiper-slide").forEach((slide, index) => {
          if (index == removeIndex) {
            slide.classList.add("is-hidden");
            setTimeout(() => {
              slide.remove();

              if (document.querySelectorAll(".comparison__products_inner .swiper-slide").length == 0) {
                document.querySelector(".comparison__table").classList.add("is-hidden");
              } else {
                document.querySelector(".comparison__table").classList.remove("is-hidden");
              }

            }, 300)
          }
        })
      })

      if (productSliders.length) {
        productSliders.map(slider => slider.update());
        comparisonTable.update();
      }

      $.ajax({
        url: 'index.php?route=product/compare/remove',
        type: 'post',
        data: 'product_id=' + product_id,
        dataType: 'json',
        success: function (json) {

          if (json['success']) {
            $('#compare-total span').text(json['total']);
            $('.toast .p').html(json['success']);
            $('.toast').addClass('is-show');

            // TODO: автоскрытие 4 sek
            setTimeout(function () {
              $('.toast').removeClass('is-show');
            }, 4000);
            return false
          }
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    }

    if (event.target.closest(".remove_all_products")) {
      event.preventDefault();
      document.querySelectorAll(".comparison__products_inner, .comparison__table_slider").forEach(sliderElement => {
        sliderElement.querySelectorAll(".swiper-slide").forEach((slide, index) => {
          slide.classList.add("is-hidden");
          setTimeout(() => {
            slide.remove()

            if (document.querySelectorAll(".comparison__products_inner .swiper-slide").length == 0) {
              document.querySelector(".comparison__table").classList.add("is-hidden");
            } else {
              document.querySelector(".comparison__table").classList.remove("is-hidden");
            }

          }, 300)
        })
      })

      if (productSliders.length) {
        productSliders.map(slider => slider.update());
        comparisonTable.update();
      }

      $.ajax({
        url: 'index.php?route=product/compare/remove',
        type: 'post',
        data: 'product_id=all',
        dataType: 'json',
        success: function (json) {
          if (json['success']) {
            $('#compare-total span').text(json['total']);
            $('.toast .p').html(json['success']);
            $('.toast').addClass('is-show');

            // TODO: автоскрытие 4 sek
            setTimeout(function () {
              $('.toast').removeClass('is-show');
            }, 4000);
            return false
          }
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    }
  })


  function initCalcHeightListItems() {

    let liLength = document.querySelectorAll(".comparison__table_head li").length;

    document.querySelectorAll(".comparison__table_head li").forEach((li, index) => {
      li.setAttribute("data-index", index);
      const info = li.innerHTML;
      li.innerHTML = `<span>${info}</span>`;
    })

    document.querySelectorAll(".comparison__table_slide").forEach(slide => {
      slide.querySelectorAll("li").forEach((li, index) => {
        li.setAttribute("data-index", index);
        const info = li.innerHTML;
        li.innerHTML = `<span>${info}</span>`;
      })
    })

    for (let index = 0; index < liLength; index++) {
      let maxHeight = 0,
        listLi = document.querySelectorAll(`.comparison__table_head li[data-index="${index}"], .comparison__table_slide li[data-index="${index}"]`);
      listLi.forEach(li => {
        maxHeight = Math.max(maxHeight, li.querySelector("span").scrollHeight);
      })

      listLi.forEach(li => {
        li.style.setProperty("--min-height", maxHeight + 'px');
      })
    }

  }

  let liLength = document.querySelectorAll(".comparison__table_head li").length;

  function calcHeightListItems() {

    for (let index = 0; index < liLength; index++) {
      let maxHeight = 0,
        listLi = document.querySelectorAll(`.comparison__table_head li[data-index="${index}"], .comparison__table_slide li[data-index="${index}"]`);

      listLi.forEach(li => {
        maxHeight = Math.max(maxHeight, li.querySelector("span").scrollHeight);
      })

      listLi.forEach(li => {
        li.style.setProperty("--min-height", maxHeight + 'px');
      })
    }

  }

  if (document.querySelector(".comparison__table_head") && document.querySelector(".comparison__table_slide")) {
    initCalcHeightListItems();

    setInterval(() => {
      calcHeightListItems()
    }, 100)
  }


  let comparisonHeaderCheck = false;
  const comparisonHeaderFixed = $(".comparison__fixed_header");
  if (comparisonHeaderFixed) {
    const scroll = () => {
      if (window.scrollY >= 500 && !comparisonHeaderCheck) {
        comparisonHeaderCheck = true;
        comparisonHeaderFixed.addClass("is-visible");
      } else if (window.scrollY < 500 && comparisonHeaderCheck) {
        comparisonHeaderCheck = false;
        comparisonHeaderFixed.removeClass("is-visible");
      }
    }

    scroll();

    window.addEventListener("scroll", scroll)
  }


  // nts buttons

  jQuery('#nts_fixed_contact_button').on('click', function () {
    jQuery(this).toggleClass('clicked');
    jQuery('.nts-fixed-contact-dropdown').toggleClass('expanded');
    jQuery('.nts-fixed-contact-icon .nts-envelope').toggleClass('d-none');
    jQuery('.nts-fixed-contact-icon .nts-fixed-contact-text').toggleClass('d-none');
    jQuery('#nts_fixed_contact_substrate').toggleClass('active');
  });

  jQuery('.nts-fixed-contact-item').click(function (e) {
    jQuery('#nts_fixed_contact_substrate').trigger('click')
  });

  jQuery('#nts_fixed_contact_substrate').on('click', function () {
    jQuery(this).removeClass('active');
    jQuery('.nts-fixed-contact-dropdown').removeClass('expanded');
    jQuery('.nts-fixed-contact-icon .nts-envelope').removeClass('d-none');
    jQuery('.nts-fixed-contact-icon .nts-fixed-contact-text').toggleClass('d-none');
    jQuery('#nts_fixed_contact_button').removeClass('clicked');
  });

  jQuery('.nts-fixed-contact-dropdown').click(function (e) {
    e.stopPropagation();
  });

  // Modal Cart Discount Toggle

  $('body').on('click', '.cart-discount__action-toggle', function (e) {
    $(this).toggleClass('is-open');
    $('.cart-discount__action').toggleClass('is-open');
  });


  $('.js-open-battle-pass').click(function () {
    openSiteModal($('.modal--battle-pass'));
    return false;
  });

  if (document.querySelector(".js-card-related")) {
    new Swiper(".js-card-related", {
      slidesPerView: "auto",
      spaceBetween: 0,
      // loop: true,
      allowTouchMove: true,
      navigation: {
        nextEl: ".card__related-next",
        prevEl: ".card__related-prev"
      }
    });
  }

});


/**
 * Функция для работы с Cookie
 * setCookie - установить Cookie
 * getCookie - получить Cookie
 */
function setCookie(name, value, time, unit = "days") {
  const units = {
    hours: 60 * 60 * 1000,
    days: 24 * 60 * 60 * 1000
  };

  const ms = time * (units[unit] || units["days"]); // на случай ошибки unit

  const date = new Date();
  date.setTime(date.getTime() + ms);

  document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/`;
}

function getCookie(name) {
  let match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  return match ? match[2] : null;
}


var UA_MOBILE_OPERATORS = ['031', '032', '033', '034', '035', '036', '037', '041',
  '043', '044', '045', '046', '047', '048', '049', '050', '051', '052', '053',
  '054', '055', '056', '057', '061', '062', '063', '064', '066', '067', '068',
  '073', '091', '092', '093', '094', '095', '096', '097', '098', '099', '077', '075'
];

/**
 * Нормализует UA-номер к 10 цифрам вида 0XXXXXXXXX (как в маске +38(0XX)...).
 */
function getUaMobileNational(val) {
  if (!val) {
    return '';
  }

  var formatted = String(val).replace(/\s/g, '');
  // Маска Simple/Maska: +38(0XX)XXX-XX-XX
  var match = formatted.match(/^\+38\((\d{3})\)(\d{3})-?(\d{2})-?(\d{2})$/);

  if (match) {
    return match[1] + match[2] + match[3] + match[4];
  }

  var digits = String(val).replace(/\D/g, '');

  if (digits.indexOf('380') === 0 && digits.length === 12) {
    return '0' + digits.substr(3);
  }

  if (digits.indexOf('0') === 0 && digits.length === 10) {
    return digits;
  }

  return '';
}

function isFakeMobileNumber(val) {
  var national = getUaMobileNational(val);

  if (national.length !== 10) {
    return false;
  }

  // Все цифры одинаковые: 0000000000, 0111111111 уже покроет код оператора
  if (/^(\d)\1{9}$/.test(national)) {
    return true;
  }

  // Абонентская часть из одинаковых цифр: 0500000000, 0671111111
  if (/^(\d)\1{6}$/.test(national.substr(3))) {
    return true;
  }

  if (national === '0123456789' || national === '1234567890' || national.substr(1) === '123456789') {
    return true;
  }

  return false;
}

function detectedMobileErrors(el) {
  var val = el.val();
  var $parent = el.parent();
  var hasSimpleRules = $parent.find('.simplecheckout-rule-group').length > 0;

  // Пустое или неполное значение маски - не показываем ошибку оператора
  if (!val || !String(val).trim() || String(val).indexOf('_') !== -1) {
    $parent.find('.form-message.error').remove();
    el.removeClass('error');
    return;
  }

  var message = '';

  if (!detectedMobileCode(val)) {
    message = 'Вкажіть правильний код мобільного оператора!';
  } else if (isFakeMobileNumber(val)) {
    message = 'Вкажіть коректний номер телефону!';
  }

  $parent.find('.form-message.error').remove();

  if (message) {
    el.addClass('error');
    // Для Simple-полей текст ошибки уже в .simplecheckout-rule - не дублируем
    if (!hasSimpleRules) {
      $parent.append('<p class="form-message error">' + message + '</p>');
    } else {
      $parent.find('.simplecheckout-rule[data-rule="regexp"]').show();
    }
  } else {
    el.removeClass('error');
    if (hasSimpleRules) {
      $parent.find('.simplecheckout-rule[data-rule="regexp"]').hide();
    }
  }
}

function detectedMobileCode(val) {
  if (!val) {
    return true;
  }

  val = String(val).replace(/\s/g, '');

  // Основной путь: маска +38(0XX)...
  var code = val.substr(4, 3).replace(/_/g, '');

  if (code.length === 3 && /^\d{3}$/.test(code)) {
    return $.inArray(code, UA_MOBILE_OPERATORS) !== -1;
  }

  // Fallback для голых цифр 380XXXXXXXXX → код 0XX
  var digits = String(val).replace(/\D/g, '');

  if (digits.indexOf('380') === 0 && digits.length >= 5) {
    code = '0' + digits.substr(3, 2);
    return $.inArray(code, UA_MOBILE_OPERATORS) !== -1;
  }

  return true;
}

/**
 * Полная проверка UA-мобильного для checkout (клиент + получатель).
 * Пустые / неполные значения не блокируем здесь - их ловит Simple (required/regexp).
 */
window.validateCheckoutMobilePhones = function () {
  var ok = true;
  var selectors = ['#customer_telephone', '#shipping_address_receiver_phone'];

  $.each(selectors, function (_, selector) {
    var $el = $(selector);

    if (!$el.length || !$el.is(':visible') || $el.is(':disabled')) {
      return;
    }

    var val = $.trim($el.val() || '');

    if (!val || val.indexOf('_') !== -1) {
      return;
    }

    var national = getUaMobileNational(val);

    if (national.length < 10) {
      return;
    }

    if (!detectedMobileCode(val) || isFakeMobileNumber(val)) {
      detectedMobileErrors($el);
      ok = false;
    }
  });

  return ok;
};

function basketTopSaleProducts() {
  let swiperBasket = new Swiper(".js-product-basket", {
    // loop: true,
    slidesPerView: 'auto',
    navigation: {
      nextEl: ".modal__additional-actions .swiper-button-next",
      prevEl: ".modal__additional-actions .swiper-button-prev"
    },
  });
}

// checkout additional slider — start
window.initCheckoutAdditionalSlider = function () {
  var sliderEl = document.querySelector('.js-checkout-additional-slider');

  if (!sliderEl) {
    window.checkoutAdditionalSwiper = null;
    return null;
  }

  if (window.checkoutAdditionalSwiper && typeof window.checkoutAdditionalSwiper.destroy === 'function') {
    window.checkoutAdditionalSwiper.destroy(true, true);
    window.checkoutAdditionalSwiper = null;
  }

  window.checkoutAdditionalSwiper = new Swiper(sliderEl, {
    slidesPerView: 1,
    spaceBetween: 0,
    observer: true,
    observeParents: true,
    navigation: {
      nextEl: '.checkout__additional-actions .swiper-button-next',
      prevEl: '.checkout__additional-actions .swiper-button-prev'
    },
  });

  return window.checkoutAdditionalSwiper;
};

window.initCheckoutAdditionalSlider();
// checkout additional slider — end

function showLoginModal() {
  if ($('body').attr('data-age-gate') === '1') {
    return;
  }

  $('.modal--wheel').hide();
  if (typeof window.syncBodyModalLock === 'function') {
    window.syncBodyModalLock();
  } else {
    $('body').removeClass('is-open-modal');
  }
  $('.regsms_open').trigger('click');
}


// copy code
document.addEventListener('DOMContentLoaded', function () {
  const copyButtons = document.querySelectorAll('.js-copy-code');

  copyButtons.forEach((button) => {
    button.addEventListener('click', async () => {
      const box = button.closest('.js-code-parent');
      if (!box) return;

      const textEl = box.querySelector('.js-input-code');
      if (!textEl) return;

      const icon = button.querySelector('use');
      const originalHref = icon ? icon.getAttribute('href') : null;

      const code = textEl.textContent.trim();

      try {
        await navigator.clipboard.writeText(code);

        if (icon) {
          icon.setAttribute('href', '#icon-check');
        }

        button.disabled = true;

        setTimeout(() => { 
          if (icon && originalHref) {
            icon.setAttribute('href', originalHref);
          }
          button.disabled = false;
        }, 2000);

      } catch (err) {
        console.error('Copy failed:', err);
      }
    });
  });
});

