if (typeof __head_flag == "undefined") {
	var cookieDomain = window.location.hostname.split('.').slice(-2).join('.');
	var __permanent_hash_key = '7856a9943c5cb6b9947e8b1f3fdecdf3';
	var Md5Util = {
		hexcase: 0,
		b64pad: "",
		chrsz: 8,
		binl2hex: function (a) {
			var b = this.hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
			var c = "";
			for (var i = 0; i < a.length * 4; i++) {
				c += b.charAt((a[i >> 2] >> ((i % 4) * 8 + 4)) & 0xF) + b.charAt((a[i >> 2] >> ((i % 4) * 8)) & 0xF)
			}
			return c
		},
		core_md5: function (x, e) {
			x[e >> 5] |= 0x80 << ((e) % 32);
			x[(((e + 64) >>> 9) << 4) + 14] = e;
			var a = 1732584193;
			var b = -271733879;
			var c = -1732584194;
			var d = 271733878;
			for (var i = 0; i < x.length; i += 16) {
				var f = a;
				var g = b;
				var h = c;
				var j = d;
				a = this.md5_ff(a, b, c, d, x[i + 0], 7, -680876936);
				d = this.md5_ff(d, a, b, c, x[i + 1], 12, -389564586);
				c = this.md5_ff(c, d, a, b, x[i + 2], 17, 606105819);
				b = this.md5_ff(b, c, d, a, x[i + 3], 22, -1044525330);
				a = this.md5_ff(a, b, c, d, x[i + 4], 7, -176418897);
				d = this.md5_ff(d, a, b, c, x[i + 5], 12, 1200080426);
				c = this.md5_ff(c, d, a, b, x[i + 6], 17, -1473231341);
				b = this.md5_ff(b, c, d, a, x[i + 7], 22, -45705983);
				a = this.md5_ff(a, b, c, d, x[i + 8], 7, 1770035416);
				d = this.md5_ff(d, a, b, c, x[i + 9], 12, -1958414417);
				c = this.md5_ff(c, d, a, b, x[i + 10], 17, -42063);
				b = this.md5_ff(b, c, d, a, x[i + 11], 22, -1990404162);
				a = this.md5_ff(a, b, c, d, x[i + 12], 7, 1804603682);
				d = this.md5_ff(d, a, b, c, x[i + 13], 12, -40341101);
				c = this.md5_ff(c, d, a, b, x[i + 14], 17, -1502002290);
				b = this.md5_ff(b, c, d, a, x[i + 15], 22, 1236535329);
				a = this.md5_gg(a, b, c, d, x[i + 1], 5, -165796510);
				d = this.md5_gg(d, a, b, c, x[i + 6], 9, -1069501632);
				c = this.md5_gg(c, d, a, b, x[i + 11], 14, 643717713);
				b = this.md5_gg(b, c, d, a, x[i + 0], 20, -373897302);
				a = this.md5_gg(a, b, c, d, x[i + 5], 5, -701558691);
				d = this.md5_gg(d, a, b, c, x[i + 10], 9, 38016083);
				c = this.md5_gg(c, d, a, b, x[i + 15], 14, -660478335);
				b = this.md5_gg(b, c, d, a, x[i + 4], 20, -405537848);
				a = this.md5_gg(a, b, c, d, x[i + 9], 5, 568446438);
				d = this.md5_gg(d, a, b, c, x[i + 14], 9, -1019803690);
				c = this.md5_gg(c, d, a, b, x[i + 3], 14, -187363961);
				b = this.md5_gg(b, c, d, a, x[i + 8], 20, 1163531501);
				a = this.md5_gg(a, b, c, d, x[i + 13], 5, -1444681467);
				d = this.md5_gg(d, a, b, c, x[i + 2], 9, -51403784);
				c = this.md5_gg(c, d, a, b, x[i + 7], 14, 1735328473);
				b = this.md5_gg(b, c, d, a, x[i + 12], 20, -1926607734);
				a = this.md5_hh(a, b, c, d, x[i + 5], 4, -378558);
				d = this.md5_hh(d, a, b, c, x[i + 8], 11, -2022574463);
				c = this.md5_hh(c, d, a, b, x[i + 11], 16, 1839030562);
				b = this.md5_hh(b, c, d, a, x[i + 14], 23, -35309556);
				a = this.md5_hh(a, b, c, d, x[i + 1], 4, -1530992060);
				d = this.md5_hh(d, a, b, c, x[i + 4], 11, 1272893353);
				c = this.md5_hh(c, d, a, b, x[i + 7], 16, -155497632);
				b = this.md5_hh(b, c, d, a, x[i + 10], 23, -1094730640);
				a = this.md5_hh(a, b, c, d, x[i + 13], 4, 681279174);
				d = this.md5_hh(d, a, b, c, x[i + 0], 11, -358537222);
				c = this.md5_hh(c, d, a, b, x[i + 3], 16, -722521979);
				b = this.md5_hh(b, c, d, a, x[i + 6], 23, 76029189);
				a = this.md5_hh(a, b, c, d, x[i + 9], 4, -640364487);
				d = this.md5_hh(d, a, b, c, x[i + 12], 11, -421815835);
				c = this.md5_hh(c, d, a, b, x[i + 15], 16, 530742520);
				b = this.md5_hh(b, c, d, a, x[i + 2], 23, -995338651);
				a = this.md5_ii(a, b, c, d, x[i + 0], 6, -198630844);
				d = this.md5_ii(d, a, b, c, x[i + 7], 10, 1126891415);
				c = this.md5_ii(c, d, a, b, x[i + 14], 15, -1416354905);
				b = this.md5_ii(b, c, d, a, x[i + 5], 21, -57434055);
				a = this.md5_ii(a, b, c, d, x[i + 12], 6, 1700485571);
				d = this.md5_ii(d, a, b, c, x[i + 3], 10, -1894986606);
				c = this.md5_ii(c, d, a, b, x[i + 10], 15, -1051523);
				b = this.md5_ii(b, c, d, a, x[i + 1], 21, -2054922799);
				a = this.md5_ii(a, b, c, d, x[i + 8], 6, 1873313359);
				d = this.md5_ii(d, a, b, c, x[i + 15], 10, -30611744);
				c = this.md5_ii(c, d, a, b, x[i + 6], 15, -1560198380);
				b = this.md5_ii(b, c, d, a, x[i + 13], 21, 1309151649);
				a = this.md5_ii(a, b, c, d, x[i + 4], 6, -145523070);
				d = this.md5_ii(d, a, b, c, x[i + 11], 10, -1120210379);
				c = this.md5_ii(c, d, a, b, x[i + 2], 15, 718787259);
				b = this.md5_ii(b, c, d, a, x[i + 9], 21, -343485551);
				a = this.safe_add(a, f);
				b = this.safe_add(b, g);
				c = this.safe_add(c, h);
				d = this.safe_add(d, j)
			}
			return Array(a, b, c, d)
		},
		md5_cmn: function (q, a, b, x, s, t) {
			return this.safe_add(this.bit_rol(this.safe_add(this.safe_add(a, q), this.safe_add(x, t)), s), b)
		},
		md5_ff: function (a, b, c, d, x, s, t) {
			return this.md5_cmn((b & c) | ((~b) & d), a, b, x, s, t)
		},
		md5_gg: function (a, b, c, d, x, s, t) {
			return this.md5_cmn((b & d) | (c & (~d)), a, b, x, s, t)
		},
		md5_hh: function (a, b, c, d, x, s, t) {
			return this.md5_cmn(b ^ c ^ d, a, b, x, s, t)
		},
		md5_ii: function (a, b, c, d, x, s, t) {
			return this.md5_cmn(c ^ (b | (~d)), a, b, x, s, t)
		},
		str2binl: function (a) {
			var b = Array();
			var c = (1 << this.chrsz) - 1;
			for (var i = 0; i < a.length * this.chrsz; i += this.chrsz) b[i >> 5] |= (a.charCodeAt(i / this.chrsz) & c) << (i % 32);
			return b
		},
		safe_add: function (x, y) {
			var a = (x & 0xFFFF) + (y & 0xFFFF);
			var b = (x >> 16) + (y >> 16) + (a >> 16);
			return (b << 16) | (a & 0xFFFF)
		},
		bit_rol: function (a, b) {
			return (a << b) | (a >>> (32 - b))
		},
		hex_md5: function (s) {
			return this.binl2hex(this.core_md5(this.str2binl(s), s.length * this.chrsz))
		}
	};
	var CookieUtil = {
		get: function (a) {
			var b = encodeURIComponent(a) + "=",
				cookieStart = document.cookie.indexOf(b),
				cookieValue = "";
			if (cookieStart > -1) {
				var c = document.cookie.indexOf(";", cookieStart);
				if (c == -1) {
					c = document.cookie.length
				}
				cookieValue = decodeURIComponent(document.cookie.substring(cookieStart + b.length, c))
			}
			return cookieValue
		},
		set: function (a, b, c, d, e, f) {
			var g = encodeURIComponent(a) + "=" + encodeURIComponent(b);
			if (c instanceof Date) {
				g += "; expires=" + c.toGMTString()
			}
			if (d) {
				g += "; path=" + d
			}
			if (e) {
				g += "; domain=" + e
			}
			if (f) {
				g += "; secure"
			}
			document.cookie = g
		},
		unset: function (a, b, c, d) {
			this.set(a, "", new Date(0), b, c, d)
		}
	};
	var __permanentFunctions = {
		createPermanentID: function () {
			var n = new Date();
			var y = n.getFullYear() + '';
			var m = n.getMonth() + 1;
			if (m < 10) m = "0" + m;
			var d = n.getDate();
			if (d < 10) d = "0" + d;
			var H = n.getHours();
			if (H < 10) H = "0" + H;
			var M = n.getMinutes();
			if (M < 10) M = "0" + M;
			var S = n.getSeconds();
			if (S < 10) S = "0" + S;
			var a = "00" + n.getMilliseconds();
			a = a.substr(a.length - 3, 3);
			var b = Math.floor(100000 + Math.random() * 900000);
			var c = Math.floor(100000 + Math.random() * 900000);
			var e = y + m + d + H + M + S + a + b + c + __permanent_hash_key;
			var f = Md5Util.hex_md5(e);
			f = this.formatHashCode(f);
			return y + m + d + H + M + S + a + f + b + c
		},
		formatHashCode: function (a) {
			var b = parseInt(a.substr(0, 8), 16);
			var c = String(b).substr(0, 6);
			var d = c.length;
			if (d < 6) {
				c += this.str_repeat('0', Math.abs(6 - d))
			}
			return c
		},
		str_repeat: function (a, b) {
			return new Array(b + 1).join(a)
		},
		initTime: function () {
			var t = new Date();
			return t.getTime()
		},
		init: function () {
			this.permanent_id = CookieUtil.get("__permanent_id");
			if (typeof this.permanent_id == 'undefined' || !/^\d{35}$/.test(this.permanent_id)) {
				var a = new Date(2020, 1, 1);
				this.permanent_id = this.createPermanentID();
				CookieUtil.set("__permanent_id", this.permanent_id, a, "/", cookieDomain)
			}
		}
	};
	__permanentFunctions.init()
}
